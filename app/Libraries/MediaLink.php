<?php

namespace App\Libraries;

/**
 * Tautan media luar untuk Pustaka Kedu: YouTube, Google Drive, Vimeo,
 * Wikimedia Commons, atau berkas gambar/video langsung (https).
 *
 * Server yang menerjemahkan tautan menjadi alamat tampil — bukan browser —
 * sehingga hanya bentuk yang dikenali yang pernah dirender, dan alamat
 * `javascript:`/`data:` tidak pernah lolos. Pemutar video pihak ketiga
 * (YouTube, Vimeo, Drive) baru dimuat setelah siswa menekan Putar
 * (lihat game/library.php), jadi membuka Pustaka tidak menghubungi domain
 * luar sama sekali; YouTube memakai domain youtube-nocookie.com.
 */
class MediaLink
{
    private const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif'];

    private const VIDEO_EXT = ['mp4', 'webm', 'ogv', 'ogg', 'm4v'];

    /**
     * @return array{provider: string, kind: string, src: string|null, embed: string|null, href: string, label: string}|null
     *         null bila bukan tautan http(s) yang sah.
     *         provider: youtube | vimeo | drive | commons | file | link
     *         kind    : image | video (bentuk akhir yang dirender)
     *         src     : alamat <img>/<video> (tampil langsung)
     *         embed   : alamat <iframe> (dimuat setelah tombol Putar ditekan)
     */
    public static function parse(string $url, string $kind = 'image'): ?array
    {
        $url  = trim($url);
        $kind = $kind === 'video' ? 'video' : 'image';

        if ($url === '' || mb_strlen($url) > 1000 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts  = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host   = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return null;
        }

        $path  = (string) ($parts['path'] ?? '');
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $query);
        $host = preg_replace('/^(www\.|m\.)/', '', $host) ?? $host;

        $youtube = self::youtubeId($host, $path, $query);

        if ($youtube !== null) {
            $start = self::seconds((string) ($query['t'] ?? $query['start'] ?? ''));

            return [
                'provider' => 'youtube',
                'kind'     => 'video',
                'src'      => null,
                'embed'    => 'https://www.youtube-nocookie.com/embed/' . $youtube . '?rel=0&modestbranding=1&playsinline=1' . ($start > 0 ? '&start=' . $start : ''),
                'href'     => 'https://www.youtube.com/watch?v=' . $youtube . ($start > 0 ? '&t=' . $start . 's' : ''),
                'label'    => 'YouTube',
            ];
        }

        if ($host === 'vimeo.com' || $host === 'player.vimeo.com') {
            if (preg_match('~/(?:video/)?(\d{5,12})(?:/([0-9a-f]{6,20}))?~', $path, $m)) {
                $hash = $m[2] ?? ($query['h'] ?? '');

                return [
                    'provider' => 'vimeo',
                    'kind'     => 'video',
                    'src'      => null,
                    'embed'    => 'https://player.vimeo.com/video/' . $m[1] . '?dnt=1' . ($hash !== '' ? '&h=' . rawurlencode((string) $hash) : ''),
                    'href'     => 'https://vimeo.com/' . $m[1] . ($hash !== '' ? '/' . $hash : ''),
                    'label'    => 'Vimeo',
                ];
            }
        }

        $drive = self::driveId($host, $path, $query);

        if ($drive !== null) {
            return [
                'provider' => 'drive',
                'kind'     => $kind,
                'src'      => $kind === 'image' ? 'https://drive.google.com/thumbnail?id=' . $drive . '&sz=w1600' : null,
                'embed'    => $kind === 'video' ? 'https://drive.google.com/file/d/' . $drive . '/preview' : null,
                'href'     => 'https://drive.google.com/file/d/' . $drive . '/view',
                'label'    => 'Google Drive',
            ];
        }

        // Halaman berkas Wikimedia Commons → berkas aslinya lewat Special:FilePath
        if (in_array($host, ['commons.wikimedia.org', 'en.wikipedia.org', 'id.wikipedia.org'], true)
            && preg_match('~/wiki/(?:File|Berkas|Special:FilePath):(.+)$~i', rawurldecode($path), $m)) {
            $file      = str_replace(' ', '_', $m[1]);
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $isVideo   = in_array($extension, self::VIDEO_EXT, true);
            $source    = 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($file) . ($isVideo ? '' : '?width=1280');

            return [
                'provider' => 'commons',
                'kind'     => $isVideo ? 'video' : 'image',
                'src'      => $source,
                'embed'    => null,
                'href'     => 'https://commons.wikimedia.org/wiki/File:' . rawurlencode($file),
                'label'    => 'Wikimedia Commons',
            ];
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, self::IMAGE_EXT, true) || in_array($extension, self::VIDEO_EXT, true)) {
            return [
                'provider' => 'file',
                'kind'     => in_array($extension, self::VIDEO_EXT, true) ? 'video' : 'image',
                'src'      => $url,
                'embed'    => null,
                'href'     => $url,
                'label'    => $host,
            ];
        }

        // Selebihnya ditampilkan sebagai kartu tautan yang dibuka di tab baru
        return [
            'provider' => 'link',
            'kind'     => $kind,
            'src'      => null,
            'embed'    => null,
            'href'     => $url,
            'label'    => $host,
        ];
    }

    /** @param array<string, mixed> $query */
    private static function youtubeId(string $host, string $path, array $query): ?string
    {
        $id = null;

        if ($host === 'youtu.be') {
            $id = explode('/', ltrim($path, '/'))[0] ?? null;
        } elseif (in_array($host, ['youtube.com', 'youtube-nocookie.com', 'music.youtube.com'], true)) {
            if ($path === '/watch') {
                $id = $query['v'] ?? null;
            } elseif (preg_match('~^/(?:embed|shorts|live|v)/([^/?#]+)~', $path, $m)) {
                $id = $m[1];
            }
        }

        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
    }

    /** @param array<string, mixed> $query */
    private static function driveId(string $host, string $path, array $query): ?string
    {
        if (! in_array($host, ['drive.google.com', 'docs.google.com'], true)) {
            return null;
        }

        $id = null;

        if (preg_match('~/(?:file/)?d/([A-Za-z0-9_-]{10,})~', $path, $m)) {
            $id = $m[1];
        } elseif (isset($query['id']) && is_string($query['id'])) {
            $id = $query['id'];
        }

        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{10,}$/', $id) ? $id : null;
    }

    /** `90`, `90s`, `1m30s`, `1h2m3s` → detik. */
    private static function seconds(string $value): int
    {
        if (ctype_digit($value)) {
            return (int) $value;
        }

        if (preg_match('/^(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/', $value, $m) && $value !== '') {
            return (int) ($m[1] ?? 0) * 3600 + (int) ($m[2] ?? 0) * 60 + (int) ($m[3] ?? 0);
        }

        return 0;
    }
}
