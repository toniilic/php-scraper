<?php

require __DIR__ . '/../../vendor/autoload.php';

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

$client = new Client();
$guzzleClient = new GuzzleClient(array(
    'timeout' => 60,
));

$client->setClient($guzzleClient);






$pageUrls = array();

$lastScrapedUrl = ""; // TODO: get last scraped url from database


// .filter_controler_forward_button a


$url = "https://uk.hotels.com/search.do?resolved-location=CITY%3A549499%3AUNKNOWN%3AUNKNOWN&destination-id=549499&q-destination=London,%20England,%20United%20Kingdom&q-check-in=2019-01-12&q-check-out=2019-01-13&q-rooms=1&q-room-0-adults=2&q-room-0-children=0";
$crawler = $client->request('GET', $url);




$crawler->filter('.p-name')->each(function ($node) use ($crawler, &$pageUrls) {

    $linksCrawler = $crawler->selectLink(trim($node->text()));
    $link = $linksCrawler->link();

    $pageUrls[] = $link->getUri();
});




dump($pageUrls);

$locale = 'uk';
$city = 'London';

foreach($pageUrls as $url) {
    $crawler = $client->request('GET', $url);

    $title = $crawler->filter('.vcard h1')->text();
    $description = $crawler->filter('.tagline b')->text();
/*    $bonusCode = $crawler->filter('.about-casino__info dl dd')->text();
    $players = $crawler->filter('.about-casino__info dl dd')->eq(2)->text();
    $validUntil = $crawler->filter('.about-casino__info dl dd')->eq(3)->text();
    preg_match('/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/', $validUntil, $matches);
    if(count($matches) == 4) {
        $validUntil = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
    } else {
        $validUntil = '';
    }

    if(!$validUntil) {
        $wagering = $crawler->filter('.about-casino__info dl dd')->eq(3)->text();
        $maxCashOut = $crawler->filter('.about-casino__info dl dd')->eq(4)->text();

    } else {
        try {
            $wagering = $crawler->filter('.about-casino__info dl dd')->eq(4)->text();
        }catch (\Exception $exception) {
            $wagering = "";
        }
        try {
            $maxCashOut = $crawler->filter('.about-casino__info dl dd')->eq(5)->text();
        }catch (\Exception $exception) {
            $maxCashOut = "";
        }
    }*/

    $data = [
        'title' => $title,
        'url' => $url,
        'locale' => $locale,
        'description' => $description,
/*        'bonus_code' => $bonusCode,
        'players' => $players,
        'valid_until' => $validUntil,
        'wagering' => $wagering,
        'max_cash_out' => $maxCashOut,*/
    ];

    dump($data);


    /*
     * Title
     * Type
     * Recension
     *
     *
     * */



    // TODO: $api->storeSingle($data) | send a post request to API http://localhost:8080/api/product/create.php
}