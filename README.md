# UC Santa Cruz Service Blocks

A collection of WordPress blocks Service Block for UC Santa Cruz. Additional blocks may be added as use cases arise.

![GitHub Release](https://img.shields.io/github/v/release/ucsc/ucsc-service-blocks?logo=github&logoColor=%23fdc700&labelColor=%23003c6c&color=%23fdc700)
 ![GitHub issues](https://img.shields.io/github/issues/ucsc/ucsc-service-blocks?logo=github&logoColor=%23fdc700&labelColor=%23003c6c&color=%23fdc700) ![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/ucsc/ucsc-service-blocks/release.yml?logo=github&logoColor=%23fdc700&labelColor=%23003c6c&color=%23fdc700)

## TL;DR

Assumes you already have a local environment.

```bash
cd wp-content/plugins/
git clone https://github.com/ucsc/ucsc-service-blocks.git
cd ucsc-service-blocks
npm install
composer install
npm run start
```

See `package.json` file for additional `npm` scripts. See `composer.json` file for `composer` scripts.

## Current Blocks

- **UCSC Course Catalog** -- a block for embedding a course catalog onto a post or page
- **UCSC Class Schedule** -- a block for embedding a class schedule onto a post or page
- **UCSC Campus Directory** -- a block for embedding a campus directory onto a post or page

## Requirements

Cloning and installing this plugin requires:

- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
- [GitHub Cli](https://cli.github.com/manual/installation) (optional but helpful)
- [Node.js/npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm) (MacOS and Linux can install with [Homebrew](https://brew.sh/))
- [Composer](https://getcomposer.org/)

## Getting Started

The following will cover preqrequisites, development environment setup, basic block development and how to contribute.

### Local development environment

Block development will require a local WordPress development environment.

#### WP-ENV

- [@wordpress/env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/).

#### Localwp.com

- <https://localwp.com/> is a tool to spin up and manage local wp sites

### Development Environment Setup (Local.wp)

- Create a multisite local site with localwp

- In order for PHP Intelephense to work in VS Code open it with in the public directory.

```
cd ~/Local\ Sites/kumar-demo/app/public/
```

```
code .
```

- In vscode's terminal clone this repo into the plugin directory

```
cd wp-content/plugins/
```

```
git clone https://github.com/ucsc/ucsc-service-blocks.git
```

- Install and start wp-scripts

```
cd ucsc-service-blocks
```

```
npm install
```

```
composer install
```

```
npm run start
```

### Basic Block Development

As a reference a commit to adding a demo block to this repo can be found here: <https://github.com/ucsc/ucsc-gutenberg-blocks/commit/10dafbecede6286ae2ad2868b58e61d90443dc08>

This commit show's how to create a Dynamic Block vs a Static block. There are many benefits to using Dynamic Blocks, here are some resources discussing the benefits:

- <https://design.oit.ncsu.edu/2019/03/11/choosing-dynamic-blocks-one/>
- <https://www.youtube.com/watch?v=0EtQO1kx8Vg>

#### Instructions

- Create a file in the `classes/` to hold the PHP/Wordpress code.
    - Actions can be added
    - Blocks can be registered
    - Site and Network settings
- Include the new file in the `index.php` file and instantiate the class
- Create a js file in `src/blocks/` directory
    - This file can import libs and components but is not a component itself
    - Create and export a function where you can register the block.
    - Make sure the name you are registering here matches the name you registered in PHP
- In `src/index.js` import your function and call it so that the block gets registered.

### How To Contribute

- Create Feature branch `git checkout -b "feature/site-level-admin-form"`
- Write code
    - See [Anatomy of a Custom Block](CustomBlock.md)
- Check `git diff` to see files that have been modified.
- Git add the files you have modified or added: `git add <filename> <filename> <filename>` or use `git add .` if all the modified files need to be checked in
- Commit with a good message `git commit -m "feat: ✨ Updating README"`
- push code to github: `git push origin feature/site-level-admin-form`
- In GitHub create a PR into the main branch
