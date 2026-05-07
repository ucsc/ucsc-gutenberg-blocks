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
};
