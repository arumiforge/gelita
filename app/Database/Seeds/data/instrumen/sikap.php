<?php

/**
 * Attitude scale "Menjaga Warisan" in Balai Refleksi (research revision 10,
 * part 8: 34 §6.1, §9, K2, K13).
 *
 * Copied as-is from docs/revisi-10-analitik/17_SKALA_SIKAP.md (Butir,
 * Empat pilihan, Petunjuk untuk siswa). Status `needs_verification`; it becomes
 * `sikap-1.0` when frozen at G4 (17). Edit this file and raise `version` after
 * every change, then run `php spark gelita:instrumen:impor`. Indonesian only (K13).
 *
 * `direction` and `indicator` are for part 9 (35); the module stores the raw
 * codes 1–4 and never reverses the negative items (34 §6.1).
 */

return [
    'version' => 'sikap-0.1',
    'status'  => 'needs_verification',
    'source'  => 'docs/revisi-10-analitik 17 (2 November 2026)',
    'intro'   => [
        'title'      => 'Pendapatmu tentang budaya Kedu',
        'paragraphs' => [
            'Kamu akan membaca 12 kalimat. Pilih yang paling cocok dengan dirimu: Tidak setuju, Kurang setuju, Setuju, atau Sangat setuju.',
            'Tidak ada jawaban benar atau salah. Jawabanmu tidak dinilai dan tidak dilihat gurumu. Kalau ragu, pilih yang paling dekat dengan perasaanmu. Kamu boleh melewati kalimat yang tidak ingin kamu jawab.',
            'Ayo coba dulu satu contoh.',
        ],
    ],
    // Left to right, the same for every item (17 → Empat pilihan)
    'choices' => ['1' => 'Tidak setuju', '2' => 'Kurang setuju', '3' => 'Setuju', '4' => 'Sangat setuju'],
    'items'   => [
        'skp-ct-01' => ['no' => 0, 'text' => 'Aku suka makan buah.', 'example' => true],
        'skp-01'    => ['no' => 1, 'indicator' => 'SK4', 'direction' => '+', 'text' => 'Aku bangga pada budaya daerah Kedu.'],
        'skp-02'    => ['no' => 2, 'indicator' => 'SK1', 'direction' => '+', 'text' => 'Candi di daerah Kedu adalah warisan yang berharga.'],
        'skp-03'    => ['no' => 3, 'indicator' => 'SK2', 'direction' => '-', 'text' => 'Belajar tentang candi dan tari itu membosankan.'],
        'skp-04'    => ['no' => 4, 'indicator' => 'SK3', 'direction' => '+', 'text' => 'Aku mau ikut menjaga kebersihan saat berkunjung ke candi.'],
        'skp-05'    => ['no' => 5, 'indicator' => 'SK2', 'direction' => '+', 'text' => 'Aku ingin tahu lebih banyak tentang budaya Kedu.'],
        'skp-06'    => ['no' => 6, 'indicator' => 'SK1', 'direction' => '-', 'text' => 'Tradisi lama sudah kuno, jadi boleh dilupakan.'],
        'skp-07'    => ['no' => 7, 'indicator' => 'SK4', 'direction' => '+', 'text' => 'Aku senang menceritakan budaya Kedu kepada teman.'],
        'skp-08'    => ['no' => 8, 'indicator' => 'SK3', 'direction' => '-', 'text' => 'Merawat warisan budaya itu urusan orang dewasa saja.'],
        'skp-09'    => ['no' => 9, 'indicator' => 'SK1', 'direction' => '+', 'text' => 'Kesenian dari daerah Kedu penting untuk dijaga.'],
        'skp-10'    => ['no' => 10, 'indicator' => 'SK4', 'direction' => '-', 'text' => 'Aku malu memakai bahasa daerah di depan teman.'],
        'skp-11'    => ['no' => 11, 'indicator' => 'SK2', 'direction' => '+', 'text' => 'Aku suka bertanya tentang tradisi kepada orang yang lebih tua.'],
        'skp-12'    => ['no' => 12, 'indicator' => 'SK3', 'direction' => '+', 'text' => 'Aku mau ikut acara budaya seperti merti desa dan kirab.'],
    ],
];
