<?php
namespace App\Dom;

use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use App\Http\Client;

class OsmanliMirasCrawler {
    private SymfonyCrawler $crawler;
    private Client $client;

    public function __construct(SymfonyCrawler $crawler, Client $client) {
        $this->crawler = $crawler;
        $this->client = $client;
    }

    private function safeFilterText(string $selector): ?string {
        try {
            return $this->crawler->filter($selector)->text();
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function safeFilterAttr(string $selector, string $attribute): ?string {
        try {
            return $this->crawler->filter($selector)->attr($attribute);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function safeFilterTextXPath(string $xpath): ?string {
        try {
            return trim($this->crawler->filterXPath($xpath)->text());
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function getYear(): ?string {
        return $this->safeFilterTextXPath('//h1/b[contains(text(), "Yıl:")]/following-sibling::text()[1]');
    }

    public function getVolume(): ?string {
        return $this->safeFilterTextXPath('//h1/b[contains(text(), "Cilt:")]/following-sibling::text()[1]');
    }

    public function getNumber(): ?string {
        return $this->safeFilterTextXPath('//h1/b[contains(text(), "Sayı:")]/following-sibling::text()[1]');
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

    public function getAuthors(): array {
        $authors = [];
        $this->crawler->filter('meta[name="citation_author"]')->each(function (SymfonyCrawler $node) use (&$authors) {
            $authors[] = $node->attr('content');
        });
        return $authors;
    }

    public function getLanguage(): ?string {
        return $this->safeFilterAttr('meta[name="citation_language"]', 'content');
    }
}
