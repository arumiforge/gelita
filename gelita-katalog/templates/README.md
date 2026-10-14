# Template katalog

`catalog.template.html` berisi struktur halaman serta penanda data/ringkasan `/*__DATA__*/` dan `/*__SUMMARY__*/` yang diganti oleh build. Penanda `<!--__BUILD_DATE__-->` memakai tanggal build menurut Asia/Bangkok. Tampilan di [catalog.css](../assets/catalog/catalog.css), interaksi di [catalog.js](../assets/catalog/catalog.js). Bootstrap 5.3.8 serta font lokal ada di [vendor](../assets/vendor/README.md).

Jalur gambar dan tautan dalam template dihitung dari root tempat `index.html` dihasilkan, bukan dari folder `templates/`. Template bukan halaman siap dibuka. Ubah di sini, jalankan `node build.mjs`, lalu periksa hasilnya sesuai [panduan pemeliharaan](../docs/MAINTENANCE.md).
