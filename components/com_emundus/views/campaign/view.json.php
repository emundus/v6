<?php


/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @copyright	Copyright (C) 2016 eMundus SAS. All rights reserved.
 * @license    GNU/GPL
 * @author     eMundus SAS - Benjamin Rivalland
 */

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * campaign View
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusViewCampaign extends JViewLegacy {

    function __construct($config = array()) {

        // Set up the data to be sent in the response.
        // call camaign controller
        require_once (JPATH_COMPONENT.DS.'models'.DS.'campaign.php');

        parent::__construct($config);
    }

    function display($tpl = null) {
            
        $m_campaign = new EmundusModelCampaign();
        $data = $m_campaign->getTeachingUnity();

        foreach ($data as $key => $row) {

            // Process city name
            $town = preg_replace('/[0-9]+/', '',  str_replace(" cedex", "", ucfirst(strtolower($row->location_city))));
            $town =  ucwords(strtolower($town), '\',. ');
            $beforeComma = strpos($town, "D'");
            if (!empty($beforeComma)) {
                $replace = strpbrk($town, "D'");
                $row->location_city = substr_replace($town,lcfirst($replace), $beforeComma);
            }

            // Proccess address
            $row->location_address = ucfirst(strtolower($row->location_address));

            // Proccess URL
            $row->url = 'https://www.competencesetformation.fr/formation?rowid='.$row->row_id;

            // Process tax.
            $row->prix_ttc = $row->tax_rate == 1;

            $data[$key] = $row;

        }

        echo json_encode($data);
    }
}
