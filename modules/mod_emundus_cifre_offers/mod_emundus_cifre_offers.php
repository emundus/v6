<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_emundususerdropdown
 * @copyright	Copyright (C) 2018 emundus.fr, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css");

// Load list of contact requests to and from the user.
$helper = new modEmundusCifreOffersHelper();
$offers = $helper->getContactRequests();

require JModuleHelper::getLayoutPath('mod_emundus_cifre_offers', 'default');

