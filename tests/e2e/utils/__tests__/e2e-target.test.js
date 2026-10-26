/**
 * Unit tests for the e2e target resolver (WPM-235). Run by `npm test`, not by
 * the e2e runner (its testMatch only picks up *.spec.js).
 */

const { LOCAL_DEFAULTS, resolveTarget } = require( '../e2e-target' );

const NONPROD = 'https://test-henryh.wordpress-dev.ucsc.edu/campus-directory-test/';
const PROD = 'https://www.ucsc.edu/people/';

describe( 'resolveTarget', () => {
	it( 'defaults to local with the wp-dev.ucsc seed URLs', () => {
		const t = resolveTarget( {} );
		expect( t.target ).toBe( 'local' );
		expect( t.hosted ).toBe( false );
		expect( t.pages ).toEqual( LOCAL_DEFAULTS );
		expect( t.missing ).toEqual( {} );
	} );

	it( 'lets a local run override one page URL without host checks', () => {
		const t = resolveTarget( { UCSC_CD_E2E_URL: 'https://wp-dev.ucsc/other/' } );
		expect( t.pages.campusDirectory ).toBe( 'https://wp-dev.ucsc/other/' );
		expect( t.pages.classSchedule ).toBe( LOCAL_DEFAULTS.classSchedule );
	} );

	it( 'rejects an unknown target', () => {
		expect( () => resolveTarget( { UCSC_E2E_TARGET: 'staging' } ) ).toThrow(
			/UCSC_E2E_TARGET must be one of/
		);
	} );

	describe( 'hosted-nonprod', () => {
		const env = { UCSC_E2E_TARGET: 'hosted-nonprod' };

		it( 'never falls back to local URLs and reports what is missing', () => {
			const t = resolveTarget( env );
			expect( t.hosted ).toBe( true );
			expect( Object.values( t.pages ) ).toEqual( [ undefined, undefined, undefined, undefined ] );
			expect( t.missing.campusDirectory ).toBe( 'UCSC_CD_E2E_URL' );
		} );

		it( 'accepts a wordpress-dev subsite URL', () => {
			const t = resolveTarget( { ...env, UCSC_CD_E2E_URL: NONPROD } );
			expect( t.pages.campusDirectory ).toBe( NONPROD );
			expect( t.missing.campusDirectory ).toBeUndefined();
		} );

		it.each( [
			[ 'the local stack', 'https://wp-dev.ucsc/eeb/people/' ],
			[ 'prod', PROD ],
			[ 'a look-alike domain', 'https://evilwordpress-dev.ucsc.edu/' ],
		] )( 'rejects %s', ( _label, url ) => {
			expect( () => resolveTarget( { ...env, UCSC_CD_E2E_URL: url } ) ).toThrow(
				/UCSC_CD_E2E_URL must be on \*\.wordpress-dev\.ucsc\.edu/
			);
		} );

		it( 'rejects plain http', () => {
			expect( () =>
				resolveTarget( { ...env, UCSC_CS_E2E_URL: NONPROD.replace( 'https', 'http' ) } )
			).toThrow( /UCSC_CS_E2E_URL must use https/ );
		} );

		it( 'rejects a value that is not a URL', () => {
			expect( () => resolveTarget( { ...env, UCSC_CC_E2E_URL: 'course-catalog-test' } ) ).toThrow(
				/UCSC_CC_E2E_URL is not a valid URL/
			);
		} );
	} );

	describe( 'hosted-prod', () => {
		const env = { UCSC_E2E_TARGET: 'hosted-prod', UCSC_E2E_ALLOW_PROD: '1' };

		it( 'refuses to run without UCSC_E2E_ALLOW_PROD=1', () => {
			expect( () =>
				resolveTarget( { UCSC_E2E_TARGET: 'hosted-prod', UCSC_CD_E2E_URL: PROD } )
			).toThrow( /needs UCSC_E2E_ALLOW_PROD=1/ );
			expect( () =>
				resolveTarget( { UCSC_E2E_TARGET: 'hosted-prod', UCSC_E2E_ALLOW_PROD: 'yes' } )
			).toThrow( /needs UCSC_E2E_ALLOW_PROD=1/ );
		} );

		it( 'accepts a prod URL when allowed', () => {
			const t = resolveTarget( { ...env, UCSC_CD_E2E_URL: PROD } );
			expect( t.target ).toBe( 'hosted-prod' );
			expect( t.pages.campusDirectory ).toBe( PROD );
		} );

		it.each( [
			[ 'a wordpress-dev URL', NONPROD ],
			[ 'the local stack', 'https://wp-dev.ucsc/eeb/people/' ],
		] )( 'rejects %s', ( _label, url ) => {
			expect( () => resolveTarget( { ...env, UCSC_CD_E2E_URL: url } ) ).toThrow(
				/is a non-prod or local host, not prod/
			);
		} );
	} );
} );
