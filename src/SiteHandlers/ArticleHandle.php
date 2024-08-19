<?php

namespace App\SiteHandlers;

use App\Crawlers\AzjmCrawler;
use App\Crawlers\Models\Article;
use App\Crawlers\OsmanliMirasCrawler;
use App\Crawlers\PsikologCrawler;
use App\Crawlers\YeditepeCrawler;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Exception;

class ArticleHandle
{
    /**
     * @throws Exception
     */
    public function processArticle(SymfonyCrawler $articleCrawler, string $domain): Article
    {
        $crawler = $this->getCrawlerForDomain($domain, $articleCrawler);
        $title = $crawler->getTitle($articleCrawler);
        $en_title = $crawler->getEnglishTitle($articleCrawler);
        $abstract = $crawler->getAbstract($articleCrawler);
        $keywords = $crawler->getKeywords($articleCrawler);
        $pdfUrl = $crawler->getPdfUrl($articleCrawler);
        $firstPage = $crawler->getFirstPage($articleCrawler);
        $lastPage = $crawler->getLastPage($articleCrawler);
        $authors = $crawler->getAuthors($articleCrawler);
        $primaryLanguage = $crawler->getLanguage($articleCrawler);

        return new Article($title, $en_title, $abstract, $authors, $pdfUrl, $firstPage, $lastPage, $keywords, $primaryLanguage);
    }

    /**
     * @throws Exception
     */
    private function getCrawlerForDomain(string $domain, SymfonyCrawler $articleCrawler): AzjmCrawler|PsikologCrawler|OsmanliMirasCrawler|YeditepeCrawler
    {
        return match ($domain) {
            'psikolog.org.tr' => new PsikologCrawler($articleCrawler),
            'www.osmanlimirasi.net' => new OsmanliMirasCrawler($articleCrawler),
            'globalmediajournaltr.yeditepe.edu.tr' => new YeditepeCrawler($articleCrawler),
            'azjm.org' => new AzjmCrawler($articleCrawler),
            default => throw new Exception("No crawler found for domain: " . $domain),
        };
    }
}
