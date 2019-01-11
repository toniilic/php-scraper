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



$mojPosaoScraper = new MojPosaoUrlScraper($client);
$pageUrls = $mojPosaoScraper->scrapeForUrls(10,
    "https://www.moj-posao.net/Pretraga-Poslova/?keyword=&area=9&category="
);
dump($pageUrls);


$mojPosaoPageScraper = new MojPosaoPageScraper($client);
$data = $mojPosaoPageScraper
    ->scrapeUrls($pageUrls,
        'Primorsko-goranska');
dump($data);

class MojPosaoPageScraper {
    protected $client;
    protected $crawler;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function scrapeUrls($pageUrls, $region)
    {
        $data = array();

        foreach($pageUrls as $pageUrl) {
            $data[] = $this->scrapePage($pageUrl, $region);
        }

        return $data;
    }

    public function scrapePage($pageUrl, $region)
    {
        $this->crawler = $this->client->request('GET', $pageUrl);
        $title = $this->crawler->filter('#page-title h1')->text();
        $title = trim($title);

        $data = [
            'title' => $title,
            'region' => $region,
        ];

        return $data;
    }
}


class MojPosaoUrlScraper {

    protected $client;
    protected $crawler;
    protected $url;
    protected $currentPage;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function scrapeForUrls($pagesToTraverse = 10, $url)
    {
        $this->url = $url;

        $pageUrls = array();

        $this->crawler = $this->client->request('GET', $this->url);

        for($i = 1; $i != $pagesToTraverse + 1; $i++) {

            $this->currentPage = $i;
            $this->selectNextPage($i);

            array_push($pageUrls, $this->scrapePageUrls());
        }

        $pageUrls = array_unique(flattenArray($pageUrls));

        return $pageUrls;
    }

    protected function selectNextPage($i)
    {
        if($i > 1) {
            $url = $this->url . '&page=' . $i;
        } else {
            $url = $this->url;
        }

        $this->crawler = $this->client->request('GET', $url);
    }

    protected function scrapePageUrls()
    {
        if($this->currentPage > 1) {
            $filter = '.job-title a';
        } else {
            $filter = '.job-position';
        }
        $pageUrls = array();
        $this->crawler->filter($filter)->each(function ($node) use (&$pageUrls) {

            $linksCrawler = $this->crawler->selectLink(trim($node->text()));
            $link = $linksCrawler->link();

            $pageUrls[] = $link->getUri();
        });

        return $pageUrls;
    }
}






function flattenArray(array $array) {
    $return = array();
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}