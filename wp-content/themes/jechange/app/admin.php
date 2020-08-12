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
 * Modify post slug
 *
 * @param array $data = Array( 'name' => 'Term Name', 'slug' => 'term-slug', 'term_group' => 0 )
 * @param string $post
 *
 * @return array $data
 */
add_filter( 'wp_insert_post_data', function( $data, $postArr ) {
    // is in edit mode
    $ref = $postArr["_wp_http_referer"] ?? null;
    $is_edit = true;
    if($ref) {
        if(substr($ref, - strlen($postArr['post_type'])) == $postArr['post_type']) {
            $is_edit = false;
        }
    }
    // todo change slug in edit mode

    //return data if still there is no post id set
    if(!$postArr['ID']) {
        return $data;
    }

    // $serviceTypeTermId = $postArr['acf']['field_5f323c00dd861'];
    // $serviceTermId = $postArr['acf']['field_5f3253a611f2b'];
    $mapping = [
        'assurance' => 'assureurs',
        'energie' => 'fournisseurs',
        'telecom' => 'operateurs',
        'placement' => 'banques',
        'credit' => 'societes',
    ];

    // PROVIDER 
    if($postArr['post_type'] == 'providers') {
        $serviceTypeTermId = $postArr['acf']['field_5f323c00dd861'];
        
        $serviceTypeTerm = get_term( $serviceTypeTermId );
        $serviceTypeTermSlug = $serviceTypeTerm->slug; // energie
        if($mapping[$serviceTypeTermSlug]) {
            $serviceTypeTermSlug .= '/' . $mapping[$serviceTypeTermSlug];// energie/fournisseurs
        }       


        $data['post_name'] = $serviceTypeTermSlug . '/' . sanitize_title(str_replace(',', '-', $postArr['post_title']));
    }


    // PROVIDER ARTICLE
    if($postArr['post_type'] == 'provider_article') {
        $serviceTypeTermId = $postArr['acf']['field_5f323c00dd861'];
        
        $serviceTypeTerm = get_term( $serviceTypeTermId );
        $serviceTypeTermSlug = $serviceTypeTerm->slug; // energie
        if($mapping[$serviceTypeTermSlug]) {
            $serviceTypeTermSlug .= '/' . $mapping[$serviceTypeTermSlug];// energie/fournisseurs
        }   
        
        $providerId = $postArr['acf']['field_5f30f8420913c'][0];
        $provider = get_post($providerId);
        $serviceTypeTermSlug .= '/' . sanitize_title(str_replace(',', '-', $provider->post_title));// energie/fournisseurs/gazprom
        
        $serviceTypeTermSlug .= '/' . sanitize_title(str_replace(',', '-', $postArr['post_title'])); // energie/fournisseurs/gazprom/article

        $data['post_name'] = $serviceTypeTermSlug;
    }

    
    if($postArr['post_type'] == 'post' ||  $postArr['post_type'] == 'guides') {
        $serviceTypeTermId = $postArr['acf']['field_5f323c00dd861'];
        $serviceTypeTerm = get_term( $serviceTypeTermId );
        $serviceTermId = $postArr['acf']['field_5f3253a611f2b'];
        $serviceTerm = get_term( $serviceTermId );
        $postType = ($postArr['post_type'] == 'post') ? 'news' : 'guides';
        $data['post_name'] = $serviceTypeTerm->slug . '/' . $serviceTerm->slug . '/' . $postType . '/' . sanitize_title(str_replace(',', '-', $postArr['post_title']));
    }
    
    if($postArr['post_type'] == 'page' ) {
        $slug = '';
        $serviceTypeTermId = $postArr['acf']['field_5f3402f389887'];
        $serviceTypeTerm = get_term( $serviceTypeTermId );
        $slug .= $serviceTypeTerm->slug . '/' ;

        $serviceTermId = $postArr['acf']['field_5f34032989888'];
        if($serviceTermId) {
            $serviceTerm = get_term( $serviceTermId );
            $slug .= $serviceTerm->slug . '/' ;
        }
        
        $data['post_name'] =  $slug . sanitize_title(str_replace(',', '-', $postArr['post_title']));
    }
    return $data;
}, 1, 2);

/**
 * remove category and tags
 */
add_action('init', function(){
    global $pagenow;
 
    register_taxonomy( 'post_tag', array() );
    register_taxonomy( 'category', array() );
 
    $tax = array('post_tag','category');
 
    if($pagenow == 'edit-tags.php' && in_array($_GET['taxonomy'],$tax) ){
    wp_die('Invalid taxonomy');
    }
});



/**
 * Remove the slug from published post permalinks. Only affect our CPT though.
 */
add_filter( 'post_type_link', function($post_link, $post) {
    if ( !in_array($post->post_type, ['guides','providers','provider_article','press_release', 'press_review']) ) {
        return $post_link;
    }
    $post_link = preg_replace( '/\/' . $post->post_type . '\//', '/', $post_link , 1);
	return $post_link;
}, 10, 2 );

/** fix  %post_name% permalink in posts */
add_filter( 'post_link', function($post_link, $post) {
    $post_link = str_replace( '%post_name%/', $post->post_name, $post_link);
	return $post_link;
}, 10, 2 );
/** fix %pagename% (duplicate slug) permalink in pages */
add_filter( 'page_link', function($post_link, $post) {
    $post_link = str_replace( '%pagename%/', $post->post_name, $post_link);
	return $post_link;
}, 10, 2 );

/**
 * Have WordPress match postname to any of our public post types (post, page, race).
 * All of our public post types can have /post-name/ as the slug, so they need to be unique across all posts.
 * By default, WordPress only accounts for posts and pages where the slug is /post-name/.
 *
 * @param $query The current query.
 */
add_action('pre_get_posts', function ($query) {
    
    // Only noop the main query
    if (!$query->is_main_query()) {
        return;
    }
    // Bail if this query doesn't match our very specific rewrite rule.
    if (!isset($query->query['page']) || 2 !== count($query->query)) {
        return;
    }
    $query->set('exact_where', "post_name like '".$query->query['pagename']."'");


    return $query;
});

add_filter( 'posts_where', function ( $where, $wp_query ) {
    if ( $extend_where = $wp_query->get( 'extend_where' ) ) {
        $where .= 'AND '. $extend_where;
    }
    if ( $exact_where = $wp_query->get( 'exact_where' ) ) {
        $where = 'AND '. $exact_where;
    }
    return $where;
}, 10, 2 );

add_filter('posts_request', function ($input) {
    if (!is_admin()) {
        echo '<pre>', var_dump($input), '</pre>';
    }
    return $input;
});

