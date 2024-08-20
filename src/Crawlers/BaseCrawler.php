<?php

namespace App\Crawlers;

use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class BaseCrawler
{
    public function getTitle(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getEnglishTitle(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getAbstract(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getKeywords(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getFirstPage(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getLastPage(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getPdfUrl(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getAuthors(SymfonyCrawler $row): array {
        return [];
    }
    public function getLanguage(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getEnglishAbstract(SymfonyCrawler $row): ?string {
        return null;
    }
    public function getEnglishKeywords(SymfonyCrawler $row): ?string {
        return null;
    }

}