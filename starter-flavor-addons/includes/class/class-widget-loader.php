<?php
/**
 * Elementor Widget Loader
 *
 * Discovers and registers all widgets from the theme's elementor-widgets/
 * directory plus any widgets bundled directly in this plugin.
 *
 * @package Starter_Flavor_Addons
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SFA_Widget_Loader {

	private static ?self $instance = null;

	/** Map: file-basename → class name */
	private array $widget_map = [];

	public static function instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'elementor/widgets/register',          array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
	}

	/**
	 * Register the "Starter Flavor" widget category.
	 */
	public function register_category(): void {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) return;

		\Elementor\Plugin::$instance->elements_manager->add_category(
			'starter-flavor',
			array(
				'title' => esc_html__( 'Starter Flavor', 'starter-flavor-addons' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * Load all widget files and register with Elementor.
	 */
	public function register_widgets( $widgets_manager ): void {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) return;

		$this->build_widget_map();

		foreach ( $this->widget_map as $file => $class ) {
			if ( ! file_exists( $file ) ) continue;

			require_once $file;

			if ( class_exists( $class ) ) {
				try {
					$widgets_manager->register( new $class() );
				} catch ( \Throwable $e ) {
					// Log quietly; never crash the whole admin.
					error_log( "SFA Widget Loader: failed to register {$class} — " . $e->getMessage() );
				}
			}
		}
	}

	/**
	 * Build the map of widget file → class name.
	 * Sources (in priority order):
	 *   1. Plugin-local widgets:  starter-flavor-addons/includes/widgets/
	 *   2. Theme widgets:         starter-flavor/includes/elementor-widgets/
	 */
	private function build_widget_map(): void {
		$this->widget_map = [];

		// ── Source 1: Plugin-bundled widgets ──
		$plugin_widget_dir = SFA_DIR . 'includes/widgets/';
		if ( is_dir( $plugin_widget_dir ) ) {
			foreach ( glob( $plugin_widget_dir . '*.php' ) as $file ) {
				$class = $this->file_to_class( $file, 'SFA_' );
				if ( $class ) {
					$this->widget_map[ $file ] = $class;
				}
			}
		}

		// ── Source 2: Theme widgets (fall back gracefully if theme not active) ──
		$theme_widget_dir = get_template_directory() . '/includes/elementor-widgets/';
		if ( is_dir( $theme_widget_dir ) ) {
			foreach ( glob( $theme_widget_dir . '*.php' ) as $file ) {
				// Extract class from file content (most reliable)
				$class = $this->extract_class_from_file( $file );
				if ( $class && ! isset( $this->widget_map[ $file ] ) ) {
					$this->widget_map[ $file ] = $class;
				}
			}
		}
	}

	/**
	 * Convert a filename to a class name.
	 * E.g. "my-widget.php" → "Prefix_My_Widget_Widget"
	 */
	private function file_to_class( string $file, string $prefix ): string {
		$base  = basename( $file, '.php' );
		$parts = array_map( 'ucfirst', explode( '-', $base ) );
		return $prefix . implode( '_', $parts ) . '_Widget';
	}

	/**
	 * Extract the first "class Foo extends" declaration from a file.
	 * Used for theme widgets that may have non-standard class names.
	 */
	private function extract_class_from_file( string $file ): string {
		static $cache = [];
		if ( isset( $cache[ $file ] ) ) return $cache[ $file ];

		$content = @file_get_contents( $file );
		if ( ! $content ) return $cache[ $file ] = '';

		if ( preg_match( '/^class\s+(Starter_Flavor_\S+Widget)\s+extends/m', $content, $m ) ) {
			return $cache[ $file ] = $m[1];
		}
		return $cache[ $file ] = '';
	}
}
