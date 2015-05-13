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

jimport('joomla.application.component.view');

/**
 * View class for a list of Emundus.
 */
class EmundusViewProgramme extends JViewLegacy
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {

        // Get data from the model
        //$items = $this->get('Items');
        $model = $this->getModel();

        $app = JFactory::getApplication();
        $jinput = $app->input;
        $itemid = $jinput->get('Itemid', 0, 'int');
        $menu_params = $model->getParams($itemid);

        $this->com_emundus_programme_progdesc_class = '';
        $this->com_emundus_programme_campdesc_class = '';
        $this->com_emundus_programme_showcampaign = '0';
        $this->com_emundus_programme_showprogramme ='1';
        $this->com_emundus_programme_showprogramme = $menu_params['com_emundus_programme_showprogramme'];
        $this->com_emundus_programme_showcampaign = $menu_params['com_emundus_programme_showcampaign'];
        $this->com_emundus_programme_progdesc_class = $menu_params['com_emundus_programme_progdesc_class'];
        $this->com_emundus_programme_campdesc_class = $menu_params['com_emundus_programme_campdesc_class'];

        $idcampaign = $jinput->get('id', 0, 'int');
        $campaign = $model->getCampaign($idcampaign);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }
        // Assign data to the view
        $this->campaign = $campaign;
        $this->menu_params = $menu_params;

        // Display the template
        parent::display($tpl);

    }
}