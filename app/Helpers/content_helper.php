<?php

/**
 * Helper konten dwibahasa & media. Dimuat global lewat Config\Autoload::$helpers.
 */

if (! function_exists('tr')) {
    /**
     * Ambil $row[$field.'_'.$locale]; bila kosong jatuh ke $field.'_id'.
     * Contoh: tr($node, 'title') → title_en bila locale en dan terisi, selain itu title_id.
     */
    function tr(array|object|null $row, string $field, ?string $locale = null): string
    {
        if ($row === null) {
            return '';
        }

        $data   = is_object($row) ? (method_exists($row, 'toRawArray') ? $row->toRawArray() : (array) $row) : $row;
        $locale ??= service('request')->getLocale() ?: 'id';

        $value = $data[$field . '_' . $locale] ?? null;

        if ($value === null || $value === '') {
            $value = $data[$field . '_id'] ?? '';
        }

        return (string) $value;
    }
}

if (! function_exists('node_ref')) {
    /**
     * Kode tantangan workbook dari kode wilayah + urutan: ('magelang', 4) → 'mgl-4'.
     * Wilayah di luar Config\Gelita::$levelPrefixes memakai kodenya sendiri.
     */
    function node_ref(string $levelCode, int $sequence): string
    {
        $prefix = array_search($levelCode, config('Gelita')->levelPrefixes, true);

        return ($prefix === false ? $levelCode : $prefix) . '-' . $sequence;
    }
}

if (! function_exists('rich_text')) {
    /**
     * Format ringan untuk teks panjang Pustaka Kedu, ditulis admin tanpa HTML:
     *
     *   baris kosong        → paragraf baru
     *   `## Subjudul`       → <h3>
     *   `- butir`           → daftar berpoin
     *   `> Tahukah kamu? …` → kotak fakta (<aside class="book-fact">)
     *   `Sumber: …`         → catatan rujukan kecil
     *   `**tebal**`         → <strong>
     *   `*miring*`          → <em> (istilah asing/daerah)
     *
     * Seluruh teks di-escape LEBIH DULU; tag hanya disusun dari penanda di
     * atas, jadi isi database tidak pernah dapat menyisipkan HTML.
     */
    function rich_text(string $text): string
    {
        $inline = static function (string $line): string {
            $html = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', esc($line)) ?? esc($line);

            // *miring*: bintang tunggal yang menempel pada kata, bukan sisa **tebal**
            return preg_replace('/(?<![*\w])\*(?=\S)([^*]+?)(?<=\S)\*(?![*\w])/u', '<em>$1</em>', $html) ?? $html;
        };

        $html  = '';
        $para  = [];
        $list  = [];
        $quote = [];

        $flush = static function () use (&$html, &$para, &$list, &$quote, $inline): void {
            if ($para !== []) {
                $html .= '<p>' . implode('<br>', array_map($inline, $para)) . '</p>';
                $para = [];
            }

            if ($list !== []) {
                $html .= '<ul>' . implode('', array_map(static fn (string $item): string => '<li>' . $inline($item) . '</li>', $list)) . '</ul>';
                $list = [];
            }

            if ($quote !== []) {
                $html .= '<aside class="book-fact">' . implode('<br>', array_map($inline, $quote)) . '</aside>';
                $quote = [];
            }
        };

        foreach (preg_split('/\R/u', trim($text)) ?: [] as $raw) {
            $line = trim($raw);

            if ($line === '') {
                $flush();

                continue;
            }

            if (preg_match('/^#{2,3}\s+(.+)$/u', $line, $m)) {
                $flush();
                $html .= '<h3>' . $inline($m[1]) . '</h3>';
            } elseif (preg_match('/^[-*•]\s+(.+)$/u', $line, $m)) {
                if ($para !== [] || $quote !== []) {
                    $flush();
                }
                $list[] = $m[1];
            } elseif (preg_match('/^>\s?(.*)$/u', $line, $m)) {
                if ($para !== [] || $list !== []) {
                    $flush();
                }
                $quote[] = $m[1];
            } elseif (preg_match('/^(Sumber|Rujukan|Source|Sources|References?)\s*:/iu', $line)) {
                $flush();
                $html .= '<p class="book-source">' . $inline($line) . '</p>';
            } else {
                if ($list !== [] || $quote !== []) {
                    $flush();
                }
                $para[] = $line;
            }
        }

        $flush();

        return $html;
    }
}

if (! function_exists('media_src')) {
    /**
     * URL media aktif dari peta media ber-cache (ContentRepository::mediaMap()).
     * Bila tidak ada / nonaktif → $fallback, atau media_placeholder_src().
     *
     * While an asset package is active (AssetPackageService), an asset that is
     * in the package gets its content-addressed package URL (assets/p/…);
     * assets added after the build keep their normal URL.
     */
    function media_src(?int $mediaAssetId, ?string $fallback = null): string
    {
        $placeholder = $fallback === null ? media_placeholder_src() : base_url($fallback);

        if ($mediaAssetId === null || $mediaAssetId <= 0) {
            return $placeholder;
        }

        $path = service('contentRepository')->mediaMap()[$mediaAssetId] ?? null;

        return $path ? base_url(service('assetPackages')->urlForId($mediaAssetId) ?? $path) : $placeholder;
    }
}

if (! function_exists('media_placeholder_src')) {
    /**
     * Stand-in picture for missing media: the `ui.placeholder` slot when
     * uploaded, else the static assets/ui/placeholder.svg.
     */
    function media_placeholder_src(): string
    {
        return media_key_src('ui.placeholder') ?? base_url('assets/ui/placeholder.svg');
    }
}

if (! function_exists('media_is_placeholder')) {
    /** Is this URL the stand-in picture (slot or static file), not real media? */
    function media_is_placeholder(?string $url): bool
    {
        return $url !== null && $url !== ''
            && ($url === media_placeholder_src() || str_ends_with(strtok($url, '?'), 'assets/ui/placeholder.svg'));
    }
}

if (! function_exists('region_art_src')) {
    /**
     * City art on the Kedu map (display plan Phase C): `pin` → map.pin.{code},
     * then ui.pin; `lantern` → map.lantern.{code}, then ui.lantern. Null when
     * neither is uploaded: the view keeps its SVG icon.
     */
    function region_art_src(string $kind, ?string $levelCode = null): ?string
    {
        $own = $levelCode === null || $levelCode === '' ? null : media_key_src("map.{$kind}.{$levelCode}");

        return $own ?? media_key_src("ui.{$kind}");
    }
}

if (! function_exists('art_or_icon')) {
    /**
     * `<img>` of an uploaded picture, or the built-in SVG icon when there is
     * none. Decorative: the caller names the control in text.
     */
    function art_or_icon(?string $src, string $icon): string
    {
        return $src === null
            ? icon($icon)
            : '<img class="art" src="' . esc($src, 'attr') . '" alt="" decoding="async" draggable="false">';
    }
}

if (! function_exists('region_loading_bg')) {
    /**
     * Background of the "Menuju {wilayah}…" curtain: bg.loading.{code}, then
     * the region background already resolved by the caller, else null (the
     * curtain keeps its light gradient).
     */
    function region_loading_bg(string $levelCode, ?string $regionBackground = null): ?string
    {
        return media_key_src("bg.loading.{$levelCode}") ?? ($regionBackground !== null && $regionBackground !== '' ? $regionBackground : null);
    }
}

if (! function_exists('avatar_choices')) {
    /**
     * Avatars a student can pick: number → image URL, only uploaded
     * `avatar.N` slots (1 … Config\Gelita::$avatarSlots), in order.
     *
     * @return array<int, string>
     */
    function avatar_choices(): array
    {
        $choices = [];

        for ($n = 1; $n <= config('Gelita')->avatarSlots; $n++) {
            if (($src = media_key_src("avatar.{$n}")) !== null) {
                $choices[$n] = $src;
            }
        }

        return $choices;
    }
}

if (! function_exists('avatar_src')) {
    /** Image of a chosen avatar, or null (none chosen, or the slot was emptied): show the initial. */
    function avatar_src(?int $avatarNo): ?string
    {
        return $avatarNo === null || $avatarNo < 1 ? null : media_key_src("avatar.{$avatarNo}");
    }
}

if (! function_exists('media_exists')) {
    /** Media aktif dengan id ini ada? Dipakai view yang punya tampilan pengganti sendiri. */
    function media_exists(?int $mediaAssetId): bool
    {
        return $mediaAssetId !== null && $mediaAssetId > 0
            && isset(service('contentRepository')->mediaMap()[$mediaAssetId]);
    }
}

if (! function_exists('media_first')) {
    /**
     * URL media aktif pertama dari beberapa kandidat id (mis. latar node, lalu
     * latar wilayah). String kosong bila tidak ada satu pun — layout lalu
     * memakai gradien CSS alih-alih gambar pengganti.
     */
    function media_first(?int ...$mediaAssetIds): string
    {
        foreach ($mediaAssetIds as $id) {
            if (media_exists($id)) {
                return media_src($id);
            }
        }

        return '';
    }
}

if (! function_exists('media_key_src')) {
    /**
     * URL media aktif menurut asset_key resmi (mis. `bg.welcome`, `char.jaka.idle.1`).
     * NULL bila aset belum diunggah/nonaktif — view memilih penggantinya sendiri
     * (gradien CSS untuk latar, monogram untuk karakter) alih-alih kotak rusak.
     */
    function media_key_src(string $assetKey): ?string
    {
        $path = service('contentRepository')->mediaKeyMap()[$assetKey] ?? null;

        // Package URL while an asset package is active (see media_src())
        return $path ? base_url(service('assetPackages')->urlForKey($assetKey) ?? $path) : null;
    }
}

if (! function_exists('character_frame_src')) {
    /**
     * Frame pertama tokoh dalam pose tertentu (`char.{jaka|kedu}.{pose}.1`),
     * cadangan pose `idle`. NULL bila keduanya belum diunggah (view memakai
     * monogram) atau untuk narator, yang tidak tampil sebagai gambar.
     */
    function character_frame_src(string $character, ?string $pose = null): ?string
    {
        if ($character === '' || $character === 'narator') {
            return null;
        }

        $slug = $character === 'mbah_kedu' ? 'kedu' : $character;
        $pose = $pose === null || $pose === '' ? 'idle' : $pose;

        return media_key_src("char.{$slug}.{$pose}.1") ?? media_key_src("char.{$slug}.idle.1");
    }
}

if (! function_exists('character_frames')) {
    /**
     * Animation of a character pose: the uploaded frames `char.{jaka|kedu}.{pose}.{n}`
     * in order, with fps and loop from Config\Gelita::$characterAnimations.
     * Falls back to `idle` when frame 1 of the pose is missing, so `frames[0]`
     * always equals character_frame_src(); a missing later frame is skipped.
     * NULL for the narrator or when neither pose has frame 1.
     *
     * @return array{frames: list<string>, fps: int, loop: bool}|null
     */
    function character_frames(string $character, ?string $pose = null): ?array
    {
        if ($character === '' || $character === 'narator') {
            return null;
        }

        $slug       = $character === 'mbah_kedu' ? 'kedu' : $character;
        $pose       = $pose === null || $pose === '' ? 'idle' : $pose;
        $animations = config('Gelita')->characterAnimations;

        foreach (array_unique([$pose, 'idle']) as $candidate) {
            if (media_key_src("char.{$slug}.{$candidate}.1") === null) {
                continue;
            }

            $animation = $animations["{$slug}.{$candidate}"] ?? [];
            $frames    = [];

            for ($i = 1; $i <= max(1, (int) ($animation['frames'] ?? 1)); $i++) {
                if (($src = media_key_src("char.{$slug}.{$candidate}.{$i}")) !== null) {
                    $frames[] = $src;
                }
            }

            return [
                'frames' => $frames,
                'fps'    => max(1, (int) ($animation['fps'] ?? 1)),
                'loop'   => (bool) ($animation['loop'] ?? false),
            ];
        }

        return null;
    }
}

if (! function_exists('character_frames_attr')) {
    /**
     * Data attributes for core/sprite.js (`{prefix}frames` as a JSON list,
     * `{prefix}fps`, `{prefix}loop`), with a leading space. Empty when the
     * animation has fewer than 2 frames: the figure then stays on frame 1.
     *
     * @param array{frames: list<string>, fps: int, loop: bool}|null $animation from character_frames()
     */
    function character_frames_attr(?array $animation, string $prefix = 'data-'): string
    {
        if ($animation === null || count($animation['frames']) < 2) {
            return '';
        }

        return ' ' . $prefix . 'frames="' . esc(json_encode($animation['frames'], JSON_UNESCAPED_SLASHES), 'attr') . '"'
            . ' ' . $prefix . 'fps="' . $animation['fps'] . '"'
            . ' ' . $prefix . 'loop="' . ($animation['loop'] ? '1' : '0') . '"';
    }
}

if (! function_exists('media_key_src_locale')) {
    /**
     * media_key_src() untuk gambar bertulisan yang punya varian bahasa
     * (05_VIEW_UI.md §Kondisi aset hilang): `{kunci}.{locale}` dicoba lebih
     * dulu, lalu kunci dasar (varian Indonesia). `ui.btn-start` + en →
     * `ui.btn-start.en`, bila tidak ada → `ui.btn-start`.
     */
    function media_key_src_locale(string $assetKey, ?string $locale = null): ?string
    {
        $locale ??= service('request')->getLocale() ?: 'id';

        if ($locale !== 'id' && ($localized = media_key_src($assetKey . '.' . $locale)) !== null) {
            return $localized;
        }

        return media_key_src($assetKey);
    }
}

if (! function_exists('media_catalog')) {
    /**
     * Seluruh baris media_assets (aktif maupun belum berberkas) per id, untuk
     * pemilih media di panel admin. Dibaca sekali per request.
     *
     * @return array<int, array<string, mixed>>
     */
    function media_catalog(bool $refresh = false): array
    {
        static $rows = null;

        if ($rows === null || $refresh) {
            $rows = [];

            foreach (model(\App\Models\MediaAssetModel::class)->orderBy('asset_key', 'ASC')->findAll() as $row) {
                $rows[(int) $row['id']] = $row;
            }
        }

        return $rows;
    }
}

if (! function_exists('audio_catalog')) {
    /**
     * Seluruh audio_assets + asset_key, untuk pemilih audio di panel admin.
     *
     * @return array<int, array<string, mixed>>
     */
    function audio_catalog(): array
    {
        static $rows = null;

        if ($rows === null) {
            $rows = [];
            $list = db_connect()->table('audio_assets aa')
                ->select('aa.id, aa.locale, aa.context_code, aa.character_code, aa.approval_status, ma.asset_key')
                ->join('media_assets ma', 'ma.id = aa.media_asset_id')
                ->orderBy('aa.context_code', 'ASC')
                ->orderBy('aa.locale', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($list as $row) {
                $rows[(int) $row['id']] = $row;
            }
        }

        return $rows;
    }
}

if (! function_exists('audio_src')) {
    /**
     * URL audio; NULL bila approval_status != 'approved' atau media nonaktif.
     *
     * `audio_assets` tidak punya kolom `is_active` (lihat migration CreateAudioAssets);
     * status tayang aset audio ditentukan `approval_status` miliknya sendiri
     * dan `is_active` pada `media_assets` induknya.
     */
    function audio_src(?int $audioAssetId): ?string
    {
        if ($audioAssetId === null || $audioAssetId <= 0) {
            return null;
        }

        $row = db_connect()->table('audio_assets aa')
            ->select('ma.id, ma.storage_path, ma.sha256')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id')
            ->where('aa.id', $audioAssetId)
            ->where('aa.approval_status', 'approved')
            ->where('ma.is_active', 1)
            ->get()
            ->getRow();

        if (! $row) {
            return null;
        }

        // Narration is packaged too (see media_src())
        return base_url(service('assetPackages')->urlForId((int) $row->id)
            ?? \App\Libraries\MediaStore::versionedPath((string) $row->storage_path, $row->sha256));
    }
}

if (! function_exists('stars_html')) {
    /**
     * Bintang 0..$max sebagai ikon + teks alternatif. Warna bukan satu-satunya
     * penanda: bintang kosong bergaris, bintang penuh terisi, dan pembaca layar
     * membaca "2 / 3".
     */
    function stars_html(int $stars, int $max = 3): string
    {
        if (! function_exists('icon')) {
            helper('ui');
        }

        $max   = max(1, $max);
        $stars = max(0, min($max, $stars));
        $html  = '<span class="stars" role="img" aria-label="' . esc($stars . ' / ' . $max) . '">';

        for ($i = 1; $i <= $max; $i++) {
            $html .= '<span class="star' . ($i <= $stars ? ' is-on' : '') . '">' . icon('star') . '</span>';
        }

        return $html . '</span>';
    }
}

if (! function_exists('first_pass_text')) {
    /**
     * "Berhasil pada Percobaan Pertama" satu pengerjaan untuk layar siswa
     * (Dok 1 §13.2): "2 dari 3 soal"; Susun Gambar (satu gambar): "Ya" / "Belum".
     */
    function first_pass_text(App\Entities\ChallengeAttempt $attempt, ?string $locale = null, ?string $engine = null): string
    {
        $locale ??= service('request')->getLocale();
        $first    = (int) $attempt->first_pass_correct;
        $total    = (int) $attempt->scorable_items;

        if ($engine === 'puzzle' && $total <= 1) {
            return lang($first >= 1 && $total >= 1 ? 'Game.firstTryYes' : 'Game.firstTryNo', [], $locale);
        }

        return lang('Game.firstTryCount', [$first, $total], $locale);
    }
}
