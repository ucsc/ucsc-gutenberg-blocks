# How to Add a New Custom Block
This file includes the basics of what to add and what to change to add a 
new custom block to this plugin.

## Plugin Files
These are the core plugin files. You will add pointers to your custom block files.
### index.php
This is the core plugin file. 
Here you will add a call to include your block's PHP file.
```phpregexp
include(plugin_dir_path(__FILE__) . 'classes/CourseCatalog.php');
```
Then you will instantiate your block's object.
```phpregexp
$CourseCatalog = new CourseCatalog();
```
### src/index.js
This is the core javascript file for the plugin. Similar to the PHP file, you will 
include your block's javascript and instantiate it.
```javascript
import CourseCatalog from './blocks/CourseCatalog';
CourseCatalog();
```
### build/index.js
The wp-scripts node package you add in the [Development Environment Setup](https://github.com/ucsc/ucsc-gutenberg-blocks#development-environment-setup) 
will combine all the files referenced in src/index.js .
## Your Block Files
At this point you should follow the conventions for the code that is already there in another block and name your files in a similar fashion.
### classes/Whatever.php
This is where you declare the class for your block that you're including and instantiating in index.php.
#### Constructor
This is where you tell WordPress about your block's features and what it should do in default cases.
```phpregexp
function __construct() {
```
### src/blocks/Whatever.js
This is basic declaration for the Gutenberg block. We don't fully use Gutenberg blocks because we render the display dynamically, so much of this is placeholder. You do define the name of the block and its icon here.
### src/components/Whatever
Here you keep your front-end Javascript and CSS files. They must be included in your class constructor. See CourseCatalog as an example.
