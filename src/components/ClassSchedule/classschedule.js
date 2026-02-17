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
    'instructor': 7
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

// ── Init ──────────────────────────────────────────────────────────────────────

// Apply default column visibility (time/location/instructor are unchecked by default)
document.addEventListener('DOMContentLoaded', function() {
    applyColumnVisibility();
});
