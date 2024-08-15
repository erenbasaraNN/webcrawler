<?php
namespace App\Dom;

use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;

class AzjmCrawler
{
    private SymfonyCrawler $crawler;

    public function __construct(SymfonyCrawler $crawler)
    {
        $this->crawler = $crawler;
    }

    // Extracts the volume, number, and year from the <h2> tag
    public function getVolumeNumberYear(): array
    {
        $headerText = $this->crawler->filterXPath('//h2')->text(); // e.g., "Vol 13, No 2 (2023)"
        preg_match('/Vol\s(\d+),\sNo\s(\d+)\s\((\d{4})\)/', $headerText, $matches);

        return [
            'volume' => $matches[1] ?? null,
            'number' => $matches[2] ?? null,
            'year' => $matches[3] ?? null,
        ];
    }

    public function getTitle(SymfonyCrawler $row): ?string
    {
        return $row->filterXPath('//div[@class="tocTitle"]/a')->text();
    }

    public function getPdfUrl(SymfonyCrawler $row): ?string
    {
        // Extract the URL from the <a> tag inside the .tocTitle div
        $link = $row->filterXPath('//div[@class="tocTitle"]/a')->attr('href');

        // Check if the URL ends with '.pdf'
        if (!str_ends_with($link, '.pdf')) {
            return null;
        }

        // Handle relative URLs by prepending the base URL if necessary
        return str_starts_with($link, 'http') ? $link : 'https://azjm.org/' . ltrim($link, '/');
    }


    public function getAuthors(SymfonyCrawler $row): array
    {
        $authorsText = $row->filterXPath('//div[@class="tocAuthors"]')->text();
        // Split the authors by commas
        $authors = array_map('trim', explode(',', $authorsText));

        $authorsArray = [];
        foreach ($authors as $author) {
            // Split the author's name into parts (first name and last name)
            $nameParts = explode(' ', $author);
            $lastName = array_pop($nameParts); // Get the last part as the last name
            $firstName = implode(' ', $nameParts); // Join the rest as the first name

            // Add the author to the array
            $authorsArray[] = [
                'firstName' => $firstName,
                'lastName' => $lastName,
            ];
        }

        return $authorsArray;
    }

    public function getFirstPage(SymfonyCrawler $row): ?string
    {
        // Match any element with the class `tocPages`
        $pagesText = $row->filter('.tocPages')->text();
        $pages = explode('-', $pagesText);

        return isset($pages[0]) ? trim($pages[0]) : null;
    }

    public function getLastPage(SymfonyCrawler $row): ?string
    {
        // Match any element with the class `tocPages`
        $pagesText = $row->filter('.tocPages')->text();
        $pages = explode('-', $pagesText);

        return isset($pages[1]) ? trim($pages[1]) : null;
    }

}
