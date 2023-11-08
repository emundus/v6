<?php
/**
 * @package        Joomla.Site
 * @subpackage     mod_emunduscifresuggestions
 * @copyright      Copyright (C) 2018 emundus.fr, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css");

$nb_suggestions = $params->get('nb_suggestions', 5);
$intro          = $params->get('intro', '');

$helper = new modEmundusCifreSuggestionsHelper();
$offers = $helper->getSuggestions($nb_suggestions);

require JModuleHelper::getLayoutPath('mod_emundus_cifre_suggestions', 'default');

