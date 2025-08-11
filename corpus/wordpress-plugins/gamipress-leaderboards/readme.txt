=== GamiPress - Leaderboards ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, point, achievement, rank, badge, award, reward, credit, engagement, ajax
Requires at least: 4.4
Tested up to: 6.7
Stable tag: 1.4.9
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Add leaderboards to intensify the gamification of your site.

== Description ==

Leaderboards gives you the ability to easily create, configure and add leaderboards on your website.

Place any leaderboard anywhere, including in-line on any page or post, using a simple shortcode, or on any sidebar through a configurable widget.

Also, this add-on adds new features to extend and expand the functionality of GamiPress.

= Features =

* Create as many leaderboards as you like.
* Ability to configure the metrics by which users should be ranked (the user rank, the points types and/or the number of earned achievements).
* Filter the leaderboard by a set of predefined time periods (today, yesterday, current week/month/year and past week/month/year).
* Support for custom time periods to filter the leaderboard on a range of dates you want.
* Responsive leaderboards that will get adapted to any screen size.
* Ability to configure the display options for each single leaderboard.
* Drag and drop options to reorder the leaderboard columns.
* Displayed leaderboards can be filtered and sorted without refresh the page.
* Configurable lazy loading feature for large leaderboards.
* Ability to set up leaderboard results cache to improve loading time speed on large leaderboards.
* Ability to hide website administrators from the leaderboard.
* Block, shortcode and widget to place any leaderboard anywhere.
* Block, shortcode and widget to show user's position on a specific leaderboard.

== Installation ==

= From WordPress backend =

1. Navigate to Plugins -> Add new.
2. Click the button "Upload Plugin" next to "Add plugins" title.
3. Upload the downloaded zip file and activate it.

= Direct upload =

1. Upload the downloaded zip file into your `wp-content/plugins/` folder.
2. Unzip the uploaded zip file.
3. Navigate to Plugins menu on your WordPress admin area.
4. Activate this plugin.

== Frequently Asked Questions ==

== Changelog ==

= 1.4.9 =

* **Improvements**
* Improved Leaderboards menu.
* Fixed notice related to textdomain.

= 1.4.8 =

* **Bug Fixes**
* Fixed position shortcode for paginated leaderboards with disabled cache.

= 1.4.7 =

* **New Features**
* Hide Users Without Earnings.
* Added the field "Hide Users Without Earnings" to the leaderboard edit screen.
* Added the attribute "hide_no_earners" to the [gamipress_leaderboard] shortcode.
* Added the field "Hide Users Without Earnings" to the GamiPress: Leaderboard widget.
* **Improvements**
* Improve database query performance based in GamiPress 6.9.4 changes.

= 1.4.6 =

* **Improvements**
* Improved features for compatibility with the latest version of GamiPress.
* Added check for metrics.

= 1.4.5 =

* **Bug Fixes**
* Fixed pagination for All users option.

= 1.4.4 =

* **Bug Fixes**
* Fixed pagination.

= 1.4.3 =

* **Bug Fixes**
* Fixed multisite compatibility.

= 1.4.2 =

* **Bug Fixes**
* Fixed point display for decentralized multisites.

= 1.4.1 =

* **Improvements**
* Only load Leaderboard scripts in leaderboard single pages and pages where the shortcode, block or widget is placed.

= 1.4.0 =

* **Improvements**
* Added debug messages for admins on user position block, shortcode and widget.
* **Bug Fixes**
* Fixed default parameters values for user position block, shortcode and widget.
