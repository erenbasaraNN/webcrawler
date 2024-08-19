<?php
namespace App\SiteHandlers;

use App\Crawlers\Models\Article;
use App\Crawlers\PsikologCrawler;
use App\Http\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Exception;

class PsikologHandler implements SiteHandlerInterface {
    private Client $client;
    private ArticleHandle $articleHandle;

    public function __construct(Client $client, ArticleHandle $articleHandle) {
        $this->client = $client;
        $this->articleHandle = $articleHandle;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function handle(string $url): array {
        $html = $this->client->get($url);
        $domCrawler = new SymfonyCrawler($html);

        $issueDivs = $domCrawler->filter('div.accordic');

        if ($issueDivs->count() > 1) {
            $issueDivs = $issueDivs->slice(1);
        } else {
            throw new Exception('No issue divs found after skipping the first.');
        }

        $allIssues = [];
        foreach ($issueDivs as $div) {
            $issueCrawler = new SymfonyCrawler($div);
            $issueData = $this->processIssue($issueCrawler);

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
    private function processArticle(SymfonyCrawler $articleCrawler): Article
    {
        return $this->articleHandle->processArticle($articleCrawler, 'psikolog.org.tr');
    }

}
