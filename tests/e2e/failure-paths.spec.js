/**
 * What the user sees when a browser-side request fails (WPM-202).
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh.
 *
 * All three blocks render their frontend on the server, so the only requests
 * the browser itself makes are directory photos (frontend) and the
 * dropdown/requirements REST calls (editor). Those are what is intercepted
 * here. Server-side failures (LDAP bind, course feed, REST proxy) are covered
 * by the PHP suites.
 *
 * The editor case answers with an HTML 429, the shape of the CampusPress edge
 * rate limit that broke the block editor network-wide in July 2026.
 */

const {
	PAGES,
	shimPage,
	gotoPage,
	captureErrors,
	adminPage,
	openNewPageInEditor,
	insertBlock,
} = require( './utils/e2e-helpers' );

jest.setTimeout( 120000 );

// 1x1 transparent PNG.
const PIXEL = Buffer.from(
	'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
	'base64'
);

describe( 'Campus Directory: a directory photo fails to load', () => {
	let p;
	let errors;
	let photos = [];

	beforeAll( async () => {
		p = shimPage( await browser.newPage() );
		errors = captureErrors( p );
		await p.setRequestInterception( true );
		p.on( 'request', ( req ) => {
			const url = req.url();
			if ( url.includes( 'campusdirectory.ucsc.edu/photo.php' ) ) {
				req.respond( { status: 404, contentType: 'text/plain', body: 'not found' } );
			} else if ( url.includes( 'static.ucsc.edu/images/icon-slug.jpg' ) ) {
				// Serve the fallback locally so the test does not depend on
				// static.ucsc.edu being reachable from the container.
				req.respond( { status: 200, contentType: 'image/png', body: PIXEL } );
			} else {
				req.continue();
			}
		} );
		await gotoPage( p, PAGES.campusDirectory );
		photos = await p.$$eval( '.ucsc-block-directory img', ( imgs ) =>
			imgs.map( ( i ) => ( { src: i.src, loaded: i.complete && i.naturalWidth > 0 } ) )
		);
	} );

	afterAll( () => p && p.close() );

	it( 'renders people', () => {
		expect( photos.length ).toBeGreaterThan( 1 );
	} );

	it( 'swaps every broken photo for the placeholder image', () => {
		photos.forEach( ( ph ) => {
			expect( ph.src ).toMatch( /\/\/static\.ucsc\.edu\/images\/icon-slug\.jpg$/ );
			expect( ph.loaded ).toBe( true );
		} );
	} );

	it( 'raises no uncaught errors', () => {
		expect( errors.pageErrors ).toEqual( [] );
	} );
} );

const RATE_LIMITED = {
	status: 429,
	contentType: 'text/html',
	body: '<html><body><h1>429 Too Many Requests</h1></body></html>',
};

describe.each( [
	[ 'ucscblocks/coursecatalog', /\/ucscgutenbergblocks\/v1\/(subjectcode|departmentcode)/ ],
	[ 'ucscblocks/classschedule', /\/ucscgutenbergblocks\/v1\/(subjectcode|departmentcode)/ ],
] )( 'Editor: %s when its dropdown endpoints are rate limited', ( blockName, endpoint ) => {
	let context;
	let p;
	let errors;
	let postId;

	beforeAll( async () => {
		( { context, page: p } = await adminPage() );
		shimPage( p );
		errors = captureErrors( p );
		await openNewPageInEditor( p );
		postId = await p.evaluate( () =>
			wp.data.select( 'core/editor' ).getCurrentPostId()
		);
		await p.setRequestInterception( true );
		p.on( 'request', ( req ) =>
			endpoint.test( req.url() ) ? req.respond( RATE_LIMITED ) : req.continue()
		);
		await insertBlock( p, blockName );
		// Long enough for the intercepted fetch to settle either way.
		await new Promise( ( r ) => setTimeout( r, 5000 ) );
	} );

	afterAll( async () => {
		if ( p && postId ) {
			await p
				.evaluate(
					( id ) =>
						wp.apiFetch( { path: `/wp/v2/pages/${ id }?force=true`, method: 'DELETE' } ),
					postId
				)
				.catch( () => {} );
		}
		if ( context ) {
			await context.close();
		}
	} );

	// The dropdowns do fetch().then(res => res.text()).then(JSON.parse) with no
	// status check and no catch (src/components/*Dropdown.js), so an HTML error
	// page throws an unhandled rejection.
	it( '[RED until dropdowns handle HTTP errors] raises no uncaught errors', () => {
		expect( errors.pageErrors ).toEqual( [] );
	} );

	// ...and the dropdown never leaves its loading state, so the editor shows
	// "Dropdown Loading..." forever with no hint that anything failed.
	it( '[RED until dropdowns handle HTTP errors] does not stay on "Loading..." forever', async () => {
		expect( await p.evaluate( () => document.body.innerText ) ).not.toMatch(
			/Dropdown Loading\.\.\./
		);
	} );
} );
