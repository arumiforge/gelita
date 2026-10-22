/**
 * Empty or invalid fields on the student forms (sign-up steps, login): the
 * check runs in the browser before anything is sent, and one friendly modal
 * names every field that still needs attention, so the page does not reload
 * just to say "this is empty" (owner request, 12 October 2026).
 *
 * - The browser's own bubbles are turned off (`noValidate`) only here, with
 *   JavaScript; without it the form keeps `required` and the server checks.
 * - The check listens in the capture phase and is added before any other
 *   submit handler (core/device.js, the double-submit guard), so an invalid
 *   form stops before those run.
 * - Fields are named by `data-field-name`, else their <label>, else their
 *   fieldset <legend>, without the `*` mark.
 * - The server still validates everything; this only saves a round trip.
 */
import { $$, scrollToCenter } from '../core/dom.js';
import { t } from '../core/config.js';
import { showModal } from '../core/modal.js';

const CONTROLS = 'input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])';

function fieldBox(control) {
  return control.closest('.field, fieldset, .check') || control.parentElement;
}

function fieldName(control) {
  const named = control.closest('[data-field-name]')?.dataset.fieldName || control.dataset.fieldName;
  if (named) return named;

  const label = control.id ? control.form?.querySelector(`label[for="${CSS.escape(control.id)}"]`) : null;
  const legend = control.closest('fieldset')?.querySelector('legend');
  const source = (control.type === 'radio' ? legend : label) || legend || control.closest('label');
  const text = source ? source.cloneNode(true) : null;
  text?.querySelectorAll('.req, .visually-hidden, input').forEach((node) => node.remove());
  return (text?.textContent || control.name || '').replace(/\s+/g, ' ').trim();
}

function joinNames(names) {
  if (names.length < 2) return names[0] ?? '';
  return `${names.slice(0, -1).join(', ')} ${t('formAnd')} ${names.at(-1)}`;
}

/**
 * The fields that are not ready yet, one entry per field (a radio group
 * counts once): `{ control, box, name, missing }`.
 */
export function invalidFields(form) {
  const seen = new Set();
  const list = [];

  for (const control of $$(CONTROLS, form)) {
    if (control.closest('[hidden]') || control.validity.valid) continue;
    const key = control.type === 'radio' ? `radio:${control.name}` : control;
    if (seen.has(key)) continue;
    seen.add(key);
    list.push({ control, box: fieldBox(control), name: fieldName(control), missing: control.validity.valueMissing });
  }

  return list;
}

/** Shows the modal for these fields and marks them; focus goes to the first one afterwards. */
export async function reportFields(fields) {
  for (const field of fields) field.box?.classList.add('has-error');

  const missing = fields.filter((field) => field.missing).map((field) => field.name).filter(Boolean);
  // A field with its own message (setCustomValidity, e.g. the full name) says it in its own words
  const explained = fields.filter((field) => !field.missing && field.control.validity.customError && field.control.validationMessage);
  const wrong = fields.filter((field) => !field.missing && !explained.includes(field)).map((field) => field.name).filter(Boolean);
  const text = [];
  if (missing.length) text.push(t('formMissingText', joinNames(missing)));
  if (wrong.length) text.push(t('formInvalidText', joinNames(wrong)));
  explained.forEach((field) => text.push(field.control.validationMessage));

  await showModal({
    type: 'info',
    icon: 'hint',
    title: missing.length ? t('formMissingTitle') : t('formInvalidTitle'),
    text,
    buttons: [{ label: missing.length ? t('formFix') : t('ok'), style: 'primary', value: 'ok' }],
  });

  const first = fields[0]?.control;
  if (first) {
    scrollToCenter(fieldBox(first) || first);
    first.focus({ preventScroll: true });
  }
}

/** Stops a submit with empty or invalid fields and explains them in a modal. Call before other submit handlers. */
export function checkBeforeSubmit(form) {
  if (!form) return;
  form.noValidate = true;

  form.addEventListener('submit', (event) => {
    if (form.dataset.submitting) return; // already on its way; the double-submit guard handles it
    const fields = invalidFields(form);
    if (!fields.length) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    reportFields(fields);
  }, true);

  const clear = (event) => {
    const control = event.target;
    if (!control.validity) return;
    const group = control.type === 'radio' ? $$(`input[name="${CSS.escape(control.name)}"]`, form) : [control];
    if (group.every((node) => node.validity.valid)) fieldBox(control)?.classList.remove('has-error');
  };
  form.addEventListener('input', clear);
  form.addEventListener('change', clear);
}
