<?php
/**
 * Gaya bersama laporan PDF (gaya admin netral, Dok 3 §8.1/§10). mPDF hanya
 * memahami sebagian CSS: tabel, warna, border, dan lebar blok — tanpa
 * flex/grid atau variabel CSS.
 */
?>
<style>
  body { font-family: dejavusans, sans-serif; font-size: 9pt; color: #101828; line-height: 1.4; }
  h1 { font-size: 17pt; color: #101828; margin: 0 0 4mm; }
  h2 { font-size: 11.5pt; color: #101828; border-bottom: 0.3mm solid #E4E7EC; padding-bottom: 1.5mm; margin: 7mm 0 2.5mm; }
  p { margin: 0 0 2.5mm; }
  .muted { color: #667085; }
  .small { font-size: 7.5pt; }
  .eyebrow { letter-spacing: 0.6pt; text-transform: uppercase; }
  table { border-collapse: collapse; width: 100%; }
  table.data th, table.data td { border-bottom: 0.2mm solid #E4E7EC; padding: 1.4mm 1.6mm; text-align: left; vertical-align: top; }
  table.data th { background: #F9FAFB; font-weight: bold; color: #344054; }
  table.data td.num, table.data th.num { text-align: right; }
  table.data.compact th, table.data.compact td { font-size: 7.5pt; padding: 1.2mm 1.2mm; }
  table.meta td { padding: 1mm 0; vertical-align: top; }
  table.meta td.key { width: 48mm; color: #667085; }
  .pdf-header { font-size: 7pt; color: #667085; text-align: right; border-bottom: 0.2mm solid #E4E7EC; padding-bottom: 1mm; }
  table.pdf-footer td { font-size: 7pt; color: #667085; }
  table.pdf-footer td.right { text-align: right; }
</style>
