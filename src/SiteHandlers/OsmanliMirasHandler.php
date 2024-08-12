<?php
namespace App\SiteHandlers;

use App\Dom\OsmanliMirasCrawler;
use App\Http\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Exception;

class OsmanliMirasHandler implements SiteHandlerInterface {
    private Client $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function handle(string $url): array {
        // Sayfayı yükle
        $html = $this->client->get($url);
        $domCrawler = new SymfonyCrawler($html);

        // XPath ile makale linklerini çek
        $articleLinks = $domCrawler->filterXPath('//div[contains(@class, "sj-content")]//article//h3/a')->each(function ($node) {
            return $node->attr('href');
        });

        // Eğer makale linki bulunamazsa hata fırlat
        if (empty($articleLinks)) {
            throw new Exception('No article links found on the page.');
        }

        // Her bir makale linkini işleyerek verileri kaydet
        $articles = [];
        foreach ($articleLinks as $link) {
            $fullUrl = str_starts_with($link, 'http') ? $link : 'https://www.osmanlimirasi.net' . $link;

            // Makale verilerini işleme ve hataları yakalama
            try {
                $articles[] = $this->processArticle($fullUrl);
            } catch (Exception $e) {
                echo 'Error processing article: ' . $e->getMessage();
            }
        }

        // Cilt, Yıl ve Sayı bilgilerini çek
        $volume = $this->extractData($domCrawler, 'h2:contains("Cilt")', 'Volume not found');
        $year = $this->extractData($domCrawler, 'h2:contains("Yıl")', 'Year not found');
        $number = $this->extractData($domCrawler, 'h2:contains("Sayı")', 'Number not found');

        return [
            'volume' => $volume,
            'year' => $year,
            'number' => $number,
            'articles' => $articles,
        ];
    }

    private function processArticle(string $articleUrl): array {
        // Makale sayfasını yükle
        $html = $this->client->get($articleUrl);
        $domCrawler = new SymfonyCrawler($html);
        $crawler = new OsmanliMirasCrawler($domCrawler);

        // Verileri işle ve hataları yakala
        return [
            'title' => $this->extractData($domCrawler, 'meta[name="citation_title"]', 'Title not found for URL: ' . $articleUrl),
            'abstract' => $crawler->getAbstract(),
            'keywords' => $crawler->getKeywords(),
            'pdf_url' => $crawler->getPdfUrl(),
            'firstpage' => $crawler->getFirstPage(),
            'lastpage' => $crawler->getLastPage(),
            'authors' => $crawler->getAuthors(),
        ];
    }

    private function extractData(SymfonyCrawler $crawler, string $filter, string $errorMessage): string {
        $node = $crawler->filter($filter);

        if ($node->count() === 0) {
            throw new Exception($errorMessage);
        }

        return trim($node->text());
    }
}
