/**
 * Class Schedule — Search, Sort, and Filter functionality
 *
 * Table structure (column indices used by sort):
 *   0  col-status     (always visible)
 *   1  col-course-id  (always visible)
 *   2  col-title      (always visible)
 *   3  col-seats      (toggleable, default on)
 *   4  col-days       (toggleable, default on)
 *   5  col-time       (toggleable, default off)
 *   6  col-location   (toggleable, default off)
 *   7  col-instructor (toggleable, default off)
 *   8  col-class-num  (toggleable, default off)
 *   9  col-enrollment (toggleable, default off)
 */

// Wrap classschedule.js in IIFE to avoid global scope pollution
(function() {
'use strict';

// ── A11Y: Live count update (aria-live region) ───────────────────────────────

function updateClassCount() {
    var rows = document.querySelectorAll('#classScheduleTable .course-row');
    var visible = 0;
    rows.forEach(function(row) {
        if (row.style.display !== 'none') visible++;
    });
    var el = document.getElementById('classCount');
    if (el) el.innerHTML = 'Displaying <strong>' + visible + '</strong> classes';
}

// ── Search ────────────────────────────────────────────────────────────────────

function classScheduleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const rows = document.querySelectorAll('#classScheduleTable .course-row');

    const activeStatuses = getActiveStatuses();

    rows.forEach(row => {
        const cells = row.querySelectorAll('[role="cell"]');
        let matchesSearch = false;

        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(searchTerm)) {
                matchesSearch = true;
            }
        });

        const rowStatus = row.dataset.status;
        const matchesStatus = activeStatuses.length === 0 || activeStatuses.includes(rowStatus);

        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });

    updateClassCount();
}

function getActiveStatuses() {
    const activeStatuses = [];
    document.querySelectorAll('.status-filter').forEach(filter => {
        if (filter.checked) activeStatuses.push(filter.dataset.status);
    });
    return activeStatuses;
}

// ── Sort ──────────────────────────────────────────────────────────────────────

let currentSortColumn = -1;
let sortAscending = true;

function sortClassSchedule(columnIndex) {
    if (currentSortColumn === columnIndex) {
        sortAscending = !sortAscending;
    } else {
        sortAscending = true;
        currentSortColumn = columnIndex;
    }

    const tbody = document.querySelector('#classScheduleTable .el-table__body');
    const rows  = Array.from(tbody.querySelectorAll('.course-row'));
    const dir   = sortAscending ? 1 : -1;

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

    updateSortIndicators(columnIndex, sortAscending);
}

function updateSortIndicators(columnIndex, ascending) {
    // columnIndex matches the column position (0 = status, 1 = course-id, etc.)
    document.querySelectorAll('#classScheduleTable .el-table__header-row > [role="columnheader"]').forEach((col, i) => {
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

// Saved checkbox states so Cancel can restore them
let savedColumnStates = {};
let savedStatusStates = {};

function openFilterModal() {
    // Snapshot current checkbox states before the user makes changes
    savedColumnStates = {};
    document.querySelectorAll('.column-toggle').forEach(t => {
        savedColumnStates[t.dataset.column] = t.checked;
    });
    savedStatusStates = {};
    document.querySelectorAll('.status-filter').forEach(f => {
        savedStatusStates[f.dataset.status] = f.checked;
    });

    // A11Y: remember which element opened the modal so we can restore focus
    filterModalOpener = document.activeElement;

    var modal = document.getElementById('filterModal');
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
    var modal = document.getElementById('filterModal');
    if (!modal.classList.contains('active')) return;

    if (event.key === 'Escape') {
        event.preventDefault();
        closeFilterModal();
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

function closeFilterModal() {
    // Restore checkbox states to what they were when the modal opened
    document.querySelectorAll('.column-toggle').forEach(t => {
        if (savedColumnStates.hasOwnProperty(t.dataset.column)) {
            t.checked = savedColumnStates[t.dataset.column];
        }
    });
    document.querySelectorAll('.status-filter').forEach(f => {
        if (savedStatusStates.hasOwnProperty(f.dataset.status)) {
            f.checked = savedStatusStates[f.dataset.status];
        }
    });

    document.getElementById('filterModal').classList.remove('active');

    // A11Y: remove focus trap handler and restore focus to opener
    document.removeEventListener('keydown', filterModalKeyHandler);
    if (filterModalOpener) {
        filterModalOpener.focus();
        filterModalOpener = null;
    }
}

function applyFilters() {
    applyColumnVisibility();
    applyStatusFilters();

    // Update saved states so Cancel reflects the newly applied state
    document.querySelectorAll('.column-toggle').forEach(t => {
        savedColumnStates[t.dataset.column] = t.checked;
    });
    document.querySelectorAll('.status-filter').forEach(f => {
        savedStatusStates[f.dataset.status] = f.checked;
    });

    saveColumnState();

    document.getElementById('filterModal').classList.remove('active');

    // A11Y: remove focus trap handler and restore focus to opener
    document.removeEventListener('keydown', filterModalKeyHandler);
    if (filterModalOpener) {
        filterModalOpener.focus();
        filterModalOpener = null;
    }
}

// Default checked columns (matches the original Vue app defaults)
const defaultColumns = ['seats', 'days'];

// Persist column visibility choices in sessionStorage so they survive
// navigation (e.g. clicking an instructor link and pressing Back).
function saveColumnState() {
    var state = {};
    document.querySelectorAll('.column-toggle').forEach(function(t) {
        state[t.dataset.column] = t.checked;
    });
    try { sessionStorage.setItem('cs_columns', JSON.stringify(state)); } catch(e) { /* ignore */ }
}

function restoreColumnState() {
    try {
        var saved = sessionStorage.getItem('cs_columns');
        if (!saved) return;
        var state = JSON.parse(saved);
        document.querySelectorAll('.column-toggle').forEach(function(t) {
            if (state.hasOwnProperty(t.dataset.column)) {
                t.checked = state[t.dataset.column];
            }
        });
    } catch(e) { /* ignore */ }
}

function applyColumnVisibility() {
    const table = document.getElementById('classScheduleTable');

    document.querySelectorAll('.column-toggle').forEach(toggle => {
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

    updateGridTemplate();
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

function updateGridTemplate() {
    var table = document.getElementById('classScheduleTable');
    var gridCols = gridColumnDefs.map(function(col) {
        var sample = table.querySelector('.' + col.cls);
        return (sample && sample.classList.contains('hidden')) ? '0px' : col.width;
    }).join(' ');

    table.querySelectorAll('.el-table__header-row, .el-table__row').forEach(function(row) {
        row.style.gridTemplateColumns = gridCols;
    });
}

function applyStatusFilters() {
    const activeStatuses = getActiveStatuses();
    const searchTerm = (document.getElementById('courseSearch')?.value || '').toLowerCase();

    document.querySelectorAll('#classScheduleTable .course-row').forEach(row => {
        const matchesStatus = activeStatuses.length === 0 || activeStatuses.includes(row.dataset.status);

        // Re-check search too so both filters stay in sync
        const cells = row.querySelectorAll('[role="cell"]');
        let matchesSearch = !searchTerm;
        if (searchTerm) {
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchTerm)) matchesSearch = true;
            });
        }

        row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
    });

    updateClassCount();
}

function resetFilters() {
    // Reset columns to defaults (only Seats and Days checked, matching original Vue app)
    document.querySelectorAll('.column-toggle').forEach(t => {
        t.checked = defaultColumns.includes(t.dataset.column);
    });
    document.querySelectorAll('.status-filter').forEach(f => f.checked = true);

    const searchInput = document.getElementById('courseSearch');
    if (searchInput) searchInput.value = '';

    try { sessionStorage.removeItem('cs_columns'); } catch(e) { /* ignore */ }
}

// Close modal when clicking the backdrop
window.addEventListener('click', function(event) {
    const modal = document.getElementById('filterModal');
    if (event.target === modal) closeFilterModal();
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

function classScheduleDownloadCSV() {
    var table = document.getElementById('classScheduleTable');
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
    var termSelect = document.getElementById('quarterDropdown');
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
    restoreColumnState();
    applyColumnVisibility();

    // a11y: attach change listener here instead of inline onchange to avoid jump menu a11y warning
    var quarterDropdown = document.getElementById('quarterDropdown');
    if (quarterDropdown) {
        quarterDropdown.addEventListener('change', function() {
            classScheduleChangeTerm(this);
        });
    }
});

// Re-apply column visibility on back/forward navigation.
// Browsers restore checkbox state *after* DOMContentLoaded, so columns can
// get out of sync with the checkboxes when the user navigates back.
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        applyColumnVisibility();
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
window.classScheduleDownloadCSV = classScheduleDownloadCSV;

})();
