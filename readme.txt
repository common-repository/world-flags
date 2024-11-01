=== World Flags ===
Contributors: bsurprised
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HMNUXVMGNXDR8
Tags: flags, ip2country, visitor, ip, country, flag, lookup, widget, shortcode, multilingual, geo-ip, geolocation
Requires at least: 2.9.2
Tested up to: 3.0.1
Stable tag: 1.1

Add country flags anywhere in your Wordpress blog using simple shortcodes and/or widgets, or show visitor country flag based on their IP address.

== Description ==

Add country flags anywhere in your Wordpress blog using simple shortcodes and/or widgets, or show visitor country flag and info based on their IP address.

This is the first version of World Flags. This version uses the [software77.net](http://software77.net/geo-ip/) ip2country database and only wraps the IPV4 version. The plugin imports the csv file into a mysql table for fast queries. Fetching of the csv IP database can be scheduled weekly using the WP cron jobs, or manually via the plugin options page. 

= Features =

* Supports shortcodes anywhere and widgets
* Template functions and comments author flags (NEW!)
* Uses software77 IP database with more than 3 billion addresses included
* Multilingual interface
* Scheduled downloads of the IP database file
* MD5 checks to validate database before populating tables
* Uses MySQL for super fast queries
* 4 image sizes for country flags - 16, 24, 32 and 48
* Different ways to insert HTML - simple, JavaScript and jQuery (with Ajax)
* Plugin uninstall support

= Multilingual Support =

World Flags supports multilingual interface and currently has:

* Persian (fa_IR)

You can also translate the `data/country-codes.txt` file for country names in your language.

All translation submissions are appreciated. Thank you.


**Warning:** Please be aware that software77.net may ban your IP if you send many download requrests. Visit [software77.net](http://software77.net/geo-ip/) for their license, privacy policy and specially the periodic download limit for the database file. 

Visit [BSurprised Home Page](http://bsurprised.com/) for updates, feature requests and information on future releases.


== Installation ==

Installation is the same routine as most WP plugins:

1. Upload 'word-flags' folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress
3. Find the World Flags submenu under `Options` menu in your Wordpress to change some settings or manually update the IP database. 

**Notice:** You need to manually fetch the IP database the first time as the plugin does not include the files.

= Using World Flags =

* Place `[flag code="your-country-code/auto" size="16/24/32/48" text="yes/no"]` in your posts.
   Example: `[flag code="auto" size="48" text="yes"]`
* Drag the plugin widget in your Widgets menu and customize its options to show what you want.
* If you want to show the country flag for your visitor comments, you can add the flag before or after the comment text along with your custom text or use `<?php if (function_exists('world_flags_comment_flag')) echo world_flags_comment_flag($comment); ?>` inside your comments loop in your template. `$comment` is the variable that holds the comment inside your loop.
* You can add `<?php if (function_exists('world_flags_insert_flag')) world_flags_insert_flag(); ?>` inside your template to add flags anywhere. Optional argument `$code` can be provided to override `auto` mode. Example: `world_flags_insert_flag( 'US' )`

== Frequently Asked Questions ==

= What are the options for the World Flags shortcode? =

The shortcode supports these attributes:

* **code**: Can be an [ISO_3166-1_alpha-2](http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) two-letter country code, or the keyword `auto` for the automatic display of the flags based on the visitor IP address.
* **size**: Currently supports `16`, `24`, `32` and `48` pixel image sizes.
* **text**: `yes` or `no`, whether to show the country name text beside the flag image.

= What are the requirements for the plugin? =

* You need at least 15 megabytes of space on your hosting as the ip2country database file, when extracted, takes some space.
* Plugin needs to have write permissions on its `/data` directory.

= The plugin auto mode does not show visitor flags correctly. Why? =

You probably have some sort of caching enabled like wp-supercache. You need to set the mode to jQuery for the flags to load by ajax calls.


== Screenshots ==

1. World Flags settings page 

== Upgrade Notice ==

= 1.1 =
New version can show flags in comments based on comment author IP, and can be setup both automatically and manually. There's the function to include flags anywhere in your template, and many customizable options in the options page.

= 1.0.1-beta =
Update fixes the cache problem by implementing ajax calls in jQuery

== Changelog ==

= 1.1 =
2010-08-23

* Comment flags based on comment author IP address
* function to include flags anywhere inside template
* Many new customizable options

= 1.0.1-beta =
2010-08-21

* Fixed the cache problem by implementing ajax calls in jQuery

= 1.0-beta =
2010-08-17

* First released version
* Supports shortcodes and widgets
* Uses software77 IP database
* Only IPV4
* Plugin uninstall support