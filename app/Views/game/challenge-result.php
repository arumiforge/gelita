<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?>Hasil · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="challenge-result">
  <h1>Hasil · <?= esc($node->text('title', $locale)) ?></h1>

  <table class="data-table">
    <thead>
      <tr>
        <th scope="col">Percobaan</th>
        <th scope="col"><?= esc(lang('Game.score')) ?></th>
        <th scope="col">Bintang</th>
        <th scope="col"><?= esc(lang('Game.accuracyFirst')) ?></th>
        <th scope="col"><?= esc(lang('Game.timeSpent')) ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($attempts as $attempt): ?>
        <tr<?= $best !== null && $best->id === $attempt->id ? ' class="is-best"' : '' ?>>
          <td>#<?= esc($attempt->attempt_no) ?></td>
          <td><?= esc($attempt->score) ?></td>
          <td><?= stars_html((int) $attempt->stars) ?></td>
          <td><?= esc($attempt->first_pass_accuracy) ?>%</td>
          <td><?= esc(ms_to_human((int) $attempt->duration_ms)) ?></td>
        </tr>
      <?php endforeach ?>
      <?php if ($attempts === []): ?>
        <tr><td colspan="5">Belum ada percobaan yang selesai.</td></tr>
      <?php endif ?>
    </tbody>
  </table>

  <a class="btn btn-primary" href="<?= base_url('tantangan/' . $level->code . '/' . $sequence) ?>">Coba lagi</a>
  <a class="btn btn-quiet" href="<?= base_url('wilayah/' . $level->code) ?>"><?= esc(lang('Game.back')) ?></a>
</section>
<?= $this->endSection() ?>
