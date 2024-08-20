<?php
namespace App\Crawlers;

use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class AzjmCrawler extends BaseCrawler
{
    private SymfonyCrawler $crawler;

    public function __construct(SymfonyCrawler $crawler)
    {
        $this->crawler = $crawler;
    }

    private function getVolumeNumberYear(): array
    {
        $headerText = $this->crawler->filterXPath('//h2')->text();

        if (stripos($headerText, 'Special Issue') !== false) {
            preg_match('/\((\d{4})\)/', $headerText, $matches);

            return [
                'volume' => 'Special',
                'number' => 'Special',
                'year' => $matches[1] ?? null,
            ];
        }

        // Handle the regular issue format
        preg_match('/Vol\s*(\d+),\s*No\s*(\d+)\s*\((\d{4})\)/', $headerText, $matches);

        return [
            'volume' => $matches[1] ?? null,
            'number' => $matches[2] ?? null,
            'year' => $matches[3] ?? null,
        ];
    }

    public function getVolume(): ?string
    {
        $volumeData = $this->getVolumeNumberYear();
        return $volumeData['volume'] ?? null;
    }

    public function getNumber(): ?string
    {
        $volumeData = $this->getVolumeNumberYear();
        return $volumeData['number'] ?? null;
    }

    public function getYear(): ?string
    {
        $volumeData = $this->getVolumeNumberYear();
        return $volumeData['year'] ?? null;
    }

    public function getTitle(SymfonyCrawler $row): ?string
    {
        return $row->filterXPath('//div[@class="tocTitle"]/a')->text();
    }

    public function getPdfUrl(SymfonyCrawler $row): ?string
    {
        $link = $row->filterXPath('//div[@class="tocTitle"]/a')->attr('href');
        if (!str_ends_with($link, '.pdf')) {
            return null;
        }
        return str_starts_with($link, 'http') ? $link : 'https://azjm.org/' . ltrim($link, '/');
    }

    public function getAuthors(SymfonyCrawler $row): array
    {
        $authorsText = $row->filterXPath('//div[@class="tocAuthors"]')->text();
        $authors = array_map('trim', explode(',', $authorsText));
        $authorsArray = [];

        foreach ($authors as $author) {
            $nameParts = explode(' ', $author);
            $lastName = array_pop($nameParts);
            $firstName = implode(' ', $nameParts);
            $authorsArray[] = [
                'firstName' => $firstName,
                'lastName' => $lastName,
            ];
        }

        return $authorsArray;
    }

    public function getFirstPage(SymfonyCrawler $row): ?string
    {
        $pagesText = $row->filter('.tocPages')->text();
        $pages = explode('-', $pagesText);
        return isset($pages[0]) ? trim($pages[0]) : null;
    }

    public function getLastPage(SymfonyCrawler $row): ?string
    {
        $pagesText = $row->filter('.tocPages')->text();
        $pages = explode('-', $pagesText);
        return isset($pages[1]) ? trim($pages[1]) : null;
    }

    public function getEnglishTitle(SymfonyCrawler $row): ?string {
        return null;
    }
}
