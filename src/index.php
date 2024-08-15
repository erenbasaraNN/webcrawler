<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scraper Interface</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .response-message {
            margin-top: 20px;
            font-size: 16px;
            color: #333;
            text-align: center;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Web Scraper</h1>
    <form method="post" action="">
        <label for="url">Please enter a URL:</label>
        <input type="text" id="url" name="url" placeholder="https://example.com" required>
        <button type="submit">Fetch Data</button>
    </form>

    <div class="response-message">
        <?php
        require_once __DIR__ . '/vendor/autoload.php';
        use App\Scraper;
        use App\Xml\Generator;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
            $url = trim($_POST['url']);

            try {
                $scraper = new Scraper();
                $data = $scraper->scrape($url);

                $generator = new Generator();
                $xmlOutput = $generator->generate($data);

                // Scraper'daki getOutputForDomain fonksiyonunu kullanarak dosya adını al
                $fileName = $scraper->getOutputForDomain($url);

                file_put_contents($fileName, $xmlOutput);
                echo "XML file created: <a href='$fileName'>$fileName</a>";
            } catch (Exception $e) {
                echo "An error occurred: " . $e->getMessage();
            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            }
        }
        ?>
    </div>
</div>

</body>
</html>
