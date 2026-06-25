/**
 * End-to-end test for the Course Catalog block on the live wp-dev.ucsc frontend.
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving the host's
 * Google Chrome). Launch options live in jest-puppeteer.config.js at the plugin
 * root. Run via the validate skill's e2e mode, or directly:
 *
 *   npm run test:e2e
 *
 * Prerequisites (handled by `run`): the Docker stack is up, the plugin is
 * active, and a public page containing the block is published. The default
 * target page is the seeded `course-catalog-page` page (override with
 * COURSE_CATALOG_E2E_URL).
 */

const PAGE_URL =
	process.env.COURSE_CATALOG_E2E_URL ||
	'https://wp-dev.ucsc/course-catalog-page/';

// A course row is the visible course list row.
const COURSE_ROWS = '#tableSorter tbody tr.pointer';

async function visibleRowCount() {
	return page.$$eval( COURSE_ROWS, ( rows ) =>
		rows.filter( ( row ) => row.offsetParent !== null ).length
	);
}

describe( 'Course Catalog block (frontend)', () => {
	beforeAll( async () => {
		await page.goto( PAGE_URL, { waitUntil: 'networkidle0', timeout: 30000 } );
	} );

	it( 'renders the block container server-side', async () => {
		await page.waitForSelector( '#courseCatalog', { timeout: 15000 } );
		expect( await page.$( '#courseCatalog' ) ).not.toBeNull();
	} );

	it( 'renders the course catalog table with headers and data rows', async () => {
		await page.waitForSelector( '#tableSorter', { timeout: 15000 } );

		const headerCount = await page.$$eval(
			'#tableSorter thead th',
			( headers ) => headers.length
		);
		expect( headerCount ).toBe( 4 );

		expect( await visibleRowCount() ).toBeGreaterThan( 0 );
	} );

	it( 'exposes the interactive search and expand/collapse links', async () => {
		expect( await page.$( '#search' ) ).not.toBeNull();
		expect( await page.$( '#expandAll' ) ).not.toBeNull();
		expect( await page.$( '#collapseAll' ) ).not.toBeNull();
	} );

	it( 'toggles the detail row when clicking a course row', async () => {
		// Verify first description row is not active initially
		const firstDetailSelector = '#tableSorter tbody tr.hide';
		await page.waitForSelector( firstDetailSelector );
		
		let isActive = await page.$eval( firstDetailSelector, ( el ) =>
			el.classList.contains( 'active' )
		);
		expect( isActive ).toBe( false );

		// Click the first course cell (td) in the first pointer row
		const firstCellSelector = '#tableSorter tbody tr.pointer td';
		await page.click( firstCellSelector );

		// Expect it to become active
		isActive = await page.$eval( firstDetailSelector, ( el ) =>
			el.classList.contains( 'active' )
		);
		expect( isActive ).toBe( true );

		// Click again to toggle off
		await page.click( firstCellSelector );
		isActive = await page.$eval( firstDetailSelector, ( el ) =>
			el.classList.contains( 'active' )
		);
		expect( isActive ).toBe( false );
	} );

	it( 'filters rows when typing in the course search (proves tablesorter.js search ran)', async () => {
		const initial = await visibleRowCount();
		expect( initial ).toBeGreaterThan( 0 );

		// Use the first course title as a query guaranteed to match at least one row
		const query = await page.$eval(
			'#tableSorter tbody tr.pointer td.collapseExpandText',
			( el ) => el.textContent.trim()
		);
		expect( query.length ).toBeGreaterThan( 0 );

		await page.click( '#search', { clickCount: 3 } );
		await page.type( '#search', query );

		// Wait for search to filter
		await page.waitForFunction(
			( sel ) =>
				Array.from( document.querySelectorAll( sel ) ).some(
					( row ) => row.offsetParent !== null
				),
			{ timeout: 5000 },
			COURSE_ROWS
		);

		const filtered = await visibleRowCount();
		expect( filtered ).toBeGreaterThanOrEqual( 1 );
		expect( filtered ).toBeLessThanOrEqual( initial );

		// Type a non-matching query and expect 0 visible rows
		await page.click( '#search', { clickCount: 3 } );
		await page.type( '#search', 'zzz-no-such-course-zzz' );
		await page.waitForFunction(
			( sel ) =>
				Array.from( document.querySelectorAll( sel ) ).every(
					( row ) => row.offsetParent === null
				),
			{ timeout: 5000 },
			COURSE_ROWS
		);
		expect( await visibleRowCount() ).toBe( 0 );
	} );
} );
