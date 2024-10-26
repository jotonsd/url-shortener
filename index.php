<?php
require_once 'src/UrlShortenerService.php';

$message = '';
$shortUrl = '';
$service = new UrlShortener\UrlShortenerService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $longUrl = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
        if (!$longUrl) {
            throw new Exception('Please enter a valid URL');
        }

        $shortCode = $service->shortenUrl($longUrl);
        $shortUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/s/' . $shortCode;
        $message = '<div class="alert alert-success">URL shortened successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --error-color: #f44336;
            --border-color: #ddd;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .url-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        input[type="url"] {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="url"]:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        button {
            padding: 12px 24px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #dff0d8;
            border: 1px solid #d0e9c6;
            color: #3c763d;
        }

        .alert-danger {
            background-color: #f2dede;
            border: 1px solid #ebcccc;
            color: var(--error-color);
        }

        .result {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            display: none;
        }

        .result.active {
            display: block;
        }

        .short-url {
            display: flex;
            gap: 10px;
            align-items: center;
            background-color: white;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 5px;
        }

        .short-url input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 16px;
        }

        .copy-btn {
            background-color: #6c757d;
            padding: 8px 16px;
        }

        .copy-btn:hover {
            background-color: #5a6268;
        }

        @media (max-width: 600px) {
            .input-group {
                flex-direction: column;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>URL Shortener</h1>

        <?php echo $message; ?>

        <form method="POST" class="url-form">
            <div class="input-group">
                <input type="url" name="url" placeholder="Enter your long URL here" required>
                <button type="submit">Shorten URL</button>
            </div>
        </form>

        <?php if ($shortUrl): ?>
            <div class="result active">
                <h3>Your shortened URL:</h3>
                <div class="short-url">
                    <input type="text" id="shortUrl" value="<?php echo htmlspecialchars($shortUrl); ?>" readonly>
                    <button onclick="copyToClipboard()" class="copy-btn">Copy</button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function copyToClipboard() {
            const shortUrlInput = document.getElementById('shortUrl');
            shortUrlInput.select();
            document.execCommand('copy');

            const copyBtn = document.querySelector('.copy-btn');
            const originalText = copyBtn.textContent;
            copyBtn.textContent = 'Copied!';
            setTimeout(() => {
                copyBtn.textContent = originalText;
            }, 2000);
        }
    </script>
</body>

</html>