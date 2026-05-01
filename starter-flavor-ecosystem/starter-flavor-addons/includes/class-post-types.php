<?php
/**
 * Custom Post Types: Portfolio, Team, Testimonial, FAQ, Pricing Plan
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class SF_Addons_Post_Types {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function init(): void {
		add_action( 'init', [ $this, 'register_post_types' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_meta_boxes' ] );
	}

	public function register_post_types(): void {

		// ── Portfolio ──────────────────────────────────────────────────────────
		register_post_type( 'sf_portfolio', [
			'labels' => [
				'name'          => esc_html__( 'Portfolio', 'sf-addons' ),
				'singular_name' => esc_html__( 'Portfolio Item', 'sf-addons' ),
				'add_new_item'  => esc_html__( 'Add New Portfolio Item', 'sf-addons' ),
				'edit_item'     => esc_html__( 'Edit Portfolio Item', 'sf-addons' ),
			],
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-portfolio',
			'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'show_in_rest'  => true,
			'rewrite'       => [ 'slug' => 'portfolio', 'with_front' => false ],
		]);

		// ── Team Member ────────────────────────────────────────────────────────
		register_post_type( 'sf_team', [
			'labels' => [
				'name'          => esc_html__( 'Team', 'sf-addons' ),
				'singular_name' => esc_html__( 'Team Member', 'sf-addons' ),
				'add_new_item'  => esc_html__( 'Add New Team Member', 'sf-addons' ),
				'edit_item'     => esc_html__( 'Edit Team Member', 'sf-addons' ),
			],
			'public'       => true,
			'has_archive'  => false,
			'menu_icon'    => 'dashicons-groups',
			'supports'     => [ 'title', 'editor', 'thumbnail' ],
			'show_in_rest' => true,
		]);

		// ── Testimonial ────────────────────────────────────────────────────────
		register_post_type( 'sf_testimonial', [
			'labels' => [
				'name'          => esc_html__( 'Testimonials', 'sf-addons' ),
				'singular_name' => esc_html__( 'Testimonial', 'sf-addons' ),
				'add_new_item'  => esc_html__( 'Add New Testimonial', 'sf-addons' ),
			],
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-format-quote',
			'supports'     => [ 'title', 'editor', 'thumbnail' ],
			'show_in_rest' => true,
		]);

		// ── FAQ ────────────────────────────────────────────────────────────────
		register_post_type( 'sf_faq', [
			'labels' => [
				'name'          => esc_html__( 'FAQs', 'sf-addons' ),
				'singular_name' => esc_html__( 'FAQ', 'sf-addons' ),
				'add_new_item'  => esc_html__( 'Add New FAQ', 'sf-addons' ),
			],
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-editor-help',
			'supports'     => [ 'title', 'editor' ],
			'show_in_rest' => true,
		]);

		// ── Pricing Plan ───────────────────────────────────────────────────────
		register_post_type( 'sf_pricing', [
			'labels' => [
				'name'          => esc_html__( 'Pricing Plans', 'sf-addons' ),
				'singular_name' => esc_html__( 'Pricing Plan', 'sf-addons' ),
				'add_new_item'  => esc_html__( 'Add New Pricing Plan', 'sf-addons' ),
			],
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-tag',
			'supports'     => [ 'title' ],
			'show_in_rest' => true,
		]);
	}

	public function register_taxonomies(): void {
		// Portfolio Categories
		register_taxonomy( 'sf_portfolio_cat', 'sf_portfolio', [
			'labels' => [
				'name'          => esc_html__( 'Portfolio Categories', 'sf-addons' ),
				'singular_name' => esc_html__( 'Portfolio Category', 'sf-addons' ),
			],
			'hierarchical'  => true,
			'show_in_rest'  => true,
			'rewrite'       => [ 'slug' => 'portfolio-category' ],
		]);

		// Team Department
		register_taxonomy( 'sf_team_dept', 'sf_team', [
			'labels' => [
				'name'          => esc_html__( 'Departments', 'sf-addons' ),
				'singular_name' => esc_html__( 'Department', 'sf-addons' ),
			],
			'hierarchical'  => true,
			'show_in_rest'  => true,
		]);

		// FAQ Category
		register_taxonomy( 'sf_faq_cat', 'sf_faq', [
			'labels' => [
				'name'          => esc_html__( 'FAQ Categories', 'sf-addons' ),
				'singular_name' => esc_html__( 'FAQ Category', 'sf-addons' ),
			],
			'hierarchical'  => true,
			'show_in_rest'  => true,
		]);
	}

	public function add_meta_boxes(): void {
		add_meta_box(
			'sf_team_meta',
			esc_html__( 'Team Member Details', 'sf-addons' ),
			[ $this, 'render_team_meta' ],
			'sf_team',
			'normal',
			'high'
		);
		add_meta_box(
			'sf_testimonial_meta',
			esc_html__( 'Testimonial Details', 'sf-addons' ),
			[ $this, 'render_testimonial_meta' ],
			'sf_testimonial',
			'normal',
			'high'
		);
		add_meta_box(
			'sf_pricing_meta',
			esc_html__( 'Pricing Plan Details', 'sf-addons' ),
			[ $this, 'render_pricing_meta' ],
			'sf_pricing',
			'normal',
			'high'
		);
		add_meta_box(
			'sf_portfolio_meta',
			esc_html__( 'Portfolio Details', 'sf-addons' ),
			[ $this, 'render_portfolio_meta' ],
			'sf_portfolio',
			'normal',
			'high'
		);
	}

	public function render_team_meta( $post ): void {
		wp_nonce_field( 'sf_team_meta_nonce', 'sf_team_nonce' );
		$fields = [
			'sf_team_role'      => esc_html__( 'Role / Position', 'sf-addons' ),
			'sf_team_email'     => esc_html__( 'Email', 'sf-addons' ),
			'sf_team_phone'     => esc_html__( 'Phone', 'sf-addons' ),
			'sf_team_linkedin'  => esc_html__( 'LinkedIn URL', 'sf-addons' ),
			'sf_team_twitter'   => esc_html__( 'Twitter URL', 'sf-addons' ),
			'sf_team_github'    => esc_html__( 'GitHub URL', 'sf-addons' ),
		];
		echo '<table class="form-table">';
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, $key, true );
			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
			echo '<td><input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="large-text"></td></tr>';
		}
		echo '</table>';
	}

	public function render_testimonial_meta( $post ): void {
		wp_nonce_field( 'sf_testimonial_meta_nonce', 'sf_testimonial_nonce' );
		$fields = [
			'sf_testimonial_author'   => esc_html__( 'Author Name', 'sf-addons' ),
			'sf_testimonial_position' => esc_html__( 'Position / Company', 'sf-addons' ),
			'sf_testimonial_rating'   => esc_html__( 'Rating (1–5)', 'sf-addons' ),
			'sf_testimonial_url'      => esc_html__( 'Website URL', 'sf-addons' ),
		];
		echo '<table class="form-table">';
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, $key, true );
			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
			echo '<td><input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="large-text"></td></tr>';
		}
		echo '</table>';
	}

	public function render_pricing_meta( $post ): void {
		wp_nonce_field( 'sf_pricing_meta_nonce', 'sf_pricing_nonce' );
		$price    = get_post_meta( $post->ID, 'sf_pricing_price', true );
		$period   = get_post_meta( $post->ID, 'sf_pricing_period', true );
		$features = get_post_meta( $post->ID, 'sf_pricing_features', true );
		$btn_text = get_post_meta( $post->ID, 'sf_pricing_btn_text', true );
		$btn_url  = get_post_meta( $post->ID, 'sf_pricing_btn_url', true );
		$featured = get_post_meta( $post->ID, 'sf_pricing_featured', true );
		?>
		<table class="form-table">
			<tr><th><?php esc_html_e( 'Price', 'sf-addons' ); ?></th>
				<td><input type="text" name="sf_pricing_price" value="<?php echo esc_attr( $price ); ?>" class="regular-text"></td></tr>
			<tr><th><?php esc_html_e( 'Period (e.g. / month)', 'sf-addons' ); ?></th>
				<td><input type="text" name="sf_pricing_period" value="<?php echo esc_attr( $period ); ?>" class="regular-text"></td></tr>
			<tr><th><?php esc_html_e( 'Features (one per line)', 'sf-addons' ); ?></th>
				<td><textarea name="sf_pricing_features" rows="8" class="large-text"><?php echo esc_textarea( $features ); ?></textarea></td></tr>
			<tr><th><?php esc_html_e( 'Button Text', 'sf-addons' ); ?></th>
				<td><input type="text" name="sf_pricing_btn_text" value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text"></td></tr>
			<tr><th><?php esc_html_e( 'Button URL', 'sf-addons' ); ?></th>
				<td><input type="url" name="sf_pricing_btn_url" value="<?php echo esc_attr( $btn_url ); ?>" class="large-text"></td></tr>
			<tr><th><?php esc_html_e( 'Featured Plan?', 'sf-addons' ); ?></th>
				<td><input type="checkbox" name="sf_pricing_featured" value="1" <?php checked( $featured, '1' ); ?>></td></tr>
		</table>
		<?php
	}

	public function render_portfolio_meta( $post ): void {
		wp_nonce_field( 'sf_portfolio_meta_nonce', 'sf_portfolio_nonce' );
		$fields = [
			'sf_portfolio_client'      => esc_html__( 'Client', 'sf-addons' ),
			'sf_portfolio_year'        => esc_html__( 'Year', 'sf-addons' ),
			'sf_portfolio_url'         => esc_html__( 'Project URL', 'sf-addons' ),
			'sf_portfolio_services'    => esc_html__( 'Services (comma-separated)', 'sf-addons' ),
		];
		echo '<table class="form-table">';
		foreach ( $fields as $key => $label ) {
			$value = get_post_meta( $post->ID, $key, true );
			echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
			echo '<td><input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="large-text"></td></tr>';
		}
		echo '</table>';
	}

	public function save_meta_boxes( int $post_id ): void {
		// Team
		if ( isset( $_POST['sf_team_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sf_team_nonce'] ) ), 'sf_team_meta_nonce' ) ) {
			$team_fields = [ 'sf_team_role', 'sf_team_email', 'sf_team_phone', 'sf_team_linkedin', 'sf_team_twitter', 'sf_team_github' ];
			foreach ( $team_fields as $field ) {
				if ( isset( $_POST[ $field ] ) ) {
					update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
				}
			}
		}
		// Testimonial
		if ( isset( $_POST['sf_testimonial_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sf_testimonial_nonce'] ) ), 'sf_testimonial_meta_nonce' ) ) {
			$t_fields = [ 'sf_testimonial_author', 'sf_testimonial_position', 'sf_testimonial_rating', 'sf_testimonial_url' ];
			foreach ( $t_fields as $field ) {
				if ( isset( $_POST[ $field ] ) ) {
					update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
				}
			}
		}
		// Pricing
		if ( isset( $_POST['sf_pricing_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sf_pricing_nonce'] ) ), 'sf_pricing_meta_nonce' ) ) {
			$p_fields = [ 'sf_pricing_price', 'sf_pricing_period', 'sf_pricing_btn_text', 'sf_pricing_btn_url' ];
			foreach ( $p_fields as $field ) {
				if ( isset( $_POST[ $field ] ) ) {
					update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
				}
			}
			if ( isset( $_POST['sf_pricing_features'] ) ) {
				update_post_meta( $post_id, 'sf_pricing_features', sanitize_textarea_field( wp_unslash( $_POST['sf_pricing_features'] ) ) );
			}
			update_post_meta( $post_id, 'sf_pricing_featured', isset( $_POST['sf_pricing_featured'] ) ? '1' : '0' );
		}
		// Portfolio
		if ( isset( $_POST['sf_portfolio_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sf_portfolio_nonce'] ) ), 'sf_portfolio_meta_nonce' ) ) {
			$port_fields = [ 'sf_portfolio_client', 'sf_portfolio_year', 'sf_portfolio_url', 'sf_portfolio_services' ];
			foreach ( $port_fields as $field ) {
				if ( isset( $_POST[ $field ] ) ) {
					update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
				}
			}
		}
	}
}
