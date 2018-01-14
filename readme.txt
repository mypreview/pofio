=== Pofio ===
Contributors: mahdiyazdani, gookaani, mypreview
Tags: custom post type, portfolio, pofio, projects
Donate link: https://www.mypreview.one
Requires at least: 4.8
Tested up to: 4.9.1
Requires PHP: 7.0
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Registers a custom post type along with tags and categories for portfolio projects.

== Description ==
Registers a custom post type along with tags and categories for portfolio projects.

== Installation ==
1. Upload the entire `pofio` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Start by adding portfolio projects `Dashboard` > `Portfolio`.

== Frequently Asked Questions ==
= How can I embed portfolio projects on post or page? =
Add the `[pofio]` shortcode to any post or page to display projects.

= Are there any attributes to custom the portfolio output? =
Optionally add the following attributes to custom the portfolio layout:

* **display_types:** display Project Types – displayed by default. (true/false)
* **display_tags:** display Project Tag – displayed by default. (true/false)
* **display_content:** display project content – displayed by default. (true/false)
* **display_author:** display project author name – hidden by default. (true/false)
* **include_type:** display specific Project Types. Defaults to all. (comma-separated list of Project Type slugs)
include_tag: display specific Project Tags. Defaults to all. (comma-separated list of Project Tag slugs)
* **columns:** number of columns in shortcode. Defaults to 2. (number, 1-6)
* **order:** display projects in ascending or descending order. Defaults to ASC for sorting in ascending order, but you can reverse the order by using DESC to display projects in descending order instead. (ASC/DESC)
* **orderby:** sort projects by different criteria, including author name, project title, and even rand to display in a random order. Defaults to sorting by date. (author, date, title, rand)

= Where can I follow development process? =
If you’re a theme author, plugin author, or just a code hobbyist, you can follow the development of this plugin on it’s [GitHub repository](https://github.com/mypreview/pofio).

= Why doesn’t this plugin do anything? =
You’ll need to create templates for archive-portfolio.php and single-portfolio.php if you want to display portfolio projects.

== Changelog ==
= 1.0.0 =
* Initial release.

== Upgrade Notice ==
= 1.0.0 =
* Nothing so far.

== Credits ==
* [Jetpack by WordPress.com](https://wordpress.org/plugins/jetpack/)
* [Featured Galleries](https://wordpress.org/plugins/featured-galleries/)