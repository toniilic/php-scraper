<?php

require __DIR__ . '/../../vendor/autoload.php';


use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

$client = new Client();
$guzzleClient = new GuzzleClient(array(
    'timeout' => 60,
));

$client->setClient($guzzleClient);





/*
 * .h2_blog_box_title
 * #blog-bonuses-server-response-wrapper .blog_box_left > a
 *
 */

$pageUrls = array();

$lastScrapedUrl = ""; // TODO: get last scraped url from database


// .filter_controler_forward_button a


$url = "https://www.nonstopbonus.com/";
$crawler = $client->request('GET', $url);


$crawler->filter('.bonus_page > a')->each(function ($node) use ($crawler, &$pageUrls) {

    $linksCrawler = $crawler->selectLink(trim($node->text()));
    $link = $linksCrawler->link();

    $pageUrls[] = $link->getUri();
});




dump($pageUrls);


foreach($pageUrls as $url) {
    $crawler = $client->request('GET', $url);


    $title = $crawler->filter('.bonus_page > a')->text();
    $bonusCode = $crawler->filter('.about-casino__info dl dd')->text();
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
    } else {
        try {
            $wagering = $crawler->filter('.about-casino__info dl dd')->eq(4)->text();
        }catch (\Exception $exception) {
            $wagering = "";
        }

    }

    $data = [
        'title' => $title,
        'url' => $url,
        'bonus_code' => $bonusCode,
        'players' => $players,
        'valid_until' => $validUntil,
        'wagering' => $wagering,
    ];

    dump($data);


    /*$data = [
        'title' => $title,
        'url' => $url,
        'bonus_codes' => $bonusCode,
        'bonus_types' => $bonusTypes,
        'valid_until' => $validUntil,
        'games_allowed' => $gamesAllowed,
        'wagering' => $wagering,
    ];*/



    // TODO: $api->storeSingle($data) | send a post request to API http://localhost:8080/api/product/create.php
}