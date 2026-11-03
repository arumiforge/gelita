<?php

use App\Libraries\ErrorCodeResolver;
use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * ErrorCodeResolver (31 §10.1): one case per item type, an English word
 * mapped to its Indonesian pair, another clue's target, and an unknown
 * choice (null).
 *
 * @internal
 */
final class ErrorCodeResolverTest extends CIUnitTestCase
{
    use BankPlay;

    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $this->originalKey           = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-bank-31';
        $this->conn                  = $this->createGradeFiveWorld();
        $this->service               = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        Factories::reset('libraries');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testOneCasePerItemType(): void
    {
        $resolver = new ErrorCodeResolver();

        $this->assertSame('INF-HUB', $resolver->resolve($this->item('tmg-2-a02'), ['text' => 'pasir'], 'id'));
        $this->assertSame('INF-LIT', $resolver->resolve($this->item('tmg-2-a01'), ['text' => 'Pasir '], 'id'), 'trimmed, any case');
        $this->assertSame('INF-REL', $resolver->resolve($this->item('tmg-2-a01'), ['text' => 'abu'], 'id'));
        $this->assertSame('INF-HUB', $resolver->resolve($this->item('tmg-2-a02'), ['text' => 'sand'], 'en'), 'English word → Indonesian pair');
        $this->assertSame('INF-REL', $resolver->resolve($this->item('tmg-2-a01'), ['text' => 'ash'], 'en'));
        $this->assertSame('INF-SUM', $resolver->resolve($this->item('tmg-3-a03'), ['verdict' => 'benar'], 'id'));
        $this->assertSame('INF-KAT', $resolver->resolve($this->item('tmg-3-x01'), ['verdict' => 'benar'], 'id'), 'anchor card');
        $this->assertSame('INF-BUD', $resolver->resolve($this->item('tmg-4-x01'), ['item_id' => 0], 'id', 'tmg-4-x05'));
        $this->assertSame('INF-LIT', $resolver->resolve($this->item('tmg-4-x01'), ['item_id' => 0], 'id', 'tmg-4-x02'), "another clue's target");
        $this->assertSame('BUD-ATR', $resolver->resolve($this->item('tmg-4-a01'), ['part' => 'A1'], 'id', 'A1'), 'main lure');
        $this->assertSame('BUD-ATR', $resolver->resolve($this->item('tmg-4-a01'), ['part' => 'A8'], 'id', 'a8'), 'other part: clue type');
        $this->assertSame('BUD-ETK', $resolver->resolve($this->item('wnb-4-a04'), ['part' => 'E7'], 'id', 'E7'));
        $this->assertNull($resolver->resolve($this->item('tmg-2-a01'), ['text' => 'salju'], 'id'), 'unknown word');
        $this->assertNull($resolver->resolve($this->item('tmg-1-a01'), ['order' => [1, 0]], 'id'), 'Susun Gambar has no codes');
    }
}
