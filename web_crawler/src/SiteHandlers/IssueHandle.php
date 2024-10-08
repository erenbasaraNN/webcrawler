<?php

namespace App\SiteHandlers;

use App\Crawlers\AzjmCrawler;
use App\Crawlers\IsAhlakiCrawler;
use App\Crawlers\OsmanliMirasCrawler;
use App\Crawlers\PsikologCrawler;
use App\Crawlers\YeditepeCrawler;
use App\Enums\Domain;
use App\Models\Issue;
use Exception;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class IssueHandle
{
    /**
     * @throws Exception
     */
    public function generateIssue(SymfonyCrawler $issueCrawler, $domain, array $articles): Issue
    {
        $domainEnum = Domain::from($domain);
        $crawler = $this->getCrawlerForDomain($domainEnum, $issueCrawler);

        $volume = $crawler->getVolume() ?? 'Özel Sayı';  // Fallback to 'Unknown' if null
        $year = $crawler->getYear() ?? 'Özel Sayı';      // Fallback to 'Unknown' if null
        $number = $crawler->getNumber() ?? 'Özel Sayı';  // Fallback to 'Unknown' if null


        return new Issue($volume, $year, $number, $articles);
    }

    /**
     * @throws Exception
     */
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
