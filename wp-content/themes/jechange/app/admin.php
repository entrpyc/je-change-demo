<?php

namespace App;

/**
 * Theme customizer
 */
add_action('customize_register', function (\WP_Customize_Manager $wp_customize) {
    // Add postMessage support
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->selective_refresh->add_partial('blogname', [
        'selector' => '.brand',
        'render_callback' => function () {
            bloginfo('name');
        }
    ]);
});

/**
 * Customizer JS
 */
add_action('customize_preview_init', function () {
    wp_enqueue_script('sage/customizer.js', asset_path('scripts/customizer.js'), ['customize-preview'], null, true);
});

/**
 * Custom CSS For Admin
 */
add_action('admin_head', function () {
    ?>
    <style type="text/css">
        #wpseo-links,
        #wpseo-score,
        #wpseo-score-readability,
        #wpseo-title,
        #wpseo-metadesc,
        #wpseo-focuskw {  display: none; }

        td.column-wpseo-links, td.column-wpseo-score,
        td.column-wpseo-score-readability,
        td.column-wpseo-title,
        td.column-wpseo-metadesc,
        td.column-wpseo-focuskw  { display: none }

        th.column-wpseo-links, th.column-wpseo-score,
        th.column-wpseo-score-readability,
        th.column-wpseo-title,
        th.column-wpseo-metadesc,
        th.column-wpseo-focuskw  { display: none }
    </style>
    <?php
});


/**
 * Modify term slug with certain characters
 *
 * @param array $data = Array( 'name' => 'Term Name', 'slug' => 'term-slug', 'term_group' => 0 )
 * @param string $taxonomy
 * @param array $args
 *
 * @return array $data
 */
add_filter( 'wp_insert_term_data', function( $data, $taxonomy, $args ) {
    if($taxonomy == 'service'){
        $serviceTypeTermId = $args['acf']['field_5f2a8c8e63cdf'] ?? ''; // Service Type ACF Field
        $serviceTypeTerm = get_term( $serviceTypeTermId, 'service_type' );
        $data['slug'] = $serviceTypeTerm->slug . '/' . sanitize_title(str_replace(',', '-', $data['name']));
    }
    return $data;
}, 99, 3);

add_filter( 'wp_update_term_data', function( $data, $term_id, $taxonomy, $args ) {
    if($taxonomy == 'service'){
        $serviceTypeTermId = $args['acf']['field_5f2a8c8e63cdf'] ?? ''; // Service Type ACF Field
        $serviceTypeTerm = get_term( $serviceTypeTermId, 'service_type' );
        $data['slug'] = $serviceTypeTerm->slug . '/' . sanitize_title(str_replace(',', '-', $data['name']));
    }
    return $data;
}, 99, 4);