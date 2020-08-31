<?php

namespace App\Controllers;

use Sober\Controller\Controller;
use WP_Query;

class SingleProviderArticle extends Controller
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
        $id = get_the_ID();

        $data['id'] = get_the_title($id);
        $data['title'] = get_the_title($id);
        $data['url'] = get_permalink($id);
        $data['date'] = get_the_date('d/m/Y', $id);
        $data['image'] = get_the_post_thumbnail_url($id, 'full');

        // TODO Service Type & Service & Provider ...

        return $data;
    }


}
