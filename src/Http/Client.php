<?php

namespace App\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

class Client {
    private GuzzleClient $client;

    public function __construct() {
        $this->client = new GuzzleClient();
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $url): string {
        $response = $this->client->request('GET', $url);
        return $response->getBody()->getContents();
    }
}
