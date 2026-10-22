/**
 * End-to-end test for Campus Directory photos served from LDAP.
 *
 * campusdirectory.ucsc.edu/photo.php only answers on campus/VPN, so off-campus
 * visitors saw every directory photo hang. Photos now come from this plugin's
 * own /wp-json/ucscgutenbergblocks/v1/directoryphoto/<uid> endpoint, which reads
 * jpegPhoto from LDAP.
 *
 * Needs LDAP reachable (UCSC VPN) so the seeded EEB page renders people.
 */

const { PAGES, SITE_ORIGIN, shimPage, gotoPage } = require( './utils/e2e-helpers' );

const PHOTO_SELECTOR = '.ucsc-block-directory img.item-image';
const ENDPOINT = `${ SITE_ORIGIN }/wp-json/ucscgutenbergblocks/v1/directoryphoto/`;

let photos = [];

describe( 'Campus Directory photos served from LDAP', () => {
	beforeAll( async () => {
		shimPage( page );
		await gotoPage( page, PAGES.campusDirectory );
		photos = await page.$$eval( PHOTO_SELECTOR, ( imgs ) =>
			imgs.map( ( img ) => ( {
				src: img.getAttribute( 'src' ),
				loaded: img.complete && img.naturalWidth > 0,
			} ) )
		);
	} );

	it( 'renders directory photos', () => {
		expect( photos.length ).toBeGreaterThan( 1 );
	} );

	it( 'points every photo at the local endpoint, never campusdirectory.ucsc.edu', () => {
		photos.forEach( ( { src } ) => {
			expect( src ).toContain( ENDPOINT );
			expect( src ).not.toContain( 'campusdirectory.ucsc.edu' );
		} );
	} );

	it( 'loads every photo (a real photo or the slug fallback)', () => {
		expect( photos.filter( ( p ) => ! p.loaded ) ).toEqual( [] );
	} );

	it( 'serves a listed person as a cacheable JPEG', async () => {
		const response = await page.goto( photos[ 0 ].src );
		expect( response.status() ).toBe( 200 );
		expect( response.headers()[ 'content-type' ] ).toBe( 'image/jpeg' );
		expect( response.headers()[ 'cache-control' ] ).toContain( 'max-age=86400' );
		const body = await response.buffer();
		// JPEG start-of-image marker
		expect( body[ 0 ] ).toBe( 0xff );
		expect( body[ 1 ] ).toBe( 0xd8 );
	} );

	it( 'redirects an unknown CruzID to the slug placeholder', async () => {
		const response = await page.goto( ENDPOINT + 'zzqnotarealcruzid' );
		const chain = response.request().redirectChain();
		expect( chain.length ).toBe( 1 );
		expect( chain[ 0 ].response().status() ).toBe( 302 );
		expect( chain[ 0 ].response().headers().location ).toBe(
			'https://static.ucsc.edu/images/icon-slug.jpg'
		);
	} );

	it( 'rejects a uid with characters outside the route pattern', async () => {
		const response = await page.goto( ENDPOINT + 'jdoe%3B' );
		expect( response.status() ).toBe( 404 );
	} );
} );
