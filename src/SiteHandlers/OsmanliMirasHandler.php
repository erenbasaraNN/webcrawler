<?php

namespace App\SiteHandlers;

use App\Dom\OsmanliMirasCrawler;
use App\Http\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Exception;

class OsmanliMirasHandler implements SiteHandlerInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function handle(string $url): array {
        $issueLinks = $this->getIssueLinks($url);

        $allIssues = [];
        foreach ($issueLinks as $issueLink) {
            $issueData = $this->handleIssue($issueLink);
            $allIssues[] = $issueData;
        }

        return $allIssues;
    }


    private function getIssueLinks(string $url): array
    {
        $html = $this->client->get($url);
        $crawler = new SymfonyCrawler($html);

        // XPath ile tüm issue linklerini toplar
        $issueLinks = $crawler->filterXPath('//a[contains(@class, "sj-btnrecord")]')->each(function ($node) {
            return $node->attr('href');
        });

        return $issueLinks;
    }

    /**
     * @throws GuzzleException
     */
    private function handleIssue(string $issueUrl): array
    {
        // Issue sayfasını yükle
        $html = $this->client->get($issueUrl);
        $domCrawler = new SymfonyCrawler($html);
        $crawler = new OsmanliMirasCrawler($domCrawler, $this->client);

        // Makale linklerini bulur
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
            $fullUrl = str_starts_with($link, 'http') ? $link : 'https://globalmediajournaltr.yeditepe.edu.tr' . $link;

            // Makale verilerini işleme ve hataları yakalama
            try {
                $articles[] = $this->processArticle($fullUrl);
            } catch (Exception $e) {
                echo 'Error processing article: ' . $e->getMessage();
            }
        }

        // Cilt, Yıl ve Sayı bilgilerini çek
        $issueData = [
            'volume' => $crawler->getVolume(),
            'year' => $crawler->getYear(),
            'number' => $crawler->getNumber()
        ];

        return [
            'articles' => $articles,
            'issueData' => $issueData
        ];
    }

    private function processArticle(string $articleUrl): array
    {
        // Makale sayfasını yükle
        $html = $this->client->get($articleUrl);
        $domCrawler = new SymfonyCrawler($html);
        $crawler = new OsmanliMirasCrawler($domCrawler, $this->client);

        // Verileri işle ve hataları yakala
        return [
            'title' => $crawler->getTitle(),
            'abstract' => $crawler->getAbstract(),
            'keywords' => $crawler->getKeywords(),
            'pdf_url' => $crawler->getPdfUrl(),
            'firstpage' => $crawler->getFirstPage(),
            'lastpage' => $crawler->getLastPage(),
            'authors' => $crawler->getAuthors(),
            'primary_language' => $crawler->getLanguage(),
            'en_title' => $crawler->getEnglishTitle(),
            'en_abstract' => $crawler->getEnglishAbstract(),
            'en_keywords' => $crawler->getEnglishKeywords(),
        ];
    }
}
