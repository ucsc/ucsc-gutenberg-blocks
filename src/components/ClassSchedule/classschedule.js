/**
 * Class Schedule - Search and Sort functionality
 */

// Search function
function classScheduleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const container = document.getElementById('classScheduleTable');
    const rows = container.querySelectorAll('.course-row');

    // Get active status filters
    const statusFilters = document.querySelectorAll('.status-filter');
    const activeStatuses = [];
    statusFilters.forEach(filter => {
        if (filter.checked) {
            activeStatuses.push(filter.dataset.status);
        }
    });

    rows.forEach(row => {
        const cols = row.querySelectorAll('.course-col');
        let foundInSearch = false;

        // Check if row matches search term
        for (let j = 0; j < cols.length; j++) {
            const cellText = cols[j].textContent || cols[j].innerText;
            if (cellText.toLowerCase().indexOf(searchTerm) > -1) {
                foundInSearch = true;
                break;
            }
        }

        // Check if row matches status filter
        const rowStatus = row.dataset.status;
        let matchesStatusFilter = activeStatuses.length === 0 || activeStatuses.includes(rowStatus);

        // Show row only if it matches both search and status filter
        row.style.display = (foundInSearch && matchesStatusFilter) ? '' : 'none';
    });
}

// Sort function
let currentSortColumn = -1;
let sortAscending = true;

function sortClassSchedule(columnIndex) {
    const container = document.getElementById('classScheduleTable');
    const body = container.querySelector('.course-list-body');
    const rows = Array.from(body.querySelectorAll('.course-row'));

    // Toggle sort direction if clicking the same column
    if (currentSortColumn === columnIndex) {
        sortAscending = !sortAscending;
    } else {
        sortAscending = true;
        currentSortColumn = columnIndex;
    }

    const dirModifier = sortAscending ? 1 : -1;

    // Sort rows
    const sortedRows = rows.sort((a, b) => {
        const aCols = a.querySelectorAll('.course-col');
        const bCols = b.querySelectorAll('.course-col');
        const aColText = aCols[columnIndex].textContent.trim();
        const bColText = bCols[columnIndex].textContent.trim();

        // Try to parse as numbers for numeric columns
        const aNum = parseFloat(aColText);
        const bNum = parseFloat(bColText);

        if (!isNaN(aNum) && !isNaN(bNum)) {
            return (aNum - bNum) * dirModifier;
        }

        // Otherwise, sort as strings
        return aColText.localeCompare(bColText) * dirModifier;
    });

    // Remove all existing rows
    while (body.firstChild) {
        body.removeChild(body.firstChild);
    }

    // Re-add sorted rows
    body.append(...sortedRows);

    // Update sort indicators
    updateSortIndicators(container, columnIndex, sortAscending);
}

function updateSortIndicators(container, columnIndex, ascending) {
    // Remove all existing sort indicators
    const headers = container.querySelectorAll('.course-list-header .course-col');
    headers.forEach(header => {
        header.classList.remove('sorted-asc', 'sorted-desc');
    });

    // Add indicator to current sorted column
    const currentHeader = headers[columnIndex];
    if (currentHeader) {
        currentHeader.classList.add(ascending ? 'sorted-asc' : 'sorted-desc');
    }
}

// Filter Modal Functions
function openFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.classList.add('active');
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.classList.remove('active');
}

function applyFilters() {
    applyColumnVisibility();
    applyStatusFilters();
    closeFilterModal();
}

function applyColumnVisibility() {
    const toggles = document.querySelectorAll('.column-toggle');
    const columnMap = {
        'subject': 1,
        'course-num': 2,
        'title': 3,
        'type': 4,
        'days': 5,
        'time': 6,
        'location': 7,
        'instructor': 8,
        'seats': 9
    };

    toggles.forEach(toggle => {
        const columnName = toggle.dataset.column;
        const columnIndex = columnMap[columnName];
        const isVisible = toggle.checked;

        // Toggle header column
        const container = document.getElementById('classScheduleTable');
        const headers = container.querySelectorAll('.course-list-header .course-col');
        if (headers[columnIndex]) {
            if (isVisible) {
                headers[columnIndex].classList.remove('hidden');
            } else {
                headers[columnIndex].classList.add('hidden');
            }
        }

        // Toggle data columns in all rows
        const rows = container.querySelectorAll('.course-row');
        rows.forEach(row => {
            const cols = row.querySelectorAll('.course-col');
            if (cols[columnIndex]) {
                if (isVisible) {
                    cols[columnIndex].classList.remove('hidden');
                } else {
                    cols[columnIndex].classList.add('hidden');
                }
            }
        });
    });
}

function applyStatusFilters() {
    const statusFilters = document.querySelectorAll('.status-filter');
    const activeStatuses = [];

    statusFilters.forEach(filter => {
        if (filter.checked) {
            activeStatuses.push(filter.dataset.status);
        }
    });

    const container = document.getElementById('classScheduleTable');
    const rows = container.querySelectorAll('.course-row');

    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        let shouldShow = false;

        // Check if the row's status matches any active filter
        if (activeStatuses.includes(rowStatus)) {
            shouldShow = true;
        }

        // Also respect the search filter
        if (shouldShow) {
            // Check if row is hidden by search
            const currentDisplay = row.style.display;
            if (currentDisplay !== 'none') {
                row.style.display = '';
            }
        } else {
            row.style.display = 'none';
        }
    });
}

function resetFilters() {
    // Reset all column toggles to checked
    const columnToggles = document.querySelectorAll('.column-toggle');
    columnToggles.forEach(toggle => {
        toggle.checked = true;
    });

    // Reset all status filters to checked
    const statusFilters = document.querySelectorAll('.status-filter');
    statusFilters.forEach(filter => {
        filter.checked = true;
    });

    // Apply the reset filters
    applyFilters();

    // Clear search
    const searchInput = document.getElementById('courseSearch');
    if (searchInput) {
        searchInput.value = '';
    }

    // Show all rows
    const container = document.getElementById('classScheduleTable');
    const rows = container.querySelectorAll('.course-row');
    rows.forEach(row => {
        row.style.display = '';
    });
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('filterModal');
    if (event.target === modal) {
        closeFilterModal();
    }
}
