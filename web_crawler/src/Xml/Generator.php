<?php

namespace App\Xml;

use App\Models\Article;
use App\Models\Issue;
use SimpleXMLElement;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Generator
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new Logger('xml_generator');

        $this->logger->pushHandler(new StreamHandler(__DIR__ . '/logs/generator.log', Logger::INFO));
    }

    public function generate(array $issues): string
    {
        // Create the root element <journal>
        $xml = new SimpleXMLElement('<journal/>');

        // Add the <issues> element as a child of <journal>
        $issuesElement = $xml->addChild('issues');

        $skippedIssues = [];
        $missingDataCounts = [
            'PdfUrl' => 0,
            'Title' => 0,
            'Authors' => 0,
            'FirstPage' => 0,
            'LastPage' => 0,
            'Abstract' => 0,
            'Keywords' => 0,
        ];

        /** @var Issue $issueData */
        foreach ($issues as $issueData) {
            $skipIssue = false;

            foreach ($issueData->getArticles() as $articleHandle) {
                if (empty($articleHandle->getPdfUrl())) {
                    $missingDataCounts['PdfUrl']++;
                    $skipIssue = true;
                    break;
                }
            }
            if ($skipIssue) {
                $skippedIssues[] = [
                    'volume' => $issueData->getVolume() ?? 'Unknown',
                    'year' => $issueData->getYear() ?? 'Unknown',
                    'number' => $issueData->getNumber() ?? 'Özel Sayı'
                ];
                continue;
            }

            $issue = $issuesElement->addChild('issue');
            $issue->addChild('volume', htmlspecialchars($issueData->getVolume() ?? ''));
            $issue->addChild('year', htmlspecialchars($issueData->getYear() ?? ''));
            $issue->addChild('number', htmlspecialchars($issueData->getNumber() ?? 'Özel Sayı'));

            $articlesElement = $issue->addChild('articles');
            foreach ($issueData->getArticles() as $articleHandle) {
                /** @var Article $articleHandle */
                $articleElement = $articlesElement->addChild('article');
                $articleElement->addChild('fulltext-file', htmlspecialchars($articleHandle->getPdfUrl() ?? ''));
                $articleElement->addChild('firstpage', htmlspecialchars($articleHandle->getFirstPage() ?? ''));
                $articleElement->addChild('lastpage', htmlspecialchars($articleHandle->getLastPage() ?? ''));

                if (empty($articleHandle->getTitle())) {
                    $missingDataCounts['Title']++;
                }

                if (empty($articleHandle->getFirstPage())) {
                    $missingDataCounts['FirstPage']++;
                }

                if (empty($articleHandle->getLastPage())) {
                    $missingDataCounts['LastPage']++;
                }

                if (empty($articleHandle->getAbstract())) {
                    $missingDataCounts['Abstract']++;
                }

                if (empty($articleHandle->getKeywords())) {
                    $missingDataCounts['Keywords']++;
                }

                $articleElement->addChild('primary-language', htmlspecialchars($articleHandle->getPrimaryLanguage() ?? ''));

                $translationsElement = $articleElement->addChild('translations');
                $translationElement = $translationsElement->addChild('translation');
                $translationElement->addChild('locale', 'tr');
                $translationElement->addChild('title', htmlspecialchars($articleHandle->getTitle() ?? ''));
                $translationElement->addChild('abstract', htmlspecialchars($articleHandle->getAbstract() ?? ''));
                $translationElement->addChild('keywords', htmlspecialchars($articleHandle->getKeywords() ?? ''));

                if (!empty($articleHandle->getEnglishTitle())) {
                    $translationElement = $translationsElement->addChild('translation');
                    $translationElement->addChild('locale', 'en');
                    $translationElement->addChild('title', htmlspecialchars($articleHandle->getEnglishTitle()));
                    $translationElement->addChild('abstract', htmlspecialchars($articleHandle->getEnglishAbstract() ?? ''));
                    $translationElement->addChild('keywords', htmlspecialchars($articleHandle->getEnglishKeywords() ?? ''));
                }

                $authorsElement = $articleElement->addChild('authors');
                $authors = $articleHandle->getAuthors();
                if (!empty($authors)) {
                    foreach ($authors as $author) {
                        $authorElement = $authorsElement->addChild('author');
                        $authorElement->addChild('firstname', htmlspecialchars($author['firstName'] ?? ''));
                        $authorElement->addChild('lastname', htmlspecialchars($author['lastName'] ?? ''));
                    }
                } else {
                    $missingDataCounts['Authors']++;
                    $authorsElement->addChild('author', 'BURAYI DOLDUR');
                }
            }
        }

        if (!empty($skippedIssues)) {
            $skippedElement = $issuesElement->addChild('skipped_issues');
            foreach ($skippedIssues as $skippedIssue) {
                $issueElement = $skippedElement->addChild('issue');
                $issueElement->addChild('volume', htmlspecialchars($skippedIssue['volume']));
                $issueElement->addChild('year', htmlspecialchars($skippedIssue['year']));
                $issueElement->addChild('number', htmlspecialchars($skippedIssue['number']));
            }
        }

        foreach ($missingDataCounts as $key => $count) {
            $this->logger->info("Missing $key: $count");
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}