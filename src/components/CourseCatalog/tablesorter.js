/**
 * Sorts a HTML table.
 *
 * @param {HTMLTableElement} table The table to sort
 * @param {number} column The index of the column to sort
 * @param {boolean} asc Determines if the sorting will be in ascending
 */

function sortTableByColumn(table, column, asc = true) {
    const dirModifier = asc ? 1 : -1;
    const tBody = table.tBodies[0];
    let rows = Array.from(tBody.querySelectorAll("tr"));

    const rowsLinkedToDesc = [];
    for (var i = 0; i < rows.length; i += 2) {
        rowsLinkedToDesc.push({
            row: rows[i],
            description: rows[i+1]
        })
    }

    // Sort each row
    const sortedLinkedRows = rowsLinkedToDesc.sort((a, b) => {
        var special =  a.row.querySelector(`td:nth-child(${ column + 1 }) span`) !== null;
        //if needed, the span can have a type to enable special sorting beyond integer
        if (special) {
            const aColVal = parseInt(a.row.querySelector(`td:nth-child(${ column + 1 }) span`).textContent.trim());
            const bColVal = parseInt(a.row.querySelector(`td:nth-child(${ column + 1 }) span`).textContent.trim());
            return aColVal > bColVal ? (1 * dirModifier) : (-1 * dirModifier);
        } else {
            const aColText = a.row.querySelector(`td:nth-child(${ column + 1 })`).textContent.trim();
            const bColText = b.row.querySelector(`td:nth-child(${ column + 1 })`).textContent.trim();
            return aColText > bColText ? (1 * dirModifier) : (-1 * dirModifier);
        }
    });

    const sortedRows = [];
    for(var i = 0; i <= sortedLinkedRows.length; i++) {
        if (sortedLinkedRows[i]){
            sortedRows.push(sortedLinkedRows[i].row);
            sortedRows.push(sortedLinkedRows[i].description);
        }
    }

    // Remove all existing TRs from the table
    while (tBody.firstChild) {
        // a shange
        tBody.removeChild(tBody.firstChild);
    }

    // Re-add the newly sorted rows
    tBody.append(...sortedRows);

    // Remember how the column is currently sorted
    table.querySelectorAll("th").forEach(th => th.classList.remove("th-sort-asc", "th-sort-desc"));
    table.querySelector(`th:nth-child(${ column + 1})`).classList.toggle("th-sort-asc", asc);
    table.querySelector(`th:nth-child(${ column + 1})`).classList.toggle("th-sort-desc", !asc);
}

document.querySelectorAll(".table-sortable th").forEach((headerCell, headerIndex) => {
    headerCell.addEventListener("click", () => {
        const tableElement = headerCell.parentElement.parentElement.parentElement;
        const currentIsAscending = headerCell.classList.contains("th-sort-asc");
        sortTableByColumn(tableElement, headerIndex, !currentIsAscending);
    });
});

document.querySelectorAll(".table-sortable td").forEach((tableSortable, index) => {
    tableSortable.addEventListener("click", (event) => {
        event.stopPropagation();
        var tr = event.target.closest("tr").nextElementSibling;
        tr.classList.toggle('active');
    });
});

function tableSearch() {
    // Declare variables
    var input, filter, table, tr, td;
    input = document.getElementById("Search");
    filter = input.value.toUpperCase();
    table = document.getElementById("tableSorter");
    tr = table.getElementsByTagName("tr");

    // Loop through all table rows, and hide those who don't match the search query
    for (var i = 1; i < tr.length; i+=2) {
        td = tr[i].getElementsByTagName("td");
        var matched = "none";
        for (var j = 0; j < td.length; j++) {
            if (td[j].innerHTML.toUpperCase().indexOf(filter) > -1) {
                matched = "";
                break;
            }
        }
        if (matched !== "") {
            td = tr[i+1].getElementsByTagName("td");
            for (var j = 0; j < td.length; j++) {
                if (td[j].innerHTML.toUpperCase().indexOf(filter) > -1) {
                    matched = "";
                    break;
                }
            }
        }
        tr[i].style.display = matched;
        tr[i+1].style.display = matched;
    }
}

document.getElementById('expandAll').addEventListener('click', () => {
    document.querySelectorAll('.hidden').forEach((hiddenRow, index) => hiddenRow.classList.add("active"));
});

document.getElementById('collapseAll').addEventListener('click', () => {
    document.querySelectorAll('.hidden').forEach((hiddenRow, index) => hiddenRow.classList.remove("active"));
});
