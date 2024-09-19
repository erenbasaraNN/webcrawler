<?php
namespace App\Crawlers;

use InvalidArgumentException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;


class IsAhlakiCrawler extends BaseCrawler {
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
        $xpath = '//h2[@class="mb-30"]/strong[contains(text(), "Yıl:")]/following-sibling::text()[1]';
        return $this->safeFilterTextXPath($xpath);
    }

    public function getVolume(): ?string {
        $xpath = '//h2[@class="mb-30"]/strong[contains(text(), "Cilt:")]/following-sibling::text()[1]';
        return $this->safeFilterTextXPath($xpath);
    }
    public function getNumber(): ?string {
        $xpath = '//h2[@class="mb-30"]/strong[contains(text(), "Sayı:")]/following-sibling::text()[1]';
        return $this->safeFilterTextXPath($xpath);
    }

    public function getTitle(SymfonyCrawler $row): ?string {
        $xpath = "//h1[@class='title']";
        return $this->safeFilterTextXPath($xpath);
    }

    public function getAbstract(SymfonyCrawler $row): ?string {
        $xpath = "//div[@id='main-area']//p";
        return $this->safeFilterTextXPath($xpath);
    }

    public function getKeywords(SymfonyCrawler $row): ?string {
        $keywords = $row->filterXPath('//div[@id="main-area"]//a')->each(function (SymfonyCrawler $node) {
            return trim($node->text());
        });

        return !empty($keywords) ? implode(', ', $keywords) : null;
    }


    public function getPdfUrl(SymfonyCrawler $row): ?string {
        $ddCount = $row->filterXPath('//dl[@class="description-item"]//dd')->count();

        $xpath = $ddCount == 7 ? "//dl[@class='description-item']//dd[4]//a/@href" : "//dl[@class='description-item']//dd[5]//a/@href";

        return $this->safeFilterTextXPath($xpath);
    }


    public function getFirstPage(SymfonyCrawler $row): ?string {
        $ddCount = $row->filterXPath('//dl[@class="description-item"]//dd')->count();

        $xpath = $ddCount == 7 ? '//dl[@class="description-item"]//dd[3]' : '//dl[@class="description-item"]//dd[4]';

        $text = $this->safeFilterTextXPath($xpath);

        if ($text !== null && preg_match('/(\d+)-\d+/', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }


    public function getLastPage(SymfonyCrawler $row): ?string {
        $ddCount = $row->filterXPath('//dl[@class="description-item"]//dd')->count();

        $xpath = $ddCount == 7 ? '//dl[@class="description-item"]//dd[3]' : '//dl[@class="description-item"]//dd[4]';

        $text = $this->safeFilterTextXPath($xpath);

        if ($text !== null && preg_match('/\d+-(\d+)/', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }



    public function getAuthors(SymfonyCrawler $row): array {
        $authors = [];

        $row->filterXPath('//dl[@class="description-item"]//dd[1]//a')->each(function (SymfonyCrawler $node) use (&$authors) {
            $authorText = trim($node->text());

            if (str_contains($authorText, ',')) {
                $authorName = explode(',', $authorText)[0];
            } else {
                $authorName = $authorText;
            }

            $nameParts = explode(' ', trim($authorName));
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);

            $authors[] = [
                'firstName' => $firstName,
                'lastName' => $lastName,
            ];
        });

        return $authors;
    }




    public function getLanguage(SymfonyCrawler $row): ?string {
        return $this->safeFilterAttr('meta[name="citation_language"]', 'content');
    }

    public function getEnglishTitle(SymfonyCrawler $row): ?string
    {
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
