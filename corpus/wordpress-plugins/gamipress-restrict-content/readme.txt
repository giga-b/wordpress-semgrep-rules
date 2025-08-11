=== GamiPress - Restrict Content ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, point, achievement, rank, badge, award, reward, credit, engagement
Requires at least: 4.4
Tested up to: 6.7
Stable tag: 1.2.8
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Limit access to any post or page based on GamiPress interactions.

== Description ==

Restrict Content gives you the ability to configure several restrictions to any post or page until user completes all the requirements specified.

Limit the content to be displayed, replace the links or images by the text you want or restrict completely the access and redirect the users that not meets the requirements.

Restrict a portion of content anywhere, including in-line on any page or post, using a simple shortcode or on any sidebar through a configurable widget.

In addition, this add-on has the ability to restrict posts and portions of content by expending points and award those points to the post author, making Restrict Content your Sell Content add-on.

Also, this add-on adds new activity events and features to extend and expand the functionality of GamiPress.

= New Events =

* Get access to a post: When an users unlock access to a post.
* Get access to a specific post: When an users unlock access to a specific post.
* Get access to a post by meeting all requirements: When an users unlock access to a post by meeting all requirements.
* Get access to a specific post by meeting all requirements: When an users unlock access to a specific post by meeting all requirements.
* Get access to a post using points: When an users unlock access to a post using points.
* Get access to a specific post using points: When an users unlock access to a specific post using points.
* Get access to a portion of content: When an users unlock access to a portion of content.
* Get access to a specific portion of content: When an users unlock access to a specific portion of content.
* Get access to a portion of content on a specific post: When an users unlock access to a portion of content on a specific post.
* Get access to a specific portion of content on a specific post: When an users unlock access to a specific portion of content on a specific post.

= Features =

* Add content restrictions on any post by the next requirements:
    * Points earned.
    * Rank reached.
    * Achievements earned.
    * Achievements of a specific type earned.
    * All achievements of a specific type earned.
* Let users to optionally unlock restricted content without meet the requirements by expending points.
* Add content restrictions on any post to be unlocked just by expending points.
* Post author will earn the points expended (sell content).
* Restrict access to users that not meets the requirements and redirect them to the page you want.
* Restrict the content output to users that not meets the requirements (text, links and/or images).
* Ability to customize how content restriction should restrict the content:
    * Until read more tag.
    * Replacing content with excerpt.
    * Trimming the content to a desired number of characters.
    * Replacing the full content.
* Restrict a portion of content anywhere through a block, shortcode and/or widget.
* Render a list of all posts restricted anywhere through a block, shortcode and/or widget.
* Render a list of all posts that user got access anywhere through a block, shortcode and/or widget.
* Render post restrictions and/or access button of a desired post anywhere through a block, shortcode and/or widget.
* Blocks, shortcodes and widgets to show and hide a portion of content if user meets a specific condition.
* Grant access to a specific users or by role to a specific restricted post.
* Support to all registered post types with public access.

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

= 1.2.8 =

* **Bug Fixes**
* Fixed bug related to [gamipress_posts_unlocked] and [gamipress_posts_restricted] shortcodes when there were no unlocked posts.

= 1.2.7 =

* **Improvements**
* Added question to ensure that the user wants to unlock the content.
* Added new feature to restrict featured image of a post.

= 1.2.6 =

* **Bug Fixes**
* Fixed bug related to Image restrictions.

= 1.2.5 =

* **Improvements**
* Improved features for compatibility with the latest version of GamiPress.
* **Bug Fixes**
* Fixed some PHP warnings on post functions.

= 1.2.4 =

* **Bug Fixes**
* Fixed bug that prevented shortcodes from being rendered before Read More tag.

= 1.2.3 =

* **Bug Fixes**
* Prevent PHP 8 notices caused by required parameters followed by optional parameters.
* Fixed the function gamipress_restrict_content_get_user_unlocked_posts() who incorrectly does not makes use of the given user ID.

= 1.2.2 =

* **Improvements**
* Added new hooks to exclude content filters to pass to a restricted content.

= 1.2.1 =

* **Improvements**
* Force to always display the achievement or rank selector when restricting for a specific one.
* Style improvements on the achievement and rank selector loader.
* Updated Javascript function to use the most compatible ajax functions.
* **Bug Fixes**
* Fixed typo that causes that content restrictions in listings are not displayed correctly.

= 1.2.0 =

* **Improvements**
* Updated deprecated jQuery functions.

= 1.1.9 =

* **Improvements**
* Apply points format on templates.

= 1.1.8 =

* **Bug Fixes**
* Fixed some PHP notices on template functions.

= 1.1.7 =

* **Improvements**
* Added the content field on Restrict Content, Show Content If and Hide Content If blocks.

= 1.1.6 =

* **Bug Fixes**
* Removed posts selector on Restrict Content block ID field.

= 1.1.5 =

* **New Features**
* Added support to GamiPress 1.8.0.
* **Improvements**
* Make use of WordPress security functions for ajax requests.

= 1.1.4 =

* **New Features**
* Added support to GamiPress shortcode groups features.
* Added support on [gamipress_restrict_content] to require multiples ranks.
* Added support on GamiPress: Restrict Content block and widget to require multiples ranks.
* New block, shortcode and widget to show a portion of content if user meets a specific condition.
* New block, shortcode and widget to hide a portion of content if user meets a specific condition.
* **Improvements**
* Improved users selector on meta boxes.

= 1.1.3 =

* **New Features**
* Added support to GamiPress 1.7.0.
* **Improvements**
* Improved post and user selector on widgets area and shortcode editor.
* Great amount of code reduction thanks to the new GamiPress 1.7.0 API functions.

= 1.1.2 =

* **Bug Fixes**
* Fixed rank required check if rank passed is the default rank (lowest priority rank).

= 1.1.1 =

* **Improvements**
* Make redirect page field options being loaded from ajax to reduce edit post time load.
* Allow add content restrictions to GamiPress achievements and ranks.
* **Developer Notes**
* Added redirect post types hooks to allow setup custom redirect post types to work on redirect page option.

= 1.1.0 =

* **Improvements**
* Make use of after content replacement text for guests on GamiPress: Post Restrictions block, shortcode and widget.
* Reset public changelog (moved old changelog to changelog.txt file).