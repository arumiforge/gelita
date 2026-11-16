<?php
/**
 * Glossary popovers of one guide page: short meaning, a concrete example,
 * and a link to the guide page that explains the term (or to its entry on
 * the glossary page). Opened by the dotted term buttons in the text
 * (popovertarget, native popover: Esc and a click outside close it);
 * js/admin/guide.js places each one next to the term that opened it.
 *
 * @var list<string>               $terms    term keys, StaffGuideContent::termsIn()
 * @var list<array<string, mixed>> $chapters
 * @var string                $query
 */
use App\Libraries\StaffGuideContent as Guide;

$all    = Guide::terms();
$titles = [];

foreach ($chapters as $chapter) {
    foreach ($chapter['sections'] as $s) {
        $titles[$s['id']] = $s['title'];
    }
}
?>
<?php foreach ($terms as $key): ?>
  <?php
    $term = $all[$key];
    $href = $term['more'] !== null ? Guide::url($term['more']) . $query : Guide::url('istilah') . $query . '#istilah-' . $key;
    $more = $titles[$term['more'] ?? 'istilah'] ?? '';
    $id   = 'istilah-pop-' . $key;
  ?>
  <div class="term-pop" id="<?= esc($id, 'attr') ?>" popover role="dialog" aria-labelledby="<?= esc($id, 'attr') ?>-t">
    <div class="term-pop-head">
      <p class="term-pop-title" id="<?= esc($id, 'attr') ?>-t"><?= esc($term['term']) ?></p>
      <button type="button" class="term-pop-close" popovertarget="<?= esc($id, 'attr') ?>" popovertargetaction="hide" aria-label="<?= esc(lang('Admin.guideTermClose'), 'attr') ?>"><?= icon('cross') ?></button>
    </div>
    <p class="term-pop-def"><?= Guide::inline($term['def'], $query) ?></p>
    <p class="term-pop-example"><span><?= esc(lang('Admin.guideExample')) ?></span> <?= Guide::inline($term['example'], $query) ?></p>
    <a class="term-pop-more" href="<?= esc($href, 'attr') ?>"><?= esc(lang('Admin.guideTermMore', [$more])) ?> <?= icon('arrow-right') ?></a>
  </div>
<?php endforeach ?>
