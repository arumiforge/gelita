<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.reflection')) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="reflection" data-screen="reflection">
  <h1><?= esc(lang('Game.reflection')) ?></h1>
  <?= $this->include('partials/flash') ?>

  <?php if ($existing !== null): ?>
    <p class="alert alert-info" role="status">Kamu sudah mengirim masukan. Boleh mengirim lagi bila ada tambahan.</p>
  <?php endif ?>

  <form method="post" action="<?= base_url('refleksi') ?>" class="form">
    <?= csrf_field() ?>

    <fieldset class="field">
      <legend>Seberapa senang kamu bermain GELITA?</legend>
      <?php for ($star = 1; $star <= 5; $star++): ?>
        <label class="check">
          <input type="radio" name="rating" value="<?= $star ?>" required
                 <?= (int) old('rating') === $star ? 'checked' : '' ?>>
          <?= $star ?>
        </label>
      <?php endfor ?>
    </fieldset>

    <?php
    $questions = [
        'liked_most'   => 'Bagian apa yang paling kamu sukai?',
        'hardest_part' => 'Bagian apa yang paling sulit?',
        'new_learning' => 'Hal baru apa yang kamu pelajari?',
        'suggestion'   => 'Saranmu untuk permainan ini?',
    ];
    ?>
    <?php foreach ($questions as $name => $label): ?>
      <div class="field">
        <label for="<?= esc($name) ?>"><?= esc($label) ?></label>
        <textarea id="<?= esc($name) ?>" name="<?= esc($name) ?>" rows="3" maxlength="2000"><?= esc(old($name)) ?></textarea>
      </div>
    <?php endforeach ?>

    <p><small>Isi minimal dua pertanyaan, ya.</small></p>
    <button class="btn btn-primary btn-lg" type="submit">Kirim masukan</button>
  </form>

  <a class="btn btn-quiet" href="<?= base_url('peta') ?>"><?= esc(lang('Game.back')) ?></a>
</section>
<?= $this->endSection() ?>
