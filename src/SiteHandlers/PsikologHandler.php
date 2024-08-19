<?php
namespace App\SiteHandlers;

use App\Crawlers\PsikologCrawler;
use App\Http\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Exception;

class PsikologHandler implements SiteHandlerInterface {
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

        // Extract issues based on the div.accordic elements
        $issueDivs = $domCrawler->filter('div.accordic');

        // Skip the first issue div
        if ($issueDivs->count() > 1) {
            $issueDivs = $issueDivs->slice(1);
        } else {
            throw new Exception('No issue divs found after skipping the first.');
        }

        $allIssues = [];
        foreach ($issueDivs as $div) {
            $issueCrawler = new SymfonyCrawler($div);
            $issueData = $this->processIssue($issueCrawler);

            // Only add issues that contain articles with a valid PDF link
                $allIssues[] = $issueData;
            }

        return $allIssues;
    }

    /**
     * Process a single issue
     * @throws Exception
     */
    private function processIssue(SymfonyCrawler $issueCrawler): array {
        $crawler = new PsikologCrawler($issueCrawler);

        // Fetch articles within the issue
        $articleRows = $issueCrawler->filterXPath('//div[@class="yayinDiv"]');
        $articles = [];
        foreach ($articleRows as $row) {
            $articleCrawler = new SymfonyCrawler($row);
            $articles[] = $this->processArticle($articleCrawler);
        }

        return [
            'volume' => $crawler->getVolume(),
            'year' => $crawler->getYear(),
            'number' => $crawler->getNumber(),
            'articles' => $articles,
        ];
    }

    /**
     * Process a single article
     * @throws Exception
     */
    private function processArticle(SymfonyCrawler $articleCrawler): array {
        $crawler = new PsikologCrawler($articleCrawler);
        try {
            return [
                'title' => $crawler->getTitle($articleCrawler),
                'en_title' => null,
                'abstract' => null, // No abstract available
                'keywords' => null, // No keywords available
                'pdf_url' => $crawler->getPdfUrls($articleCrawler), // This includes both Turkish and English PDFs
                'firstPage' => null,
                'lastPage' => null,
                'authors' => $crawler->getAuthors($articleCrawler),
                'primary_language' => 'tr',
            ];
        } catch (Exception $e) {
            throw new Exception('Error processing article: ' . $e->getMessage());
        }
    }
}
