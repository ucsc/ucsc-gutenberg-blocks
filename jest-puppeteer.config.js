/**
 * Puppeteer launch config for `wp-scripts test-e2e`.
 *
 * The wp-dev.ucsc stack serves the site over a self-signed cert at the vanity
 * host https://wp-dev.ucsc. `@wordpress/scripts` ships only `puppeteer-core`
 * (no bundled Chromium), so we drive a real Chromium binary directly.
 *
 * The supported entry point is `tests/e2e/run-e2e.sh`, which runs this suite
 * inside a Node+Chromium container (no host Node/Chrome needed). In that
 * container UCSC_E2E_IN_CONTAINER=1 is set and `wp-dev.ucsc` resolves to the
 * host via `--add-host=wp-dev.ucsc:host-gateway`, reaching the host's published
 * 443. A host fallback (developer Chrome + a 127.0.0.1 host-resolver rule, the
 * same trick `run/driver.sh drive` uses) is kept for ad-hoc local runs.
 *
 * Overrides: PUPPETEER_EXECUTABLE_PATH (browser binary), PUPPETEER_HEADED=1.
 */
const inContainer = !! process.env.UCSC_E2E_IN_CONTAINER;

const executablePath =
	process.env.PUPPETEER_EXECUTABLE_PATH ||
	( inContainer
		? '/usr/bin/chromium'
		: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' );

const args = [
	'--ignore-certificate-errors', // self-signed cert on the vanity host
	'--no-sandbox',
	'--disable-setuid-sandbox',
];

if ( inContainer ) {
	// DNS for wp-dev.ucsc comes from the container's /etc/hosts (--add-host).
	args.push( '--disable-dev-shm-usage' );
} else {
	// Host run: map the vanity host to the local stack, no /etc/hosts edit.
	args.push( '--host-resolver-rules=MAP wp-dev.ucsc 127.0.0.1' );
}

module.exports = {
	launch: {
		executablePath,
		headless: process.env.PUPPETEER_HEADED ? false : 'new',
		acceptInsecureCerts: true,
		ignoreHTTPSErrors: true,
		args,
	},
	browserContext: 'default',
};
