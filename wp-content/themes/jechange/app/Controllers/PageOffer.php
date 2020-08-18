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
        $types = [
            'energie/electricite/comparatif' => 'electricity',
            'energie/duale/comparatif' => 'duale',
            'energie/gaz/comparatif' => 'gaz',
            'telecom/internet/comparatif' => 'internet',
            'telecom/mobile/forfait-bloque' => 'mobile',
            'telecom/mobile/forfaitillimite' => 'mobile',
        ];
        $type = $types[$request];
        // default args - show all offers from current service
        $args = [
            'post_type' => 'offer',
            'meta_key' => 'service',
            'meta_value' => get_post_meta($post->ID, 'service')[0]
        ];
        $query = new \WP_Query($args);
        $posts = $query->posts;
        
        if($type == 'internet') {
            $typeDeLigne  = $_GET['typeDeLigne'];
            $tv = $_GET['television'];
            $telephonieMobile = $_GET['telephonieMobile'];
            $debit = $_GET['debit-100'];
            foreach($posts as $k => $offer) {
                $features = get_field('features',$offer->ID);
                if($debit) {
                    foreach($features as $feature) {
                        if($feature['filter_id'] == 'NET') {
                            $net = explode(':', $feature['filter_text']);
                            $net = trim(end($net));
                            if($debit == '99') {
                                if($net > $debit) {
                                    // unset all offer who are bigger then 99 mb/s
                                    unset($posts[$k]);
                                }
                            } 
                            else {
                                if($net < $debit) {
                                    // unset all offer who are smaller then 100 mb/s
                                    unset($posts[$k]);
                                }
                            } 
                        }
                    }
                }
            }
        }
        // echo '<pre>', var_dump($posts), '</pre>';exit();

        return [
            'type' => $type,
            'posts' => $posts,
        ];
    }
}
