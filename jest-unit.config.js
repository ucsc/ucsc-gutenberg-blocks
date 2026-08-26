const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config' );

module.exports = {
	...defaultConfig,
	setupFiles: [
		...( defaultConfig.setupFiles || [] ),
		'<rootDir>/jest-setup.js',
	],
	// Remove enzyme-to-json serializer — its cheerio dependency requires
	// Node 18+ APIs (ReadableStream). We use @testing-library/react, not enzyme.
	snapshotSerializers: [],

	// Coverage (WPM-116). collectCoverageFrom must be explicit: without it Jest
	// reports only on modules some test happened to import, silently omitting
	// every untouched file — which is exactly the set we are trying to find.
	collectCoverageFrom: [
		'src/**/*.js',
		'!src/**/__tests__/**',
		'!src/**/*.test.js',
		'!**/node_modules/**',
	],
	// json-summary and lcov are what the coverage report ingests; text-summary
	// keeps the terminal output short.
	coverageReporters: [ 'text-summary', 'json-summary', 'lcov' ],
	coverageDirectory: 'coverage',
};
