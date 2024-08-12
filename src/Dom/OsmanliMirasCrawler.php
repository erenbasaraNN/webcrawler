<?php
namespace App\Dom;

use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class OsmanliMirasCrawler {
    private SymfonyCrawler $crawler;

    public function __construct(SymfonyCrawler $crawler) {
        $this->crawler = $crawler;
    }

    private function safeFilterText(string $selector): ?string {
        try {
            return $this->crawler->filter($selector)->text();
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function safeFilterAttr(string $selector, string $attribute): ?string {
        try {
            return $this->crawler->filter($selector)->attr($attribute);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function getVolume(): ?string {
        return $this->safeFilterText('h2:contains("Cilt")');
    }

    public function getYear(): ?string {
        return $this->safeFilterText('h2:contains("Yıl")');
    }

    public function getNumber(): ?string {
        return $this->safeFilterText('h2:contains("Sayı")');
    }

    public function getTitle(): ?string {
        return $this->safeFilterAttr('meta[name="citation_title"]', 'content');
    }

    public function getAbstract(): ?string {
        return $this->safeFilterAttr('meta[name="citation_abstract"]', 'content');
    }

    public function getKeywords(): ?string {
        return $this->safeFilterAttr('meta[name="citation_keywords"]', 'content');
    }

    public function getPdfUrl(): ?string {
        return $this->safeFilterAttr('meta[name="citation_pdf_url"]', 'content');
    }

    public function getFirstPage(): ?string {
        return $this->safeFilterAttr('meta[name="citation_firstpage"]', 'content');
    }

    public function getLastPage(): ?string {
        return $this->safeFilterAttr('meta[name="citation_lastpage"]', 'content');
    }

    public function getAuthors(): ?string {
        return $this->safeFilterAttr('meta[name="citation_author"]', 'content');
    }
}
