/**
 * Adapter ECharts — seluruh chart panel dibuat lewat renderChart(), sehingga
 * query analitik tidak pernah terikat pada library chart.
 *
 *   renderChart(el, type, data, options)
 *   type: line | bar | stacked-bar | heatmap | matrix | scatter | gauge
 *
 * Bentuk `data` dibuat server (App\Libraries\ChartData): API admin
 * mengembalikannya sebagai kunci `chart`; chart tanpa endpoint membawanya di
 * <script type="application/json" class="chart-data">.
 *
 * Setiap chart punya kondisi kosong ("Belum ada data untuk filter ini"),
 * aria-label + tabel data alternatif yang tersembunyi secara visual tetapi
 * terbaca pembaca layar, dan ikut berubah ukuran bersama wadahnya.
 * Tema GELITA dibaca sekali dari token CSS (getComputedStyle).
 *
 * Bila ECharts gagal dimuat, visual cadangan dari server tetap di tempatnya.
 */
import { apiRequest } from '../core/api.js';
import { el } from '../core/dom.js';

const instances = new Map(); // host → { chart, observer }
let theme = null;

function tokens() {
  if (theme) return theme;
  const style = getComputedStyle(document.documentElement);
  const read = (name, fallback) => style.getPropertyValue(name).trim() || fallback;
  theme = {
    gold: read('--gold-500', '#DFC087'),
    goldLight: read('--gold-300', '#F0DFB8'),
    goldDark: read('--gold-600', '#C7A265'),
    ok: read('--ok', '#8FD14F'),
    bad: read('--bad', '#D9704A'),
    warn: read('--warn', '#F0A868'),
    ink: read('--ink', '#EAF0FA'),
    inkDim: read('--ink-dim', 'rgba(234,240,250,.72)'),
    surface: read('--navy-800', '#121C2E'),
    line: read('--line', 'rgba(223,192,135,.22)'),
    font: read('--font-body', 'system-ui, sans-serif'),
    mono: read('--font-mono', 'monospace'),
  };
  theme.palette = [theme.gold, theme.ok, theme.warn, theme.goldLight, theme.bad];
  return theme;
}

const hasEcharts = () => typeof window.echarts?.init === 'function';

const fmt = (value, digits = 1) => (value === null || value === undefined || Number.isNaN(Number(value))
  ? '—'
  : Number(value).toLocaleString('id-ID', { maximumFractionDigits: digits }));

// ---------------------------------------------------------------- kosong?

function isEmpty(type, data) {
  if (!data) return true;
  switch (type) {
    case 'heatmap':
    case 'matrix':
      return !Array.isArray(data.values) || !data.values.some((cell) => cell[2] !== null && cell[2] !== undefined);
    case 'scatter':
      return !Array.isArray(data.points) || data.points.length === 0;
    case 'gauge':
      return data.value === null || data.value === undefined;
    default:
      return !Array.isArray(data.series) || !data.series.length
        || !data.series.some((s) => Array.isArray(s.values) && s.values.some((v) => v !== null && v !== undefined));
  }
}

// ------------------------------------------------------------- opsi chart

function baseOption(t) {
  return {
    color: t.palette,
    textStyle: { fontFamily: t.font, color: t.ink },
    grid: { left: 8, right: 16, top: 36, bottom: 8, containLabel: true },
    tooltip: {
      backgroundColor: t.surface,
      borderColor: t.goldDark,
      textStyle: { color: t.ink, fontFamily: t.font },
      confine: true,
    },
    animationDuration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 400,
  };
}

const axisStyle = (t) => ({
  axisLine: { lineStyle: { color: t.line } },
  axisLabel: { color: t.inkDim, fontFamily: t.font },
  splitLine: { lineStyle: { color: t.line, type: 'dashed' } },
  nameTextStyle: { color: t.inkDim },
});

function seriesOption(type, data, t) {
  const kind = type === 'line' ? 'line' : 'bar';
  const option = baseOption(t);
  option.tooltip.trigger = 'axis';
  option.tooltip.valueFormatter = (v) => fmt(v, 2);
  if (data.series.length > 1) {
    option.legend = { top: 0, textStyle: { color: t.inkDim } };
  } else {
    option.grid.top = 16;
  }
  option.xAxis = { type: 'category', data: data.labels, ...axisStyle(t), axisLabel: { ...axisStyle(t).axisLabel, interval: 0, hideOverlap: true } };
  option.yAxis = { type: 'value', ...axisStyle(t), ...(data.max ? { max: data.max } : {}), ...(data.integer ? { minInterval: 1 } : {}), min: 0 };
  option.series = data.series.map((s) => ({
    name: s.name,
    type: kind,
    data: s.values,
    ...(type === 'stacked-bar' ? { stack: 'total' } : {}),
    ...(kind === 'bar' ? { barMaxWidth: 44, itemStyle: { borderRadius: [4, 4, 0, 0] } } : { symbolSize: 9, lineStyle: { width: 3 } }),
    label: { show: data.labels.length <= 8, position: 'top', color: t.ink, formatter: ({ value }) => fmt(value) },
  }));
  return option;
}

function gridOption(type, data, t) {
  const labels = new Map((data.labels || []).map(([x, y, text]) => [`${x},${y}`, text]));
  const ratio = Boolean(data.ratio);
  const option = baseOption(t);
  option.grid = { left: 8, right: 16, top: 8, bottom: 48, containLabel: true };
  option.tooltip.trigger = 'item';
  option.tooltip.formatter = (params) => {
    const [x, y, v] = params.value;
    const head = `${data.y[y]} · ${data.x[x]}`;
    const body = labels.get(`${x},${y}`) || (v === null ? '—' : fmt(ratio ? v * 100 : v));
    return `${escapeHtml(head)}<br>${escapeHtml(body)}`;
  };
  option.xAxis = { type: 'category', data: data.x, ...axisStyle(t), splitArea: { show: false } };
  option.yAxis = { type: 'category', data: data.y, inverse: true, ...axisStyle(t) };
  option.visualMap = {
    min: 0,
    max: data.max ?? (ratio ? 1 : 100),
    calculable: false,
    orient: 'horizontal',
    left: 'center',
    bottom: 0,
    itemHeight: 120,
    text: [data.high || '', data.low || ''],
    textStyle: { color: t.inkDim },
    // heatmap: merah = sulit; matriks: hijau = dikuasai
    inRange: { color: type === 'matrix' ? [t.bad, t.warn, t.ok] : ['rgba(217,112,74,.12)', t.bad] },
  };
  option.series = [{
    type: 'heatmap',
    data: data.values.map(([x, y, v]) => [x, y, v]),
    label: {
      show: true,
      color: t.ink,
      fontFamily: t.mono,
      formatter: ({ value }) => (value[2] === null || value[2] === undefined ? '—' : (ratio ? `${Math.round(value[2] * 100)}%` : fmt(value[2], 0))),
    },
    itemStyle: { borderColor: t.surface, borderWidth: 3, borderRadius: 6 },
    emphasis: { itemStyle: { borderColor: t.gold } },
  }];
  return option;
}

function scatterOption(data, t) {
  const option = baseOption(t);
  option.grid.top = 24;
  option.tooltip.trigger = 'item';
  option.tooltip.formatter = (params) => {
    const point = data.points[params.dataIndex] || {};
    return `${escapeHtml(point.label || '')}<br>${escapeHtml(data.x_name || 'x')}: ${fmt(point.x)}<br>${escapeHtml(data.y_name || 'y')}: ${fmt(point.y)}`;
  };
  option.xAxis = { type: 'value', name: data.x_name, nameLocation: 'middle', nameGap: 28, ...axisStyle(t) };
  // ruang untuk nama sumbu (containLabel hanya menghitung label angka)
  option.grid.left = 30;
  option.grid.bottom = 34;
  option.yAxis = { type: 'value', name: data.y_name, nameLocation: 'middle', nameRotate: 90, nameGap: 40, min: 0, max: 100, ...axisStyle(t) };
  option.series = [{ type: 'scatter', symbolSize: 12, data: data.points.map((p) => [p.x, p.y]), itemStyle: { color: t.gold, opacity: 0.85 } }];
  return option;
}

function gaugeOption(data, t) {
  const option = baseOption(t);
  option.series = [{
    type: 'gauge',
    min: 0,
    max: data.max ?? 100,
    progress: { show: true, width: 14, itemStyle: { color: t.gold } },
    axisLine: { lineStyle: { width: 14, color: [[1, t.line]] } },
    axisTick: { show: false },
    splitLine: { show: false },
    axisLabel: { color: t.inkDim },
    pointer: { show: false },
    detail: { valueAnimation: true, color: t.goldLight, fontFamily: t.mono, formatter: (v) => `${fmt(v)}${data.unit || ''}` },
    data: [{ value: data.value }],
  }];
  return option;
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
}

// --------------------------------------------------- tabel untuk pembaca layar

function accessibleTable(type, data, caption) {
  const table = el('table', { class: 'visually-hidden chart-table' }, el('caption', {}, caption));
  const row = (cells, header = false) => el('tr', {}, cells.map((c, i) => el(header || i === 0 ? 'th' : 'td', header ? { scope: 'col' } : (i === 0 ? { scope: 'row' } : {}), c)));

  if (type === 'heatmap' || type === 'matrix') {
    const map = new Map(data.values.map(([x, y, v]) => [`${x},${y}`, v]));
    const labels = new Map((data.labels || []).map(([x, y, text]) => [`${x},${y}`, text]));
    table.append(el('thead', {}, row(['', ...data.x], true)));
    table.append(el('tbody', {}, data.y.map((name, y) => row([name, ...data.x.map((_, x) => labels.get(`${x},${y}`) || fmt(map.get(`${x},${y}`)))]))));
  } else if (type === 'scatter') {
    table.append(el('thead', {}, row(['', data.x_name || 'x', data.y_name || 'y'], true)));
    table.append(el('tbody', {}, data.points.map((p) => row([p.label || '', fmt(p.x), fmt(p.y)]))));
  } else if (type === 'gauge') {
    table.append(el('tbody', {}, row([caption, fmt(data.value)])));
  } else {
    table.append(el('thead', {}, row(['', ...data.series.map((s) => s.name)], true)));
    table.append(el('tbody', {}, data.labels.map((label, i) => row([label, ...data.series.map((s) => fmt(s.values[i], 2))]))));
  }
  return table;
}

// ------------------------------------------------------------------ render

export function disposeCharts(root = document) {
  for (const [host, entry] of instances) {
    if (root === document || root.contains(host)) {
      entry.observer?.disconnect();
      entry.chart.dispose();
      instances.delete(host);
    }
  }
}

/**
 * Gambar satu chart di dalam `el` (.chart-canvas). Mengembalikan false bila
 * ECharts tidak tersedia — visual cadangan server dibiarkan.
 */
export function renderChart(canvas, type, data, { description = '', emptyText = 'Belum ada data untuk filter ini.' } = {}) {
  if (!hasEcharts()) return false;

  disposeCharts(canvas);
  canvas.replaceChildren();
  canvas.classList.remove('is-loading');

  if (isEmpty(type, data)) {
    canvas.append(el('p', { class: 'chart-empty' }, emptyText));
    return true;
  }

  const t = tokens();
  const host = el('div', { class: 'chart-host', role: 'img', 'aria-label': description });
  canvas.append(host, accessibleTable(type, data, description));

  let option;
  if (type === 'heatmap' || type === 'matrix') option = gridOption(type, data, t);
  else if (type === 'scatter') option = scatterOption(data, t);
  else if (type === 'gauge') option = gaugeOption(data, t);
  else option = seriesOption(type, data, t);

  const chart = window.echarts.init(host, null, { renderer: 'svg' });
  chart.setOption(option);

  if (data.links) {
    chart.on('click', (params) => {
      const [x, y] = params.value || [];
      const target = data.links[`${x},${y}`];
      if (target) window.location.href = target;
    });
  }

  const observer = 'ResizeObserver' in window ? new ResizeObserver(() => chart.resize()) : null;
  observer?.observe(host);
  if (!observer) window.addEventListener('resize', () => chart.resize());
  instances.set(host, { chart, observer });
  return true;
}

/** Pasang semua .chart-canvas[data-chart] di dalam root. */
export function initCharts(root = document) {
  if (!hasEcharts()) return;

  for (const canvas of root.querySelectorAll('.chart-canvas[data-chart]')) {
    const type = canvas.dataset.chart;
    const options = { description: canvas.dataset.description || '', emptyText: canvas.dataset.empty || undefined };
    const embedded = canvas.parentElement?.querySelector('script.chart-data');

    if (embedded) {
      try {
        renderChart(canvas, type, JSON.parse(embedded.textContent), options);
      } catch {
        /* data rusak: visual cadangan tetap tampil */
      }
      continue;
    }

    if (!canvas.dataset.endpoint) continue;

    canvas.classList.add('is-loading');
    canvas.setAttribute('aria-busy', 'true');
    apiRequest(canvas.dataset.endpoint)
      .then((data) => {
        if (data && data.chart) renderChart(canvas, type, data.chart, options);
      })
      .catch(() => { /* gagal memuat: visual cadangan server tetap terbaca */ })
      .finally(() => {
        canvas.classList.remove('is-loading');
        canvas.removeAttribute('aria-busy');
      });
  }
}
