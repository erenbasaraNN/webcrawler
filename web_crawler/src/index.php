<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scraper Interface</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
    <link href="Assets/styles/base.css" rel="stylesheet">
</head>
<body>

<div class="container" id="scraperForm">
    <h1>Web Scraper</h1>
    <form method="post" action="loading.php">
        <label for="domain">Please select a site.</label>
        <select id="domain" name="domain" required>
            <option value="https://azjm.org/volumes.html">Azerbaijan</option>
            <option value="https://psikolog.org.tr/yayinlar/turk-psikoloji-dergisi">Psikolog.org</option>
            <option value="https://www.osmanlimirasi.net/arsiv.html">Osmanlı Mirasi</option>
            <option value="https://globalmediajournaltr.yeditepe.edu.tr/tr/tum-sayilar">Yeditepe EDU</option>
            <option value="https://isahlakidergisi.com/sayilar">Is Ahlaki</option>
        </select>
        <button type="submit">Fetch Data</button>
    </form>

    <div class="response-message">
        <?php
        require_once __DIR__ . '/vendor/autoload.php';

        use App\Scraper;
        use App\Xml\Generator;
        use GuzzleHttp\Exception\GuzzleException;

        function handlePostRequest()
        {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
                $url = trim($_POST['domain']);
                processScraping($url);
            }
        }

        function processScraping($url)
        {
            try {
                $scraper = new Scraper();
                $data = $scraper->scrape($url);

                $generator = new Generator();
                $xmlOutput = $generator->generate($data);

                $fileName = $scraper->getOutputForDomain($url);
                file_put_contents($fileName, $xmlOutput);

                displayResult($fileName);
            } catch (Exception $e) {
                displayError("An error occurred: " . $e->getMessage());
            } catch (GuzzleException $e) {
                displayError("A Guzzle error occurred: " . $e->getMessage());
            }
        }

        function displayResult($fileName): void
        {
            echo "<p>XML file created: <a href='$fileName'>$fileName</a></p>";
        }

        function displayError($message): void
        {
            echo "<p>$message</p>";
        }

        handlePostRequest();
        ?>
    </div>
</div>

</body>
</html>