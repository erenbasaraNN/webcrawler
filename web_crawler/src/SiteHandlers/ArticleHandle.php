<?php

namespace App\SiteHandlers;

use App\Crawlers\AzjmCrawler;
use App\Crawlers\IsAhlakiCrawler;
use App\Crawlers\OsmanliMirasCrawler;
use App\Crawlers\PsikologCrawler;
use App\Crawlers\YeditepeCrawler;
use App\Enums\Domain;
use App\Models\Article;
use Exception;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class ArticleHandle
{

    /**
     * @throws Exception
     */
    public function processArticle(SymfonyCrawler $articleCrawler, $domain): Article
    {
        $domainEnum = Domain::from($domain);
        $crawler = $this->getCrawlerForDomain($domainEnum, $articleCrawler);
        $title = $crawler->getTitle($articleCrawler);
        $en_title = $crawler->getEnglishTitle($articleCrawler);
        $abstract = $crawler->getAbstract($articleCrawler);
        $keywords = $crawler->getKeywords($articleCrawler);
        $pdfUrl = $crawler->getPdfUrl($articleCrawler);
        $firstPage = $crawler->getFirstPage($articleCrawler);
        $lastPage = $crawler->getLastPage($articleCrawler);
        $authors = $crawler->getAuthors($articleCrawler);
        $primaryLanguage = $crawler->getLanguage($articleCrawler);
        $en_abstract = $crawler->getEnglishAbstract($articleCrawler);
        $en_keywords = $crawler->getEnglishKeywords($articleCrawler);

        return new Article($title, $en_title, $abstract, $authors, $pdfUrl, $firstPage, $lastPage, $keywords, $primaryLanguage,$en_abstract, $en_keywords);
    }

    private function getCrawlerForDomain(Domain $domain, SymfonyCrawler $articleCrawler): AzjmCrawler|PsikologCrawler|OsmanliMirasCrawler|YeditepeCrawler|IsAhlakiCrawler
    {
        return match ($domain) {
            Domain::PSIKOLOG => new PsikologCrawler($articleCrawler),
            Domain::OSMANLI => new OsmanliMirasCrawler($articleCrawler),
            Domain::YEDITEPE => new YeditepeCrawler($articleCrawler),
            Domain::AZJM => new AzjmCrawler($articleCrawler),
            Domain::IS_AHLAKI => new IsAhlakiCrawler($articleCrawler),
        };
    }
}
