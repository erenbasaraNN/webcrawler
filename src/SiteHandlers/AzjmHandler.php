<?php
namespace App\SiteHandlers;

use App\Crawlers\AzjmCrawler;
use App\Crawlers\Models\Article;
use App\Http\Client;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class AzjmHandler implements SiteHandlerInterface
{
    private Client $client;
    private ArticleHandle $articleHandle;

    public function __construct(Client $client, ArticleHandle $articleHandle)
    {
        $this->client = $client;
        $this->articleHandle = $articleHandle;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function handle(string $url): array
    {
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
    private function getIssueLinks(SymfonyCrawler $crawler): array
    {
        return $crawler->filterXPath('//h4/a')->each(function ($node) {
            $link = $node->attr('href');
            return str_starts_with($link, 'http') ? $link : 'https://azjm.org/' . ltrim($link, '/');
        });
    }

    /**
     * Process a single issue
     * @throws GuzzleException
     * @throws Exception
     */
    private function processIssue(string $issueUrl): array
    {
        $html = $this->client->get($issueUrl);
        $domCrawler = new SymfonyCrawler($html);

        // Extract volume, number, and year
        $crawler = new AzjmCrawler($domCrawler);
        $issueInfo = $crawler->getVolumeNumberYear();

        // Fetch articles within the issue
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
     * @throws Exception
     */
    private function processArticle(SymfonyCrawler $articleCrawler): Article
    {
        return $this->articleHandle->processArticle($articleCrawler, 'azjm.org');
    }
}
