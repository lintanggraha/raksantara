/**
 * Click-to-sort for every table header in the app.
 *
 * - Applies to any <table> with a <thead>, including tables rendered later
 *   (event delegation). Opt out with data-no-sort on the table or a <th>.
 * - Skips DataTables tables (they sort themselves) and tables whose body uses
 *   rowspan, where reordering rows would break the layout.
 * - A cell can supply its own key with data-sort-value.
 * - Detects numbers (Indonesian and English formats, Rupiah with Jt/M/T,
 *   percentages), dates (ISO, d-m-Y, d/m/Y, "14 Okt 2026") and falls back to
 *   natural text order. Empty values ("-", "–") always go last.
 * - Server-paginated tables sort the rows on the current page only.
 */
(function () {
    'use strict';

    const MONTHS = {
        jan: 0, januari: 0, january: 0,
        feb: 1, februari: 1, february: 1,
        mar: 2, maret: 2, march: 2,
        apr: 3, april: 3,
        mei: 4, may: 4,
        jun: 5, juni: 5, june: 5,
        jul: 6, juli: 6, july: 6,
        agu: 7, agt: 7, agus: 7, agustus: 7, aug: 7, august: 7,
        sep: 8, sept: 8, september: 8,
        okt: 9, oct: 9, oktober: 9, october: 9,
        nov: 10, november: 10,
        des: 11, dec: 11, desember: 11, december: 11,
    };
    const EMPTY = /^(-|–|—|n\/a|tidak ada)?$/i;
    const collator = new Intl.Collator('id', { numeric: true, sensitivity: 'base' });

    function cellText(cell) {
        if (!cell) return '';
        if (cell.dataset.sortValue !== undefined) return cell.dataset.sortValue.trim();
        const field = cell.querySelector('select, input:not([type="hidden"])');
        if (field && !cell.textContent.trim()) {
            return field.tagName === 'SELECT' ? (field.selectedOptions[0]?.textContent || '') : field.value;
        }
        return cell.textContent.replace(/\s+/g, ' ').trim();
    }

    function parseNumber(text) {
        let value = text.replace(/\s+/g, ' ').trim();
        let multiplier = 1;
        const rupiah = /^rp\.?\s*/i.test(value);
        value = value.replace(/^rp\.?\s*/i, '');
        const suffix = value.match(/\s*(t|m|jt|rb|k)$/i);
        if (rupiah && suffix) {
            multiplier = { t: 1e12, m: 1e9, jt: 1e6, rb: 1e3, k: 1e3 }[suffix[1].toLowerCase()];
            value = value.slice(0, -suffix[0].length);
        }
        value = value.replace(/\s*%$/, '').replace(/\s+/g, '');
        if (!/^[-+]?[\d.,]+$/.test(value) || !/\d/.test(value)) return null;

        const lastComma = value.lastIndexOf(',');
        const lastDot = value.lastIndexOf('.');
        if (lastComma > -1 && lastDot > -1) {
            // Whichever separator comes last is the decimal mark.
            value = lastComma > lastDot ? value.replace(/\./g, '').replace(',', '.') : value.replace(/,/g, '');
        } else if (lastComma > -1) {
            // "1,5" is a decimal; "1,234,567" groups thousands.
            value = /,\d{3}(,\d{3})*$/.test(value) && value.split(',').length > 2 ? value.replace(/,/g, '') : value.replace(',', '.');
        } else if (lastDot > -1 && /^\d{1,3}(\.\d{3})+$/.test(value.replace(/^[-+]/, ''))) {
            // Indonesian thousands grouping: 1.234.567
            value = value.replace(/\./g, '');
        }

        const number = Number(value);
        return Number.isFinite(number) ? number * multiplier : null;
    }

    function parseDate(text) {
        const value = text.trim().toLowerCase();
        let match = value.match(/^(\d{4})-(\d{2})-(\d{2})(?:[ t](\d{1,2}):(\d{2}))?/);
        if (match) return Date.UTC(+match[1], +match[2] - 1, +match[3], +(match[4] || 0), +(match[5] || 0));

        match = value.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})(?:\s+(\d{1,2}):(\d{2}))?$/);
        if (match) return Date.UTC(+match[3], +match[2] - 1, +match[1], +(match[4] || 0), +(match[5] || 0));

        match = value.match(/^(\d{1,2})[\s-]([a-z]+)\.?[\s-],?\s*(\d{4})(?:[,\s]+(\d{1,2})[:.](\d{2}))?/);
        if (match && MONTHS[match[2]] !== undefined) {
            return Date.UTC(+match[3], MONTHS[match[2]], +match[1], +(match[4] || 0), +(match[5] || 0));
        }

        match = value.match(/^([a-z]+)\.?\s+(\d{1,2}),?\s+(\d{4})/); // "Oct 14, 2026"
        if (match && MONTHS[match[1]] !== undefined) return Date.UTC(+match[3], MONTHS[match[1]], +match[2]);

        return null;
    }

    function columnIndex(th) {
        let index = 0;
        for (let cell = th.previousElementSibling; cell; cell = cell.previousElementSibling) {
            index += cell.colSpan || 1;
        }
        return index;
    }

    function cellAt(row, index) {
        let position = 0;
        for (const cell of row.cells) {
            const span = cell.colSpan || 1;
            if (index < position + span) return span === 1 ? cell : null;
            position += span;
        }
        return null;
    }

    function isSortableHeader(th) {
        const table = th.closest('table');
        if (!table || !table.tHead || !table.tHead.contains(th)) return false;
        if (table.hasAttribute('data-no-sort') || th.hasAttribute('data-no-sort')) return false;
        if (table.classList.contains('dataTable') || table.closest('.dataTables_wrapper')) return false;
        if (table.tHead.rows.length !== 1 || (th.colSpan || 1) > 1) return false;
        if (!th.textContent.trim() || th.querySelector('input, select, button')) return false;
        return Array.from(table.tBodies).every((body) => body.rows.length > 1
            ? !Array.from(body.querySelectorAll('td, th')).some((cell) => (cell.rowSpan || 1) > 1)
            : true);
    }

    function sortTable(th) {
        const table = th.closest('table');
        const index = columnIndex(th);
        const direction = th.getAttribute('aria-sort') === 'ascending' ? 'descending' : 'ascending';

        table.tHead.querySelectorAll('th[aria-sort]').forEach((cell) => cell.removeAttribute('aria-sort'));
        th.setAttribute('aria-sort', direction);

        const headerCount = Array.from(table.tHead.rows[0].cells).reduce((sum, cell) => sum + (cell.colSpan || 1), 0);

        Array.from(table.tBodies).forEach((body) => {
            const rows = Array.from(body.rows);
            // Full-width rows (empty states, notes) stay at the end.
            const fixed = rows.filter((row) => row.cells.length === 1 && (row.cells[0].colSpan || 1) >= headerCount);
            const sortable = rows.filter((row) => !fixed.includes(row));
            if (sortable.length < 2) return;

            const entries = sortable.map((row, position) => {
                const text = cellText(cellAt(row, index));
                return { row, position, text, empty: EMPTY.test(text) };
            });
            const filled = entries.filter((entry) => !entry.empty);

            let type = 'text';
            if (filled.length && filled.every((entry) => (entry.number = parseNumber(entry.text)) !== null)) {
                type = 'number';
            } else if (filled.length && filled.every((entry) => (entry.date = parseDate(entry.text)) !== null)) {
                type = 'date';
            }

            const factor = direction === 'ascending' ? 1 : -1;
            entries.sort((a, b) => {
                if (a.empty !== b.empty) return a.empty ? 1 : -1;
                let result = 0;
                if (!a.empty) {
                    if (type === 'number') result = a.number - b.number;
                    else if (type === 'date') result = a.date - b.date;
                    else result = collator.compare(a.text, b.text);
                }
                return result * factor || a.position - b.position;
            });

            const fragment = document.createDocumentFragment();
            entries.forEach((entry) => fragment.appendChild(entry.row));
            fixed.forEach((row) => fragment.appendChild(row));
            body.appendChild(fragment);
        });
    }

    function decorate(root) {
        root.querySelectorAll('table > thead > tr > th').forEach((th) => {
            if (th.dataset.sortReady) return;
            if (isSortableHeader(th)) {
                th.dataset.sortReady = '1';
                th.classList.add('is-sortable');
                th.tabIndex = 0;
                th.setAttribute('title', th.getAttribute('title') || 'Klik untuk mengurutkan');
            }
        });
    }

    document.addEventListener('click', (event) => {
        const th = event.target.closest('table > thead > tr > th');
        if (!th || event.target.closest('a, button, input, select, label') || !isSortableHeader(th)) return;
        sortTable(th);
    });

    document.addEventListener('keydown', (event) => {
        const th = event.target.closest && event.target.closest('th.is-sortable');
        if (!th || (event.key !== 'Enter' && event.key !== ' ')) return;
        event.preventDefault();
        sortTable(th);
    });

    const start = () => {
        decorate(document);
        let pending = null;
        new MutationObserver(() => {
            clearTimeout(pending);
            pending = setTimeout(() => decorate(document), 150);
        }).observe(document.body, { childList: true, subtree: true });
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }

    window.RaksantaraTableSort = { parseNumber, parseDate, sortTable };
})();
