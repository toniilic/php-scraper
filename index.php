<?php

require __DIR__ . '/vendor/autoload.php';


use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;

$client = new Client();
$guzzleClient = new GuzzleClient(array(
    'timeout' => 60,
));

$client->setClient($guzzleClient);


$url = "https://mojkupon.rs/coupon_category/beograd/";

$crawler = $client->request('GET', $url);

$crawler->filter('.entry-title.td-module-title  a')->each(function ($node) {
    print $node->text()."\n\n";
});
