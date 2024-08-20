<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scraper Interface</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-weight: bold;
            background-color: #333333;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            font-weight: bolder;
            color: #333;
            margin-bottom: 35px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            font-weight: bold;
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            align-content: center;
        }
        button:hover {
            background-color: #333333;
            color: #4CAF50;
        }
        .response-message {
            margin-top: 20px;
            font-size: 16px;
            color: #333;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        option {
            font-size: 16px;
            font-weight: normal;
            align-content: center;
        }
    </style>
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
        </select>
        <button type="submit">Fetch Data</button>
    </form>

    <div class="response-message">
        <?php
        require_once __DIR__ . '/vendor/autoload.php';
        use App\Scraper;
        use App\Xml\Generator;
        use GuzzleHttp\Exception\GuzzleException;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['domain'])) {
            $url = trim($_POST['domain']);

            try {
                // Scraper logic
                $scraper = new Scraper();
                $data = $scraper->scrape($url);

                // XML generation logic
                $generator = new Generator();
                $xmlOutput = $generator->generate($data);

                // Save the XML to a file
                $fileName = $scraper->getOutputForDomain($url);
                file_put_contents($fileName, $xmlOutput);

                // Display the result
                echo "<p>XML file created: <a href='$fileName'>$fileName</a></p>";
            } catch (Exception $e) {
                echo "An error occurred: " . $e->getMessage();
            } catch (GuzzleException $e) {
                echo "A Guzzle error occurred: " . $e->getMessage();
            }
        }
        ?>
    </div>
</div>

</body>
</html>
