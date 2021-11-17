## About The Plugin

This is a plugin that contains multiple custom gutenberg blocks.

## Getting Started

The following will cover preqrequisites, development environment setup, basic block development and how to contribute.

### Prerequisites

- A local wordpress development environment.

  - https://localwp.com/ is a tool to spin up and manage local wp sites

- Node/npm
  - With MacOS I use the following:
- https://brew.sh/

```
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

- nvm: https://github.com/nvm-sh/nvm

```
nvm install node
```

- Make sure it all works

```
node --version
```

```
npm --version
```

### Development Environment Setup

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
git clone https://github.com/ucsc/ucsc-gutenberg-blocks.git
```

- Install and start wp-scripts

```
cd ucsc-gutenberg-blocks
```

```
npm install
```

```
npm run start
```

### Basic Block Development

As a reference a commit to adding a demo block to this repo can be found here: https://github.com/ucsc/ucsc-gutenberg-blocks/commit/10dafbecede6286ae2ad2868b58e61d90443dc08

This commit show's how to create a Dynamic Block vs a Static block. There are many benefits to using Dynamic Blocks, here are some resources discussing the benefits:

- https://design.oit.ncsu.edu/2019/03/11/choosing-dynamic-blocks-one/
- https://www.youtube.com/watch?v=0EtQO1kx8Vg

#### Instructions:

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
- Check `git diff` to see files that have been modified.
- Git add the files you have modified or added: `git add <filename> <filename> <filename>` or use `git add .` if all the modified files need to be checked in
- Commit with a good message `git commit -m "feat: ✨ Updating README"`
- push code to github: `git push origin feature/site-level-admin-form`
- In GitHub create a PR into the main branch
