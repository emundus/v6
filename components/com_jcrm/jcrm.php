<?php
/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

if(!in_array(7, JAccess::getAuthorisedViewLevels(JFactory::getUser()->id)))
{
	die(JText::_('ACCESS_DENIED'));
}

// Execute the task.
$controller	= JControllerLegacy::getInstance('Jcrm');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
