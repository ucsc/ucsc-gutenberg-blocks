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
	editorBlock,
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
			// Photos load from the plugin's LDAP-backed endpoint first (WPM-226),
			// then campusdirectory photo.php; break both.
			if (
				url.includes( '/ucscgutenbergblocks/v1/directoryphoto/' ) ||
				url.includes( 'campusdirectory.ucsc.edu/photo.php' )
			) {
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

// The network gives up on the request, as when the CampusPress edge hangs
// until the browser times out (WPM-202).
const TIMED_OUT = 'timedout';

const DROPDOWN_ENDPOINTS = /\/ucscgutenbergblocks\/v1\/(subjectcode|departmentcode)/;

describe.each( [
	[ 'ucscblocks/coursecatalog', 'are rate limited', RATE_LIMITED ],
	[ 'ucscblocks/classschedule', 'are rate limited', RATE_LIMITED ],
	[ 'ucscblocks/coursecatalog', 'time out', TIMED_OUT ],
	[ 'ucscblocks/classschedule', 'time out', TIMED_OUT ],
] )( 'Editor: %s when its dropdown endpoints %s', ( blockName, _how, failure ) => {
	let context;
	let p;
	let errors;
	let postId;
	let clientId;

	beforeAll( async () => {
		( { context, page: p } = await adminPage() );
		shimPage( p );
		errors = captureErrors( p );
		await openNewPageInEditor( p );
		postId = await p.evaluate( () =>
			wp.data.select( 'core/editor' ).getCurrentPostId()
		);
		await p.setRequestInterception( true );
		p.on( 'request', ( req ) => {
			if ( ! DROPDOWN_ENDPOINTS.test( req.url() ) ) {
				req.continue();
			} else if ( failure === TIMED_OUT ) {
				req.abort( TIMED_OUT );
			} else {
				req.respond( failure );
			}
		} );
		clientId = await insertBlock( p, blockName );
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
	it( 'raises no uncaught errors (WPM-207)', () => {
		expect( errors.pageErrors ).toEqual( [] );
	} );

	// ...and the dropdown never leaves its loading state, so the editor shows
	// "Dropdown Loading..." forever with no hint that anything failed.
	it( 'does not stay on "Loading..." forever (WPM-207)', async () => {
		const { handle } = await editorBlock( p, clientId );
		expect( await handle.evaluate( ( el ) => el.innerText ) ).not.toMatch(
			/Dropdown Loading\.\.\./
		);
	} );
} );
