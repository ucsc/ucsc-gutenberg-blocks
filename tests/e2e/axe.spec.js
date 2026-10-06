/**
 * Automated WCAG 2.1 A/AA scan of each block's frontend (WPM-203).
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh, which seeds the target pages first.
 *
 * Uses the axe-core already installed as a dependency of @wordpress/scripts,
 * so no new package. Each scan is scoped to the block's own root, so theme
 * header/footer issues do not count against the plugin.
 *
 * Fails on serious/critical violations only; the failure lists each axe rule
 * with a count and an example node. All four pages were clean on 2026-10-05.
 * Most open WPM A11Y issues (click-only rows, missing aria-expanded/-sort,
 * duplicate tab stops) are behavioural and invisible to a static scan; they
 * are proven in keyboard.spec.js instead. This file guards against static
 * regressions (missing alt/labels/names, contrast, invalid ARIA).
 *
 * A clean scan is only meaningful if axe actually ran against the block, so
 * each page also plants a known violation and requires axe to report it.
 */

const path = require( 'path' );
const { PAGES, shimPage, gotoPage } = require( './utils/e2e-helpers' );

const AXE_PATH = path.join( path.dirname( require.resolve( 'axe-core' ) ), 'axe.min.js' );

beforeAll( () => shimPage( page ) );

async function seriousViolations( selector ) {
	await page.addScriptTag( { path: AXE_PATH } );
	return page.evaluate( async ( sel ) => {
		const results = await window.axe.run( sel, {
			runOnly: { type: 'tag', values: [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa' ] },
			resultTypes: [ 'violations' ],
		} );
		return results.violations
			.filter( ( v ) => v.impact === 'serious' || v.impact === 'critical' )
			.map( ( v ) => `${ v.impact } ${ v.id } x${ v.nodes.length }: ${ v.nodes[ 0 ].target.join( ' ' ) }` );
	}, selector );
}

describe.each( [
	[ 'Course Catalog', PAGES.courseCatalog, '#courseCatalog' ],
	[ 'Class Schedule', PAGES.classSchedule, '#classSchedule' ],
	[ 'Campus Directory (in-site links)', PAGES.campusDirectory, '.ucsc-block-directory' ],
	[ 'Campus Directory (link-out)', PAGES.campusDirectoryLals, '.ucsc-block-directory' ],
] )( 'axe: %s', ( _name, url, selector ) => {
	it( 'has no serious or critical WCAG 2.1 A/AA violations', async () => {
		await gotoPage( page, url );
		expect( await page.$( selector ) ).not.toBeNull();
		expect( await seriousViolations( selector ) ).toEqual( [] );
	} );

	it( 'reports a violation planted inside the block (the scan is live)', async () => {
		await gotoPage( page, url );
		await page.$eval( selector, ( root ) => {
			const img = document.createElement( 'img' );
			img.src = 'data:image/gif;base64,R0lGODlhAQABAAAAACw=';
			root.appendChild( img );
		} );
		const found = await seriousViolations( selector );
		expect( found.some( ( v ) => v.includes( 'image-alt' ) ) ).toBe( true );
	} );
} );
