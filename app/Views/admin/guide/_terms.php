<?php
/**
 * Glossary popovers of one guide page (owner, 18 November 2026: a rich
 * popover): the term with its kind, a picture when the guide names one and
 * it is installed here, the short meaning, a concrete example, a link to the
 * guide page that explains the term (or its entry on the glossary page), and
 * a link to the asset catalogue when the term has something to look at
 * there.
 *
 * Opened by the dotted term buttons in the text (popovertarget, native
 * popover: Esc and a click outside close it). js/admin/guide.js places each
 * one next to the term that opened it, and on a mouse also opens it after a
 * short hover; a click keeps it open.
 *
 * @var list<string>               $terms    term keys, GuideContent::termsIn()
 * @var list<array<string, mixed>> $chapters
 * @var string                $query
 * @var class-string<\App\Libraries\GuideContent> $guide
 */

$all    = $guide::terms();
$titles = [];

foreach ($chapters as $chapter) {
    foreach ($chapter['sections'] as $s) {
        $titles[$s['id']] = $s['title'];
    }
}
?>
<?php foreach ($terms as $key): ?>
  <?php
    $term    = $all[$key];
    $href    = $term['more'] !== null ? $guide::url($term['more']) . $query : $guide::url('istilah') . $query . '#istilah-' . $key;
    $more    = $titles[$term['more'] ?? 'istilah'] ?? '';
    $id      = 'istilah-pop-' . $key;
    $picture = isset($term['picture']) ? $guide::pictureSrc($term['picture']) : null;
  ?>
  <div class="term-pop<?= $picture !== null ? ' has-picture' : '' ?>" id="<?= esc($id, 'attr') ?>" popover role="dialog" aria-labelledby="<?= esc($id, 'attr') ?>-t">
    <div class="term-pop-head">
      <div>
        <?php if (! empty($term['kind'])): ?><p class="term-pop-kind"><?= esc($term['kind']) ?></p><?php endif ?>
        <p class="term-pop-title" id="<?= esc($id, 'attr') ?>-t"><?= esc($term['term']) ?></p>
      </div>
      <button type="button" class="term-pop-close" popovertarget="<?= esc($id, 'attr') ?>" popovertargetaction="hide" aria-label="<?= esc(lang('Admin.guideTermClose'), 'attr') ?>"><?= icon('cross') ?></button>
    </div>
    <?php if ($picture !== null): ?>
      <img class="term-pop-picture" src="<?= esc($picture, 'attr') ?>" alt="" loading="lazy" decoding="async">
    <?php endif ?>
    <p class="term-pop-def"><?= $guide::inline($term['def'], $query) ?></p>
    <p class="term-pop-example"><span><?= esc(lang('Admin.guideExample')) ?></span> <?= $guide::inline($term['example'], $query) ?></p>
    <div class="term-pop-links">
      <a class="term-pop-more" href="<?= esc($href, 'attr') ?>"><?= esc(lang('Admin.guideTermMore', [$more])) ?> <?= icon('arrow-right') ?></a>
      <?php if (! empty($term['catalogue'])): ?>
        <a class="term-pop-more is-catalogue" href="<?= esc($guide::CATALOGUE . '#' . $term['catalogue'], 'attr') ?>" target="_blank" rel="noopener"><?= icon('image') ?> <?= esc(lang('Admin.guideTermCatalogue')) ?></a>
      <?php endif ?>
    </div>
  </div>
<?php endforeach ?>
