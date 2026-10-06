/**
 * Block-editor smoke test for course-catalog, class-schedule and
 * campus-directory (WPM-204).
 *
 * Harness: `wp-scripts test-e2e` (Jest + puppeteer-core driving containerized
 * Chromium). Run via tests/e2e/run-e2e.sh.
 *
 * Every other e2e spec drives the frontend; the editor is otherwise covered
 * only by Jest with mocked @wordpress/* packages, so a broken editor bundle
 * (e.g. from a toolchain change such as Node 22, WPM-196) would pass every
 * suite. This inserts each block into a real editor, waits for its REST-fed
 * dropdowns, and saves.
 *
 * Logs in with the local dev credentials (UCSC_WP_USER / UCSC_WP_PASS
 * override) in an incognito context, so the session never reaches the
 * frontend specs. campus-directory's dropdowns need LDAP (UCSC VPN).
 * Every page this creates is force-deleted afterwards.
 */

const {
	shimPage,
	captureErrors,
	adminPage,
	openNewPageInEditor,
	insertBlock,
	editorBlock,
} = require( './utils/e2e-helpers' );

jest.setTimeout( 120000 );

// Text the dropdown components show until their fetch resolves.
const STILL_LOADING = /Dropdown Loading\.\.\./;

const BLOCKS = [
	[ 'ucscblocks/coursecatalog', { saves: true } ],
	[ 'ucscblocks/classschedule', { saves: true } ],
	// A fresh Campus Directory block uses automated feeds with no department
	// chosen, which deliberately locks saving (WPM-153).
	[ 'ucscblocks/campusdirectory', { saves: false } ],
];

describe.each( BLOCKS )( 'Editor: %s', ( blockName, { saves } ) => {
	let context;
	let p;
	let errors;
	let clientId;
	let postId;

	beforeAll( async () => {
		( { context, page: p } = await adminPage() );
		shimPage( p );
		errors = captureErrors( p );
		await openNewPageInEditor( p );
		postId = await p.evaluate( () =>
			wp.data.select( 'core/editor' ).getCurrentPostId()
		);
		clientId = await insertBlock( p, blockName );
	} );

	afterAll( async () => {
		if ( p && postId ) {
			await p
				.evaluate(
					( id ) =>
						wp.apiFetch( {
							path: `/wp/v2/pages/${ id }?force=true`,
							method: 'DELETE',
						} ),
					postId
				)
				.catch( () => {} );
		}
		if ( context ) {
			await context.close();
		}
	} );

	it( 'is registered in the editor', async () => {
		expect(
			await p.evaluate( ( n ) => !! wp.blocks.getBlockType( n ), blockName )
		).toBe( true );
	} );

	it( 'renders in the canvas without a block error', async () => {
		const { handle } = await editorBlock( p, clientId );
		expect(
			await handle.evaluate( ( el ) =>
				!! el.querySelector( '.block-editor-warning' )
			)
		).toBe( false );
		expect(
			await p.evaluate(
				( id ) => wp.data.select( 'core/block-editor' ).getBlock( id ).isValid,
				clientId
			)
		).toBe( true );
	} );

	// campus-directory hides all of its controls behind this notice when
	// requirements() (classes/CampusDirectory.php) finds no ldap_api_key. On
	// wp-dev.ucsc the frontend binds LDAP anonymously under DOCKER_DEV
	// (CampusDirectoryAPI.php), so the notice there is wrong: the block renders
	// people but cannot be configured. RED on wp-dev.ucsc until requirements()
	// honours DOCKER_DEV the way the API does.
	it( 'shows its settings rather than a "not Configured Correctly" notice', async () => {
		await new Promise( ( r ) => setTimeout( r, 3000 ) );
		expect( await p.evaluate( () => document.body.innerText ) ).not.toMatch(
			/not Configured Correctly/
		);
	} );

	// Absence of "Loading..." alone passes when no dropdown mounted at all, so
	// also require a <select> filled from the REST endpoint (the departments,
	// subjects and divisions lists all run to dozens of options).
	it( 'loads its REST-fed dropdowns', async () => {
		await p.waitForFunction(
			( re ) =>
				! new RegExp( re ).test( document.body.innerText ) &&
				Array.from( document.querySelectorAll( 'select' ) ).some(
					( s ) => s.options.length > 10
				),
			{ timeout: 30000 },
			STILL_LOADING.source
		);
	} );

	it( 'raises no uncaught errors and no plugin console errors', () => {
		const pluginConsoleErrors = errors.consoleErrors.filter( ( m ) =>
			/ucsc|ucscblocks|ucscgutenbergblocks/i.test( m )
		);
		expect( errors.pageErrors ).toEqual( [] );
		expect( pluginConsoleErrors ).toEqual( [] );
	} );

	if ( saves ) {
		it( 'saves, and the saved page contains the block', async () => {
			await p.evaluate( () => wp.data.dispatch( 'core/editor' ).savePost() );
			await p.waitForFunction(
				() => ! wp.data.select( 'core/editor' ).isSavingPost(),
				{ timeout: 30000 }
			);
			expect(
				await p.evaluate( () =>
					wp.data.select( 'core/editor' ).didPostSaveRequestSucceed()
				)
			).toBe( true );

			const raw = await p.evaluate(
				( id ) =>
					wp
						.apiFetch( { path: `/wp/v2/pages/${ id }?context=edit` } )
						.then( ( r ) => r.content.raw ),
				postId
			);
			expect( raw ).toContain( `<!-- wp:${ blockName }` );
		} );
	} else {
		it( 'locks saving and says why until a department is chosen', async () => {
			expect(
				await p.evaluate( () =>
					wp.data.select( 'core/editor' ).isPostSavingLocked()
				)
			).toBe( true );
			expect( await p.evaluate( () => document.body.innerText ) ).toContain(
				'Unable to publish'
			);
		} );
	}
} );
