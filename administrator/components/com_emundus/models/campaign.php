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

        $result = EmundusHelperUpdate::createTable('jos_emundus_setup_campaigns_more', $columns, $foreign_keys);
        $tasks[] = $result['status'];


        $form_params = [
            'only_process_curl' => ['onAfterProcess'],
            'curl_code' => [
                "echo '<script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@8\"></script>';\r\necho '<script src=\"https://code.jquery.com/jquery-3.3.1.slim.js\" integrity=\"sha256-fNXJFIlca05BIO2Y5zh1xrShK3ME+/lYZ0j+ChxX2DA=\" crossorigin=\"anonymous\"></script>';\r\n\r\necho '<style>\r\n.em-swal-title{\r\n  margin: 8px 8px 32px 8px !important;\r\n  font-family: \"Maven Pro\", sans-serif;\r\n}\r\n</style>';\r\n\r\ndie(\"<script>\r\n      $(document).ready(function () {\r\n        Swal.fire({\r\n          position: 'top',\r\n          type: 'success',\r\n          title: '\".JText::_('SAVED').\"',\r\n          showConfirmButton: false,\r\n          timer: 2000,\r\n          customClass: {\r\n            title: 'em-swal-title',\r\n          },\r\n          onClose: () => {\r\n            let old_url = window.location.href;\r\n            let new_url = old_url.replace(/rowid=[0-9]+/, 'rowid=\" . \$data['rowid'] . \"');\r\n    \r\n            if(new_url.indexOf('rowid') === -1) {\r\n                new_url += '&rowid=\" . \$data['rowid'] . \"';\r\n            }\r\n            \r\n            window.location.href = new_url;\r\n          }\r\n        })\r\n      });\r\n      </script>\");"
            ],
            'plugins' => ['php'],
            'plugin_locations' => ['both'],
            'plugin_events' => ['both'],
            'plugin_state' => [1],
            'form_php_file' => [-1],
            'goback_button' => 0
        ];

        $form = EmundusHelperUpdate::addFabrikForm([
            'label' => 'Campagnes - Informations spécifiques',
            'form_template' => 'emundus',
            'view_only_template' => 'emundus'
        ], $form_params);
        $tasks[] = $form['status'];
        if ($form['status']) {
            $form_id = $form['id'];
            $group = EmundusHelperUpdate::addFabrikGroup(['name' => 'Informations spécifiques'], ['repeat_group_show_first' => 1], 1, true);

            if ($group['status']) {
                EmundusHelperUpdate::joinFormGroup($form_id, [$group['id']]);

                $datas = [
                    'name'                 => 'id',
                    'group_id'             => $group['id'],
                    'plugin'               => 'internalid',
                    'label'                => 'id',
                    'show_in_list_summary' => 0,
                    'hidden'               => 1
                ];
                $result = EmundusHelperUpdate::addFabrikElement($datas);
                $tasks[] = $result['status'];

                $datas = [
                    'name'                 => 'date_time',
                    'group_id'             => $group['id'],
                    'plugin'               => 'date',
                    'label'                => 'Date de création',
                    'show_in_list_summary' => 0,
                    'hidden'               => 1
                ];
                $result = EmundusHelperUpdate::addFabrikElement($datas);
                $tasks[] = $result['status'];

                $datas = [
                    'name'                 => 'campaign_id',
                    'group_id'             => $group['id'],
                    'plugin'               => 'databasejoin',
                    'label'                => 'Campagne',
                    'show_in_list_summary' => 0,
                    'hidden'               => 1,
                    'default'              => '$input = JFactory::getApplication()->input;return $input->getInt(\'jos_emundus_setup_campaigns_more___campaign_id\', 0);',
	                'eval'                 => 1
                ];
                $params = [
                    'database_join_display_type' => 'list',
                    'join_db_name' => 'jos_emundus_setup_campaigns',
                    'join_key_column' => 'id',
                    'join_val_column' => 'label',
                    'advanced_behavior' => 1
                ];
                $result = EmundusHelperUpdate::addFabrikElement($datas, $params);
                $tasks[] = $result['status'];
            }

            $result = EmundusHelperUpdate::addFabrikList([
                'label' => 'Campagnes - Informations spécifiques',
                'db_table_name' => 'jos_emundus_setup_campaigns_more',
                'form_id' => $form_id
            ]);
            $tasks[] = $result['status'];
        }
        if (!in_array(false, $tasks)) {
            $installed = true;
        }

        return $installed;
    }
}