
## About The Plugin

A WordPress plugin providing UCSC custom Gutenberg blocks: class schedule, course catalog, and campus directory as seen at:

- [Courses](https://history.ucsc.edu/courses/?sub=HIS)
- [Class Schedule](https://literature.ucsc.edu/class-schedule/course-catalog/)
- [Directory](https://campusdirectory.ucsc.edu/cd_department?ou=soe)

### Development Setup Instructions

- Follow the setup instructions in the [wp-dev.ucsc README](https://github.com/ucsc/wp-dev.ucsc)

### How To Contribute Code / Develop

- From the wordpress root (by default wp-dev.ucsc): cd public/wp-content/plugins/ucsc-gutenberg-blocks
- This is a separate repo that gets cloned to this directory during the initial setup `setup.sh`
- Create Feature branch `git checkout -b "feature/WPM-xxx_my_feature"`
- Write code, see [Anatomy of a Custom Block](CustomBlock.md)
- Commit and push your changes, then create a PR into the `main` branch on GitHub
- [Instructions for pushing](https://docs.google.com/document/d/1XFb_JTMC8SP3HuwXVbqc6exMKpfMfYiO0XRE_QH8tjU/edit?tab=t.0#heading=h.pt501scbu4vi) to the development and production campus press servers

### Basic Block Development

As a reference [a commit to adding a demo block](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/10dafbecede6286ae2ad2868b58e61d90443dc08) to this repo

This commit shows how to create a Dynamic Block vs a Static Block. There are many benefits to using Dynamic Blocks, here are some resources discussing the benefits:

- https://design.oit.ncsu.edu/2019/03/11/choosing-dynamic-blocks-one/
- https://www.youtube.com/watch?v=0EtQO1kx8Vg

#### Instructions

- Create a file in `src/classes` to hold the PHP/Wordpress code.
  - Actions can be added
  - Blocks can be registered
  - Site and Network settings
- Include the new file in the `index.php` file and instantiate the class
- Create a js file in `src/blocks/` directory
  - This file can import libs and components but is not a component itself
  - Create and export a function where you can register the block.
  - Make sure the name you are registering here matches the name you registered in PHP
- In `src/index.js` import your function and call it so that the block gets registered.
- If needed, add JS and CSS component code under `src/components`

## Testing

Unit tests use [Jest](https://jestjs.io/) via `@wordpress/scripts` and [@testing-library/react](https://testing-library.com/docs/react-testing-library/intro/) for rendering Gutenberg block edit components.

### Running Tests

From the plugin directory, the host wrapper runs everything in one-off Docker
containers. It needs only Docker, not the WordPress stack or host PHP/Node:

```bash
scripts/test-tiers.sh
scripts/test-tiers.sh all
scripts/test-tiers.sh gate --js -- --testPathPattern=ClassSchedule
scripts/test-tiers.sh gate --php -- tests/php/CourseCatalogTest.php
```

`gate` (the default) runs the PHP suite and Jest. `all` runs both with coverage.
`--php` or `--js` limits the run to one side, and arguments after `--` go to that
side's runner. The wrapper follows the UCSC testing standard (baseapp
`doc/TESTING-STANDARDS.md`, "Tiers and entry points"): it only starts containers
and calls the standard entry points, which CI can call directly:

| Entry point | Runs in | Does |
| --- | --- | --- |
| `npm test` | Node container | Jest |
| `npm run test:coverage` | Node container | Jest with coverage |
| `composer test` | PHP test container | PHP harness (`tests/php/*Test.php`) |
| `composer test:coverage` | PHP test container (Xdebug) | PHP harness with coverage |
| `composer coverage:snapshot` | PHP test container | Dated coverage snapshot, see below |

The PHP test container is built from `tests/php/Dockerfile.coverage`.

### Coverage

`scripts/test-tiers.sh all` writes the reports to `coverage/` (gitignored):

```text
coverage/clover.xml           PHP, machine-readable
coverage/html/index.html      PHP, per-file report
coverage/lcov.info            JS, machine-readable
coverage/lcov-report/index.html  JS, per-file report
```

When both suites pass, it also writes a dated snapshot to
`docs/coverage/<date>.md` (checked in) with the totals and every file that still
has uncovered lines, and adds a row to `docs/coverage/README.md`, so progress
shows over time. Commit the snapshot when you want to record a milestone.

Coverage counts only the tracked blocks (campus-directory, class-schedule,
course-catalog) and the shared code they use. Out-of-scope blocks are listed in
`tests/coverage-exclude.txt`, which both the PHP harness and `jest-unit.config.js`
read; they are not coverage gaps. E2E tests are pass/fail only and are not
included in coverage percentages.

### Writing Tests

Test files live in `src/blocks/__tests__/` and follow the naming convention `BlockName.test.js`. Since WordPress packages like `@wordpress/components` are provided at runtime (not installed as dependencies), they must be mocked with `{ virtual: true }`:

```javascript
jest.mock('@wordpress/components', () => ({
  Panel: ({ children }) => <div>{children}</div>,
}), { virtual: true });
```

Child components (dropdowns, layouts, etc.) are also mocked so tests focus on the block's own logic rather than its children.

## VScode/Xdebug setup

The [PHP Debug plugin](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) is required. On the debug tab click `Create a launch.json file` and select type `php`.

You can replace the contents of `launch.json` with the following:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html/wp-content/plugins/ucsc-gutenberg-blocks": "${workspaceRoot}"
      },
      "hostname": "wp-dev.ucsc"
    }
  ]
}
```
