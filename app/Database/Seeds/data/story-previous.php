<?php

/**
 * Earlier versions of story lines, keyed by line code (data/story.php).
 *
 * `php spark gelita:story:sync` replaces a `dialogues` row automatically only
 * when its title and text still equal the current script or one of the
 * versions listed here; any other text counts as an edit made in the panel
 * and is left alone unless `--timpa` is given.
 *
 * When the script changes again, append the replaced title_id/title_en/
 * text_id/text_en of every changed line here (oldest first) before or in the
 * same commit that updates data/story.php.
 *
 * Versions listed now: the script used before draft 2 of October 2026
 * (data/story.php at commit 2dcef33), for the 32 lines draft 2 changed;
 * peta-03 before the Peta Kedu redesign of 18 October 2026 (lantern icon →
 * Kenali button); intro-07 before the one HUD of 18 October 2026 (the lantern
 * moved from the top-left corner to the top middle); and the script before
 * research revision 10 (12, part 6, 4 November 2026) for the 35 lines it
 * changed, including the `tuntas-*-03`/`-04` codes that shifted when each
 * region-done story got its new third line.
 *
 * @return array<string, list<array{title_id: ?string, title_en: ?string, text_id: string, text_en: string}>>
 */
return [
    'intro-01' => [
        [
            'title_id' => 'Dataran yang Bercahaya',
            'title_en' => 'The Shining Plain',
            'text_id'  => 'Dahulu kala, di antara Gunung Sindoro, Sumbing, Merapi, Merbabu, dan Perbukitan Menoreh, terbentang Dataran Kedu. Setiap malam, desa-desanya diterangi cahaya lembut yang disebut Cahaya Kedu.',
            'text_en'  => 'Long ago, surrounded by Mount Sindoro, Mount Sumbing, Mount Merapi, Mount Merbabu, and the Menoreh Hills, lay the Kedu Plain. Every night, its villages glowed with a gentle light called the Light of Kedu.',
        ],
    ],
    'intro-02' => [
        [
            'title_id' => 'Cahaya dari Ilmu dan Budaya',
            'title_en' => 'A Light of Knowledge and Culture',
            'text_id'  => 'Cahaya itu lahir dari ilmu dan budaya: dari dongeng para kakek dan nenek, dari relief Candi Borobudur, dari tembang para petani, dan dari setiap tulisan yang dibaca dengan teliti. Cahaya itu disimpan di dalam sebuah lentera tua bernama Lentera Kedu.',
            'text_en'  => 'That light was born from knowledge and culture: from grandparents\' tales, from the reliefs of Borobudur Temple, from the songs of the farmers, and from every piece of writing read with care. It was kept inside an old lantern called the Lantern of Kedu.',
        ],
    ],
    'intro-03' => [
        [
            'title_id' => 'Kabut Lupa',
            'title_en' => 'The Mist of Forgetting',
            'text_id'  => 'Namun perlahan, orang-orang mulai lupa. Mereka membaca dengan terburu-buru, memercayai kabar tanpa memeriksanya, dan meninggalkan cerita para leluhur. Dari kelupaan itu, naiklah kabut kelabu yang dingin: Kabut Lupa.',
            'text_en'  => 'But slowly, people began to forget. They read in a hurry, believed news without checking it, and left their ancestors\' stories behind. From all that forgetting rose a cold grey mist: the Mist of Forgetting.',
        ],
        [
            'title_id' => 'Kabut Lupa',
            'title_en' => 'The Mist of Forgetting',
            'text_id'  => 'Namun perlahan, orang-orang mulai lupa. Mereka membaca dengan terburu-buru, memercayai kabar tanpa memeriksanya, dan meninggalkan cerita para leluhur. Dari kebiasaan itu, naiklah kabut kelabu yang dingin. Namanya Kabut Lupa.',
            'text_en'  => 'But slowly, people began to forget. They read in a hurry, believed news without checking it, and left their ancestors\' stories behind. From those habits, a cold grey mist rose up. It was called the Mist of Forgetting.',
        ],
    ],
    'intro-04' => [
        [
            'title_id' => 'Malam Lentera Retak',
            'title_en' => 'The Night the Lantern Cracked',
            'text_id'  => 'Pada suatu malam, Kabut Lupa membelit Lentera Kedu. Kacanya retak, apinya bergetar, lalu cahayanya pecah! Dua belas Serpihan Cahaya melesat ke langit dan jatuh di Temanggung, Magelang, dan Wonosobo.',
            'text_en'  => 'One night, the Mist of Forgetting coiled around the Lantern of Kedu. Its glass cracked, its flame trembled, and then its light shattered! Twelve Shards of Light shot into the sky and fell across Temanggung, Magelang, and Wonosobo.',
        ],
        [
            'title_id' => 'Malam Lentera Retak',
            'title_en' => 'The Night the Lantern Cracked',
            'text_id'  => 'Pada suatu malam, Kabut Lupa melilit Lentera Kedu. Kacanya retak, apinya bergetar, lalu cahayanya pecah! Dua belas Serpihan Cahaya melesat ke langit dan jatuh di Temanggung, Magelang, dan Wonosobo.',
            'text_en'  => 'One night, the Mist of Forgetting coiled around the Lantern of Kedu. Its glass cracked, its flame trembled, and then its light shattered! Twelve Shards of Light shot into the sky and fell across Temanggung, Magelang, and Wonosobo.',
        ],
    ],
    'intro-05' => [
        [
            'title_id' => 'Penjaga yang Menua',
            'title_en' => 'The Ageing Keeper',
            'text_id'  => 'Aku Mbah Kedu, penjaga lentera ini. Kakiku sudah terlalu tua untuk berjalan jauh, dan setiap kali cahayanya meredup, tubuhku ikut melemah. Aku butuh anak yang berani dan mau membaca dengan sungguh-sungguh.',
            'text_en'  => 'I am Mbah Kedu, which means Grandpa Kedu, the keeper of this lantern. My legs are too old to walk far, and each time its light fades, my body grows weaker too. I need a child who is brave and willing to read with all their heart.',
        ],
    ],
    'intro-08' => [
        [
            'title_id' => 'Tiga Kunci Cahaya',
            'title_en' => 'Three Keys of Light',
            'text_id'  => 'Ingat tiga kunci ini, Le. Di Temanggung, temukan informasi yang tertulis jelas. Di Magelang, hubungkan informasi dan bedakan fakta dari pendapat. Di Wonosobo, nilailah setiap kabar sebelum kamu memercayainya.',
            'text_en'  => 'Remember these three keys, my boy. In Temanggung, find the information that is clearly written. In Magelang, connect information and tell facts from opinions. In Wonosobo, judge every piece of news before you believe it.',
        ],
        [
            'title_id' => 'Tiga Kunci Cahaya',
            'title_en' => 'Three Keys of Light',
            'text_id'  => 'Ingat tiga kunci ini, Le. Di Temanggung, temukan informasi yang tertulis jelas. Di Magelang, hubungkan informasi dan bedakan fakta dari pendapat. Di Wonosobo, periksa dan timbang setiap kabar sebelum kamu memercayainya.',
            'text_en'  => 'Remember these three keys, my boy. In Temanggung, find the information that is clearly written. In Magelang, connect information and tell facts from opinions. In Wonosobo, check and weigh every piece of news before you believe it.',
        ],
    ],
    'peta-01' => [
        [
            'title_id' => 'Dataran Kedu',
            'title_en' => 'The Kedu Plain',
            'text_id'  => 'Inilah Dataran Kedu. Dulu lembah ini menyimpan cahaya ilmu dan budaya. Sekarang lihatlah... Kabut Lupa menutupi hampir semuanya.',
            'text_en'  => 'This is the Kedu Plain. This valley once held the light of knowledge and culture. Now look... the Mist of Forgetting covers almost all of it.',
        ],
    ],
    'kenal-temanggung-01' => [
        [
            'title_id' => 'Di Antara Dua Gunung',
            'title_en' => 'Between Two Mountains',
            'text_id'  => 'Inilah Temanggung, Le. Tanahnya diapit dua gunung, Sindoro dan Sumbing. Kalau pagi cerah, kedua puncaknya tampak berdiri berdampingan, seperti kakak dan adik yang saling menjaga.',
            'text_en'  => 'This is Temanggung, my boy. It lies between two mountains, Sindoro and Sumbing. On a clear morning, their peaks stand side by side, like an older and a younger sibling looking after each other.',
        ],
    ],
    'kenal-temanggung-02' => [
        [
            'title_id' => 'Tanah Para Petani',
            'title_en' => 'Land of Farmers',
            'text_id'  => 'Tanah di lereng gunung ini sangat subur. Para petani menanam tembakau, kopi, dan sayuran. Kopi dari lereng Sindoro dan Sumbing terkenal harum sampai ke luar daerah.',
            'text_en'  => 'The soil on these mountain slopes is very fertile. Farmers grow tobacco, coffee, and vegetables. Coffee from the slopes of Sindoro and Sumbing is famous for its aroma, even far beyond the region.',
        ],
    ],
    'kenal-temanggung-03' => [
        [
            'title_id' => 'Jejak Masa Lalu',
            'title_en' => 'Traces of the Past',
            'text_id'  => 'Temanggung juga menyimpan jejak masa lalu. Ada Situs Liyangan, permukiman kuno yang pernah tertimbun material letusan Gunung Sindoro. Ada pula Candi Pringapus, dan Prasasti Gondosuli yang dibuat pada tahun 832 Masehi.',
            'text_en'  => 'Temanggung also keeps traces of the past. There is the Liyangan Site, an ancient settlement once buried by material from an eruption of Mount Sindoro. There is also Pringapus Temple, and the Gondosuli Inscription, made in the year 832 CE.',
        ],
        [
            'title_id' => 'Jejak Masa Lalu',
            'title_en' => 'Traces of the Past',
            'text_id'  => 'Temanggung juga menyimpan jejak masa lalu. Ada Situs Liyangan, permukiman kuno yang pernah tertimbun pasir dan batu dari letusan Gunung Sindoro. Ada pula Candi Pringapus dengan arca lembu Nandi di dalamnya, dan Prasasti Gondosuli yang dibuat pada tahun 832 Masehi.',
            'text_en'  => 'Temanggung also keeps traces of the past. There is the Liyangan Site, an ancient settlement once buried under sand and stones from an eruption of Mount Sindoro. There is also Pringapus Temple, with a statue of Nandi the bull inside, and the Gondosuli Inscription, made in the year 832 CE.',
        ],
    ],
    'kenal-temanggung-04' => [
        [
            'title_id' => 'Pesan Mbah',
            'title_en' => 'Mbah\'s Advice',
            'text_id'  => 'Saat ada perayaan, warga menari jaran kepang diiringi kendang dan gamelan. Di sini kamu akan belajar menemukan informasi yang tertulis jelas dari gambar, teks pendek, dan suara. Jawabannya sering ada di depan mata, asal kamu membacanya dengan teliti.',
            'text_en'  => 'At celebrations, people dance the jaran kepang, the hobby-horse dance, to the beat of drums and gamelan. Here you will learn to find information that is clearly stated in pictures, short texts, and sounds. The answer is often right in front of you, as long as you read carefully.',
        ],
    ],
    'kenal-magelang-03' => [
        [
            'title_id' => 'Tiga Candi Segaris',
            'title_en' => 'Three Temples in a Line',
            'text_id'  => 'Tak jauh dari Borobudur ada Candi Mendut dan Candi Pawon. Ketiganya berdiri hampir segaris. Di tengah kota ada Gunung Tidar, yang dalam cerita rakyat dijuluki paku tanah Jawa.',
            'text_en'  => 'Not far from Borobudur stand Mendut Temple and Pawon Temple. The three temples stand almost in a straight line. In the middle of the city is Mount Tidar, which folk tales call the nail of the land of Java.',
        ],
    ],
    'kenal-magelang-04' => [
        [
            'title_id' => 'Pesan Mbah',
            'title_en' => 'Mbah\'s Advice',
            'text_id'  => 'Jangan lewatkan tari Topeng Ireng yang gagah, dengan hiasan kepala berbulu warna-warni. Di Magelang, satu informasi saja tidak cukup. Kamu harus menghubungkan beberapa informasi, lalu membedakan mana fakta dan mana pendapat.',
            'text_en'  => 'Don\'t miss the bold Topeng Ireng dance, with its colourful feathered headdresses. In Magelang, one piece of information is not enough. You must connect several pieces of information, then tell which are facts and which are opinions.',
        ],
    ],
    'kenal-wonosobo-02' => [
        [
            'title_id' => 'Candi dan Telaga',
            'title_en' => 'Temples and a Lake',
            'text_id'  => 'Di Dieng berdiri kompleks Candi Arjuna, salah satu candi Hindu tertua di Jawa. Ada juga Telaga Warna. Airnya mengandung belerang, sehingga warnanya bisa tampak hijau, biru, atau kekuningan.',
            'text_en'  => 'On Dieng stands the Arjuna Temple complex, one of the oldest Hindu temples in Java. There is also Telaga Warna, the Lake of Colours. Its water contains sulphur, so it can look green, blue, or yellowish.',
        ],
    ],
    'kenal-wonosobo-03' => [
        [
            'title_id' => 'Hangatnya Budaya Pegunungan',
            'title_en' => 'The Warmth of Mountain Culture',
            'text_id'  => 'Udara di sini dingin sekali. Untuk menghangatkan badan, warga menyantap mi ongklok yang berkuah kental, lalu menikmati manisan carica, si pepaya gunung. Ada pula tari Lengger dan tradisi ruwatan anak berambut gimbal.',
            'text_en'  => 'The air here is very cold. To warm up, people eat mie ongklok, noodles in a thick sauce, and enjoy sweet carica, the mountain papaya. There is also the Lengger dance and the ruwatan ceremony for children with naturally matted hair.',
        ],
    ],
    'kenal-wonosobo-04' => [
        [
            'title_id' => 'Pesan Mbah',
            'title_en' => 'Mbah\'s Advice',
            'text_id'  => 'Di sinilah Kabut Lupa paling tebal, Le. Kamu akan menemukan banyak kabar, dan tidak semuanya benar. Periksa sumbernya, timbang alasannya, lalu putuskan dengan bijak.',
            'text_en'  => 'This is where the Mist of Forgetting is thickest, my boy. You will come across a lot of news, and not all of it is true. Check the source, weigh the reasons, then decide wisely.',
        ],
    ],
    'dialog-temanggung-05' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Ada. Tapi kabut membuat tulisannya samar. Orang hanya melirik sekilas, lalu menebak arah. Satu tebakan keliru, dan mereka berputar-putar di ladang sampai senja.',
            'text_en'  => 'There is. But the mist makes the writing faint. People only glance at it, then guess the way. One wrong guess, and they wander in circles through the fields until dusk.',
        ],
    ],
    'dialog-temanggung-07' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Lihat, ada papan petunjuk! Panahnya ke kanan... Ayo, Mbah, lewat sini!',
            'text_en'  => 'Look, a signpost! The arrow points right... Come on, Mbah, this way!',
        ],
    ],
    'dialog-temanggung-09' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => '"Jembatan rusak. Silakan lewat kiri." Aduh... Kalau tadi aku berlari ke kanan, kita bisa terperosok ke sungai.',
            'text_en'  => '"Bridge broken. Please go left." Oh no... If I had run to the right, we could have fallen into the river.',
        ],
    ],
    'dialog-temanggung-10' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Nah. Mata yang terburu-buru melewatkan jawaban yang tertulis jelas. Kabut Lupa paling senang pada anak yang tidak sabar.',
            'text_en'  => 'There, you see. Hurried eyes miss answers that are written clearly. The Mist of Forgetting loves an impatient child most of all.',
        ],
    ],
    'dialog-magelang-02' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Jangan cepat merasa hebat, Le. Kabut di Magelang punya tipu daya yang lain.',
            'text_en'  => 'Don\'t feel too sure of yourself, my boy. The mist in Magelang has other tricks.',
        ],
    ],
    'dialog-magelang-03' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Tipu daya apa, Mbah? Itu Candi Borobudur, kan? Megah sekali... Tapi kenapa relief-reliefnya tampak aneh?',
            'text_en'  => 'What tricks, Mbah? That\'s Borobudur Temple, isn\'t it? It\'s magnificent... But why do the reliefs look strange?',
        ],
    ],
    'dialog-magelang-06' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Yang satu berteriak, "Katanya candi ini dibangun dalam satu malam!" Yang lain membalas, "Katanya semua itu cuma dongeng!" Keduanya bersuara keras, tapi tak seorang pun mau memeriksa.',
            'text_en'  => 'One shouts, "They say this temple was built in a single night!" The other shouts back, "They say it\'s all just a fairy tale!" Both are loud, but neither of them wants to check.',
        ],
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Bapak yang satu berteriak, "Katanya candi ini dibangun dengan semen!" Bapak yang lain membalas, "Katanya candi ini baru dibangun seratus tahun lalu!" Keduanya hanya berkata "katanya". Tak seorang pun mau memeriksa.',
            'text_en'  => 'One man shouts, "They say this temple was built with cement!" The other shouts back, "They say it was built only a hundred years ago!" Both of them only say "they say". Neither of them wants to check.',
        ],
    ],
    'dialog-magelang-08' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Itulah tugasmu, Le. Pendapat boleh berbeda, tapi fakta bisa dibuktikan. Hubungkan informasi yang satu dengan yang lain, maka kebenaran akan terlihat.',
            'text_en'  => 'That is your task, my boy. Opinions may differ, but facts can be proven. Connect one piece of information with another, and the truth will appear.',
        ],
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Itulah tugasmu, Le. Pendapat, atau opini, boleh berbeda, tapi fakta bisa dibuktikan. "Candi ini indah sekali" itu pendapat. "Candi ini dibangun lebih dari seribu tahun lalu" itu fakta. Hubungkan informasi yang satu dengan yang lain, maka kebenaran akan terlihat.',
            'text_en'  => 'That is your task, my boy. Opinions may differ, but facts can be proven. "This temple is very beautiful" is an opinion. "This temple was built more than a thousand years ago" is a fact. Connect one piece of information with another, and the truth will appear.',
        ],
    ],
    'dialog-magelang-15' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Ingat pesan Mbah: temukan ide pokoknya, susun urutannya, dan pisahkan fakta dari pendapat. Empat serpihan Magelang menunggumu.',
            'text_en'  => 'Remember my advice: find the main idea, put things in order, and separate facts from opinions. Four shards of Magelang are waiting for you.',
        ],
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Ingat pesan Mbah: temukan gagasan pokoknya, susun urutannya, dan pisahkan fakta dari pendapat. Empat serpihan Magelang menunggumu.',
            'text_en'  => 'Remember my advice: find the main idea, put things in order, and separate facts from opinions. Four shards of Magelang are waiting for you.',
        ],
    ],
    'dialog-wonosobo-03' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Mbah, dengar! Ada kentongan dipukul. Orang-orang berlarian sambil memegang ponsel!',
            'text_en'  => 'Mbah, listen! Someone is beating the kentongan drum. People are running around holding their phones!',
        ],
    ],
    'dialog-wonosobo-04' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Mereka menerima pesan berantai: "Kawah akan meledak besok pagi! Cepat mengungsi! Sebarkan ke semua orang!" Tak ada nama pengirim, tak ada sumber resmi.',
            'text_en'  => 'They have received a chain message: "The crater will erupt tomorrow morning! Evacuate now! Share this with everyone!" There is no sender\'s name and no official source.',
        ],
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Mereka menerima pesan berantai: "Kawah akan meletus besok pagi! Cepat mengungsi! Sebarkan ke semua orang!" Tak ada nama pengirim, tak ada sumber resmi.',
            'text_en'  => 'They have received a chain message: "The crater will erupt tomorrow morning! Evacuate now! Share this with everyone!" There is no sender\'s name and no official source.',
        ],
    ],
    'dialog-wonosobo-12' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Dengarkan pesan Mbah. Sebelum percaya, periksa sumbernya. Sebelum membagikan, pikirkan akibatnya. Kalau ragu, tanyakan kepada orang yang tepat.',
            'text_en'  => 'Listen to my advice. Before you believe, check the source. Before you share, think about what could happen. When in doubt, ask the right person.',
        ],
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Dengarkan pesan Mbah. Sebelum percaya, periksa sumbernya. Sebelum membagikan, pikirkan akibatnya. Kalau ragu, tanyakan kepada orang yang tepat, seperti guru, orang tua, atau petugas resmi.',
            'text_en'  => 'Listen to my advice. Before you believe, check the source. Before you share, think about what could happen. When in doubt, ask the right person, such as a teacher, a parent, or an official.',
        ],
    ],
    'tuntas-temanggung-03' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Itu buah ketelitianmu. Pustaka Temanggung kini terbuka untukmu. Bacalah sepuasnya.',
            'text_en'  => 'That is the reward for your care. The Temanggung Library is now open to you. Read as much as you like.',
        ],
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Itu hasil ketelitianmu. Pustaka Temanggung kini terbuka untukmu. Bacalah sepuasnya.',
            'text_en'  => 'That is the reward for your care. The Temanggung Library is now open to you. Read as much as you like.',
        ],
    ],
    'tuntas-magelang-01' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Dengar, Le... Kedua bapak itu berhenti bertengkar. Sekarang mereka membaca relief bersama-sama, dari awal sampai akhir.',
            'text_en'  => 'Listen, my boy... Those two men have stopped arguing. Now they are reading the reliefs together, from beginning to end.',
        ],
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Dengar, Le... Kedua bapak itu berhenti bertengkar. Di papan informasi tertulis: Borobudur dibangun pada abad ke-8 dan ke-9 dari batu yang disusun saling mengunci, tanpa semen. Jadi, kedua kabar "katanya" tadi keliru. Sekarang mereka membaca relief bersama-sama.',
            'text_en'  => 'Listen, my boy... Those two men have stopped arguing. The information board says: Borobudur was built in the 8th and 9th centuries from interlocking stones, without cement. So both "they say" stories were wrong. Now they are reading the reliefs together.',
        ],
    ],
    'tuntas-wonosobo-01' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Mbah! Aku berhasil! Warga sudah menanyakan kabar itu kepada petugas resmi. Kawahnya aman, tidak ada yang perlu mengungsi!',
            'text_en'  => 'Mbah! I did it! People asked the officials about the message. The crater is safe, and nobody needs to evacuate!',
        ],
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Mbah! Aku berhasil! Aku mengajak warga bertanya kepada petugas resmi. Kata petugas, keadaan kawah normal, jadi tidak ada yang perlu mengungsi. Semua tetap menjaga jarak sesuai rambu.',
            'text_en'  => 'Mbah! I did it! I got people to ask the officials. The officials say the crater is at normal status, so nobody needs to evacuate. Everyone is still keeping a safe distance, as the signs say.',
        ],
    ],
    'penutup-02' => [
        [
            'title_id' => 'Kabut Tersibak',
            'title_en' => 'The Mist Clears',
            'text_id'  => 'Kabut Lupa tersibak dari Temanggung, Magelang, sampai Wonosobo. Papan petunjuk terbaca jelas, relief bercerita dengan urutan yang benar, dan pesan berantai tak lagi ditelan mentah-mentah.',
            'text_en'  => 'The Mist of Forgetting cleared from Temanggung to Magelang to Wonosobo. Signposts could be read clearly, the reliefs told their stories in the right order, and chain messages were no longer swallowed whole.',
        ],
        [
            'title_id' => 'Kabut Tersibak',
            'title_en' => 'The Mist Clears',
            'text_id'  => 'Kabut Lupa tersibak dari Temanggung, Magelang, sampai Wonosobo. Papan petunjuk terbaca jelas, relief bercerita dengan urutan yang benar, dan pesan berantai tidak lagi langsung dipercaya begitu saja.',
            'text_en'  => 'The Mist of Forgetting cleared from Temanggung to Magelang to Wonosobo. Signposts could be read clearly, the reliefs told their stories in the right order, and chain messages were no longer believed without checking.',
        ],
    ],
    'peta-03' => [
        [
            'title_id' => 'Kenali Dulu',
            'title_en' => 'Get to Know Them First',
            'text_id'  => 'Ketuk ikon lentera di setiap wilayah untuk mendengar cerita Mbah Kedu tentang tempat itu. Kalau sudah siap, ketuk wilayah yang lenteranya menyala. Ayo, kita berangkat!',
            'text_en'  => 'Tap the lantern icon on each region to hear Mbah Kedu\'s story about that place. When you are ready, tap the region whose lantern is lit. Come on, let\'s go!',
        ],
    ],
    'intro-07' => [
        [
            'title_id' => 'Cara Mengumpulkan Serpihan',
            'title_en' => 'How to Gather the Shards',
            'text_id'  => 'Setiap wilayah menyimpan empat serpihan, dan setiap serpihan dijaga oleh satu tantangan. Selesaikan tantangannya untuk mendapatkan serpihan dan bintang. Lihat lentera di pojok kiri atas: lentera itu makin terang setiap kali serpihan kembali.',
            'text_en'  => 'Each region holds four shards, and each shard is guarded by a challenge. Complete the challenge to win its shard and stars. Watch the lantern in the top-left corner: it grows brighter every time a shard returns.',
        ],
        [
            'title_id' => 'Cara Mengumpulkan Serpihan',
            'title_en' => 'How to Gather the Shards',
            'text_id'  => 'Setiap wilayah menyimpan empat serpihan, dan setiap serpihan dijaga oleh satu tantangan. Selesaikan tantangannya untuk mendapatkan serpihan dan bintang. Lihat lentera di bagian atas layar: lentera itu makin terang setiap kali serpihan kembali.',
            'text_en'  => 'Each region holds four shards, and each shard is guarded by a challenge. Complete the challenge to win its shard and stars. Watch the lantern at the top of the screen: it grows brighter every time a shard returns.',
        ],
    ],
    'intro-06' => [
        [
            'title_id' => 'Jaka, Pembawa Lentera',
            'title_en' => 'Jaka, the Lantern Bearer',
            'text_id'  => 'Biar aku yang pergi, Mbah! Aku akan mencari kedua belas serpihan itu satu per satu, sampai Lentera Kedu menyala lagi. Aku janji!',
            'text_en'  => 'Let me go, Mbah! I will find all twelve shards, one by one, until the Lantern of Kedu shines again. I promise!',
        ],
    ],
    'peta-02' => [
        [
            'title_id' => 'Tiga Wilayah',
            'title_en' => 'Three Regions',
            'text_id'  => 'Aku harus menjelajahi tiga wilayah untuk memulihkannya: Temanggung, lalu Magelang, dan terakhir Wonosobo. Di setiap wilayah, empat Serpihan Cahaya sedang menunggu.',
            'text_en'  => 'I must explore three regions to bring it back: Temanggung, then Magelang, and finally Wonosobo. In each region, four Shards of Light are waiting.',
        ],
    ],
    'kenal-magelang-02' => [
        [
            'title_id' => 'Buku dari Batu',
            'title_en' => 'A Book Made of Stone',
            'text_id'  => 'Dinding Borobudur dihiasi 2.672 panel relief. Setiap panel adalah bagian dari sebuah cerita. Membacanya seperti membaca buku raksasa dari batu: urutannya harus benar supaya ceritanya utuh.',
            'text_en'  => 'Borobudur\'s walls are decorated with 2,672 relief panels. Each panel is part of a story. Reading them is like reading a giant book made of stone: the order must be right for the story to make sense.',
        ],
    ],
    'dialog-temanggung-14' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Empat serpihan menunggumu di Temanggung. Temukan informasi yang tertulis jelas, maka kabut ini akan mundur dengan sendirinya.',
            'text_en'  => 'Four shards are waiting for you in Temanggung. Find the information that is clearly written, and this mist will retreat on its own.',
        ],
    ],
    'dialog-wonosobo-05' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Tapi bagaimana kalau pesan itu benar, Mbah? Kalau kita diam saja, orang bisa celaka!',
            'text_en'  => 'But what if the message is true, Mbah? If we do nothing, people could get hurt!',
        ],
    ],
    'dialog-wonosobo-06' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Pertanyaan yang bagus, Le. Justru karena ini penting, kita harus memeriksanya. Kabar bohong paling mudah menyebar ketika orang sedang takut.',
            'text_en'  => 'That\'s a good question, my boy. It is exactly because this matters that we must check it. False news spreads most easily when people are afraid.',
        ],
    ],
    'dialog-wonosobo-13' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Periksa sumbernya... pikirkan akibatnya... tanyakan kepada orang yang tepat. Aku akan mengingatnya, Mbah.',
            'text_en'  => 'Check the source... think about what could happen... ask the right person. I will remember, Mbah.',
        ],
    ],
    'dialog-wonosobo-15' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Itu baru anak pemberani. Pergilah, Le. Empat serpihan terakhir menunggu di balik kabut.',
            'text_en'  => 'Now that\'s a brave child. Go, my boy. The last four shards are waiting behind the mist.',
        ],
    ],
    'tuntas-temanggung-02' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Empat serpihan sudah kembali ke lentera! Rasanya hangat sekali, Mbah.',
            'text_en'  => 'Four shards are back in the lantern! It feels so warm, Mbah.',
        ],
    ],
    'tuntas-temanggung-04' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Terima kasih, Mbah. Sekarang, ke Magelang!',
            'text_en'  => 'Thank you, Mbah. Now, on to Magelang!',
        ],
    ],
    'tuntas-magelang-02' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Dan Mbah... Mbah sudah bisa berdiri tegak lagi!',
            'text_en'  => 'And Mbah... you can stand up straight again!',
        ],
    ],
    'tuntas-magelang-03' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Delapan serpihan sudah kembali, dan tenaga Mbah ikut pulih. Pustaka Magelang kini terbuka untukmu.',
            'text_en'  => 'Eight shards have returned, and my strength is coming back. The Magelang Library is now open to you.',
        ],
    ],
    'tuntas-magelang-04' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Tinggal satu wilayah lagi. Wonosobo, kami datang!',
            'text_en'  => 'Only one region left. Wonosobo, here we come!',
        ],
    ],
    'tuntas-wonosobo-02' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Mbah melihatnya dari tepi telaga, Le. Setiap kali kamu memilih dengan bijak, kabut di puncak itu menipis sedikit demi sedikit.',
            'text_en'  => 'I saw it from the lake shore, my boy. Every time you chose wisely, the mist on that peak grew a little thinner.',
        ],
    ],
    'tuntas-wonosobo-03' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Dua belas serpihan... semuanya sudah kembali!',
            'text_en'  => 'Twelve shards... every single one is back!',
        ],
    ],
    'tuntas-wonosobo-04' => [
        [
            'title_id' => null,
            'title_en' => null,
            'text_id'  => 'Pustaka Wonosobo terbuka untukmu. Sekarang, mari kita pulang dan nyalakan Lentera Kedu bersama-sama.',
            'text_en'  => 'The Wonosobo Library is open to you. Now, let\'s go home and light the Lantern of Kedu together.',
        ],
    ],
    'penutup-01' => [
        [
            'title_id' => 'Lentera Menyala Kembali',
            'title_en' => 'The Lantern Shines Again',
            'text_id'  => 'Di puncak bukit, Jaka mengangkat lenteranya tinggi-tinggi. Kedua belas serpihan menyatu, dan cahaya keemasan memancar ke seluruh Dataran Kedu.',
            'text_en'  => 'On top of the hill, Jaka lifted his lantern high. All twelve shards joined together, and a golden light poured out across the whole Kedu Plain.',
        ],
    ],
    'penutup-03' => [
        [
            'title_id' => 'Mbah Kedu Pulih',
            'title_en' => 'Mbah Kedu Recovers',
            'text_id'  => 'Badan Mbah terasa segar lagi, Le! Tapi ingat, cahaya ini bukan hanya milik lentera. Cahaya ini hidup setiap kali seseorang membaca dengan teliti.',
            'text_en'  => 'I feel strong again, my boy! But remember, this light does not belong to the lantern alone. It lives every time someone reads with care.',
        ],
    ],
    'penutup-04' => [
        [
            'title_id' => 'Janji Jaka',
            'title_en' => 'Jaka\'s Promise',
            'text_id'  => 'Aku akan terus membaca dengan teliti, menghubungkan informasi, dan memeriksa sumbernya, Mbah. Supaya Kabut Lupa tidak pernah kembali.',
            'text_en'  => 'I will keep reading carefully, connecting information, and checking the source, Mbah. So that the Mist of Forgetting never comes back.',
        ],
    ],
    'peta-tuntas-01' => [
        [
            'title_id' => 'Kabut Sudah Pergi',
            'title_en' => 'The Mist Is Gone',
            'text_id'  => 'Lihat! Kabut Lupa sudah hilang dari Dataran Kedu. Temanggung, Magelang, dan Wonosobo bercahaya lagi berkat kerja kerasmu.',
            'text_en'  => 'Look! The Mist of Forgetting is gone from the Kedu Plain. Temanggung, Magelang, and Wonosobo shine again thanks to your hard work.',
        ],
    ],
    'peta-tuntas-02' => [
        [
            'title_id' => 'Cahaya yang Dijaga',
            'title_en' => 'A Light to Keep',
            'text_id'  => 'Kedua belas Serpihan Cahaya sudah kembali. Sekarang tugas kita menjaga cahayanya: terus membaca dengan teliti dan memeriksa sumbernya.',
            'text_en'  => 'All twelve Shards of Light are back. Now our job is to keep the light shining: keep reading carefully and checking the source.',
        ],
    ],
];
