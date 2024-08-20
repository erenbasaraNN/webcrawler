<?php

namespace App\Xml;

use App\Crawlers\Models\Article;
use App\Crawlers\Models\Issue;
use SimpleXMLElement;

class Generator {
    public function generate(array $issues): string {
        $xml = new SimpleXMLElement('<issues/>');
        $skippedIssues = []; // Skipped issues list

        /** @var Issue $issueData */
        foreach ($issues as $issueData) {
            $skipIssue = false;

            // Check if any article in the issue is missing a PDF URL
            foreach ($issueData->getArticles() as $articleHandle) {  // Use getArticles() method to get articles
                if (empty($articleHandle->getPdfUrl())) {
                    $skipIssue = true;
                    break;
                }
            }

            // Skip the issue if any article is missing a PDF URL
            if ($skipIssue) {
                $skippedIssues[] = [
                    'volume' => $issueData->getVolume() ?? 'Unknown',
                    'year' => $issueData->getYear() ?? 'Unknown',
                    'number' => $issueData->getNumber() ?? 'Özel Sayı'
                ];
                continue;
            }

            // Add the issue to the XML
            $issue = $xml->addChild('issue');
            $issue->addChild('volume', htmlspecialchars($issueData->getVolume() ?? ''));
            $issue->addChild('year', htmlspecialchars($issueData->getYear() ?? ''));
            $issue->addChild('number', htmlspecialchars($issueData->getNumber() ?? 'Özel Sayı'));

            // Articles
            $articlesElement = $issue->addChild('articles');
            foreach ($issueData->getArticles() as $articleHandle) {  // Use getArticles() method to get articles
                /** @var Article $articleHandle */
                $articleElement = $articlesElement->addChild('article');
                $articleElement->addChild('fulltext-file', htmlspecialchars($articleHandle->getPdfUrl() ?? ''));
                $articleElement->addChild('firstpage', htmlspecialchars($articleHandle->getFirstPage() ?? ''));
                $articleElement->addChild('lastpage', htmlspecialchars($articleHandle->getLastPage() ?? ''));
                $articleElement->addChild('primary-language', htmlspecialchars($articleHandle->getPrimaryLanguage() ?? ''));

                // Translations
                $translationsElement = $articleElement->addChild('translations');

                // Turkish Translation
                $translationElement = $translationsElement->addChild('translation');
                $translationElement->addChild('locale', 'tr');
                $translationElement->addChild('title', htmlspecialchars($articleHandle->getTitle() ?? ''));
                $translationElement->addChild('abstract', htmlspecialchars($articleHandle->getAbstract() ?? ''));
                $translationElement->addChild('keywords', htmlspecialchars($articleHandle->getKeywords() ?? ''));

                // English Translation (if available)
                if (!empty($articleHandle->getEnglishTitle())) {
                    $translationElement = $translationsElement->addChild('translation');
                    $translationElement->addChild('locale', 'en');
                    $translationElement->addChild('title', htmlspecialchars($articleHandle->getEnglishTitle()));
                    $translationElement->addChild('abstract', htmlspecialchars($articleHandle->getEnglishAbstract() ?? ''));
                    $translationElement->addChild('keywords', htmlspecialchars($articleHandle->getEnglishKeywords() ?? ''));
                }

                // Authors
                $authorsElement = $articleElement->addChild('authors');
                $authors = $articleHandle->getAuthors();
                if (!empty($authors)) {
                    foreach ($authors as $author) {
                        $authorElement = $authorsElement->addChild('author');
                        $authorElement->addChild('firstname', htmlspecialchars($author['firstName'] ?? ''));
                        $authorElement->addChild('lastname', htmlspecialchars($author['lastName'] ?? ''));
                    }
                } else {
                    $authorsElement->addChild('author', 'BURAYI DOLDUR');
                }
            }
        }

        // Add skipped issues at the end of the XML
        if (!empty($skippedIssues)) {
            $skippedElement = $xml->addChild('skipped_issues');
            foreach ($skippedIssues as $skippedIssue) {
                $issueElement = $skippedElement->addChild('issue');
                $issueElement->addChild('volume', htmlspecialchars($skippedIssue['volume']));
                $issueElement->addChild('year', htmlspecialchars($skippedIssue['year']));
                $issueElement->addChild('number', htmlspecialchars($skippedIssue['number']));
            }
        }

        // Format the XML output
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
