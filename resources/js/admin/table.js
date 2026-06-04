/*
 * Admin table progressive-enhancement helper.
 *
 * Tags an existing `<table class="adm-table">` with extras driven by
 * data-attributes. Server still renders the full HTML; this module wires
 * up sorting, search and pagination on top.
 *
 * Usage:
 *
 *   <input type="search" data-table-search="#users-table" placeholder="Buscar...">
 *
 *   <table id="users-table" class="adm-table" data-paginate="20">
 *       <thead>
 *           <tr>
 *               <th data-sort="user_id">ID</th>           ← click to sort
 *               <th data-sort="user_name">Name</th>
 *               <th data-sort="user_points" data-sort-type="number">Points</th>
 *               <th>Actions</th>                          ← no data-sort = not sortable
 *           </tr>
 *       </thead>
 *       <tbody><tr><td>1</td><td>jonamix</td>…</tr></tbody>
 *   </table>
 *
 *   <div data-table-pager="#users-table"></div>          ← optional pagination strip
 *
 * Sort direction is tri-state: none → asc → desc. Rows MUST be present in
 * the initial HTML — no data fetching here.
 */

import { createIcons, ArrowUpDown, ArrowUp, ArrowDown, ChevronLeft, ChevronRight } from 'lucide';

function iconHTML(name) {
    // Lucide returns the SVG string when rendering — we keep it inline so
    // the icon swap on sort is cheap.
    const node = document.createElement('span');
    node.className = 'adm-table-sort-icon';
    node.setAttribute('data-lucide', name);
    return node.outerHTML;
}

const SORT_ICON_NONE = iconHTML('arrow-up-down');
const SORT_ICON_ASC  = iconHTML('arrow-up');
const SORT_ICON_DESC = iconHTML('arrow-down');

function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

function enhance(table) {
    if (table.dataset.tableEnhanced === '1') return;
    table.dataset.tableEnhanced = '1';

    const tbody = table.tBodies[0];
    if (!tbody) return;

    const allRows = Array.from(tbody.rows);
    const colKeys = Array.from(table.tHead?.rows[0]?.cells || []).map((th) => ({
        key: th.dataset.sort || null,
        type: th.dataset.sortType || 'text',
    }));

    // Annotate each <th data-sort> with a sort icon + click handler.
    Array.from(table.tHead?.rows[0]?.cells || []).forEach((th, idx) => {
        if (!th.dataset.sort) return;
        th.style.cursor = 'pointer';
        th.style.userSelect = 'none';

        const icon = document.createElement('span');
        icon.className = 'adm-table-sort-wrap';
        icon.innerHTML = SORT_ICON_NONE;
        th.appendChild(icon);

        th.addEventListener('click', () => {
            const cur = state.sort;
            let dir;
            if (cur.col !== idx) dir = 'asc';
            else if (cur.dir === 'asc') dir = 'desc';
            else if (cur.dir === 'desc') dir = null;
            else dir = 'asc';
            state.sort = { col: dir ? idx : -1, dir };
            render();
        });
    });

    const state = {
        sort: { col: -1, dir: null },
        filter: '',
        page: 0,
        pageSize: parseInt(table.dataset.paginate || '0', 10) || 0,
    };

    const pagerEl = document.querySelector(`[data-table-pager="#${table.id}"]`)
        || (table.id ? document.querySelector(`[data-table-pager="${table.id}"]`) : null);

    function compareCells(a, b, idx, type) {
        const av = (a.cells[idx]?.textContent || '').trim();
        const bv = (b.cells[idx]?.textContent || '').trim();
        if (type === 'number') {
            const na = parseFloat(av.replace(/[^\d.\-]/g, ''));
            const nb = parseFloat(bv.replace(/[^\d.\-]/g, ''));
            return (Number.isNaN(na) ? 0 : na) - (Number.isNaN(nb) ? 0 : nb);
        }
        return av.localeCompare(bv, undefined, { sensitivity: 'base' });
    }

    function applyView() {
        let view = allRows;
        if (state.filter) {
            const q = state.filter.toLowerCase();
            view = view.filter((row) => row.textContent.toLowerCase().includes(q));
        }
        if (state.sort.col >= 0 && state.sort.dir) {
            const idx = state.sort.col;
            const type = colKeys[idx]?.type || 'text';
            const sign = state.sort.dir === 'asc' ? 1 : -1;
            view = view.slice().sort((a, b) => sign * compareCells(a, b, idx, type));
        }
        return view;
    }

    function render() {
        const view = applyView();

        Array.from(table.tHead?.rows[0]?.cells || []).forEach((th, idx) => {
            const wrap = th.querySelector('.adm-table-sort-wrap');
            if (!wrap) return;
            if (state.sort.col === idx && state.sort.dir === 'asc')  wrap.innerHTML = SORT_ICON_ASC;
            else if (state.sort.col === idx && state.sort.dir === 'desc') wrap.innerHTML = SORT_ICON_DESC;
            else wrap.innerHTML = SORT_ICON_NONE;
        });

        const pageSize = state.pageSize;
        const totalPages = pageSize > 0 ? Math.max(1, Math.ceil(view.length / pageSize)) : 1;
        if (state.page >= totalPages) state.page = totalPages - 1;
        if (state.page < 0) state.page = 0;
        const slice = pageSize > 0
            ? view.slice(state.page * pageSize, (state.page + 1) * pageSize)
            : view;

        tbody.replaceChildren(...slice);

        if (slice.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = colKeys.length || 1;
            td.style.padding = '18px';
            td.style.textAlign = 'center';
            td.style.color = '#64748b';
            td.style.fontSize = '13px';
            td.textContent = 'No results';
            tr.appendChild(td);
            tbody.appendChild(tr);
        }

        renderPager(view.length, totalPages);

        // Re-render any lucide icons that were just injected.
        createIcons({
            icons: { ArrowUpDown, ArrowUp, ArrowDown, ChevronLeft, ChevronRight },
            attrs: { class: 'adm-table-sort-icon' },
        });
    }

    function renderPager(total, totalPages) {
        if (!pagerEl) return;
        if (state.pageSize <= 0 || total <= state.pageSize) {
            pagerEl.innerHTML = '';
            return;
        }
        const from = state.page * state.pageSize + 1;
        const to = Math.min(total, (state.page + 1) * state.pageSize);
        pagerEl.innerHTML = `
            <div class="adm-table-pager">
                <span class="adm-table-pager-info">${from}–${to} of ${total}</span>
                <div class="adm-table-pager-actions">
                    <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm" ${state.page === 0 ? 'disabled' : ''} data-page-prev>
                        <i data-lucide="chevron-left"></i>
                    </button>
                    <span class="adm-table-pager-pos">${state.page + 1} / ${totalPages}</span>
                    <button type="button" class="adm-btn adm-btn-secondary adm-btn-sm" ${state.page >= totalPages - 1 ? 'disabled' : ''} data-page-next>
                        <i data-lucide="chevron-right"></i>
                    </button>
                </div>
            </div>`;
        pagerEl.querySelector('[data-page-prev]')?.addEventListener('click', () => { state.page--; render(); });
        pagerEl.querySelector('[data-page-next]')?.addEventListener('click', () => { state.page++; render(); });
    }

    const searchSelector = `[data-table-search="#${table.id}"], [data-table-search="${table.id}"]`;
    document.querySelectorAll(searchSelector).forEach((input) => {
        input.addEventListener('input', debounce((ev) => {
            state.filter = (ev.target.value || '').trim();
            state.page = 0;
            render();
        }, 150));
    });

    render();
}

export function initAdminTables(root = document) {
    root.querySelectorAll('table.adm-table[id]').forEach((table) => {
        const hasSort = table.tHead?.querySelector('th[data-sort]');
        const hasPaginate = table.dataset.paginate && parseInt(table.dataset.paginate, 10) > 0;
        const hasSearch = document.querySelector(`[data-table-search="#${table.id}"], [data-table-search="${table.id}"]`);
        if (hasSort || hasPaginate || hasSearch) {
            enhance(table);
        }
    });
}
