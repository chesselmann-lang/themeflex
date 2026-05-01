<?php
/**
 * Plugin Name:       Starter Flavor Addons
 * Plugin URI:        https://whatsdigital.de/starter-flavor
 * Description:       Companion plugin for the Starter Flavor theme. Provides 51+ Elementor widgets, shortcodes, demo importer, and advanced functionality. Required for full theme features.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            WhatsDigital
 * Author URI:        https://whatsdigital.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       starter-flavor-addons
 * Domain Path:       /languages
 *
 * @package Starter_Flavor_Addons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ============================================================================
   CONSTANTS
   ============================================================================ */

define( 'SFA_VERSION',    '1.0.0' );
define( 'SFA_FILE',       __FILE__ );
define( 'SFA_DIR',        plugin_dir_path( __FILE__ ) );
define( 'SFA_URL',        plugin_dir_url( __FILE__ ) );
define( 'SFA_ASSETS_URL', SFA_URL . 'assets/' );
define( 'SFA_MIN_PHP',    '8.0' );
define( 'SFA_MIN_WP',     '6.0' );
define( 'SFA_MIN_ELM',    '3.0' );

/* ============================================================================
   REQUIREMENTS CHECK
   ============================================================================ */

function sfa_check_requirements() {
	$ok = true;

	// PHP version
	if ( version_compare( PHP_VERSION, SFA_MIN_PHP, '<' ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="notice notice-error"><p>';
			printf( esc_html__( 'Starter Flavor Addons requires PHP %s or higher.', 'starter-flavor-addons' ), SFA_MIN_PHP );
			echo '</p></div>';
		} );
		$ok = false;
	}

	// WordPress version
	if ( version_compare( get_bloginfo( 'version' ), SFA_MIN_WP, '<' ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="notice notice-error"><p>';
			printf( esc_html__( 'Starter Flavor Addons requires WordPress %s or higher.', 'starter-flavor-addons' ), SFA_MIN_WP );
			echo '</p></div>';
		} );
		$ok = false;
	}

	// Starter Flavor theme
	if ( 'starter-flavor' !== get_template() ) {
		add_action( 'admin_notices', function() {
			echo '<div class="notice notice-warning"><p>';
			esc_html_e( 'Starter Flavor Addons is designed for the Starter Flavor theme. Some features may not work correctly with other themes.', 'starter-flavor-addons' );
			echo '</p></div>';
		} );
		// Not blocking — plugin still loads
	}

	return $ok;
}

if ( ! sfa_check_requirements() ) {
	return;
}

/* ============================================================================
   CORE CLASS
   ============================================================================ */

final class Starter_Flavor_Addons {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Load required files.
	 */
	private function includes(): void {
		require_once SFA_DIR . 'includes/class/class-widget-loader.php';
		require_once SFA_DIR . 'includes/class/class-assets.php';
		require_once SFA_DIR . 'includes/class/class-shortcodes.php';
		require_once SFA_DIR . 'includes/class/class-demo-importer.php';
		require_once SFA_DIR . 'includes/class/class-white-label.php';
	}

	/**
	 * Register WordPress hooks.
	 */
	private function hooks(): void {
		add_action( 'plugins_loaded',          array( $this, 'load_textdomain' ) );
		add_action( 'elementor/init',          array( $this, 'init_elementor' ) );
		add_action( 'wp_enqueue_scripts',      array( $this, 'enqueue_frontend' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_editor' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'starter-flavor-addons', false, SFA_DIR . 'languages/' );
	}

	public function init_elementor(): void {
		SFA_Widget_Loader::instance();
	}

	public function enqueue_frontend(): void {
		SFA_Assets::instance()->enqueue_frontend();
	}

	public function enqueue_editor(): void {
		SFA_Assets::instance()->enqueue_editor();
	}
}

/* ============================================================================
   BOOT
   ============================================================================ */

add_action( 'plugins_loaded', function() {
	Starter_Flavor_Addons::instance();
}, 5 );


/* ============================================================================
   ACTIVATION / DEACTIVATION
   ============================================================================ */

register_activation_hook( __FILE__, function() {
	// Reset kit sync flag so colors push to Elementor on next load
	delete_option( 'sf_elementor_kit_synced' );
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function() {
	flush_rewrite_rules();
} );

/* ============================================================================
   AJAX: Contact Form Handler
   ============================================================================ */
add_action('wp_ajax_sf_contact_submit',        'sfa_contact_form_handler');
add_action('wp_ajax_nopriv_sf_contact_submit', 'sfa_contact_form_handler');

function sfa_contact_form_handler(): void {
    check_ajax_referer('sf_contact_nonce', 'nonce');

    $name    = sanitize_text_field($_POST['name'] ?? '');
    $email   = sanitize_email($_POST['email'] ?? '');
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');

    if (!$email || !is_email($email) || !$name || !$message) {
        wp_send_json_error(['message' => __('Please fill in all required fields.', 'starter-flavor-addons')]);
    }

    $to = get_option('admin_email');

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        "Reply-To: {$name} <{$email}>",
        "From: {$name} <{$email}>",
    ];

    $body = "<html><body style='font-family:sans-serif;'>
        <h2 style='color:#FF5E2E;'>New Contact Form Submission</h2>
        <table>
            <tr><td><strong>Name:</strong></td><td>".esc_html($name)."</td></tr>
            <tr><td><strong>Email:</strong></td><td>".esc_html($email)."</td></tr>
            <tr><td><strong>Subject:</strong></td><td>".esc_html($subject)."</td></tr>
        </table>
        <h3>Message:</h3>
        <p>".nl2br(esc_html($message))."</p>
    </body></html>";

    $sent = wp_mail($to, "Contact: {$subject}", $body, $headers);

    if ($sent) {
        wp_send_json_success(['message' => __('Message sent successfully. We\'ll be in touch soon!', 'starter-flavor-addons')]);
    } else {
        wp_send_json_error(['message' => __('Failed to send message. Please try again.', 'starter-flavor-addons')]);
    }
}

/* ============================================================================
   AJAX: Google Maps Proxy (uses theme option API key)
   ============================================================================ */
add_action('wp_ajax_sf_maps_embed_url',        'sfa_get_maps_url');
add_action('wp_ajax_nopriv_sf_maps_embed_url', 'sfa_get_maps_url');

function sfa_get_maps_url(): void {
    check_ajax_referer('sf_maps_nonce', 'nonce');
    $address = sanitize_text_field($_POST['address'] ?? '');
    $key     = get_option('sf_google_maps_api_key', '');
    if (!$address) wp_send_json_error('No address');
    $url = 'https://www.google.com/maps/embed/v1/place?q='.urlencode($address).($key ? '&key='.$key : '');
    wp_send_json_success(['url' => $url]);
}

/* ============================================================================
   AJAX: Portfolio Filter (proxies to theme class)
   ============================================================================ */
// Already handled by Starter_Flavor_Portfolio::ajax_filter() in the theme

/* ============================================================================
   Frontend: Enqueue Plugin Assets + Localize
   ============================================================================ */
add_action('wp_enqueue_scripts', function() {
    if (!defined('ELEMENTOR_VERSION')) return;
    wp_localize_script('sfa-widgets', 'sfaData', array_merge(
        is_array(wp_scripts()->get_data('sfa-widgets', 'data')) ? wp_scripts()->get_data('sfa-widgets', 'data') : [],
        [
            'ajaxUrl'      => admin_url('admin-ajax.php'),
            'contactNonce' => wp_create_nonce('sf_contact_nonce'),
            'mapsNonce'    => wp_create_nonce('sf_maps_nonce'),
            'hasMapsKey'   => !empty(get_option('sf_google_maps_api_key', '')),
            'i18n'         => [
                'sending'       => __('Sending…', 'starter-flavor-addons'),
                'success'       => __('Message sent!', 'starter-flavor-addons'),
                'error'         => __('Error. Please try again.', 'starter-flavor-addons'),
                'invalidEmail'  => __('Please enter a valid email.', 'starter-flavor-addons'),
                'required'      => __('This field is required.', 'starter-flavor-addons'),
            ],
        ]
    ));
}, 20);
