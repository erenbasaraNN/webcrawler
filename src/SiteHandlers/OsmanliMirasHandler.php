<?php

namespace App\SiteHandlers;

use App\Crawlers\Models\Article;
use App\Crawlers\OsmanliMirasCrawler;
use App\Http\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Exception;


class OsmanliMirasHandler implements SiteHandlerInterface
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
    public function handle(string $url): array {
        $issueLinks = $this->getIssueLinks($url);

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
    private function getIssueLinks(string $url): array
    {
        $html = $this->client->get($url);
        $crawler = new SymfonyCrawler($html);

        return $crawler->filterXPath('//a[contains(@class, "sj-btnrecord")]')->each(function ($node) {
            return $node->attr('href');
        });
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function handleIssue(string $issueUrl): array
    {
        $html = $this->client->get($issueUrl);
        $domCrawler = new SymfonyCrawler($html);
        $crawler = new OsmanliMirasCrawler($domCrawler);

        $articleLinks = $domCrawler->filterXPath('//div[contains(@class, "sj-content")]//article//h3/a')->each(function ($node) {
            return $node->attr('href');
        });

        if (empty($articleLinks)) {
            throw new Exception('No article links found on the page.');
        }

        $articles = [];
        foreach ($articleLinks as $link) {
            $fullUrl = str_starts_with($link, 'http') ? $link : 'https://www.osmanlimirasi.net' . $link;
            try {
                $articleHtml = $this->client->get($fullUrl);
                $articleCrawler = new SymfonyCrawler($articleHtml);
                $articles[] = $this->processArticle($articleCrawler);
            } catch (Exception $e) {
                echo 'Error processing article: ' . $e->getMessage();
            }
        }

        return [
            'articles' => $articles,
            'volume' => $crawler->getVolume(),
            'year' => $crawler->getYear(),
            'number' => $crawler->getNumber()
        ];
    }

    /**
     * Process a single article
     * @throws Exception
     */
    private function processArticle(SymfonyCrawler $articleCrawler): Article
    {
        return $this->articleHandle->processArticle($articleCrawler, 'www.osmanlimirasi.net');
    }
}
