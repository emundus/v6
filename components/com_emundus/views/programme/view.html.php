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
        $menu_params = $app->getMenu()->getActive()->params;


        $this->com_emundus_programme_progdesc_class = '';
        $this->com_emundus_programme_campdesc_class = '';
        $this->com_emundus_programme_showcampaign = '0';
        $this->com_emundus_programme_showprogramme ='1';
        $this->com_emundus_programme_showlink ='0';
        $this->com_emundus_programme_campaign_id = $menu_params->get('com_emundus_programme_campaign_id',0);
        $this->com_emundus_programme_showprogramme = $menu_params->get('com_emundus_programme_showprogramme');
        $this->com_emundus_programme_showcampaign = $menu_params->get('com_emundus_programme_showcampaign');
        $this->com_emundus_programme_progdesc_class = $menu_params->get('com_emundus_programme_progdesc_class');
        $this->com_emundus_programme_campdesc_class = $menu_params->get('com_emundus_programme_campdesc_class');
        $this->com_emundus_programme_showlink = $menu_params->get('com_emundus_programme_showlink');
        $this->com_emundus_programme_showlink_class = $menu_params->get('com_emundus_programme_showlink_class');
        $this->com_emundus_programme_candidate_link = $menu_params->get('com_emundus_programme_candidate_link', 'index.php?option=com_fabrik&amp;view=form&amp;formid=307&amp;Itemid=2700');


        $idcampaign = $jinput->get('id') ? $jinput->get('id',0,'int') : $jinput->get('cid',0,'int');
		if(empty($idcampaign)){
			$idcampaign = $this->com_emundus_programme_campaign_id;
		}
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
