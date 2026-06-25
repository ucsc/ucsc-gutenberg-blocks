/**
 * End-to-end test for the Class Schedule block on the live wp-dev.ucsc frontend.
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving the host's
 * Google Chrome). Launch options live in jest-puppeteer.config.js at the plugin
 * root. Run via the validate skill's e2e mode, or directly:
 *
 *   npm run test:e2e
 *
 * Prerequisites (handled by `run`): the Docker stack is up, the plugin is
 * active, and a public page containing the block is published. The default
 * target page is the seeded `class-schedule-demo` page (override with
 * CLASS_SCHEDULE_E2E_URL). The block server-renders its table only when the
 * configured department/subject has course data, so the target page must be one
 * whose criterion currently returns classes.
 */

const PAGE_URL =
	process.env.CLASS_SCHEDULE_E2E_URL ||
	'https://wp-dev.ucsc/class-schedule-demo/';

// A row is "visible" when its filter has not hidden it (display:none / detached).
const VISIBLE_ROWS = '#classScheduleTable .el-table__body .el-table__row.course-row';

async function visibleRowCount() {
	return page.$$eval( VISIBLE_ROWS, ( rows ) =>
		rows.filter( ( row ) => row.offsetParent !== null ).length
	);
}

describe( 'Class Schedule block (frontend)', () => {
	beforeAll( async () => {
		await page.goto( PAGE_URL, { waitUntil: 'networkidle0', timeout: 30000 } );
	} );

	it( 'renders the block container server-side', async () => {
		await page.waitForSelector( '#classSchedule', { timeout: 15000 } );
		expect( await page.$( '#classSchedule' ) ).not.toBeNull();
	} );

	it( 'renders the accessible schedule table with a header row and course rows', async () => {
		await page.waitForSelector( '#classScheduleTable', { timeout: 15000 } );

		const tableRole = await page.$eval( '#classScheduleTable', ( el ) =>
			el.getAttribute( 'role' )
		);
		expect( tableRole ).toBe( 'table' );

		const headerCount = await page.$$eval(
			'#classScheduleTable .el-table__header-row [role="columnheader"]',
			( cells ) => cells.length
		);
		expect( headerCount ).toBeGreaterThan( 0 );

		expect( await visibleRowCount() ).toBeGreaterThan( 0 );
	} );

	it( 'exposes the interactive search and quarter controls', async () => {
		expect( await page.$( '#courseSearch' ) ).not.toBeNull();
		expect( await page.$( '#quarterDropdown' ) ).not.toBeNull();
	} );

	it( 'filters rows when typing in the course search (proves view.js ran)', async () => {
		const initial = await visibleRowCount();
		expect( initial ).toBeGreaterThan( 0 );

		// Use the first rendered course id as a query guaranteed to match >=1 row.
		const query = await page.$eval(
			`${ VISIBLE_ROWS } .col-course-id .cell`,
			( el ) => el.textContent.trim()
		);
		expect( query.length ).toBeGreaterThan( 0 );

		await page.click( '#courseSearch', { clickCount: 3 } );
		await page.type( '#courseSearch', query );

		// Let the keyup-driven filter settle.
		await page.waitForFunction(
			( sel ) =>
				Array.from( document.querySelectorAll( sel ) ).some(
					( row ) => row.offsetParent !== null
				),
			{ timeout: 5000 },
			VISIBLE_ROWS
		);

		const filtered = await visibleRowCount();
		expect( filtered ).toBeGreaterThanOrEqual( 1 );
		expect( filtered ).toBeLessThanOrEqual( initial );

		// Type a string that should match nothing, expect the filter to empty the table.
		await page.click( '#courseSearch', { clickCount: 3 } );
		await page.type( '#courseSearch', 'zzz-no-such-course-zzz' );
		await page.waitForFunction(
			( sel ) =>
				Array.from( document.querySelectorAll( sel ) ).every(
					( row ) => row.offsetParent === null
				),
			{ timeout: 5000 },
			VISIBLE_ROWS
		);
		expect( await visibleRowCount() ).toBe( 0 );
	} );
} );
