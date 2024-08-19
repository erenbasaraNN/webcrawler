<?php
namespace App\SiteHandlers;

use App\Crawlers\AzjmCrawler;
use App\Http\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Exception;

class AzjmHandler implements SiteHandlerInterface {
    private Client $client;

    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function handle(string $url): array {
        $html = $this->client->get($url);
        $domCrawler = new SymfonyCrawler($html);

        // Extract issues based on the <h4> links within <div id="issue-3">
        $issueLinks = $this->getIssueLinks($domCrawler);

        $allIssues = [];
        foreach ($issueLinks as $issueLink) {
            $issueData = $this->processIssue($issueLink);
            $allIssues[] = $issueData;
        }

        return $allIssues;
    }

    /**
     * Extract issue links from the main page
     */
    private function getIssueLinks(SymfonyCrawler $crawler): array {
        // Adjust XPath to select <a> tags inside <h4> tags within <div id="issue-3">
        return $crawler->filterXPath('//h4/a')->each(function ($node) {
            $link = $node->attr('href');
            // Handle relative URLs
            return str_starts_with($link, 'http') ? $link : 'https://azjm.org/' . ltrim($link, '/');
        });
    }

    /**
     * Process a single issue
     * @throws GuzzleException
     * @throws Exception
     */
    private function processIssue(string $issueUrl): array {
        $html = $this->client->get($issueUrl);
        $domCrawler = new SymfonyCrawler($html);
        $crawler = new AzjmCrawler($domCrawler);

        // Extract volume, number, and year
        $issueInfo = $crawler->getVolumeNumberYear();

        // Fetch articles within the issue (all <table class="tocArticle">)
        $articleRows = $domCrawler->filter('table.tocArticle');
        $articles = [];
        foreach ($articleRows as $row) {
            $articleCrawler = new SymfonyCrawler($row);
            $articles[] = $this->processArticle($articleCrawler);
        }

        return [
            'volume' => $issueInfo['volume'],
            'year' => $issueInfo['year'],
            'number' => $issueInfo['number'],
            'articles' => $articles,
        ];
    }

    /**
     * Process a single article
     * @throws Exception
     */
    private function processArticle(SymfonyCrawler $articleCrawler): array {
        $crawler = new AzjmCrawler($articleCrawler);
        try {
            return [
                'title' => $crawler->getTitle($articleCrawler),
                'en_title' => null, // No English title available
                'abstract' => null, // No abstract provided
                'keywords' => null, // No keywords provided
                'pdf_url' => $crawler->getPdfUrl($articleCrawler),
                'firstPage' => $crawler->getFirstPage($articleCrawler),
                'lastPage' => $crawler->getLastPage($articleCrawler),
                'authors' => $crawler->getAuthors($articleCrawler),
                'primary_language' => 'en', // Assuming English as primary language
            ];
        } catch (Exception $e) {
            throw new Exception('Error processing article: ' . $e->getMessage());
        }
    }
}