=== GamiPress - Expirations ===
Contributors: gamipress, tsunoa, rubengc, eneribs
Tags: gamipress, gamification, point, achievement, rank, badge, award, reward, credit, engagement, ajax
Requires at least: 4.4
Tested up to: 6.6
Stable tag: 1.0.8
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

Set an expiration date to your points, achievements and ranks.

== Description ==

Expirations lets you set an expiration date to the GamiPress elements of your choice.

You are able to configure a specific expiration date or a relative one after several days, weeks, months or years since the user got that earning.

Even, you can set an expiration date to requirements which gives you the possibility of, for example, configure an achievement that requires to complete all its steps in 2 hours.

In addition, Expirations add-on includes configurable emails to being sent in a period before the item expiration as well as the same moment of the expiration.

= Features =

* Set an expiration date to your points, achievements, ranks and requirements.
* Ability to configure a specific expiration date or a relative one (after days, weeks, months or years since the user got that earning).
* Configurable emails to notify users about any upcoming expirations (eg: an email 15 days before a points amount expiration).
* Configurable emails to notify users about expired elements (eg: to notify that an achievement has expired).

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

= 1.0.8 =

* **Bug Fixes**
* Fixed bug related to items deleted with Expirations set.

= 1.0.7 =

* **Improvements**
* Improved features for compatibility with the latest version of GamiPress.

= 1.0.6 =

* **Improvements**
* Update functions for compatibility with last versions of GamiPress and WordPress.

= 1.0.5 =

* **New Features**
* Added a new option to recalculate the expiration date for ranks when the previous rank expires.
* **Improvements**
* Improvemens in the expiration check process.

= 1.0.4 =

* **Improvements**
* Ensure to send the email expiration only 1 time per user earning expired.

= 1.0.3 =

* **Improvements**
* Make the cron event run every 5 minutes to make expirations more accurate.

= 1.0.2 =

* **Bug Fixes**
* Fixed expiration display for ranks.

= 1.0.1 =

* **Improvements**
* Improved MySQL compatibility using backward compatibility SQL code.

= 1.0.0 =

* Initial release.