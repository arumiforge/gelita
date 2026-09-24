<?php

/**
 * Teks warisan: isi tabel `dialogues` dari ContentSeeder sebelum naskah
 * lengkap (docs/naskah-cerita.md) dipakai. Disalin apa adanya dari seeder
 * lama dan tidak boleh diubah lagi.
 *
 * `php spark gelita:story:update` memakainya untuk membedakan baris yang
 * masih berisi teks seeder lama (aman diganti naskah baru) dari baris yang
 * sudah disunting admin (dilewati, kecuali `--force`).
 *
 * Kunci: "{level|_}|{context}|{sequence}" → [text_id, text_en].
 *
 * @return array<string, array{0: string, 1: string}>
 */
return [
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
