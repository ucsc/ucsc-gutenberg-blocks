/**
 * End-to-end test for rich text in the Campus Directory block (WPM-144).
 *
 * The PHP tests stub the WordPress sanitizers and can't see CSS. This runs the real
 * sanitizers and stylesheets against fake people from
 * seed-campus-directory-rich-text-e2e.php, so it needs neither LDAP nor the VPN.
 */

const ORIGIN = new URL(
	process.env.UCSC_CS_E2E_URL || 'https://wp-dev.ucsc/class-schedule-e2e/'
).origin;

const RICH = '.directory-rich-text';
const layoutUrl = ( layout ) =>
	`${ ORIGIN }/campus-directory-rich-text-${ layout }-e2e/`;

async function open( url ) {
	await page.setViewport( { width: 1280, height: 900 } );
	await page.goto( url, { waitUntil: 'load', timeout: 60000 } );
}

// Each layout seeds a formatted person first and an attacking person second.
const inspectLayout = () =>
	page.evaluate( ( selector ) => {
		const [ rich, attack ] = document.querySelectorAll( selector );
		const css = ( el ) => window.getComputedStyle( el );
		const [ first ] = rich.querySelectorAll( 'p' );
		const item = rich.querySelector( 'ul li' );
		const link = item.querySelector( 'a' );
		const bold = rich.querySelector( 'strong' );
		const top = ( el ) => el.getBoundingClientRect().top;
		return {
			count: document.querySelectorAll( selector ).length,
			richHtml: rich.innerHTML,
			boldDisplay: css( bold ).display,
			boldWeight: Number( css( bold ).fontWeight ),
			boldWidth: bold.getBoundingClientRect().width,
			paragraphMargin: parseFloat( css( first ).marginBottom ),
			itemDisplay: css( item ).display,
			itemLines: Math.round(
				item.getBoundingClientRect().height /
					parseFloat( css( item ).lineHeight )
			),
			linkOnItemLine: Math.abs( top( link ) - top( item ) ) < 8,
			bullet: css( rich.querySelector( 'ul' ) ).listStyleType,
			number: css( rich.querySelector( 'ol' ) ).listStyleType,
			attackHtml: attack.innerHTML,
			attackText: attack.textContent,
			overlayStyled: attack.querySelectorAll( '[style]' ).length,
			scripts: attack.querySelectorAll( 'script' ).length,
			handlers: attack.querySelectorAll( '[onerror]' ).length,
			jsHref: ( attack.querySelector( '#rich-js' ) || {} ).href || '',
			xss: window.__richXss,
			footerOutsideBlock: !! document.querySelector(
				'footer:not(.ucsc-block-directory footer)'
			),
			names: document.querySelectorAll( '.ucsc-block-directory .p-name' )
				.length,
		};
	}, RICH );

describe.each( [ 'tiled', 'list', 'table' ] )(
	'Campus Directory rich text: %s layout',
	( layout ) => {
		let result;

		beforeAll( async () => {
			await open( layoutUrl( layout ) );
			result = await inspectLayout();
		} );

		it( 'renders one rich-text block per person', () => {
			expect( result.count ).toBe( 2 );
			expect( result.names ).toBe( 2 );
		} );

		it( 'renders no escaped HTML', () => {
			expect( result.richHtml ).not.toMatch( /&(amp;)*(lt|gt);/ );
		} );

		it( 'shows bold text inline', () => {
			expect( result.boldDisplay ).toBe( 'inline' );
			expect( result.boldWeight ).toBeGreaterThanOrEqual( 600 );
			expect( result.boldWidth ).toBeGreaterThan( 0 );
		} );

		it( 'keeps paragraph spacing', () => {
			expect( result.paragraphMargin ).toBeGreaterThan( 0 );
		} );

		it( 'keeps list markers and each list item on one line', () => {
			expect( result.bullet ).toBe( 'disc' );
			expect( result.number ).toBe( 'decimal' );
			expect( result.itemDisplay ).toBe( 'list-item' );
			expect( result.itemLines ).toBe( 1 );
			expect( result.linkOnItemLine ).toBe( true );
		} );

		it( 'removes style attributes, scripts, and handlers', () => {
			expect( result.overlayStyled ).toBe( 0 );
			expect( result.scripts ).toBe( 0 );
			expect( result.handlers ).toBe( 0 );
			expect( result.jsHref ).not.toMatch( /^javascript:/i );
			expect( result.xss ).toBeUndefined();
		} );

		it( 'leaves script contents as harmless text', () => {
			expect( result.attackText ).toContain( 'window.__richXss=1' );
		} );

		it( 'contains stray and unclosed tags in the block', () => {
			expect( result.attackHtml ).not.toContain( '</div>' );
			expect( result.attackHtml ).toContain( '<strong>unclosed</strong>' );
			expect( result.footerOutsideBlock ).toBe( true );
		} );
	}
);

describe( 'Campus Directory rich text: profile page', () => {
	it( 'keeps formatting', async () => {
		await open( `${ ORIGIN }/?directoryprofilecruzid=cdrichtext` );
		const bold = await page.$eval(
			'.item-expertise strong',
			( el ) => el.textContent
		);
		expect( bold ).toBe( 'bold' );
	} );

	it( 'removes style attributes and scripts', async () => {
		await open( `${ ORIGIN }/?directoryprofilecruzid=cdrichattack` );
		const result = await page.evaluate( () => ( {
			sections: document.querySelectorAll( '.item-expertise' ).length,
			styled: document.querySelectorAll( '.item-expertise [style]' ).length,
			scripts: document.querySelectorAll( '.item-expertise script' ).length,
			xss: window.__richXss,
		} ) );
		expect( result.sections ).toBeGreaterThan( 0 );
		expect( result.styled ).toBe( 0 );
		expect( result.scripts ).toBe( 0 );
		expect( result.xss ).toBeUndefined();
	} );
} );
