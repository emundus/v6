<?php

/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

/**
 * Emundus helper.
 */
class EmundusHelper {

    /**
     * Configure the Linkbar.
     */
    public static function addSubmenu($vName = '') {
        		JSubMenuHelper::addEntry(
			JText::_('COM_EMUNDUS_TITLE_JOBS'),
			'index.php?option=com_emundus_onboard&view=jobs',
			$vName == 'jobs'
		);

    }
}
