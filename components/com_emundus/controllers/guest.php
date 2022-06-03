<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      eMundus - Benjamin Rivalland
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      v6
 */
class EmundusControllerGuest extends JControllerLegacy {
    var $m_campaign = null;

    function __construct($config = array()){
        parent::__construct($config);

        $this->m_campaign = $this->getModel('campaign');
    }

    public function getallcampaign() {

        $jinput = JFactory::getApplication()->input;

        $filter = $jinput->getString('filter');
        $sort = $jinput->getString('sort');
        $recherche = $jinput->getString('recherche');
        $lim = $jinput->getInt('lim');
        $page = $jinput->getInt('page');
        $program=$jinput->getString('program','all');

        $campaigns = $this->m_campaign->getAssociatedCampaigns($filter, $sort, $recherche, $lim, $page,$program);

        if (count($campaigns) > 0) {
            $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $campaigns);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('NO_CAMPAIGNS'), 'data' => $campaigns);
        }

        echo json_encode((object)$tab);
        exit;
    }
}
?>
