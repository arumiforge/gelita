<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php /** Engine puzzle — Susun potongan. Mesin arena dibuat tahap 6. */ ?>
<?= $this->include('game/challenge/arena') ?>
<?= $this->endSection() ?>
