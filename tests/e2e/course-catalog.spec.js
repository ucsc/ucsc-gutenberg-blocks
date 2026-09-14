/**
 * End-to-end test for the Course Catalog block on the live wp-dev.ucsc frontend.
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh, which seeds the target page first.
 *
 * WPM-168: campus-directory and class-schedule each had an e2e spec; this was
 * the missing third. Minimal per the ticket: listing renders, a course row
 * opens its detail view.
 *
 * "Detail view" here is the in-page expand/collapse the block actually does
 * (classes/CourseCatalog.php::theHTML() emits paired <tr class="pointer">
 * (summary) / <tr class="hide"> (description) rows; clicking the summary row
 * toggles "active" on its description sibling — see tablesorter.js). There is
 * no separate detail page/route for this block, unlike class-schedule's
 * /course/{term}/{id}/ template.
 *
 * The interaction test needs real course rows, which depend on the
 * PeopleSoft course feed being reachable from the stack — it self-skips
 * (with a warning) when no rows rendered so a feed outage reads as one clear
 * failure, not two.
 */

const PAGE_URL =
	process.env.UCSC_CC_E2E_URL || 'https://wp-dev.ucsc/course-catalog-e2e/';

let hasRows = false;

describe( 'Course Catalog block (frontend)', () => {
	beforeAll( async () => {
		if ( typeof page !== 'undefined' && page && ! page.removeListener ) {
			page.removeListener = page.off || page.removeEventListener;
		}
		await page.goto( PAGE_URL, { waitUntil: 'networkidle0', timeout: 60000 } );
		hasRows = ( await page.$( '#tableSorter tbody tr.pointer' ) ) !== null;
		if ( ! hasRows ) {
			// eslint-disable-next-line no-console
			console.warn(
				'No course rows rendered (course catalog feed unreachable?) — interaction test self-skips.'
			);
		}
	} );

	it( 'renders the course catalog container', async () => {
		await page.waitForSelector( '#courseCatalog', { timeout: 15000 } );
		expect( await page.$( '#courseCatalog' ) ).not.toBeNull();
	} );

	it( 'renders course rows (requires the course catalog feed)', async () => {
		expect( hasRows ).toBe( true );
	} );

	it( 'clicking a course row opens its detail view', async () => {
		if ( ! hasRows ) return;

		const firstRow = await page.$( '#tableSorter tbody tr.pointer' );
		const detailRow = await page.evaluateHandle(
			( row ) => row.nextElementSibling,
			firstRow
		);

		expect(
			await page.evaluate( ( el ) => el.classList.contains( 'active' ), detailRow )
		).toBe( false );

		await firstRow.click();

		await page.waitForFunction(
			( el ) => el.classList.contains( 'active' ),
			{ timeout: 5000 },
			detailRow
		);

		expect(
			await page.evaluate( ( el ) => el.classList.contains( 'active' ), detailRow )
		).toBe( true );

		const detailText = await page.evaluate(
			( el ) => el.textContent.trim(),
			detailRow
		);
		expect( detailText.length ).toBeGreaterThan( 0 );

		// Click again collapses it back.
		await firstRow.click();
		await page.waitForFunction(
			( el ) => ! el.classList.contains( 'active' ),
			{ timeout: 5000 },
			detailRow
		);
	} );
} );
