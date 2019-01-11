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



$ritelefaxScraper = new RitelefaxScraper($client);
$pageUrls = $ritelefaxScraper->scrapeUrls(10,
    "https://burza.com.hr/oglasi/posao-ponuda?oglasivac=0&q=&f=0&pf=&pt=&stranica=1"
);

class RitelefaxScraper {

    protected $client;
    protected $crawler;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function scrapeUrls($pagesToTraverse = 10, $url)
    {
        $pageUrls = array();
        
        $this->crawler = $this->client->request('GET', $url);

        for($i = 1; $i != $pagesToTraverse + 1; $i++) {

            array_push($pageUrls, $this->scrapePageUrls());

            $this->selectNextPage($url, $i);
        }

        return $pageUrls;
    }

    protected function selectNextPage($url, $i)
    {
        $url = substr_replace($url ,"", -1);
        $url = $url . $i;
        dump($url);
        // select next page
        $this->crawler = $this->client->request('GET', $url);
    }

    protected function scrapePageUrls()
    {
        $pageUrls = array();
        $this->crawler->filter('.bhsi-txt h3 a')->each(function ($node) use (&$pageUrls) {

            $linksCrawler = $this->crawler->selectLink(trim($node->text()));
            $link = $linksCrawler->link();

            $pageUrls[] = $link->getUri();
        });

        return $pageUrls;
    }
}



dump($pageUrls);


function flattenArray(array $array) {
    $return = array();
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
}


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