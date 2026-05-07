
## About The Plugin
A WordPress plugin providing UCSC custom Gutenberg blocks: class schedule, course catalog, and campus directory as seen at:
- https://history.ucsc.edu/courses/?sub=HIS
- https://literature.ucsc.edu/class-schedule/course-catalog/
- https://campusdirectory.ucsc.edu/cd_department?ou=soe

### Development Setup Instructions
- Follow the setup instructions in the [wp-dev.ucsc README](https://github.com/ucsc/wp-dev.ucsc)

### How To Contribute Code / Develop
- From the wordpress root (by default wp-dev.ucsc): cd public/wp-content/plugins/ucsc-gutenberg-blocks
- This is a separate repo that gets cloned to this directory during the initial setup `setup.sh`
- Create Feature branch `git checkout -b "feature/WPM-xxx_my_feature"`
- Write code, see [Anatomy of a Custom Block](CustomBlock.md)
- Commit and push your changes, then create a PR into the `main` branch on GitHub
- Instructions for pushing to the development and production campus press servers can be found https://docs.google.com/document/d/1XFb_JTMC8SP3HuwXVbqc6exMKpfMfYiO0XRE_QH8tjU/edit?tab=t.0#heading=h.pt501scbu4vi

### Basic Block Development

As a reference a commit to adding a demo block to this repo can be found here: https://github.com/ucsc/ucsc-gutenberg-blocks/commit/10dafbecede6286ae2ad2868b58e61d90443dc08

This commit shows how to create a Dynamic Block vs a Static Block. There are many benefits to using Dynamic Blocks, here are some resources discussing the benefits:

- https://design.oit.ncsu.edu/2019/03/11/choosing-dynamic-blocks-one/
- https://www.youtube.com/watch?v=0EtQO1kx8Vg


#### Instructions:
```
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

From the `wp-dev.ucsc` project root, run tests inside Docker:

```bash
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npm test
```

Or to run a single test file:

```bash
docker compose -f docker-compose.yml -f docker-compose-start.yml run --rm \
  -w /var/www/html/wp-content/plugins/ucsc-gutenberg-blocks \
  plugin_npm_start npx wp-scripts test-unit-js --testPathPattern=ClassSchedule
```

### Writing Tests

Test files live in `src/blocks/__tests__/` and follow the naming convention `BlockName.test.js`. Since WordPress packages like `@wordpress/components` are provided at runtime (not installed as dependencies), they must be mocked with `{ virtual: true }`:

```js
jest.mock('@wordpress/components', () => ({
  Panel: ({ children }) => <div>{children}</div>,
}), { virtual: true });
```

Child components (dropdowns, layouts, etc.) are also mocked so tests focus on the block's own logic rather than its children.

## VScode/Xdebug setup
```
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
