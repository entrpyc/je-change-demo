<?php

namespace App\Controllers;

use Sober\Controller\Controller;
use WP_Query;

class SingleProviders extends Controller
{
    public function __construct()
    {
    }

    /**
     * Post feature image
     * @return array|false
     */
    public static function data()
    {
        $provider_id = get_the_ID();

        // default args - show all offers from current service
        $args = [
            'post_type' => 'offer',
            'meta_query' => [
                [
                    'key' => 'provider_id',
                    'value' => $provider_id,
                ],
            ],
            'orderby' => 'meta_value_num',
            'order' => 'asc',
            'meta_key' => 'price'
        ];
        $query = new \WP_Query($args);
        $posts = $query->posts;

        return [
            'posts' => $posts,
            'page_build' => get_field('page_build', $provider_id) // PAGE BUILD
        ];
    }

}
