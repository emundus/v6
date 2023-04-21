<?php
/**
 * Main Emundus administrator controller
 * @version     2.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015-2023 Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\BaseController;

jimport('joomla.application.component.controller');

/**
 * Emundus master display controller.
 *
 * @package     Joomla.Administrator
 * @subpackage  Emundus
 * @since       3.0
 */

class EmundusAdminController extends BaseController
{
    /**
     * Display the view
     *
     * @param	bool    $cachable	If true, the view output will be cached
     * @param	array	$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return	void
     */

    public function display($cachable = false, $urlparams = false)
    {
        $this->default_view = 'panel';
        parent::display();
    }

}