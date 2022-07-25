# How to Add a New Custom Block
This file includes the basics of what to add and what to change to add a
new custom block to this plugin.

## Plugin Files
These are the core plugin files. You will add pointers to your custom block files.
### index.php
This is the core plugin file.
Here you will add a call to include your block's PHP file.
```phpregexp
include_once(plugin_dir_path(__FILE__) . 'classes/CourseCatalog.php');
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
This is where you declare the class for your block that you're including and instantiating in index.php. EVERYTHING else stems from this.

Any data you fetch from an API must be cached to work with CampusPress code standards.
#### Constructor
This is where you tell WordPress about your block's features and what it should do in default cases.
```phpregexp
function __construct() {
```
If you have supplementary Javascript and CSS files, you must add an action to a function that will make WordPress aware of them.
This is where you have to declare that function and link to the [files](CustomBlock.md#srccomponentswhatever) you will create.

```phpregexp
add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
```
#### Supporting Files
Then you must build that function to register and enqueue each script file and style file.
```phpregexp
 public function register_plugin_styles() {
        $file = '../src/components/CourseCatalog/tablesorter.js';
        wp_register_script(
                'tablesorterjs',
                plugins_url($file, __FILE__),
                array(),
                filemtime(plugin_dir_path(__FILE__) . $file),
                true
        );
        wp_enqueue_script('tablesorterjs');
```
### src/blocks/Whatever.js
This is basic declaration for the Gutenberg block. We don't fully use Gutenberg blocks because we render the display dynamically, so much of this is placeholder. You do define the name of the block and its icon here.
### src/components/Whatever
Here you keep your front-end Javascript and CSS files. They must be included in your class constructor. See CourseCatalog as an example.

## Future Improvements
### Templating
The code in the function you declare as the 'init' for the script must include markup and data to be displayed with ```echo``` statements.
The method used so far is functional but problematic when the markup gets more complex and the
data more complex or nested. These are problems that were solved long ago with templating tools.
#### REST API and browser processing
Most blocks can deliver their data via a REST API. [Front end solutions](https://awhitepixel.com/blog/create-and-fetch-custom-rest-endpoints-in-gutenberg-blocks/) can be used to render this data.
#### PHP templating
Even for simple blocks, it would be good to find a simple template to use to reduce errors and speed debugging.
