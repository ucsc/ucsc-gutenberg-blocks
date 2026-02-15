/**
 * Class Schedule - Search and Sort functionality
 */

// Search function
function classScheduleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const container = document.getElementById('classScheduleTable');
    const rows = container.querySelectorAll('.course-row');

    rows.forEach(row => {
        const cols = row.querySelectorAll('.course-col');
        let found = false;

        for (let j = 0; j < cols.length; j++) {
            const cellText = cols[j].textContent || cols[j].innerText;
            if (cellText.toLowerCase().indexOf(searchTerm) > -1) {
                found = true;
                break;
            }
        }

        row.style.display = found ? '' : 'none';
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
