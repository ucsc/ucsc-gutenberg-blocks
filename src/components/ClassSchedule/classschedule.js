/**
 * Class Schedule - Search and Sort functionality
 */

// Search function
function classScheduleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();
    const table = document.getElementById('classScheduleTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;

        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().indexOf(searchTerm) > -1) {
                found = true;
                break;
            }
        }

        row.style.display = found ? '' : 'none';
    }
}

// Sort function
let currentSortColumn = -1;
let sortAscending = true;

function sortClassSchedule(columnIndex) {
    const table = document.getElementById('classScheduleTable');
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.querySelectorAll('tr'));

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
        const aColText = a.querySelector(`td:nth-child(${columnIndex + 1})`).textContent.trim();
        const bColText = b.querySelector(`td:nth-child(${columnIndex + 1})`).textContent.trim();

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
    while (tbody.firstChild) {
        tbody.removeChild(tbody.firstChild);
    }

    // Re-add sorted rows
    tbody.append(...sortedRows);

    // Update sort indicators
    updateSortIndicators(table, columnIndex, sortAscending);
}

function updateSortIndicators(table, columnIndex, ascending) {
    // Remove all existing sort indicators
    const headers = table.querySelectorAll('th');
    headers.forEach(header => {
        header.classList.remove('sorted-asc', 'sorted-desc');
    });

    // Add indicator to current sorted column
    const currentHeader = headers[columnIndex];
    if (currentHeader) {
        currentHeader.classList.add(ascending ? 'sorted-asc' : 'sorted-desc');
    }
}
