<?php
namespace App\Xml;

use Exception;

class Generator {
    public function generate(array $data): string {
        $xml = new \SimpleXMLElement('<issues/>');

        // Root element içerisine "issue" elemanını ekle
        $issue = $xml->addChild('issue');

        // Volume, Year, Number
        $issue->addChild('volume', htmlspecialchars($data['x']['volume'] ?? 'BURAYI DOLDUR'));
        $issue->addChild('year', htmlspecialchars($data['x']['year'] ?? 'BURAYI DOLDUR'));
        $issue->addChild('number', htmlspecialchars($data['x']['number'] ?? 'BURAYI DOLDUR'));

        // Articles
        $articlesElement = $issue->addChild('articles');
        foreach ($data['articles'] as $articleData) {
            $articleElement = $articlesElement->addChild('article');
            $articleElement->addChild('fulltext-file', htmlspecialchars($articleData['pdf_url'] ?? 'BURAYI DOLDUR'));
            $articleElement->addChild('firstpage', htmlspecialchars($articleData['firstpage'] ?? 'BURAYI DOLDUR'));
            $articleElement->addChild('lastpage', htmlspecialchars($articleData['lastpage'] ?? 'BURAYI DOLDUR'));
            $articleElement->addChild('primary-language', htmlspecialchars($articleData['primary_language'] ?? 'BURAYI DOLDUR'));

            // Translations
            $translationsElement = $articleElement->addChild('translations');
            $translationElement = $translationsElement->addChild('translation');
            $translationElement->addChild('locale', 'tr'); // Assuming 'tr' as locale
            $translationElement->addChild('title', htmlspecialchars($articleData['title'] ?? 'BURAYI DOLDUR'));
            $translationElement->addChild('abstract', htmlspecialchars($articleData['abstract'] ?? 'BURAYI DOLDUR'));
            $translationElement->addChild('keywords', htmlspecialchars($articleData['keywords'] ?? 'BURAYI DOLDUR'));

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
            } else {
                continue;
            }
        }

        // Format XML output
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->encoding = 'UTF-8';
        $dom->formatOutput = true;
        return $dom->saveXML();
    }
}
