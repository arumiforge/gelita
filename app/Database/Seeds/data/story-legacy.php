<?php

/**
 * Teks warisan: versi lama baris tabel `dialogues` yang aman diganti naskah
 * terbaru. Disalin apa adanya dan tidak boleh diubah lagi; bila naskah
 * berubah lagi, tambahkan kelompok baru berisi teks versi sebelumnya.
 *
 * `php spark gelita:story:update` memakainya untuk membedakan baris yang
 * masih berisi teks lama (aman diganti naskah baru) dari baris yang sudah
 * disunting admin (dilewati, kecuali `--force`).
 *
 * Kelompok:
 * - seeder ($seeder): isi ContentSeeder sebelum naskah lengkap (docs/naskah-cerita.md);
 * - lima_belas ($limaBelas): naskah lengkap sebelum perombakan kelas 5 (15 serpihan,
 *   lima tantangan per wilayah), Dok 2 §11.1.
 *
 * Kunci setiap kelompok: "{level|_}|{context}|{sequence}" → [text_id, text_en].
 * Satu posisi boleh muncul di beberapa kelompok (beberapa versi lama).
 *
 * @return array<string, array<string, array{0: string, 1: string}>> kelompok → kunci → teks
 */
$seeder = [
    '_|intro|1' => [
        'Di tanah Kedu hiduplah Jaka, anak pembawa lentera. Suatu malam lenteranya padam, dan cahayanya pecah menjadi Serpihan Cahaya yang tersebar jauh.',
        'In the land of Kedu lived Jaka, the lantern bearer. One night his lantern went dark, and its light broke into Shards of Light that scattered far away.',
    ],
    '_|intro|2' => [
        'Serpihan itu ada di tiga wilayah, Le: pertama Temanggung, lalu Magelang, dan terakhir Wonosobo. Setiap serpihan menunggu anak yang mau membaca dengan teliti.',
        'The shards are in three regions, my boy: first Temanggung, then Magelang, and finally Wonosobo. Each shard waits for a child who reads carefully.',
    ],
    '_|intro|3' => [
        'Aku akan mencarinya, Mbah! Aku mulai dari Temanggung, lalu ke Magelang, dan terakhir ke Wonosobo.',
        'I will find them, Mbah! I will start in Temanggung, then go to Magelang, and finally to Wonosobo.',
    ],
    '_|intro|4' => [
        'Bawa lenteramu. Baca dengan teliti, pikirkan baik-baik, lalu putuskan dengan bijak. Ayo berangkat!',
        'Bring your lantern. Read carefully, think it through, then decide wisely. Let us set off!',
    ],

    'temanggung|level_open|1' => [
        'Selamat datang di Temanggung, Le. Tanah subur ini berada di antara Gunung Sindoro dan Gunung Sumbing.',
        'Welcome to Temanggung, my boy. This fertile land lies between Mount Sindoro and Mount Sumbing.',
    ],
    'temanggung|level_open|2' => [
        'Aku akan mencari informasi yang tertulis jelas dengan teliti, Mbah!',
        'I will carefully look for information that is stated clearly, Mbah!',
    ],
    'temanggung|level_done|1' => [
        'Bagus sekali! Serpihan Cahaya Temanggung sudah terkumpul.',
        'Well done! The Shards of Light of Temanggung have been gathered.',
    ],
    'temanggung|level_done|2' => [
        'Lenteraku mulai menyala. Sekarang ke Magelang!',
        'My lantern is starting to glow. Now on to Magelang!',
    ],

    'magelang|level_open|1' => [
        'Inilah Magelang, tanah Candi Borobudur dan Gunung Tidar. Di sini kamu belajar menghubungkan informasi.',
        'This is Magelang, home of Borobudur Temple and Mount Tidar. Here you learn to connect information.',
    ],
    'magelang|level_open|2' => [
        'Aku akan membandingkan informasi dan membedakan fakta dari pendapat!',
        'I will compare information and tell facts from opinions!',
    ],
    'magelang|level_done|1' => [
        'Kamu pandai menghubungkan informasi. Serpihan Cahaya Magelang kini milikmu.',
        'You are good at connecting information. The Shards of Light of Magelang are now yours.',
    ],
    'magelang|level_done|2' => [
        'Tinggal satu wilayah lagi: Wonosobo!',
        'Only one region left: Wonosobo!',
    ],

    'wonosobo|level_open|1' => [
        'Kita tiba di Wonosobo, negeri di atas awan. Di sini kamu harus menilai informasi sebelum mempercayainya.',
        'We have reached Wonosobo, the land above the clouds. Here you must judge information before you believe it.',
    ],
    'wonosobo|level_open|2' => [
        'Aku akan memeriksa sumbernya dulu dan bertindak dengan bijak!',
        'I will check the source first and act wisely!',
    ],
    'wonosobo|level_done|1' => [
        'Luar biasa! Serpihan Cahaya Wonosobo sudah terkumpul.',
        'Amazing! The Shards of Light of Wonosobo have been gathered.',
    ],
    'wonosobo|level_done|2' => [
        'Terima kasih, Mbah. Lenteraku menyala terang!',
        'Thank you, Mbah. My lantern shines brightly!',
    ],

    '_|ending|1' => [
        'Lentera Jaka kembali menyala terang. Cahayanya menerangi Temanggung, Magelang, dan Wonosobo.',
        "Jaka's lantern shines brightly again. Its light reaches Temanggung, Magelang, and Wonosobo.",
    ],
    '_|ending|2' => [
        'Ingat, Le: baca dengan teliti, periksa sumbernya, dan jagalah warisan budaya Kedu.',
        'Remember, my boy: read carefully, check the source, and look after the cultural heritage of Kedu.',
    ],
];

$limaBelas = [
    '_|intro|4' => [ // intro-04
        'Pada suatu malam, Kabut Lupa membelit Lentera Kedu. Kacanya retak, apinya bergetar, lalu cahayanya pecah! Lima belas Serpihan Cahaya melesat ke langit dan jatuh di Temanggung, Magelang, dan Wonosobo.',
        'One night, the Mist of Forgetting coiled around the Lantern of Kedu. Its glass cracked, its flame trembled, and then its light shattered! Fifteen Shards of Light shot into the sky and fell across Temanggung, Magelang, and Wonosobo.',
    ],
    '_|intro|6' => [ // intro-06
        'Biar aku yang pergi, Mbah! Aku akan mencari kelima belas serpihan itu satu per satu, sampai Lentera Kedu menyala lagi. Aku janji!',
        'Let me go, Mbah! I will find all fifteen shards, one by one, until the Lantern of Kedu shines again. I promise!',
    ],
    '_|intro|7' => [ // intro-07
        'Setiap wilayah menyimpan lima serpihan, dan setiap serpihan dijaga oleh satu tantangan. Selesaikan tantangannya untuk mendapatkan serpihan dan bintang. Lihat lentera di pojok kiri atas: lentera itu makin terang setiap kali serpihan kembali.',
        'Each region holds five shards, and each shard is guarded by a challenge. Complete the challenge to win its shard and stars. Watch the lantern in the top-left corner: it grows brighter every time a shard returns.',
    ],
    '_|map_intro|2' => [ // peta-02
        'Aku harus menjelajahi tiga wilayah untuk memulihkannya: Temanggung, lalu Magelang, dan terakhir Wonosobo. Di setiap wilayah, lima Serpihan Cahaya sedang menunggu.',
        'I must explore three regions to bring it back: Temanggung, then Magelang, and finally Wonosobo. In each region, five Shards of Light are waiting.',
    ],
    'temanggung|level_open|14' => [ // dialog-temanggung-14
        'Lima serpihan menunggumu di Temanggung. Temukan informasi yang tertulis jelas, maka kabut ini akan mundur dengan sendirinya.',
        'Five shards are waiting for you in Temanggung. Find the information that is clearly written, and this mist will retreat on its own.',
    ],
    'magelang|level_open|15' => [ // dialog-magelang-15
        'Ingat pesan Mbah: temukan ide pokoknya, susun urutannya, dan pisahkan fakta dari pendapat. Lima serpihan Magelang menunggumu.',
        'Remember my advice: find the main idea, put things in order, and separate facts from opinions. Five shards of Magelang are waiting for you.',
    ],
    'wonosobo|level_open|15' => [ // dialog-wonosobo-15
        'Itu baru anak pemberani. Pergilah, Le. Lima serpihan terakhir menunggu di balik kabut.',
        'Now that\'s a brave child. Go, my boy. The last five shards are waiting behind the mist.',
    ],
    'temanggung|level_done|2' => [ // tuntas-temanggung-02
        'Lima serpihan sudah kembali ke lentera! Rasanya hangat sekali, Mbah.',
        'Five shards are back in the lantern! It feels so warm, Mbah.',
    ],
    'magelang|level_done|3' => [ // tuntas-magelang-03
        'Sepuluh serpihan sudah kembali, dan tenaga Mbah ikut pulih. Pustaka Magelang kini terbuka untukmu.',
        'Ten shards have returned, and my strength is coming back. The Magelang Library is now open to you.',
    ],
    'wonosobo|level_done|3' => [ // tuntas-wonosobo-03
        'Lima belas serpihan... semuanya sudah kembali!',
        'Fifteen shards... every single one is back!',
    ],
    '_|ending|1' => [ // penutup-01
        'Di puncak bukit, Jaka mengangkat lenteranya tinggi-tinggi. Kelima belas serpihan menyatu, dan cahaya keemasan memancar ke seluruh Dataran Kedu.',
        'On top of the hill, Jaka lifted his lantern high. All fifteen shards joined together, and a golden light poured out across the whole Kedu Plain.',
    ],
];

return ['seeder' => $seeder, 'lima_belas' => $limaBelas];
