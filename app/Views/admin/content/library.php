<?php
/**
 * Pustaka Kedu — `/admin/konten/pustaka/{levelId}` → ContentController::library
 *
 * Halaman buku yang dibaca siswa di menu Pustaka. Satu form menyimpan semua
 * halaman beserta medianya; kartu "Halaman baru" diabaikan bila judulnya kosong.
 *
 * Setiap halaman boleh punya gambar/video sebanyak apa pun (tabel
 * library_media). Tiap media berasal dari SALAH SATU:
 *   - Berkas: asset_key terdaftar, atau unggah berkas baru di baris itu;
 *   - Tautan: YouTube, Google Drive, Vimeo, Wikimedia Commons, atau alamat
 *     berkas gambar/video langsung (https).
 * Baris media yang dikosongkan atau dicentang Hapus dihapus saat disimpan.
 * Halaman & media nonaktif tetap tampil di sini (bertanda) tetapi tidak di
 * permainan.
 *
 * @var App\Entities\Level                               $level
 * @var list<App\Entities\LibraryPage>                   $pages
 * @var array<int, list<App\Entities\LibraryMedia>>      $media  library_page_id → media
 */
$rows   = $pages;
$rows[] = null;
$spare  = 2;   // baris media kosong per halaman untuk menambah
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Pustaka Kedu',
    'eyebrow' => 'Wilayah ' . $level->sequence . ' · ' . $level->text('name', 'id'),
    'lead'    => 'Halaman buku yang terbuka untuk siswa di menu Pustaka, lengkap dengan galeri gambar dan video. Judul wajib dwibahasa; isi English yang kosong diganti teks Indonesia saat permainan.',
]) ?>
<?= component('partials/content-nav', ['level' => $level, 'active' => 'library']) ?>
<?= $this->include('partials/flash') ?>

<details class="panel">
  <summary class="panel-title"><?= icon('info') ?> Cara menulis isi & menambah media</summary>
  <div class="split-grid">
    <div class="format-help">
      <p><b>Format isi halaman</b> (ditulis biasa, tanpa HTML):</p>
      <ul>
        <li>Baris kosong = paragraf baru.</li>
        <li><code>## Subjudul</code> di awal baris = subjudul.</li>
        <li><code>- teks</code> di awal baris = butir daftar.</li>
        <li><code>**kata**</code> = cetak tebal; <code>*kata*</code> = cetak miring.</li>
        <li><code>&gt; Tahukah kamu? …</code> = kotak fakta menarik.</li>
        <li><code>Sumber: …</code> di baris terakhir = catatan rujukan kecil.</li>
      </ul>
    </div>
    <div class="format-help">
      <p><b>Sumber media</b> — pilih salah satu per baris:</p>
      <ul>
        <li><b>Berkas</b>: pilih asset_key yang sudah diunggah, atau unggah berkas baru (gambar maks. 64 MB; saran 960 × 640 px).</li>
        <li><b>Tautan</b>: YouTube (<code>youtu.be/…</code>, <code>youtube.com/watch?v=…</code>), Google Drive (berkas dibagikan "Siapa saja yang memiliki link"), Vimeo, Wikimedia Commons (<code>commons.wikimedia.org/wiki/File:…</code>), atau alamat berkas <code>.jpg/.png/.webp/.mp4</code>.</li>
        <li>Video YouTube, Vimeo, dan Drive baru dimuat setelah siswa menekan Putar.</li>
        <li>Isi kredit (pemilik/lisensi) untuk setiap media dari pihak lain.</li>
      </ul>
    </div>
  </div>
</details>

<?= component('components/media-datalist', ['types' => ['image']]) ?>
<?= component('components/media-datalist', ['types' => ['image', 'video']]) ?>

<form method="post" action="<?= base_url('admin/konten/pustaka/' . $level->id) ?>" class="stack" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <?php foreach ($rows as $index => $page): ?>
    <?php
    $isNew      = $page === null;
    $p          = 'lib' . $index;
    $pageMedia  = $isNew ? [] : ($media[$page->id] ?? []);
    $mediaRows  = array_merge($pageMedia, array_fill(0, $spare, null));
    $sequence   = $isNew ? (count($pages) === 0 ? 1 : max(array_map(static fn ($pg): int => $pg->sequence, $pages)) + 1) : $page->sequence;
    $nextMedia  = $pageMedia === [] ? 1 : max(array_map(static fn ($md): int => $md->sequence, $pageMedia)) + 1;
    ?>
    <details class="repeat-row library-page-card<?= $isNew ? ' is-new' : '' ?>" <?= $isNew || count($pages) <= 2 ? 'open' : '' ?>>
      <summary>
        <?= icon($isNew ? 'sparkle' : 'book') ?>
        <b><?= $isNew ? 'Halaman baru' : 'Halaman ' . esc($page->sequence) . ' · ' . esc($page->title_id) ?></b>
        <?php if (! $isNew): ?>
          <span class="chip"><?= icon('image') ?> <?= count($pageMedia) ?> media</span>
          <?php if (! $page->is_active): ?><span class="badge is-inactive">nonaktif</span><?php endif ?>
        <?php endif ?>
      </summary>

      <?php if (! $isNew): ?>
        <input type="hidden" name="pages[<?= $index ?>][id]" value="<?= esc($page->id, 'attr') ?>">
      <?php endif ?>

      <div class="form-grid">
        <div class="field">
          <label for="<?= $p ?>-seq">Urutan halaman</label>
          <input type="number" id="<?= $p ?>-seq" name="pages[<?= $index ?>][sequence]" min="1" max="999" value="<?= esc($sequence, 'attr') ?>">
        </div>
      </div>

      <div class="bilingual">
        <span class="bilingual-label">Judul<?php if (! $isNew): ?> <span class="req">*</span><?php endif ?></span>
        <div class="field">
          <label for="<?= $p ?>-title"><span class="lang-tag">ID</span> Indonesia</label>
          <input type="text" id="<?= $p ?>-title" name="pages[<?= $index ?>][title_id]" maxlength="250" <?= $isNew ? '' : 'required' ?> value="<?= esc($isNew ? '' : ($page->title_id ?? ''), 'attr') ?>">
        </div>
        <div class="field">
          <label for="<?= $p ?>-title-en"><span class="lang-tag">EN</span> English</label>
          <input type="text" id="<?= $p ?>-title-en" name="pages[<?= $index ?>][title_en]" maxlength="250" <?= $isNew ? '' : 'required' ?> value="<?= esc($isNew ? '' : ($page->title_en ?? ''), 'attr') ?>">
        </div>
      </div>

      <div class="bilingual">
        <span class="bilingual-label">Isi halaman</span>
        <div class="field">
          <label for="<?= $p ?>-body"><span class="lang-tag">ID</span> Indonesia</label>
          <textarea id="<?= $p ?>-body" name="pages[<?= $index ?>][body_id]" rows="12"><?= esc($isNew ? '' : ($page->body_id ?? '')) ?></textarea>
        </div>
        <div class="field">
          <label for="<?= $p ?>-body-en"><span class="lang-tag">EN</span> English</label>
          <textarea id="<?= $p ?>-body-en" name="pages[<?= $index ?>][body_en]" rows="12"><?= esc($isNew ? '' : ($page->body_en ?? '')) ?></textarea>
        </div>
        <?php if ($isNew): ?>
          <p class="field-help">Biarkan judul Indonesia kosong bila tidak menambah halaman. Bila diisi, judul English juga wajib.</p>
        <?php endif ?>
      </div>

      <fieldset class="form-section">
        <legend class="panel-subtitle"><?= icon('image') ?> Galeri halaman — gambar & video</legend>
        <ol class="library-media-list">
          <?php foreach ($mediaRows as $m => $item): ?>
            <?php
            $isNewMedia = $item === null;
            $q          = $p . '-m' . $m;
            $base       = 'pages[' . $index . '][media][' . $m . ']';
            $source     = $isNewMedia ? 'upload' : ($item->external_url !== null && $item->external_url !== '' ? 'url' : 'upload');
            $kind       = $isNewMedia ? 'image' : (string) $item->media_kind;
            $link       = ! $isNewMedia && $source === 'url' ? App\Libraries\MediaLink::parse((string) $item->external_url, $kind) : null;
            ?>
            <li class="library-media-row<?= $isNewMedia ? ' is-new' : '' ?>">
              <?php if (! $isNewMedia): ?>
                <input type="hidden" name="<?= $base ?>[id]" value="<?= esc($item->id, 'attr') ?>">
              <?php endif ?>
              <div class="library-media-head">
                <b><?= $isNewMedia ? 'Media baru' : 'Media ' . esc($item->sequence) ?></b>
                <div class="field">
                  <label for="<?= $q ?>-seq" class="visually-hidden">Urutan media</label>
                  <input type="number" id="<?= $q ?>-seq" name="<?= $base ?>[sequence]" min="1" max="999" value="<?= esc($isNewMedia ? $nextMedia + $m - count($pageMedia) : $item->sequence, 'attr') ?>" style="width: 5.5em" title="Urutan media">
                </div>
                <div class="field">
                  <label for="<?= $q ?>-kind" class="visually-hidden">Jenis media</label>
                  <select id="<?= $q ?>-kind" name="<?= $base ?>[media_kind]">
                    <option value="image" <?= $kind === 'image' ? 'selected' : '' ?>>Gambar</option>
                    <option value="video" <?= $kind === 'video' ? 'selected' : '' ?>>Video</option>
                  </select>
                </div>
                <fieldset class="source-switch">
                  <legend class="visually-hidden">Sumber media</legend>
                  <label class="check"><input type="radio" name="<?= $base ?>[source]" value="upload" <?= $source === 'upload' ? 'checked' : '' ?>> <?= icon('upload') ?> Berkas</label>
                  <label class="check"><input type="radio" name="<?= $base ?>[source]" value="url" <?= $source === 'url' ? 'checked' : '' ?>> <?= icon('link') ?> Tautan</label>
                </fieldset>
                <?php if (! $isNewMedia): ?>
                  <input type="hidden" name="<?= $base ?>[is_active]" value="0">
                  <label class="check"><input type="checkbox" name="<?= $base ?>[is_active]" value="1" <?= $item->is_active ? 'checked' : '' ?>> Tampil</label>
                  <label class="check"><input type="checkbox" name="<?= $base ?>[_delete]" value="1"> <?= icon('trash') ?> Hapus</label>
                <?php endif ?>
              </div>

              <div class="source-upload">
                <?= component('components/media-field', [
                    'id'         => $q . '-file',
                    'label'      => 'Berkas gambar/video',
                    'keyName'    => $base . '[media_key]',
                    'fileName'   => 'library_file[' . $index . '][' . $m . ']',
                    'mediaId'    => $isNewMedia ? null : $item->media_asset_id,
                    'defaultKey' => 'library.' . App\Libraries\MediaStore::slug((string) $level->code) . '.p' . $sequence . '.' . ($m + 1),
                    'types'      => ['image', 'video'],
                    'size'       => 'gambar 960 × 640 px; video MP4/WebM maks. 64 MB',
                    'help'       => 'Jenis berkas harus sama dengan pilihan Gambar/Video di atas.',
                ]) ?>
              </div>

              <div class="source-url field">
                <label for="<?= $q ?>-url">Tautan media</label>
                <input type="url" id="<?= $q ?>-url" name="<?= $base ?>[external_url]" maxlength="1000" spellcheck="false"
                       placeholder="https://youtu.be/… · https://drive.google.com/file/d/… · https://commons.wikimedia.org/wiki/File:…"
                       value="<?= esc($isNewMedia ? '' : ($item->external_url ?? ''), 'attr') ?>">
                <?php if ($link !== null): ?>
                  <span class="link-preview"><?= icon($link['kind'] === 'video' ? 'video' : 'image') ?> dikenali sebagai <?= esc($link['label']) ?> · <?= esc($link['kind'] === 'video' ? 'video' : 'gambar') ?><?= $link['provider'] === 'link' ? ' (tampil sebagai tautan)' : '' ?></span>
                <?php elseif (! $isNewMedia && $source === 'url'): ?>
                  <span class="field-error">Tautan tidak sah — perbaiki atau hapus baris ini.</span>
                <?php endif ?>
              </div>

              <div class="poster-field">
                <?= component('components/media-field', [
                    'id'         => $q . '-poster',
                    'label'      => 'Poster video (opsional, untuk berkas video)',
                    'keyName'    => $base . '[poster_key]',
                    'fileName'   => 'library_poster[' . $index . '][' . $m . ']',
                    'mediaId'    => $isNewMedia ? null : $item->poster_media_id,
                    'defaultKey' => 'library.' . App\Libraries\MediaStore::slug((string) $level->code) . '.p' . $sequence . '.' . ($m + 1) . '.poster',
                    'size'       => '960 × 540 px',
                ]) ?>
              </div>

              <div class="bilingual">
                <div class="field">
                  <label for="<?= $q ?>-cap"><span class="lang-tag">ID</span> Keterangan gambar</label>
                  <input type="text" id="<?= $q ?>-cap" name="<?= $base ?>[caption_id]" maxlength="500" value="<?= esc($isNewMedia ? '' : ($item->caption_id ?? ''), 'attr') ?>">
                </div>
                <div class="field">
                  <label for="<?= $q ?>-cap-en"><span class="lang-tag">EN</span> Caption</label>
                  <input type="text" id="<?= $q ?>-cap-en" name="<?= $base ?>[caption_en]" maxlength="500" value="<?= esc($isNewMedia ? '' : ($item->caption_en ?? ''), 'attr') ?>">
                </div>
              </div>
              <div class="field">
                <label for="<?= $q ?>-credit">Kredit / lisensi</label>
                <input type="text" id="<?= $q ?>-credit" name="<?= $base ?>[credit]" maxlength="300" placeholder="mis. Foto: Nama Fotografer, CC BY-SA 4.0 via Wikimedia Commons" value="<?= esc($isNewMedia ? '' : ($item->credit ?? ''), 'attr') ?>">
              </div>
            </li>
          <?php endforeach ?>
        </ol>
        <p class="field-help">Baris "Media baru" yang dibiarkan kosong diabaikan. Butuh lebih banyak baris? Simpan dulu — dua baris kosong baru selalu tersedia.</p>
      </fieldset>

      <div class="check-row">
        <label class="check"><input type="checkbox" name="pages[<?= $index ?>][is_active]" value="1" <?= $isNew || $page->is_active ? 'checked' : '' ?>> Tampil di permainan</label>
        <?php if (! $isNew): ?>
          <label class="check"><input type="checkbox" name="pages[<?= $index ?>][_delete]" value="1"> <?= icon('trash') ?> Hapus halaman ini beserta medianya</label>
        <?php endif ?>
      </div>
    </details>
  <?php endforeach ?>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan pustaka</button>
  </div>
</form>
<?= $this->endSection() ?>
