# Spesifikasi kandidat04 — implementasi lokal

Kanvas1920×1080; skala seragam min(viewportWidth/1920,viewportHeight/1080), letterbox bila berbeda rasio. Tidak ada reflow potret. Bidang peta(30,174,1280,650), panel wilayah(1330,174,560,650), cerita(30,832,1860,232). Master2:1 terdaftar identik pada tiga lapisan.

## Lapisan dan state

Polygon Wonosobo: (0,0),(34,0),(36,25),(43,46),(43,58),(37,80),(34,100),(0,100), persen bidang peta.

Polygon Temanggung: (34,0),(100,0),(100,49),(43,49),(43,46),(36,25).

Polygon Magelang: (43,49),(100,49),(100,100),(34,100),(37,80),(43,58).

Selector .open mengembalikan brightness1/saturate1.03; lainnya brightness.32/saturate.12. Irisan memakai mix-blend-mode:lighten terhadap navy. Ini potongan viewport master, bukan batas geografis. Pemisah mengikuti celah air; evaluasi ulang polygon bila gambar diganti.

Pin satu target besar termasuk medali/nama/status: Temanggung(710,74), Magelang(800,357), Wonosobo(104,230), masing-masing300×244 relatif bidang peta. Nilai berbeda dari posisi web lama, hanya berlaku pada usulan diorama baru.

## Sentuhan dan keyboard

HUD/Play/Previous/Next dan Kenali/Lanjut tinggi144px; menu/close132px. Pada640×360 menjadi48px/44px. Dot12px noninteraktif. Area label/pin tidak memiliki target kecil bersarang. Overlay menahan fokus, Escape/scrim/tutup mengembalikan fokus, panel belakang inert. Disabled tidak menjalankan aksi.

Pemeriksa mengukur DOM seluruh tombol enabled yang terlihat, batas viewport, dan pasangan area klik tanpa overlap pada640×360/854×480, termasuk menu/bahasa/Kenali. Perangkat fisik/fullscreen belum diuji. Bila iframe katalog lebih sempit dari640px, preview mengikuti iframe; hasil minimum44px berlaku untuk viewport mandiri yang diuji.

## Efek ketik

Dua karakter setiap34ms. Play menjeda/melanjutkan; setelah selesai mengulang bagian. Previous/Next mengganti bagian dan memulai efek lagi. Timer berhenti saat dokumen tersembunyi/pagehide, tidak mengupdate di belakang overlay. Reduced-motion menampilkan bagian penuh. Dot menunjukkan bagian, aria menyebut Part/Bagian, teks lengkap diumumkan setelah selesai.

Part03 menyesuaikan petunjuk lentera menjadi Kenali hanya dalam preview. Rekaman audio narasi belum dibuat; jangan menyatakan Play sudah memiliki rekaman.

## Integrasi berikutnya

Hubungkan pemilihan wilayah, progress/rating/unlock, rute menu, status didengar dan narasi ke state aplikasi aktual. Contoh0/12 dan9/12 adalah fixture; bukan penetapan aturan game. Teks/dot/label/tombol tetap hidup; pakai master diorama tanpa UI. Data.json dan seluruh aset resmi terjaga.
