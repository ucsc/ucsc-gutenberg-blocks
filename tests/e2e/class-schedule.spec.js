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

	// WPM-163: the schedule table and CourseDetailTemplate.php each had
	// coverage in isolation, but nothing exercised the actual navigation
	// between them — a course title is a real <a href> to
	// /course/{term}/{class_nbr}/ (templates/ClassScheduleTemplate.php),
	// which classes/ClassSchedule.php rewrites onto
	// templates/CourseDetailTemplate.php.
	describe( 'opening a course detail page', () => {
		let detailUrl = null;

		beforeAll( async () => {
			if ( ! hasRows ) return;

			detailUrl = await page.$eval(
				'#classScheduleTable .course-row .col-title a',
				( el ) => el.getAttribute( 'href' )
			);
		} );

		it( 'the course row title links to /course/{term}/{class_nbr}', () => {
			if ( ! hasRows ) return;
			expect( detailUrl ).not.toBeNull();
			expect( new URL( detailUrl ).pathname ).toMatch(
				/^\/course\/\d+\/\d+\/?$/
			);
		} );

		describe( 'the detail page itself', () => {
			beforeAll( async () => {
				if ( ! hasRows || ! detailUrl ) return;
				await page.goto( detailUrl, {
					waitUntil: 'networkidle0',
					timeout: 60000,
				} );
			} );

			it( 'renders the course detail page, not an error fallback', async () => {
				if ( ! hasRows ) return;
				await page.waitForSelector( '#class-info', { timeout: 15000 } );
				expect( await page.$( '#class-info' ) ).not.toBeNull();
			} );

			it( 'breadcrumb links back to Class Schedule', async () => {
				if ( ! hasRows ) return;
				const breadcrumbText = await page.$eval(
					'.breadcrumbs__trail',
					( el ) => el.textContent
				);
				expect( breadcrumbText ).toContain( 'Class Schedule' );
			} );

			it( 'shows the course title heading', async () => {
				if ( ! hasRows ) return;
				expect( await page.$( '#title.page-title' ) ).not.toBeNull();
			} );
		} );
	} );
} );
