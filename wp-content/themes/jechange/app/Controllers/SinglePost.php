<?php

namespace App\Controllers;

use Sober\Controller\Controller;
use WP_Query;

class SinglePost extends Controller
{
    public function __construct()
    {
    }

    /**
     * Post feature image
     * @return array|false
     */
    public function data()
    {
        $id = get_the_ID();

        $data['title'] = get_the_title($id);
        $data['url'] = get_permalink($id);
        $data['date'] = get_the_date('d/m/Y', $id);
        $data['image'] = get_the_post_thumbnail_url($id, 'full');

        $categories = get_the_category($id);
        $data['category_name'] = $categories[0]->name;
        $data['category_url'] = get_category_link($categories[0]->term_id);

        return $data;
    }

    /**
     * All Posts Query
     * Optional pagination
     * @return WP_Query
     */
    public function relatedNews()
    {
        $current_post_id = get_the_ID();

        $args = array(
            'post_type' => 'post',
            'orderby' => 'date',
            'order'  => 'desc',
            'posts_per_page' => 9,
            "suppress_filters" => false,
            'post__not_in' => [$current_post_id], // exclude current project in the query
            'meta_query' => [
                'relation' => 'AND',
            ]
        );
        $query = new WP_Query($args);

        foreach($query->posts as $post) {
            $post->title = get_the_title($post->ID);
            $post->url = get_permalink($post->ID);
            $post->date = App::getDateInProperFormat($post->post_date);
            $post->excerpt = get_the_excerpt($post->ID);
        }

        return $query;
    }

    /**
     * Get Older Post Link
     */
    public function nextPostLink()
    {
        $nextPostObj = get_next_post();

        if(!$nextPostObj) {
            return '';
        }

        return get_permalink($nextPostObj->ID);
    }

    /**
     * Get Current Post Category Link
     */
    public function postCategoryLink()
    {
        $category = reset(get_the_category(get_the_ID()));

        if(!$category) {
            return '';
        }

        return get_category_link($category->cat_ID);
    }

}
