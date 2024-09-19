<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Scraper;
use App\Xml\Generator;
use GuzzleHttp\Exception\GuzzleException;

function handlePostRequest(): string
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
        $url = trim($_POST['domain']);
        return processScraping($url);
    }
    return '';
}

function processScraping($url): string
{
    try {
        $scraper = new Scraper();
        $data = $scraper->scrape($url);

        $generator = new Generator();
        $xmlOutput = $generator->generate($data);
        $outputDir = '/var/tmp/web_crawler/xml/';
        // Ensure the directory exists
        if (!is_dir($outputDir) && !mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $outputDir));
        }

        $fileName = $outputDir . $scraper->getOutputForDomain($url);
        file_put_contents($fileName, $xmlOutput);

        return displayResult($fileName);
    } catch (Exception $e) {
        return displayError("An error occurred: " . $e->getMessage());
    } catch (GuzzleException $e) {
        return displayError("A Guzzle error occurred: " . $e->getMessage());
    }
}

function displayResult($fileName): string
{
    return "<p>XML file created: <a href='$fileName'>$fileName</a></p>";
}

function displayError($message): string
{
    return "<p>$message</p>";
}

$response = handlePostRequest();

// Include the HTML file
include 'Templates/index.html';
