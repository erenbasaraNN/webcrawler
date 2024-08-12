<?php
namespace App\SiteHandlers;

use App\Http\Client;
use App\Dom\OsmanliMirasCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class SiteTwoHandler implements SiteHandlerInterface {
    private Client $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * @throws GuzzleException
     */
    public function handle(string $url): array {
        $html = $this->client->get($url);
        $domCrawler = new SymfonyCrawler($html);
        $crawler = new OsmanliMirasCrawler($domCrawler);

        $volume = $crawler->getVolume();
        $year = $crawler->getYear();
        $number = $crawler->getNumber();
        $articles = $crawler->getArticles();

        return [
            'volume' => $volume,
            'year' => $year,
            'number' => $number,
            'articles' => $articles,

        ];
    }
}
