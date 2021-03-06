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



$ritelefaxScraper = new RitelefaxUrlScraper($client);
$pageUrls = $ritelefaxScraper->scrapeForUrls(3,
    "https://burza.com.hr/oglasi/posao-ponuda?oglasivac=0&q=&f=0&pf=&pt=&stranica=1"
);
//dump($pageUrls);


$ritelefaxPageScraper = new RitelefaxPageScraper($client);
$data = $ritelefaxPageScraper->scrapeUrls($pageUrls, "Primorsko-goranska");
dump($data);

class RitelefaxPageScraper {
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
        $title = $this->crawler->filter('.col-md-9.left-zero h2')->text();
        $description = $this->crawler->filter('.col-md-5.opis-oglasa p')->text();

        $data = [
            'title' => $title,
            'description' => $description,
            'region' => $region,
        ];

        dump($data);

        return $data;
    }
}


class RitelefaxUrlScraper {

    protected $client;
    protected $crawler;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function scrapeForUrls($pagesToTraverse = 10, $url)
    {
        $pageUrls = array();

        $this->crawler = $this->client->request('GET', $url);

        for($i = 1; $i != $pagesToTraverse + 1; $i++) {

            array_push($pageUrls, $this->scrapePageUrls());

            $this->selectNextPage($url, $i);
        }

        $pageUrls = array_unique(flattenArray($pageUrls));

        return $pageUrls;
    }

    protected function selectNextPage($url, $i)
    {
        $url = substr_replace($url ,"", -1);
        $url = $url . $i;
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






function flattenArray(array $array) {
    $return = array();
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
}
