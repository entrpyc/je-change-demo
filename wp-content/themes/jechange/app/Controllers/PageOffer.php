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
        //do_action('sync_offers');

        global $post;
        global $wp;

        $request = $wp->request;
        $types = [
            'energie/electricite/comparatif' => 'electricite',
            'energie/duale/comparatif' => 'duale',
            'energie/gaz/comparatif' => 'gaz',
            'telecom/internet/comparatif' => 'internet',
            'telecom/mobile/forfait-bloque' => 'mobile', // limited
            'telecom/mobile/forfaitillimite' => 'mobile', //unlimited
        ];
        $type = $types[$request];
        // default args - show all offers from current service
        $args = [
            'post_type' => 'offer',
            'meta_query' => [
                [
                    'key' => 'service',
                    'value' => get_post_meta($post->ID, 'service')[0],
                ],
            ],
            'orderby' => 'meta_value_num',
            'order' => 'asc',
            'meta_key' => 'price'
        ];
        //todo filter by valid from and valid to
        $query = new \WP_Query($args);
        $posts = $query->posts;

        // internet filters
        $typeDeLigne  = $_GET['typeDeLigne'] ?? null;
        $tv = $_GET['television'] ?? null;
        $telephonieMobile = $_GET['telephonieMobile'] ?? null;
        $debit = $_GET['debit-100'] ?? null;
        // /internet

        // electricite filters
        $pourcentVert = $_GET['pourcentVert'] ?? null; // green energy
        // /electricite

        foreach ($posts as $k => $offer) {
            $features = get_field('features', $offer->ID);

            $noTv = true; // offer don't include tv service
            $noGrn = true; // offer don't include green energy
            $forfaitlimite = true; // is limited offer - for mobile

            foreach ($features as $feature) {
                // internet
                // filter Débits
                if ($debit) {
                    if ($feature['filter_id'] == 'NET') {
                        $filter = explode(':', $feature['filter_text']);
                        $filter = trim(end($filter));
                        if ($debit < 100) {
                            //jusqu'à 100 Mb/s
                            if ($filter > $debit) {
                                // unset all offer who are bigger then 99 mb/s
                                unset($posts[$k]);
                            }
                        } else {
                            // à partir de 100 Mb/s
                            if ($filter < $debit) {
                                // unset all offer who are smaller then 100 mb/s
                                unset($posts[$k]);
                            }
                        }
                    }
                }
                // /filter Débits

                // filter Dégroupage
                if ($typeDeLigne) {
                    if ($feature['filter_id'] == 'LIN') {
                        if ($typeDeLigne == 1) {
                            // dégroupé
                            if (mb_strpos($feature['filter_text'], 'Dégroupage total') === false) {
                                unset($posts[$k]);
                            }
                        } else {
                            // non dégroupé
                            if (mb_strpos($feature['filter_text'], 'Non dégroupé') === false) {
                                unset($posts[$k]);
                            }
                        }
                    }
                }
                // /filter Dégroupage

                // filter tv
                if ($feature['filter_id'] == 'TVCHAN') {
                    $noTv = false;
                }

                // electricite
                // filter green energy

                if ($feature['filter_id'] == 'GRN') {
                    $noGrn = false;
                }

                // mobile
                // filter limited plan
                if ($feature['filter_id'] == 'ILIMITECAL') {
                    $forfaitlimite = false;
                }
            }
            // filter tv
            if ($tv && $noTv) {
                unset($posts[$k]);
            }

            // electricite
            // filter green energy
            if ($pourcentVert == 100 && $noGrn) {
                unset($posts[$k]);
            }

            // todo telephonieMobile Appels illimités vers les mobiles - internet
            // todo prixFixe - electricite


            // mobile
            if ($request == 'telecom/mobile/forfait-bloque') { // limited
                if(!$forfaitlimite) {
                    unset($posts[$k]);
                }
            } else  if ($request == 'telecom/mobile/forfaitillimite') { // unlimited
                if($forfaitlimite) {
                    unset($posts[$k]);
                }
            }


        }
        // echo '<pre>', var_dump($posts), '</pre>';exit();


        return [
            'type' => $type,
            'posts' => $posts,
            'page_build' => get_field('page_build',  get_the_ID()) // PAGE BUILD
        ];
    }
}
