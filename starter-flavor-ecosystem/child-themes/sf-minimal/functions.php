<?php
/**
 * Starter Flavor Minimal – Child Theme Functions
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'sf-minimal',
        get_stylesheet_uri(),
        [ 'starter-flavor-style' ],
        wp_get_theme()->get('Version')
    );
    // Load Playfair Display
    wp_enqueue_style( 'sf-minimal-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap', [], null );
}, 20);

// Minimal: no hero overlay, no section backgrounds
add_filter( 'sf_hero_defaults', function( $defaults ) {
    $defaults['sf_hero_bg_color'] = '#ffffff';
    $defaults['sf_hero_text_color'] = '#111111';
    return $defaults;
});
