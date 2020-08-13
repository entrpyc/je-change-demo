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
        dd('page build');
        return [];
    }

}
