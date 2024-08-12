<?php
namespace App;

use App\SiteHandlers\OsmanliMirasHandler;
use App\SiteHandlers\SiteTwoHandler;
use App\Http\Client;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class Scraper {
    private Client $client;

    public function __construct() {
        $this->client = new Client();
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function scrape(string $url): array {
        $domain = parse_url($url, PHP_URL_HOST);
        $handler = $this->getHandlerForDomain($domain);

        $data = $handler->handle($url);

        // Veriyi kontrol et
        if (!isset($data['volume'], $data['year'], $data['number'], $data['articles'])) {
            throw new \Exception("Handler'dan dönen veri yapısı beklenilen formatta değil.");
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    private function getHandlerForDomain(string $domain): SiteTwoHandler|OsmanliMirasHandler
    {
        return match ($domain) {
            'www.osmanlimirasi.net' => new OsmanliMirasHandler($this->client),
            'www.example.com' => new SiteTwoHandler($this->client),
            default => throw new Exception("Domain için uygun bir handler bulunamadı: " . $domain),
        };
    }
}
