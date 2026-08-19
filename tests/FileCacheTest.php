<?php
declare(strict_types=1);
namespace CityLens\Tests;
use CityLens\Cache\FileCache;
use CityLens\WeatherReport;
use PHPUnit\Framework\TestCase;
final class FileCacheTest extends TestCase
{
    private string $directory;
    protected function setUp(): void { $this->directory = sys_get_temp_dir() . '/citylens-test-' . bin2hex(random_bytes(6)); }
    protected function tearDown(): void { foreach (glob($this->directory . '/*') ?: [] as $file) { unlink($file); } if (is_dir($this->directory)) { rmdir($this->directory); } }
    public function testCacheHitUsesNormalizedCityAndUnits(): void
    {
        $cache = new FileCache($this->directory, 600); $report = $this->report(); $cache->put(' New   York ', 'metric', $report);
        $cached = $cache->get('new york', 'METRIC'); self::assertNotNull($cached); self::assertSame('Amsterdam', $cached->city);
    }
    public function testExpiredEntryIsMiss(): void
    {
        $cache = new FileCache($this->directory, 10); $cache->put('Amsterdam', 'metric', $this->report());
        touch($cache->path('Amsterdam','metric'), time() - 11); clearstatcache(); self::assertNull($cache->get('Amsterdam','metric'));
    }
    public function testUnreadableCacheDirectoryDoesNotPreventPut(): void
    {
        $cache = new FileCache('/dev/null/not-a-directory', 600); $cache->put('Amsterdam','metric',$this->report()); self::assertNull($cache->get('Amsterdam','metric'));
    }
    private function report(): WeatherReport { return new WeatherReport('Amsterdam','NL','clear sky',800,20,19,50,2,'metric'); }
}
