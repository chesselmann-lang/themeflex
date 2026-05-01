<?php
/**
 * Starter Flavor Free — Functions
 * Free version of Starter Flavor, available on WordPress.org.
 * Upgrade to Pro at https://whatsdigital.de/starter-flavor
 *
 * @package Starter_Flavor_Free
 */
if (!defined('ABSPATH')) exit;

define('SFF_VERSION', '1.0.0');
define('SFF_DIR',     get_template_directory());
define('SFF_URL',     get_template_directory_uri());

/* ── Setup ── */
add_action('after_setup_theme', function() {
    // Core WP features
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    add_theme_support('woocommerce');
    add_theme_support('elementor');

    // Translation
    load_theme_textdomain('starter-flavor-free', SFF_DIR.'/languages/');

    // Menus
    register_nav_menus(['primary' => __('Primary Menu','starter-flavor-free')]);
});

/* ── Enqueue ── */
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('sff-style',  get_stylesheet_uri(), [], SFF_VERSION);
    wp_enqueue_script('sff-main', SFF_URL.'/assets/js/main.js', [], SFF_VERSION, true);
});

/* ── Widgets ── */
add_action('widgets_init', function() {
    register_sidebar(['name'=>__('Sidebar','starter-flavor-free'),'id'=>'sidebar-1',
        'before_widget'=>'<section id="%1$s" class="widget %2$s">','after_widget'=>'</section>',
        'before_title'=>'<h3 class="widget-title">','after_title'=>'</h3>']);
    register_sidebar(['name'=>__('Footer','starter-flavor-free'),'id'=>'footer-1',
        'before_widget'=>'<section id="%1$s" class="widget %2$s">','after_widget'=>'</section>',
        'before_title'=>'<h4 class="widget-title">','after_title'=>'</h4>']);
});

/* ── FREE LIMITS — 10 widgets, 3 skins, 3 demos ── */
define('SFF_FREE_WIDGETS', ['hero-section','cta-box','testimonial','pricing-table','team-member','services','counter-stats','icon-box','accordion-tabs','contact-form']);
define('SFF_FREE_SKINS',   ['default','corporate','creative']);
define('SFF_FREE_DEMOS',   ['default','creative','saas']);

/* ── Upsell notice in Elementor panel ── */
add_action('elementor/editor/before_enqueue_scripts', function() {
    ?>
    <style>
    .elementor-panel-category[data-category="starter-flavor"] .elementor-panel-category-title::after {
        content: ' — Free (10/80)';
        font-size: 10px;
        color: #FF5E2E;
        font-weight: 700;
    }
    </style>
    <?php
});

/* ── Pro upgrade notice in admin ── */
add_action('admin_notices', function() {
    if (get_user_meta(get_current_user_id(),'sff_upsell_dismissed',true)) return;
    echo '<div class="notice notice-info" style="border-left:4px solid #FF5E2E;display:flex;align-items:center;gap:16px;padding:14px 16px;">';
    echo '<span style="font-size:24px;">🚀</span>';
    echo '<div><strong>Starter Flavor Free</strong> — '.esc_html__('Upgrade to Pro for 80+ widgets, 20 skins, 15 demos, AI Content Assistant, and WooCommerce Pro features.','starter-flavor-free').'</div>';
    echo '<a href="https://themeforest.net" target="_blank" class="button button-primary" style="margin-left:auto;background:#FF5E2E;border-color:#FF5E2E;flex-shrink:0;">'.esc_html__('Upgrade to Pro — $59','starter-flavor-free').'</a>';
    echo '<a href="?sff_dismiss_upsell=1" style="margin-left:8px;color:#94a3b8;text-decoration:none;">&times;</a>';
    echo '</div>';
});
add_action('admin_init', function() {
    if (isset($_GET['sff_dismiss_upsell'])) {
        update_user_meta(get_current_user_id(),'sff_upsell_dismissed',true);
    }
});

/* ── Customizer (minimal free version) ── */
add_action('customize_register', function(WP_Customize_Manager $wp) {
    $wp->add_section('sff_general',['title'=>__('Starter Flavor','starter-flavor-free'),'priority'=>10]);
    $wp->add_setting('sff_primary_color',['default'=>'#FF5E2E','sanitize_callback'=>'sanitize_hex_color']);
    $wp->add_control(new WP_Customize_Color_Control($wp,'sff_primary_color',['section'=>'sff_general','label'=>__('Primary Color','starter-flavor-free')]));
});
add_action('wp_head', function() {
    $color = get_theme_mod('sff_primary_color','#FF5E2E');
    echo "<style>:root{--sf-color-primary:".esc_attr($color).";}</style>\n";
});
