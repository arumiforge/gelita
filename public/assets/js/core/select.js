/**
 * Game dropdown (owner, 22 October 2026): a <select> becomes a gold-and-navy
 * list that opens with a short animation, the owner's gold triangle as its
 * arrow, a search box on long lists (provinces, cities), and full keyboard use.
 *
 * - The native <select> stays in the form, hidden, and keeps the value that is
 *   sent: without JavaScript, or in the panel, it is the plain select. Every
 *   pick sets its value and fires `change`, so the region filter and the school
 *   check in game/register.js keep working unchanged.
 * - The list is rebuilt whenever the select's options or `disabled` change
 *   (the city list is swapped per province), and on `change`.
 * - The open list is placed on #stage, not inside the field: the form scrolls
 *   (.stage-scroll) and would clip it. It opens downwards, or upwards when the
 *   stage has more room above.
 * - Keys: ↓/↑ move, Home/End, Enter or Space picks, Esc closes, typing filters
 *   (search box) or jumps to the first match (short lists). Touch devices do
 *   not focus the search box by themselves, so the keyboard stays down until
 *   the student taps it.
 * - A focus on the hidden select (its label, the form check's focus on the
 *   first wrong field) goes to the button instead.
 */
import { el } from './dom.js';
import { t } from './config.js';

const SEARCH_FROM = 8; // options from which the list gets a search box
let counter = 0;
let openSelect = null; // the one open list: { close }

const fold = (text) => text.normalize('NFD').replace(/\p{M}/gu, '').toLocaleLowerCase('id');

/** Text with the matched part in <mark>, built from text nodes only. */
function highlight(text, query) {
  if (!query) return [text];
  const at = fold(text).indexOf(query);
  if (at < 0) return [text];
  return [text.slice(0, at), el('mark', {}, text.slice(at, at + query.length)), text.slice(at + query.length)];
}

/**
 * @param {HTMLSelectElement} select
 * @returns {{ refresh: () => void } | null}
 */
export function enhanceSelect(select) {
  if (!select || select.dataset.fancy) return null;
  const stage = document.getElementById('stage');
  if (!stage) return null;
  select.dataset.fancy = '1';

  const id = select.id || `fancy-${++counter}`;
  const label = select.id ? document.querySelector(`label[for="${CSS.escape(select.id)}"]`) : null;
  if (label && !label.id) label.id = `${id}-label`;

  const value = el('span', { class: 'fancy-select-value', id: `${id}-value` });
  const arrow = el('span', { class: 'fancy-select-arrow', 'aria-hidden': 'true' });
  const button = el('button', {
    type: 'button',
    class: 'fancy-select-button',
    role: 'combobox',
    'aria-haspopup': 'listbox',
    'aria-expanded': 'false',
    'aria-controls': `${id}-list`,
    'aria-labelledby': [label?.id, value.id].filter(Boolean).join(' '),
  }, value, arrow);

  const search = el('input', {
    type: 'search',
    class: 'fancy-select-search',
    placeholder: t('selectSearch'),
    'aria-label': t('selectSearch'),
    'aria-controls': `${id}-list`,
    autocomplete: 'off',
    spellcheck: 'false',
    enterkeyhint: 'done',
  });
  const list = el('ul', { class: 'fancy-select-list', role: 'listbox', id: `${id}-list`, tabindex: '-1' });
  if (label) list.setAttribute('aria-labelledby', label.id);
  const empty = el('p', { class: 'fancy-select-empty', hidden: true }, t('selectNoMatch'));
  const status = el('p', { class: 'visually-hidden', 'aria-live': 'polite' });
  const pop = el('div', { class: 'fancy-select-pop', hidden: true }, search, list, empty, status);

  const wrap = el('div', { class: 'fancy-select' });
  select.before(wrap);
  wrap.append(select, button);
  select.classList.add('fancy-select-native');
  select.tabIndex = -1;
  select.setAttribute('aria-hidden', 'true');
  stage.append(pop);

  /** @type {Array<{ node: HTMLLIElement, option: HTMLOptionElement, text: string, key: string }>} */
  let items = [];
  let active = -1;

  const visible = () => items.filter((item) => !item.node.hidden);

  const setActive = (index, scroll = true) => {
    const shown = visible();
    items.forEach((item) => item.node.classList.remove('is-active'));
    if (!shown.length) {
      active = -1;
      list.removeAttribute('aria-activedescendant');
      search.removeAttribute('aria-activedescendant');
      return;
    }
    active = Math.max(0, Math.min(index, shown.length - 1));
    const node = shown[active].node;
    node.classList.add('is-active');
    list.setAttribute('aria-activedescendant', node.id);
    search.setAttribute('aria-activedescendant', node.id);
    if (scroll) node.scrollIntoView({ block: 'nearest' });
  };

  const build = () => {
    const query = fold(search.value.trim());
    const groups = select.querySelectorAll('optgroup');
    const nodes = [];
    items = [];

    for (const child of select.children) {
      const options = child.tagName === 'OPTGROUP' ? Array.from(child.children) : [child];
      if (child.tagName === 'OPTGROUP' && groups.length > 1) {
        nodes.push(el('li', { class: 'fancy-select-group', role: 'presentation' }, child.label));
      }
      for (const option of options) {
        if (option.value === '') continue; // the "— pilih —" line is the button's empty text
        const text = option.textContent.trim();
        const node = el('li', {
          class: 'fancy-select-option',
          role: 'option',
          id: `${id}-opt-${items.length}`,
          'aria-selected': option.selected ? 'true' : 'false',
        });
        if (option.disabled) node.setAttribute('aria-disabled', 'true');
        items.push({ node, option, text, key: fold(text) });
        nodes.push(node);
      }
    }
    list.replaceChildren(...nodes);

    const selected = select.selectedOptions[0];
    value.textContent = selected ? selected.textContent.trim() : '';
    wrap.classList.toggle('is-placeholder', !selected || selected.value === '');
    button.disabled = select.disabled;
    wrap.classList.toggle('is-disabled', select.disabled);
    search.hidden = items.length < SEARCH_FROM;
    filter(query);
  };

  const filter = (query) => {
    let shown = 0;
    for (const item of items) {
      const match = !query || item.key.includes(query);
      item.node.hidden = !match;
      item.node.replaceChildren(el('span', { class: 'fancy-select-label' }, ...highlight(item.text, match ? query : '')));
      if (match) shown += 1;
    }
    empty.hidden = shown > 0;
    status.textContent = query ? (shown ? t('selectCount', shown) : t('selectNoMatch')) : '';
    const current = visible().findIndex((item) => item.option.selected);
    setActive(current >= 0 ? current : 0, false);
  };

  const place = () => {
    const box = button.getBoundingClientRect();
    const frame = stage.getBoundingClientRect();
    const rem = parseFloat(getComputedStyle(document.documentElement).fontSize) || 16;
    const below = frame.bottom - box.bottom - rem;
    const above = box.top - frame.top - 4 * rem; // keep clear of the HUD
    const up = below < 14 * rem && above > below;
    pop.classList.toggle('is-up', up);
    pop.style.left = `${box.left - frame.left}px`;
    pop.style.width = `${Math.max(box.width, 16 * rem)}px`;
    pop.style.maxHeight = `${Math.max(10 * rem, Math.min(22 * rem, up ? above : below))}px`;
    if (up) {
      pop.style.top = '';
      pop.style.bottom = `${frame.bottom - box.top + 0.375 * rem}px`;
    } else {
      pop.style.bottom = '';
      pop.style.top = `${box.bottom - frame.top + 0.375 * rem}px`;
    }
  };

  const onOutside = (event) => {
    if (!wrap.contains(event.target) && !pop.contains(event.target)) close(false);
  };
  const onMove = () => place();

  function close(focus = true) {
    if (pop.hidden) return;
    pop.hidden = true;
    wrap.classList.remove('is-open');
    button.setAttribute('aria-expanded', 'false');
    document.removeEventListener('pointerdown', onOutside, true);
    window.removeEventListener('resize', onMove);
    wrap.closest('.stage-scroll')?.removeEventListener('scroll', onMove);
    if (openSelect?.close === close) openSelect = null;
    if (focus) button.focus({ preventScroll: true });
  }

  const open = () => {
    if (select.disabled || !pop.hidden) return;
    openSelect?.close(false);
    search.value = '';
    build();
    pop.hidden = false;
    wrap.classList.add('is-open');
    button.setAttribute('aria-expanded', 'true');
    place();
    openSelect = { close };
    document.addEventListener('pointerdown', onOutside, true);
    window.addEventListener('resize', onMove);
    wrap.closest('.stage-scroll')?.addEventListener('scroll', onMove, { passive: true });

    const coarse = window.matchMedia('(pointer: coarse)').matches;
    if (!search.hidden && !coarse) search.focus({ preventScroll: true });
    else list.focus({ preventScroll: true });
    const shown = visible();
    const current = shown.findIndex((item) => item.option.selected);
    setActive(current >= 0 ? current : 0);
  };

  const pick = (item) => {
    if (!item || item.option.disabled) return;
    const changed = select.value !== item.option.value;
    select.value = item.option.value;
    close();
    build();
    if (changed) {
      select.dispatchEvent(new Event('input', { bubbles: true }));
      select.dispatchEvent(new Event('change', { bubbles: true }));
    }
  };

  let typed = '';
  let typedAt = 0;

  const onKey = (event) => {
    const shown = visible();
    switch (event.key) {
      case 'ArrowDown': event.preventDefault(); setActive(active + 1); return;
      case 'ArrowUp': event.preventDefault(); setActive(active - 1); return;
      case 'PageDown': event.preventDefault(); setActive(active + 8); return;
      case 'PageUp': event.preventDefault(); setActive(active - 8); return;
      case 'Home': if (event.target === search) return; event.preventDefault(); setActive(0); return;
      case 'End': if (event.target === search) return; event.preventDefault(); setActive(shown.length - 1); return;
      case 'Enter': event.preventDefault(); pick(shown[active]); return;
      case 'Escape': event.preventDefault(); event.stopPropagation(); close(); return;
      case 'Tab': close(false); return;
      case ' ':
        if (event.target === search) return;
        event.preventDefault();
        pick(shown[active]);
        return;
      default:
        break;
    }
    // Short lists: jump to the first option that starts with the letters typed
    if (event.target === list && event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
      const now = Date.now();
      typed = now - typedAt > 700 ? event.key : typed + event.key;
      typedAt = now;
      const at = shown.findIndex((item) => item.key.startsWith(fold(typed)));
      if (at >= 0) setActive(at);
    }
  };

  button.addEventListener('click', () => (pop.hidden ? open() : close()));
  button.addEventListener('keydown', (event) => {
    if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(event.key)) {
      event.preventDefault();
      open();
    }
  });
  list.addEventListener('keydown', onKey);
  search.addEventListener('keydown', onKey);
  search.addEventListener('input', () => filter(fold(search.value.trim())));
  list.addEventListener('click', (event) => {
    const node = event.target.closest('.fancy-select-option');
    pick(items.find((item) => item.node === node));
  });
  list.addEventListener('pointermove', (event) => {
    const node = event.target.closest('.fancy-select-option');
    const at = visible().findIndex((item) => item.node === node);
    if (at >= 0 && at !== active) setActive(at, false);
  });

  // The label, the form check, or a script focusing the hidden select: the button takes it
  select.addEventListener('focus', () => button.focus());
  select.addEventListener('change', build);
  new MutationObserver(build).observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });

  build();
  return { refresh: build };
}

/** Every `select[data-fancy-select]` under `root`. */
export function enhanceSelects(root = document) {
  root.querySelectorAll('select[data-fancy-select]').forEach((select) => enhanceSelect(select));
}
