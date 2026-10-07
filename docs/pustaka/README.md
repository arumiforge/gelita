# Pustaka Kedu

Isi Pustaka Kedu (buku bacaan setiap wilayah di menu Pustaka) untuk Temanggung, Magelang, dan Wonosobo: 30 halaman dan 48 gambar/video, dalam bahasa Indonesia dan Inggris.

| Berkas | Isi |
|---|---|
| [`gelita-pustaka.xlsx`](gelita-pustaka.xlsx) | workbook siap impor: sheet PETUNJUK, KAMUS_KOLOM, `library`, dan `library_media` |
| [`data/temanggung.php`](data/temanggung.php), [`data/magelang.php`](data/magelang.php), [`data/wonosobo.php`](data/wonosobo.php) | sumber isi (satu wilayah per berkas) |
| [`build-workbook.php`](build-workbook.php) | penyusun workbook dari berkas data |

Workbook bank soal kelas 5 tidak memuat Pustaka, sehingga impor bank soal dan `php spark gelita:reset` tidak menyentuhnya.

## Kapan dipakai

Workbook ini diimpor otomatis saat instalasi (`php spark db:seed DatabaseSeeder`, lewat `KontenKelas5Seeder`). Sesudah itu isi Pustaka disunting lewat **Konten permainan → editor wilayah**.

Mengimpornya lagi (`php spark gelita:bank:import docs/pustaka/gelita-pustaka.xlsx`, atau **Impor soal dari Excel** di panel) menimpa halaman bernomor sama dan mengganti seluruh galeri halaman yang punya baris di `library_media`, termasuk suntingan admin. Jadi impor ulang hanya untuk mengembalikan isi bawaan.

## Bentuk berkas data

```php
return [
    'library' => [
        [   // halaman 1, 2, … sesuai urutan
            'title' => [judul ID, judul EN],
            'body'  => [teks ID, teks EN],   // format ringan: "## subjudul", "- poin", "**tebal**", "*miring*", "> Tahukah kamu?", "Sumber: …"
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:…', 'caption' => [ID, EN], 'credit' => 'pemilik & lisensi'],
                ['kind' => 'video', 'url' => 'https://youtu.be/…', 'caption' => [ID, EN], 'credit' => '…'],
            ],
        ],
    ],
];
```

Setelah menyunting berkas data, jalankan `php docs/pustaka/build-workbook.php` dan commit workbook-nya (`BankWorkbookGuideTest` memeriksa jumlah halaman dan medianya).
