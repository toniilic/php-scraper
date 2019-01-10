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


$url = "https://www.thebigfreechiplist.com/Casino-No-Deposit-Bonus";
$crawler = $client->request('GET', $url);


$crawler->filter('#blog-bonuses-server-response-wrapper .blog_box_left > a')->each(function ($node) use ($crawler, &$pageUrls) {

    $linksCrawler = $crawler->selectLink(trim($node->text()));
    $link = $linksCrawler->link();

    $pageUrls[] = $link->getUri();
});




// TODO: go to second page
// TODO: in DB add the first url as last_scraped_url ($pageUrls[0])
dump($pageUrls);

// TODO(1) Create a repository that uses the model and stores complicated values
// TODO(2) combine php-rest with rest-ap-authentication-example: 1) Refactorirati postojeći jwt kod da koristi composer i staviti jwt auth u svoju zasebnu klasu

// TODO: login to API with JWT

# Parse each page
foreach($pageUrls as $url) {
    $crawler = $client->request('GET', $url);

    $crawler->filter('.blog_box_left')->each(function ($node) use ($crawler, $url) {


        $data = array();

        $title = $crawler->filter('.h2_blog_box_title')->text();
        $title = trim($title);

        $bonusCode = $crawler->filter('.right_d_a')->eq(0)->text();
        $bonusCode = array_map('trim', explode(',', $bonusCode));


        $bonusTypes = array();
        $crawler->filter('.right_d_a a')->each(function ($node) use ($crawler, &$bonusTypes) {
            $bonusType = $node->text();
            $bonusTypes[] = $bonusType;
        });

        $validUntil = $crawler->filter('.right_d_a')->eq(2)->text();
        preg_match('/^([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])/', $validUntil, $matches);
        if(count($matches) == 4) {
            $validUntil = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        } else {
            $validUntil = '';
        }

        if(!$validUntil) {
            $gamesAllowed = $crawler->filter('.right_d_a')->eq(2)->text();
            $wagering = $crawler->filter('.right_d_a')->eq(3)->text();
        } else {
            $gamesAllowed = $crawler->filter('.right_d_a')->eq(3)->text();
            $wagering = $crawler->filter('.right_d_a')->eq(4)->text();
        }
        $gamesAllowed = array_map('trim', explode(',', $gamesAllowed));


        $data = [
            'title' => $title,
            'url' => $url,
            'bonus_codes' => $bonusCode,
            'bonus_types' => $bonusTypes,
            'valid_until' => $validUntil,
            'games_allowed' => $gamesAllowed,
            'wagering' => $wagering,
        ];

        dump($data);
        /*$linksCrawler = $crawler->selectLink(trim($node->text()));
        $link = $linksCrawler->link();*/


    });


    // TODO: $api->storeSingle($data) | send a post request to API http://localhost:8080/api/product/create.php
}


/*$crawler->filter('.blog_box_left a')->each(function ($node) {
    // Text
    print $node->text()."\n\n";
});*/

