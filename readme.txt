=== Quentn WP ===
Contributors: quentn
Tags: Quentn, countdown, page restriction, email, marketing automation
Requires at least: 4.6.0
Tested up to: 6.7.2
Stable tag: 1.2.12
Requires PHP: 5.6.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Restrict access to specific pages, create access links and display countdowns. Connect your wordpress installation with your Quentn account.

== Description ==

Quentn plugin allows you to restrict access to specific pages, create custom access links and create dynamic page countdowns. Optionally, you can connect your Quentn account to your WordPress installation to share contacts and manage access restrictions through Quentn.

The Quentn WordPress plugin offers a variety of functions:

*   **Membership** With the creation of access-restricted pages, only selected contacts will get access to the landing page - this creates exclusivity. For these contacts, for example, selected offers can be offered, webinars or downloads can be made available - this exclusivity strengthens customer loyalty
*   **Personalization**  By using placeholders, you can address contacts by name. Even unknown contacts can be addressed individually (but not by name)
*   **Countdown** The various countdown options can contribute to lead generation and increase the conversion rate
*   **Integration and tagging in Quentn**  Quentn contacts can easily be sent to your Wordpress page via a link. Wordpress users (e.g. forum members, employees, etc.) can also be sent and tagged in Quentn - without having to rely on third-party providers such as Zapier
*   **Elementor Integration**  If you are using Elementor Pro, You can send contacts from Elementor forms to your Quentn account. You can apply tags and can easily map Elementor form fields to contact fields in the Quentn.
*   **LearnDash Integration** You can set default courses for all user roles which will be assigned when user gets that role.

For more info, vist the [Quentn WordPress plugin Documentation](https://docs.quentn.com/de/plugins/beta-quentn-wordpress-plugin).
== Installation ==

### INSTALL QUENTN WP FROM WITHIN WORDPRESS

1. Go to WordPress Dashboard. Locate Plugins -> Add New
2. Search 'Quentn' using the search option
3. Find the plugin and click Install Now button
4. After installation, click on Activate Plugin link to activate the plugin.

### INSTALL QUENTN WP MANUALLY

1. Download the plugin quentn-wp.zip
2. Go to WordPress Dashboard. Locate Plugins -> Add New
3. Click on Upload Plugin link from the top
4. Upload the downloaded quentn-wp.zip file and click on Install Now
5. After installation, click on Activate Plugin link to activate the plugin.

### Connect plugin with Quentn

*   After installing the Pugin, click on the Quentn icon in the left bar
*   To connect to your Quentn account, click on the green button
*   Log in to Quentn and select your host (Note: at least one Quentn Basic Account is required, as the connection uses the API)

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

= What about foo bar? =

Answer to foo bar dilemma.

== Screenshots ==

1. Countdown
2. Page restrictions settings
3. Select Quentn Tags for User roles
4. Integration Elementor PRO forms with Quentn

== Changelog ==

= 1.2.12 =
* Fix: Fixed access links count for list of restricted pages.

= 1.2.11 =
* Fix: Fixed pagination and screen options for list of restricted pages and access overview.

= 1.2.10 =
* Fix: Update user messages

= 1.2.9.1 =
* Meta: Fixed plugin version header (was incorrectly still 1.2.8 in previous release).

= 1.2.9 =
* Security Fix: Fixed SQL injection vulnerabilities
* Security Fix: Hardened input validation for all admin operations
* Security Fix: Improved data sanitization and escaping

= 1.2.8 =
* Add: Log option to trace different user quentn related activities.
* Fix: Prefix psr/http-message and psr/log libraries namespaces to avoid conflict with other plugins.

= 1.2.7 =
* Fix: Reduce number of API calls.

= 1.2.6 =
* Fix: Guzzle files were not autoloaded due to duplicated hash value.

= 1.2.5 =
* Fix: Learndash issue when new user is registered.

= 1.2.4 =
* Test with new wordpress ( 6.1.1 ), Elementor ( 3.9.0 ) and Elemnetor PRO ( 3.9.0 ) versions.

= 1.2.3 =
* Fix: Field mapping issues in Form widget of Elementor PRO

= 1.2.2 =
* Fix: Make compatible for Elementor versions >= 3.5

= 1.2.1 =
* Fix: Error handling while getting password reset key.

= 1.2.0 =
* Fix: The thrive theme bug for user restriction pages.

= 1.1.9 =
* Fix: The styling bug when using the Form widget of Elementor PRO.
* Tweak: Improved method of loading field mapping in Form widget of Elementor PRO.

= 1.1.8 =
* Fixed minor issue how ternary operators used in Elementor integration.

= 1.1.7 =
* Prefix Guzzle library namespace to avoid conflict with other plugins.

= 1.1.6 =
* Slash at start of the namespace of rest API route is removed.

= 1.1.5 =
* Improve the method of how cookie value is fetched to check page access permission.

= 1.1.4 =
* Fix: Elementor integration for WordPress version 5.6.

= 1.1.3 =
* Fix: Load elementor integration file after elementor instance has been initiated.

= 1.1.2 =
* Implements Quentn redirect, flood protection and spam protection in Elementor forms.

= 1.1.1 =
* Add: Web tracking code updated in WP when user updates settings in Quentn.

= 1.1.0 =
* Fix: Error when activate Elementor network wide in multisite.
* Add: Users can be logged-in automatically using a link generated by Quentn.

= 1.0.9 =
* Add support of Quentn datetime field with Elementor forms.

= 1.0.8 =
* Fixes issue with Elementor radio form fields mapping with Quentn custom fields.

= 1.0.7 =
* Fill Elementor PRO form fields automatically on page load.

= 1.0.6 =
* Integrate Elementor PRO forms with Quentn.

= 1.0.5 =
* Fix: Better data sanitation and validation.
* Add css files within the plugin.

= 1.0.4 =
* Fix: Better data sanitation and validation.
* Fix: Use the default timezone of the website for countdowns.
* Updated Bootstrap to Version 4.

= 1.0.3 =
* Fixes a bug with user account notification mail.

= 1.0.2 =
* Fixes issue with page restrictions on Nginx Webservers.
* Fixes a bug with page access url according to permalink settings.

= 1.0.1 =
* Cache restricted pages behave abnormally.
* Added support for LearnDash.

= 0.1.0 =
* Initial version.

== Upgrade Notice ==

= 1.2.12 =
Thanks for using Quentn Plugin! Please update the plugin to fix access links count for list of restricted pages.

= 1.2.11 =
Thanks for using Quentn Plugin! Please update the plugin to fix pagination and screen options for list of restricted pages and access overview.

= 1.2.10 - Security Update =
This is a critical security update. It addresses potential SQL injection vulnerabilities found in previous versions. Please update immediately to ensure your site remains secure.

= 1.2.9.1 - Security Update =
This is a critical security update. It addresses potential SQL injection vulnerabilities found in previous versions. Please update immediately to ensure your site remains secure.

= 1.2.9 - Security Update =
This is a critical security update. It addresses potential SQL injection vulnerabilities found in previous versions. Please update immediately to ensure your site remains secure.

= 1.2.8 =
Thanks for using Quentn Plugin! Please update the plugin to add log quentn activities. It will also fix any namespace conflict with other plugins.

= 1.2.7 =
Thanks for using Quentn Plugin! Please update the plugin. It reduced the number of API calls and tested with new wordpress ( 6.3 )

= 1.2.6 =
Thanks for using Quentn Plugin! Please update the plugin to fix error when sometimes Guzzle files were not autoloaded.

= 1.2.5 =
Thanks for using Quentn Plugin! Please update the plugin to fix Learndash error when new user is registered.

= 1.2.4 =
Thanks for using Quentn Plugin! Please update the plugin. It is now tested with new wordpress ( 6.1.1 ), Elementor ( 3.9.0 ) and Elemnetor PRO ( 3.9.0 ) versions.

= 1.2.3 =
Thanks for using Quentn Plugin! Please update the plugin to fix field mapping issues in form widget of Elementor PRO.

= 1.2.2 =
Thanks for using Quentn Plugin! Please update the plugin to make compatible for Elementor versions >= 3.5.

= 1.2.1 =
Thanks for using Quentn Plugin! Please update the plugin to fix the error handling while getting password reset key.

= 1.2.0 =
Thanks for using Quentn Plugin! Please update the plugin to fix the thrive theme bug in user restriction pages.

= 1.1.9 =
Thanks for using Quentn Plugin! Please update the plugin to fix the styling bug when using the Form widget of Elementor PRO

= 1.1.8 =
Thanks for using Quentn Plugin! Please update the plugin to fix ternary operator error in Elementor integration.

= 1.1.7 =
Thanks for using Quentn Plugin! Please update the plugin to avoid conflict of Guzzle library with other plugins.

= 1.1.6 =
Thanks for using Quentn Plugin! Please update the plugin to improve the namespace of the rest API routes.

= 1.1.5 =
Thanks for using Quentn Plugin! Please update the plugin to improve handling of page access permission.

= 1.1.4 =
Thanks for using Quentn Plugin! Please update the plugin to fix elementor integration for WordPress version 5.6.

= 1.1.3 =
Thanks for using Quentn Plugin! Please update the plugin to fix issue with load elementor PRO forms integration file.

= 1.1.2 =
Thanks for using Quentn Plugin! Please update the plugin to implement Quentn redirect, flood protection and spam protection in Elementor forms.

= 1.1.1 =
Thanks for using Quentn Plugin! Please update the plugin to update web tracking code in WP when user updates settings in Quentn.

= 1.1.0 =
Thanks for using Quentn Plugin! Please update the plugin to add support of auto log-in links generated by Quentn.

= 1.0.9 =
Thanks for using Quentn Plugin! Please update the plugin to add support of Quentn datetime field with Elementor forms.

= 1.0.8 =
Thanks for using Quentn Plugin! Please update the plugin to fixes issue with Elementor radio form fields mapping with Quentn custom fields.

= 1.0.7 =
Thanks for using Quentn Plugin! Please update the plugin to fill Elementor PRO form fields automatically on page load.

= 1.0.6 =
Thanks for using Quentn Plugin! Please update the plugin to integrate Elementor PRO forms with Quentn.

= 1.0.5 =
Thanks for using Quentn Plugin! This version has some improvements in design and security. Please Upgrade immediately.