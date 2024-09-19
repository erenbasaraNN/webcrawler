<?php
namespace App\Crawlers;

use Exception;
use GuzzleHttp\Client;
use App\Services\MergePDF;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use App\Scraper;

class PsikologCrawler extends BaseCrawler {
    private SymfonyCrawler $crawler;
    private Scraper $scraper;
    public function __construct(SymfonyCrawler $crawler) {
        $this->crawler = $crawler;
        $this->scraper = new Scraper();
    }

    public function getTitle(SymfonyCrawler $row): ?string {
        return $row->filter('div.yayinDiv > div.yayinsutun > p')->first()->text() ?? null;
    }

    public function getAuthors(SymfonyCrawler $row): array {

        $authorsText = $row->filter('div.yayinDiv > div.yayinsutun > i')->text();
        $authorsText = preg_replace('/^Yazar\s*:\s*/', '', $authorsText);

        $authors = $this->scraper->trimAuthorByComma($authorsText);

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


    /**
     * @throws Exception
     */
    public function getPdfUrl(SymfonyCrawler $row): ?string {
        $turkishPdfPath = $row->filter('a[title="TÜRKÇE PDF"]')->count() ? $row->filter('a[title="TÜRKÇE PDF"]')->attr('href') : null;
        $englishPdfPath = $row->filter('a[title="İNGİLİZCE PDF"]')->count() ? $row->filter('a[title="İNGİLİZCE PDF"]')->attr('href') : null;

        if (!$turkishPdfPath && !$englishPdfPath) {
            return null;
        }

        $cleanPdfPath = function($path) {
            return preg_replace('/^\.\.\//', '', $path);
        };

        $pdfUrls = [];
        $client = new Client();

        if ($turkishPdfPath) {
            $cleanedTurkishPdfPath = 'https://psikolog.org.tr/' . $cleanPdfPath($turkishPdfPath);
            if ($this->isUrlValid($client, $cleanedTurkishPdfPath)) {
                $pdfUrls[] = $cleanedTurkishPdfPath;
            }
        }

        if ($englishPdfPath) {
            $cleanedEnglishPdfPath = 'https://psikolog.org.tr/' . $cleanPdfPath($englishPdfPath);
            if ($this->isUrlValid($client, $cleanedEnglishPdfPath)) {
                $pdfUrls[] = $cleanedEnglishPdfPath;
            }
        }

        if (count($pdfUrls) > 1) {
            return (new MergePDF())->merge($pdfUrls[0], $pdfUrls[1]);
        }

        if (count($pdfUrls) === 1) {
            return $pdfUrls[0];
        }

        return null;
    }

    /**
     * Check if the URL returns a 200 OK status code
     *
     * @param Client $client
     * @param string $url
     * @return bool
     */
    private function isUrlValid(Client $client, string $url): bool {
        try {
            $response = $client->head($url);
            return $response->getStatusCode() === 200;
        } catch (Exception) {
            return false;
        } catch (GuzzleException) {
        }
    }



    public function getVolume(): ?string {
        return $this->extractYearVolumeNumber()[1];
    }

    public function getYear(): ?string {
        return $this->extractYearVolumeNumber()[0];
    }

    public function getNumber(): ?string {
        return $this->extractYearVolumeNumber()[2];
    }

    private function extractYearVolumeNumber(): array {
        $text = $this->crawler->filterXPath('//a[contains(@class, "accord_baslik")]')->text();
        if (!$text) {
            return [null, null, null];
        }

        preg_match('/Cilt\s*(\d+)/i', $text, $volume);
        preg_match('/Sayı\s*(\d+)/i', $text, $number);
        preg_match('/\((\d{4})\)/', $text, $year);

        return [
            $year[1] ?? null,  // Year
            $volume[1] ?? null, // Volume
            $number[1] ?? null  // Number
        ];
    }

    public function getEnglishTitle(SymfonyCrawler $row): ?string {
        return null;
    }

}
