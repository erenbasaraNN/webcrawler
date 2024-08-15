<?php
namespace App\Xml;

use Exception;

class Generator {
    public function generate(array $data): string {
        $xml = new \SimpleXMLElement('<issues/>');
        $skippedIssues = []; // Array to store skipped issues

        // Loop through each issue to create an "issue" element
        foreach ($data as $issueData) {
            $skipIssue = false;

            // Check if any article in the issue has a missing PDF URL
            foreach ($issueData['articles'] as $articleData) {
                if (empty($articleData['pdf_url'])) {
                    $skipIssue = true;
                    break; // Skip the entire issue if any article is missing a PDF URL
                }
            }

            // If any article is missing a PDF URL, skip the entire issue
            if ($skipIssue) {
                $skippedIssues[] = [
                    'volume' => $issueData['volume'] ?? 'Unknown',
                    'year' => $issueData['year'] ?? 'Unknown',
                    'number' => $issueData['number'] ?? 'Özel Sayı'
                ];
                continue; // Skip the current issue
            }

            // Add the issue to the XML if all articles have valid PDF URLs
            $issue = $xml->addChild('issue');

            // Volume, Year, Number
            $issue->addChild('volume', htmlspecialchars($issueData['volume'] ?? ''));
            $issue->addChild('year', htmlspecialchars($issueData['year'] ?? ''));
            $issue->addChild('number', htmlspecialchars($issueData['number'] ?? 'Özel Sayı'));

            // Articles
            $articlesElement = $issue->addChild('articles');
            foreach ($issueData['articles'] as $articleData) {
                $articleElement = $articlesElement->addChild('article');
                $articleElement->addChild('fulltext-file', htmlspecialchars($articleData['pdf_url'] ?? ''));
                $articleElement->addChild('firstpage', htmlspecialchars($articleData['firstPage'] ?? ''));
                $articleElement->addChild('lastpage', htmlspecialchars($articleData['lastPage'] ?? ''));
                $articleElement->addChild('primary-language', htmlspecialchars($articleData['primary_language'] ?? ''));

                // Translations
                $translationsElement = $articleElement->addChild('translations');

                // Turkish Translation
                $translationElement = $translationsElement->addChild('translation');
                $translationElement->addChild('locale', 'tr'); // Assuming 'tr' as locale
                $translationElement->addChild('title', htmlspecialchars($articleData['title'] ?? ''));
                $translationElement->addChild('abstract', htmlspecialchars($articleData['abstract'] ?? ''));
                $translationElement->addChild('keywords', htmlspecialchars($articleData['keywords'] ?? ''));

                // English Translation (only if en_title is not empty)
                if (!empty($articleData['en_title'])) {
                    $translationElement = $translationsElement->addChild('translation');
                    $translationElement->addChild('locale', 'en'); // Assuming 'en' as locale
                    $translationElement->addChild('title', htmlspecialchars($articleData['en_title'] ?? ''));
                    $translationElement->addChild('abstract', htmlspecialchars($articleData['en_abstract'] ?? ''));
                    $translationElement->addChild('keywords', htmlspecialchars($articleData['en_keywords'] ?? ''));
                }

                // Authors
                $authorsElement = $articleElement->addChild('authors');
                if (!empty($articleData['authors'])) {
                    foreach ($articleData['authors'] as $author) {
                        $authorElement = $authorsElement->addChild('author');
                        $authorElement->addChild('firstname', htmlspecialchars($author['firstName'] ?? ''));
                        $authorElement->addChild('lastname', htmlspecialchars($author['lastName'] ?? ''));
                    }
                } else {
                    $authorsElement->addChild('author', 'BURAYI DOLDUR');
                }

                // Citations
                $citationsElement = $articleElement->addChild('citations');
                if (!empty($articleData['citations'])) {
                    foreach ($articleData['citations'] as $index => $citation) {
                        $citationElement = $citationsElement->addChild('citation');
                        $citationElement->addChild('row', $index + 1);
                        $citationElement->addChild('value', htmlspecialchars($citation ?? ''));
                    }
                }
            }
        }

        // Add a section for skipped issues at the end of the XML
        if (!empty($skippedIssues)) {
            $skippedElement = $xml->addChild('skipped_issues');
            foreach ($skippedIssues as $skippedIssue) {
                $issueElement = $skippedElement->addChild('issue');
                $issueElement->addChild('volume', htmlspecialchars($skippedIssue['volume']));
                $issueElement->addChild('year', htmlspecialchars($skippedIssue['year']));
                $issueElement->addChild('number', htmlspecialchars($skippedIssue['number']));
            }
        }

        // Format XML output
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
