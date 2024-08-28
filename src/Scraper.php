<?php
namespace App;

use App\SiteHandlers\ArticleHandle;
use App\SiteHandlers\AzjmHandler;
use App\SiteHandlers\IsAhlakiHandler;
use App\SiteHandlers\OsmanliMirasHandler;
use App\SiteHandlers\PsikologHandler;
use App\SiteHandlers\YeditepeHandler;
use App\Http\Client;
use App\SiteHandlers\IssueHandle;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class Scraper {
    private Client $client;
    private ArticleHandle $articleHandle;
    private IssueHandle $issueHandle;

    public function __construct() {
        $this->client = new Client();
        $this->articleHandle = new ArticleHandle();
        $this->issueHandle = new IssueHandle();
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function scrape(string $url): array {
        $domain = parse_url($url, PHP_URL_HOST);
        $handler = $this->getHandlerForDomain($domain);

        return $handler->handle($url);
    }

    /**
     * @throws Exception
     */
    private function getHandlerForDomain(string $domain): PsikologHandler|OsmanliMirasHandler|YeditepeHandler|AzjmHandler|IsAhlakiHandler
    {
        return match ($domain) {
            'psikolog.org.tr' => new PsikologHandler($this->client, $this->articleHandle, $this->issueHandle),
            'www.osmanlimirasi.net' => new OsmanliMirasHandler($this->client, $this->articleHandle, $this->issueHandle),
            'globalmediajournaltr.yeditepe.edu.tr' => new YeditepeHandler($this->client, $this->articleHandle, $this->issueHandle),
            'azjm.org' => new AzjmHandler($this->client, $this->articleHandle, $this->issueHandle),
            'isahlakidergisi.com' => new IsAhlakiHandler($this->client, $this->articleHandle, $this->issueHandle),
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
            'isahlakidergisi.com' => 'isahlaki.xml',
            default => 'output.xml',
        };
    }

    public function trimAuthorByComma(array|string|null $authorsText): array
    {
        return array_map('trim', explode(',', $authorsText));
    }
}
