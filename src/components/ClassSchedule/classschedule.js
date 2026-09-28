/**
 * Class Schedule — Search, Sort, and Filter functionality
 *
 * Table structure (column indices used by sort):
 *   0  col-status     (always visible)
 *   1  col-course-id  (always visible)
 *   2  col-title      (always visible)
 *   3  col-seats      (toggleable)
 *   4  col-days       (toggleable)
 *   5  col-time       (toggleable)
 *   6  col-location   (toggleable)
 *   7  col-instructor (toggleable)
 *   8  col-class-num  (toggleable)
 *   9  col-enrollment (toggleable)
 *
 * Which toggleable columns are shown by default is configured by the site editor
 * per-block and emitted as data-default-columns on .el-table.
 *
 * Every lookup is scoped to one block's .class-schedule root so two Class
 * Schedule blocks on the same page work independently (WPM-180). Handlers
 * called without a DOM reference fall back to the first block on the page.
 */

// Wrap classschedule.js in IIFE to avoid global scope pollution
(function() {
'use strict';

// ── Block instances ───────────────────────────────────────────────────────────

// Resolve the block root from an element, an event, or nothing (first block).
function rootFor(ref) {
    var el = ref && ref.nodeType === 1 ? ref : (ref && ref.target);
    var root = el && el.closest ? el.closest('.class-schedule') : null;
    return root || document.querySelector('.class-schedule');
}

// Per-block sort order and the checkbox snapshot Cancel restores.
var instanceState = new WeakMap();

function stateFor(root) {
    var state = instanceState.get(root);
    if (!state) {
        state = { sortColumn: -1, sortAscending: true, savedColumns: {}, savedStatuses: {} };
        instanceState.set(root, state);
    }
    return state;
}

// ── A11Y: Live count update (aria-live region) ───────────────────────────────

function updateClassCount(root) {
    var rows = root.querySelectorAll('.el-table .course-row');
    var visible = 0;
    rows.forEach(function(row) {
        if (row.style.display !== 'none') visible++;
    });
    var el = root.querySelector('.class-count');
    if (el) el.innerHTML = 'Displaying <strong>' + visible + '</strong> classes';
}

// ── Search ────────────────────────────────────────────────────────────────────

function classScheduleSearch(event) {
    const root = rootFor(event);
    const searchTerm = event.target.value.toLowerCase();
    const rows = root.querySelectorAll('.el-table .course-row');

    const activeStatuses = getActiveStatuses(root);

    rows.forEach(row => {
        const matchesSearch = rowMatchesSearch(row, searchTerm);

        const rowStatus = row.dataset.status;
        const matchesStatus = activeStatuses.length === 0 || activeStatuses.includes(rowStatus);

        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });

    updateClassCount(root);
}

function getActiveStatuses(root) {
    const activeStatuses = [];
    root.querySelectorAll('.status-filter').forEach(filter => {
        if (filter.checked) activeStatuses.push(filter.dataset.status);
    });
    return activeStatuses;
}

// Check whether a row matches the search term.
// Mirrors the old Vue class-schedule app (resources/js/pages/courses.vue)
// which searched: title, location, instructor, enrollment status,
// class number, and catalog number (course ID).
function rowMatchesSearch(row, searchTerm) {
    if (!searchTerm) return true;

    const textOf = cls => {
        const el = row.querySelector(cls);
        return el ? el.textContent.toLowerCase() : '';
    };

    return textOf('.col-title').includes(searchTerm) ||
           textOf('.col-location').includes(searchTerm) ||
           textOf('.col-instructor').includes(searchTerm) ||
           textOf('.col-class-num').includes(searchTerm) ||
           textOf('.col-course-id').includes(searchTerm) ||
           (row.dataset.status || '').toLowerCase().includes(searchTerm);
}

// ── Sort ──────────────────────────────────────────────────────────────────────

function sortClassSchedule(columnIndex, ref) {
    const root  = rootFor(ref);
    const state = stateFor(root);
    if (state.sortColumn === columnIndex) {
        state.sortAscending = !state.sortAscending;
    } else {
        state.sortAscending = true;
        state.sortColumn = columnIndex;
    }

    const tbody = root.querySelector('.el-table .el-table__body');
    const rows  = Array.from(tbody.querySelectorAll('.course-row'));
    const dir   = state.sortAscending ? 1 : -1;

    rows.sort((a, b) => {
        const aCells = a.querySelectorAll('[role="cell"]');
        const bCells = b.querySelectorAll('[role="cell"]');
        const aText  = (aCells[columnIndex] ? aCells[columnIndex].textContent.trim() : '');
        const bText  = (bCells[columnIndex] ? bCells[columnIndex].textContent.trim() : '');

        // Numeric sort for seats column (parse "N open / M total" → N)
        const aNum = parseFloat(aText);
        const bNum = parseFloat(bText);
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return (aNum - bNum) * dir;
        }

        return aText.localeCompare(bText) * dir;
    });

    rows.forEach(row => tbody.appendChild(row));

    updateSortIndicators(root, columnIndex, state.sortAscending);
}

function updateSortIndicators(root, columnIndex, ascending) {
    // columnIndex matches the column position (0 = status, 1 = course-id, etc.)
    root.querySelectorAll('.el-table .el-table__header-row > [role="columnheader"]').forEach((col, i) => {
        col.classList.remove('ascending', 'descending');
        if (i === columnIndex) {
            col.classList.add(ascending ? 'ascending' : 'descending');
            col.setAttribute('aria-sort', ascending ? 'ascending' : 'descending');
        } else {
            col.removeAttribute('aria-sort');
        }
    });
}

// ── Filter Modal ──────────────────────────────────────────────────────────────

// Snapshot this block's checkbox states so Cancel can restore them
function snapshotFilterState(root) {
    var state = stateFor(root);
    state.savedColumns = {};
    root.querySelectorAll('.column-toggle').forEach(t => {
        state.savedColumns[t.dataset.column] = t.checked;
    });
    state.savedStatuses = {};
    root.querySelectorAll('.status-filter').forEach(f => {
        state.savedStatuses[f.dataset.status] = f.checked;
    });
}

function openFilterModal(ref) {
    var root = rootFor(ref);

    // Snapshot current checkbox states before the user makes changes
    snapshotFilterState(root);

    // A11Y: remember which element opened the modal so we can restore focus
    filterModalOpener = document.activeElement;

    var modal = root.querySelector('.filter-modal');
    modal.classList.add('active');

    // A11Y: move focus into the modal
    var firstFocusable = modal.querySelector('input, button, [tabindex]:not([tabindex="-1"])');
    if (firstFocusable) firstFocusable.focus();

    // A11Y: trap focus and handle Escape
    document.addEventListener('keydown', filterModalKeyHandler);
}

// A11Y: element that opened the modal (for focus restoration)
var filterModalOpener = null;

// A11Y: keyboard handler for focus trapping and Escape
function filterModalKeyHandler(event) {
    // Only one filter modal can be open at a time
    var modal = document.querySelector('.class-schedule .filter-modal.active');
    if (!modal) return;

    if (event.key === 'Escape') {
        event.preventDefault();
        closeFilterModal(modal);
        return;
    }

    if (event.key === 'Tab') {
        var focusable = modal.querySelectorAll('input, button, [tabindex]:not([tabindex="-1"])');
        if (focusable.length === 0) return;
        var first = focusable[0];
        var last = focusable[focusable.length - 1];

        if (event.shiftKey) {
            if (document.activeElement === first) {
                event.preventDefault();
                last.focus();
            }
        } else {
            if (document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    }
}

function closeFilterModal(ref) {
    var root  = rootFor(ref);
    var state = stateFor(root);

    // Restore checkbox states to what they were when the modal opened
    root.querySelectorAll('.column-toggle').forEach(t => {
        if (state.savedColumns.hasOwnProperty(t.dataset.column)) {
            t.checked = state.savedColumns[t.dataset.column];
        }
    });
    root.querySelectorAll('.status-filter').forEach(f => {
        if (state.savedStatuses.hasOwnProperty(f.dataset.status)) {
            f.checked = state.savedStatuses[f.dataset.status];
        }
    });

    root.querySelector('.filter-modal').classList.remove('active');

    // A11Y: remove focus trap handler and restore focus to opener
    document.removeEventListener('keydown', filterModalKeyHandler);
    if (filterModalOpener) {
        filterModalOpener.focus();
        filterModalOpener = null;
    }
}

function applyFilters(ref) {
    var root = rootFor(ref);
    applyColumnVisibility(root);
    applyStatusFilters(root);

    // Update saved states so Cancel reflects the newly applied state
    snapshotFilterState(root);

    saveColumnState(root);

    root.querySelector('.filter-modal').classList.remove('active');

    // A11Y: remove focus trap handler and restore focus to opener
    document.removeEventListener('keydown', filterModalKeyHandler);
    if (filterModalOpener) {
        filterModalOpener.focus();
        filterModalOpener = null;
    }
}

// Default checked columns. The site editor configures these per-block; the chosen
// set is emitted on .el-table as data-default-columns. Falls back to the
// original Vue app defaults (Seats + Days) when the attribute is absent.
function getDefaultColumns(root) {
    var table = root.querySelector('.el-table');
    var attr = table ? table.getAttribute('data-default-columns') : null;
    if (attr === null) return ['seats', 'days'];
    if (attr.trim() === '') return [];
    return attr.split(',').map(function(s) { return s.trim(); }).filter(Boolean);
}

// Persist column visibility choices in sessionStorage so they survive
// navigation (e.g. clicking an instructor link and pressing Back).
// Each block on the page keeps its own key; the first keeps the original one.
function columnStateKey(root) {
    var instance = root.getAttribute('data-cs-instance');
    return (instance && instance !== '1') ? 'cs_columns_' + instance : 'cs_columns';
}

function saveColumnState(root) {
    var state = {};
    root.querySelectorAll('.column-toggle').forEach(function(t) {
        state[t.dataset.column] = t.checked;
    });
    try { sessionStorage.setItem(columnStateKey(root), JSON.stringify(state)); } catch(e) { /* ignore */ }
}

function restoreColumnState(root) {
    try {
        var saved = sessionStorage.getItem(columnStateKey(root));
        if (!saved) return;
        var state = JSON.parse(saved);
        root.querySelectorAll('.column-toggle').forEach(function(t) {
            if (state.hasOwnProperty(t.dataset.column)) {
                t.checked = state[t.dataset.column];
            }
        });
    } catch(e) { /* ignore */ }
}

function applyColumnVisibility(root) {
    const table = root.querySelector('.el-table');

    root.querySelectorAll('.column-toggle').forEach(toggle => {
        const colClass = 'col-' + toggle.dataset.column;
        const isVisible = toggle.checked;

        // Toggle all header and body cells with this column class
        table.querySelectorAll('.' + colClass).forEach(cell => {
            cell.classList.toggle('hidden', !isVisible);

            // A11Y: prevent keyboard focus on hidden sortable header buttons
            // (tabindex goes on the inner <button>, not the div, to avoid a double focus stop)
            if (cell.getAttribute('role') === 'columnheader' && cell.classList.contains('is-sortable')) {
                var btn = cell.querySelector('button');
                if (btn) {
                    btn.setAttribute('tabindex', isVisible ? '0' : '-1');
                }
            }
        });
    });

    updateGridTemplate(root);
}

// Rebuild CSS Grid column tracks based on which columns are visible.
// Hidden columns get 0px tracks so the grid collapses them properly.
var gridColumnDefs = [
    // Column mins mirror the old Vue/Element UI app (resources/js/pages/courses.vue).
    // Use fr units so extra width is distributed across visible columns.
    { cls: 'col-status',     width: '45px' },
    { cls: 'col-course-id',  width: 'minmax(108px, 1.35fr)' },
    { cls: 'col-title',      width: 'minmax(175px, 2.19fr)' },
    { cls: 'col-seats',      width: 'minmax(145px, 1.81fr)' },
    { cls: 'col-days',       width: 'minmax(80px, 1fr)' },
    { cls: 'col-time',       width: 'minmax(150px, 1.88fr)' },
    { cls: 'col-location',   width: 'minmax(140px, 1.75fr)' },
    { cls: 'col-instructor', width: 'minmax(120px, 1.50fr)' },
    { cls: 'col-class-num',  width: 'minmax(90px, 1.13fr)' },
    { cls: 'col-enrollment', width: 'minmax(120px, 1.50fr)' }
];

function updateGridTemplate(root) {
    var table = root.querySelector('.el-table');
    var gridCols = gridColumnDefs.map(function(col) {
        var sample = table.querySelector('.' + col.cls);
        return (sample && sample.classList.contains('hidden')) ? '0px' : col.width;
    }).join(' ');

    table.querySelectorAll('.el-table__header-row, .el-table__row').forEach(function(row) {
        row.style.gridTemplateColumns = gridCols;
    });
}

function applyStatusFilters(root) {
    const activeStatuses = getActiveStatuses(root);
    const searchTerm = (root.querySelector('.course-search')?.value || '').toLowerCase();

    root.querySelectorAll('.el-table .course-row').forEach(row => {
        const matchesStatus = activeStatuses.length === 0 || activeStatuses.includes(row.dataset.status);

        // Re-check search too so both filters stay in sync
        const matchesSearch = !searchTerm || rowMatchesSearch(row, searchTerm);

        row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
    });

    updateClassCount(root);
}

function resetFilters(ref) {
    const root = rootFor(ref);

    // Reset columns to the site-editor-configured defaults for this block
    const defaultColumns = getDefaultColumns(root);
    root.querySelectorAll('.column-toggle').forEach(t => {
        t.checked = defaultColumns.includes(t.dataset.column);
    });
    root.querySelectorAll('.status-filter').forEach(f => f.checked = true);

    const searchInput = root.querySelector('.course-search');
    if (searchInput) searchInput.value = '';

    // The search box lives outside the modal and isn't gated by Apply/Cancel,
    // so clearing it here must immediately re-filter the table and refresh
    // the aria-live count (WPM-112) instead of leaving stale rows hidden.
    applyStatusFilters(root);
}

// Close modal when clicking the backdrop
window.addEventListener('click', function(event) {
    const target = event.target;
    if (target && target.classList && target.classList.contains('filter-modal')) {
        closeFilterModal(target);
    }
});

// ── Copy URL ──────────────────────────────────────────────────────────────────

function classScheduleCopyUrl() {
    var url = window.location.href;

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
            classScheduleShowCopyToast(url);
        }, function() {
            classScheduleCopyFallback(url);
        });
    } else {
        classScheduleCopyFallback(url);
    }
}

function classScheduleCopyFallback(url) {
    var textArea = document.createElement('textarea');
    textArea.value = url;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();
    try { document.execCommand('copy'); } catch (e) { /* ignore */ }
    document.body.removeChild(textArea);
    classScheduleShowCopyToast(url);
}

function classScheduleShowCopyToast(url) {
    var toast = document.createElement('div');
    toast.className = 'cs-toast';

    var strong = document.createElement('strong');
    strong.textContent = 'Copied ';
    toast.appendChild(strong);

    var em = document.createElement('em');
    em.textContent = url;
    toast.appendChild(em);

    document.body.appendChild(toast);
    setTimeout(function() { toast.classList.add('cs-toast-visible'); }, 10);
    setTimeout(function() {
        toast.classList.remove('cs-toast-visible');
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

// ── Download CSV ──────────────────────────────────────────────────────────────

function classScheduleDownloadCSV(ref) {
    var root = rootFor(ref);
    var table = root.querySelector('.el-table');
    var headerCells = table.querySelectorAll('.el-table__header-row > [role="columnheader"]');
    var rows = table.querySelectorAll('.el-table__body .course-row');

    // Determine which columns are visible
    // Header and body divs share the same indices (both include status at index 0)
    var visibleCols = [];
    headerCells.forEach(function(col, i) {
        // Skip the status column (index 0) — it only contains a visual indicator, not text data
        if (i === 0) return;
        if (!col.classList.contains('hidden')) {
            visibleCols.push({
                index: i,
                label: col.textContent.trim()
            });
        }
    });

    // Build CSV header
    var csvRows = [];
    csvRows.push(visibleCols.map(function(c) { return '"' + c.label + '"'; }).join(','));

    // Build CSV data rows (only visible/filtered rows)
    rows.forEach(function(row) {
        if (row.style.display === 'none') return; // skip filtered-out rows

        var cells = row.querySelectorAll('[role="cell"]');
        var csvCols = visibleCols.map(function(c) {
            var text = (cells[c.index] ? cells[c.index].textContent.trim() : '');
            // Escape quotes in CSV
            return '"' + text.replace(/"/g, '""') + '"';
        });
        csvRows.push(csvCols.join(','));
    });

    var csvContent = csvRows.join('\n');
    var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    var url = URL.createObjectURL(blob);

    // Build filename from term dropdown
    var termSelect = root.querySelector('.quarter-dropdown');
    var termName = termSelect ? termSelect.options[termSelect.selectedIndex].text : 'ClassSchedule';
    var filename = termName.replace(/\s+/g, '_') + '.csv';

    var link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// ── Term Dropdown ─────────────────────────────────────────────────────────────

function classScheduleChangeTerm(select) {
    const url = new URL(window.location.href);
    url.searchParams.set('class_schedule_term', select.value);
    window.location.href = url.toString();
}

// ── Init ──────────────────────────────────────────────────────────────────────

// Apply column visibility — restore any saved choices, then apply
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.class-schedule').forEach(function(root) {
        restoreColumnState(root);
        applyColumnVisibility(root);

        // a11y: attach change listener here instead of inline onchange to avoid jump menu a11y warning
        var quarterDropdown = root.querySelector('.quarter-dropdown');
        if (quarterDropdown) {
            quarterDropdown.addEventListener('change', function() {
                classScheduleChangeTerm(this);
            });
        }
    });
});

// Re-apply column visibility on back/forward navigation.
// Browsers restore checkbox state *after* DOMContentLoaded, so columns can
// get out of sync with the checkboxes when the user navigates back.
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        document.querySelectorAll('.class-schedule').forEach(applyColumnVisibility);
    }
});

// Expose functions used by inline event handlers in the template
window.classScheduleSearch = classScheduleSearch;
window.sortClassSchedule = sortClassSchedule;
window.openFilterModal = openFilterModal;
window.closeFilterModal = closeFilterModal;
window.applyFilters = applyFilters;
window.resetFilters = resetFilters;
window.classScheduleCopyUrl = classScheduleCopyUrl;
window.classScheduleShowCopyToast = classScheduleShowCopyToast;
window.classScheduleDownloadCSV = classScheduleDownloadCSV;
window.classScheduleChangeTerm = classScheduleChangeTerm;

})();
