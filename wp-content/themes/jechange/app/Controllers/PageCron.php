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
        4 => 35, //duale
        2 => 22, //electricite
        3 => 23, //gaz
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
        if(!$post) {
            throw new \Exception('no post');
        }
        $api_id = $post->ID;
        $time = strtotime($post->post_modified);
        $time = 0;
        $offers = $this->getOffers($post->post_content, $time);
        if(!$offers) {
            return [];
        }
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
        $acf['field_5f3696c9dcc84'] = 'features';
        $acf['field_5f3696dbdcc85'] = 'features_original';
        $acf['field_5f3a38f503168'] = 'pictograms';
        $acf['field_5f3a391003169'] = 'pictograms_original';

        $offers_ids = [];// check if already exists
        $posts_for_update = [];// post for update
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

        // mark ids for update
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
            $features = '';
            $pictograms = '';
            foreach ($offer['features'] as $key => $feature) {
                foreach ($feature as $k => $serviceFeature) {
                    if ($k == 'text') {
                        $features .= "<div class='offer-features'>$serviceFeature</div>";
                    } else if ($k == 'pictogram') {
                        if ($serviceFeature) {
                            $pictograms .= "<div class='offer-pictogram'><img src='$serviceFeature[image]'>$serviceFeature[content]</div>";
                        }
                    }
                }
            }
            $pictograms_original = $pictograms;
            $features_original = $features;


            $newOffer = array(
                'post_title'    => $title,
                'post_type'     => 'offer',
                'post_status'   => 'publish',
            );

            if(isset($posts_for_update[$offer_id])) {
                $postArr = [
                    'ID' => $posts_for_update[$offer_id],
                    'post_title' => $title,
                ];
                
                echo 'updatе ' . var_dump($offer_id);
                $post_id = wp_update_post( $postArr );
                // echo 'updated ' . var_dump($post_id);
            }
            else {
                // Insert the post into the database
                $post_id = wp_insert_post($newOffer);
                var_dump($post_id);
                
            }

            // when we have update the date is rewrited with the original content
            foreach ($acf as $field_key => $value) {
                update_field($field_key, $$value, $post_id);
            }
        }

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

    public function getOffers($service = null, $time = null)
    {
        // demo data
        return json_decode('{"filters":{"GRN":{"condition":"eq","fieldAccessor":"booleanValue","value":"1"}},"timestamp":"2020-08-14T14:56:18+00:00","total":4,"page":1,"limit":10,"isLastPage":true,"data":[{"id":2,"provider":{"id":6,"name":"Total direct energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/1\/8\/18.png","createdAt":"2020-07-08T09:21:06+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Classique \u00e9lectricit\u00e9","description":"<div>&nbsp;<strong>Classique \u00e9lectricit\u00e9<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 5% sur le prix du kwh HT, par rapport au tarif r\u00e9glement\u00e9 en vigueur","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 1 an","pictogram":null}],"createdAt":"2020-07-08T09:31:05+00:00","updatedAt":"2020-07-08T09:31:05+00:00","isMonthly":"1","electricityPrice":"8031.88"},{"id":11,"provider":{"id":9,"name":"Ovo Energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/2\/5\/25.png","createdAt":"2020-07-08T14:41:05+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Ovo Electricit\u00e9 verte","description":"<div>&nbsp;<strong>Ovo Electricit\u00e9 verte<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 16% sur le prix du kwh HT par rapport au tarif r\u00e9glement\u00e9 en vigueur","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 10","pictogram":null}],"createdAt":"2020-07-08T14:41:32+00:00","updatedAt":"2020-07-08T14:41:32+00:00","isMonthly":"1","electricityPrice":"9719.88"},{"id":10,"provider":{"id":8,"name":"OHM \u00e9nergie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/2\/4\/24.png","createdAt":"2020-07-08T14:37:48+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"OHM Beaux Jours 1er avril au 31 octobre","description":"<div>&nbsp;<strong>OHM Beaux Jours 1er avril au 31 octobre<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 30% sur le prix du kwh HT par rapport au tarif r\u00e9glement\u00e9, du 1er avril au 31 octobre","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 2","pictogram":null}],"createdAt":"2020-07-08T14:38:11+00:00","updatedAt":"2020-07-08T14:38:11+00:00","isMonthly":"1","electricityPrice":"12550.6"},{"id":1,"provider":{"id":6,"name":"Total direct energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/1\/8\/18.png","createdAt":"2020-07-08T09:21:06+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Online \u00e9lectricit\u00e9","description":"<div>&nbsp;<strong>Online \u00e9lectricit\u00e9<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Non propos\u00e9","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 1 an","pictogram":null}],"createdAt":"2020-07-08T09:22:40+00:00","updatedAt":"2020-07-08T09:22:40+00:00","isMonthly":"1","electricityPrice":"13119.88"}]}', 1);
        //
        if ($service === null) {
            return [];
            // throw new \Exception('no service');
        }
        if ($this->bearer === null) {
            $this->api_login();
        }
        $uri = "wordpress/$service/offers?page=1&limit=10&lastSyncAt=$time";
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
