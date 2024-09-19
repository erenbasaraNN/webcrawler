<?php

namespace App\SiteHandlers;

use App\Http\Client;
use App\Models\Issue;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class YeditepeHandler implements SiteHandlerInterface
{
    const DOMAINYEDITEPE = 'globalmediajournaltr.yeditepe.edu.tr';
    private Client $client;
    private ArticleHandle $articleHandle;
    private IssueHandle $issueHandle;

    public function __construct(Client $client, ArticleHandle $articleHandle, IssueHandle $issueHandle)
    {
        $this->client = $client;
        $this->articleHandle = $articleHandle;
        $this->issueHandle = $issueHandle;
    }

    /**
     * @throws GuzzleException
     */
    public function handle(string $url): array
    {
        $issueLinks = $this->getIssueLinksFromMultiplePages($url);
        $allIssues = [];

        foreach ($issueLinks as $issueLink) {
            $allIssues[] = $this->processIssue($issueLink);
        }

        return $allIssues;
    }

    /**
     * @throws GuzzleException
     */
    private function getIssueLinksFromMultiplePages(string $url): array
    {
        $issueLinks = [];
        $pages = [0, 1, 2];

        foreach ($pages as $page) {
            $pageUrl = $url . '?page=' . $page;
            $html = $this->client->get($pageUrl);
            $crawler = new SymfonyCrawler($html);

            $xpath = '//span[@class="gbaslik"]/a';
            $links = $crawler->filterXPath($xpath)->each(function ($node) {
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
    private function processIssue(string $issueUrl): Issue
    {
        $fullIssueUrl = 'https://globalmediajournaltr.yeditepe.edu.tr' . $issueUrl;
        $html = $this->client->get($fullIssueUrl);
        $domCrawler = new SymfonyCrawler($html);

        $articles = $this->processArticles($domCrawler);

        return $this->issueHandle->generateIssue($domCrawler, self::DOMAINYEDITEPE, $articles);
    }

    /**
     * @throws Exception
     */
    private function processArticles(SymfonyCrawler $domCrawler): array
    {
        $xpath = '//table/tbody/tr';
        $articleRows = $domCrawler->filterXPath($xpath);

        if ($articleRows->count() === 0) {
            throw new Exception('No article rows found on the page.');
        }

        $articles = [];
        foreach ($articleRows as $row) {
            $articleCrawler = new SymfonyCrawler($row);
            $articles[] = $this->articleHandle->processArticle($articleCrawler, self::DOMAINYEDITEPE);
        }

        return $articles;
    }
}
