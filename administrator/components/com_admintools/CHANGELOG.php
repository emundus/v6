<?php die() ?>
Admin Tools 6.1.8
================================================================================
~ Remove the warning about the version being too old.

Admin Tools 6.1.7
================================================================================
# [LOW] Htaccess Maker: Fixed explicitly allowing CORS
# [LOW] NginX Conf Maker: Fixed explicitly allowing CORS
# [LOW] WebConfig Maker: Fixed explicitly allowing CORS

Admin Tools 6.1.6
================================================================================
+ Added .xsl to the allowed file types in front- and backend directories in the .htaccess Maker, NginX Conf Maker and web.config Maker

Admin Tools 6.1.5
================================================================================
# [LOW] Fixed default enable/disable values of some WAF features

Admin Tools 6.1.4
================================================================================
- Removed “Convert all links to HTTPS” feature; it has stopped making sense since circa 2015.
# [HIGH] Cannot edit component Options on PHP 8 if the site had Admin Tools 5.1.0 to 5.7.2 installed in the past

Admin Tools 6.1.3
================================================================================
# [MEDIUM] Possible PHP error on PHP 8 if the .htaccess/NginX Conf/web.config Maker configuration had empty exception files
# [MEDIUM] UploadShield: Fixed fatal under PHP 8.0 when an empty file is uploaded

Admin Tools 6.1.2
================================================================================
+ Support improved Joomla Update in Joomla 4.0.4
~ Improve language string
# [HIGH] “Neutralise SVG execution” in NginX Conf Maker would cause a server error.

Admin Tools 6.1.1
================================================================================
~ Changes in Joomla 4.0.0-RC5 broke the date and time input fields. Now using native HTML 5 controls.
# [HIGH] Uninstallation broken on Joomla 4 due to different installation script event handling.
# [LOW] Optimise Tables fails on Joomla 3 when you're using the PDO MySQL database driver.

Admin Tools 6.1.0
================================================================================
+ Added default rules to not block DuckDuckGo, Baidu, Yahoo and Yandex indexers
+ Repair and Optimize tables is now possible again on Joomla 4
+ You can now choose the protection mode for the Administrator password protection
+ .htaccess Maker: Disable client-side risky behavior in static content
+ Explicitly allowed domain names
~ Updated the default offline.html file contents with modern CSS
~ Admin password protection will now reset error pages using an even more aggressive method
~ Clean temp folder: only files and folders over 60 seconds old will be deleted.
# [MEDIUM] The system plugin would perform temporary redirections with HTTP 301 (permanent redirection), causing problem with CDNs and proxies
# [LOW] NginX Conf Maker: Missing 'noserversignature'
# [LOW] PHP File Change Scanner reports: the scan start was no longer reported
# [LOW] Backend blocked requests graph would not reload correctly

Admin Tools 6.0.6
================================================================================
# [HIGH] Fixed wrongly detection of unauthorised security settings modification under NginX and IIS
# [MEDIUM] The print view of the PHP File Change Scanner report is missing information
# [LOW] Default email templates still had GeoIP information even though we removed this feature in January 2020
# [LOW] The system plugin is incompatible with the Joomla 4 API application

Admin Tools 6.0.5
================================================================================
~ Update Chart.js
# [LOW] The server configuration file was detected as changed when it hasn't.
# [LOW] The Quick Setup Wizard didn't enable the “Reset custom error pages” option when applying the administrator password protection.

Admin Tools 6.0.4
================================================================================
~ Rewritten installer plugin
~ Converted all tables to InnoDB for better performance
# [LOW] Administrator Exclusive Allow IP List displayed ordering instead of checkboxes, making it impossible to delete entries
# [LOW] Error dialog if you click on a feature button before the security exceptions graph is fully loaded

Admin Tools 6.0.3
================================================================================
~ Rewritten installer plugin
# [LOW] Marking a file as safe/unsafe redirects you to an empty scan results page
# [LOW] Master Password causes Javascript message box while loading graphs (gh-240)

Admin Tools 6.0.2
================================================================================
# [MEDIUM] Could not delete records using an IP as their primary key
# [LOW] Joomla was printing out a lot of warnings when installing the extension

Admin Tools 6.0.1
================================================================================
! Update could fail on sites with old plugins we have removed years ago still installed

Admin Tools 6.0.0
================================================================================
+ Rewritten with FOF 4
+ Renamed ViewTemplates to tmpl (Joomla 4 convention, with fallback code for Joomla 3)
+ Yes/No options in the component and plugin options now work correctly under Joomla 4.0 beta 7 and later
# [LOW] QuickStart Wizard: Inherit default configuration before applying per-site optimizations

Admin Tools 5.9.2
================================================================================
+ WAF Exceptions and Deny List will consider the URL parameter 'controller' as an alias for 'view' on Joomla 4
+ Multiple selection fields in Configure WAF now use Chosen (J3) or Choices.js (J4) for easier selections.
~ All tables now have a PRIMARY KEY
~ Improve the layout in the Unblock an IP page
~ Improved CHANGELOG layout in the Control Panel page
# [MEDIUM] Delete Inactive users feature was deleting users waiting for activation
# [LOW] ActionLog and Installer plugins showed the wrong version in Extensions, Manage
# [LOW] Htaccess Maker: Blocking malicious user agents is now case insensitive

Admin Tools 5.9.1
================================================================================
- Dropped support for PHP 7.1.0
~ Add PHP 8.0 in the list of known PHP versions, recommend PHP 7.4 or later
# [LOW] Fixed HTML block page when original request format wasn't HTML

Admin Tools 5.9.0
================================================================================
+ .htaccess and NginX Conf Maker: Better support for more file types in setting the expiration time
+ .htaccess, NginX and WebConfig Maker: added option to reset all the options to default values
- Removed update notifications inside the component
~ Improved unhandled PHP exception error page
~ Using nullable DATETIME instead of zero dates
# [HIGH] web.config Maker would block some Joomla index.php URLs whose last parameter ended in ".php"
# [HIGH] template=something blocked for com_ajax requests (they must always be allowed)
# [LOW] Remote file change scanner with frontend URL would fail if the secret word contained the plus sign character due to a PHP bug.
# [LOW] Wrong version query to the namespace.js
# [LOW] Temporary Super Users were blocked by the Monitor Super Users feature
# [LOW] Session Cleaner was running only when backend users accessed to the site

Admin Tools 5.8.0
================================================================================
+ .htaccess Maker: Automatically compress static resources will now use Brotli compression with priority if it's supported by both the server (mod_brotli) and the client (accepts encoding "br").
+ .htaccess and NginX Conf Maker: Neutralise SVG script execution. There are major caveats! Please do read the documentation before enabling this option.
+ Inherit the base font size instead of defining a fixed one
- Removed support for Internet Explorer
~ Adjust size of control panel icons
~ Improve the UX of the URL Redirect form page
~ More clarity in the in-component update notifications, explaining they come from Joomla itself
~ Improve "Redirect index.php to the site's root" code
# [LOW] web.config Maker: 1 year option for the expiration time did not take effect
# [LOW] Auto-compression and expiration time didn't work on .js files served with MIME type text/javascript
# [LOW] There were leftover, non-functional dismiss buttons in some information and warning messages in the backend
# [LOW] Removed obsolete country and continent information from the default email templates for new installations.
# [LOW] Rescue URL would trigger an Admin Query String blocked request before sending the email (just annoying, not really causing a functional problem)

Admin Tools 5.7.4
================================================================================
+ .htaccess/NginX Conf/web.config Maker: more options for the expiration time
# [LOW] Clearing the Security Exceptions Log date filters can cause an error on MySQL 8
# [LOW] Expiration time was not being set for WOFF fonts in the .htaccess Maker

Admin Tools 5.7.3
================================================================================
~ Small change in the FOF library to prevent harmless but confusing and annoying errors from appearing during upgrade
~ The following items are carried over from the retracted version 5.7.0
+ Improved Apache and IIS server signature removal
+ Improved CORS handling
+ Added support for Joomla 4 API application in the .htaccess Maker and Web.Config Maker
+ Added support for automatically serving .css.gz/.js.gz files in the .htaccess Maker and Web.Config Maker per Joomla 4 recommendations
- Removed Change Database Collation and Repair & Optimise Tables features under Joomla 4 (not supported by the J4 database driver)
- Removed the CSRFShield feature
~ Minimum requirements raised to PHP 7.1, Joomla 3.9
~ Use JAccess instead of DB queries [gh-223]
~ Improve the rendering of the System - Admin Tools plugin options
~ Improve the rendering of the component options
~ Changed the terms blacklist and whitelist to be more clear
~ Changed the term Security Exceptions to Blocked Requests for clarity
# [HIGH] Emergency Off-line completely broken under Joomla 4
# [HIGH] Purge Sessions does not work under Joomla 4
# [HIGH] Features with modal dialogs would not work under Joomla 4
# [MEDIUM] The "User groups to check for well-known passwords" feature could cause a PHP notice when modifying the component Options
# [MEDIUM] Temporary Super Users feature does not work when Monitor Super Users or Disable Editing Backend Users features are enabled
# [LOW] Some help text blocks were using the wrong class, making them illegible in Dark Mode.
# [LOW] Email Templates help text referenced country and continent which were removed in version 5.5.0.
# [LOW] Rescue URL doesn't work with Joomla 4
# [LOW] Notices thrown when plugin loaded in Joomla 4 Console application
# [LOW] Master Password page incorrectly referenced an UnlockIP view with an untranslated name; the correct is UnblockIP
# [LOW] Missing filtering reason for "Monitor Super User" feature

Admin Tools 5.7.2
================================================================================
# [LOW] Missing filtering reason for "Monitor Super User" feature

Admin Tools 5.7.1
================================================================================
! Newline at the top of file can cause site breakage

Admin Tools 5.7.0
================================================================================
+ Improved Apache and IIS server signature removal
+ Improved CORS handling
+ Added support for Joomla 4 API application in the .htaccess Maker and Web.Config Maker
+ Added support for automatically serving .css.gz/.js.gz files in the .htaccess Maker and Web.Config Maker per Joomla 4 recommendations
- Removed Change Database Collation and Repair & Optimise Tables features under Joomla 4 (not supported by the J4 database driver)
- Removed the CSRFShield feature
~ Minimum requirements raised to PHP 7.1, Joomla 3.9
~ Use JAccess instead of DB queries [gh-223]
~ Improve the rendering of the System - Admin Tools plugin options
~ Improve the rendering of the component options
~ Changed the terms blacklist and whitelist to be more clear
~ Changed the term Security Exceptions to Blocked Requests for clarity
# [HIGH] Emergency Off-line completely broken under Joomla 4
# [HIGH] Purge Sessions does not work under Joomla 4
# [HIGH] Features with modal dialogs would not work under Joomla 4
# [MEDIUM] The "User groups to check for well-known passwords" feature could cause a PHP notice when modifying the component Options
# [MEDIUM] Temporary Super Users feature does not work when Monitor Super Users or Disable Editing Backend Users features are enabled
# [LOW] Some help text blocks were using the wrong class, making them illegible in Dark Mode.
# [LOW] Email Templates help text referenced country and continent which were removed in version 5.5.0.
# [LOW] Rescue URL doesn't work with Joomla 4
# [LOW] Notices thrown when plugin loaded in Joomla 4 Console application
# [LOW] Master Password page incorrectly referenced an UnlockIP view with an untranslated name; the correct is UnblockIP

Admin Tools 5.6.0
================================================================================
+ Prevent www and non-www redirs when Joomla site settings are wrong
+ Added feature to import configuration settings from a remote URL on a schedule
+ Add cartupdate to the list of allowed tmpl keywords to cater for VirtueMart's not following Joomla best practices
+ The .htaccess Set a Long Expiration Time now also applies a no-cache setting for administrator URLs to prevent browsers from caching redirects and / or error messages in admin pages
+ Add WebP to Set a Long Expiration Time in .htaccess and NginX Conf Maker
~ Better IP Workarounds auto-detection
# [LOW] Control Panel graphs are affected when you apply filters in the Security Exceptions Log page.
# [LOW] URL Redirection: URL fragment included twice after redirecting to a URL that includes a fragment
# [LOW] Monitor Critical Files: PHP warning sometimes issued from leftover code
# [LOW] PHP Scanner: fixed scanning when PHP reports installation path being /

Admin Tools 5.5.0
================================================================================
- Removed GeoGraphic IP blocking due to changes in MaxMind's policy (see our site's News section)
- Removed System - Admin Tools Joomla! Update Email plugin. This feature has been contributed to Joomla itself a long time ago
+ IP Workarounds: added new value "auto" for automatically detecting and enabling them if required
+ Common PHP version warning scripts
~ Refactored backend code for the "Change Database Collation" feature.
~ Improved Dark Mode
# [HIGH] CLI (CRON) scripts could sometimes stop with a Joomla crash due to Joomla's mishandling of the session under CLI.
# [HIGH] New installations may get a SQL error on installation or trying to access the component
# [LOW] Troubleshooting email on administrative functions was always sent, regardless of the option's setting
# [LOW] Exception thrown when blocking an attack with a customisable error page if the blocked request had already set the 'layout' request parameter

Admin Tools 5.4.0
================================================================================
+ Administrator IP whitelist, Never Block these IPs: you can now add dynamic IPv6 domain names instead of IPs by prefixing them with #.
+ Support for Dark Mode
+ Support for Joomla 4 Download Key management in the Update Sites page
# [LOW] Missing SessionShield value inside reasons select lists

Admin Tools 5.3.4
================================================================================
+ Installer plugin to fix update issues after using Joomla's Rebuild Update Sites feature
+ Password-protect Administrator: option to reset custom error pages to avoid 404 errors accessing administrator
+ Admin Tools will warn the user if it detects that manual edits were performed on server configuration files (ie .htaccess, nginx.conf or web.config)
+ Allow editing own Joomla! user profile even when editing backend user information is forbidden
~ Disable forgotten users no longer requires the user groups to give backend or Super User access to consider them
~ Work towards future PHP 7.4 support
~ Away Schedule is now more clear about the use of time zones
# [HIGH] Core version: Administrator Password Protect would result in fatal error
# [LOW] JDEBUG not defined under CLI
# [LOW] Prevent fatal errors while scanning very large files

Admin Tools 5.3.3
================================================================================
+ Added option to log usernames or not during failed logins (required by GDPR)
+ Htaccess Maker: Added webp and WEBP file extensions to the default list of allowed extensions. Please remember to add them to your list (both for frontend and backend)
+ NginX Maker: Added webp and WEBP file extensions to the default list of allowed extensions. Please remember to add them to your list (both for frontend and backend)
+ WebConfig Maker: Added webp and WEBP file extensions to the default list of allowed extensions. Please remember to add them to your list (both for frontend and backend)
+ Troubleshooting email sent automatically whenever your Admin Tools administrative action might lock you out of your site (gh-149)
+ Added check if user request has an header usually used for proxied connection and suggest him to turn on IP workarounds (gh-200)
# [HIGH] IP filtering with CIDR or netmask notation may not work for certain IP address blocks

Admin Tools 5.3.2
================================================================================
~ PHP File Change Scanner comment editing now uses the Joomla WYSIWYG editor.
# [LOW] A Joomla installer bug caused an error message to be displayed when updating from 5.3.0 or earlier.

Admin Tools 5.3.1
================================================================================
+ Display detected IP, country and continent inside the Geoblocking IP feature
~ Formerly unsupported Pro to Core downgrade with simultaneous version change no longer causes a site crash
# [MEDIUM] Failed backend login email always reports user as "Guest"
# [MEDIUM] Rescue mode was always disabled
# [LOW] Action log plugin would result in untranslated strings displayed in the Latest Actions backend module

Admin Tools 5.3.0
================================================================================
+ Protection of all component and plugin folders against direct web access
+ Disable access to backend users who haven't logged in for a long time (gh-188)
+ Updated default list of blocked User Agents
+ Joomla! 4 alpha 7 compatibility
~ Added PHP malware samples in the PHP File Change Scanner
# [LOW] Failed Login log entries would be deleted on upgrade even if they don't contained plaintext passwords

Admin Tools 5.2.1
================================================================================
+ Mark All as Safe button in the PHP File Change Scanner report viewer
# [MEDIUM] Super User list not cleared on deactivating the Monitor Super Users feature, requiring you to edit every Super User added in the meantime after re-enabling the feature
# [LOW] Fixed a fatal error if a security exception is triggered in the backend while using HTML templates
# [LOW] PHP File Change Scanner page: Possible Threats column included "Marked as Safe" files.
# [LOW] PHP File Change Scanner, Examine file view: pressing Cancel took you to a page with no records displayed

Admin Tools 5.2.0
================================================================================
+ Referrer Policy option for web.config Maker
+ Referrer Policy option for NginX Conf Maker
+ Rescue URL will tell you if you are using the wrong email address
+ Administrator IP whitelist, Never Block these IPs: you can now add dynamic IP domain names instead of IPs by prefixing them with @.
~ "Disable editing backend users' properties" will now let Joomla! 3.9 privacy consent to go through
~ "Forbid frontend Super Administrator login" will now let Joomla! 3.9 privacy consent to go through
~ Detect if the GeoIP Provider plugin was installed and then deleted but not uninstalled.
~ Cosmetic improvements to the PHP File Change Scanner progress modal
# [HIGH] NginX Conf Maker rules removed URL queries from requests without index.php in them
# [MEDIUM] Feature Monitor Critical Files was not working
# [MEDIUM] Monitor specific files feature was working only with the last item of the list
# [LOW] PHP 7.3 warning in the Control Panel page
# [LOW] URL Redirection required you to enter the subdirectory path when your site is located in a subdirectory instead of a path relative to the site's root URL. This made redirections non-portable.
# [LOW] Fixed JavaScript issues when Content-Security-Policy header is missing the unsafe-eval value
# [LOW] Missing filtering option in the Security Exception Logs page

Admin Tools 5.1.4
================================================================================
+ PHPShield feature now will block additional stream wrappers
- Remove IP workarounds "recommended setting" notice due to high rate of false detections
~ Removed legacy menu params that could cause issues with future versions of Joomla! (3.9 and later)
~ web.config Maker: add automatic dynamic compression of XML, XHTML and RSS documents
# [MEDIUM] The feature to check for leaked passwords was enabled for every user group, ignoring the settings
# [MEDIUM] HTTP Referer Policy language strings were untranslated because of the use of an extra "R" in the code
# [LOW] User re-activation email always displays the username as "Guest"
# [LOW] Options requiring .htaccess support shown to IIS and NginX users
# [LOW] web.config Maker: "Remove Apache and PHP version signature" causes a server error

Admin Tools 5.1.3
================================================================================
# [HIGH] IP Workarounds were always enabled
# [LOW] Control Panel graphs do not display on Safari due to Safari's quirky JavaScript date parsing
# [LOW] Admin Tools proposed secret URL parameter may start with a number
# [LOW] Prevent a security exception when the secret param is used and we're explicitly logging out

Admin Tools 5.1.2
================================================================================
! .htaccess Maker's front-end protection causes 403 errors (regression introduced in 5.1.1)

Admin Tools 5.1.1
================================================================================
# [HIGH] .htaccess Maker's front-end protection breaks sites using SEF URLs without URL rewrite, causing all URLs to report a 403 Forbidden error
# [MEDIUM] Fixed file scanning when file is inaccessible due to open_basedir restrictions
# [LOW] Grant access to robots.txt file when .htaccess Maker is used

Admin Tools 5.1.0
================================================================================
+ Added feature to manually unblock a specific IP
+ Added option to choose if Admin Tools should write the log file or not (required by GDPR)
+ Added option to allow only specific email domains during user registration
+ Added feature to check if user password is inside any dictionary of leaked passwords. This feature is disabled by default.
- Removed the option to log failed passwords. This could be a security risk since the information is stored unencrypted in the security exceptions database table.
~ Changed the debug log file type to .php. READ THE DOCUMENTATION. This may have implications on your host.
# [HIGH] .htaccess Maker does not block access to static media files for front-end directories which are not listed under "Frontend directories where file type exceptions are allowed"
# [MEDIUM] Critical Files Monitor feature was not enabled due to a SQL error
# [MEDIUM] Missing reason for Critical Files Monitor inside WAF Email Templates configuration
# [MEDIUM] Fixed blocking non authorized IPs in Emergency Offline Mode
# [LOW] Fixed default values for 404 Shield feature
# [LOW] Copy doesn't work in Redirect URL

Admin Tools 5.0.2
================================================================================
# [HIGH] .htaccess Maker causes 500 error on Apache 2.4 if mod_filter is not loaded and you enable the "Automatically compress static resources" feature
# [MEDIUM] WAF Blacklist rules were always applied, regardless if we were in the backend or the frontend
# [MEDIUM] Fixed Console Warn feature causing issues with RTL languages
# [LOW] The charts overflow their container and the legend is illegible
# [LOW] Fixed record editing in WAF Blacklist feature
# [LOW] Fixed toggling published status in list views in WAF Blacklist and WAF Templates pages
# [LOW] Feature 404 Shield was always enabled

Admin Tools 5.0.1
================================================================================
+ Warn users if either FOF or FEF is not installed
# [MEDIUM] Removed two detection rules which trigger a bug in older versions of PHP
# [LOW] Regression: "Repair & Optimize tables" button was linking to "Temp and log directory check" page
# [LOW] Fixed deleting a record from Auto IP Blocking History
# [LOW] Fixed integration between 404 Shield feature and com_redirect

Admin Tools 5.0.0
================================================================================
+ Purge Sessions now uses the Joomla! API to garbage collect expired sessions before forcibly truncating the sessions table.
# [LOW] Missing control for "Monitor Super User accounts" option in Configure WAF page
# [LOW] Regression: Administrator Password icon should reflect whether the feature is applied or not
# [LOW] Session table optimizer won't run on Joomla 3.8+

Admin Tools 5.0.0.b1
================================================================================
+ Rewritten interface using our brand new Akeeba Frontend Framework
+ Joomla! 4 compatibility
~ Simplified tab names in the Configure WAF page
# [LOW] Security exception not logged in the database if the URL is too long
# [LOW] Fixed fatal error while exporting empty list of Blacklisted Addresses
