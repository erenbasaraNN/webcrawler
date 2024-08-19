<?php
namespace App\SiteHandlers;

use App\Crawlers\YeditepeCrawler;
use App\Http\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Exception;

class YeditepeHandler implements SiteHandlerInterface
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
    public function handle(string $url): array
    {
        $issueLinks = $this->getIssueLinksFromMultiplePages($url);

        $allIssues = [];
        foreach ($issueLinks as $issueLink) {
            $issueData = $this->handleIssue($issueLink);
            $allIssues[] = $issueData;
        }

        return $allIssues;
    }

    /**
     * @throws GuzzleException
     */
    private function getIssueLinksFromMultiplePages(string $url): array
    {
        $issueLinks = [];
        $pages = [0, 1, 2]; // The pages you mentioned

        foreach ($pages as $page) {
            $pageUrl = $url . '?page=' . $page;
            $html = $this->client->get($pageUrl);
            $crawler = new SymfonyCrawler($html);

            $links = $crawler->filterXPath('//span[@class="gbaslik"]/a')->each(function ($node) {
                return $node->attr('href');
            });

            $issueLinks = array_merge($issueLinks, $links);
        }

        return $issueLinks;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function handleIssue(string $issueUrl): array
    {
        $fullIssueUrl = 'https://globalmediajournaltr.yeditepe.edu.tr' . $issueUrl;

        $html = $this->client->get($fullIssueUrl);
        $domCrawler = new SymfonyCrawler($html);
        $crawler = new YeditepeCrawler($domCrawler);

        $articleRows = $domCrawler->filterXPath('//table/tbody/tr');
        if ($articleRows->count() === 0) {
            throw new Exception('No article rows found on the page.');
        }

        $articles = [];
        foreach ($articleRows as $row) {
            $articleCrawler = new SymfonyCrawler($row);
            $articles[] = $this->processArticle($articleCrawler);
        }


        return [
            'articles' => $articles,
            'volume' => $crawler->getVolume(),
            'year' => $crawler->getYear(),
            'number' => $crawler->getNumber()
        ];
    }

    private function processArticle(SymfonyCrawler $articleCrawler): array
    {
        $crawler = new YeditepeCrawler($articleCrawler);
            return [
                'title' => $crawler->getTitle($articleCrawler),
                'abstract' => null,
                'keywords' => $crawler->getKeywords($articleCrawler), // No keywords in the structure you provided
                'pdf_url' => $crawler->getPdfUrl($articleCrawler),
                'firstpage' => $crawler->getFirstPage($articleCrawler), // Not available directly in table format
                'lastpage' => $crawler->getLastPage($articleCrawler),  // Not available directly in table format
                'authors' => $crawler->getAuthors($articleCrawler),
                'primary_language' => 'tr', // Assuming Turkish is the primary language
                'en_title' => null,
                'en_abstract' => null,
                'en_keywords' => null
            ];

    }

}
