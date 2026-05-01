<?php
/**
 * One-Click Demo Importer
 * Handles AJAX demo installation with live progress feedback.
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class SF_Addons_Demo_Importer {

	private static $instance = null;

	/** Demo pack definitions */
	private array $demos = [];

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function init(): void {
		add_action( 'init',             [ $this, 'load_demos' ] );
		add_action( 'wp_ajax_sf_demo_install', [ $this, 'ajax_install' ] );
		add_action( 'wp_ajax_sf_demo_status',  [ $this, 'ajax_status' ] );
	}

	public function load_demos(): void {
		$demo_dirs = glob( SF_ADDONS_DEMO_DIR . '*/demo.json' );
		if ( empty( $demo_dirs ) ) return;

		foreach ( $demo_dirs as $json_file ) {
			$data = json_decode( file_get_contents( $json_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( $data && isset( $data['slug'] ) ) {
				$data['dir'] = dirname( $json_file ) . '/';
				$this->demos[ $data['slug'] ] = $data;
			}
		}
	}

	public function get_demos(): array {
		return $this->demos;
	}

	public function ajax_install(): void {
		check_ajax_referer( 'sf_demo_import', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'sf-addons' ) ] );
		}

		$slug = isset( $_POST['demo'] ) ? sanitize_key( $_POST['demo'] ) : '';
		if ( empty( $slug ) || ! isset( $this->demos[ $slug ] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid demo pack.', 'sf-addons' ) ] );
		}

		$demo = $this->demos[ $slug ];
		$steps = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 0;

		switch ( $steps ) {
			case 0:
				// Step 1: Install required plugins
				$result = $this->install_plugins( $demo );
				wp_send_json_success( [
					'step'    => 1,
					'message' => esc_html__( 'Required plugins ready.', 'sf-addons' ),
					'percent' => 15,
				]);
				break;

			case 1:
				// Step 2: Import content via WXR
				$result = $this->import_content( $demo );
				wp_send_json_success( [
					'step'    => 2,
					'message' => esc_html__( 'Content imported.', 'sf-addons' ),
					'percent' => 45,
				]);
				break;

			case 2:
				// Step 3: Import Customizer settings
				$result = $this->import_customizer( $demo );
				wp_send_json_success( [
					'step'    => 3,
					'message' => esc_html__( 'Customizer settings applied.', 'sf-addons' ),
					'percent' => 65,
				]);
				break;

			case 3:
				// Step 4: Import Elementor templates
				$result = $this->import_elementor( $demo );
				wp_send_json_success( [
					'step'    => 4,
					'message' => esc_html__( 'Page builder templates imported.', 'sf-addons' ),
					'percent' => 80,
				]);
				break;

			case 4:
				// Step 5: Set menus, homepage, etc.
				$result = $this->set_homepage_and_menus( $demo );
				wp_send_json_success( [
					'step'    => 5,
					'message' => esc_html__( 'Reading options set.', 'sf-addons' ),
					'percent' => 95,
				]);
				break;

			case 5:
				// Done
				wp_send_json_success( [
					'step'    => 'done',
					'message' => esc_html__( 'Demo installed successfully! Enjoy your new site.', 'sf-addons' ),
					'percent' => 100,
					'url'     => home_url( '/' ),
				]);
				break;

			default:
				wp_send_json_error( [ 'message' => esc_html__( 'Unknown step.', 'sf-addons' ) ] );
		}
	}

	private function install_plugins( array $demo ): bool {
		if ( empty( $demo['required_plugins'] ) ) return true;

		foreach ( $demo['required_plugins'] as $plugin_slug ) {
			// Check if plugin is already active
			if ( is_plugin_active( $plugin_slug . '/' . $plugin_slug . '.php' ) ) continue;

			// Attempt to activate if installed
			$plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
				activate_plugin( $plugin_file );
			}
			// Note: For non-installed plugins, we rely on TGM Plugin Activation or user pre-installs
		}
		return true;
	}

	private function import_content( array $demo ): bool {
		$xml_file = $demo['dir'] . 'content.xml';
		if ( ! file_exists( $xml_file ) ) return false;

		// Use WordPress importer if available
		if ( class_exists( 'WP_Import' ) ) {
			$importer = new WP_Import();
			$importer->fetch_attachments = true;
			ob_start();
			$importer->import( $xml_file );
			ob_end_clean();
			return true;
		}

		// Fallback: basic WXR parsing
		$this->basic_wxr_import( $xml_file );
		return true;
	}

	private function basic_wxr_import( string $xml_file ): void {
		// Lightweight WXR importer for pages and posts (no media)
		$xml = simplexml_load_file( $xml_file );
		if ( ! $xml ) return;

		$xml->registerXPathNamespace( 'wp',      'http://wordpress.org/export/1.2/' );
		$xml->registerXPathNamespace( 'content', 'http://purl.org/rss/1.0/modules/content/' );

		$items = $xml->channel->item ?? [];
		foreach ( $items as $item ) {
			$ns      = $item->children( 'http://wordpress.org/export/1.2/' );
			$content = $item->children( 'http://purl.org/rss/1.0/modules/content/' );

			$post_type   = (string) $ns->post_type;
			$post_status = (string) $ns->status;
			$title       = (string) $item->title;
			$body        = (string) $content->encoded;
			$slug        = (string) $ns->post_name;

			if ( ! in_array( $post_type, [ 'post', 'page' ], true ) ) continue;

			// Skip if already exists
			if ( get_page_by_path( $slug, OBJECT, $post_type ) ) continue;

			wp_insert_post( [
				'post_title'   => sanitize_text_field( $title ),
				'post_content' => wp_kses_post( $body ),
				'post_name'    => $slug,
				'post_type'    => $post_type,
				'post_status'  => 'publish' === $post_status ? 'publish' : 'draft',
			] );
		}
	}

	private function import_customizer( array $demo ): bool {
		$dat_file = $demo['dir'] . 'customizer.dat';
		if ( ! file_exists( $dat_file ) ) return false;

		$data = json_decode( file_get_contents( $dat_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( empty( $data ) ) return false;

		foreach ( $data as $key => $value ) {
			set_theme_mod( sanitize_key( $key ), $value );
		}
		return true;
	}

	private function import_elementor( array $demo ): bool {
		$json_file = $demo['dir'] . 'elementor-templates.json';
		if ( ! file_exists( $json_file ) ) return false;

		if ( ! class_exists( '\Elementor\Plugin' ) ) return false;

		$templates = json_decode( file_get_contents( $json_file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		if ( empty( $templates ) ) return false;

		foreach ( $templates as $template ) {
			$post_id = wp_insert_post( [
				'post_title'  => sanitize_text_field( $template['title'] ?? 'Template' ),
				'post_type'   => 'elementor_library',
				'post_status' => 'publish',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $template['content'] ?? [] ) ) );
				update_post_meta( $post_id, '_elementor_template_type', sanitize_text_field( $template['type'] ?? 'page' ) );
				update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
			}
		}
		return true;
	}

	private function set_homepage_and_menus( array $demo ): bool {
		// Set static front page
		if ( ! empty( $demo['homepage_slug'] ) ) {
			$homepage = get_page_by_path( $demo['homepage_slug'] );
			if ( $homepage ) {
				update_option( 'page_on_front', $homepage->ID );
				update_option( 'show_on_front', 'page' );
			}
		}

		// Set blog page
		if ( ! empty( $demo['blog_slug'] ) ) {
			$blog_page = get_page_by_path( $demo['blog_slug'] );
			if ( $blog_page ) {
				update_option( 'page_for_posts', $blog_page->ID );
			}
		}

		// Assign nav menus
		if ( ! empty( $demo['nav_menus'] ) ) {
			$locations = get_registered_nav_menus();
			foreach ( $demo['nav_menus'] as $location => $menu_name ) {
				$menu = get_term_by( 'name', $menu_name, 'nav_menu' );
				if ( $menu ) {
					$menus[ $location ] = $menu->term_id;
				}
			}
			if ( ! empty( $menus ) ) {
				set_theme_mod( 'nav_menu_locations', $menus );
			}
		}

		return true;
	}

	public function ajax_status(): void {
		check_ajax_referer( 'sf_demo_import', 'nonce' );
		wp_send_json_success( [
			'demos' => array_map( function( $demo ) {
				return [
					'slug'       => $demo['slug'],
					'name'       => $demo['name'],
					'category'   => $demo['category'] ?? '',
					'screenshot' => $demo['dir'] ? SF_ADDONS_URL . 'demo-packs/' . $demo['slug'] . '/screenshot.jpg' : '',
					'installed'  => get_option( 'sf_demo_installed_' . $demo['slug'], false ),
				];
			}, $this->demos ),
		]);
	}
}
