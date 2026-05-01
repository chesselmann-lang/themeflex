<?php
/**
 * Plugin Name:       Starter Flavor Addons
 * Plugin URI:        https://starterflavor.com/addons
 * Description:       The essential companion plugin for the Starter Flavor theme. Adds 10+ Elementor widgets, custom post types, a one-click demo importer, and advanced theme options.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Starter Flavor
 * Author URI:        https://starterflavor.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sf-addons
 * Domain Path:       /languages
 *
 * @package StarterFlavorAddons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'SF_ADDONS_VERSION',   '1.0.0' );
define( 'SF_ADDONS_FILE',      __FILE__ );
define( 'SF_ADDONS_DIR',       plugin_dir_path( __FILE__ ) );
define( 'SF_ADDONS_URL',       plugin_dir_url( __FILE__ ) );
define( 'SF_ADDONS_DEMO_DIR',  SF_ADDONS_DIR . 'demo-packs/' );

/**
 * Main plugin class – loaded once via singleton.
 */
final class Starter_Flavor_Addons {

	/** @var self */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup();
		}
		return self::$instance;
	}

	private function __construct() {}

	private function setup(): void {
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_action( 'plugins_loaded', [ $this, 'load_modules' ] );
		add_action( 'admin_notices',  [ $this, 'check_requirements' ] );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'sf-addons',
			false,
			dirname( plugin_basename( SF_ADDONS_FILE ) ) . '/languages'
		);
	}

	public function load_modules(): void {
		// Core modules – always active
		require_once SF_ADDONS_DIR . 'includes/class-post-types.php';
		require_once SF_ADDONS_DIR . 'includes/class-demo-importer.php';
		require_once SF_ADDONS_DIR . 'includes/class-assets.php';

		// Admin modules
		if ( is_admin() ) {
			require_once SF_ADDONS_DIR . 'includes/admin/class-admin.php';
			SF_Addons_Admin::instance();
		}

		// Elementor integration
		if ( did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) ) {
			add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		} else {
			add_action( 'elementor/loaded', function() {
				add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
			});
		}

		// Init all modules
		SF_Addons_Post_Types::instance();
		SF_Addons_Demo_Importer::instance();
		SF_Addons_Assets::instance();
	}

	public function register_widgets( $widgets_manager ): void {
		$widgets_dir = SF_ADDONS_DIR . 'includes/elementor-widgets/';
		$widgets = [
			'portfolio-grid'       => 'SF_Widget_Portfolio_Grid',
			'team-members'         => 'SF_Widget_Team_Members',
			'testimonials-slider'  => 'SF_Widget_Testimonials_Slider',
			'pricing-table'        => 'SF_Widget_Pricing_Table',
			'faq-accordion'        => 'SF_Widget_FAQ_Accordion',
			'stats-counter'        => 'SF_Widget_Stats_Counter',
			'timeline'             => 'SF_Widget_Timeline',
			'logo-carousel'        => 'SF_Widget_Logo_Carousel',
			'icon-box'             => 'SF_Widget_Icon_Box',
			'map-embed'            => 'SF_Widget_Map_Embed',
		];

		foreach ( $widgets as $file => $class ) {
			$path = $widgets_dir . $file . '.php';
			if ( file_exists( $path ) ) {
				require_once $path;
				if ( class_exists( $class ) ) {
					$widgets_manager->register( new $class() );
				}
			}
		}
	}

	/**
	 * Show admin notice if required theme is not active.
	 */
	public function check_requirements(): void {
		$theme = wp_get_theme();
		$is_sf  = ( 'starter-flavor' === $theme->get_template() );

		if ( ! $is_sf ) {
			echo '<div class="notice notice-warning is-dismissible"><p>';
			printf(
				/* translators: %s: theme name link */
				esc_html__( 'Starter Flavor Addons works best with the %s theme. Please install and activate it.', 'sf-addons' ),
				'<a href="' . esc_url( 'https://starterflavor.com' ) . '">Starter Flavor</a>'
			);
			echo '</p></div>';
		}
	}
}

// Kick off
add_action( 'plugins_loaded', function() {
	Starter_Flavor_Addons::instance();
}, 5 );
