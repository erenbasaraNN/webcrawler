<?php

namespace App\Dom;

use Symfony\Component\DomCrawler\Crawler;

class Parser {
    public function parse(string $html): Crawler {
        return new Crawler($html);
    }
}
