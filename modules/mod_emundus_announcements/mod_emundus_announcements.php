<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_emundus_custom
 *
 * @copyright   Copyright (C) 2018 eMundus. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$announcement_content = $params->get('announcement_content', '');

require JModuleHelper::getLayoutPath('mod_emundus_announcements', 'default');
