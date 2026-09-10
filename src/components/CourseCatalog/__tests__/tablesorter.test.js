function courseRow({ subject, number, title, level, levelSort, units, description }) {
  return `
    <tr class="pointer">
      <td>${subject} <span class="intsort">${number}</span></td>
      <td class="collapseExpandText">${title}</td>
      <td>${level}<span class="secret">${levelSort}</span></td>
      <td>${units} Units</td>
    </tr>
    <tr class="hide"><td colspan="4"><p>${description}</p></td></tr>`;
}

const FIXTURE_ROWS = [
  {
    subject: 'CSE',
    number: '20',
    title: 'Beginning Programming',
    level: 'Lower Division',
    levelSort: '1',
    units: '5',
    description: 'Introductory programming course.',
  },
  {
    subject: 'CSE',
    number: '101',
    title: 'Algorithms',
    level: 'Upper Division',
    levelSort: '2',
    units: '5',
    description: 'Algorithm design and analysis.',
  },
  {
    subject: 'CSE',
    number: '200',
    title: 'Research Seminar',
    level: 'Graduate',
    levelSort: '3',
    units: '2',
    description: 'Graduate research topics.',
  },
];

function buildFixture(rows = FIXTURE_ROWS) {
  document.body.innerHTML = `
    <div id="courseCatalog">
      <div class="introText">
        <label>Search Courses:<input type="text" id="search"></label>
      </div>
      <div class="introText clickText">
        <label>
          Select a course title for details.
          <a id="expandAll" class="expandAll collapseExpandLinks pointer">Expand all</a>
          <a id="collapseAll" class="collapseAll collapseExpandLinks pointer">Collapse all</a>
        </label>
      </div>
      <table class="table-sortable" id="tableSorter">
        <thead><tr><th>Course #</th><th>Course Title</th><th>Course Level</th><th>Units</th></tr></thead>
        <tbody>${rows.map(courseRow).join('')}</tbody>
      </table>
    </div>`;
}

function loadScript() {
  jest.resetModules();
  require('../tablesorter');
}

function visibleCourseNumbers() {
  return Array.from(document.querySelectorAll('#tableSorter tbody tr.pointer')).map((row) =>
    row.querySelector('td:first-child span').textContent.trim()
  );
}

function descriptionTexts() {
  return Array.from(document.querySelectorAll('#tableSorter tbody tr.hide')).map((row) =>
    row.textContent.trim()
  );
}

describe('tablesorter.js course catalog frontend', () => {
  beforeEach(() => {
    buildFixture();
    loadScript();
  });

  it('sorts course numbers numerically and keeps description rows paired', () => {
    document.querySelectorAll('#tableSorter th')[0].click();

    expect(visibleCourseNumbers()).toEqual(['20', '101', '200']);
    expect(descriptionTexts()).toEqual([
      'Introductory programming course.',
      'Algorithm design and analysis.',
      'Graduate research topics.',
    ]);
    expect(document.querySelectorAll('#tableSorter th')[0].classList.contains('th-sort-asc')).toBe(true);
  });

  it('toggles the same column to descending on the second click', () => {
    const courseNumberHeader = document.querySelectorAll('#tableSorter th')[0];

    courseNumberHeader.click();
    courseNumberHeader.click();

    expect(visibleCourseNumbers()).toEqual(['200', '101', '20']);
    expect(courseNumberHeader.classList.contains('th-sort-desc')).toBe(true);
  });

  it('sorts text columns alphabetically', () => {
    document.querySelectorAll('#tableSorter th')[1].click();

    expect(visibleCourseNumbers()).toEqual(['101', '20', '200']);
  });

  it('sorts level values by the hidden numeric sort span', () => {
    document.querySelectorAll('#tableSorter th')[2].click();

    expect(visibleCourseNumbers()).toEqual(['20', '101', '200']);
  });

  it('expands and collapses all hidden description rows', () => {
    const hiddenRows = Array.from(document.querySelectorAll('#tableSorter tbody tr.hide'));

    document.getElementById('expandAll').click();
    expect(hiddenRows.every((row) => row.classList.contains('active'))).toBe(true);

    document.getElementById('collapseAll').click();
    expect(hiddenRows.every((row) => row.classList.contains('active'))).toBe(false);
  });

  it('toggles one description row when a course row cell is clicked', () => {
    const firstDescription = document.querySelector('#tableSorter tbody tr.hide');

    document.querySelector('#tableSorter tbody tr.pointer td').click();
    expect(firstDescription.classList.contains('active')).toBe(true);

    document.querySelector('#tableSorter tbody tr.pointer td').click();
    expect(firstDescription.classList.contains('active')).toBe(false);
  });

  it('filters course and description rows from the inline search handler', () => {
    const input = document.getElementById('search');

    input.value = 'algorithm';
    window.tableSearch({ currentTarget: input });

    const rows = Array.from(document.querySelectorAll('#tableSorter tbody tr'));
    expect(rows.map((row) => row.style.display)).toEqual(['none', 'none', '', '', 'none', 'none']);
  });

  it('handles empty tables without throwing', () => {
    buildFixture([]);
    loadScript();

    expect(() => document.querySelector('#tableSorter th').click()).not.toThrow();
    expect(visibleCourseNumbers()).toEqual([]);
  });

  it('handles malformed tables with an unpaired course row without appending undefined text', () => {
    document.body.innerHTML = `
      <table class="table-sortable" id="tableSorter">
        <thead><tr><th>Course #</th><th>Course Title</th><th>Course Level</th><th>Units</th></tr></thead>
        <tbody><tr class="pointer"><td>CSE <span class="intsort">101</span></td><td>Algorithms</td><td>Upper Division<span class="secret">2</span></td><td>5 Units</td></tr></tbody>
      </table>`;
    loadScript();

    expect(() => document.querySelector('#tableSorter th').click()).not.toThrow();
    expect(document.querySelector('#tableSorter tbody').textContent).not.toContain('undefined');
    expect(visibleCourseNumbers()).toEqual(['101']);
  });
});
