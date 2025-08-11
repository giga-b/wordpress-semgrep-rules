=== GamiPress - Time-based Rewards ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, gamify, point, achievement, badge, award, reward, credit, engagement, ajax
Requires at least: 4.4
Tested up to: 6.8
Stable tag: 1.1.3
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Add time-based rewards to let your users coming back to claim them.

== Description ==

Time-based Rewards gives you the ability to configure rewards that your users will be able to claim in a time span you want.

With just a few controls, you will be able to create a time-based reward and setup the items that may be rewarded when an user claims it (you can configure the range of quantities to award and also force to always include any item as reward).

Place any time-based reward anywhere, including in-line on any page or post, using a configurable block or a shortcode, or on any sidebar through a configurable widget.

Time-based Rewards extends and expands GamiPress adding new activity events and features.

= New Events =

* Claim any time-based reward: When an user claims a time-based reward.
* Claim a specific time-based reward: When an user claims a specific time-based reward.
* Earn any reward of any type on claim a time-based reward: When an user earns any type of reward on claim a time-based reward.
* Earn points on claim a time-based reward: When an user earns points on claim a time-based reward.
* Earn points of a specific type on claim a time-based reward: When an user earns points of a specific type on claim a time-based reward.
* Earn an achievement on claim a time-based reward: When an user earns an achievement on claim a time-based reward.
* Earn a specific achievement on claim a time-based reward: When an user earns a specific achievement on claim a time-based reward.
* Earn an rank on claim a time-based reward: When an user earns an rank on claim a time-based reward.
* Earn a specific rank on claim a time-based reward: When an user earns a specific rank on claim a time-based reward.

= Features =

* Ability to create as many time-based rewards as you like.
* Set the recurrence time of each time-based reward in hours, minutes and seconds.
* Live counter with the remaining time the user needs to wait until claim a time-based reward again.
* You can define points, achievements and ranks as possible rewards.
* Ability to force rewards to being included always.
* Configure the range of quantities to award each time an user claims a time-based reward.
* User time-based reward's claim will be live processed (without refresh the page).
* A pop-up will be displayed with the rewards that user got on claim a time-based reward.
* Block to place any time-based reward anywhere.
* Shortcode to place any time-based reward anywhere (with support to GamiPress live shortcode embedder).
* Widget to place any time-based reward on any sidebar.

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

= 1.1.3 =

* **Improvements**
* Improved filter to display popup.

= 1.1.2 =

* **Improvements**
* Improved Time Based Rewards menu.
* Fixed notice related to textdomain.

= 1.1.1 =

* **Improvements**
* Points claimed will be registered in User Earnings.

= 1.1.0 =

* **Improvements**
* Improved features for compatibility with the latest version of GamiPress.

= 1.0.9 =

* **Improvements**
* Update functions for compatibility with last versions of GamiPress and WordPress.

= 1.0.8 =

* **Bug Fixes**
* Fixed block, shortcode and widget display issue on multisite installs.

= 1.0.7 =

* **Improvements**
* Updated deprecated jQuery functions.

= 1.0.6 =

* **Bug Fixes**
* Renamed function to avoid name collision.

= 1.0.5 =

* **New Features**
* Added the tag {image} to display the earned element image on the pop-up content
* **Improvements**
* Improved the random achievement search function.

= 1.0.4 =

* **New Features**
* New setting: No Rewards Content.
* New time-based reward setting: No Rewards Content.
* **Improvements**
* Ensure to award only published achievements on the random achievements feature.
* Ensure to don't award an achievement if user exceeds the achievement maximum earnings.

= 1.0.3 =

* **Improvements**
* Apply WordPress timezone settings on all dates differentiations functions.

= 1.0.2 =

* **New Features**
* Added the ability to set a random achievement as reward.
* Added support to GamiPress 1.8.0.
* **Improvements**
* Make use of WordPress security functions for ajax requests.

= 1.0.1 =

* **New Features**
* Added support to display days remaining on the time-based reward's counter if recurring time is higher than 24 hours.
* **Improvements**
* Improvements on the time-based reward's counter remaining time calculation.

= 1.0.0 =

* Initial release.
