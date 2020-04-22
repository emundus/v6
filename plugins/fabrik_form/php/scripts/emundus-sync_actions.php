<?php
defined('_JEXEC') or die();
/**
 * @version 1: sync_actions.php 89 2019-04-15 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2019 eMundus SAS. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Sync access rights to group ACL.
 */

$jinput = JFactory::getApplication()->input;

require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'actions.php');
$m_actions = new EmundusModelActions();
$m_actions->syncAllActions();

JFactory::getApplication()->redirect('index.php?option=com_emundus&view=users&layout=showgrouprights&Itemid=1169&rowid='.$jinput->get('jos_emundus_setup_groups___id'));
