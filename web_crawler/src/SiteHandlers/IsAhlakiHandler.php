<?php

namespace App\SiteHandlers;

use App\Http\Client;
use App\Models\Issue;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class IsAhlakiHandler implements SiteHandlerInterface
{
    const DOMAIN = 'isahlakidergisi.com';
    const ISAHLAKIURL = 'https://isahlakidergisi.com';
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
        $issueLinks = $this->getIssueLinks($url);
        $allIssues = [];

        foreach ($issueLinks as $issueLink) {
            $allIssues[] = $this->processIssue($issueLink);
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

        $xpath = '//div[contains(@class, "content-wrapper")]//h3[@class="title"]/a';
        return $crawler->filterXPath($xpath)->each(function ($node) {
            return $node->attr('href');
        });
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function processIssue(string $issueUrl): Issue
    {
        $html = $this->client->get($issueUrl);
        $domCrawler = new SymfonyCrawler($html);

        $articles = $this->processArticles($domCrawler);

        return $this->issueHandle->generateIssue($domCrawler, self::DOMAIN, $articles);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function processArticles(SymfonyCrawler $issueCrawler): array
    {
        $xpath = '//div[@id="articles"]//div[contains(@class, "content-wrapper")]//h3[@class="title"]/a';
        $articleLinks = $issueCrawler->filterXPath($xpath)->each(function ($node) {
            return $node->attr('href');
        });

        $articles = [];
        foreach ($articleLinks as $link) {
            $fullUrl = str_starts_with($link, 'http') ? $link : self::ISAHLAKIURL . $link;
            $articleHtml = $this->client->get($fullUrl);
            $articleCrawler = new SymfonyCrawler($articleHtml);
            $articles[] = $this->articleHandle->processArticle($articleCrawler, self::DOMAIN);
        }

        return $articles;
    }
}
