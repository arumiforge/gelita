/**
 * Sidebar groups (components/admin-sidebar.php) are <details> elements that
 * fold without JavaScript. This module only remembers, per browser, which
 * groups the reader folded or unfolded, so the menu stays the way they left
 * it. The group holding the open page always stays open.
 */
const KEY = 'gelita.admin.sidebar';

function readState() {
  try {
    return JSON.parse(localStorage.getItem(KEY) || '{}') || {};
  } catch {
    return {};
  }
}

function writeState(state) {
  try {
    localStorage.setItem(KEY, JSON.stringify(state));
  } catch {
    // Private window or blocked storage: the menu simply starts as the server drew it
  }
}

export function initSidebar(root = document) {
  const state = readState();

  for (const group of root.querySelectorAll('details.sidebar-group[data-group]')) {
    const name = group.dataset.group;
    const holdsCurrent = group.querySelector('[aria-current]') !== null;

    if (!holdsCurrent && typeof state[name] === 'boolean') {
      group.open = state[name];
    }

    group.addEventListener('toggle', () => {
      const next = readState();
      next[name] = group.open;
      writeState(next);
    });
  }
}

/**
 * A link to an anchor inside a folded <details> (the staff guide's chapters)
 * opens that <details> first. Newer browsers do this themselves; this covers
 * the rest.
 */
export function revealTarget() {
  const open = () => {
    const id = decodeURIComponent(location.hash.slice(1));
    const target = id ? document.getElementById(id) : null;
    if (!target) return;
    let node = target;
    let opened = false;
    while (node) {
      if (node.tagName === 'DETAILS' && !node.open) {
        node.open = true;
        opened = true;
      }
      node = node.parentElement;
    }
    if (opened) target.scrollIntoView();
  };
  window.addEventListener('hashchange', open);
  open();
}
