<?php

namespace UrlShortener;

class UrlShortenerService
{
    private $pdo;
    private const SHORT_URL_LENGTH = 6;
    private const MAX_ATTEMPTS = 5;

    private $host = "localhost";
    private $dbName = "sheba_assessment";
    private $userName = "root";
    private $userPassword = "";

    public function __construct()
    {
        // Initialize database connection
        try {
            $this->pdo = new \PDO(
                "mysql:host=$this->host;dbname=$this->dbName",
                $this->userName,
                $this->userPassword,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            // Create table if it doesn't exist
            $this->initializeDatabase();
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function initializeDatabase(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS urls (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            original_url TEXT NOT NULL,
            short_code VARCHAR(10) UNIQUE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            access_count INT UNSIGNED DEFAULT 0
        )";
        $this->pdo->exec($sql);
    }

    /**
     * Generate a short URL from a long URL
     * @param string $longUrl The original URL to shorten
     * @return string The generated short code
     * @throws \Exception If URL is invalid or generation fails
     */
    public function shortenUrl(string $longUrl): string
    {
        // Validate URL
        if (!filter_var($longUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception("Invalid URL provided");
        }

        // Check if URL already exists
        $existingCode = $this->getExistingCode($longUrl);
        if ($existingCode) {
            return $existingCode;
        }

        // Generate new short code
        $attempts = 0;
        do {
            $shortCode = $this->generateShortCode();
            $attempts++;

            try {
                $this->saveUrl($longUrl, $shortCode);
                return $shortCode;
            } catch (\PDOException $e) {
                // If duplicate key, try again
                if ($attempts >= self::MAX_ATTEMPTS) {
                    throw new \Exception("Failed to generate unique short code");
                }
            }
        } while ($attempts < self::MAX_ATTEMPTS);

        throw new \Exception("Failed to generate short URL");
    }

    /**
     * Get the original URL from a short code
     * @param string $shortCode The short code to look up
     * @return string The original URL
     * @throws \Exception If short code is not found
     */
    public function getOriginalUrl(string $shortCode): string
    {
        // First, update the access count
        $updateStmt = $this->pdo->prepare("
            UPDATE urls 
            SET access_count = access_count + 1 
            WHERE short_code = ?
        ");
        $updateStmt->execute([$shortCode]);

        // Then retrieve the URL
        $selectStmt = $this->pdo->prepare("
            SELECT original_url 
            FROM urls 
            WHERE short_code = ?
        ");
        $selectStmt->execute([$shortCode]);


        $result = $selectStmt->fetch(\PDO::FETCH_COLUMN);

        if (!$result) {
            throw new \Exception("Short URL not found");
        }

        return $result;
    }

    /**
     * Check if URL already exists and return its short code
     */
    private function getExistingCode(string $longUrl): ?string
    {
        $stmt = $this->pdo->prepare("SELECT short_code FROM urls WHERE original_url = ?");
        $stmt->execute([$longUrl]);
        return $stmt->fetch(\PDO::FETCH_COLUMN) ?: null;
    }

    /**
     * Generate a random short code
     */
    private function generateShortCode(): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < self::SHORT_URL_LENGTH; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /**
     * Save URL mapping to database
     */
    private function saveUrl(string $longUrl, string $shortCode): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO urls (original_url, short_code) 
            VALUES (?, ?)
        ");
        $stmt->execute([$longUrl, $shortCode]);
    }
}

// Unit Tests
class UrlShortenerTest
{
    private $service;

    private const SHORT_URL_LENGTH = 6;

    public function __construct()
    {
        $this->service = new UrlShortenerService();
    }

    public function runTests(): void
    {
        $this->testValidUrl();
        $this->testInvalidUrl();
        $this->testDuplicateUrl();
        $this->testNonexistentShortCode();
        echo "All tests passed!\n";
    }

    private function testValidUrl(): void
    {
        $longUrl = "https://www.example.com";
        $shortCode = $this->service->shortenUrl($longUrl);
        assert(strlen($shortCode) === self::SHORT_URL_LENGTH);

        $retrievedUrl = $this->service->getOriginalUrl($shortCode);
        assert($retrievedUrl === $longUrl);
    }

    private function testInvalidUrl(): void
    {
        try {
            $this->service->shortenUrl("not-a-valid-url");
            assert(false, "Should have thrown exception");
        } catch (\Exception $e) {
            assert($e->getMessage() === "Invalid URL provided");
        }
    }

    private function testDuplicateUrl(): void
    {
        $longUrl = "https://www.example.com/duplicate";
        $firstCode = $this->service->shortenUrl($longUrl);
        $secondCode = $this->service->shortenUrl($longUrl);
        assert($firstCode === $secondCode);
    }

    private function testNonexistentShortCode(): void
    {
        try {
            $this->service->getOriginalUrl("nonexistent");
            assert(false, "Should have thrown exception");
        } catch (\Exception $e) {
            assert($e->getMessage() === "Short URL not found");
        }
    }
}
