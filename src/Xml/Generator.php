<?php

namespace App\Xml;

use App\Crawlers\Models\Article;
use SimpleXMLElement;

class Generator {
    public function generate(array $issues): string {
        $xml = new SimpleXMLElement('<issues/>');
        $skippedIssues = []; // Skipped issues list

        foreach ($issues as $issueData) {
            $skipIssue = false;

            // Check if any article in the issue is missing a PDF URL
            foreach ($issueData['articles'] as $articleHandle) {
                if (empty($articleHandle->getPdfUrl())) {
                    $skipIssue = true;
                    break;
                }
            }

            // Skip the issue if any article is missing a PDF URL
            if ($skipIssue) {
                $skippedIssues[] = [
                    'volume' => $issueData['volume'] ?? 'Unknown',
                    'year' => $issueData['year'] ?? 'Unknown',
                    'number' => $issueData['number'] ?? 'Özel Sayı'
                ];
                continue;
            }

            // Add the issue to the XML
            $issue = $xml->addChild('issue');
            $issue->addChild('volume', htmlspecialchars($issueData['volume'] ?? ''));
            $issue->addChild('year', htmlspecialchars($issueData['year'] ?? ''));
            $issue->addChild('number', htmlspecialchars($issueData['number'] ?? 'Özel Sayı'));

            // Articles
            $articlesElement = $issue->addChild('articles');
            foreach ($issueData['articles'] as $articleHandle) {
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
                if (!empty($articleHandle->getEnTitle())) {
                    $translationElement = $translationsElement->addChild('translation');
                    $translationElement->addChild('locale', 'en');
                    $translationElement->addChild('title', htmlspecialchars($articleHandle->getEnTitle() ?? ''));
                    $translationElement->addChild('abstract', htmlspecialchars($articleHandle->getAbstract() ?? ''));
                    $translationElement->addChild('keywords', htmlspecialchars($articleHandle->getKeywords() ?? ''));
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
