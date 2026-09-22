<?php

namespace App\Services;

use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Entities\Level;
use App\Entities\LibraryPage;
use App\Entities\ReadingPassage;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use App\Models\DialogueModel;
use App\Models\GameReleaseModel;
use App\Models\HintModel;
use App\Models\LevelModel;
use App\Models\LibraryPageModel;
use App\Models\MediaAssetModel;
use App\Models\ReadingPassageModel;

/**
 * Pembaca konten permainan dengan cache.
 * Kunci cache selalu menyertakan content_version release aktif, sehingga
 * sesi yang sedang berjalan tidak terganggu saat admin menyunting konten.
 */
class ContentRepository
{
    private const TTL    = 3600;
    private const PREFIX = 'content';

    private ?string $version = null;

    /** Cache satu request, agar mediaMap() tidak dibaca berulang. */
    private array $local = [];

    /** @return list<Level> */
    public function levels(): array
    {
        return $this->remember('levels', static fn (): array => model(LevelModel::class)->ordered());
    }

    public function level(string $code): ?Level
    {
        foreach ($this->levels() as $level) {
            if ($level->code === $code) {
                return $level;
            }
        }

        return null;
    }

    public function levelById(int $levelId): ?Level
    {
        foreach ($this->levels() as $level) {
            if ($level->id === $levelId) {
                return $level;
            }
        }

        return null;
    }

    /** @return list<ChallengeNode> */
    public function nodesForLevel(int $levelId): array
    {
        return $this->remember(
            'nodes.' . $levelId,
            static fn (): array => model(ChallengeNodeModel::class)->forLevel($levelId),
        );
    }

    public function node(int $nodeId): ?ChallengeNode
    {
        foreach ($this->levels() as $level) {
            foreach ($this->nodesForLevel($level->id) as $node) {
                if ($node->id === $nodeId) {
                    return $node;
                }
            }
        }

        return null;
    }

    /**
     * Bank soal satu node, opsi jawaban sudah ikut dimuat (hindari N+1).
     *
     * @return list<ChallengeItem>
     */
    public function itemBank(int $nodeId): array
    {
        return $this->remember('items.' . $nodeId, static function () use ($nodeId): array {
            $model = model(ChallengeItemModel::class);
            $items = $model->bankForNode($nodeId);

            $optionMap = $model->withOptions(array_map(static fn (ChallengeItem $item): int => $item->id, $items));

            foreach ($items as $item) {
                $item->setLoadedOptions($optionMap[$item->id] ?? []);
            }

            return $items;
        });
    }

    /** Petunjuk node, atau petunjuk item bila $itemId diisi. */
    public function hintsFor(int $nodeId, ?int $itemId = null): array
    {
        $key = 'hints.' . $nodeId . '.' . ($itemId ?? 0);

        return $this->remember($key, static fn (): array => $itemId === null
            ? model(HintModel::class)->forNode($nodeId)
            : model(HintModel::class)->forItem($itemId));
    }

    public function dialogues(?int $levelId, string $context = 'level_open'): array
    {
        $key = 'dialogues.' . ($levelId ?? 0) . '.' . $context;

        return $this->remember($key, static fn (): array => $levelId === null
            ? model(DialogueModel::class)->intro()
            : model(DialogueModel::class)->forLevel($levelId, $context));
    }

    /** @return list<LibraryPage> */
    public function libraryPages(int $levelId): array
    {
        return $this->remember(
            'library.' . $levelId,
            static fn (): array => model(LibraryPageModel::class)->forLevel($levelId),
        );
    }

    /** @return list<ReadingPassage> */
    public function passagesForLevel(int $levelId): array
    {
        return $this->remember(
            'passages.' . $levelId,
            static fn (): array => model(ReadingPassageModel::class)->forLevel($levelId),
        );
    }

    /**
     * Teks bacaan untuk sekumpulan id. Tidak di-cache per kombinasi id;
     * satu query batch sudah cukup murah.
     *
     * @param list<int> $ids
     *
     * @return array<int, ReadingPassage>
     */
    public function passagesForIds(array $ids): array
    {
        return model(ReadingPassageModel::class)->forIds($ids);
    }

    /**
     * Peta id => storage_path seluruh media aktif.
     *
     * @return array<int, string>
     */
    public function mediaMap(): array
    {
        if (isset($this->local['media'])) {
            return $this->local['media'];
        }

        return $this->local['media'] = $this->remember(
            'media',
            static fn (): array => model(MediaAssetModel::class)->map(),
        );
    }

    /** Dipanggil setelah konten disunting atau diimpor. */
    public function flush(): void
    {
        $this->local   = [];
        $this->version = null;

        $cache = service('cache');

        if (method_exists($cache, 'deleteMatching')) {
            // deleteMatching() mencocokkan nama berkas, jadi prefix handler ikut disertakan.
            $cache->deleteMatching(config('Cache')->prefix . self::PREFIX . '.*');

            return;
        }

        $cache->clean();
    }

    /** content_version release aktif; '0' bila belum ada release. */
    public function version(): string
    {
        if ($this->version !== null) {
            return $this->version;
        }

        $release = model(GameReleaseModel::class)->activeOrNull();

        return $this->version = (string) ($release['content_version'] ?? '0');
    }

    /**
     * Membaca dari cache atau menghitung lalu menyimpannya.
     *
     * @template T
     *
     * @param callable(): T $producer
     *
     * @return T
     */
    private function remember(string $key, callable $producer)
    {
        $cacheKey = self::PREFIX . '.' . $key . '.v' . $this->version();
        $cache    = service('cache');
        $value    = $cache->get($cacheKey);

        if ($value !== null) {
            return $value;
        }

        $value = $producer();
        $cache->save($cacheKey, $value, self::TTL);

        return $value;
    }
}
