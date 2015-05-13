<?php

/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Jcrm.
 */
class JcrmViewMigrate extends JViewLegacy {

    protected $items;
    protected $pagination;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $this->state = $this->get('State');
        
        

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        $this->addToolbar();

        $input = JFactory::getApplication()->input;
        $view = $input->getCmd('view', '');
        JcrmHelper::addSubmenu($view);

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar() {

    }

}
