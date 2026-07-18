/**
 * End-to-end test for the Class Schedule block on the live wp-dev.ucsc frontend.
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh, which seeds the target page first.
 *
 * The container and count tests are strict. The interaction tests need real
 * course rows, which depend on the courses REST API being reachable from the
 * stack — they self-skip (with a warning) when no rows rendered so an API
 * outage reads as one clear failure, not five.
 */

const PAGE_URL =
	process.env.UCSC_CS_E2E_URL || 'https://wp-dev.ucsc/class-schedule-e2e/';

let hasRows = false;

describe( 'Class Schedule block (frontend)', () => {
	beforeAll( async () => {
		if ( typeof page !== 'undefined' && page && ! page.removeListener ) {
			page.removeListener = page.off || page.removeEventListener;
		}
		await page.goto( PAGE_URL, { waitUntil: 'networkidle0', timeout: 60000 } );
		hasRows = ( await page.$( '#classScheduleTable .course-row' ) ) !== null;
		if ( ! hasRows ) {
			// eslint-disable-next-line no-console
			console.warn(
				'No course rows rendered (courses API unreachable?) — interaction tests self-skip.'
			);
		}
	} );

	it( 'renders the schedule container', async () => {
		await page.waitForSelector( '#classSchedule', { timeout: 15000 } );
		expect( await page.$( '#classSchedule' ) ).not.toBeNull();
	} );

	it( 'renders course rows (requires the courses REST API)', async () => {
		expect( hasRows ).toBe( true );
	} );

	it( 'search narrows the rows and the aria-live count', async () => {
		if ( ! hasRows ) return;

		await page.type( '#courseSearch', 'zzz-no-such-course' );
		await page.waitForFunction(
			() =>
				Array.from(
					document.querySelectorAll( '#classScheduleTable .course-row' )
				).every( ( row ) => row.style.display === 'none' ),
			{ timeout: 5000 }
		);
		const countText = await page.$eval( '#classCount', ( el ) => el.textContent );
		expect( countText ).toContain( '0' );

		// Clear the search through the same handler the input uses.
		await page.evaluate( () => {
			const input = document.getElementById( 'courseSearch' );
			input.value = '';
			window.classScheduleSearch( { target: input } );
		} );
	} );

	it( 'sorting a column sets aria-sort on its header', async () => {
		if ( ! hasRows ) return;

		await page.click( '.col-title.is-sortable button' );
		expect(
			await page.$eval( '.col-title.is-sortable', ( el ) =>
				el.getAttribute( 'aria-sort' )
			)
		).toBe( 'ascending' );

		await page.click( '.col-title.is-sortable button' );
		expect(
			await page.$eval( '.col-title.is-sortable', ( el ) =>
				el.getAttribute( 'aria-sort' )
			)
		).toBe( 'descending' );
	} );

	it( 'filter modal applies column visibility changes', async () => {
		if ( ! hasRows ) return;

		await page.click( '#filterButton' );
		await page.waitForSelector( '#filterModal.active', { timeout: 5000 } );

		await page.click( '.column-toggle[data-column="time"]' );
		await page.click( '.apply-button' );
		await page.waitForFunction(
			() =>
				! document
					.getElementById( 'filterModal' )
					.classList.contains( 'active' ),
			{ timeout: 5000 }
		);

		expect(
			await page.$eval(
				'#classScheduleTable .el-table__header-row .col-time',
				( el ) => el.classList.contains( 'hidden' )
			)
		).toBe( false );
	} );
} );
