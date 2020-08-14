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
        $offers = $this->getOffers('electricite');
        foreach($offers['data'] as $offer) {
            $provider = $offer['provider']['id'];
            $providerLogo = $offer['provider']['logo'];
            $service = $offer['service']['name'];
            $description = $offer['description'];
            $features = '';
            foreach($offer['features'] as $key => $feature) {
                foreach($feature as $k => $serviceFeature) {
                    echo "<pre>$k ", var_dump($serviceFeature), '</pre>';
                    
                }
            }
            echo '<pre>', var_dump($offer), '</pre>';
            exit;
        }
        return [];
    }

    const api = 'https://jechange.sn77.net/api/v1/';
    private $bearer = null;
    public function api_login() {
        if($_SESSION['jechange-api']) {
            $this->bearer = $_SESSION['jechange-api'];
            return;
        }
        $uri = 'login';
        $options  =[
            'json' => [
                'username' => 'admin@jechange.fr',
                'password' => '123',
            ],
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', self::api . $uri, $options);
            if($response->getStatusCode() == 200) {
                $js = json_decode($response->getBody(), 1); // json
                $this->bearer = 'Bearer ' . $js['token'];
                $_SESSION['jechange-api'] = $this->bearer;
            }
            
        } catch(\Exception $e) {
            echo '<pre>', var_dump($e), '</pre>';exit();
        }
    }

    public function getOffers($service = null) {
        // demo data
        return json_decode('{"filters":{"GRN":{"condition":"eq","fieldAccessor":"booleanValue","value":"1"}},"timestamp":"2020-08-14T14:56:18+00:00","total":4,"page":1,"limit":10,"isLastPage":true,"data":[{"id":2,"provider":{"id":6,"name":"Total direct energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/1\/8\/18.png","createdAt":"2020-07-08T09:21:06+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Classique \u00e9lectricit\u00e9","description":"<div>&nbsp;<strong>Classique \u00e9lectricit\u00e9<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 5% sur le prix du kwh HT, par rapport au tarif r\u00e9glement\u00e9 en vigueur","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 1 an","pictogram":null}],"createdAt":"2020-07-08T09:31:05+00:00","updatedAt":"2020-07-08T09:31:05+00:00","isMonthly":"1","electricityPrice":"8031.88"},{"id":11,"provider":{"id":9,"name":"Ovo Energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/2\/5\/25.png","createdAt":"2020-07-08T14:41:05+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Ovo Electricit\u00e9 verte","description":"<div>&nbsp;<strong>Ovo Electricit\u00e9 verte<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 16% sur le prix du kwh HT par rapport au tarif r\u00e9glement\u00e9 en vigueur","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 10","pictogram":null}],"createdAt":"2020-07-08T14:41:32+00:00","updatedAt":"2020-07-08T14:41:32+00:00","isMonthly":"1","electricityPrice":"9719.88"},{"id":10,"provider":{"id":8,"name":"OHM \u00e9nergie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/2\/4\/24.png","createdAt":"2020-07-08T14:37:48+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"OHM Beaux Jours 1er avril au 31 octobre","description":"<div>&nbsp;<strong>OHM Beaux Jours 1er avril au 31 octobre<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 922","callMeBack":true,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Remise de 30% sur le prix du kwh HT par rapport au tarif r\u00e9glement\u00e9, du 1er avril au 31 octobre","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 2","pictogram":null}],"createdAt":"2020-07-08T14:38:11+00:00","updatedAt":"2020-07-08T14:38:11+00:00","isMonthly":"1","electricityPrice":"12550.6"},{"id":1,"provider":{"id":6,"name":"Total direct energie","logo":"http:\/\/jechange.sn77.net\/media\/cache\/original\/1\/8\/18.png","createdAt":"2020-07-08T09:21:06+00:00"},"service":{"id":2,"name":"\u00c9lectricit\u00e9 moins ch\u00e8re","callCenter":"0800 811 911","pricingType":"energy-electricity"},"name":"Online \u00e9lectricit\u00e9","description":"<div>&nbsp;<strong>Online \u00e9lectricit\u00e9<\/strong>&nbsp;<\/div>","isActive":true,"sites":["http:\/\/jechange.sn77.net"],"weight":0,"showOnProviderDetails":false,"callCenterPhone":"0800 811 911","callMeBack":false,"features":[{"id":"GRN","text":"100% d\'\u00e9nergie certifi\u00e9e verte","pictogram":{"content":"100%% d\'\u00e9nergie certifi\u00e9e verte","image":"http:\/\/jechange.sn77.net\/media\/cache\/resolve\/original\/1\/9\/19.png"}},{"id":"RMS","text":"Remise : Non propos\u00e9","pictogram":null},{"id":"RMSGAR","text":"Remise garantie : 1 an","pictogram":null}],"createdAt":"2020-07-08T09:22:40+00:00","updatedAt":"2020-07-08T09:22:40+00:00","isMonthly":"1","electricityPrice":"13119.88"}]}',1);
        //
        if($service === null) {
            throw new \Exception('no service');
        }
        if($this->bearer === null) {
            $this->api_login();
        }
        $uri = "wordpress/$service/offers?page=1&limit=10";
        $options  =[
            'headers'        => [
                'Authorization' => $this->bearer,
            ],
        ];
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('get', self::api . $uri, $options);
            if($response->getStatusCode() == 200) {
                // echo $response->getBody();exit;
                $js = json_decode($response->getBody(), 1); // json
                return $js;
            }
            
        } catch(\Exception $e) {
            echo '<pre>', var_dump($e), '</pre>';exit();
        }
    }

}
