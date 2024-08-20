<?php

namespace App\Crawlers;

use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class YeditepeCrawler extends BaseCrawler
{
    private SymfonyCrawler $crawler;

    public function __construct(SymfonyCrawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function getTitle(SymfonyCrawler $row): ?string
    {
        return $row->filterXPath('//td[1]//em/strong')->count() ? $row->filterXPath('//td[1]//em/strong')->text() : 'Editörlerimizden';
    }

    public function getKeywords(SymfonyCrawler $row): ?string
    {
        return $row->filterXPath('//td[2]//p[contains(text(), "Keywords")]')->count() ? $row->filterXPath('//td[2]//p[contains(text(), "Keywords")]')->text() : null;
    }

    public function getFirstPage(SymfonyCrawler $row): ?string
    {
        return $row->filterXPath('//td[3]')->count() ? $row->filterXPath('//td[3]')->text() : null;
    }

    public function getLastPage(SymfonyCrawler $row): ?string
    {
        return $row->filterXPath('//td[4]')->count() ? $row->filterXPath('//td[4]')->text() : null;
    }

    public function getPdfUrl(SymfonyCrawler $row): ?string
    {
        $pdfPath = $row->filterXPath('//td[2]/a')->count() ? $row->filterXPath('//td[2]/a')->attr('href') : null;
        return $pdfPath ? 'https://globalmediajournaltr.yeditepe.edu.tr' . $pdfPath : null;
    }

    public function getAuthors(SymfonyCrawler $row): array
    {
        if ($row->filterXPath('//td[1]/strong')->count() === 0) {
            return [];
        }

        $authorsText = $row->filterXPath('//td[1]/strong')->first()->text();
        $authors = explode('&', $authorsText);
        $authorsArray = [];

        foreach ($authors as $author) {
            $author = trim($author);
            if (empty($author)) {
                continue;
            }

            $nameParts = explode(' ', $author);
            $lastName = array_pop($nameParts) ?? null;
            $firstName = implode(' ', $nameParts) ?? null;

            $authorsArray[] = [
                'firstName' => $firstName,
                'lastName' => $lastName,
            ];
        }

        return $authorsArray;
    }

    public function getVolume(): ?string
    {
        return $this->extractYearVolumeNumber()[1];
    }

    public function getYear(): ?string
    {
        return $this->extractYearVolumeNumber()[0];
    }

    public function getNumber(): ?string
    {
        return $this->extractYearVolumeNumber()[2];
    }

    private function extractYearVolumeNumber(): array
    {
        $text = $this->crawler->filterXPath('//*[@id="block-gmedia-custom-content"]/div/article/div/div/p[1]')->text();

        preg_match('/(\d{4})/', $text, $year);
        preg_match('/Cilt\s(\d+)/', $text, $volume);
        preg_match('/Sayı\s(\d+)/', $text, $number);

        return [
            $year[1] ?? null,
            $volume[1] ?? null,
            $number[1] ?? null
        ];
    }

    public function getEnglishTitle(SymfonyCrawler $row): ?string
    {
        return null;
    }
}
