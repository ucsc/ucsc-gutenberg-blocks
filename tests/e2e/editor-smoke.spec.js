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
	SITE_ORIGIN,
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
	// requirements() (classes/CampusDirectory.php) finds no ldap_api_key. Local
	// dev binds LDAP as production does (WPM-234), so the rig must set
	// LDAP_API_KEY; RED here means the rig is missing it.
	it( 'shows its settings rather than a "not Configured Correctly" notice', async () => {
		await new Promise( ( r ) => setTimeout( r, 3000 ) );
		const { handle } = await editorBlock( p, clientId );
		expect( await handle.evaluate( ( el ) => el.innerText ) ).not.toMatch(
			/not Configured Correctly/
		);
	} );

	// Absence of "Loading..." alone passes when no dropdown mounted at all, so
	// also require a <select> filled from the REST endpoint (the departments,
	// subjects and divisions lists all run to dozens of options).
	// The dropdowns render in the block body, which is inside the canvas
	// iframe when WordPress iframes the editor (apiVersion 3, WPM-205).
	it( 'loads its REST-fed dropdowns', async () => {
		const { frame } = await editorBlock( p, clientId );
		await frame.waitForFunction(
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
			const { handle } = await editorBlock( p, clientId );
			expect( await handle.evaluate( ( el ) => el.innerText ) ).toContain(
				'Unable to publish'
			);
		} );

		// WPM-250: Editoria11y flagged an H2 to H5 skip from the settings labels.
		// The only headings left are the H2 titles WordPress's Panel/PanelBody render.
		it( 'adds no heading below H2 in the editor, so the outline cannot skip a level', async () => {
			const { handle } = await editorBlock( p, clientId );
			expect(
				await handle.evaluate(
					( el ) => el.querySelectorAll( 'h3, h4, h5, h6' ).length
				)
			).toBe( 0 );
		} );

		// WPM-250: the editor outline Editoria11y checks on a page like
		// innovation.ucsc.edu/about-us/contact-us: page title, an author H2,
		// then this block. No heading may be more than one level deeper than
		// the heading before it.
		it( 'keeps the editor heading outline free of skipped levels under an author H2', async () => {
			await p.evaluate( () => {
				wp.data.dispatch( 'core/block-editor' ).insertBlocks(
					wp.blocks.createBlock( 'core/heading', { level: 2, content: 'Contact Us' } ),
					0
				);
			} );
			const { frame } = await editorBlock( p, clientId );
			const levels = await frame.evaluate( () =>
				Array.from( document.querySelectorAll( 'h1, h2, h3, h4, h5, h6' ) ).map(
					( h ) => Number( h.tagName[ 1 ] )
				)
			);
			const skips = levels.filter( ( level, i ) => i > 0 && level > levels[ i - 1 ] + 1 );
			expect( { levels, skips } ).toEqual( { levels: expect.arrayContaining( [ 2 ] ), skips: [] } );
		} );

		// WPM-92: authors pick the person-name heading level.
		it( 'offers the person-name heading level setting, defaulting to H3', async () => {
			const { handle } = await editorBlock( p, clientId );
			expect(
				await handle.evaluate( ( el ) => {
					const label = Array.from( el.querySelectorAll( 'label' ) ).find(
						( l ) => l.textContent.trim() === 'Person name heading level'
					);
					const select = label && el.querySelector( `#${ label.htmlFor }` );
					return select ? select.value : null;
				} )
			).toBe( '3' );
		} );
	}
} );

// WPM-254: opening a saved page must not mark it as edited. The blocks used to
// call setAttributes while rendering whenever an attribute was unset (here
// `subject`), so every author who opened one of these pages got an "unsaved
// changes" prompt on leaving. The saved markup matches the e2e pages.
const SAVED_BLOCKS = [
	[ 'ucscblocks/coursecatalog', '{"subjectOrDept":"dept","department":"CSE"}' ],
	[ 'ucscblocks/classschedule', '{"subjectOrDept":"dept","department":"CSE"}' ],
];

describe.each( SAVED_BLOCKS )( 'Editor, saved page: %s', ( blockName, attrs ) => {
	let context;
	let p;
	let draftId;
	let pageId;

	beforeAll( async () => {
		( { context, page: p } = await adminPage() );
		shimPage( p );
		await openNewPageInEditor( p );
		draftId = await p.evaluate( () =>
			wp.data.select( 'core/editor' ).getCurrentPostId()
		);
		pageId = await p.evaluate(
			( content ) =>
				wp
					.apiFetch( {
						path: '/wp/v2/pages',
						method: 'POST',
						data: { title: 'WPM-254 e2e', status: 'draft', content },
					} )
					.then( ( r ) => r.id ),
			`<!-- wp:${ blockName } ${ attrs } /-->`
		);
		await p.goto( `${ SITE_ORIGIN }/wp-admin/post.php?post=${ pageId }&action=edit`, {
			waitUntil: 'domcontentloaded',
			timeout: 60000,
		} );
		await p.waitForFunction(
			() =>
				window.wp &&
				wp.data &&
				wp.data.select( 'core/editor' ) &&
				wp.data.select( 'core/editor' ).getCurrentPostId() &&
				wp.data.select( 'core/block-editor' ).getBlocks().length > 0,
			{ timeout: 60000 }
		);
		const clientId = await p.evaluate(
			() => wp.data.select( 'core/block-editor' ).getBlocks()[ 0 ].clientId
		);
		// Wait for the REST-fed dropdowns, so every component has mounted and run.
		const { frame } = await editorBlock( p, clientId );
		await frame.waitForFunction(
			( re ) =>
				! new RegExp( re ).test( document.body.innerText ) &&
				Array.from( document.querySelectorAll( 'select' ) ).some(
					( s ) => s.options.length > 10
				),
			{ timeout: 30000 },
			STILL_LOADING.source
		);
	} );

	afterAll( async () => {
		if ( p ) {
			for ( const id of [ pageId, draftId ] ) {
				if ( ! id ) {
					continue;
				}
				await p
					.evaluate(
						( i ) =>
							wp.apiFetch( {
								path: `/wp/v2/pages/${ i }?force=true`,
								method: 'DELETE',
							} ),
						id
					)
					.catch( () => {} );
			}
		}
		if ( context ) {
			await context.close();
		}
	} );

	it( 'opens without marking the page as edited', async () => {
		const state = await p.evaluate( () => ( {
			dirty: wp.data.select( 'core/editor' ).isEditedPostDirty(),
			edits: Object.keys( wp.data.select( 'core/editor' ).getPostEdits() ),
		} ) );
		// On failure the edits list names what changed (e.g. content, blocks).
		expect( state ).toEqual( { dirty: false, edits: [] } );
	} );
} );
