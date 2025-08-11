=== GamiPress - Rest API Extended ===
Contributors: gamipress, rubengc, eneribs
Tags: gamipress, gamification, point, achievement, rank, badge, award, reward, credit, engagement
Requires at least: 4.4
Tested up to: 6.7
Stable tag: 1.0.7
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

New rest API endpoints to extend interaction between your gamification environment and external applications.

== Description ==

Rest API Extended introduces new rest API endpoints to extend interaction between your gamification environment and external applications.

In addition, this add-on includes settings that let's you customize numerous aspects of the new endpoints like a custom base URL or the ability to enable GET access.

Also, this add-on adds new features to extend and expand the functionality of GamiPress.

= Features =

* New points-related routes to retrieve, award and revoke points to any user.
* New achievement-related routes to retrieve, award and revoke achievements to any user.
* New rank-related routes to retrieve (current, next and previous), award and revoke ranks to any user.
* New requirement-related routes to award and revoke any requirement of any type to any user.
* Rank utility routes to upgrade (to next rank) and downgrade (to previous rank) the user rank.
* Ability to enable access to all routes through GET method (for testing purposes).
* Ability to setup the base URL for all routes.

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

= 1.0.7 =

* **Bug Fixes**
* Fixed bug related to revoking ranks.

= 1.0.6 =

* **Improvements**
* Improved features for compatibility with the latest version of GamiPress.

= 1.0.5 =

* **New Features**
* Extended "get-achievements" endpoint to allow multiple achievement types or all achievement types.

= 1.0.4 =

* **Improvements**
* Register points awards and deducts on the user earnings table.
* **Developer Notes**
* New filters to deactivate register earnings on award or deduct points.

= 1.0.3 =

* **New Features**
* Added user parameter on all endpoints to allow provide the user username, email or ID.

= 1.0.2 =

* **New Features**
* Added filter hooks before process any endpoint.
* Added action hooks before and after all endpoints processing.
* Added support to GamiPress 1.8.0.

= 1.0.1 =

* **New Features**
* Added support to GamiPress 1.7.0.

= 1.0.0 =

* Initial release.
