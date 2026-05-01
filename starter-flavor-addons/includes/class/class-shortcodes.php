<?php
/**
 * Shortcode Registration
 * Placeholder — shortcodes are registered here as the plugin grows.
 *
 * @package Starter_Flavor_Addons
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SFA_Shortcodes {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// [sfa_button], [sfa_icon], [sfa_price] etc. — to be added
		add_shortcode( 'sfa_button', array( $this, 'shortcode_button' ) );
	}

	public function shortcode_button( array $atts, string $content = '' ): string {
		$a = shortcode_atts( array(
			'url'    => '#',
			'text'   => esc_html__( 'Click here', 'starter-flavor-addons' ),
			'target' => '_self',
			'style'  => 'primary',
		), $atts, 'sfa_button' );

		$target = 'blank' === $a['target'] ? '_blank" rel="noopener noreferrer' : '_self';
		return sprintf(
			'<a href="%s" target="%s" class="sf-btn-primary sfa-btn sfa-btn--%s">%s</a>',
			esc_url( $a['url'] ),
			esc_attr( $target ),
			esc_attr( $a['style'] ),
			esc_html( $a['text'] )
		);
	}
}

add_action( 'init', array( SFA_Shortcodes::instance(), '__construct' ), 10 );
