<?php
namespace App\Dom;

use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use App\Http\Client;

class PsikologCrawler {
    private SymfonyCrawler $crawler;
    private Client $client;

    public function __construct(SymfonyCrawler $crawler, Client $client) {
        $this->crawler = $crawler;
        $this->client = $client;
    }

    public function getTitle(SymfonyCrawler $row): ?string {
        return $row->filter('div.yayinDiv > div.yayinsutun > p')->first()->text() ?? null;
    }

    public function getAuthors(SymfonyCrawler $row): array {
        $authorsText = $row->filter('div.yayinDiv > div.yayinsutun > i')->text();
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

    public function getPdfUrls(SymfonyCrawler $row): ?string {
        // Türkçe PDF'i kontrol et
        $turkishPdfPath = $row->filter('a[title="TÜRKÇE PDF"]')->count() ? $row->filter('a[title="TÜRKÇE PDF"]')->attr('href') : null;
        // İngilizce PDF'i kontrol et
        $englishPdfPath = $row->filter('a[title="İNGİLİZCE PDF"]')->count() ? $row->filter('a[title="İNGİLİZCE PDF"]')->attr('href') : null;

        // Eğer ikisi de yoksa null döndür
        if (!$turkishPdfPath && !$englishPdfPath) {
            return null;
        }

        // Eğer biri varsa, URL'leri birleştir ve döndür
        $pdfUrls = [];
        if ($turkishPdfPath) {
            $pdfUrls[] = 'https://psikolog.org.tr' . $turkishPdfPath;
        }
        if ($englishPdfPath) {
            $pdfUrls[] = 'https://psikolog.org.tr' . $englishPdfPath;
        }

        // URL'leri virgülle ayırarak döndür
        return implode(',', $pdfUrls);
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
        // Extract the text from the accord_baslik class
        $text = $this->crawler->filterXPath('//a[contains(@class, "accord_baslik")]')->text();

        // Print the extracted text for debugging purposes
        echo "Extracted text: " . $text . "\n";

        // Check if the text is found
        if (!$text) {
            return [null, null, null]; // Return nulls if the text is empty
        }

        // Extract the year, volume, and number using regular expressions
        preg_match('/Cilt\s*(\d+)/i', $text, $volume); // Matches the volume (e.g., 38)
        preg_match('/Sayı\s*(\d+)/i', $text, $number); // Matches the number (e.g., 91)
        preg_match('/\((\d{4})\)/', $text, $year); // Matches the year within parentheses (e.g., 2023)

        // Debugging output
        echo "Volume: " . ($volume[1] ?? 'Not found') . "\n";
        echo "Number: " . ($number[1] ?? 'Not found') . "\n";
        echo "Year: " . ($year[1] ?? 'Not found') . "\n";

        return [
            $year[1] ?? null,  // Year
            $volume[1] ?? null, // Volume
            $number[1] ?? null  // Number
        ];
    }


    // English versions
    public function getEnglishAbstract(SymfonyCrawler $row): ?string {
        return null; // No abstract available
    }

    public function getEnglishKeywords(SymfonyCrawler $row): ?string {
        return null; // No keywords available
    }
    public function getEnglishTitle(SymfonyCrawler $row): ?string {
        return null; // No English title available

    {

    }
}
}
