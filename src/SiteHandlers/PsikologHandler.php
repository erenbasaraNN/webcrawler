<?php

namespace App\SiteHandlers;

use App\Crawlers\Models\Issue;
use App\Http\Client;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

class PsikologHandler implements SiteHandlerInterface
{
    const DOMAINPSIKOLOG = 'psikolog.org.tr';
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
     * @throws Exception
     */
    public function handle(string $url): array
    {
        $html = $this->client->get($url);
        $domCrawler = new SymfonyCrawler($html);

        $selector = 'div.accordic';
        $issueDivs = $domCrawler->filter($selector);

        if ($issueDivs->count() > 1) {
            $issueDivs = $issueDivs->slice(1);
        }

        $allIssues = [];
        foreach ($issueDivs as $div) {
            $issueCrawler = new SymfonyCrawler($div);
            $allIssues[] = $this->processIssue($issueCrawler);
        }

        return $allIssues;
    }

    /**
     * @throws Exception
     */
    private function processIssue(SymfonyCrawler $issueCrawler): Issue
    {
        $articles = $this->processArticles($issueCrawler);

        return $this->issueHandle->generateIssue($issueCrawler, self::DOMAINPSIKOLOG, $articles);
    }

    /**
     * @throws Exception
     */
    private function processArticles(SymfonyCrawler $issueCrawler): array
    {
        $xpath = '//div[@class="yayinDiv"]';
        $articleRows = $issueCrawler->filterXPath($xpath);
        $articles = [];

        foreach ($articleRows as $row) {
            $articleCrawler = new SymfonyCrawler($row);
            $articles[] = $this->articleHandle->processArticle($articleCrawler, self::DOMAINPSIKOLOG);
        }

        return $articles;
    }
}
