<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class PageCron extends Controller
{
    public function __construct()
    {
    }

    private $services = [
        1 => 28, //animaux
        7 => 27, //auto
        8 => 41, //bourse
        2 => 22, //electricite
        3 => 23, //gaz
        4 => 35, //duale
        5 => 20, //internet
        6 => 36, //mobile
    ];
    private $serviceTypes = [
        1 => 17, //'telecoms',
        2 => 24, //'assurance',
        3 => 21, //'energie',
        4 => 26, //'credits',
        5 => 25, //'finances',
    ];


    public function data()
    {
        global $post;
        global $wpdb;
        if (!$post) {
            throw new \Exception('no post');
        }
        $api_id = $post->ID;

        /**
         * acf fields
         */
        $acf = [];
        $acf['field_5f3a67c541538'] = 'offer_id';
        $acf['field_5f3643a52befc'] = 'service';
        $acf['field_5f3643f82befd'] = 'provider';
        $acf['field_5f369724269d1'] = 'provider_logo';
        $acf['field_5f3a637ead988'] = 'provider_name';
        $acf['field_5f3695d05d79a'] = 'title';
        $acf['field_5f36961e5d79b'] = 'title_original';
        $acf['field_5f3696505d79c'] = 'description';
        $acf['field_5f36965f5d79d'] = 'description_original';
        $acf['field_5f3bcf11a9cd5'] = 'features'; // repeater
        $acf['field_5f3bd6bfd00dd'] = 'features_original';
        $acf['field_5f3a38f503168'] = 'pictograms';
        $acf['field_5f3a391003169'] = 'pictograms_original';
        $acf['field_5f3b7b5243c39'] = 'call_center_phone';
        $acf['field_5f3b7e6b43c3b'] = 'call_me_back';
        $acf['field_5f3b7e9243c3c'] = 'is_active';
        $acf['field_5f3b7eaf43c3d'] = 'show_on_provider';
        $acf['field_5f3b7e3d43c3a'] = 'price';
        $acf['field_5f3b7f1f43c3e'] = 'is_monthly';
        $acf['field_5f3b86e4b28d9'] = 'valid_from';
        $acf['field_5f3b86fcb28da'] = 'valid_to';

        $time = strtotime($post->post_modified);
        $time = 0; // for delete 
        $page = 1;
        do {
            $offers = $this->getOffers($post->post_content, $time, $page);
            $page++;
            if (!$offers) {
                return [];
            }

            $offers_ids = []; // check if already exists
            $posts_for_update = []; // post for update
            foreach ($offers['data'] as $offer) {
                $offers_ids[] = $offer['id'];
            }
            // get existing posts
            $args = array(
                'post_type' => 'offer',
                'meta_query' => array(
                    array(
                        'key' => 'offer_id', // provider_id
                        'value' => implode(',', $offers_ids),
                        'compare' => 'in',
                    )
                )
            );
            $query = new \WP_Query($args);

            // mark which offer ids are for update
            foreach ($query->posts as $k => $post) {
                $posts_for_update[get_post_meta($post->ID, 'offer_id')[0]] = $post->ID;
            }

            foreach ($offers['data'] as $offer) {
                /*
            // взимаме провайдер по записаното ID от апи-то
            $args = array(
            'meta_query' => array(
                array(
                    'key' => 'field_5f3a3e183cf26', // provider_id
                    'value' => $offer['provider']['id'],
                    'compare' => '=',
                )
            )
            );
            $query = new WP_Query($args);
            */


                // for the moment insert 
                // check first if offer exists

                $offer_id = $offer['id'];
                $service = $this->services[$offer['service']['id']];
                $title = wp_strip_all_tags($offer['name']);
                $title_original = $title;
                $provider = $offer['provider']['id'];
                $provider_name = $offer['provider']['name'];
                $provider_logo = $offer['provider']['logo']; // по принцип трябва да взимаме тъмба от провайдера. За момента е линка подаден от апито
                $description = $offer['description'];
                $description_original = $description;
                $call_center_phone = $offer['callCenterPhone'];
                $call_me_back = ($offer['callMeBack']) ? 1 : 0;
                $is_active = ($offer['isActive']) ? 1 : 0;
                $show_on_provider = ($offer['showOnProviderDetails']) ? 1 : 0;
                $price = '';
                if (isset($offer['electricityPrice'])) {
                    $price = $offer['electricityPrice'];
                } else if (isset($offer['gasPrices'])) {
                    $price = $offer['gasPrices'];
                } else if (isset($offer['telecomPrice'])) {
                    $price = $offer['telecomPrice'];
                } else if (isset($offer['price'])) {
                    $price = $offer['price'];
                }
                $is_monthly = ($offer['isMonthly']) ? 1 : 0;
                $valid_from = $offer['validFrom'] ?? null;
                $valid_to = $offer['validTo'] ?? null;

                $features = '';
                $pictograms = '';

                $features = [];
                $features_original = [];
                foreach ($offer['features'] as $key => $feature) {
                    $filter_id = '';
                    $filter_text = '';
                    foreach ($feature as $k => $serviceFeature) {
                        if ($k == 'id') {
                            $filter_id = $serviceFeature;
                        } else if ($k == 'text') {
                            $filter_text = $serviceFeature;
                        } else if ($k == 'pictogram') {
                            if ($serviceFeature) {
                                $pictograms .= "<div class='offer-pictogram'><img src='$serviceFeature[image]'>$serviceFeature[content]</div>";
                            }
                        }
                    }
                    //repeater
                    $features[] = [
                        'field_5f3bcf92e0aa6' => $filter_id,
                        'field_5f3bcffe2cd78' => $filter_text,
                    ];
                    $features_original[] = [
                        'field_5f3bd6bfd00de' => $filter_id,
                        'field_5f3bd6bfd00df' => $filter_text,
                    ];
                }
                $pictograms_original = $pictograms;


                $newOffer = array(
                    'post_title'    => $title,
                    'post_type'     => 'offer',
                    'post_status'   => 'publish',
                );

                if (isset($posts_for_update[$offer_id])) {
                    $postArr = [
                        'ID' => $posts_for_update[$offer_id],
                        'post_title' => $title,
                    ];

                    echo 'updatе ' . var_dump($offer_id);
                    $post_id = wp_update_post($postArr);
                    // echo 'updated ' . var_dump($post_id);
                } else {
                    // Insert the post into the database
                    $post_id = wp_insert_post($newOffer);
                    var_dump($post_id);
                }

                // when we have update the date is rewrited with the original content
                foreach ($acf as $field_key => $value) {
                    update_field($field_key, $$value, $post_id);
                }
            }
        } while (!$offers['isLastPage']);

        // update the cron page with new time (post_modified = lastSyncAt)
        $mysql_time_format = "Y-m-d H:i:s";

        $post_modified = gmdate($mysql_time_format);
        $post_modified_gmt = gmdate($mysql_time_format, (time() + get_option('gmt_offset') * HOUR_IN_SECONDS));

        $post_id = $api_id;
        $update_time = $wpdb->query("UPDATE $wpdb->posts SET post_modified = '{$post_modified}', post_modified_gmt = '{$post_modified_gmt}'  WHERE ID = {$post_id}");
        return [];
    }

    const api = 'https://jechange.sn77.net/api/v1/';
    private $bearer = null;
    public function api_login()
    {
        if ($_SESSION['jechange-api']) {
            $this->bearer = $_SESSION['jechange-api'];
            return;
        }
        $uri = 'login';
        $options  = [
            'json' => [
                'username' => 'admin@jechange.fr',
                'password' => '123',
            ],
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', self::api . $uri, $options);
            if ($response->getStatusCode() == 200) {
                $js = json_decode($response->getBody(), 1); // json
                $this->bearer = 'Bearer ' . $js['token'];
                $_SESSION['jechange-api'] = $this->bearer;
            }
        } catch (\Exception $e) {
            echo '<pre>', var_dump($e), '</pre>';
            exit();
        }
    }

    public function getOffers($service = null, $time = null, $page = 1)
    {
        // demo data
        // electricite
        // return json_decode('{"filters":{"GRN":{"condition":"eq","fieldAccessor":"booleanValue","value":"1"}},"timestamp":"2020-08-14T14:56:18+00:00","total":4,"page":1,"limit":10,"isLastPage":true,"data":[{"id":2,"provider":{"id":6,"name":"Total direct energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/1\/8\/18.png","createdAt":"2020-07-08T09:21:06+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Classique \u00e9lectricit\u00e9","description":"<div>&nbsp;<strong>Classique \u00e9lectricit\u00e9<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 5% sur le prix du kwh HT, par rapport au tarif r\u00e9glement\u00e9 en vigueur","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 1 an","pictogram":null}],"createdAt":"2020-07-08T09:31:05+00:00","updatedAt":"2020-07-08T09:31:05+00:00","isMonthly":"1","electricityPrice":"8031.88"},{"id":11,"provider":{"id":9,"name":"Ovo Energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/2\/5\/25.png","createdAt":"2020-07-08T14:41:05+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Ovo Electricit\u00e9 verte","description":"<div>&nbsp;<strong>Ovo Electricit\u00e9 verte<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 16% sur le prix du kwh HT par rapport au tarif r\u00e9glement\u00e9 en vigueur","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 10","pictogram":null}],"createdAt":"2020-07-08T14:41:32+00:00","updatedAt":"2020-07-08T14:41:32+00:00","isMonthly":"1","electricityPrice":"9719.88"},{"id":10,"provider":{"id":8,"name":"OHM \u00e9nergie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/2\/4\/24.png","createdAt":"2020-07-08T14:37:48+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"OHM Beaux Jours 1er avril au 31 octobre","description":"<div>&nbsp;<strong>OHM Beaux Jours 1er avril au 31 octobre<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 30% sur le prix du kwh HT par rapport au tarif r\u00e9glement\u00e9, du 1er avril au 31 octobre","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 2","pictogram":null}],"createdAt":"2020-07-08T14:38:11+00:00","updatedAt":"2020-07-08T14:38:11+00:00","isMonthly":"1","electricityPrice":"12550.6"},{"id":1,"provider":{"id":6,"name":"Total direct energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/1\/8\/18.png","createdAt":"2020-07-08T09:21:06+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Online \u00e9lectricit\u00e9","description":"<div>&nbsp;<strong>Online \u00e9lectricit\u00e9<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Non propos\u00e9","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 1 an","pictogram":null}],"createdAt":"2020-07-08T09:22:40+00:00","updatedAt":"2020-07-08T09:22:40+00:00","isMonthly":"1","electricityPrice":"13119.88"}]}', 1);
        // internet
        // return json_decode('{"filters":{"NET":{"condition":"between","fieldAccessor":"numericValue","min":"","max":""}},"timestamp":"2020-08-18T07:02:26+00:00","total":6,"page":1,"limit":10,"isLastPage":true,"data":[{"id":8,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Série Spéciale B&YOU 100Mo","description":"<div>&nbsp;<strong>Série Spéciale B&amp;YOU 100Mo</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":true,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 100.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/resolve/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:33:27+00:00","updatedAt":"2020-07-08T14:33:27+00:00","telecomPrice":"4.990000","isMonthly":"0","price":null},{"id":9,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Forfait Sensation 100Mo (12 mois)","description":"<div>&nbsp;<strong>Forfait Sensation 100Mo (12 mois)</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 90.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/resolve/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:34:17+00:00","updatedAt":"2020-07-08T14:34:17+00:00","telecomPrice":"12.990000","isMonthly":"0","price":null},{"id":7,"provider":{"id":1,"name":"Sosh","logo":"http://jechange.sn77.net/media/cache/original/1/1.png","createdAt":"2020-05-28T12:31:50+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Série Limitée Sosh 80Go","description":"<div>&nbsp;<strong>Série Limitée Sosh 80Go</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 800.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/resolve/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:23:30+00:00","updatedAt":"2020-07-08T14:23:30+00:00","telecomPrice":"14.000000","isMonthly":"0","price":null},{"id":3,"provider":{"id":7,"name":"Red by SFR","logo":"http://jechange.sn77.net/media/cache/original/2/0/20.png","createdAt":"2020-07-08T12:27:39+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Red XDSL TV","description":"<div>&nbsp;<strong>Red XDSL TV</strong></div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":true,"features":[{"id":"NET","text":"Débit (Mb/s): : 70.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Gratuit","pictogram":null},{"id":"LIN","text":"Type de ligne : Dégroupage total","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : oui","pictogram":null},{"id":"MDM","text":"Modem : SFR box","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : Disney Channel, NRJ Hits, Fashion TV","pictogram":null}],"createdAt":"2020-07-08T14:10:28+00:00","updatedAt":"2020-07-08T14:10:28+00:00","telecomPrice":"20.000000","isMonthly":"0","price":null},{"id":5,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"BBox Must","description":"<div>&nbsp;<strong>BBox Must</strong></div><div><br>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"NET","text":"Débit (Mb/s): : 70.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Gratuit","pictogram":null},{"id":"LIN","text":"Type de ligne : Non dégroupé","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : non","pictogram":null},{"id":"MDM","text":"Modem : BBox incluse dans le tarif","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : NRJ Hits, Fashion TV","pictogram":null}],"createdAt":"2020-07-08T14:13:55+00:00","updatedAt":"2020-07-08T14:13:55+00:00","telecomPrice":"33.990000","isMonthly":"0","price":null},{"id":6,"provider":{"id":4,"name":"Orange","logo":"http://jechange.sn77.net/media/cache/original/4/4.png","createdAt":"2020-05-28T16:01:49+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Livebox","description":"<div>&nbsp;<strong>Livebox</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"NET","text":"Débit (Mb/s): : 15.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Non proposé","pictogram":null},{"id":"LIN","text":"Type de ligne : Dégroupage total","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : non","pictogram":null},{"id":"MDM","text":"Modem : Coriolis Box","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : Disney Channel, NRJ Hits, Fashion TV, DW","pictogram":null}],"createdAt":"2020-07-08T14:16:04+00:00","updatedAt":"2020-07-08T14:16:04+00:00","telecomPrice":"36.990000","isMonthly":"0","price":null}]}', 1);
        // mobile
        return json_decode('{"filters":{"ILIMITECAL":{"condition":"eq","fieldAccessor":"booleanValue","value":"1"},"ILIMITESMS":{"condition":"eq","fieldAccessor":"booleanValue","value":"1"},"INTERNETPICT":{"condition":"between","fieldAccessor":"numericValue","min":"","max":""}},"timestamp":"2020-08-18T10:22:03+00:00","total":6,"page":1,"limit":10,"isLastPage":true,"data":[{"id":8,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Série Spéciale B&YOU 100Mo","description":"<div>&nbsp;<strong>Série Spéciale B&amp;YOU 100Mo</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":true,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 100.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:33:27+00:00","updatedAt":"2020-07-08T14:33:27+00:00","telecomPrice":"4.990000","isMonthly":"0","price":null},{"id":9,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Forfait Sensation 100Mo (12 mois)","description":"<div>&nbsp;<strong>Forfait Sensation 100Mo (12 mois)</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 90.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:34:17+00:00","updatedAt":"2020-07-08T14:34:17+00:00","telecomPrice":"12.990000","isMonthly":"0","price":null},{"id":7,"provider":{"id":1,"name":"Sosh","logo":"http://jechange.sn77.net/media/cache/original/1/1.png","createdAt":"2020-05-28T12:31:50+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Série Limitée Sosh 80Go","description":"<div>&nbsp;<strong>Série Limitée Sosh 80Go</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 800.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:23:30+00:00","updatedAt":"2020-07-08T14:23:30+00:00","telecomPrice":"14.000000","isMonthly":"0","price":null},{"id":3,"provider":{"id":7,"name":"Red by SFR","logo":"http://jechange.sn77.net/media/cache/original/2/0/20.png","createdAt":"2020-07-08T12:27:39+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Red XDSL TV","description":"<div>&nbsp;<strong>Red XDSL TV</strong></div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":true,"features":[{"id":"NET","text":"Débit (Mb/s): : 70.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Gratuit","pictogram":null},{"id":"LIN","text":"Type de ligne : Dégroupage total","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : oui","pictogram":null},{"id":"MDM","text":"Modem : SFR box","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : Disney Channel, NRJ Hits, Fashion TV","pictogram":null}],"createdAt":"2020-07-08T14:10:28+00:00","updatedAt":"2020-07-08T14:10:28+00:00","telecomPrice":"20.000000","isMonthly":"0","price":null},{"id":5,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"BBox Must","description":"<div>&nbsp;<strong>BBox Must</strong></div><div><br>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"NET","text":"Débit (Mb/s): : 70.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Gratuit","pictogram":null},{"id":"LIN","text":"Type de ligne : Non dégroupé","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : non","pictogram":null},{"id":"MDM","text":"Modem : BBox incluse dans le tarif","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : NRJ Hits, Fashion TV","pictogram":null}],"createdAt":"2020-07-08T14:13:55+00:00","updatedAt":"2020-07-08T14:13:55+00:00","telecomPrice":"33.990000","isMonthly":"0","price":null},{"id":6,"provider":{"id":4,"name":"Orange","logo":"http://jechange.sn77.net/media/cache/original/4/4.png","createdAt":"2020-05-28T16:01:49+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Livebox","description":"<div>&nbsp;<strong>Livebox</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"NET","text":"Débit (Mb/s): : 15.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Non proposé","pictogram":null},{"id":"LIN","text":"Type de ligne : Dégroupage total","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : non","pictogram":null},{"id":"MDM","text":"Modem : Coriolis Box","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : Disney Channel, NRJ Hits, Fashion TV, DW","pictogram":null}],"createdAt":"2020-07-08T14:16:04+00:00","updatedAt":"2020-07-08T14:16:04+00:00","telecomPrice":"36.990000","isMonthly":"0","price":null}]}', 1);
        if ($service === null) {
            return [];
            // throw new \Exception('no service');
        }
        if ($this->bearer === null) {
            $this->api_login();
        }
        $uri = "wordpress/$service/offers?page=$page&limit=10&lastSyncAt=$time";
        $options  = [
            'headers'        => [
                'Authorization' => $this->bearer,
            ],
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('get', self::api . $uri, $options);
            if ($response->getStatusCode() == 200) {
                // echo $response->getBody();exit;
                $js = json_decode($response->getBody(), 1); // json
                return $js;
            }
        } catch (\Exception $e) {
            echo '<pre>', var_dump($e), '</pre>';
            exit();
        }
    }
}
