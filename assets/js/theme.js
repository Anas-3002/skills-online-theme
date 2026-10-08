/* Skills Online — front-end behaviour.
 *
 * Everything here drives a control that exists in the Stitch design (or in the
 * theme's own mobile navigation). The design ships only two handlers
 * (accordion + pricing toggle); the rest are implemented so that no control on
 * the site is decorative.
 */
(function () {
  'use strict';

  var doc = document;
  var $ = function (sel, root) { return (root || doc).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || doc).querySelectorAll(sel)); };

  /* ---------------------------------------------------------- mobile menu -- */
  function initMenu() {
    var panel = $('[data-so-menu-panel]');
    if (!panel) return;
    var openBtn = $('[data-so-menu-open]');
    var lastFocus = null;

    function open() {
      lastFocus = doc.activeElement;
      panel.classList.remove('hidden');
      doc.documentElement.style.overflow = 'hidden';
      if (openBtn) openBtn.setAttribute('aria-expanded', 'true');
      var first = panel.querySelector('a, button');
      if (first) first.focus();
    }
    function close() {
      panel.classList.add('hidden');
      doc.documentElement.style.overflow = '';
      if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    if (openBtn) openBtn.addEventListener('click', open);
    $$('[data-so-menu-close]', panel).forEach(function (el) { el.addEventListener('click', close); });
    doc.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !panel.classList.contains('hidden')) close();
    });
  }

  /* ------------------------------------------------------------ countdown --
   * Counts to the real cohort deadline stored on the site. When the deadline
   * passes the label switches to the open state instead of inventing new time.
   */
  function initCountdown() {
    $$('[data-so-countdown]').forEach(function (el) {
      var target = Date.parse(el.getAttribute('data-so-countdown'));
      var label = $('.so-countdown-label', el);
      if (!label || isNaN(target)) return;
      function tick() {
        var left = target - Date.now();
        if (left <= 0) { label.textContent = 'ENROLLMENT OPEN'; return; }
        var d = Math.floor(left / 86400000);
        var h = Math.floor((left % 86400000) / 3600000);
        var m = Math.floor((left % 3600000) / 60000);
        label.textContent = d > 0
          ? 'CLOSES IN ' + d + 'D ' + String(h).padStart(2, '0') + 'H'
          : 'CLOSES IN ' + h + 'H ' + String(m).padStart(2, '0') + 'M';
      }
      tick();
      setInterval(tick, 60000);
    });
  }

  /* ------------------------------------------------------------ accordion -- */
  function initAccordions() {
    $$('[data-so-accordion], .so-accordion').forEach(function (item) {
      var trigger = $('[data-so-accordion-trigger], .so-accordion-trigger', item);
      var body = $('[data-so-accordion-body], .so-accordion-body', item);
      if (!trigger || !body) return;
      trigger.setAttribute('aria-expanded', body.classList.contains('hidden') ? 'false' : 'true');
      trigger.addEventListener('click', function () {
        var hidden = body.classList.toggle('hidden');
        trigger.setAttribute('aria-expanded', hidden ? 'false' : 'true');
        var icon = $('[data-so-accordion-icon], .so-accordion-icon', item);
        if (icon) icon.classList.toggle('rotate-180', !hidden);
      });
    });
  }

  /* -------------------------------------------------------- pricing toggle -- */
  function initPricing() {
    var monthly = $('#btn-monthly');
    var upfront = $('#btn-upfront');
    if (!monthly || !upfront) return;
    var active = ['bg-primary-container', 'text-on-primary', 'font-bold', 'shadow-sm'];

    function set(mode) {
      var on = mode === 'upfront' ? upfront : monthly;
      var off = mode === 'upfront' ? monthly : upfront;
      on.classList.add.apply(on.classList, active);
      on.classList.remove('text-on-surface-variant');
      off.classList.remove.apply(off.classList, active);
      off.classList.add('text-on-surface-variant');
      on.setAttribute('aria-pressed', 'true');
      off.setAttribute('aria-pressed', 'false');
      $$('.price-display, .period-display').forEach(function (el) {
        var m = el.querySelector('.so-when-monthly');
        var u = el.querySelector('.so-when-upfront');
        if (m && u) {
          m.classList.toggle('hidden', mode !== 'monthly');
          u.classList.toggle('hidden', mode !== 'upfront');
          return;
        }
        var v = el.getAttribute('data-' + mode);
        if (v !== null) el.textContent = v;
      });
    }
    monthly.addEventListener('click', function () { set('monthly'); });
    upfront.addEventListener('click', function () { set('upfront'); });
    set(monthly.classList.contains('bg-primary-container') ? 'monthly' : 'upfront');
  }

  /* ------------------------------------------------------------ track filter -- */
  function initFilters() {
    var chips = $$('[data-so-filter], .so-filter');
    if (!chips.length) return;
    var items = $$('[data-so-track], .so-track');
    function valueOf(el, prefix, attr) {
      var v = el.getAttribute(attr);
      if (v) return v;
      for (var i = 0; i < el.classList.length; i++) {
        if (el.classList[i].indexOf(prefix) === 0) return el.classList[i].slice(prefix.length);
      }
      return '';
    }
    function apply(value) {
      chips.forEach(function (c) {
        var on = valueOf(c, 'so-filter-', 'data-so-filter') === value;
        c.classList.toggle('bg-primary-container', on);
        c.classList.toggle('text-on-primary', on);
        c.classList.toggle('border-primary-container', on);
        c.classList.toggle('bg-surface-container-lowest', !on);
        c.classList.toggle('text-on-surface-variant', !on);
        c.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
      items.forEach(function (item) {
        var tags = [];
        var attr = item.getAttribute('data-so-track');
        if (attr) {
          tags = attr.split(/\s+/);
        } else {
          item.classList.forEach(function (c) {
            if (c.indexOf('so-track-') === 0) tags.push(c.slice('so-track-'.length));
          });
        }
        item.classList.toggle('hidden', value !== 'all' && tags.indexOf(value) === -1);
      });
    }
    chips.forEach(function (chip) {
      chip.addEventListener('click', function () { apply(valueOf(chip, 'so-filter-', 'data-so-filter')); });
    });
    apply('all');
  }

  /* ---------------------------------------------------------------- modals -- */
  function initModals() {
    var opens = $$('[data-so-modal-open], .so-open-video');
    if (!opens.length) return;
    var lastFocus = null;

    function close(modal) {
      modal.classList.add('hidden');
      doc.documentElement.style.overflow = '';
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    opens.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        var modal = doc.getElementById(btn.getAttribute('data-so-modal-open') || 'so-preview-modal');
        if (!modal) return;
        lastFocus = doc.activeElement;
        modal.classList.remove('hidden');
        doc.documentElement.style.overflow = 'hidden';
        var focusable = modal.querySelector('button, [href], input, select, textarea');
        if (focusable) focusable.focus();
      });
    });
    $$('[data-so-modal]').forEach(function (modal) {
      $$('[data-so-modal-close]', modal).forEach(function (el) {
        el.addEventListener('click', function () { close(modal); });
      });
      doc.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(modal);
      });
    });
  }

  /* ------------------------------------------------- search shortcut (⌘K) -- */
  function initSearchShortcut() {
    var input = $('[data-so-search]');
    if (!input) return;
    doc.addEventListener('keydown', function (e) {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        input.focus();
        input.select();
      }
    });
  }

  /* ------------------------------------------- anchors under a fixed header -- */
  function initAnchors() {
    var headerH = 104;
    $$('a[href^="#"]').forEach(function (a) {
      var href = a.getAttribute('href');
      if (!href || href === '#') return;
      a.addEventListener('click', function (e) {
        var target = doc.getElementById(href.slice(1));
        if (!target) return;
        e.preventDefault();
        var top = target.getBoundingClientRect().top + window.scrollY - headerH;
        window.scrollTo({ top: top < 0 ? 0 : top, behavior: 'smooth' });
        history.replaceState(null, '', href);
      });
    });
  }

  /* -------------------------------------------------- form submit feedback -- */
  function initForms() {
    $$('.so-form').forEach(function (form) {
      form.addEventListener('submit', function () {
        var btn = $('.so-submit', form);
        if (!btn) return;
        btn.setAttribute('aria-busy', 'true');
        btn.disabled = true;
        var label = $('.so-submit-label', btn);
        if (label) label.textContent = label.getAttribute('data-busy') || 'Sending…';
      });
    });
  }

  function boot() {
    initMenu();
    initCountdown();
    initAccordions();
    initPricing();
    initFilters();
    initModals();
    initSearchShortcut();
    initAnchors();
    initForms();
  }

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
