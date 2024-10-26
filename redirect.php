<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'src/UrlShortenerService.php';

// Get the short code from the URL parameters
$shortCode = isset($_GET['code']) ? $_GET['code'] : '';
// Debug output
error_log("Received short code: " . $shortCode);

if (empty($shortCode)) {
    header('HTTP/1.1 404 Not Found');
    die('Short code not provided');
}

try {
    $service = new UrlShortener\UrlShortenerService();
    $originalUrl = $service->getOriginalUrl($shortCode);

    if (!empty($originalUrl)) {
        // Debug output
        error_log("Redirecting to: " . $originalUrl);

        // Perform the redirect
        header("Location: " . $originalUrl, true, 302);
        exit();
    } else {
        throw new Exception("URL not found for code: " . $shortCode);
    }
} catch (Exception $e) {
    error_log("Error in redirect.php: " . $e->getMessage());
    header('HTTP/1.1 404 Not Found');
    die('Error: ' . htmlspecialchars($e->getMessage()));
}
