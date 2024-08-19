<?php
namespace App;

use App\SiteHandlers\ArticleHandle;
use App\SiteHandlers\AzjmHandler;
use App\SiteHandlers\OsmanliMirasHandler;
use App\SiteHandlers\PsikologHandler;
use App\SiteHandlers\YeditepeHandler;
use App\Http\Client;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class Scraper {
    private Client $client;
    private ArticleHandle $articleHandle;

    public function __construct() {
        $this->client = new Client();
        $this->articleHandle = new ArticleHandle();
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function scrape(string $url): array {
        $domain = parse_url($url, PHP_URL_HOST);
        $handler = $this->getHandlerForDomain($domain);

        // Veriyi kontrol et
        return $handler->handle($url);
    }

    /**
     * @throws Exception
     */
    private function getHandlerForDomain(string $domain): PsikologHandler|OsmanliMirasHandler|YeditepeHandler|AzjmHandler
    {
        return match ($domain) {
            'psikolog.org.tr' => new PsikologHandler($this->client, $this->articleHandle),
            'www.osmanlimirasi.net' => new OsmanliMirasHandler($this->client, $this->articleHandle),
            'globalmediajournaltr.yeditepe.edu.tr' => new YeditepeHandler($this->client, $this->articleHandle),
            'azjm.org' => new AzjmHandler($this->client, $this->articleHandle),
            default => throw new Exception("No handler found for domain: " . $domain),
        };
    }

    public function getOutputForDomain(string $url): string
    {
        $domain = parse_url($url, PHP_URL_HOST);
        return match ($domain) {
            'www.osmanlimirasi.net' => 'osmanlimirasi.xml',
            'globalmediajournaltr.yeditepe.edu.tr' => 'yeditepe.xml',
            'psikolog.org.tr' => 'psikolog.xml',
            'azjm.org' => 'azjm.xml',
            default => 'output.xml',
        };
    }

    public function trimAuthorByComma(array|string|null $authorsText): array
    {
        return array_map('trim', explode(',', $authorsText));
    }
}
