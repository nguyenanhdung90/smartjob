=== No category parents ===
Contributors: milardovich
Tags: categories, category parents, category base, category, permalinks, permastruct, links, seo, cms
Requires at least: 2.3
Tested up to: 4.1
Stable tag: trunk
Donate link: http://www.milardovich.com.ar/

== Description ==

This plugin will completely remove the mandatory 'Category Base' and all the parents from your category permalinks (e.g. `/category/parent-category/my-category/` to `/my-category/`).

== Installation ==

1. Upload `no-category-parents.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it! You sould now be able to access your categories via http://mysite.com/my-category/

See http://www.milardovich.com.ar/no-category-parents/ for troubleshooting.



== Changelog ==

= 0.2.4.1 =
* Quickfix: fixed pagination bug

= 0.2.4 =
* Tested up to WP 4.1
* Fixed pagination problem (special thanks to Lukáš Wojnar).

= 0.2.3 =
* Changed some links.
* Fixed "empty category" problem (special thanks to absolutex).

= 0.2.2 =
* In 0.2.1 when the "Category Base" field wasn't empty the plugin didn't work. Now the "Category Base" field will automatically be empty when you activate the plugin.

= 0.2.1 =
* Minor changes in the comments.

= 0.2 =
* The plugin now works with the permastruct /%category%/ and also replaces the post permalinks.
* Other minor fixes.
