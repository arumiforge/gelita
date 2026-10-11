<?php

namespace App\Libraries;

/**
 * Size rule for image slots with a fixed file size (Config\Gelita::$assetSizes).
 *
 * The game draws every image scaled (object-fit, percent positions), so the
 * slot size is the size a file must be able to fill, not a size it must match
 * to the pixel:
 *
 * - smaller than the slot on either side → rejected (it would look blurry);
 * - a width-to-height ratio more than RATIO_TOLERANCE off the slot → rejected
 *   (cropping would cut away part of the picture or move hunt objects);
 * - larger with the same ratio → accepted and shrunk to the slot size with GD,
 *   centre-cropping the sliver left by the tolerance, so stored files stay at
 *   the official size and school devices never download more than needed.
 *
 * SVG has no pixel size and is never checked here.
 */
final class ImageFitter
{
    /** Allowed relative difference between the file ratio and the slot ratio. */
    public const RATIO_TOLERANCE = 0.01;

    /**
     * Why a file of size $has cannot fill a slot of size $need, or null when
     * it can (as is, or after fit()).
     *
     * @param array{0: int, 1: int} $has
     * @param array{0: int, 1: int} $need
     */
    public static function problem(array $has, array $need, string $assetKey): ?string
    {
        if ($has[0] < $need[0] || $has[1] < $need[1]) {
            return sprintf(
                'Gambar ini %d × %d piksel, lebih kecil dari ukuran %s (%d × %d piksel). Pakai gambar yang paling kecil %d × %d piksel, lalu unggah lagi.',
                $has[0],
                $has[1],
                $assetKey,
                $need[0],
                $need[1],
                $need[0],
                $need[1],
            );
        }

        if (! self::sameRatio($has, $need)) {
            return sprintf(
                'Bentuk gambar ini (%d × %d piksel) tidak sama dengan bentuk %s (%d × %d piksel). Potong dulu gambarnya dengan perbandingan lebar dan tinggi yang sama, lalu unggah lagi.',
                $has[0],
                $has[1],
                $assetKey,
                $need[0],
                $need[1],
            );
        }

        return null;
    }

    /**
     * @param array{0: int, 1: int} $has
     * @param array{0: int, 1: int} $need
     */
    public static function sameRatio(array $has, array $need): bool
    {
        // Cross-multiplied so no division by a zero height
        $file = $has[0] * $need[1];
        $slot = $need[0] * $has[1];

        return abs($file - $slot) <= self::RATIO_TOLERANCE * $slot;
    }

    /**
     * Shrink the image at $path in place to exactly $need, keeping its format
     * and transparency. Call only after problem() returned null.
     *
     * @param array{0: int, 1: int} $need
     *
     * @return string|null error text for staff, or null when the file now has size $need
     */
    public function fit(string $path, string $mime, array $need): ?string
    {
        $read = @getimagesize($path);

        if (! is_array($read)) {
            return 'Berkas ini bukan gambar yang dapat dibuka. Simpan ulang sebagai PNG atau JPG, lalu unggah lagi.';
        }

        [$width, $height] = [(int) $read[0], (int) $read[1]];

        if ([$width, $height] === $need) {
            return null;
        }

        $tooLarge = sprintf(
            'Gambar ini (%d × %d piksel) terlalu besar untuk diperkecil di server. Perkecil dulu menjadi %d × %d piksel, lalu unggah lagi.',
            $width,
            $height,
            $need[0],
            $need[1],
        );

        $codec = [
            'image/png'  => ['imagecreatefrompng', static fn ($image, string $to): bool => imagepng($image, $to, 6)],
            'image/jpeg' => ['imagecreatefromjpeg', static fn ($image, string $to): bool => imagejpeg($image, $to, 90)],
            'image/webp' => ['imagecreatefromwebp', static fn ($image, string $to): bool => imagewebp($image, $to, 90)],
        ][$mime] ?? null;

        // GIF (possibly animated) would lose frames or transparency
        if ($mime === 'image/gif') {
            return sprintf(
                'Gambar GIF ini %d × %d piksel dan tidak dapat diperkecil otomatis. Ubah dulu ukurannya menjadi %d × %d piksel, atau simpan sebagai PNG, lalu unggah lagi.',
                $width,
                $height,
                $need[0],
                $need[1],
            );
        }

        // GD missing or built without this format (GD is a server requirement, docs/08)
        if ($codec === null || ! function_exists($codec[0]) || ! function_exists('imagecreatetruecolor')) {
            return sprintf(
                'Gambar ini %d × %d piksel dan tidak dapat diperkecil otomatis di server. Ubah dulu ukurannya menjadi %d × %d piksel, lalu unggah lagi.',
                $width,
                $height,
                $need[0],
                $need[1],
            );
        }

        if (! $this->hasMemoryFor($width * $height + $need[0] * $need[1])) {
            return $tooLarge;
        }

        $source = @$codec[0]($path);

        if ($source === false) {
            return $tooLarge;
        }

        // Centre crop to the slot ratio (at most RATIO_TOLERANCE of one side)
        if ($width * $need[1] > $need[0] * $height) {
            $cropWidth  = (int) round($height * $need[0] / $need[1]);
            $cropHeight = $height;
        } else {
            $cropWidth  = $width;
            $cropHeight = (int) round($width * $need[1] / $need[0]);
        }

        $target = imagecreatetruecolor($need[0], $need[1]);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            intdiv($width - $cropWidth, 2),
            intdiv($height - $cropHeight, 2),
            $need[0],
            $need[1],
            $cropWidth,
            $cropHeight,
        );
        unset($source); // free the decoded original before encoding

        // Write next to the original, then swap, so a failed write never leaves half a file
        $temp  = $path . '.fit';
        $saved = $codec[1]($target, $temp);
        unset($target);

        if (! $saved || ! @rename($temp, $path)) {
            @unlink($temp);

            return 'Gambar gagal diperkecil di server. Coba lagi; bila tetap gagal, hubungi petugas teknis.';
        }

        clearstatcache(true, $path);

        return null;
    }

    /** GD keeps about 5 bytes per pixel; leave headroom under memory_limit. */
    private function hasMemoryFor(int $pixels): bool
    {
        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '' || $limit === '-1') {
            return true;
        }

        $bytes = (int) $limit;
        $bytes *= match (strtolower(substr($limit, -1))) {
            'g'     => 1024 ** 3,
            'm'     => 1024 ** 2,
            'k'     => 1024,
            default => 1,
        };

        return memory_get_usage() + $pixels * 5 + 16 * 1024 ** 2 <= $bytes;
    }
}
