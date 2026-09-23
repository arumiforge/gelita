<?php
/**
 * Gaya bersama laporan PDF. mPDF hanya memahami sebagian CSS: tabel,
 * warna, border, dan lebar blok — tanpa flex/grid atau variabel CSS.
 */
?>
<style>
  body { font-family: dejavusans, sans-serif; font-size: 9.5pt; color: #2b2520; line-height: 1.45; }
  h1 { font-size: 20pt; color: #5a3a1e; margin: 0 0 4mm; }
  h2 { font-size: 13pt; color: #5a3a1e; border-bottom: 0.4mm solid #d9cbb5; padding-bottom: 1.5mm; margin: 8mm 0 3mm; }
  h3 { font-size: 10.5pt; color: #3d2c1d; margin: 5mm 0 2mm; }
  p { margin: 0 0 2.5mm; }
  .muted { color: #6f6458; }
  .small { font-size: 8pt; }
  .cover { padding-top: 30mm; }
  .cover .eyebrow { font-size: 9pt; letter-spacing: 1pt; color: #8a6a44; text-transform: uppercase; }
  .cover table.meta { margin-top: 10mm; width: 100%; }
  table { border-collapse: collapse; width: 100%; }
  table.data th, table.data td { border-bottom: 0.2mm solid #e4d9c8; padding: 1.6mm 2mm; text-align: left; vertical-align: top; }
  table.data th { background: #f1e9dc; font-weight: bold; color: #3d2c1d; }
  table.data td.num, table.data th.num { text-align: right; }
  table.data.compact th, table.data.compact td { font-size: 8pt; padding: 1.4mm 1.2mm; }
  table.meta td { padding: 1.2mm 0; vertical-align: top; }
  table.meta td.key { width: 42mm; color: #6f6458; }
  table.kpi td { width: 33%; padding: 2.5mm; border: 0.2mm solid #e4d9c8; vertical-align: top; }
  .kpi-value { font-size: 15pt; font-weight: bold; color: #5a3a1e; }
  .kpi-label { font-size: 8pt; color: #6f6458; }
  table.bars td { padding: 1.2mm 1mm; vertical-align: middle; }
  table.bars td.label { width: 52mm; }
  table.bars td.value { width: 20mm; text-align: right; }
  .badge-bad { color: #9c2f22; font-weight: bold; }
  .note { background: #f7f2ea; border-left: 1mm solid #d9cbb5; padding: 2.5mm 3mm; margin: 3mm 0; }
  .pdf-header { font-size: 7.5pt; color: #8a7d6d; text-align: right; border-bottom: 0.2mm solid #e4d9c8; padding-bottom: 1mm; }
  table.pdf-footer td { font-size: 7.5pt; color: #8a7d6d; }
  table.pdf-footer td.right { text-align: right; }
</style>
