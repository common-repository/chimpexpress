=== ChimpExpress ===
Contributors: freakedout
Donate link: https://chimpxpress.com
Tags: mailchimp, newsletter
Requires at least: 6.0
Tested up to: 6.5.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.4

chimpXpress - The Mailchimp WordPress Integration

== Description ==

chimpXpress is a Mailchimp integration for WordPress. It allows you to create blog posts from existing Mailchimp campaigns and create new campaign drafts from within WordPress by using your blog posts as campaign content.
If you're having trouble with the plugin visit our forums https://chimpXpress.com/support - Thank you!

== Installation ==

Installing chimpXpress is very easy. Use the WordPress plugin installer to upload the zip file or simply follow the steps below.

1. Extract the package to obtain the `chimpxpress` folder
2. Upload the `chimpxpress` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the settings according to your needs through the 'Settings' > 'chimpXpress' menu
5. Start creating campaigns or import content from Mailchimp
6. If you need help please visit our forums at https://chimpXpress.com !

== Upgrade Notice ==

* If you need help please visit our forums at https://chimpXpress.com !

== Frequently Asked Questions ==



== Screenshots ==

1. chimpXpress Dashboard
2. Import your Mailchimp campaigns into WordPress as blog post or landing page.
3. Create new Mailchimp campaigns and include your blog posts.
4. Landing page archive. Review and edit your existing landing pages.
5. chimpXpress Settings. Enter your Mailchimp API key and you're ready to go. If your write permissions allow the plugin to write files directly to the server you don't need to enter ftp credentials. Otherwise you will be prompted to supply the credentials. You can enter your Google Analytics ID if you want to be able to track visitors on your landing pages.

== Changelog ==

= 1.0 =
* Initial release of the chimpXpress plugin
= 1.1 =
* When inserting blog posts into campaign content, the post title is now included.
= 1.2 =
* php.js library updated to v3.24
* Bugfix: writing files via ftp failed in some cases
= 1.3 =
* FTP credentials are no longer mandatory! If the plugin is able to write files directly users don't have to enter ftp credentials anymore.
* Import to landing page - added check to prevent unintentional overwriting of existing landing pages.
* Import to landing page - added check to prevent empty page title.
* Landing page archive - creation date now takes timezone offset from WordPress configuration into account.
* Landing page archive - delete function added.
* Compose page - added check to prevent empty campaign name and subject (consisting only of spaces).
* Compose page - encoding campaign name and subject to html entities to avoid multiple escaping.
* Compose page - fixed bug that caused user not being able to go from last step to second last.
* Compose page - fixed bug that appeared in WordPress 3.1.1 and caused creation of multiple campaigns.
* Settings page - error messages showed up even after the reason was corrected.
* Updated German translations.
= 1.4 =
* Removed position parameter from add_menu_page to avoid conflicts with CPT (custom post types)
= 1.5 =
* Fix to include css and js files only on chimpXpress pages.
* Adjusted css selectors to avoid conflicting with other plugins/widgets.
* Fixed multiple backslashes being added to campaign content (entered in wysiwyg editor).
= 1.6 =
* Fixed all compatibility issues with the latest WordPress versions (3.3 and later)
* Bugfix: inserting blog posts didn't work in newer versions
* Bugfix: fixed issue when editor wasn't working properly
* Bugfix: fixed several Javascript errors
* Minor updates on the user interface
= 1.6.1 =
* Fixed a bug when editing landing pages.
= 1.6.2 =
* Fixed layout bug that interferred with WordPress navigation.
* Fixed bug when creating landing pages and file already existed.
= 1.6.3 =
* innerxhtml.js library contained a wrong license declaration.
= 2.0.0 =
* Refactored plugin to be compatible with WordPress 6 and PHP 8
