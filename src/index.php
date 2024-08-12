
<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Scraper;
use App\Xml\Generator;

header('Content-Type: application/xml; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $url = trim($_POST['url']);

    try {
        $scraper = new Scraper();
        $data = $scraper->scrape($url);

        $generator = new Generator();
        $xmlOutput = $generator->generate($data);

        file_put_contents('output.xml', $xmlOutput);
        echo "XML dosyası oluşturuldu: <a href='output.xml'>output.xml</a>";
    } catch (Exception $e) {
        echo "Bir hata oluştu: " . $e->getMessage();
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
    }
} else {
    echo '<form method="post" action="">
            <label for="url">Lütfen bir URL girin:</label>
            <input type="text" id="url" name="url" required>
            <button type="submit">Veri Çek</button>
          </form>';
}
