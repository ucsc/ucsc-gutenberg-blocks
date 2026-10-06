/**
 * Sorting, search, filters, reset, Copy URL and CSV in a real browser
 * (WPM-200).
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh, which seeds the target pages first.
 *
 * class-schedule.spec.js already covers aria-sort on the Title header, search
 * to zero rows, and showing a hidden column. This file asserts what those
 * leave out: that the rows actually reorder and filter, and what the CSV and
 * copied URL contain.
 */

const { PAGES, shimPage, gotoPage } = require( './utils/e2e-helpers' );

beforeAll( () => shimPage( page ) );

const nonDecreasing = ( values, cmp ) =>
	values.every( ( v, i ) => i === 0 || cmp( values[ i - 1 ], v ) <= 0 );

describe( 'Class Schedule interactions', () => {
	const visibleRows = () =>
		page.$$eval( '#classScheduleTable .course-row', ( rows ) =>
			rows
				.filter( ( r ) => r.style.display !== 'none' )
				.map( ( r ) => ( {
					status: r.dataset.status,
					id: r.querySelector( '.col-course-id' ).textContent.trim(),
					seats: r.querySelector( '.col-seats' ).textContent.trim(),
				} ) )
		);
	const countText = () =>
		page.$eval( '#classCount strong', ( el ) => Number( el.textContent ) );

	beforeEach( async () => {
		await gotoPage( page, PAGES.classSchedule );
		await page.evaluate( () => sessionStorage.clear() );
		await gotoPage( page, PAGES.classSchedule );
		expect( await page.$( '#classScheduleTable .course-row' ) ).not.toBeNull();
	} );

	it( 'sorting Course ID reorders rows ascending, then descending', async () => {
		const cmp = ( a, b ) => a.localeCompare( b );

		await page.click( '.col-course-id.is-sortable button' );
		const asc = ( await visibleRows() ).map( ( r ) => r.id );
		expect( nonDecreasing( asc, cmp ) ).toBe( true );

		await page.click( '.col-course-id.is-sortable button' );
		const desc = ( await visibleRows() ).map( ( r ) => r.id );
		expect( nonDecreasing( [ ...desc ].reverse(), cmp ) ).toBe( true );
		expect( desc[ 0 ] ).not.toBe( asc[ 0 ] );
	} );

	it( 'sorting Seats orders numerically by open seats', async () => {
		await page.click( '.col-seats.is-sortable button' );
		const seats = ( await visibleRows() ).map( ( r ) => parseFloat( r.seats ) );
		expect( seats.every( ( n ) => ! Number.isNaN( n ) ) ).toBe( true );
		expect( nonDecreasing( seats, ( a, b ) => a - b ) ).toBe( true );
		// A string sort would put "10 open" before "9 open"; make sure the
		// fixture actually has multi-digit values so this is meaningful.
		expect( Math.max( ...seats ) ).toBeGreaterThanOrEqual( 10 );
	} );

	it( 'unchecking "Closed" and applying hides closed rows and updates the count', async () => {
		const before = await visibleRows();
		const open = before.filter( ( r ) => r.status !== 'closed' ).length;
		expect( before.some( ( r ) => r.status === 'closed' ) ).toBe( true );

		await page.click( '#filterButton' );
		await page.waitForSelector( '#filterModal.active' );
		await page.click( '.status-filter[data-status="closed"]' );
		await page.click( '.apply-button' );

		const after = await visibleRows();
		expect( after.some( ( r ) => r.status === 'closed' ) ).toBe( false );
		expect( after.length ).toBe( open );
		expect( await countText() ).toBe( open );
	} );

	it( 'Cancel discards unapplied checkbox changes', async () => {
		const before = ( await visibleRows() ).length;

		await page.click( '#filterButton' );
		await page.waitForSelector( '#filterModal.active' );
		await page.click( '.status-filter[data-status="closed"]' );
		await page.click( '.cancel-button' );

		expect(
			await page.$eval( '.status-filter[data-status="closed"]', ( c ) => c.checked )
		).toBe( true );
		expect( ( await visibleRows() ).length ).toBe( before );
	} );

	// WPM-112 guard, end to end: reset must clear the search box AND re-show
	// the rows the search had hidden.
	it( '"Reset all filters" clears search and status filters and restores every row', async () => {
		const total = ( await visibleRows() ).length;

		await page.type( '#courseSearch', 'zzz-no-such-course' );
		await page.waitForFunction(
			() => document.querySelector( '#classCount strong' ).textContent === '0'
		);
		await page.click( '#filterButton' );
		await page.waitForSelector( '#filterModal.active' );
		await page.click( '.status-filter[data-status="closed"]' );
		await page.click( '.reset-filters' );

		expect( await page.$eval( '#courseSearch', ( i ) => i.value ) ).toBe( '' );
		expect(
			await page.$eval( '.status-filter[data-status="closed"]', ( c ) => c.checked )
		).toBe( true );
		expect( ( await visibleRows() ).length ).toBe( total );
		expect( await countText() ).toBe( total );
	} );

	it( 'Copy URL copies the current page URL', async () => {
		await page.click( 'button[aria-label="Copy URL"]' );
		await page.waitForSelector( '.cs-toast em', { timeout: 5000 } );
		expect( await page.$eval( '.cs-toast em', ( el ) => el.textContent ) ).toBe(
			page.url()
		);
	} );

	describe( 'Download CSV', () => {
		// Capture the generated CSV instead of downloading it.
		const captureCsv = () =>
			page.evaluate( () => {
				window.__e2eCsv = null;
				const orig = URL.createObjectURL;
				URL.createObjectURL = ( blob ) => {
					blob.text().then( ( t ) => ( window.__e2eCsv = t ) );
					return orig.call( URL, blob );
				};
			} );
		const readCsv = async () => {
			await page.waitForFunction( () => window.__e2eCsv !== null );
			return page.evaluate( () => window.__e2eCsv.split( '\n' ) );
		};

		it( 'has a header of the visible columns and one line per visible row', async () => {
			await captureCsv();
			await page.click( 'button[aria-label="Download CSV"]' );
			const lines = await readCsv();

			const visibleHeaders = await page.$$eval(
				'#classScheduleTable .el-table__header-row > [role="columnheader"]',
				( cols ) =>
					cols
						.slice( 1 )
						.filter( ( c ) => ! c.classList.contains( 'hidden' ) )
						.map( ( c ) => '"' + c.textContent.trim() + '"' )
						.join( ',' )
			);
			expect( lines[ 0 ] ).toBe( visibleHeaders );
			expect( lines.length - 1 ).toBe( ( await visibleRows() ).length );
		} );

		it( 'leaves out rows hidden by a filter', async () => {
			await page.click( '#filterButton' );
			await page.waitForSelector( '#filterModal.active' );
			await page.click( '.status-filter[data-status="closed"]' );
			await page.click( '.apply-button' );

			await captureCsv();
			await page.click( 'button[aria-label="Download CSV"]' );
			const lines = await readCsv();
			const shown = await visibleRows();

			expect( lines.length - 1 ).toBe( shown.length );
			expect( lines[ 1 ] ).toContain( '"' + shown[ 0 ].id + '"' );
		} );
	} );
} );

describe( 'Course Catalog interactions', () => {
	const rows = () =>
		page.$$eval( '#tableSorter tbody tr', ( trs ) =>
			trs.map( ( tr ) => ( {
				cls: tr.className,
				title: tr.classList.contains( 'pointer' )
					? tr.children[ 1 ].textContent.trim()
					: null,
				shown: tr.style.display !== 'none',
				active: tr.classList.contains( 'active' ),
			} ) )
		);

	beforeEach( async () => {
		await gotoPage( page, PAGES.courseCatalog );
		expect( await page.$( '#tableSorter tbody tr.pointer' ) ).not.toBeNull();
	} );

	it( 'sorting Course Title reorders courses and keeps each description under its course', async () => {
		const pairOf = () =>
			page.$$eval( '#tableSorter tbody tr.pointer', ( trs ) =>
				trs.map( ( tr ) => [
					// Course # is unique; titles repeat ("Group Tutorial" x3).
					tr.children[ 0 ].textContent.trim(),
					tr.children[ 1 ].textContent.trim(),
					tr.nextElementSibling && tr.nextElementSibling.classList.contains( 'hide' )
						? tr.nextElementSibling.textContent.trim().slice( 0, 40 )
						: null,
				] )
			);
		const original = new Map(
			( await pairOf() ).map( ( [ num, , d ] ) => [ num, d ] )
		);

		await page.click( '#tableSorter thead th:nth-child(2)' );
		const asc = await pairOf();
		expect( nonDecreasing( asc.map( ( p ) => p[ 1 ] ), ( a, b ) => ( a > b ? 1 : a < b ? -1 : 0 ) ) ).toBe( true );
		asc.forEach( ( [ num, , d ] ) => expect( d ).toBe( original.get( num ) ) );

		await page.click( '#tableSorter thead th:nth-child(2)' );
		const desc = ( await pairOf() ).map( ( p ) => p[ 1 ] );
		expect( nonDecreasing( [ ...desc ].reverse(), ( a, b ) => ( a > b ? 1 : a < b ? -1 : 0 ) ) ).toBe( true );
	} );

	it( 'search hides non-matching courses together with their descriptions', async () => {
		const first = ( await rows() ).find( ( r ) => r.title ).title;

		await page.type( '#search', first );
		const after = await rows();
		const shownCourses = after.filter( ( r ) => r.title && r.shown );
		expect( shownCourses.map( ( r ) => r.title ) ).toContain( first );
		expect( shownCourses.length ).toBeLessThan( after.filter( ( r ) => r.title ).length );
		// Course and description rows are shown or hidden as a pair.
		for ( let i = 0; i < after.length; i += 2 ) {
			expect( after[ i + 1 ].shown ).toBe( after[ i ].shown );
		}
	} );

	it( '"Expand all" opens every description and "Collapse all" closes them', async () => {
		await page.click( '#expandAll' );
		let details = ( await rows() ).filter( ( r ) => r.cls.includes( 'hide' ) );
		expect( details.length ).toBeGreaterThan( 0 );
		expect( details.every( ( r ) => r.active ) ).toBe( true );

		await page.click( '#collapseAll' );
		details = ( await rows() ).filter( ( r ) => r.cls.includes( 'hide' ) );
		expect( details.some( ( r ) => r.active ) ).toBe( false );
	} );
} );
