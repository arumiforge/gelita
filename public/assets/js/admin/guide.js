/**
 * Staff guide (layouts/guide): the menu's search box, the menu button on
 * narrow screens, and placing a glossary popover next to the term that
 * opened it. The popovers themselves are native (popover attribute): they
 * open, close on Esc or a click outside, and stay usable without this file,
 * centred on the screen.
 */
const body = document.body;
const nav = document.getElementById('gd-nav');

/* ---------- Search: filter the menu's topics by title and text ---------- */
const search = document.getElementById('gd-search');
if (nav && search) {
  const empty = nav.querySelector('.gd-search-empty');
  const groups = [...nav.querySelectorAll('.gd-group')];
  const wasOpen = new Map(groups.map((g) => [g, g.open]));

  search.addEventListener('input', () => {
    const words = search.value.toLocaleLowerCase('id').split(/\s+/).filter(Boolean);
    let shown = 0;
    for (const group of groups) {
      let inGroup = 0;
      for (const link of group.querySelectorAll('a[data-search]')) {
        const text = `${link.textContent.toLocaleLowerCase('id')} ${link.dataset.search}`;
        const hit = words.every((w) => text.includes(w));
        link.parentElement.hidden = !hit;
        if (hit) inGroup += 1;
      }
      group.hidden = inGroup === 0;
      group.open = words.length > 0 ? inGroup > 0 : wasOpen.get(group);
      shown += inGroup;
    }
    if (empty) empty.hidden = shown > 0;
  });

  search.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
      const first = nav.querySelector('.gd-group:not([hidden]) li:not([hidden]) a');
      if (first) location.href = first.href;
    }
  });
}

/* ---------- Menu button (narrow screens) ---------- */
const menuButton = document.querySelector('.gd-menu-btn');
if (menuButton && nav) {
  const setOpen = (open) => {
    body.classList.toggle('is-nav-open', open);
    menuButton.setAttribute('aria-expanded', String(open));
  };
  menuButton.addEventListener('click', () => {
    const open = !body.classList.contains('is-nav-open');
    setOpen(open);
    if (open) search?.focus();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && body.classList.contains('is-nav-open')) {
      setOpen(false);
      menuButton.focus();
    }
  });
  document.addEventListener('click', (event) => {
    if (body.classList.contains('is-nav-open') && !nav.contains(event.target) && !menuButton.contains(event.target)) setOpen(false);
  });
}

/* ---------- Glossary popovers: place beside the term that opened them ---------- */
const GAP = 8;
const EDGE = 16;
let opener = null;
let placed = null;

const place = () => {
  if (!placed || !opener) return;
  const term = opener.getBoundingClientRect();
  const pop = placed.getBoundingClientRect();
  const below = term.bottom + GAP;
  const top = below + pop.height <= window.innerHeight - EDGE || term.top - GAP - pop.height < EDGE
    ? below
    : term.top - GAP - pop.height;
  const left = Math.min(Math.max(EDGE, term.left), window.innerWidth - pop.width - EDGE);
  placed.style.top = `${Math.max(EDGE, top)}px`;
  placed.style.left = `${Math.max(EDGE, left)}px`;
};

document.addEventListener('click', (event) => {
  const term = event.target.closest('.term[popovertarget]');
  if (term) opener = term;
});

for (const pop of document.querySelectorAll('.term-pop[popover]')) {
  pop.addEventListener('toggle', (event) => {
    if (event.newState === 'open') {
      pop.classList.add('is-placed');
      placed = pop;
      place();
    } else if (placed === pop) {
      placed = null;
      opener?.focus({ preventScroll: true });
    }
  });
  pop.querySelector('.term-pop-more')?.addEventListener('click', () => pop.hidePopover?.());
}

window.addEventListener('resize', place);
window.addEventListener('scroll', place, { passive: true });
