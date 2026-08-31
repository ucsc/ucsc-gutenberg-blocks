/**
 * Tests for the Class Schedule frontend script (search, sort, filter modal,
 * column visibility, CSV export) against a DOM fixture mirroring
 * templates/ClassScheduleTemplate.php.
 */

function headerCell(cls, label, { sortable = true, hidden = false } = {}) {
  const classes = [cls, sortable && 'is-sortable', hidden && 'hidden'].filter(Boolean).join(' ');
  const tabindex = hidden ? ' tabindex="-1"' : '';
  return `<div class="${classes}" role="columnheader"><button type="button" class="cell"${tabindex}>${label}</button></div>`;
}

function rowHTML({ status, courseId, title, seats, days, time, location, instructor, classNum, enrollment }) {
  return `
    <div class="el-table__row course-row" role="row" data-status="${status}">
      <div class="col-status" role="cell"><div class="cell"><span class="${status}"></span></div></div>
      <div class="col-course-id" role="cell"><div class="cell"><span>${courseId}</span></div></div>
      <div class="col-title" role="cell"><div class="cell"><a href="#">${title}</a></div></div>
      <div class="col-seats" role="cell"><div class="cell"><span>${seats}</span></div></div>
      <div class="col-days" role="cell"><div class="cell"><span>${days}</span></div></div>
      <div class="col-time hidden" role="cell"><div class="cell"><span>${time}</span></div></div>
      <div class="col-location hidden" role="cell"><div class="cell"><span>${location}</span></div></div>
      <div class="col-instructor hidden" role="cell"><div class="cell"><span>${instructor}</span></div></div>
      <div class="col-class-num hidden" role="cell"><div class="cell"><span>${classNum}</span></div></div>
      <div class="col-enrollment hidden" role="cell"><div class="cell"><span>${enrollment}</span></div></div>
    </div>`;
}

const FIXTURE_ROWS = [
  {
    status: 'open',
    courseId: 'CSE-20',
    title: 'Beginning Programming',
    seats: '80 open / 100 total',
    days: 'MWF',
    time: '10:40 AM - 11:45 AM',
    location: 'Kresge 1',
    instructor: 'Ada Lovelace',
    classNum: '54321',
    enrollment: '20',
  },
  {
    status: 'closed',
    courseId: 'CSE-101',
    title: 'Algo "Advanced"',
    seats: '0 open / 50 total',
    days: 'TTh',
    time: '1:20 PM - 2:55 PM',
    location: 'Engineering 2 192',
    instructor: 'Grace Hopper',
    classNum: '12345',
    enrollment: '50',
  },
  {
    status: 'waitlist',
    courseId: 'CSE-150',
    title: 'Compilers',
    seats: '5 open / 30 total',
    days: 'MW',
    time: '9:00 AM - 10:35 AM',
    location: 'Baskin Auditorium',
    instructor: 'Staff',
    classNum: '33333',
    enrollment: '25',
  },
];

function buildFixture() {
  document.body.innerHTML = `
    <div id="classSchedule">
      <select id="quarterDropdown">
        <option value="2260">Summer 2026</option>
        <option value="2262" selected>Fall 2026</option>
      </select>
      <input type="text" id="courseSearch">
      <div id="filterModal" class="filter-modal">
        <label><input type="checkbox" class="column-toggle" data-column="seats" checked> Seats</label>
        <label><input type="checkbox" class="column-toggle" data-column="days" checked> Days</label>
        <label><input type="checkbox" class="column-toggle" data-column="time"> Time</label>
        <label><input type="checkbox" class="column-toggle" data-column="location"> Location</label>
        <label><input type="checkbox" class="column-toggle" data-column="instructor"> Instructor</label>
        <label><input type="checkbox" class="column-toggle" data-column="class-num"> Class #</label>
        <label><input type="checkbox" class="column-toggle" data-column="enrollment"> Enrollment</label>
        <label><input type="checkbox" class="status-filter" data-status="open" checked> Open</label>
        <label><input type="checkbox" class="status-filter" data-status="closed" checked> Closed</label>
        <label><input type="checkbox" class="status-filter" data-status="waitlist" checked> Wait List</label>
        <button class="apply-button">Apply</button>
      </div>
      <div id="classCount" aria-live="polite">Displaying <strong>3</strong> classes</div>
      <div class="el-table" id="classScheduleTable" role="table">
        <div class="el-table__header" role="rowgroup">
          <div class="el-table__header-row" role="row">
            <div class="col-status" role="columnheader"><div class="cell">Status</div></div>
            ${headerCell('col-course-id', 'Course ID')}
            ${headerCell('col-title', 'Title')}
            ${headerCell('col-seats', 'Seats')}
            ${headerCell('col-days', 'Days')}
            ${headerCell('col-time', 'Time', { hidden: true })}
            ${headerCell('col-location', 'Location', { hidden: true })}
            ${headerCell('col-instructor', 'Instructor', { hidden: true })}
            ${headerCell('col-class-num', 'Class #', { hidden: true })}
            ${headerCell('col-enrollment', 'Enrollment', { hidden: true })}
          </div>
        </div>
        <div class="el-table__body" role="rowgroup">
          ${FIXTURE_ROWS.map(rowHTML).join('')}
        </div>
      </div>
    </div>`;
}

function visibleCourseIds() {
  return Array.from(document.querySelectorAll('.course-row'))
    .filter((row) => row.style.display !== 'none')
    .map((row) => row.querySelector('.col-course-id').textContent.trim());
}

function bodyCourseIds() {
  return Array.from(document.querySelectorAll('.el-table__body .course-row')).map((row) =>
    row.querySelector('.col-course-id').textContent.trim()
  );
}

function classCountText() {
  return document.getElementById('classCount').textContent;
}

function readBlob(blob) {
  return new Promise((resolve) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.readAsText(blob);
  });
}

describe('classschedule.js frontend', () => {
  beforeEach(() => {
    jest.resetModules();
    sessionStorage.clear();
    buildFixture();
    require('../classschedule');
  });

  describe('search', () => {
    it('filters rows by title and updates the live count', () => {
      window.classScheduleSearch({ target: { value: 'compilers' } });
      expect(visibleCourseIds()).toEqual(['CSE-150']);
      expect(classCountText()).toContain('1');
    });

    it('matches course id, location, instructor, class number, and status', () => {
      window.classScheduleSearch({ target: { value: 'cse-20' } });
      expect(visibleCourseIds()).toEqual(['CSE-20']);

      window.classScheduleSearch({ target: { value: 'kresge' } });
      expect(visibleCourseIds()).toEqual(['CSE-20']);

      window.classScheduleSearch({ target: { value: 'hopper' } });
      expect(visibleCourseIds()).toEqual(['CSE-101']);

      window.classScheduleSearch({ target: { value: '33333' } });
      expect(visibleCourseIds()).toEqual(['CSE-150']);

      window.classScheduleSearch({ target: { value: 'waitlist' } });
      expect(visibleCourseIds()).toEqual(['CSE-150']);
    });

    it('shows every row again when the search is cleared', () => {
      window.classScheduleSearch({ target: { value: 'compilers' } });
      window.classScheduleSearch({ target: { value: '' } });
      expect(visibleCourseIds()).toEqual(['CSE-20', 'CSE-101', 'CSE-150']);
      expect(classCountText()).toContain('3');
    });

    it('respects active status filters while searching', () => {
      document.querySelector('.status-filter[data-status="open"]').checked = false;
      window.classScheduleSearch({ target: { value: 'cse' } });
      expect(visibleCourseIds()).toEqual(['CSE-101', 'CSE-150']);
    });
  });

  describe('status filters', () => {
    it('hides rows whose status is unchecked and re-runs the search', () => {
      document.getElementById('courseSearch').value = 'cse';
      document.querySelector('.status-filter[data-status="closed"]').checked = false;
      document.querySelector('.status-filter[data-status="waitlist"]').checked = false;
      window.applyFilters();
      expect(visibleCourseIds()).toEqual(['CSE-20']);
      expect(classCountText()).toContain('1');
    });
  });

  describe('sort', () => {
    it('sorts text columns ascending, then toggles to descending', () => {
      window.sortClassSchedule(1);
      expect(bodyCourseIds()).toEqual(['CSE-101', 'CSE-150', 'CSE-20']);

      window.sortClassSchedule(1);
      expect(bodyCourseIds()).toEqual(['CSE-20', 'CSE-150', 'CSE-101']);
    });

    it('sorts the seats column numerically by open seats', () => {
      window.sortClassSchedule(3);
      expect(bodyCourseIds()).toEqual(['CSE-101', 'CSE-150', 'CSE-20']);
    });

    it('marks only the sorted column with aria-sort', () => {
      window.sortClassSchedule(2);
      const headers = document.querySelectorAll('.el-table__header-row > [role="columnheader"]');
      expect(headers[2].getAttribute('aria-sort')).toBe('ascending');
      expect(headers[2].classList.contains('ascending')).toBe(true);
      expect(headers[1].hasAttribute('aria-sort')).toBe(false);

      window.sortClassSchedule(2);
      expect(headers[2].getAttribute('aria-sort')).toBe('descending');
    });
  });

  describe('filter modal and column visibility', () => {
    it('applies column toggles to header and body cells and the grid template', () => {
      document.querySelector('.column-toggle[data-column="days"]').checked = false;
      document.querySelector('.column-toggle[data-column="time"]').checked = true;
      window.applyFilters();

      document.querySelectorAll('#classScheduleTable .col-days').forEach((cell) => {
        expect(cell.classList.contains('hidden')).toBe(true);
      });
      document.querySelectorAll('#classScheduleTable .col-time').forEach((cell) => {
        expect(cell.classList.contains('hidden')).toBe(false);
      });

      const grid = document.querySelector('.el-table__header-row').style.gridTemplateColumns;
      expect(grid).toBe(
        '45px minmax(108px, 1.35fr) minmax(175px, 2.19fr) minmax(145px, 1.81fr) 0px minmax(150px, 1.88fr) 0px 0px 0px 0px'
      );
    });

    it('moves keyboard focus stops off hidden sortable headers', () => {
      document.querySelector('.column-toggle[data-column="days"]').checked = false;
      document.querySelector('.column-toggle[data-column="time"]').checked = true;
      window.applyFilters();

      expect(document.querySelector('.col-days.is-sortable button').getAttribute('tabindex')).toBe('-1');
      expect(document.querySelector('.col-time.is-sortable button').getAttribute('tabindex')).toBe('0');
    });

    it('persists applied column choices to sessionStorage', () => {
      document.querySelector('.column-toggle[data-column="instructor"]').checked = true;
      window.applyFilters();
      const saved = JSON.parse(sessionStorage.getItem('cs_columns'));
      expect(saved.instructor).toBe(true);
      expect(saved.time).toBe(false);
      expect(document.getElementById('filterModal').classList.contains('active')).toBe(false);
    });

    it('restores the pre-open checkbox states on cancel (Escape)', () => {
      window.openFilterModal();
      const days = document.querySelector('.column-toggle[data-column="days"]');
      const open = document.querySelector('.status-filter[data-status="open"]');
      days.checked = false;
      open.checked = false;

      document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));

      expect(days.checked).toBe(true);
      expect(open.checked).toBe(true);
      expect(document.getElementById('filterModal').classList.contains('active')).toBe(false);
    });

    it('resets columns to defaults, statuses on, and clears search', () => {
      document.querySelectorAll('.column-toggle').forEach((t) => (t.checked = true));
      document.querySelector('.status-filter[data-status="open"]').checked = false;
      document.getElementById('courseSearch').value = 'algo';
      sessionStorage.setItem('cs_columns', '{"time":true}');

      window.resetFilters();

      const checked = Array.from(document.querySelectorAll('.column-toggle'))
        .filter((t) => t.checked)
        .map((t) => t.dataset.column);
      expect(checked).toEqual(['seats', 'days']);
      document.querySelectorAll('.status-filter').forEach((f) => expect(f.checked).toBe(true));
      expect(document.getElementById('courseSearch').value).toBe('');
    });

    // WPM-112: "Reset all filters" cleared the search box's value but never
    // re-ran the row filter, so the table stayed hidden by a search term the
    // user could no longer see and the aria-live count lied about it.
    it('un-hides rows and refreshes the live count when reset clears an active search', () => {
      window.classScheduleSearch({ target: { value: 'compilers' } });
      expect(visibleCourseIds()).toEqual(['CSE-150']);

      window.resetFilters();

      expect(visibleCourseIds()).toEqual(['CSE-20', 'CSE-101', 'CSE-150']);
      expect(classCountText()).toContain('3');
    });

    it('un-hides rows filtered out by status when reset re-checks all statuses', () => {
      document.querySelector('.status-filter[data-status="closed"]').checked = false;
      window.applyFilters();
      expect(visibleCourseIds()).toEqual(['CSE-20', 'CSE-150']);

      window.resetFilters();

      expect(visibleCourseIds()).toEqual(['CSE-20', 'CSE-101', 'CSE-150']);
      expect(classCountText()).toContain('3');
    });

    it('restores saved column choices on DOMContentLoaded', () => {
      sessionStorage.setItem('cs_columns', JSON.stringify({ time: true, seats: false }));
      document.dispatchEvent(new Event('DOMContentLoaded'));

      expect(document.querySelector('.column-toggle[data-column="time"]').checked).toBe(true);
      expect(document.querySelector('.column-toggle[data-column="seats"]').checked).toBe(false);
      expect(document.querySelector('.col-time.is-sortable').classList.contains('hidden')).toBe(false);
      document.querySelectorAll('#classScheduleTable .col-seats').forEach((cell) => {
        expect(cell.classList.contains('hidden')).toBe(true);
      });
    });
  });

  describe('CSV export', () => {
    let capturedBlob;

    beforeEach(() => {
      capturedBlob = null;
      URL.createObjectURL = jest.fn((blob) => {
        capturedBlob = blob;
        return 'blob:mock';
      });
      URL.revokeObjectURL = jest.fn();
      jest.spyOn(HTMLAnchorElement.prototype, 'click').mockImplementation(() => {});
    });

    it('exports only visible columns and rows, with CSV quoting', async () => {
      window.classScheduleSearch({ target: { value: 'cse-101' } });
      window.classScheduleDownloadCSV();

      const csv = await readBlob(capturedBlob);
      const lines = csv.split('\n');
      expect(lines[0]).toBe('"Course ID","Title","Seats","Days"');
      expect(lines).toHaveLength(2);
      expect(lines[1]).toBe('"CSE-101","Algo ""Advanced""","0 open / 50 total","TTh"');
    });

    it('names the file after the selected term', () => {
      const anchors = [];
      const originalCreate = document.createElement.bind(document);
      jest.spyOn(document, 'createElement').mockImplementation((tag) => {
        const el = originalCreate(tag);
        if (tag === 'a') anchors.push(el);
        return el;
      });

      window.classScheduleDownloadCSV();

      expect(anchors).toHaveLength(1);
      expect(anchors[0].download).toBe('Fall_2026.csv');
      expect(URL.revokeObjectURL).toHaveBeenCalledWith('blob:mock');
    });
  });
});
