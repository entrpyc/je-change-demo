<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class PageOffer extends Controller
{
    public function __construct()
    {
    }

    public function data()
    {
        global $post;
        global $wp;

        $request = $wp->request;
        $request = str_replace('//','/', $request);
        $explode_request = explode('/',$request);
        $type = $explode_request[1];
        $query = new \WP_Query([
            'post_type' => 'offer',
            'meta_key' => 'service',
            'meta_value' => get_post_meta($post->ID, 'service')[0]
        ]);
        return ['posts' => $query->posts];
    }
}
