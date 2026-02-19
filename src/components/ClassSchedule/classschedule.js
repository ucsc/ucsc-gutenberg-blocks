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

// ── Search ────────────────────────────────────────────────────────────────────

function classScheduleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const rows = document.querySelectorAll('#classScheduleTable .course-row');

    const activeStatuses = getActiveStatuses();

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
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

    const tbody = document.querySelector('#classScheduleTable .el-table__body tbody');
    const rows  = Array.from(tbody.querySelectorAll('.course-row'));
    const dir   = sortAscending ? 1 : -1;

    rows.sort((a, b) => {
        const aCells = a.querySelectorAll('td');
        const bCells = b.querySelectorAll('td');
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
    document.querySelectorAll('#classScheduleTable .el-table__header th').forEach((th, i) => {
        th.classList.remove('ascending', 'descending');
        if (i === columnIndex) {
            th.classList.add(ascending ? 'ascending' : 'descending');
        }
    });
}

// ── Filter Modal ──────────────────────────────────────────────────────────────

function openFilterModal() {
    document.getElementById('filterModal').classList.add('active');
}

function closeFilterModal() {
    document.getElementById('filterModal').classList.remove('active');
}

function applyFilters() {
    applyColumnVisibility();
    applyStatusFilters();
    closeFilterModal();
}

// Map data-column values to their 0-based td/th index in each row
const columnMap = {
    'seats':      3,
    'days':       4,
    'time':       5,
    'location':   6,
    'instructor': 7,
    'class-num':  8,
    'enrollment': 9
};

function applyColumnVisibility() {
    const table = document.getElementById('classScheduleTable');

    document.querySelectorAll('.column-toggle').forEach(toggle => {
        const colIndex = columnMap[toggle.dataset.column];
        if (colIndex === undefined) return;

        const isVisible = toggle.checked;

        // Toggle header th
        const headerCells = table.querySelectorAll('.el-table__header th');
        if (headerCells[colIndex]) {
            headerCells[colIndex].classList.toggle('hidden', !isVisible);
        }

        // Toggle body td in every row
        table.querySelectorAll('.course-row').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells[colIndex]) {
                cells[colIndex].classList.toggle('hidden', !isVisible);
            }
        });
    });
}

function applyStatusFilters() {
    const activeStatuses = getActiveStatuses();
    const searchTerm = (document.getElementById('courseSearch')?.value || '').toLowerCase();

    document.querySelectorAll('#classScheduleTable .course-row').forEach(row => {
        const matchesStatus = activeStatuses.length === 0 || activeStatuses.includes(row.dataset.status);

        // Re-check search too so both filters stay in sync
        const cells = row.querySelectorAll('td');
        let matchesSearch = !searchTerm;
        if (searchTerm) {
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(searchTerm)) matchesSearch = true;
            });
        }

        row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
    });
}

function resetFilters() {
    document.querySelectorAll('.column-toggle').forEach(t => t.checked = true);
    document.querySelectorAll('.status-filter').forEach(f => f.checked = true);

    const searchInput = document.getElementById('courseSearch');
    if (searchInput) searchInput.value = '';

    applyColumnVisibility();
    applyStatusFilters();
}

// Close modal when clicking the backdrop
window.addEventListener('click', function(event) {
    const modal = document.getElementById('filterModal');
    if (event.target === modal) closeFilterModal();
});

// ── Copy URL ──────────────────────────────────────────────────────────────────

function classScheduleCopyUrl() {
    var url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        classScheduleShowToast('<strong>Copied </strong><i>' + url + '</i>');
    }, function() {
        // Fallback for older browsers
        var textArea = document.createElement('textarea');
        textArea.value = url;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        classScheduleShowToast('<strong>Copied </strong><i>' + url + '</i>');
    });
}

function classScheduleShowToast(html) {
    var toast = document.createElement('div');
    toast.className = 'cs-toast';
    toast.innerHTML = html;
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
    var headerCells = table.querySelectorAll('.el-table__header th');
    var rows = table.querySelectorAll('.el-table__body .course-row');

    // Determine which columns are visible
    var visibleCols = [];
    headerCells.forEach(function(th, i) {
        if (!th.classList.contains('hidden') && i > 0) { // skip status column
            visibleCols.push({
                index: i,
                label: th.textContent.trim()
            });
        }
    });

    // Build CSV header
    var csvRows = [];
    csvRows.push(visibleCols.map(function(c) { return '"' + c.label + '"'; }).join(','));

    // Build CSV data rows (only visible/filtered rows)
    rows.forEach(function(row) {
        if (row.style.display === 'none') return; // skip filtered-out rows

        var cells = row.querySelectorAll('td');
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

// Apply default column visibility (time/location/instructor are unchecked by default)
document.addEventListener('DOMContentLoaded', function() {
    applyColumnVisibility();
});
