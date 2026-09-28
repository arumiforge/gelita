# Daftar Sekolah Resmi Jawa Tengah

Daftar ini dipakai saat pendaftaran siswa: siswa yang memilih provinsi **Jawa Tengah**
wajib mengisi **NPSN** sekolahnya, lalu nama resmi sekolah muncul otomatis. Dengan
begitu "SD 1 CENDONO", "SD NEGERI 1 CENDONO", dan "sd 1 cendono" tidak lagi menjadi
tiga sekolah berbeda di laporan: semuanya tertaut ke satu baris `schools` ber-NPSN
(20318068).

| Berkas | Isi |
|---|---|
| [`../../app/Database/Seeds/data/sekolah-jateng.csv`](../../app/Database/Seeds/data/sekolah-jateng.csv) | satu sekolah per baris; bisa dibuka di Excel |
| [`../../app/Database/Seeds/data/sekolah-jateng.meta.json`](../../app/Database/Seeds/data/sekolah-jateng.meta.json) | sumber, tanggal data, dan jumlah per jenjang/bentuk |
| [`build-sekolah-jateng.php`](build-sekolah-jateng.php) | penyusun kedua berkas di atas dari sumber |

## Isi

Satuan pendidikan **formal** di Jawa Tengah pada jenjang SD/SMP sederajat dan SLB:

| Bentuk | Jumlah | Jenjang |
|---|---:|---|
| SD | 18.520 | sd |
| MI | 4.316 | sd |
| SMP | 3.499 | smp |
| MTs | 1.873 | smp |
| SLB | 188 | slb |
| PDF/SPM Wustha, SPK SMP | 77 | smp |
| SPK SD, SDTK, PDF/SPM Ula | 14 | sd |
| **Total** | **28.487** | 35 kab/kota |

Kolom CSV:

| Kolom | Contoh | Keterangan |
|---|---|---|
| `npsn` | `20318068` | Nomor Pokok Sekolah Nasional, 8 angka, unik |
| `nama` | `SD 1 CENDONO` | nama resmi, apa adanya dari data induk |
| `bentuk` | `SD` | SD, MI, SMP, MTS, SLB, SPK SD, … |
| `jenjang` | `sd` | `sd`, `smp`, atau `slb` — dipakai untuk peringatan "kelas tidak cocok" |
| `status` | `NEGERI` | `NEGERI` atau `SWASTA` |
| `kode_kabupaten` | `33.19` | kode Kemendagri, sama dengan `public/assets/data/wilayah-id.json` |
| `kecamatan` | `DAWE` | tanpa awalan "KEC." |
| `desa` | `CENDONO` | desa/kelurahan |

## Sumber dan lisensi

Data berasal dari **Data Induk Pendidikan** (satuan pendidikan Kemendikdasmen dan
Kemenag), diambil lewat cermin [github.com/bahrye/api-sekolah](https://github.com/bahrye/api-sekolah)
(berlisensi MIT, disinkronkan otomatis dari portal data induk pendidikan nasional).
Tanggal data ada di `upstream_updated` pada berkas `.meta.json`.

Situs resmi untuk memeriksa satu sekolah: [Data Referensi Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/dikdas)
dan [pencarian Dapodik](https://dapo.dikdasmen.go.id/pencarian).

## Memperbarui

```bash
php docs/sekolah/build-sekolah-jateng.php      # unduh, saring, tulis CSV + meta
php spark gelita:schools:import --dry-run      # lihat apa yang berubah
php spark gelita:schools:import                # terapkan ke database
```

- Skrip **menolak menulis** bila ada kab/kota yang tidak terpetakan ke `wilayah-id.json`,
  NPSN yang bukan 8 angka, NPSN ganda, atau jumlah unduhan tidak sama dengan `index.json`.
- Jaringan yang memblokir GitHub: unduh `index.json` dan `data_jawa_tengah_part*.json`
  dari folder `data_provinsi` repositori sumber, lalu
  `php docs/sekolah/build-sekolah-jateng.php --source=/folder/unduhan`.
- Impor hanya menulis baris yang berubah dan **tidak pernah menghapus** sekolah (siswa
  mungkin sudah tertaut). NPSN yang hilang dari berkas baru dilaporkan saja.
- Deploy (`deploy/linux/deploy.sh`, `deploy/windows/deploy.ps1`) menjalankan impor
  setelah migrasi, jadi pembaruan berkas ikut terpasang pada deploy berikutnya.
