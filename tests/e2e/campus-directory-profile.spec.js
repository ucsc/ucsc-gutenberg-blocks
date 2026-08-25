/**
 * End-to-end test for the Campus Directory in-site profile route (WPM-114).
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh, which seeds the target page first.
 *
 * WHAT THIS GUARDS, AND WHY IT IS SHAPED THIS WAY
 *
 * LALS was the WPM-99 site: a nested People / Faculty page whose directory
 * links go out to campusdirectory.ucsc.edu. EEB was the WPM-114 regression:
 * a People page whose links stay in-site with `?directoryprofilecruzid=`.
 *
 * On EEB, clicking a person appeared to do nothing. The profile *was* rendered
 * — appended below the entire directory listing, so it sat ~92% of the way down
 * the page and nobody scrolled to it.
 *
 * The regression was only observable as "the listing is still there". Measured
 * against the broken build, the profile view still contained the site header
 * (3 matches) and the profile breadcrumbs (1 match) — identical to the fixed
 * build. So an assertion that the header renders, or that the profile renders,
 * PASSES on broken code and catches nothing.
 *
 * The discriminating assertions are therefore:
 *   - the directory listing container is ABSENT on a profile view (0 vs 1)
 *   - exactly one person is shown, not the whole roster (1 vs 17 name nodes)
 *
 * The header/nav assertion is kept because WPM-99 (block themes losing site
 * navigation on profile pages) is the bug whose fix introduced WPM-114. Both
 * must hold at once, so both are asserted here.
 */

const LISTING_URL =
	process.env.UCSC_CD_E2E_URL || 'https://wp-dev.ucsc/eeb/people/';
const LALS_LISTING_URL =
	process.env.UCSC_CD_LALS_E2E_URL ||
	'https://wp-dev.ucsc/lals/people/faculty/';

const LISTING_SELECTOR = '.ucsc-block-directory';
const PROFILE_LINK_SELECTOR = 'a.u-url[href*="directoryprofilecruzid="]';
const EXTERNAL_PROFILE_LINK_SELECTOR =
	'a.u-url[href^="https://campusdirectory.ucsc.edu/cd_detail?uid="]';

let profileUrl = null;
let listingNameCount = 0;

describe( 'Campus Directory in-site profile route (frontend)', () => {
	beforeAll( async () => {
		if ( typeof page !== 'undefined' && page && ! page.removeListener ) {
			page.removeListener = page.off || page.removeEventListener;
		}

		await page.goto( LISTING_URL, {
			waitUntil: 'networkidle0',
			timeout: 60000,
		} );

		listingNameCount = await page.$$eval(
			'.p-name',
			( nodes ) => nodes.length
		);

		const href = await page
			.$eval( PROFILE_LINK_SELECTOR, ( el ) => el.getAttribute( 'href' ) )
			.catch( () => null );

		if ( href ) {
			profileUrl = new URL( href, LISTING_URL ).toString();
		} else {
			// eslint-disable-next-line no-console
			console.warn(
				'No in-site profile links on the listing page. The block must have ' +
					'linkToProfile ON and linkOutToCampusDirectory OFF, and LDAP must ' +
					'be reachable (UCSC VPN). Profile tests will fail loudly.'
			);
		}
	} );

	it( 'listing page renders people', async () => {
		expect( listingNameCount ).toBeGreaterThan( 1 );
	} );

	it( 'listing page links profiles in-site, not out to campusdirectory.ucsc.edu', async () => {
		expect( profileUrl ).not.toBeNull();
		expect( profileUrl ).toContain( 'directoryprofilecruzid=' );
	} );

	describe( 'LALS-format external profile links', () => {
		let externalLinkCount = 0;
		let inSiteLinkCount = 0;

		beforeAll( async () => {
			await page.goto( LALS_LISTING_URL, {
				waitUntil: 'networkidle0',
				timeout: 60000,
			} );
			externalLinkCount = await page.$$eval(
				EXTERNAL_PROFILE_LINK_SELECTOR,
				( nodes ) => nodes.length
			);
			inSiteLinkCount = await page.$$eval(
				PROFILE_LINK_SELECTOR,
				( nodes ) => nodes.length
			);
		} );

		it( 'uses the nested People / Faculty page shape', async () => {
			expect( new URL( LALS_LISTING_URL ).pathname ).toBe(
				'/lals/people/faculty/'
			);
		} );

		it( 'links profiles out to campusdirectory.ucsc.edu', async () => {
			expect( externalLinkCount ).toBeGreaterThan( 1 );
			expect( inSiteLinkCount ).toBe( 0 );
		} );
	} );

	describe( 'following a profile link', () => {
		beforeAll( async () => {
			if ( ! profileUrl ) return;
			await page.goto( profileUrl, {
				waitUntil: 'networkidle0',
				timeout: 60000,
			} );
		} );

		it( 'renders the profile', async () => {
			expect( profileUrl ).not.toBeNull();
			await page.waitForSelector( '.breadcrumbs__trail', {
				timeout: 15000,
			} );
			expect( await page.$( '.breadcrumbs__trail' ) ).not.toBeNull();
		} );

		// The WPM-114 guard. This is the assertion that fails on the regression;
		// every other assertion in this file passes on the broken build.
		it( 'does NOT also render the directory listing (WPM-114)', async () => {
			const listings = await page.$$eval(
				LISTING_SELECTOR,
				( nodes ) => nodes.length
			);
			expect( listings ).toBe( 0 );
		} );

		it( 'shows one person, not the whole roster (WPM-114)', async () => {
			const names = await page.$$eval(
				'.p-name',
				( nodes ) => nodes.length
			);
			expect( names ).toBeLessThan( listingNameCount );
		} );

		// The WPM-99 guard: the profile page must keep the theme's chrome.
		it( 'keeps the site header and navigation (WPM-99)', async () => {
			expect( await page.$( '.site-header' ) ).not.toBeNull();
			expect( await page.$( 'nav.wp-block-navigation' ) ).not.toBeNull();
		} );

		it( 'renders exactly one <main> element', async () => {
			const mains = await page.$$eval( 'main', ( nodes ) => nodes.length );
			expect( mains ).toBe( 1 );
		} );
	} );
} );
