/**
 * Shared helpers for the e2e specs (WPM-199 to WPM-204).
 *
 * Not a spec: jest's e2e testMatch only picks up `*.spec.js` and `specs/`.
 */

const PAGES = {
	classSchedule:
		process.env.UCSC_CS_E2E_URL || 'https://wp-dev.ucsc/class-schedule-e2e/',
	courseCatalog:
		process.env.UCSC_CC_E2E_URL || 'https://wp-dev.ucsc/course-catalog-e2e/',
	campusDirectory:
		process.env.UCSC_CD_E2E_URL || 'https://wp-dev.ucsc/eeb/people/',
	campusDirectoryLals:
		process.env.UCSC_CD_LALS_E2E_URL ||
		'https://wp-dev.ucsc/lals/people/faculty/',
};

const SITE_ORIGIN = new URL( PAGES.classSchedule ).origin;

// puppeteer-core 13 + jest-environment-puppeteer expects removeListener.
function shimPage( p ) {
	if ( p && ! p.removeListener ) {
		p.removeListener = p.off || p.removeEventListener;
	}
	return p;
}

async function gotoPage( p, url ) {
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
	PAGES,
	SITE_ORIGIN,
	shimPage,
	gotoPage,
	captureErrors,
	tabStopsWithin,
	adminPage,
	openNewPageInEditor,
	insertBlock,
	editorBlock,
};
