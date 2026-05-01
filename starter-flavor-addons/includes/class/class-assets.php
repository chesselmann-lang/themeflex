<?php
/**
 * Asset Management
 *
 * @package Starter_Flavor_Addons
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SFA_Assets {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function enqueue_frontend(): void {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			wp_enqueue_style(
				'sfa-widgets',
				SFA_ASSETS_URL . 'css/widgets.css',
				array(),
				SFA_VERSION
			);
			wp_enqueue_script(
				'sfa-widgets',
				SFA_ASSETS_URL . 'js/widgets.js',
				array(),
				SFA_VERSION,
				true
			);
			wp_localize_script( 'sfa-widgets', 'sfaData', array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'sfa_nonce' ),
				'i18n'    => array(
					'loading' => esc_html__( 'Loading…', 'starter-flavor-addons' ),
					'error'   => esc_html__( 'Something went wrong.', 'starter-flavor-addons' ),
				),
			) );
		}
	}

	public function enqueue_editor(): void {
		wp_enqueue_style(
			'sfa-editor',
			SFA_ASSETS_URL . 'css/editor.css',
			array(),
			SFA_VERSION
		);
	}
}
