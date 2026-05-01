<?php
/**
 * White-Label Manager
 * Allows agencies to rebrand Starter Flavor Addons for their clients.
 * Settings: custom plugin name, author, support URL, hide branding.
 *
 * @package Starter_Flavor_Addons
 * @since   1.1.0
 */
if (!defined('ABSPATH')) exit;

class SFA_White_Label {

    private static ?self $instance = null;
    private array $settings = [];

    public static function instance(): self {
        if (is_null(self::$instance)) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->settings = get_option('sfa_white_label', []);
        add_action('admin_menu',              [$this, 'add_settings_page']);
        add_filter('all_plugins',             [$this, 'modify_plugin_data']);
        add_filter('gettext',                 [$this, 'replace_branding'], 10, 3);
        add_action('admin_bar_menu',          [$this, 'modify_admin_bar'], 999);
        add_action('admin_enqueue_scripts',   [$this, 'maybe_hide_notices']);
    }

    private function get(string $key, string $default = ''): string {
        return $this->settings[$key] ?? $default;
    }

    private function is_enabled(): bool {
        return !empty($this->settings['enabled']);
    }

    // ── Settings Page ──────────────────────────────────────────────────────

    public function add_settings_page(): void {
        add_options_page(
            __('White Label Settings', 'starter-flavor-addons'),
            __('White Label', 'starter-flavor-addons'),
            'manage_options',
            'sfa-white-label',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page(): void {
        if (isset($_POST['sfa_wl_nonce']) && wp_verify_nonce($_POST['sfa_wl_nonce'], 'sfa_white_label')) {
            $data = [
                'enabled'       => !empty($_POST['sfa_wl_enabled']),
                'plugin_name'   => sanitize_text_field($_POST['sfa_wl_plugin_name'] ?? ''),
                'plugin_desc'   => sanitize_text_field($_POST['sfa_wl_plugin_desc'] ?? ''),
                'author_name'   => sanitize_text_field($_POST['sfa_wl_author_name'] ?? ''),
                'author_url'    => esc_url_raw($_POST['sfa_wl_author_url'] ?? ''),
                'support_url'   => esc_url_raw($_POST['sfa_wl_support_url'] ?? ''),
                'hide_from_plugins' => !empty($_POST['sfa_wl_hide']),
                'admin_bar_label'   => sanitize_text_field($_POST['sfa_wl_admin_bar'] ?? ''),
                'custom_css'        => sanitize_textarea_field($_POST['sfa_wl_css'] ?? ''),
            ];
            update_option('sfa_white_label', $data);
            $this->settings = $data;
            echo '<div class="notice notice-success"><p>'.esc_html__('White Label settings saved.','starter-flavor-addons').'</p></div>';
        }

        $s = $this->settings;
        ?>
        <div class="wrap">
            <h1 style="display:flex;align-items:center;gap:12px;">
                <span style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#FF5E2E,#ED4C1C);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:16px;">🏷️</span>
                <?php esc_html_e('White Label Settings','starter-flavor-addons'); ?>
            </h1>
            <p style="color:#64748b;max-width:560px;"><?php esc_html_e('Rebrand the Starter Flavor plugin for your clients. All branding is replaced on the admin side only — the code remains the same.','starter-flavor-addons'); ?></p>

            <form method="post" action="">
                <?php wp_nonce_field('sfa_white_label','sfa_wl_nonce'); ?>
                <table class="form-table">
                    <tr><th><?php esc_html_e('Enable White Label','starter-flavor-addons'); ?></th>
                        <td><label><input type="checkbox" name="sfa_wl_enabled" value="1" <?php checked(!empty($s['enabled'])); ?> /> <?php esc_html_e('Activate white labeling','starter-flavor-addons'); ?></label></td></tr>
                    <tr><th><?php esc_html_e('Plugin Name','starter-flavor-addons'); ?></th>
                        <td><input type="text" name="sfa_wl_plugin_name" value="<?php echo esc_attr($s['plugin_name'] ?? ''); ?>" class="regular-text" placeholder="My Agency Toolkit" /></td></tr>
                    <tr><th><?php esc_html_e('Plugin Description','starter-flavor-addons'); ?></th>
                        <td><input type="text" name="sfa_wl_plugin_desc" value="<?php echo esc_attr($s['plugin_desc'] ?? ''); ?>" class="large-text" placeholder="Custom toolkit for your website." /></td></tr>
                    <tr><th><?php esc_html_e('Author Name','starter-flavor-addons'); ?></th>
                        <td><input type="text" name="sfa_wl_author_name" value="<?php echo esc_attr($s['author_name'] ?? ''); ?>" class="regular-text" placeholder="Your Agency Name" /></td></tr>
                    <tr><th><?php esc_html_e('Author URL','starter-flavor-addons'); ?></th>
                        <td><input type="url" name="sfa_wl_author_url" value="<?php echo esc_url($s['author_url'] ?? ''); ?>" class="regular-text" placeholder="https://youragency.com" /></td></tr>
                    <tr><th><?php esc_html_e('Support URL','starter-flavor-addons'); ?></th>
                        <td><input type="url" name="sfa_wl_support_url" value="<?php echo esc_url($s['support_url'] ?? ''); ?>" class="regular-text" placeholder="https://youragency.com/support" /></td></tr>
                    <tr><th><?php esc_html_e('Admin Bar Label','starter-flavor-addons'); ?></th>
                        <td><input type="text" name="sfa_wl_admin_bar" value="<?php echo esc_attr($s['admin_bar_label'] ?? ''); ?>" class="regular-text" placeholder="My Agency" /></td></tr>
                    <tr><th><?php esc_html_e('Hide from Plugins List','starter-flavor-addons'); ?></th>
                        <td><label><input type="checkbox" name="sfa_wl_hide" value="1" <?php checked(!empty($s['hide_from_plugins'])); ?> /> <?php esc_html_e('Make this plugin invisible to clients','starter-flavor-addons'); ?></label></td></tr>
                    <tr><th><?php esc_html_e('Custom Admin CSS','starter-flavor-addons'); ?></th>
                        <td><textarea name="sfa_wl_css" rows="4" class="large-text code"><?php echo esc_textarea($s['custom_css'] ?? ''); ?></textarea>
                            <p class="description"><?php esc_html_e('Custom CSS injected in wp-admin (for branding colors, etc.)','starter-flavor-addons'); ?></p></td></tr>
                </table>
                <?php submit_button(__('Save White Label Settings','starter-flavor-addons')); ?>
            </form>
        </div>
        <?php
    }

    // ── Modify Plugin Data ─────────────────────────────────────────────────

    public function modify_plugin_data(array $plugins): array {
        if (!$this->is_enabled()) return $plugins;
        $plugin_file = 'starter-flavor-addons/starter-flavor-addons.php';

        if (isset($plugins[$plugin_file])) {
            if (!empty($this->get('hide_from_plugins'))) {
                unset($plugins[$plugin_file]);
                return $plugins;
            }
            if ($name = $this->get('plugin_name'))   $plugins[$plugin_file]['Name']        = $name;
            if ($desc = $this->get('plugin_desc'))   $plugins[$plugin_file]['Description']  = $desc;
            if ($auth = $this->get('author_name'))   $plugins[$plugin_file]['Author']       = $auth;
            if ($url  = $this->get('author_url'))    $plugins[$plugin_file]['AuthorURI']    = $url;
            $plugins[$plugin_file]['PluginURI'] = $this->get('support_url') ?: $url;
        }
        return $plugins;
    }

    // ── Text Replacement ──────────────────────────────────────────────────

    public function replace_branding(string $translated, string $text, string $domain): string {
        if (!$this->is_enabled() || !is_admin()) return $translated;
        if ($name = $this->get('plugin_name')) {
            $translated = str_replace('Starter Flavor Addons', $name, $translated);
        }
        if ($auth = $this->get('author_name')) {
            $translated = str_replace('WhatsDigital', $auth, $translated);
        }
        return $translated;
    }

    // ── Admin Bar ─────────────────────────────────────────────────────────

    public function modify_admin_bar(\WP_Admin_Bar $bar): void {
        if (!$this->is_enabled()) return;
        $label = $this->get('admin_bar_label');
        if (!$label) return;
        $bar->remove_node('starter-flavor-welcome');
    }

    // ── Custom CSS ─────────────────────────────────────────────────────────

    public function maybe_hide_notices(): void {
        if (!$this->is_enabled()) return;
        if ($css = $this->get('custom_css')) {
            echo '<style>'.wp_strip_all_tags($css).'</style>';
        }
    }
}

SFA_White_Label::instance();
