<?php
/**
 * Assets – Enqueue widget CSS/JS on frontend
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class SF_Addons_Assets {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function init(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_assets' ] );

		// Register Elementor widget category
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_widget_category' ] );
	}

	public function register_assets(): void {
		wp_register_style(
			'sf-addons-widgets',
			SF_ADDONS_URL . 'assets/css/widgets.css',
			[],
			SF_ADDONS_VERSION
		);

		wp_register_script(
			'sf-addons-widgets',
			SF_ADDONS_URL . 'assets/js/widgets.js',
			[],
			SF_ADDONS_VERSION,
			true
		);
	}

	public function add_widget_category( $elements_manager ): void {
		$elements_manager->add_category( 'starter-flavor', [
			'title' => esc_html__( 'Starter Flavor', 'sf-addons' ),
			'icon'  => 'fa fa-plug',
		]);
	}
}
