<?php

namespace App;

use GuzzleHttp\Exception\GuzzleException;
use WP_Query;

class SyncOffers
{
    const API_URL = 'https://jechange.sn77.net/api/v1/';
    private $bearer = null;

    private $lastSyncFile;
    private $cronLogFile;

    /**
     * Mapping of Service Types between API and WP
     * @var array
     */
    const MAP_SERVICE_TYPES = [
        1 => 17, //'telecoms',
        2 => 24, //'assurance',
        3 => 21, //'energie',
        4 => 26, //'credits',
        5 => 25, //'finances',
    ];

    /**
     * Mapping of Services between API and WP
     * @var array
     */
    const MAP_SERVICES = [
        1 => 28, //animaux
        7 => 27, //auto
        8 => 41, //bourse
        2 => 22, //electricite
        3 => 23, //gaz
        4 => 35, //duale
        5 => 20, //internet
        6 => 36, //mobile
    ];

    /**
     * Provider ACF Custom Fields
     * @var array
     */
    const ACF_PROVIDER_FIELDS = [
        'field_5f323c00dd861' => 'service_type',
        'field_5f3a3e183cf26' => 'provider_id',
        'field_5f43b5d9aae41' => 'provider_name_original',
        'field_5f43a607bc6d1' => 'provider_logo',
        'field_5f43a68bbc6d6' => 'provider_logo_original',
        'field_5f43a625bc6d2' => 'provider_description',
        'field_5f43a64fbc6d4' => 'provider_description_original',
        'field_5f43a63fbc6d3' => 'provider_short_description',
        'field_5f43a670bc6d5' => 'provider_short_description_original',
    ];

    /**
     * Offer ACF Custom Fields
     * @var array
     */
    const ACF_OFFER_FIELDS = [
        'field_5f3a67c541538' => 'offer_id',
        'field_5f3643a52befc' => 'service',
        'field_5f4512ecb8497' => 'provider_id', // ACF Provider API ID Custom Field
        'field_5f3695d05d79a' => 'title',
        'field_5f36961e5d79b' => 'title_original',
        'field_5f3696505d79c' => 'description',
        'field_5f36965f5d79d' => 'description_original',
        'field_5f3bcf11a9cd5' => 'features', // repeater
        'field_5f3bd6bfd00dd' => 'features_original',
        'field_5f3a38f503168' => 'pictograms',
        'field_5f3a391003169' => 'pictograms_original',
        'field_5f3b7b5243c39' => 'call_center_phone',
        'field_5f3b7e6b43c3b' => 'call_me_back',
        'field_5f3b7e9243c3c' => 'is_active',
        'field_5f3b7eaf43c3d' => 'show_on_provider',
        'field_5f3b7e3d43c3a' => 'price',
        'field_5f3b7f1f43c3e' => 'is_monthly',
        'field_5f3b86e4b28d9' => 'valid_from',
        'field_5f3b86fcb28da' => 'valid_to',
    ];

    /**
     * ACF Service Type Custom Field
     * Used in the Provider permalink creation on insert/update
     * @var string
     */
    const ACF_SERVICE_TYPE_FIELD = 'field_5f323c00dd861';


    public function __construct()
    {
        $this->lastSyncFile = wp_get_upload_dir()['basedir'] . '/sync_offers_time.php';
        $this->cronLogFile = wp_get_upload_dir()['basedir'] . '/cron_log.txt';
        try {
            file_put_contents($this->cronLogFile, "\nStart - " . date('d.m.Y H:i:s') . PHP_EOL, FILE_APPEND);
            $start = microtime(true);
            $this->sync();
            $time_elapsed_secs = microtime(true) - $start;
            file_put_contents($this->cronLogFile, 'FINISHED FOR ' . $time_elapsed_secs . ' DATE:' . date('d.m.Y H:i:s'), FILE_APPEND);
        } catch (\Exception $e) {
            $log = 'Caught exception: ' .  $e->getMessage() . "\n";
            file_put_contents($this->cronLogFile, 'error', FILE_APPEND);
            file_put_contents($this->cronLogFile, $log . '  DATE:' . date('d.m.Y H:i:s'), FILE_APPEND);
        }
    }

    public function sync()
    {
        // TODO Sync Offers AND CONNECT THEM WITH THE PROVIDERS

        // Using file to solve the problem with syncing several times per day // TODO To be discussed
        $lastSyncTime = 0;
        if(file_exists($this->lastSyncFile)){
            $lastSyncTime = include_once $this->lastSyncFile; // return unix timestamp
        }
        // Sync Providers
        $this->syncProviders($lastSyncTime);

        // Sync Offers
        $services = [
            'electricite',
            'mobile',
            'internet'
        ];
        foreach ($services as $service) {
            $this->syncOffers($service, 0);
        }

        // Update last sync time file
        $this->updateTimeFile();
    }

    /**
     * Keep Last Sync Time in a file
     * TODO To be discussed
     */
    private function updateTimeFile()
    {
        $time = time();
        $date = date('d.m.Y H:i:s', $time);
        $content = "<?php return $time; // last sync $date ";
        file_put_contents($this->lastSyncFile, $content);
    }

    /**
     * Sync Offers from API with Wordpress
     * Insert or Update Offers
     * @endpoint GET /api/{version}/wordpress/{serviceSlug}/offers
     * @param $service_slug
     * @param $lastSyncTime
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function syncOffers($service_slug, $lastSyncTime)
    {
        $page = 1; // api results
        $log = "\nservice: $service_slug - last sync: " . date('d.m.Y H:i:s', $lastSyncTime) . " \n";

        // do get offers from api until lastPage
        do {
            $offers = $this->getOffers($service_slug, $lastSyncTime, $page);

            $page++; // next page
            if (!$offers) {
                return false;
            }

            $offers_ids = []; // check if already exists
            $posts_for_update = []; // posts for update
            foreach ($offers['data'] as $offer) {
                $offers_ids[] = $offer['id'];
            }
            // get existing offers
            $args = array(
                'post_type' => 'offer',
                'meta_query' => array(
                    array(
                        'key' => 'offer_id',
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

                // we get provider based on provider api ID custom field
                $args = array(
                    'post_type' => 'providers',
                    'meta_query' => array(
                        array(
                            'key' => 'provider_id',
                            'value' => $offer['provider']['id'],
                            'compare' => '=',
                        )
                    )
                );
                $query = new WP_Query($args);


                $offer_id = $offer['id'];
                $service = self::MAP_SERVICES[$offer['service']['id']];
                $provider_id = $query->posts[0] ?? 0; // get based on the query first result
                $title = wp_strip_all_tags($offer['name']);
                $title_original = $title;
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
                            $pictogram_image_url = $serviceFeature['image']['url'];
                            // $image_id = $this->uploadImage($pictogram_image_url) ?? 0; // upload pictogram, return false if already in uploads
                            // TODO save pictogram to WP
                            if ($serviceFeature) {
                                $pictograms .= "<div class='offer-pictogram'><img src='$pictogram_image_url'>$pictogram_image_url</div>";
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

                if (isset($posts_for_update[$offer_id])) {
                    $postArr = [
                        'ID' => $posts_for_update[$offer_id],
                        // 'post_title' => $title,
                    ];
                    $post_id = wp_update_post($postArr);
                    $log .= "[Update] $post_id $title\n";
                    foreach (self::ACF_OFFER_FIELDS as $field_key => $value) {
                        // update only original content
                        if (key_exists($field_key . '_original', self::ACF_OFFER_FIELDS)) {
                            continue;
                        }
                        update_field($field_key, $$value, $post_id);
                    }
                } else {
                    // Insert the post into the database
                    $newOffer = array(
                        'post_title'    => $title,
                        'post_type'     => 'offer',
                        'post_status'   => 'publish',
                    );
                    $post_id = wp_insert_post($newOffer);
                    $log .= "[Insert] Offer $post_id $title\n";
                    foreach (self::ACF_OFFER_FIELDS as $field_key => $value) {
                        update_field($field_key, $$value, $post_id); // &&value example: using vars like $pictograms_original
                    }
                }
            }
        } while (!$offers['isLastPage']);
        
        file_put_contents($this->cronLogFile, $log, FILE_APPEND);

        return true;
    }

    /**
     * Sync Providers from API with Wordpress
     * Insert or Update Providers
     * @endpoint GET /api/{version}/providers
     * @param $lastSyncTime
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function syncProviders($lastSyncTime)
    {
        $page = 1; // api results
        $log = "\n ---- PROVIDERS ---- last sync: " . date('d.m.Y H:i:s', $lastSyncTime) . " \n";

        // do get providers from api until lastPage
        do {
            $providers = $this->getProviders($lastSyncTime, $page);

            $page++; // next page
            if (!$providers) {
                return false;
            }

            $providers_ids = []; // check if already exists in wp
            $posts_for_update = []; // posts for update
            foreach ($providers['data'] as $provider) {
                $providers_ids[] = $provider['id'];
            }
            // get existing posts
            $args = array(
                'post_type' => 'providers',
                'meta_query' => array(
                    array(
                        'key' => 'provider_id', // provider_id
                        'value' => implode(',', $providers_ids),
                        'compare' => 'in',
                    )
                )
            );
            $query = new \WP_Query($args);

            // mark which offer ids are for update
            foreach ($query->posts as $k => $post) {
                $posts_for_update[get_post_meta($post->ID, 'provider_id')[0]] = $post->ID;
            }

            foreach ($providers['data'] as $provider) {

                $provider_id = $provider['id'];
                $provider_slug = $provider['slug'];
                $provider_name_original = $provider['name'];
                $provider_description = $provider['description'];
                $provider_description_original = $provider['description'];
                $provider_short_description = $provider['short_description'];
                $provider_short_description_original = $provider['short_description'];
                $provider_api_logo_href = $provider['logo']['url'];
                $image_id = $this->uploadImage($provider_api_logo_href) ?? 0; // upload logo, return false if already in uploads
                $provider_logo = $image_id;
                $provider_logo_original = $image_id;

                // get service type from the first provider service, then get corresponding service type object in wp
                // we need the service type to be able to create the permalink of the provider
                $serviceTypeWpId = self::MAP_SERVICE_TYPES[$providers['data'][0]['services'][0]['serviceType']['id']] ?? self::MAP_SERVICE_TYPES[1]; // just for protection

                $service_type = $serviceTypeWpId; // pass to update_field $$value

                $providers_ids[] = $provider['id'];

                if (isset($posts_for_update[$provider['id']])) {
                    $postArr = [
                        'ID' => $posts_for_update[$provider['id']],
                        'acf' => [
                            self::ACF_SERVICE_TYPE_FIELD => $serviceTypeWpId  // pass serviceType acf field to wp_insert_post_data to abe able to create the slug
                        ]
                    ];
                    $post_id = wp_update_post($postArr);
                    $log .= "[Update] $post_id $provider_name_original\n";
                    foreach (self::ACF_PROVIDER_FIELDS as $field_key => $value) {
                        // update only original content
                        if (key_exists($field_key . '_original', self::ACF_PROVIDER_FIELDS)) {
                            continue;
                        }
                        if($$value !== 0) {
                            update_field($field_key, $$value, $post_id);
                        }
                    }
                } else {
                    // Insert the post into the database
                    $newProvider = array(
                        'post_title'    => $provider_name_original,
                        'post_type'     => 'providers',
                        'post_status'   => 'publish',
                        'acf' => [
                            self::ACF_SERVICE_TYPE_FIELD => $serviceTypeWpId  // pass serviceType acf field to wp_insert_post_data to abe able to create the slug
                        ],
                        'sync' => true  // important for wp_insert_post_data filter to be able to create the slug
                    );
                    $newProvider = apply_filters( 'wp_insert_post_data', $newProvider, $newProvider ); // call our modified hook in admin.php to return the right slug in the data
                    $post_id = wp_insert_post($newProvider);

                    $log .= "[Insert] provider $post_id $provider_name_original\n";
                    foreach (self::ACF_PROVIDER_FIELDS as $field_key => $value) {
                        if($$value !== 0) { // if image_id is not 0
                            update_field($field_key, $$value, $post_id); // &&value example: using vars like $pictograms_original
                        }
                    }
                }
            }
        } while (!$providers['isLastPage']);

        file_put_contents($this->cronLogFile, $log, FILE_APPEND);

        return true;
    }

    /**
     * API Login Authorization
     * @endpoint POST /api/{version}/login
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apiLogin()
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
            $response = $client->request('POST', self::API_URL . $uri, $options);
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

    /**
     * Get Offers From the API
     * @endpoint GET /api/{version}/wordpress/{serviceSlug}/offers
     * @doc /api/doc
     *
     * @param null $service
     * @param null $time
     * @param int $page
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getOffers($service = null, $time = null, $page = 1)
    {
        // demo data
        // electricite
        // return json_decode('{"filters":{"GRN":{"condition":"eq","fieldAccessor":"booleanValue","value":"1"}},"timestamp":"2020-08-14T14:56:18+00:00","total":4,"page":1,"limit":10,"isLastPage":true,"data":[{"id":2,"provider":{"id":6,"name":"Total direct energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/1\/8\/18.png","createdAt":"2020-07-08T09:21:06+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Classique \u00e9lectricit\u00e9","description":"<div>&nbsp;<strong>Classique \u00e9lectricit\u00e9<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 5% sur le prix du kwh HT, par rapport au tarif r\u00e9glement\u00e9 en vigueur","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 1 an","pictogram":null}],"createdAt":"2020-07-08T09:31:05+00:00","updatedAt":"2020-07-08T09:31:05+00:00","isMonthly":"1","electricityPrice":"8031.88"},{"id":11,"provider":{"id":9,"name":"Ovo Energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/2\/5\/25.png","createdAt":"2020-07-08T14:41:05+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Ovo Electricit\u00e9 verte","description":"<div>&nbsp;<strong>Ovo Electricit\u00e9 verte<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 16% sur le prix du kwh HT par rapport au tarif r\u00e9glement\u00e9 en vigueur","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 10","pictogram":null}],"createdAt":"2020-07-08T14:41:32+00:00","updatedAt":"2020-07-08T14:41:32+00:00","isMonthly":"1","electricityPrice":"9719.88"},{"id":10,"provider":{"id":8,"name":"OHM \u00e9nergie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/2\/4\/24.png","createdAt":"2020-07-08T14:37:48+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"OHM Beaux Jours 1er avril au 31 octobre","description":"<div>&nbsp;<strong>OHM Beaux Jours 1er avril au 31 octobre<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 30% sur le prix du kwh HT par rapport au tarif r\u00e9glement\u00e9, du 1er avril au 31 octobre","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 2","pictogram":null}],"createdAt":"2020-07-08T14:38:11+00:00","updatedAt":"2020-07-08T14:38:11+00:00","isMonthly":"1","electricityPrice":"12550.6"},{"id":1,"provider":{"id":6,"name":"Total direct energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/1\/8\/18.png","createdAt":"2020-07-08T09:21:06+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Online \u00e9lectricit\u00e9","description":"<div>&nbsp;<strong>Online \u00e9lectricit\u00e9<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Non propos\u00e9","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 1 an","pictogram":null}],"createdAt":"2020-07-08T09:22:40+00:00","updatedAt":"2020-07-08T09:22:40+00:00","isMonthly":"1","electricityPrice":"13119.88"}]}', 1);
        // internet
        // return json_decode('{"filters":{"NET":{"condition":"between","fieldAccessor":"numericValue","min":"","max":""}},"timestamp":"2020-08-18T07:02:26+00:00","total":6,"page":1,"limit":10,"isLastPage":true,"data":[{"id":8,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Série Spéciale B&YOU 100Mo","description":"<div>&nbsp;<strong>Série Spéciale B&amp;YOU 100Mo</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":true,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 100.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/resolve/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:33:27+00:00","updatedAt":"2020-07-08T14:33:27+00:00","telecomPrice":"4.990000","isMonthly":"0","price":null},{"id":9,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Forfait Sensation 100Mo (12 mois)","description":"<div>&nbsp;<strong>Forfait Sensation 100Mo (12 mois)</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 90.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/resolve/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:34:17+00:00","updatedAt":"2020-07-08T14:34:17+00:00","telecomPrice":"12.990000","isMonthly":"0","price":null},{"id":7,"provider":{"id":1,"name":"Sosh","logo":"http://jechange.sn77.net/media/cache/original/1/1.png","createdAt":"2020-05-28T12:31:50+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Série Limitée Sosh 80Go","description":"<div>&nbsp;<strong>Série Limitée Sosh 80Go</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/resolve/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 800.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/resolve/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:23:30+00:00","updatedAt":"2020-07-08T14:23:30+00:00","telecomPrice":"14.000000","isMonthly":"0","price":null},{"id":3,"provider":{"id":7,"name":"Red by SFR","logo":"http://jechange.sn77.net/media/cache/original/2/0/20.png","createdAt":"2020-07-08T12:27:39+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Red XDSL TV","description":"<div>&nbsp;<strong>Red XDSL TV</strong></div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":true,"features":[{"id":"NET","text":"Débit (Mb/s): : 70.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Gratuit","pictogram":null},{"id":"LIN","text":"Type de ligne : Dégroupage total","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : oui","pictogram":null},{"id":"MDM","text":"Modem : SFR box","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : Disney Channel, NRJ Hits, Fashion TV","pictogram":null}],"createdAt":"2020-07-08T14:10:28+00:00","updatedAt":"2020-07-08T14:10:28+00:00","telecomPrice":"20.000000","isMonthly":"0","price":null},{"id":5,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"BBox Must","description":"<div>&nbsp;<strong>BBox Must</strong></div><div><br>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"NET","text":"Débit (Mb/s): : 70.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Gratuit","pictogram":null},{"id":"LIN","text":"Type de ligne : Non dégroupé","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : non","pictogram":null},{"id":"MDM","text":"Modem : BBox incluse dans le tarif","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : NRJ Hits, Fashion TV","pictogram":null}],"createdAt":"2020-07-08T14:13:55+00:00","updatedAt":"2020-07-08T14:13:55+00:00","telecomPrice":"33.990000","isMonthly":"0","price":null},{"id":6,"provider":{"id":4,"name":"Orange","logo":"http://jechange.sn77.net/media/cache/original/4/4.png","createdAt":"2020-05-28T16:01:49+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Livebox","description":"<div>&nbsp;<strong>Livebox</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"NET","text":"Débit (Mb/s): : 15.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Non proposé","pictogram":null},{"id":"LIN","text":"Type de ligne : Dégroupage total","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : non","pictogram":null},{"id":"MDM","text":"Modem : Coriolis Box","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : Disney Channel, NRJ Hits, Fashion TV, DW","pictogram":null}],"createdAt":"2020-07-08T14:16:04+00:00","updatedAt":"2020-07-08T14:16:04+00:00","telecomPrice":"36.990000","isMonthly":"0","price":null}]}', 1);
        // mobile
        // return json_decode('{"filters":{"ILIMITECAL":{"condition":"eq","fieldAccessor":"booleanValue","value":"1"},"ILIMITESMS":{"condition":"eq","fieldAccessor":"booleanValue","value":"1"},"INTERNETPICT":{"condition":"between","fieldAccessor":"numericValue","min":"","max":""}},"timestamp":"2020-08-18T10:22:03+00:00","total":6,"page":1,"limit":10,"isLastPage":true,"data":[{"id":8,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Série Spéciale B&YOU 100Mo","description":"<div>&nbsp;<strong>Série Spéciale B&amp;YOU 100Mo</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":true,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 100.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:33:27+00:00","updatedAt":"2020-07-08T14:33:27+00:00","telecomPrice":"4.990000","isMonthly":"0","price":null},{"id":9,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Forfait Sensation 100Mo (12 mois)","description":"<div>&nbsp;<strong>Forfait Sensation 100Mo (12 mois)</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 90.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:34:17+00:00","updatedAt":"2020-07-08T14:34:17+00:00","telecomPrice":"12.990000","isMonthly":"0","price":null},{"id":7,"provider":{"id":1,"name":"Sosh","logo":"http://jechange.sn77.net/media/cache/original/1/1.png","createdAt":"2020-05-28T12:31:50+00:00"},"service":{"id":6,"name":"Offres Mobile","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Série Limitée Sosh 80Go","description":"<div>&nbsp;<strong>Série Limitée Sosh 80Go</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"ILIMITECAL","text":"Illimités","pictogram":{"content":"Illimités","image":"http://jechange.sn77.net/media/cache/original/2/1/21.png"}},{"id":"ILIMITESMS","text":"SMS et MMS illimités","pictogram":{"content":"SMS et MMS illimités","image":"http://jechange.sn77.net/media/cache/original/2/2/22.png"}},{"id":"INTERNETPICT","text":"Internet 800.00 MB inclus (Débit réduit au-delà)","pictogram":{"content":"Internet %s MB inclus (Débit réduit au-delà)","image":"http://jechange.sn77.net/media/cache/original/2/3/23.png"}}],"createdAt":"2020-07-08T14:23:30+00:00","updatedAt":"2020-07-08T14:23:30+00:00","telecomPrice":"14.000000","isMonthly":"0","price":null},{"id":3,"provider":{"id":7,"name":"Red by SFR","logo":"http://jechange.sn77.net/media/cache/original/2/0/20.png","createdAt":"2020-07-08T12:27:39+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Red XDSL TV","description":"<div>&nbsp;<strong>Red XDSL TV</strong></div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":true,"features":[{"id":"NET","text":"Débit (Mb/s): : 70.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Gratuit","pictogram":null},{"id":"LIN","text":"Type de ligne : Dégroupage total","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : oui","pictogram":null},{"id":"MDM","text":"Modem : SFR box","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : Disney Channel, NRJ Hits, Fashion TV","pictogram":null}],"createdAt":"2020-07-08T14:10:28+00:00","updatedAt":"2020-07-08T14:10:28+00:00","telecomPrice":"20.000000","isMonthly":"0","price":null},{"id":5,"provider":{"id":3,"name":"Bouygues Telecom","logo":"http://jechange.sn77.net/media/cache/original/3/3.png","createdAt":"2020-05-28T16:00:47+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"BBox Must","description":"<div>&nbsp;<strong>BBox Must</strong></div><div><br>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"NET","text":"Débit (Mb/s): : 70.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Gratuit","pictogram":null},{"id":"LIN","text":"Type de ligne : Non dégroupé","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : non","pictogram":null},{"id":"MDM","text":"Modem : BBox incluse dans le tarif","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : NRJ Hits, Fashion TV","pictogram":null}],"createdAt":"2020-07-08T14:13:55+00:00","updatedAt":"2020-07-08T14:13:55+00:00","telecomPrice":"33.990000","isMonthly":"0","price":null},{"id":6,"provider":{"id":4,"name":"Orange","logo":"http://jechange.sn77.net/media/cache/original/4/4.png","createdAt":"2020-05-28T16:01:49+00:00"},"service":{"id":5,"name":"Offres Internet","callCenter":"0800 811 911","pricingType":"telecom"},"name":"Livebox","description":"<div>&nbsp;<strong>Livebox</strong>&nbsp;</div>","isActive":true,"sites":["http://jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"NET","text":"Débit (Mb/s): : 15.00","pictogram":null},{"id":"FRES","text":"Frais de mise en service : Non proposé","pictogram":null},{"id":"LIN","text":"Type de ligne : Dégroupage total","pictogram":null},{"id":"QPLAY","text":"Offre quadruple play : non","pictogram":null},{"id":"MDM","text":"Modem : Coriolis Box","pictogram":null},{"id":"TVCHAN","text":"Liste des chaînes et bouquets : Disney Channel, NRJ Hits, Fashion TV, DW","pictogram":null}],"createdAt":"2020-07-08T14:16:04+00:00","updatedAt":"2020-07-08T14:16:04+00:00","telecomPrice":"36.990000","isMonthly":"0","price":null}]}', 1);
        if ($service === null) {
            return [];
            // throw new \Exception('no service');
        }
        if ($this->bearer === null) {
            $this->apiLogin();
        }
        $uri = "wordpress/$service/offers?page=$page&limit=10&lastSyncAt=$time";
        $options  = [
            'headers'        => [
                'Authorization' => $this->bearer,
            ],
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('get', self::API_URL . $uri, $options);
            if ($response->getStatusCode() == 200) {
                // echo $response->getBody();exit;
                $js = json_decode($response->getBody(), 1); // json
                return $js;
            }
        } catch (\Exception $e) {
            // TODO Log Exception
            echo '<pre>', var_dump($e), '</pre>';
            exit();
        }
        return true;
    }

    /**
     * Get Providers From the API
     * @endpoint GET /api/{version}/providers
     * @doc /api/doc
     * @param null $time
     * @param int $page
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getProviders($time = null, $page = 1)
    {
        if ($this->bearer === null) {
            $this->apiLogin();
        }
        $uri = "providers?page=$page&limit=50&lastSyncAt=$time";
        $options  = [
            'headers'        => [
                'Authorization' => $this->bearer,
            ],
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('get', self::API_URL . $uri, $options);
            if ($response->getStatusCode() == 200) {
                $js = json_decode($response->getBody(), 1); // json
                return $js;
            }
        } catch (\Exception $e) {
            // TODO Log Exception
            echo '<pre>', var_dump($e), '</pre>';
            exit();
        }
        return true;
    }

    /**
     * Upload Image From External URL and return image_id
     * Do not upload if file with the same name exists
     * @param $image_url  string
     * @return string|\WP_Error
     */
    private function uploadImage( $image_url )
    {
        $upload_dir = wp_upload_dir();
        $image_data = @file_get_contents( $image_url );

        if(!$image_data){
            return false;
        }

        $path = explode('.',$image_url);
        $extension = end($path);

        $filename = basename( $image_url );

        if ( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        }
        else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }

        // If file exists in uploads do not upload again
        // TODO
        if (file_exists($file)) {
            return false;
        }

        file_put_contents( $file, $image_data );
        $attachment = array(
            'post_mime_type' => 'image/' . $extension,
            'post_title' => sanitize_file_name( $filename ),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $image_id = wp_insert_attachment( $attachment, $file );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $image_id, $file );
        wp_update_attachment_metadata( $image_id, $attach_data );

        return $image_id ?? false;
    }
}
