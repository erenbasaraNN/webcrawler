<?php
namespace App;

use App\SiteHandlers\OsmanliMirasHandler;
use App\SiteHandlers\PsikologHandler;
use App\Http\Client;
use App\SiteHandlers\YeditepeHandler;
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


        return $data;
    }

    /**
     * @throws Exception
     */
    private function getHandlerForDomain(string $domain): PsikologHandler|OsmanliMirasHandler|YeditepeHandler
    {
        return match ($domain) {
            'psikolog.org.tr' => new PsikologHandler($this->client),
            'www.osmanlimirasi.net' => new OsmanliMirasHandler($this->client),
            'globalmediajournaltr.yeditepe.edu.tr' => new YeditepeHandler($this->client),
            default => throw new \Exception("No handler found for domain: " . $domain),
        };
    }
    public function getOutputForDomain(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        return match ($domain) {
            'www.osmanlimirasi.net' => 'osmanlimirasi.xml',
            'globalmediajournaltr.yeditepe.edu.tr' => 'yeditepe.xml',
            'psikolog.org.tr' => 'psikolog.xml',
            default => 'output.xml',
        };
    }
}
