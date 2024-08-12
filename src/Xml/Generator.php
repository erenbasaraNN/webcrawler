<?php
namespace App\Xml;

use Exception;

class Generator {
    public function generate(array $data): string {
        $xml = new \SimpleXMLElement('<root/>');

        // Root element içerisine "issue" elemanını ekle
        $issue = $xml->addChild('issue');

        $issue->addChild('volume', htmlspecialchars($this->validateData($data['volume'], 'Volume')));
        $issue->addChild('year', htmlspecialchars($this->validateData($data['year'], 'Year')));
        $issue->addChild('number', htmlspecialchars($this->validateData($data['number'], 'Number')));

        // Articles
        $articlesElement = $issue->addChild('articles');
        foreach ($data['articles'] as $articleData) {
            $articleElement = $articlesElement->addChild('article');
            $articleElement->addChild('title', htmlspecialchars($this->validateData($articleData['title'], 'Title')));
            $articleElement->addChild('abstract', htmlspecialchars($articleData['abstract'] ?? 'No abstract available'));
            $articleElement->addChild('keywords', htmlspecialchars($articleData['keywords'] ?? 'No keywords available'));
            $articleElement->addChild('pdf_url', $articleData['pdf_url'] ?? 'No PDF URL available');
            $articleElement->addChild('firstpage', $articleData['firstpage'] ?? 'N/A');
            $articleElement->addChild('lastpage', $articleData['lastpage'] ?? 'N/A');
            $articleElement->addChild('authors', htmlspecialchars($articleData['authors'] ?? 'No authors available'));
        }

        // XML'i biçimlendir
        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    private function validateData(?string $data, string $field): string {
        if ($data === null) {
            throw new Exception("$field is missing in the provided data.");
        }
        return $data;
    }
}
