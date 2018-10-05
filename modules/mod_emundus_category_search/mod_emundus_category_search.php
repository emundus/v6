<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundus_category_search
 * @copyright	Copyright (C) 2018 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

// Load list of contact requests to and from the user.
$helper = new modEmundusCategorySearchHelper();
$categories = $helper->loadCategories();
$search_page = $params->get('search_page');
$heading = $params->get('heading');

if (!empty($categories))
	require JModuleHelper::getLayoutPath('mod_emundus_category_search', $params->get('tmpl','default'));
