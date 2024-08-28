<?php
namespace App\Crawlers;

use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;


class OsmanliMirasCrawler extends BaseCrawler {
    private SymfonyCrawler $crawler;

    public function __construct(SymfonyCrawler $crawler) {
        $this->crawler = $crawler;
    }

    public function safeFilterAttr(string $selector, string $attribute): ?string {
        try {
            return $this->crawler->filter($selector)->attr($attribute);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    private function safeFilterTextXPath(string $xpath): ?string {
        try {
            return trim($this->crawler->filterXPath($xpath)->text());
        } catch (InvalidArgumentException) {
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

    public function getTitle(SymfonyCrawler $row): ?string {
        return $this->safeFilterAttr('meta[name="citation_title"]', 'content');
    }

    public function getAbstract(SymfonyCrawler $row): ?string {
        return $this->safeFilterAttr('meta[name="citation_abstract"]', 'content');
    }

    public function getKeywords(SymfonyCrawler $row): ?string {
        return $this->safeFilterAttr('meta[name="citation_keywords"]', 'content');
    }

    public function getPdfUrl(SymfonyCrawler $row): ?string {
        return $this->safeFilterAttr('meta[name="citation_pdf_url"]', 'content');
    }

    public function getFirstPage(SymfonyCrawler $row): ?string {
        return $this->safeFilterAttr('meta[name="citation_firstpage"]', 'content');
    }

    public function getLastPage(SymfonyCrawler $row): ?string {
        return $this->safeFilterAttr('meta[name="citation_lastpage"]', 'content');
    }

    public function getAuthors(SymfonyCrawler $row): array {
        $authors = [];
        $this->crawler->filter('meta[name="citation_author"]')->each(function (SymfonyCrawler $node) use (&$authors) {
            $authorText = $node->attr('content');

            $individualAuthors = explode(',', $authorText);

            foreach ($individualAuthors as $author) {
                $author = trim($author);
                $nameParts = explode(' ', $author);
                $lastName = array_pop($nameParts);
                $firstName = implode(' ', $nameParts);

                $authors[] = [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                ];
            }
        });
        return $authors;
    }

    public function getLanguage(SymfonyCrawler $row): ?string {
        return $this->safeFilterAttr('meta[name="citation_language"]', 'content');
    }

    public function getEnglishTitle(SymfonyCrawler $row): ?string
    {
        $titleNode = $row->filter('h4');
        if ($titleNode->count() > 0) {
            return $titleNode->text(null, false);
        }
        return null;
    }

    public function getEnglishKeywords(SymfonyCrawler $row): ?string
    {
        $keywordsNode = $row->filterXPath('(//div/p[2])[2]');
        if ($keywordsNode->count() > 0) {
            $strongNode = $keywordsNode->filter('strong');
            $keywordsText = $strongNode->count() > 0 ? str_replace($strongNode->text(), '', $keywordsNode->text()) : $keywordsNode->text();
            return trim($keywordsText);
        }
        return null;
    }

    public function getEnglishAbstract(SymfonyCrawler $row): ?string
    {
        $abstractNode = $row->filterXPath('(//div/p[1])[2]');
        if ($abstractNode->count() > 0) {
            return $abstractNode->text(null, false);
        }
        return null;
    }
}
