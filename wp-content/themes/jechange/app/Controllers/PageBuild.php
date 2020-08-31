<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class PageBuild extends Controller
{
    public function __construct()
    {
    }

    public function data()
    {
        $data['id'] = get_the_ID();
        $data['page_build'] = get_field('page_build', $data['id']);

        return $data;
    }

}
