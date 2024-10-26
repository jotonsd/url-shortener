<?php

use PHPUnit\Framework\TestCase;
use UrlShortener\UrlShortenerService;

class UrlShortenerServiceTest extends TestCase
{
    private $urlShortener;

    protected function setUp(): void
    {
        $this->urlShortener = new UrlShortenerService();
    }

    public function testShortenUrl()
    {
        $longUrl = "https://example.com";

        // Call shortenUrl and check if it returns a short code
        $shortCode = $this->urlShortener->shortenUrl($longUrl);
        $this->assertIsString($shortCode, "The short code should be a string");
        $this->assertEquals(strlen($shortCode), 6, "The short code should be 6 characters long");

        // Ensure the same URL returns the same short code
        $shortCodeAgain = $this->urlShortener->shortenUrl($longUrl);
        $this->assertEquals($shortCode, $shortCodeAgain, "The same long URL should return the same short code");
    }

    public function testGetOriginalUrl()
    {
        $longUrl = "https://example.com";

        // Shorten the URL
        $shortCode = $this->urlShortener->shortenUrl($longUrl);

        // Retrieve the original URL
        $retrievedUrl = $this->urlShortener->getOriginalUrl($shortCode);
        $this->assertEquals($longUrl, $retrievedUrl, "The retrieved URL should match the original URL");
    }

    public function testInvalidUrlThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid URL provided");

        // Attempt to shorten an invalid URL
        $this->urlShortener->shortenUrl("invalid-url");
    }

    public function testNonExistentShortCodeThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Short URL not found");

        // Attempt to retrieve a non-existent short code
        $this->urlShortener->getOriginalUrl("nonexistent");
    }
}
