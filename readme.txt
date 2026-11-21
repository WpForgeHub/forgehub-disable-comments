=== ForgeHub Disable Comments ===
Contributors: wpforgehub
Tags: disable comments, comments, pingbacks, trackbacks, spam
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Disable comments, pingbacks and trackbacks everywhere or per post type, and hide comment menus, admin bar items and feeds.

== Description ==

**Problem:** Spam comments, pingbacks and trackbacks clutter your site and your admin, even when you never wanted comments.

**Solution:** ForgeHub Disable Comments turns them off in one click, site-wide or only for the post types you choose.

= Features =
* Disable comments, pingbacks and trackbacks everywhere or per post type
* Hides the Comments admin menu, admin bar item and comments RSS feed
* Removes comment REST routes and XML-RPC pingback methods
* Settings link in the plugins list, zero server load, no external services

= Free vs Pro =

| Feature | Free | Pro |
| --- | --- | --- |
| Disable comments and pings (all or per post type) | Yes | Yes |
| Hide menu, admin bar, comment feed | Yes | Yes |
| Bulk delete existing comments | No | Yes |
| Role based rules | No | Yes |

= Privacy =
No data leaves your site. No API keys, no tracking.

== Frequently Asked Questions ==

= Does it delete existing comments? =
Free hides them. Pro can permanently bulk delete them.

= Can I keep comments on some post types? =
Yes, choose "Only on selected post types".

= Which PHP version is needed? =
PHP 7.4 or newer.

= Does it work on multisite? =
Yes, settings are per site.

== Screenshots ==

1. Settings page.

== Changelog ==

= 1.0.3 =
* Connect live Freemius Product ID and public key for Pro licensing and automatic updates.

= 1.0.2 =
* Restrict Pro bulk delete to comment/pingback/trackback types with confirmation & loop guard; scope RSS/XML-RPC to configured post types.

= 1.0.1 =
* Fix: Freemius SDK is no longer initialised with placeholder product keys (fatal on Pro activation).

= 1.0.0 =
* Initial release.
