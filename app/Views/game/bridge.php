<?php
/**
 * Bridge story — `/penghubung/{code}` → DialogueController::bridge (research
 * revision 10, part 6: 32 §7.2; text in 12 → *Cerita penghubung*).
 *
 * The six lines of `scenario_open` (*Kabut di Layar*) on the same narrated
 * stage as the region dialogue (partials/dialogue-scene, game/narrator.js,
 * the region's `dialog-` music). It plays once per student per study before
 * the mission brief of the Skenario Digital challenge (scenarioGate()); the
 * last line's button opens that brief (`/misi/{code}/{n}`). Lewati goes to
 * the same brief.
 *
 * game/narrator.js sends `dialogue_completed` { context: scenario_open } on
 * the last slide (29 §5.3).
 *
 * @var App\Entities\Level         $level
 * @var App\Entities\ChallengeNode $node   the region's Skenario Digital challenge
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 */
$bg     = media_exists($level->background_media_id) ? media_src($level->background_media_id) : '';
$region = $level->text('name', $locale);
$brief  = base_url('misi/' . $level->code . '/' . $node->sequence);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?><?= esc('dialog-' . $level->code) ?><?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.bridgeStoryTitle')) ?> · <?= esc($region) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-cinematic is-region-dialogue<?= $this->endSection() ?>
<?= $this->section('hudStyle') ?>menu<?= $this->endSection() ?>
<?= $this->section('hudBadge') ?><?= component('partials/region-difficulty', ['level' => $level]) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= component('partials/dialogue-scene', [
    'level'      => $level,
    'slides'     => $slides,
    'locale'     => $locale,
    'context'    => 'scenario_open',
    'screen'     => 'dialogue',
    'cinematic'  => true,
    'nextAssets' => [media_exists($node->background_media_id) ? media_src($node->background_media_id) : null],
    'eyebrow'    => lang('Game.challengeOf', [$node->sequence, count(service('contentRepository')->nodesForLevel($level->id))]),
    'tap'        => [
        'variant' => 'chapter',
        'trail'   => false,
        'eyebrow' => $region,
        'title'   => lang('Game.bridgeStoryTitle'),
        'label'   => lang('Game.chapterTap'),
    ],
    'final' => [
        'url'   => $brief,
        'label' => lang('Game.enterScenario'),
        'plate' => 'enter',
    ],
]) ?>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.skip'), 'href' => $brief, 'style' => 'quiet', 'arrow' => 'right'],
]]) ?>
<?= $this->endSection() ?>
