<?php
/**
 * CHANGELOG
 *
 * @package		gantry
 * @version		4.1.31 April 11, 2016
 * @author		RocketTheme http://www.rockettheme.com
 * @copyright 	Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>

1. Copyright and disclaimer
----------------


2. Changelog
------------
This is a non-exhaustive changelog for Gantry, inclusive of any alpha, beta, release candidate and final versions.

Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

----------- 3.2.13 (J17) Release -----------
# Invalid CRT Parameters on Windows WAMP Servers
# Issue with template AdminPraise3 and the notice box not injected in the page, causing the JS to throw an error

----------- 3.2.12 (J17) Release -----------
# Non master template styles settings are not being used when default
# RokNavMenu js not loading with add script function
# As of J1.6.3 JParameter has been deprecated and JForm should be use instead

----------- 3.2.11 (J16) Release -----------
# JSON data not formatted correctly when saving a custom preset

----------- 3.2.10 (J16) Release -----------
# 1.7 undefined index and non-property in /libraries/joomla/updater/adapters/collection.php

----------- 3.2.9 (J16) Release -----------
# All colorchooser popups open in Opera when selecting different preset
# Automatically refresh RokBox (if exists) after Load More action

----------- 3.2.8 (J16) Release -----------
# Bug in modules.php for module name in 1.6
# gantry-splitmenu errors with a bad function call to RenderItem
# Work around for change in JModuleHelper::getModule in J1.7

------- 3.2.7 Release [15-Jul-2011] -------
^ Prepped for Joomla 1.7
+ Updated Google Webfonts

------- 3.2.6 Release [1-Jun-2011] -------
# Fix for missing calendar icons in edit screen
# Fix for assignment badges in opera
+ Added custom gantry field in order to have imagelist styled properly

------- 3.2.5 Release [1-Jun-2011] -------
# Fix for missing Webfonts
# Fix for Gantry Registry Formaters

------- 3.2.4 Release [20-Apr-2011] -------
+ Added support for https in webfonts feature
+ Added missing Google webfont names
+ Added support for Joomla 1.6 built in updater
^ Cleaned up plugin a bit

------- 3.2.3 Release [06-Apr-2011] -------
^ Reworked SmartLoad to take into account inline style or custom width/height images attribute
- Smartload no longer take in consideration img tags with no width and height attributes set. It's just not consistent across browsers and acting randomly at each page load.
+ Added Category GantryField
# Fixed gantry template detection in template manager when cache is enabled
^ Changed way the redirect happens for gantry template styles under template manager
+ Added URL changing for gantry templates styles under template style manager
# Fix for Joomla 1.6.2 removing mootools from backend by default.

------- 3.2.2 Release [31-Mar-2011] -------
# Fix for imenu.js not working properly
# Fix for Smartloader Feature
# Fix for page suffix not being added to body class
# Fix for error displaying template params when gantry cache is enabled
# Fix for disableing the component on the front page
# Fix for error displaying template params when gantry cache is enabled
# Fix for backwards dashes in parameter names
^ Change to temp var for viewswitcher cookie

------- 3.2.1 Release [18-Mar-2011] -------
+ Added pin on Tips to follow you while scrolling the page
# Fix for presets when Toggles involved
# Fallback support for Hathor admin style


------- 3.2.0 Release [04-Mar-2011] -------
! Changelog Creation