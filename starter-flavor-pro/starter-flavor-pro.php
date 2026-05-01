<?php
/**
 * Plugin Name:       Starter Flavor Pro
 * Plugin URI:        https://whatsdigital.de/starter-flavor/pro
 * Description:       Pro extension for Starter Flavor — premium widgets, template library, advanced animations, and extended WooCommerce features.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            WhatsDigital
 * Author URI:        https://whatsdigital.de
 * License:           Proprietary
 * Text Domain:       starter-flavor-pro
 *
 * Requires: starter-flavor theme + starter-flavor-addons plugin
 *
 * @package Starter_Flavor_Pro
 */

if (!defined('ABSPATH')) exit;

define('SF_PRO_VERSION', '1.0.0');
define('SF_PRO_FILE',    __FILE__);
define('SF_PRO_DIR',     plugin_dir_path(__FILE__));
define('SF_PRO_URL',     plugin_dir_url(__FILE__));

// ── Dependency Check ──────────────────────────────────────────────────────
function sf_pro_check_dependencies(): bool {
    if ('starter-flavor' !== get_template()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>'
                .esc_html__('Starter Flavor Pro requires the Starter Flavor theme.', 'starter-flavor-pro')
                .'</p></div>';
        });
        return false;
    }
    if (!function_exists('sf_register_extension')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>'
                .esc_html__('Starter Flavor Pro requires the Starter Flavor Addons plugin.', 'starter-flavor-pro')
                .'</p></div>';
        });
        return false;
    }
    return true;
}

// ── Boot ──────────────────────────────────────────────────────────────────
add_action('plugins_loaded', function() {
    if (!sf_pro_check_dependencies()) return;
    Starter_Flavor_Pro::instance();
}, 10);

// ── Core Class ────────────────────────────────────────────────────────────
final class Starter_Flavor_Pro {

    private static ?self $instance = null;

    public static function instance(): self {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->register();
        $this->load();
    }

    /**
     * Register this extension with the theme's Hook API.
     * This is how extensions announce themselves to the ecosystem.
     */
    private function register(): void {
        sf_register_extension('sf-pro', [
            'name'    => 'Starter Flavor Pro',
            'version' => SF_PRO_VERSION,
            'author'  => 'WhatsDigital',
            'file'    => SF_PRO_FILE,
        ]);

        // Register Pro modules
        add_action('sf_modules_registered', function() {
            sf_register_module('pro-animations', [
                'label'       => __('Pro Animations', 'starter-flavor-pro'),
                'description' => __('Advanced scroll and entrance animations with timeline control.', 'starter-flavor-pro'),
                'enabled'     => true,
                'pro'         => true,
            ]);
            sf_register_module('template-library', [
                'label'       => __('Template Library', 'starter-flavor-pro'),
                'description' => __('100+ sections, landing pages, and block templates.', 'starter-flavor-pro'),
                'enabled'     => true,
                'pro'         => true,
            ]);
            sf_register_module('pro-woocommerce', [
                'label'       => __('Pro WooCommerce', 'starter-flavor-pro'),
                'description' => __('Quick View, AJAX Cart, Sticky Add to Cart, Wishlist.', 'starter-flavor-pro'),
                'enabled'     => class_exists('WooCommerce'),
                'pro'         => true,
            ]);
            sf_register_module('dynamic-content', [
                'label'       => __('Dynamic Content', 'starter-flavor-pro'),
                'description' => __('Custom field integration, query builder, dynamic tags.', 'starter-flavor-pro'),
                'enabled'     => true,
                'pro'         => true,
            ]);
        });
    }

    private function load(): void {
        // Pro widgets (extend the base 80)
        add_action('elementor/widgets/register', [$this, 'register_pro_widgets']);

        // Pro skin token overrides
        add_filter('sf_module_pro-animations_tokens', [$this, 'animation_tokens'], 10, 2);

        // Pro admin page
        add_action('admin_menu', [$this, 'add_pro_menu'], 20);

        // Enqueue Pro assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_pro_assets'], 20);
    }

    public function register_pro_widgets(\Elementor\Elements_Manager $manager): void {
        $widget_dir = SF_PRO_DIR . 'includes/widgets/';
        if (!is_dir($widget_dir)) return;

        foreach (glob($widget_dir . '*.php') as $file) {
            require_once $file;
            // Auto-discover class name
            $content = file_get_contents($file);
            if (preg_match('/^class\s+(SF_Pro_\w+)\s+extends/m', $content, $m)) {
                if (class_exists($m[1])) {
                    $manager->register(new $m[1]());
                }
            }
        }
    }

    public function animation_tokens(array $tokens, string $skin_id): array {
        // Pro animation timing overrides per skin
        $tokens['--sf-anim-duration-fast']   = '0.15s';
        $tokens['--sf-anim-duration-normal'] = '0.35s';
        $tokens['--sf-anim-duration-slow']   = '0.6s';
        return $tokens;
    }

    public function add_pro_menu(): void {
        add_submenu_page(
            'starter-flavor-settings',
            __('Pro Features', 'starter-flavor-pro'),
            __('⚡ Pro Features', 'starter-flavor-pro'),
            'manage_options',
            'sf-pro-features',
            [$this, 'render_pro_page']
        );
    }

    public function render_pro_page(): void {
        $modules = sf_get_modules();
        $pro_modules = array_filter($modules, fn($m) => !empty($m['pro']));
        ?>
        <div class="wrap" style="font-family:-apple-system,BlinkMacSystemFont,'Inter',sans-serif;">
            <div style="background:linear-gradient(135deg,#7c3aed,#06b6d4);border-radius:12px;padding:28px 32px;margin-bottom:24px;color:#fff;">
                <h1 style="margin:0 0 8px;font-size:1.4rem;font-weight:800;">⚡ Starter Flavor Pro</h1>
                <p style="margin:0;opacity:.8;font-size:13px;">v<?php echo esc_html(SF_PRO_VERSION); ?> — <?php printf(esc_html__('%d Pro modules active', 'starter-flavor-pro'), count($pro_modules)); ?></p>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
                <?php foreach ($pro_modules as $id => $mod): ?>
                <div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <h3 style="margin:0;font-size:14px;font-weight:800;color:#1e293b;"><?php echo esc_html($mod['label']); ?></h3>
                        <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(124,58,237,.1);color:#7c3aed;">PRO</span>
                    </div>
                    <p style="margin:0;font-size:12px;color:#64748b;line-height:1.6;"><?php echo esc_html($mod['description']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    public function enqueue_pro_assets(): void {
        if (sf_is_module_enabled('pro-animations')) {
            // Pro animation CSS would be enqueued here
        }
        if (sf_is_module_enabled('pro-woocommerce') && class_exists('WooCommerce')) {
            // Pro WooCommerce CSS would be enqueued here
        }
    }
}
