<?php
namespace App\Dom;

use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;


class OsmanliMirasCrawler {
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
            // Citation author içeriklerini alıyoruz
            $authorText = $node->attr('content');

            // Eğer yazarlar virgülle ayrılmışsa, onları bölüyoruz
            $individualAuthors = explode(',', $authorText);

            foreach ($individualAuthors as $author) {
                $author = trim($author);
                $nameParts = explode(' ', $author);
                $lastName = array_pop($nameParts); // Son kısmı soyadı olarak alıyoruz
                $firstName = implode(' ', $nameParts); // Geri kalanı adı oluşturuyor

                // İsim ve soyisimleri yazarlar listesine ekliyoruz
                $authors[] = [
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                ];
            }
        });
        return $authors;
    }

    public function getLanguage(): ?string {
        return $this->safeFilterAttr('meta[name="citation_language"]', 'content');
    }
    
    // INGILIZCE KISIMLAR İÇİN EKLENDİ.
    public function getEnglishTitle(): ?string {
        return $this->safeFilterAttr('meta[name="DC.Title"]', 'content');
    }

    public function getEnglishAbstract(): ?string {
        return $this->safeFilterTextXPath('/html/body/span/div[2]/div/main/div/div/div/div[1]/div/div/div[4]/div/p[1]/text()');
    }

    public function getEnglishKeywords(): ?string {
        return $this->safeFilterTextXPath('/html/body/span/div[2]/div/main/div/div/div/div[1]/div/div/div[4]/div/p[2]/text()');
    }
}
