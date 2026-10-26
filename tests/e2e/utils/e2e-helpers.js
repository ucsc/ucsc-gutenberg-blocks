/**
 * Shared helpers for the e2e specs (WPM-199 to WPM-204).
 *
 * Not a spec: jest's e2e testMatch only picks up `*.spec.js` and `specs/`.
 */

const { resolveTarget } = require( './e2e-target' );

const TARGET = resolveTarget();
const PAGES = TARGET.pages;

// Local only: hosted targets may span several subsites, so specs there derive
// the origin from the page they are on.
const SITE_ORIGIN = TARGET.hosted
	? undefined
	: new URL( PAGES.classSchedule ).origin;

// Specs that log in, write content, or assert local seed data run only on the
// local target; hosted runs report them as skipped (WPM-235).
const describeLocal = TARGET.hosted ? describe.skip : describe;
const itLocal = TARGET.hosted ? it.skip : it;

/**
 * URL of a named page, or a clear error naming the variable to set when a
 * hosted target was not given one.
 *
 * @param {string} name Key of PAGES.
 * @return {string} Page URL.
 */
function pageUrl( name ) {
	if ( PAGES[ name ] ) {
		return PAGES[ name ];
	}
	throw new Error(
		`${ TARGET.missing[ name ] } is not set; ${ TARGET.target } has no default page URLs`
	);
}

// puppeteer-core 13 + jest-environment-puppeteer expects removeListener.
function shimPage( p ) {
	if ( p && ! p.removeListener ) {
		p.removeListener = p.off || p.removeEventListener;
	}
	return p;
}

async function gotoPage( p, url ) {
	if ( ! url ) {
		throw new Error(
			`No page URL; on ${ TARGET.target } set ${ Object.values( TARGET.missing ).join( ', ' ) }`
		);
	}
	await p.goto( url, { waitUntil: 'networkidle0', timeout: 60000 } );
}

/**
 * Collect uncaught page errors and console errors from the moment this is
 * called. Returns the live arrays.
 */
function captureErrors( p ) {
	const pageErrors = [];
	const consoleErrors = [];
	p.on( 'pageerror', ( err ) => pageErrors.push( String( err ) ) );
	p.on( 'console', ( msg ) => {
		if ( msg.type() === 'error' ) {
			consoleErrors.push( msg.text() );
		}
	} );
	return { pageErrors, consoleErrors };
}

/**
 * Keyboard tab stops inside `rootSelector`, in order.
 *
 * Inserts a focusable sentinel immediately before the root, focuses it, then
 * presses Tab until focus leaves the root (or `max` presses). Each stop is
 * described by a short string plus whether it is in a <tbody> / a given
 * selector, so specs can assert on reachability without depending on the
 * theme's header links.
 */
async function tabStopsWithin( p, rootSelector, max = 200 ) {
	await p.evaluate( ( sel ) => {
		const root = document.querySelector( sel );
		const sentinel = document.createElement( 'button' );
		sentinel.id = 'e2e-tab-sentinel';
		sentinel.textContent = 'sentinel';
		root.parentNode.insertBefore( sentinel, root );
		sentinel.focus();
	}, rootSelector );

	const stops = [];
	for ( let i = 0; i < max; i++ ) {
		await p.keyboard.press( 'Tab' );
		const stop = await p.evaluate( ( sel ) => {
			const el = document.activeElement;
			const root = document.querySelector( sel );
			if ( ! el || ! root || ! root.contains( el ) ) {
				return null;
			}
			const id = el.id ? '#' + el.id : '';
			const cls =
				typeof el.className === 'string' && el.className
					? '.' + el.className.trim().split( /\s+/ ).join( '.' )
					: '';
			return {
				desc: el.tagName.toLowerCase() + id + cls,
				text: ( el.textContent || '' ).trim().slice( 0, 60 ),
				href: el.getAttribute( 'href' ),
				inTbody: !! el.closest( 'tbody' ),
				inThead: !! el.closest( 'thead' ),
			};
		}, rootSelector );
		if ( ! stop ) {
			break;
		}
		stops.push( stop );
	}

	await p.evaluate( () => {
		const s = document.getElementById( 'e2e-tab-sentinel' );
		if ( s ) s.remove();
	} );
	return stops;
}

/**
 * Log into wp-admin in a fresh incognito context so the auth cookie never
 * leaks into the frontend specs that share this browser.
 */
async function adminPage() {
	if ( TARGET.hosted ) {
		throw new Error( `adminPage() logs in and writes; not allowed on ${ TARGET.target }` );
	}
	const context = await browser.createIncognitoBrowserContext();
	const p = shimPage( await context.newPage() );
	await p.goto( SITE_ORIGIN + '/wp-login.php', {
		waitUntil: 'domcontentloaded',
		timeout: 60000,
	} );
	await p.type( '#user_login', process.env.UCSC_WP_USER || 'admin' );
	await p.type( '#user_pass', process.env.UCSC_WP_PASS || 'password' );
	await Promise.all( [
		p.waitForNavigation( { waitUntil: 'domcontentloaded', timeout: 60000 } ),
		p.click( '#wp-submit' ),
	] );
	return { context, page: p };
}

/**
 * Open a new page in the block editor with the welcome guide dismissed.
 */
async function openNewPageInEditor( p ) {
	await p.goto( SITE_ORIGIN + '/wp-admin/post-new.php?post_type=page', {
		waitUntil: 'domcontentloaded',
		timeout: 60000,
	} );
	await p.waitForFunction(
		() =>
			window.wp &&
			wp.data &&
			wp.blocks &&
			wp.data.select( 'core/editor' ) &&
			wp.data.select( 'core/editor' ).getCurrentPostId(),
		{ timeout: 60000 }
	);
	await p.evaluate( () => {
		const sel = wp.data.select( 'core/edit-post' );
		if ( sel && sel.isFeatureActive && sel.isFeatureActive( 'welcomeGuide' ) ) {
			wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'welcomeGuide' );
		}
	} );
}

/**
 * Insert a block by name via the data store, select it, and open the
 * settings sidebar so its InspectorControls render. Returns the clientId.
 */
async function insertBlock( p, name ) {
	const clientId = await p.evaluate( ( blockName ) => {
		const block = wp.blocks.createBlock( blockName );
		wp.data.dispatch( 'core/block-editor' ).insertBlocks( block );
		wp.data.dispatch( 'core/block-editor' ).selectBlock( block.clientId );
		wp.data
			.dispatch( 'core/edit-post' )
			.openGeneralSidebar( 'edit-post/block' );
		return block.clientId;
	}, name );
	return clientId;
}

/**
 * The block's wrapper in the editor canvas, whether or not WordPress put the
 * canvas in an iframe. Returns { frame, handle }.
 */
async function editorBlock( p, clientId, timeout = 15000 ) {
	const sel = `[data-block="${ clientId }"]`;
	const deadline = Date.now() + timeout;
	while ( Date.now() < deadline ) {
		for ( const frame of p.frames() ) {
			const handle = await frame.$( sel ).catch( () => null );
			if ( handle ) {
				return { frame, handle };
			}
		}
		await new Promise( ( r ) => setTimeout( r, 250 ) );
	}
	throw new Error( `Block ${ clientId } never appeared in the editor canvas` );
}

module.exports = {
	TARGET,
	PAGES,
	SITE_ORIGIN,
	describeLocal,
	itLocal,
	pageUrl,
	shimPage,
	gotoPage,
	captureErrors,
	tabStopsWithin,
	adminPage,
	openNewPageInEditor,
	insertBlock,
	editorBlock,
};
