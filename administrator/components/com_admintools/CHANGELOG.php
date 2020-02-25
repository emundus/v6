<?php die() ?>
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
