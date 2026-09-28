<?php

namespace App\Libraries;

/**
 * Daftar wilayah resmi Kemendagri (`public/assets/data/wilayah-id.json`),
 * dibaca sekali per proses. Satu sumber nama provinsi dan kab/kota untuk
 * formulir daftar, pemeriksaan server, dan direktori sekolah — nama dari
 * client tidak pernah dipercaya begitu saja.
 */
final class RegionDirectory
{
    /** @var array<string, mixed>|null */
    private static ?array $data = null;

    /** @var array<string, string>|null kode kab/kota => nama */
    private static ?array $districtNames = null;

    /** @return array<string, mixed> isi berkas; [] bila tidak terbaca */
    public static function all(): array
    {
        if (self::$data !== null) {
            return self::$data;
        }

        $path = FCPATH . 'assets/data/wilayah-id.json';
        $raw  = is_file($path) ? file_get_contents($path) : false;
        $data = $raw === false ? null : json_decode($raw, true);

        return self::$data = is_array($data) ? $data : [];
    }

    /** @return list<array<string, mixed>> */
    public static function provinces(): array
    {
        return self::all()['provinces'] ?? [];
    }

    /** @return array<string, mixed>|null */
    public static function province(?string $code): ?array
    {
        foreach (self::provinces() as $province) {
            if (($province['code'] ?? null) === $code) {
                return $province;
            }
        }

        return null;
    }

    /** "33.19" → "Kabupaten Kudus"; NULL bila kode tidak dikenal. */
    public static function districtName(?string $code): ?string
    {
        if (self::$districtNames === null) {
            self::$districtNames = [];

            foreach (self::provinces() as $province) {
                foreach ($province['districts'] ?? [] as $district) {
                    self::$districtNames[$district['code']] = $district['name'];
                }
            }
        }

        return $code === null ? null : (self::$districtNames[$code] ?? null);
    }

    /**
     * Kab/kota milik satu provinsi, kode => nama.
     *
     * @return array<string, string>
     */
    public static function districts(?string $provinceCode): array
    {
        return array_column(self::province($provinceCode)['districts'] ?? [], 'name', 'code');
    }
}
