<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class GelitaHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
    }

    public function testUuid4Format(): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            uuid4(),
        );
    }

    public function testRandomCodeLength(): void
    {
        $this->assertSame(32, strlen(random_code(16)));
    }

    public function testParticipantCode(): void
    {
        $this->assertSame('GLT-000123', participant_code(123));
    }

    public function testMsToHuman(): void
    {
        $this->assertSame('2:05', ms_to_human(125000));
        $this->assertSame('1:02:05', ms_to_human(3725000));
    }

    public function testHashIpNeverReturnsRawIp(): void
    {
        $hash = hash_ip('192.168.1.10');

        $this->assertSame(64, strlen($hash));
        $this->assertStringNotContainsString('192.168', $hash);
        $this->assertNull(hash_ip(null));
    }

    public function testSeededShuffleIsDeterministicAndKeepsItems(): void
    {
        $items = range(1, 20);
        $a     = seeded_shuffle($items, 'GLT-000001|7');
        $b     = seeded_shuffle($items, 'GLT-000001|7');
        $c     = seeded_shuffle($items, 'GLT-000002|7');

        $this->assertSame($a, $b);
        $this->assertNotSame($a, $c);
        $this->assertEqualsCanonicalizing($items, $a);
    }

    public function testIsApiPath(): void
    {
        $this->assertTrue(is_api_path('api/levels'));
        $this->assertTrue(is_api_path('/api'));
        $this->assertFalse(is_api_path('apiary'));
        $this->assertFalse(is_api_path('admin'));
    }

    public function testTrFallsBackToIndonesian(): void
    {
        $row = ['title_id' => 'Candi', 'title_en' => ''];

        $this->assertSame('Candi', tr($row, 'title', 'en'));
        $this->assertSame('Temple', tr(['title_id' => 'Candi', 'title_en' => 'Temple'], 'title', 'en'));
    }

    public function testStarsHtml(): void
    {
        $html = stars_html(2);

        $this->assertSame(2, substr_count($html, 'is-on'));
        $this->assertStringContainsString('aria-label="2 / 3"', $html);
    }
}
