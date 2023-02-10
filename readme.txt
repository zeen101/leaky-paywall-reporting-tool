=== Leaky Paywall - Reporting Tool ===
Contributors: zeen101
Tags: metered, paywall, leaky, wordpress, magazine, news, blog, articles, remaining
Requires at least: 5.6
Tested up to: 6.1.1
Stable tag: 1.4.2

An add-on for Leaky Paywall that adds the ability to export Leaky Paywall subscribers into a CSV file.

== Description ==

An add-on for Leaky Paywall that adds the ability to export Leaky Paywall subscribers into a CSV file.

== Installation ==

1. Upload the entire `leaky-paywall-reporting-tool` folder to your `/wp-content/plugins/` folder.
1. Go to the 'Plugins' page in the menu and activate the plugin.

== Frequently Asked Questions ==

= What are the minimum requirements for Leaky Paywall - Reporting Tool =

You must have:

* WordPress 5.6 or later
* PHP 7
* Leaky Paywall version 4.0.0 or later

= How is Leaky Paywall Licensed? =

* Leaky Paywall - Reporting Tool is GPL

== Changelog ==

= 1.4.2 =
* Add processing to allow for large datasets without timing out
* Update handling for created and level_id keys

= 1.4.1 =
* Update payment status label
* Add new payment statuses
* Allow back dating for expiration date picker
* Add EDD updater

= 1.4.0 =
* Add created date range as a search parameter
* Convert created timestamps into readable dates

= 1.3.0 =
* Load anyone who does not have an administrator role and has a level_id

= 1.2.6 =
* Add first and last name to csv output

= 1.2.5 =
* Add leaky_paywall_reporting_tool_user_meta filter so that user meta without _leaky_paywall_ prefix can be added to the export file

= 1.2.4 =
* Add leaky_paywall_reporting_tool_meta and leaky_paywall_reporting_tool_pre_users filters
* Add check for all keys in multisite environments

= 1.2.3 =
* Fixed multisite export bug
* Export now only exports users of the subscriber role
* Retrieves and exports meta with and without the `_issuem` prefix

= 1.2.2 =
* Fix export metadata bug

= 1.2.1 =
* Fix menu priority

= 1.2.0 =
* Update for new public release of Leaky Paywall

= 1.1.0 =
* Add documentation to settings page
* Remove need to check for a hash
* Add notice when no subscribers match the search parameters

= 1.0.0 =
* Initial release

== License ==

Leaky Paywall - Reporting Tool
Copyright (C) 2011 The Complete Website, LLC.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
