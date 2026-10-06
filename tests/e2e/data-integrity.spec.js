/**
 * Rendered-data integrity for the three blocks (WPM-201).
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh, which seeds the target pages first.
 *
 * The other specs check that content renders. These check that every row or
 * card that renders is complete and internally consistent, which is what a
 * template or feed-mapping regression breaks first.
 *
 * Each test asserts a non-empty list first, so an empty page cannot pass
 * vacuously.
 */

const { PAGES, shimPage, gotoPage } = require( './utils/e2e-helpers' );

beforeAll( () => shimPage( page ) );

// Mirrors templates/ClassScheduleTemplate.php's status → label mapping.
const STATUS_LABELS = {
	open: 'Open',
	closed: 'Closed',
	waitlist: 'Closed with Wait List',
};

describe( 'Class Schedule rendered data', () => {
	let rows = [];
	let countShown = null;

	beforeAll( async () => {
		await gotoPage( page, PAGES.classSchedule );
		rows = await page.$$eval( '#classScheduleTable .course-row', ( trs ) =>
			trs.map( ( r ) => {
				const icons = r.querySelectorAll( '.col-status [role="img"]' );
				return {
					status: r.dataset.status,
					iconCount: icons.length,
					iconClass: icons[ 0 ] ? icons[ 0 ].className : null,
					iconLabel: icons[ 0 ] ? icons[ 0 ].getAttribute( 'aria-label' ) : null,
					id: r.querySelector( '.col-course-id' ).textContent.trim(),
					title: r.querySelector( '.col-title' ).textContent.trim(),
					href: r.querySelector( '.col-title a' )
						? r.querySelector( '.col-title a' ).getAttribute( 'href' )
						: null,
				};
			} )
		);
		countShown = await page.$eval( '#classCount strong', ( el ) =>
			Number( el.textContent )
		);
	} );

	it( 'renders rows', () => {
		expect( rows.length ).toBeGreaterThan( 0 );
	} );

	it( 'each row has exactly one status icon, labelled to match its status', () => {
		rows.forEach( ( r ) => {
			expect( r.iconCount ).toBe( 1 );
			expect( Object.keys( STATUS_LABELS ) ).toContain( r.status );
			expect( r.iconClass ).toBe( r.status );
			expect( r.iconLabel ).toBe( STATUS_LABELS[ r.status ] );
		} );
	} );

	it( 'the "Displaying N classes" count matches the rows on the page', () => {
		expect( countShown ).toBe( rows.length );
	} );

	it( 'every row has a course ID and a title linking to /course/{term}/{class_nbr}', () => {
		rows.forEach( ( r ) => {
			expect( r.id ).toMatch( /^[A-Z]{2,5}-\S+/ );
			expect( r.title.length ).toBeGreaterThan( 0 );
			expect( new URL( r.href ).pathname ).toMatch( /^\/course\/\d+\/\d+\/?$/ );
		} );
	} );
} );

describe( 'Course Catalog rendered data', () => {
	let trs = [];

	beforeAll( async () => {
		await gotoPage( page, PAGES.courseCatalog );
		trs = await page.$$eval( '#tableSorter tbody tr', ( rows ) =>
			rows.map( ( tr ) => ( {
				cls: tr.className,
				cells: Array.from( tr.children ).map( ( td ) => td.textContent.trim() ),
			} ) )
		);
	} );

	it( 'renders course rows', () => {
		expect( trs.length ).toBeGreaterThan( 0 );
	} );

	// tablesorter.js pairs rows by index (i, i+1); an unpaired row would
	// attach every description below it to the wrong course on sort.
	it( 'alternates course row / description row strictly', () => {
		expect( trs.length % 2 ).toBe( 0 );
		trs.forEach( ( tr, i ) =>
			expect( tr.cls ).toBe( i % 2 === 0 ? 'pointer' : 'hide' )
		);
	} );

	it( 'every course row has a course #, title, level and units', () => {
		trs.filter( ( tr ) => tr.cls === 'pointer' ).forEach( ( tr ) => {
			const [ num, title, level, units ] = tr.cells;
			expect( num ).toMatch( /^[A-Z]{2,5}\s+\S+/ );
			expect( title.length ).toBeGreaterThan( 0 );
			expect( level ).toMatch( /^(Lower Division|Upper Division|Graduate)/ );
			expect( units ).toMatch( /^\d+(\.\d+)?(-\d+)? Units?$/ );
		} );
	} );

	it( 'every course has a non-empty description', () => {
		const empty = trs
			.map( ( tr, i ) => ( { tr, course: trs[ i - 1 ] } ) )
			.filter( ( { tr } ) => tr.cls === 'hide' && tr.cells.join( '' ).length === 0 )
			.map( ( { course } ) => course.cells[ 0 ] );
		expect( empty ).toEqual( [] );
	} );
} );

describe.each( [
	[ 'in-site profile links (EEB)', PAGES.campusDirectory, /^\?directoryprofilecruzid=[a-z0-9]+$/ ],
	[
		'campusdirectory.ucsc.edu links (LALS)',
		PAGES.campusDirectoryLals,
		/^https:\/\/campusdirectory\.ucsc\.edu\/cd_detail\?uid=[a-z0-9]+$/,
	],
] )( 'Campus Directory rendered data: %s', ( _label, url, linkPattern ) => {
	let cards = [];

	beforeAll( async () => {
		await gotoPage( page, url );
		cards = await page.$$eval( '.ucsc-block-directory .h-card', ( nodes ) =>
			nodes.map( ( c ) => ( {
				name: ( c.querySelector( '.p-name' ) || { textContent: '' } ).textContent.trim(),
				alt: c.querySelector( 'img' ) ? c.querySelector( 'img' ).getAttribute( 'alt' ) : null,
				links: Array.from( c.querySelectorAll( 'a.u-url' ) ).map( ( a ) =>
					a.getAttribute( 'href' )
				),
			} ) )
		);
	} );

	it( 'renders people', () => {
		expect( cards.length ).toBeGreaterThan( 1 );
	} );

	it( 'every card has a name and a photo whose alt names that person', () => {
		cards.forEach( ( c ) => {
			expect( c.name.length ).toBeGreaterThan( 0 );
			expect( c.alt ).toBe( 'Profile picture of ' + c.name );
		} );
	} );

	it( 'every profile link uses the format the block is configured for', () => {
		cards.forEach( ( c ) => {
			expect( c.links.length ).toBeGreaterThan( 0 );
			c.links.forEach( ( href ) => expect( href ).toMatch( linkPattern ) );
		} );
	} );
} );
