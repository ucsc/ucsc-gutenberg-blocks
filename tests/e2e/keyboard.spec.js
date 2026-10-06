/**
 * Keyboard navigation across the three LDAP/registrar blocks (WPM-199).
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh, which seeds the target pages first.
 *
 * Tests titled "[RED until WPM-NNN]" are bug proofs: they assert the fixed
 * behaviour and fail on the current code. They go green when that issue is
 * fixed; do not weaken them to make the suite pass.
 *
 * Tab stops are measured from a sentinel inserted just before each block
 * (utils/e2e-helpers.js tabStopsWithin), so the theme's header links and the
 * admin bar never count.
 */

const {
	PAGES,
	shimPage,
	gotoPage,
	tabStopsWithin,
} = require( './utils/e2e-helpers' );

beforeAll( () => shimPage( page ) );

describe( 'Course Catalog keyboard access', () => {
	let hasRows = false;

	beforeEach( async () => {
		await gotoPage( page, PAGES.courseCatalog );
		hasRows = ( await page.$( '#tableSorter tbody tr.pointer' ) ) !== null;
	} );

	it( 'the search box is a tab stop', async () => {
		const stops = await tabStopsWithin( page, '#courseCatalog' );
		expect( stops.map( ( s ) => s.desc ) ).toContain( 'input#search' );
	} );

	it( '[RED until WPM-109] "Expand all" and "Collapse all" are tab stops', async () => {
		const stops = await tabStopsWithin( page, '#courseCatalog' );
		const descs = stops.map( ( s ) => s.desc );
		expect( descs.some( ( d ) => d.includes( '#expandAll' ) ) ).toBe( true );
		expect( descs.some( ( d ) => d.includes( '#collapseAll' ) ) ).toBe( true );
	} );

	it( '[RED until WPM-109] a course row can be reached and expanded from the keyboard', async () => {
		expect( hasRows ).toBe( true );
		const stops = await tabStopsWithin( page, '#courseCatalog' );
		const rowStopIndex = stops.findIndex( ( s ) => s.inTbody );
		expect( rowStopIndex ).toBeGreaterThanOrEqual( 0 );

		// Re-walk to that stop, then activate it with Enter.
		await page.evaluate( () => {
			const root = document.querySelector( '#courseCatalog' );
			const s = document.createElement( 'button' );
			s.id = 'e2e-tab-sentinel';
			root.parentNode.insertBefore( s, root );
			s.focus();
		} );
		for ( let i = 0; i <= rowStopIndex; i++ ) {
			await page.keyboard.press( 'Tab' );
		}
		await page.keyboard.press( 'Enter' );

		const expanded = await page.evaluate( () => {
			const row = document.activeElement.closest( 'tr' );
			const detail = row && row.nextElementSibling;
			return !! detail && detail.classList.contains( 'active' );
		} );
		expect( expanded ).toBe( true );
	} );

	it( '[RED until WPM-109] the toggled course row exposes aria-expanded', async () => {
		expect( hasRows ).toBe( true );
		const toggles = await page.$$eval(
			'#tableSorter tbody tr.pointer',
			( rows ) =>
				rows.filter( ( r ) => r.querySelector( '[aria-expanded]' ) || r.hasAttribute( 'aria-expanded' ) ).length
		);
		expect( toggles ).toBeGreaterThan( 0 );
	} );

	it( '[RED until WPM-108] sortable column headers are tab stops', async () => {
		const stops = await tabStopsWithin( page, '#courseCatalog' );
		expect( stops.filter( ( s ) => s.inThead ).length ).toBe( 4 );
	} );
} );

describe( 'Class Schedule keyboard access', () => {
	let hasRows = false;

	beforeEach( async () => {
		// Column visibility persists in sessionStorage; start every test clean.
		await gotoPage( page, PAGES.classSchedule );
		await page.evaluate( () => sessionStorage.clear() );
		await gotoPage( page, PAGES.classSchedule );
		hasRows = ( await page.$( '#classScheduleTable .course-row' ) ) !== null;
	} );

	// Guards ClassScheduleTemplate.php's $cs_header_tabindex and the tabindex
	// half of applyColumnVisibility(): visible sort buttons are reachable,
	// hidden-column ones are not. (WPM-93 questions whether hidden columns
	// should be reachable at all; this asserts the current, intended rule.)
	it( 'every visible sort button is a tab stop and no hidden-column one is', async () => {
		expect( hasRows ).toBe( true );
		const expected = await page.$$eval(
			'#classScheduleTable .el-table__header-row > .is-sortable',
			( cols ) =>
				cols
					.filter( ( c ) => ! c.classList.contains( 'hidden' ) )
					.map( ( c ) => c.querySelector( 'button' ).textContent.trim() )
		);
		const stops = await tabStopsWithin( page, '#classSchedule' );
		const sortStops = stops
			.filter( ( s ) => s.desc.startsWith( 'button.cell' ) )
			.map( ( s ) => s.text );

		expect( expected.length ).toBeGreaterThan( 0 );
		expect( sortStops ).toEqual( expected );
	} );

	it( 'showing the Time column makes its sort button a tab stop', async () => {
		expect( hasRows ).toBe( true );
		const before = ( await tabStopsWithin( page, '#classSchedule' ) ).map( ( s ) => s.text );
		expect( before ).not.toContain( 'Time' );

		await page.click( '#filterButton' );
		await page.waitForSelector( '#filterModal.active' );
		await page.click( '.column-toggle[data-column="time"]' );
		await page.click( '.apply-button' );

		const after = ( await tabStopsWithin( page, '#classSchedule' ) ).map( ( s ) => s.text );
		expect( after ).toContain( 'Time' );
	} );

	describe( 'filter modal', () => {
		it( 'Enter on the filter button opens the modal and moves focus into it', async () => {
			await page.focus( '#filterButton' );
			await page.keyboard.press( 'Enter' );
			await page.waitForSelector( '#filterModal.active' );
			const inside = await page.evaluate( () =>
				document.getElementById( 'filterModal' ).contains( document.activeElement )
			);
			expect( inside ).toBe( true );
		} );

		it( 'traps Tab and Shift+Tab inside the modal', async () => {
			await page.focus( '#filterButton' );
			await page.keyboard.press( 'Enter' );
			await page.waitForSelector( '#filterModal.active' );

			const focusableCount = await page.$$eval(
				'#filterModal input, #filterModal button',
				( els ) => els.length
			);
			for ( let i = 0; i < focusableCount + 3; i++ ) {
				await page.keyboard.press( 'Tab' );
				expect(
					await page.evaluate( () =>
						document.getElementById( 'filterModal' ).contains( document.activeElement )
					)
				).toBe( true );
			}
			await page.keyboard.down( 'Shift' );
			for ( let i = 0; i < focusableCount + 3; i++ ) {
				await page.keyboard.press( 'Tab' );
				expect(
					await page.evaluate( () =>
						document.getElementById( 'filterModal' ).contains( document.activeElement )
					)
				).toBe( true );
			}
			await page.keyboard.up( 'Shift' );
		} );

		it( 'Escape closes the modal and returns focus to the filter button', async () => {
			await page.focus( '#filterButton' );
			await page.keyboard.press( 'Enter' );
			await page.waitForSelector( '#filterModal.active' );
			await page.keyboard.press( 'Escape' );

			expect(
				await page.$eval( '#filterModal', ( m ) => m.classList.contains( 'active' ) )
			).toBe( false );
			expect( await page.evaluate( () => document.activeElement.id ) ).toBe(
				'filterButton'
			);
		} );

		it( '[RED until WPM-197] the filter button announces the dialog it opens', async () => {
			const attrs = () =>
				page.$eval( '#filterButton', ( b ) => ( {
					haspopup: b.getAttribute( 'aria-haspopup' ),
					controls: b.getAttribute( 'aria-controls' ),
					expanded: b.getAttribute( 'aria-expanded' ),
				} ) );

			expect( await attrs() ).toEqual( {
				haspopup: 'dialog',
				controls: 'filterModal',
				expanded: 'false',
			} );

			await page.focus( '#filterButton' );
			await page.keyboard.press( 'Enter' );
			await page.waitForSelector( '#filterModal.active' );
			expect( ( await attrs() ).expanded ).toBe( 'true' );

			await page.keyboard.press( 'Escape' );
			expect( ( await attrs() ).expanded ).toBe( 'false' );
		} );
	} );
} );

describe( 'Campus Directory keyboard access', () => {
	it( '[RED until WPM-198] each person is one tab stop, not two', async () => {
		await gotoPage( page, PAGES.campusDirectory );
		const people = await page.$$eval(
			'.ucsc-block-directory .p-name',
			( n ) => n.length
		);
		expect( people ).toBeGreaterThan( 1 );

		const stops = await tabStopsWithin( page, '.ucsc-block-directory', 400 );
		const profileStops = stops.filter(
			( s ) => s.href && s.href.includes( 'directoryprofilecruzid=' )
		);
		expect( profileStops.length ).toBe( people );
	} );
} );
