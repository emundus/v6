<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
/**
 * Jobs list controller class.
 */
class EmundusModelAdministratorCampaign extends JModelList
{
    public function installCampaignMore() {
        $installed = false;

        $tasks = [];

        require_once (JPATH_ROOT . '/administrator/components/com_emundus/helpers/update.php');
        $db = JFactory::getDbo();

        /**
         * Tables that must exists
         * jos_emundus_ranking_hierarchy
         * jos_emundus_ranking_hierarchy_view
         * jos_emundus_ranking
         */
        $columns = [
            [
                'name' => 'date_time',
                'type' => 'DATE',
                'null' => 0,
                'default' => 'NOW()'
            ],
            [
                'name' => 'campaign_id',
                'type' => 'INT',
                'null' => 0
            ]
        ];
        $foreign_keys = [
            [
                'name'           => 'jos_emundus_setup_campaigns_more_campaign_id_fk',
                'from_column'    => 'campaign_id',
                'ref_table'      => 'jos_emundus_setup_campaigns',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
        ];

        $response = EmundusHelperUpdate::createTable('jos_emundus_setup_campaigns_more', $columns, $foreign_keys);
        $tasks[] = $response['status'];


        $result = EmundusHelperUpdate::addFabrikForm([
            'label' => 'Campagnes - Informations spécifiques',
            'form_template' => 'emundus',
            'view_only_template' => 'emundus',
        ]);
        $tasks[] = $result['status'];
        $form_id = $result['id'];

        $result = EmundusHelperUpdate::addFabrikList([
            'label' => 'Campagnes - Informations spécifiques',
            'db_table_name' => 'jos_emundus_setup_campaigns_more',
            'form_id' => $form_id
        ]);
        $tasks[] = $result['status'];

        if (!in_array(false, $tasks)) {
            $installed = true;
        }

        return $installed;
    }
}