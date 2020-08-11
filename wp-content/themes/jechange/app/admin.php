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
    // SERVICE_TYPE
    if($taxonomy == 'service_type'){
        $mapping = [
            'assurance' => 'assureurs',
            'energie' => 'fournisseurs',
            'telecom' => 'operateurs',
            'placement' => 'banques',
            'credit' => 'societes',
        ];
        // Create slug to be like energie/fournisseurs if exists in the mapping (migrated from the old site)
        if($mapping[$data['slug']]) {
            $data['slug'] = $data['slug'] . '/' . $mapping[$data['slug']];
        }
    }

    // SERVICE
    if($taxonomy == 'service'){
        $serviceTypeTermId = $args['acf']['field_5f2a8c8e63cdf'] ?? ''; // get service_type relational ACF Field
        $serviceTypeTermSingleSlug = get_field('single_slug', 'service_type_' . $serviceTypeTermId);
        $data['slug'] = $serviceTypeTermSingleSlug . '/' . $data['slug'];
    }
    return $data;
}, 99, 3);

add_filter( 'wp_update_term_data', function( $data, $term_id, $taxonomy, $args ) {

    // SERVICE_TYPE
    if($taxonomy == 'service_type'){
        $mapping = [
            'assurance' => 'assureurs',
            'energie' => 'fournisseurs',
            'telecom' => 'operateurs',
            'placement' => 'banques',
            'credit' => 'societes',
        ];
        // Create slug to be like energie/fournisseurs if exists in the mapping (migrated from the old site)
        $serviceTypeSingleSlug = $args['acf']['field_5f2aa976a332e'] ?? ''; // get single_slug ACF Field
        if($mapping[$serviceTypeSingleSlug]) {
            $data['slug'] = $serviceTypeSingleSlug . '/' . $mapping[$serviceTypeSingleSlug];
        }
    }

    // SERVICE
    if($taxonomy == 'service'){
        $serviceTypeTermId = $args['acf']['field_5f2a8c8e63cdf'] ?? ''; //  get service_type relational ACF Field
        $serviceTypeTermSingleSlug = get_field('single_slug', 'service_type_' . $serviceTypeTermId);
        $data['slug'] = $serviceTypeTermSingleSlug . '/' . sanitize_title(str_replace(',', '-', $data['name']));

    }
    return $data;
}, 99, 4);



/**
 * Modify post slug
 *
 * @param array $data = Array( 'name' => 'Term Name', 'slug' => 'term-slug', 'term_group' => 0 )
 * @param string $post
 *
 * @return array $data
 */
add_filter( 'wp_insert_post_data', function( $data, $postArr ) {

    //return data if still there is no post id set
    if(!$postArr['ID']) {
        return $data;
    }

    // PROVIDER 
    if($postArr['post_type'] == 'providers') {
        // TODO PROVIDER
        
        $serviceTypeTermId = $postArr['tax_input']['service_type'][0] ?? ''; // get first service type term from sidebar
        $serviceTypeTerm = get_term( $serviceTypeTermId );
        $serviceTypeTermSlug = $serviceTypeTerm->slug; // energie/fournisseurs

        $data['post_name'] = $serviceTypeTermSlug . '/' . sanitize_title(str_replace(',', '-', $postArr['post_title']));
    }


    // PROVIDER ARTICLE
    if($postArr['post_type'] == 'provider_article') {
        $serviceTypeTermId = $postArr['tax_input']['service_type'][0] ?? ''; // get first service type term from sidebar
        $serviceTypeTerm = get_term( $serviceTypeTermId );
        $serviceTypeTermSlug = $serviceTypeTerm->slug; // energie/fournisseurs

        $providerId = $postArr['acf']['field_5f30f8420913c'][0] ?? ''; // get provider relational ACF field in Provider Article
        $provider = get_post($providerId);
        $providerSlug = $provider->post_name; // gazprom

        $data['post_name'] = $serviceTypeTermSlug . '/' . $providerSlug . '/' . sanitize_title(str_replace(',', '-', $postArr['post_title']));
    }

    return $data;
}, 1, 2);


