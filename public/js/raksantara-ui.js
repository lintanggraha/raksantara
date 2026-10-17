/*
 * Raksantara UI behaviour: command palette (Ctrl/⌘+K), navigation progress
 * bar and toasts. No dependencies. Markup lives in partials/rk-overlays and
 * partials/rk-topbar.
 */
(function () {
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
    document.querySelectorAll('[data-rk-mod]').forEach((el) => { el.textContent = isMac ? '⌘' : 'Ctrl'; });

    function readJson(selector) {
        const el = document.querySelector(selector);
        if (!el) return [];
        try { return JSON.parse(el.textContent || '[]'); } catch (e) { return []; }
    }

    // ------------------------------------------------------------------
    // Navigation progress bar
    // ------------------------------------------------------------------
    const bar = document.querySelector('[data-rk-progress]');
    let barTimer = null;

    function startProgress() {
        if (!bar || reduceMotion) return;
        bar.classList.remove('is-done');
        void bar.offsetWidth; // restart the transition
        bar.classList.add('is-active');
        clearTimeout(barTimer);
        // Downloads and blocked navigations never unload the page; finish anyway.
        barTimer = setTimeout(finishProgress, 6000);
    }
    function finishProgress() {
        if (!bar) return;
        clearTimeout(barTimer);
        bar.classList.remove('is-active');
        bar.classList.add('is-done');
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
        if (link.target && link.target !== '_self') return;
        if (link.hasAttribute('download') || link.dataset.bsToggle || link.dataset.toggle) return;
        let url;
        try { url = new URL(link.href, window.location.href); } catch (e) { return; }
        if (url.origin !== window.location.origin) return;
        if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;
        // Let other handlers decide first (confirm dialogs, JS routing).
        setTimeout(() => { if (!event.defaultPrevented) startProgress(); }, 0);
    });
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (form.target && form.target !== '_self') return;
        setTimeout(() => { if (!event.defaultPrevented) startProgress(); }, 0);
    });
    window.addEventListener('pageshow', finishProgress);

    // ------------------------------------------------------------------
    // Toasts
    // ------------------------------------------------------------------
    const toastHost = document.querySelector('[data-rk-toasts]');

    function toast(type, title, text) {
        if (!toastHost) return;
        const el = document.createElement('div');
        el.className = 'rk-toast ' + (type === 'error' ? 'error' : 'success');
        el.setAttribute('role', type === 'error' ? 'alert' : 'status');

        const ico = document.createElement('span');
        ico.className = 'ico';
        ico.innerHTML = type === 'error' ? '<i class="fas fa-exclamation" aria-hidden="true"></i>' : '<i class="fas fa-check" aria-hidden="true"></i>';
        const body = document.createElement('div');
        const strong = document.createElement('strong');
        strong.textContent = title;
        const p = document.createElement('p');
        p.textContent = text;
        body.append(strong, p);
        const close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('aria-label', 'Tutup notifikasi');
        close.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';

        el.append(ico, body, close);
        toastHost.append(el);

        const lifetime = type === 'error' ? 9000 : 5000;
        let remaining = lifetime;
        let started = Date.now();
        let timer = setTimeout(dismiss, remaining);

        function dismiss() {
            clearTimeout(timer);
            if (reduceMotion) { el.remove(); return; }
            el.classList.add('is-leaving');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        }
        el.addEventListener('mouseenter', () => { clearTimeout(timer); remaining -= Date.now() - started; });
        el.addEventListener('mouseleave', () => { started = Date.now(); timer = setTimeout(dismiss, Math.max(remaining, 1500)); });
        close.addEventListener('click', dismiss);
    }
    window.rkToast = toast;
    readJson('[data-rk-flash]').forEach((item) => toast(item.type, item.title, item.text));

    // ------------------------------------------------------------------
    // Command palette
    // ------------------------------------------------------------------
    const palette = document.querySelector('[data-rk-palette]');
    if (!palette) return;
    const input = palette.querySelector('[data-rk-palette-input]');
    const list = palette.querySelector('[data-rk-palette-list]');
    let items = null;
    let filtered = [];
    let selected = 0;
    let returnFocus = null;

    function collectItems() {
        const seen = new Set();
        const out = [];
        readJson('[data-rk-actions]').forEach((action) => {
            seen.add(action.href);
            out.push({ label: action.label, hint: action.hint, icon: action.icon, href: action.href, group: 'Aksi cepat' });
        });
        document.querySelectorAll('.app-sidebar a[href]').forEach((link) => {
            const href = link.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript:') || link.closest('[data-user-menu]')) return;
            if (seen.has(link.href)) return;
            const label = (link.querySelector('span:not(.nav-link-icon)')?.textContent || link.textContent || '').replace(/\s+/g, ' ').trim();
            if (!label) return;
            seen.add(link.href);
            const parent = link.closest('.nav-item.dropdown');
            const group = parent?.querySelector(':scope > .nav-link-main span:not(.nav-link-icon)')?.textContent.trim() || 'Menu';
            const iconEl = link.querySelector('i[class*="fa-"]');
            const icon = iconEl ? Array.from(iconEl.classList).filter((c) => c.startsWith('fa')).join(' ') : 'fas fa-arrow-right';
            out.push({ label, hint: group, icon, href: link.href, group: 'Halaman' });
        });
        return out;
    }

    function normalize(text) {
        return text.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    // "Key Risk Indicators" -> "kri", so people can type the acronyms they use.
    function acronym(text) {
        return normalize(text).split(/[^a-z0-9]+/).filter(Boolean).map((w) => w[0]).join('');
    }

    function score(item, terms) {
        if (!terms.length) return 1;
        const hay = normalize(item.label + ' ' + item.hint);
        const label = normalize(item.label);
        item._acronym = item._acronym || acronym(item.label);
        let total = 0;
        for (const term of terms) {
            if (item._acronym.startsWith(term) && term.length >= 2) { total += 7; continue; }
            if (hay.indexOf(term) === -1) return 0;
            total += label.startsWith(term) ? 6 : (label.includes(' ' + term) ? 4 : (label.includes(term) ? 3 : 1));
        }
        return total;
    }

    function highlight(label, terms) {
        const frag = document.createDocumentFragment();
        if (!terms.length) { frag.append(label); return frag; }
        const norm = normalize(label);
        const marks = new Array(label.length).fill(false);
        terms.forEach((term) => {
            let from = 0;
            let at;
            while (term && (at = norm.indexOf(term, from)) !== -1) {
                for (let i = at; i < at + term.length; i++) marks[i] = true;
                from = at + term.length;
            }
        });
        let buffer = '';
        let marking = false;
        for (let i = 0; i <= label.length; i++) {
            const m = i < label.length ? marks[i] : !marking;
            if (m !== marking || i === label.length) {
                if (buffer) {
                    if (marking) { const mk = document.createElement('mark'); mk.textContent = buffer; frag.append(mk); } else { frag.append(buffer); }
                }
                buffer = '';
                marking = m;
            }
            if (i < label.length) buffer += label[i];
        }
        return frag;
    }

    function render() {
        const terms = normalize(input.value).split(/\s+/).filter(Boolean);
        filtered = items
            .map((item, index) => ({ item, index, s: score(item, terms) }))
            .filter((row) => row.s > 0)
            .sort((a, b) => (terms.length ? b.s - a.s : 0) || a.index - b.index)
            .slice(0, 40)
            .map((row) => row.item);
        selected = Math.min(selected, Math.max(filtered.length - 1, 0));
        list.replaceChildren();

        if (!filtered.length) {
            const empty = document.createElement('li');
            empty.className = 'rk-palette-empty';
            empty.textContent = 'Tidak ada menu atau aksi yang cocok dengan "' + input.value + '".';
            list.append(empty);
            input.removeAttribute('aria-activedescendant');
            return;
        }

        let lastGroup = null;
        filtered.forEach((item, i) => {
            if (item.group !== lastGroup) {
                const group = document.createElement('li');
                group.className = 'rk-palette-group';
                group.setAttribute('role', 'presentation');
                group.textContent = item.group;
                list.append(group);
                lastGroup = item.group;
            }
            const li = document.createElement('li');
            li.className = 'rk-palette-item';
            li.id = 'rk-palette-option-' + i;
            li.setAttribute('role', 'option');
            li.setAttribute('aria-selected', String(i === selected));
            const icon = document.createElement('span');
            icon.className = 'icon';
            icon.innerHTML = '<i aria-hidden="true"></i>';
            icon.firstChild.className = item.icon;
            const text = document.createElement('span');
            text.className = 'text';
            const strong = document.createElement('strong');
            strong.append(highlight(item.label, terms));
            const hint = document.createElement('span');
            hint.textContent = item.hint;
            text.append(strong, hint);
            const go = document.createElement('span');
            go.className = 'go';
            go.innerHTML = '<i class="fas fa-level-down-alt fa-rotate-90" aria-hidden="true"></i>';
            li.append(icon, text, go);
            li.addEventListener('mousemove', () => { if (selected !== i) { selected = i; updateSelection(); } });
            li.addEventListener('click', () => go_(item));
            list.append(li);
        });
        updateSelection();
    }

    function updateSelection() {
        list.querySelectorAll('.rk-palette-item').forEach((li, i) => li.setAttribute('aria-selected', String(i === selected)));
        const active = list.querySelector('#rk-palette-option-' + selected);
        if (active) {
            input.setAttribute('aria-activedescendant', active.id);
            active.scrollIntoView({ block: 'nearest' });
        }
    }

    function go_(item) {
        close();
        startProgress();
        window.location.href = item.href;
    }

    function open() {
        if (!palette.hidden) return;
        items = items || collectItems();
        returnFocus = document.activeElement;
        palette.hidden = false;
        document.documentElement.style.overflow = 'hidden';
        input.value = '';
        selected = 0;
        render();
        input.focus();
    }

    function close() {
        if (palette.hidden) return;
        palette.hidden = true;
        document.documentElement.style.overflow = '';
        if (returnFocus && typeof returnFocus.focus === 'function') returnFocus.focus();
    }

    document.querySelectorAll('[data-rk-palette-open]').forEach((btn) => btn.addEventListener('click', open));
    palette.querySelectorAll('[data-rk-palette-close]').forEach((el) => el.addEventListener('click', close));
    input.addEventListener('input', () => { selected = 0; render(); });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') { event.preventDefault(); if (filtered.length) { selected = (selected + 1) % filtered.length; updateSelection(); } }
        else if (event.key === 'ArrowUp') { event.preventDefault(); if (filtered.length) { selected = (selected - 1 + filtered.length) % filtered.length; updateSelection(); } }
        else if (event.key === 'Enter') { event.preventDefault(); if (filtered[selected]) go_(filtered[selected]); }
        else if (event.key === 'Escape') { event.preventDefault(); close(); }
        else if (event.key === 'Tab') { event.preventDefault(); } // keep focus inside the dialog
    });

    document.addEventListener('keydown', (event) => {
        const key = event.key.toLowerCase();
        if ((event.ctrlKey || event.metaKey) && key === 'k') {
            event.preventDefault();
            palette.hidden ? open() : close();
            return;
        }
        if (key === '/' && palette.hidden && !event.ctrlKey && !event.metaKey && !event.altKey) {
            const t = event.target;
            const typing = t && (t.isContentEditable || /^(input|textarea|select)$/i.test(t.tagName));
            if (!typing) { event.preventDefault(); open(); }
        }
    });
})();
