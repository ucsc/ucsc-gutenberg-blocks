/**
 * Which site the e2e suites drive (WPM-235).
 *
 * UCSC_E2E_TARGET=local (default) drives the wp-dev.ucsc Docker stack with its
 * seeded pages. hosted-nonprod and hosted-prod drive CampusPress sites, where
 * nothing can be seeded or logged into, so page URLs must be given explicitly
 * and are checked against the target's hosts. hosted-prod also needs
 * UCSC_E2E_ALLOW_PROD=1.
 *
 * Plain Node (no puppeteer), so jest-puppeteer.config.js and unit tests can
 * load it.
 */

const TARGETS = [ 'local', 'hosted-nonprod', 'hosted-prod' ];

const URL_VARS = {
	classSchedule: 'UCSC_CS_E2E_URL',
	courseCatalog: 'UCSC_CC_E2E_URL',
	campusDirectory: 'UCSC_CD_E2E_URL',
	campusDirectoryLals: 'UCSC_CD_LALS_E2E_URL',
};

const LOCAL_DEFAULTS = {
	classSchedule: 'https://wp-dev.ucsc/class-schedule-e2e/',
	courseCatalog: 'https://wp-dev.ucsc/course-catalog-e2e/',
	campusDirectory: 'https://wp-dev.ucsc/eeb/people/',
	campusDirectoryLals: 'https://wp-dev.ucsc/lals/people/faculty/',
};

const NONPROD_DOMAIN = 'wordpress-dev.ucsc.edu';
const LOCAL_HOSTS = [ 'wp-dev.ucsc', 'localhost', '127.0.0.1' ];

function isNonprodHost( host ) {
	return host === NONPROD_DOMAIN || host.endsWith( '.' + NONPROD_DOMAIN );
}

function checkHostedUrl( target, varName, value ) {
	let url;
	try {
		url = new URL( value );
	} catch ( e ) {
		throw new Error( `${ varName } is not a valid URL: "${ value }"` );
	}
	if ( url.protocol !== 'https:' ) {
		throw new Error( `${ varName } must use https on ${ target }: "${ value }"` );
	}
	const host = url.hostname;
	if ( target === 'hosted-nonprod' && ! isNonprodHost( host ) ) {
		throw new Error(
			`${ varName } must be on *.${ NONPROD_DOMAIN } for hosted-nonprod: "${ value }"`
		);
	}
	if (
		target === 'hosted-prod' &&
		( isNonprodHost( host ) || LOCAL_HOSTS.includes( host ) )
	) {
		throw new Error(
			`${ varName } is a non-prod or local host, not prod: "${ value }"`
		);
	}
}

/**
 * Resolve and validate the target from the environment. Throws on an unknown
 * target, a prod run without UCSC_E2E_ALLOW_PROD=1, or a page URL on the wrong
 * host for the target.
 *
 * @param {Object} env Environment variables (defaults to process.env).
 * @return {{target: string, hosted: boolean, pages: Object, missing: Object}}
 *         pages maps page names to URLs (undefined when not set on a hosted
 *         target); missing maps those unset page names to their variable.
 */
function resolveTarget( env = process.env ) {
	const target = env.UCSC_E2E_TARGET || 'local';
	if ( ! TARGETS.includes( target ) ) {
		throw new Error(
			`UCSC_E2E_TARGET must be one of ${ TARGETS.join( ', ' ) }; got "${ target }"`
		);
	}
	if ( target === 'hosted-prod' && env.UCSC_E2E_ALLOW_PROD !== '1' ) {
		throw new Error( 'UCSC_E2E_TARGET=hosted-prod also needs UCSC_E2E_ALLOW_PROD=1' );
	}

	const hosted = target !== 'local';
	const pages = {};
	const missing = {};
	Object.entries( URL_VARS ).forEach( ( [ name, varName ] ) => {
		const value = env[ varName ];
		if ( ! hosted ) {
			pages[ name ] = value || LOCAL_DEFAULTS[ name ];
			return;
		}
		if ( ! value ) {
			pages[ name ] = undefined;
			missing[ name ] = varName;
			return;
		}
		checkHostedUrl( target, varName, value );
		pages[ name ] = value;
	} );

	return { target, hosted, pages, missing };
}

module.exports = { TARGETS, URL_VARS, LOCAL_DEFAULTS, resolveTarget };
