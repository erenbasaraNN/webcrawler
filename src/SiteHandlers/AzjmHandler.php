<?php
namespace App\SiteHandlers;

use App\Crawlers\Models\Issue;
use App\Http\Client;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

class AzjmHandler implements SiteHandlerInterface
{
    const HTTPS_AZJM_ORG = 'https://azjm.org/';
    private Client $client;
    private ArticleHandle $articleHandle;
    private IssueHandle $issueHandle;

    const DOMAIN = 'azjm.org';

    public function __construct(Client $client, ArticleHandle $articleHandle, IssueHandle $issueHandle)
    {
        $this->client = $client;
        $this->articleHandle = $articleHandle;
        $this->issueHandle = $issueHandle;
    }

    /**

     * @throws GuzzleException
     * @throws Exception
     **/
    public function handle(string $url): array
    {
        $html = $this->client->get($url);
        $domCrawler = new SymfonyCrawler($html);

        $issueLinks = $this->getIssueLinks($domCrawler);
        $allIssues = [];

        foreach ($issueLinks as $issueLink) {
            $issueUrl = $this->buildFullIssueUrl($issueLink);
            $allIssues[] = $this->processIssue($issueUrl);
        }

        return $allIssues;
    }


    private function getIssueLinks(SymfonyCrawler $crawler): array
    {
        $xpath = '//h4/a';
        return $crawler->filterXPath($xpath)->each(function ($node) {
            return $node->attr('href');
        });
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     **/
    private function processIssue(string $issueUrl): Issue
    {
        $html = $this->client->get($issueUrl);
        $domCrawler = new SymfonyCrawler($html);

        return $this->processIssueData($domCrawler);
    }

    /**
     * @throws Exception
     **/
    private function processIssueData(SymfonyCrawler $issueCrawler): Issue
    {

        $selector = 'table.tocArticle';
        $articleRows = $issueCrawler->filter($selector);
        $articles = [];

        foreach ($articleRows as $row) {
            $articleCrawler = new SymfonyCrawler($row);
            $articles[] = $this->articleHandle->processArticle($articleCrawler,self::DOMAIN );
        }

        return $this->issueHandle->generateIssue($issueCrawler, self::DOMAIN, $articles);
    }


    private function buildFullIssueUrl(string $issueLink): string
    {
        return str_starts_with($issueLink, 'http') ? $issueLink : self::HTTPS_AZJM_ORG . ltrim($issueLink, '/');
    }
}
