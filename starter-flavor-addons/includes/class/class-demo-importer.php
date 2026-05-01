<?php
/**
 * One-Click Demo Importer
 *
 * Provides admin UI and AJAX endpoints to import demo content.
 * Integrates with the Starter Flavor theme's demo JSON files.
 *
 * @package Starter_Flavor_Addons
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SFA_Demo_Importer {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu',      array( $this, 'add_admin_page' ) );
		add_action( 'wp_ajax_sfa_get_demos',    array( $this, 'ajax_get_demos' ) );
		add_action( 'wp_ajax_sfa_import_demo',  array( $this, 'ajax_import_demo' ) );
	}

	public function add_admin_page(): void {
		add_theme_page(
			esc_html__( 'Demo Importer', 'starter-flavor-addons' ),
			esc_html__( 'Demo Importer', 'starter-flavor-addons' ),
			'manage_options',
			'sfa-demo-importer',
			array( $this, 'render_admin_page' )
		);
	}

	public function render_admin_page(): void {
		$demos = $this->get_available_demos();
		?>
		<div class="wrap sfa-demo-importer">
			<h1><?php esc_html_e( 'Starter Flavor — Demo Importer', 'starter-flavor-addons' ); ?></h1>
			<p><?php esc_html_e( 'Choose a demo to import. This will create pages and set up Elementor templates.', 'starter-flavor-addons' ); ?></p>
			<div class="sfa-demo-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:24px;margin-top:24px;">
				<?php foreach ( $demos as $demo ) : ?>
				<div class="sfa-demo-card" style="border:1px solid #ddd;border-radius:8px;overflow:hidden;background:#fff;">
					<?php if ( ! empty( $demo['preview_image'] ) ) : ?>
					<img src="<?php echo esc_url( $demo['preview_image'] ); ?>" alt="<?php echo esc_attr( $demo['name'] ); ?>" style="width:100%;height:160px;object-fit:cover;" loading="lazy" />
					<?php endif; ?>
					<div style="padding:16px;">
						<h3 style="margin:0 0 8px;"><?php echo esc_html( $demo['name'] ); ?></h3>
						<p style="font-size:13px;color:#666;margin:0 0 14px;"><?php echo esc_html( $demo['description'] ?? '' ); ?></p>
						<div style="display:flex;gap:8px;">
							<?php if ( ! empty( $demo['preview_url'] ) ) : ?>
							<a href="<?php echo esc_url( $demo['preview_url'] ); ?>" target="_blank" rel="noopener" class="button">
								<?php esc_html_e( 'Preview', 'starter-flavor-addons' ); ?>
							</a>
							<?php endif; ?>
							<button class="button button-primary sfa-import-btn"
								data-demo="<?php echo esc_attr( $demo['id'] ); ?>"
								data-nonce="<?php echo wp_create_nonce( 'sfa_import_' . $demo['id'] ); ?>">
								<?php esc_html_e( 'Import', 'starter-flavor-addons' ); ?>
							</button>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<script>
		document.querySelectorAll('.sfa-import-btn').forEach(function(btn){
			btn.addEventListener('click', function(){
				if(!confirm('<?php echo esc_js( __( 'Import this demo? Existing content will not be removed.', 'starter-flavor-addons' ) ); ?>')) return;
				btn.disabled = true;
				btn.textContent = '<?php echo esc_js( __( 'Importing…', 'starter-flavor-addons' ) ); ?>';
				fetch(ajaxurl, {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: new URLSearchParams({
						action: 'sfa_import_demo',
						demo: btn.dataset.demo,
						_ajax_nonce: btn.dataset.nonce,
					})
				}).then(r => r.json()).then(function(res){
					if(res.success){
						btn.textContent = '<?php echo esc_js( __( '✓ Done', 'starter-flavor-addons' ) ); ?>';
						btn.style.background = '#00a32a';
					} else {
						btn.textContent = '<?php echo esc_js( __( 'Failed', 'starter-flavor-addons' ) ); ?>';
						btn.disabled = false;
						alert(res.data || '<?php echo esc_js( __( 'Import failed.', 'starter-flavor-addons' ) ); ?>');
					}
				});
			});
		});
		</script>
		<?php
	}

	/** Return available demos from theme's demo directory. */
	private function get_available_demos(): array {
		$demos     = array();
		$theme_dir = get_template_directory();
		$demo_dirs = glob( $theme_dir . '/demos/*', GLOB_ONLYDIR );

		if ( ! $demo_dirs ) return $demos;

		foreach ( $demo_dirs as $dir ) {
			$manifest = $dir . '/manifest.json';
			if ( ! file_exists( $manifest ) ) continue;

			$data = json_decode( file_get_contents( $manifest ), true );
			if ( ! $data ) continue;

			$data['id'] = basename( $dir );
			$data['preview_image'] = file_exists( $dir . '/preview.jpg' )
				? get_template_directory_uri() . '/demos/' . $data['id'] . '/preview.jpg'
				: '';
			$demos[] = $data;
		}

		return $demos;
	}

	public function ajax_get_demos(): void {
		check_ajax_referer( 'sfa_nonce' );
		wp_send_json_success( $this->get_available_demos() );
	}

	public function ajax_import_demo(): void {
		$demo_id = sanitize_key( $_POST['demo'] ?? '' );
		check_ajax_referer( 'sfa_import_' . $demo_id );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'starter-flavor-addons' ) );
		}

		$demo_dir = get_template_directory() . '/demos/' . $demo_id;
		if ( ! is_dir( $demo_dir ) ) {
			wp_send_json_error( __( 'Demo not found.', 'starter-flavor-addons' ) );
		}

		// Import Elementor JSON templates
		$templates_file = $demo_dir . '/elementor-templates.json';
		if ( file_exists( $templates_file ) ) {
			$this->import_elementor_templates( $templates_file );
		}

		// Import WordPress pages (if XML exists)
		$pages_file = $demo_dir . '/pages.xml';
		if ( file_exists( $pages_file ) ) {
			$this->import_wordpress_content( $pages_file );
		}

		// Apply skin if specified in manifest
		$manifest_file = $demo_dir . '/manifest.json';
		if ( file_exists( $manifest_file ) ) {
			$manifest = json_decode( file_get_contents( $manifest_file ), true );
			if ( ! empty( $manifest['skin'] ) ) {
				set_theme_mod( 'sf_active_skin', $manifest['skin'] );
				do_action( 'sf_skin_switched', $manifest['skin'], '' );
			}
		}

		wp_send_json_success( array( 'message' => __( 'Demo imported successfully.', 'starter-flavor-addons' ) ) );
	}

	private function import_elementor_templates( string $file ): void {
		if ( ! class_exists( '\Elementor\Plugin' ) ) return;

		$data = json_decode( file_get_contents( $file ), true );
		if ( empty( $data ) || ! is_array( $data ) ) return;

		foreach ( $data as $template ) {
			if ( empty( $template['title'] ) || empty( $template['content'] ) ) continue;

			// Check if already exists
			$existing = get_posts( array(
				'post_type'   => 'elementor_library',
				'title'       => $template['title'],
				'post_status' => 'publish',
				'numberposts' => 1,
			) );
			if ( $existing ) continue;

			$post_id = wp_insert_post( array(
				'post_title'  => sanitize_text_field( $template['title'] ),
				'post_status' => 'publish',
				'post_type'   => 'elementor_library',
			) );
			if ( is_wp_error( $post_id ) ) continue;

			update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $template['content'] ) ) );
			update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
			if ( ! empty( $template['type'] ) ) {
				wp_set_object_terms( $post_id, $template['type'], 'elementor_library_type' );
			}
		}
	}

	private function import_wordpress_content( string $file ): void {
		if ( ! function_exists( 'wp_import_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/import.php';
		}
		// WordPress Importer is needed for XML — trigger graceful skip if not available
		if ( ! class_exists( 'WP_Import' ) ) return;

		$importer = new WP_Import();
		$importer->fetch_attachments = true;
		ob_start();
		$importer->import( $file );
		ob_end_clean();
	}
}

// Init demo importer on admin
add_action( 'admin_init', array( SFA_Demo_Importer::instance(), '__construct' ), 5 );
