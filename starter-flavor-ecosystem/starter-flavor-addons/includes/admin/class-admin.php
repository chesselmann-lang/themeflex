<?php
/**
 * Admin Panel – Dashboard, Demo Importer UI, Settings
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class SF_Addons_Admin {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function init(): void {
		add_action( 'admin_menu',             [ $this, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts',  [ $this, 'enqueue_assets' ] );
	}

	public function add_menu(): void {
		add_menu_page(
			esc_html__( 'Starter Flavor', 'sf-addons' ),
			esc_html__( 'Starter Flavor', 'sf-addons' ),
			'manage_options',
			'sf-addons',
			[ $this, 'render_dashboard' ],
			'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#a7aaad" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>' ),
			59
		);

		add_submenu_page(
			'sf-addons',
			esc_html__( 'Dashboard', 'sf-addons' ),
			esc_html__( 'Dashboard', 'sf-addons' ),
			'manage_options',
			'sf-addons',
			[ $this, 'render_dashboard' ]
		);

		add_submenu_page(
			'sf-addons',
			esc_html__( 'Demo Importer', 'sf-addons' ),
			esc_html__( 'Demo Importer', 'sf-addons' ),
			'manage_options',
			'sf-demo-importer',
			[ $this, 'render_demo_importer' ]
		);

		add_submenu_page(
			'sf-addons',
			esc_html__( 'Settings', 'sf-addons' ),
			esc_html__( 'Settings', 'sf-addons' ),
			'manage_options',
			'sf-settings',
			[ $this, 'render_settings' ]
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, 'sf-' ) === false && strpos( $hook, 'starter-flavor' ) === false ) return;

		wp_enqueue_style(
			'sf-addons-admin',
			SF_ADDONS_URL . 'assets/css/admin.css',
			[],
			SF_ADDONS_VERSION
		);

		wp_enqueue_script(
			'sf-addons-admin',
			SF_ADDONS_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			SF_ADDONS_VERSION,
			true
		);

		wp_localize_script( 'sf-addons-admin', 'sfAddons', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'sf_demo_import' ),
			'i18n'    => [
				'installing'  => esc_html__( 'Installing…', 'sf-addons' ),
				'done'        => esc_html__( 'Done!', 'sf-addons' ),
				'error'       => esc_html__( 'Something went wrong. Please try again.', 'sf-addons' ),
				'confirmText' => esc_html__( 'This will overwrite your current content. Are you sure?', 'sf-addons' ),
			],
		]);
	}

	public function render_dashboard(): void {
		$importer = SF_Addons_Demo_Importer::instance();
		$demos    = $importer->get_demos();
		?>
		<div class="wrap sf-addons-wrap">
			<div class="sf-addons-header">
				<h1><?php esc_html_e( 'Starter Flavor Addons', 'sf-addons' ); ?></h1>
				<p><?php esc_html_e( 'Welcome! Choose a demo to get started or manage your settings below.', 'sf-addons' ); ?></p>
			</div>

			<div class="sf-addons-cards">
				<div class="sf-card sf-card--highlight">
					<span class="dashicons dashicons-superhero-alt"></span>
					<h3><?php esc_html_e( 'Demo Importer', 'sf-addons' ); ?></h3>
					<p><?php printf( esc_html__( '%d demo packs available', 'sf-addons' ), count( $demos ) ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=sf-demo-importer' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Browse Demos', 'sf-addons' ); ?>
					</a>
				</div>

				<div class="sf-card">
					<span class="dashicons dashicons-portfolio"></span>
					<h3><?php esc_html_e( 'Portfolio', 'sf-addons' ); ?></h3>
					<p><?php echo esc_html( wp_count_posts( 'sf_portfolio' )->publish ) . ' ' . esc_html__( 'items', 'sf-addons' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sf_portfolio' ) ); ?>" class="button">
						<?php esc_html_e( 'Manage', 'sf-addons' ); ?>
					</a>
				</div>

				<div class="sf-card">
					<span class="dashicons dashicons-groups"></span>
					<h3><?php esc_html_e( 'Team', 'sf-addons' ); ?></h3>
					<p><?php echo esc_html( wp_count_posts( 'sf_team' )->publish ) . ' ' . esc_html__( 'members', 'sf-addons' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sf_team' ) ); ?>" class="button">
						<?php esc_html_e( 'Manage', 'sf-addons' ); ?>
					</a>
				</div>

				<div class="sf-card">
					<span class="dashicons dashicons-format-quote"></span>
					<h3><?php esc_html_e( 'Testimonials', 'sf-addons' ); ?></h3>
					<p><?php echo esc_html( wp_count_posts( 'sf_testimonial' )->publish ) . ' ' . esc_html__( 'reviews', 'sf-addons' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sf_testimonial' ) ); ?>" class="button">
						<?php esc_html_e( 'Manage', 'sf-addons' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_demo_importer(): void {
		$importer = SF_Addons_Demo_Importer::instance();
		$demos    = $importer->get_demos();
		require SF_ADDONS_DIR . 'includes/admin/views/demo-importer.php';
	}

	public function render_settings(): void {
		require SF_ADDONS_DIR . 'includes/admin/views/settings.php';
	}
}
