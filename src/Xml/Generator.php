<?php
namespace App\Xml;

use Exception;

class Generator {
    public function generate(array $data): string {
        $xml = new \SimpleXMLElement('<issues/>');

        // Her bir issue için bir "issue" elemanı oluştur
        foreach ($data as $issueData) {
            $issue = $xml->addChild('issue');

            // Volume, Year, Number
            $issue->addChild('volume', htmlspecialchars($issueData['issueData']['volume'] ?? 'BURAYI DOLDUR'));
            $issue->addChild('year', htmlspecialchars($issueData['issueData']['year'] ?? 'BURAYI DOLDUR'));
            $issue->addChild('number', htmlspecialchars($issueData['issueData']['number'] ?? 'BURAYI DOLDUR'));

            // Articles
            $articlesElement = $issue->addChild('articles');
            foreach ($issueData['articles'] as $articleData) {

                // Skip this article if fulltext-file is null or empty
                if (empty($articleData['pdf_url'])) {
                    continue;
                }

                $articleElement = $articlesElement->addChild('article');
                $articleElement->addChild('fulltext-file', htmlspecialchars($articleData['pdf_url']));
                $articleElement->addChild('firstpage', htmlspecialchars($articleData['firstpage'] ?? 'BURAYI DOLDUR'));
                $articleElement->addChild('lastpage', htmlspecialchars($articleData['lastpage'] ?? 'BURAYI DOLDUR'));
                $articleElement->addChild('primary-language', htmlspecialchars($articleData['primary_language'] ?? 'BURAYI DOLDUR'));

                // Translations
                $translationsElement = $articleElement->addChild('translations');

                // Turkish Translation
                $translationElement = $translationsElement->addChild('translation');
                $translationElement->addChild('locale', 'tr'); // Assuming 'tr' as locale
                $translationElement->addChild('title', htmlspecialchars($articleData['title'] ?? 'BURAYI DOLDUR'));
                $translationElement->addChild('abstract', htmlspecialchars($articleData['abstract'] ?? 'BURAYI DOLDUR'));
                $translationElement->addChild('keywords', htmlspecialchars($articleData['keywords'] ?? 'BURAYI DOLDUR'));

                // English Translation (only if en_title is not empty)
                if (!empty($articleData['en_title'])) {
                    $translationElement = $translationsElement->addChild('translation');
                    $translationElement->addChild('locale', 'en'); // Assuming 'en' as locale
                    $translationElement->addChild('title', htmlspecialchars($articleData['en_title']));
                    $translationElement->addChild('abstract', htmlspecialchars($articleData['en_abstract'] ?? 'BURAYI DOLDUR'));
                    $translationElement->addChild('keywords', htmlspecialchars($articleData['en_keywords'] ?? 'BURAYI DOLDUR'));
                }

                // Authors
                if (!empty($articleData['authors'])) {
                    $authorsElement = $articleElement->addChild('authors');
                    foreach ($articleData['authors'] as $author) {
                        $authorsElement->addChild('author', htmlspecialchars($author));
                    }
                } else {
                    $articleElement->addChild('authors', 'BURAYI DOLDUR');
                }

                // Citations - Assuming citations data is available
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

        // Format XML output
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}


