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


