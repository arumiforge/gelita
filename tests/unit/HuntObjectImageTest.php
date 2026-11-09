<?php

use App\Libraries\MediaStore;
use App\Models\ChallengeAttemptModel;
use App\Services\ChallengeService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Encryption;
use Config\Services;
use Tests\Support\Database\BankPlay;

/**
 * Cari Objek pictures without their file names (research revision 10, part
 * 7: 33 §8.1, M-04, §13.1 `HuntObjectImageTest`): the payload holds no file
 * path or item code; `GET /api/attempts/{id}/objects/{ref}` sends the
 * picture to the attempt's owner, 404 for an unknown token or another
 * student's attempt.
 *
 * @internal
 */
final class HuntObjectImageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use BankPlay;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private string $originalKey;

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->originalKey             = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-gambar';
        $this->conn                    = $this->createGradeFiveWorld();
        $this->service                 = new ChallengeService();

        // A picture for every find_object item, named like the real slots (with the item code)
        $dir = FCPATH . MediaStore::uploadDir();
        is_dir($dir) || mkdir($dir, 0775, true);
        $this->file = $dir . 'challenge.item.tmg-4-x01.webp';
        file_put_contents($this->file, 'RIFF-webp-bytes');

        $this->conn->table('media_assets')->insert([
            'asset_key' => 'challenge.item.tmg-4-x01', 'asset_type' => 'image', 'storage_path' => MediaStore::uploadDir() . 'challenge.item.tmg-4-x01.webp',
            'mime_type' => 'image/webp', 'is_active' => 1,
        ]);
        $mediaId = (int) $this->conn->insertID();
        $this->conn->table('challenge_items')->where('interaction_type', 'find_object')->update(['media_asset_id' => $mediaId]);
        service('contentRepository')->flush();
        service('cache')->clean();
    }

    protected function tearDown(): void
    {
        is_file($this->file) && unlink($this->file);
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testPayloadCarriesTokenUrlsOnly(): void
    {
        $nodeId  = $this->nodeId($this->conn, 'tmg-4');
        $payload = $this->service->openNode($this->session(), $nodeId)['payload'];
        $json    = json_encode($payload['objects'], JSON_UNESCAPED_SLASHES);

        $this->assertStringNotContainsString('tmg-4', $json);
        $this->assertStringNotContainsString('.webp', $json);
        $this->assertStringNotContainsString('uploads', $json);

        $attempt = model(ChallengeAttemptModel::class)->currentInProgress(1, $nodeId);

        foreach ($payload['objects'] as $object) {
            $this->assertSame(site_url('api/attempts/' . $attempt->id . '/objects/' . $object['ref']), $object['media']);
        }
    }

    public function testOwnerGetsThePictureOthersGet404(): void
    {
        $nodeId  = $this->nodeId($this->conn, 'tmg-4');
        $payload = $this->service->openNode($this->session(), $nodeId)['payload'];
        $attempt = model(ChallengeAttemptModel::class)->currentInProgress(1, $nodeId);
        $ref     = $payload['objects'][0]['ref'];

        $ok = $this->get(1, 'api/attempts/' . $attempt->id . '/objects/' . $ref);
        $ok->assertStatus(200);
        $this->assertSame('RIFF-webp-bytes', $ok->response()->getBody());
        $this->assertStringStartsWith('image/webp', $ok->response()->getHeaderLine('Content-Type'));
        $this->assertSame('private, max-age=86400', $ok->response()->getHeaderLine('Cache-Control'));
        $this->assertSame('', $ok->response()->getHeaderLine('Content-Disposition'));

        $this->get(1, 'api/attempts/' . $attempt->id . '/objects/' . str_repeat('a', 20))->assertStatus(404);

        $other = $this->newStudent(8);
        $this->get($other->id, 'api/attempts/' . $attempt->id . '/objects/' . $ref, $other->participant_id)->assertStatus(404);
    }

    private function get(int $sessionId, string $path, int $participantId = 1): \CodeIgniter\Test\TestResponse
    {
        Services::resetSingle('gameContext');

        return $this->withSession(['participant_id' => $participantId, 'game_session_id' => $sessionId, 'gelita_csrf' => self::CSRF_HASH])
            ->call(Method::GET, $path);
    }
}
