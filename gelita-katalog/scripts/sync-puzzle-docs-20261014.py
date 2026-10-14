"""One-time synchronization of current counts; historical progress stays unchanged."""
from pathlib import Path
ROOT=Path(__file__).resolve().parent.parent
changes={
 'README.md': [('590: 493 visual','611: 514 visual'),('440 WebP dan 53 SVG; 440 raster','461 WebP dan 53 SVG; 461 raster'),('493 “Sesuai gaya”','514 “Sesuai gaya”'),('dan 2 ikon bahasa |','2 ikon bahasa, dan 21 puzzle tambahan |'),('493 prompt visual','514 prompt visual')],
 'docs/CONTEXT.md': [('Kondisi terdokumentasi: 12 Oktober 2026','Kondisi terdokumentasi: 14 Oktober 2026'),('590 entri','611 entri'),('45 entri visual Tantangan','66 entri visual Tantangan'),('493 visual terdiri dari 440 WebP','514 visual terdiri dari 461 WebP'),('Semua 440 raster','Semua 461 raster'),('karakter/UI hasil memakai','karakter/UI hasil/puzzle memakai')],
 'docs/AI-GUIDE.md': [('590 entri: 493 visual','611 entri: 514 visual'),('440 raster','461 raster')],
 'docs/DATA-SCHEMA.md': [('array 590 objek','array 611 objek'),('| Tantangan | 45 |','| Tantangan | 66 |')],
 'docs/MAINTENANCE.md': [('440 raster','461 raster')],
 'docs/README.md': [('273 prompt aktif; otomatis','514 prompt aktif; otomatis')],
 'assets/README.md': [('493 visual utama terdiri dari 440 WebP','514 visual utama terdiri dari 461 WebP'),('45 visual kebutuhan tantangan','66 visual kebutuhan tantangan')],
 'templates/catalog.template.html': [('21 variasi tambahan masih berupa kebutuhan gambar.','21 gambar tambahan dan empat gambar pengganti sudah tersedia di katalog. Isi gambar tetap perlu diperiksa peneliti sebelum digunakan.'),('dua ikon bahasa pada 13 Oktober.','dua ikon bahasa pada 13 Oktober, dan 21 gambar puzzle tambahan pada 14 Oktober.')],
}
for file, substitutions in changes.items():
    path=ROOT/file
    text=path.read_text(encoding='utf-8')
    for old,new in substitutions:
        if old not in text:raise ValueError(f'Missing phrase in {file}: {old}')
        text=text.replace(old,new)
    path.write_text(text,encoding='utf-8')
    print(file)
append={
 'README.md': '\nRevisi puzzle 14 Oktober: 21 gambar baru untuk variasi d–j (tujuh per wilayah) dan empat penggantian untuk Sindoro–Sumbing, Pringapus, stupa Borobudur, serta mi ongklok. Semua 25 gambar berupa WebP opak 1200 × 1200. [Daftar dan prompt](docs/PUZZLE-UPDATE-20261014.md), [asal generasi](provenance/puzzle-generation-results-20261014.json), dan [hasil pemeriksa keping](reports/puzzle-tile-review-20261014.json). Tidak ditemukan keping polos/kembar; 11 gambar memiliki catatan kemiripan/pola. Uji susun dua orang dan verifikasi budaya/agama belum dijalankan.\n',
 'docs/CONTEXT.md': '\nRevisi gambar puzzle 14 Oktober: menambah 21 slot d–j dan mengganti empat slot lama sesuai panduan revisi. Katalog kini memiliki 66 visual Tantangan, termasuk 30 gambar Susun Gambar (lima gambar lama lainnya dipertahankan). 25 gambar hasil generasi memakai prompt terbaru dan WebP opak 1200 × 1200. Sebanyak 21 entri baru mengikuti basis user_override dalam permintaan pengguna; riwayat semua entri lama dipertahankan. [Catatan paket](PUZZLE-UPDATE-20261014.md). Ini belum merupakan pemasangan ke aplikasi atau verifikasi penelitian.\n',
 'docs/MEMORY.md': '\n## Keputusan gambar puzzle — 14 Oktober 2026\n\nPengguna meminta aset gambar mengikuti perubahan terbaru dan generasi tambahan. Panduan di update/gambar-puzzle menjadi acuan: menambah 21 variasi d–j (tujuh per wilayah), mengganti empat gambar yang direkomendasikan, mempertahankan kode lama serta lima puzzle lama lainnya. Hasil akhir 25 WebP opak 1200 × 1200; adegan puzzle tanpa Jaka/Mbah Kedu, memakai arah render 3D aktif. Total menjadi 611 entri: 514 visual/97 audio, 66 Tantangan. Objek audio tetap baseline. Basis user_override/previous_style_review lama dipertahankan; penandaan 21 entri baru mengikuti permintaan ini, bukan audit baru. Detail: [paket revisi](PUZZLE-UPDATE-20261014.md).\n',
 'docs/AI-GUIDE.md': '\n## 6. Puzzle terbaru\n\n21 variasi d–j tersedia untuk tmg-1, mgl-1 dan wnb-1; empat gambar lama yang direkomendasikan panduan sudah diganti. Field puzzle berisi item_key, kisi, keterangan bilingual dan needs_verification; puzzle_provenance menunjuk manifest serta sumber WebP lokal. Pertahankan kode a–c lama, jangan membuat ulang gambar yang sudah tersedia hanya karena panduan menyebut buat baru. Ikuti [paket revisi](PUZZLE-UPDATE-20261014.md).\n',
 'docs/DATA-SCHEMA.md': '\n## Metadata puzzle — 14 Oktober 2026\n\n25 entri yang dibuat/diperbarui memiliki puzzle: prompt_code, item_key, grid (3 atau 4), description_id/en, review_status (needs_verification), tile_result dan human_assembly_test. puzzle_provenance menyimpan manifest, source_native, references, revision dan replaced. Ini metadata revisi; tidak mengubah identitas kode slot lama atau kunci butir workbook. Lima puzzle lama lainnya dipertahankan.\n',
 'docs/MAINTENANCE.md': '\n## Pemeriksaan puzzle\n\n`python scripts/verify-puzzle-update-20261014.py` memeriksa 21 penambahan/empat penggantian, preservasi 586 objek lama dan 489 hash aset, riwayat gaya, dimensi 461 raster serta baseline audio. `python scripts/review-puzzle-generation-20261014.py` memeriksa keping menurut ambang panduan dan membuat lembar kontak/kisi/acak WebP. Pemeriksaan otomatis tidak menggantikan uji susun dua orang atau verifikasi isi. Skrip produksi bertanggal bukan pemeliharaan rutin dan tidak boleh digenerasi ulang tanpa permintaan baru.\n',
 'provenance/README.md': '\nPuzzle 14 Oktober: [manifest final](puzzle-generation-results-20261014.json), [prompt panduan](puzzle-generation-20261014/jobs.json), [prompt aktual dan koreksi](puzzle-generation-20261014/results.json), [sumber foto dan lisensi](puzzle-generation-20261014/factual-references/sources.json). Native WebP, kandidat, dan riwayat koreksi disimpan lokal di puzzle-generation-20261014/. 30 panggilan ImageGen: satu percobaan awal tidak dipakai, 25 generasi, empat koreksi; 25 hasil dipilih, 21 slot baru dan empat penggantian.\n',
 'reports/README.md': '\nRevisi puzzle 14 Oktober: [metrik keping](puzzle-tile-review-20261014.json) dan [verifikasi data/hash/ukuran](puzzle-update-verification-20261014.json). Metrik bukan validasi budaya atau uji susun manusia.\n',
 'AGENTS.md': '\n- Revisi puzzle 14 Oktober 2026: 611 entri (514 visual/97 audio), 66 visual Tantangan. 21 variasi d–j baru dan empat penggantian mengikuti update/gambar-puzzle. Gunakan [paket revisi](docs/PUZZLE-UPDATE-20261014.md) dan [manifest](provenance/puzzle-generation-results-20261014.json); jangan menjalankan ulang produksi bertanggal sebagai pemeliharaan rutin.\n',
}
for file, text in append.items():
    with (ROOT/file).open('a',encoding='utf-8') as stream:stream.write(text)
