/**
 * End-to-end test for the Campus Directory block on the live wp-dev.ucsc frontend.
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving the host's
 * Google Chrome). Launch options live in jest-puppeteer.config.js at the plugin
 * root. Run via the validate skill's e2e mode, or directly:
 *
 *   npm run test:e2e
 *
 * Prerequisites (handled by `run`): the Docker stack is up, the plugin is
 * active, and a public page containing the block is published. The default
 * target page is the seeded `campus-directory-page` page (override with
 * CAMPUS_DIRECTORY_E2E_URL).
 */

const PAGE_URL =
	process.env.CAMPUS_DIRECTORY_E2E_URL ||
	'https://wp-dev.ucsc/campus-directory-page/';

describe( 'Campus Directory block (frontend)', () => {
	beforeAll( async () => {
		await page.goto( PAGE_URL, { waitUntil: 'networkidle0', timeout: 30000 } );
	} );

	it( 'renders the block container server-side', async () => {
		await page.waitForSelector( '.ucsc-block-directory', { timeout: 15000 } );
		expect( await page.$( '.ucsc-block-directory' ) ).not.toBeNull();
	} );

	it( 'renders directory item cards with profile details', async () => {
		await page.waitForSelector( '.section-item.h-card', { timeout: 15000 } );

		const cardCount = await page.$$eval(
			'.section-item.h-card',
			( cards ) => cards.length
		);
		expect( cardCount ).toBeGreaterThan( 0 );

		// Check the first profile card has a name
		const firstProfileName = await page.$eval(
			'.section-item.h-card:first-child .item-name .p-name',
			( el ) => el.textContent.trim()
		);
		expect( firstProfileName.length ).toBeGreaterThan( 0 );

		// Check the first profile card has department details
		const departmentLabel = await page.$eval(
			'.section-item.h-card:first-child .item-info li strong',
			( el ) => el.textContent.trim()
		);
		expect( departmentLabel ).toBe( 'Department' );
	} );

	it( 'displays profile pictures for cards', async () => {
		const photoSelector = '.section-item.h-card img.item-image';
		await page.waitForSelector( photoSelector, { timeout: 5000 } );
		
		const photoSrc = await page.$eval( photoSelector, ( el ) =>
			el.getAttribute( 'src' )
		);
		expect( photoSrc.length ).toBeGreaterThan( 0 );
	} );
} );
