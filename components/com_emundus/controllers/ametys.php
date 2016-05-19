<?php

/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2016 emundus.fr. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Ametys controller class.
 */
class EmundusControllerAmetys extends EmundusController {

    /**
     * Method to display tools.
     *
     * @return  void
     * @since   1.6
     */
    function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) ){
            $default = 'default';
            JRequest::setVar('view', $default );
        }
        parent::display();
    }  

    public function getprogrammes(){ 
        $user = JFactory::getUser();
        $view = JRequest::getVar('view', null, 'GET', 'none',0);
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

        $model = $this->getModel('ametys');   

        if( !EmundusHelperAccess::asCoordinatorAccessLevel($user->id) )
        {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        }
        else
        {
            $programmes = $model->getProgrammes();

            if(count($programmes) > 0)
                $tab = array('status' => 1, 'msg' => JText::_('PROGRAMMES_RETRIEVED'), 'data' => $programmes);
            else
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_PROGRAMMES'), 'data' => $programmes);
        }
        echo json_encode((object)$tab);
        exit;
    }

}
