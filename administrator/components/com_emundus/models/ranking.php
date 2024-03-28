<?php
/**
 * eMundus Campaign model
 *
 * @package        Joomla
 * @subpackage    eMundus
 * @link        http://www.emundus.fr
 * @copyright    Copyright (C) 2008 - 2013 Décision Publique. All rights reserved.
 * @license        GNU/GPL
 * @author        Decision Publique - Benjamin Rivalland
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class EmundusAdministrationModelRanking extends JModelList
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Install tables and add sysadmin default menu
     * @return bool
     */
    public function install(): bool
    {
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
                'name' => 'parent_id',
                'type' => 'INT',
                'null' => 0,
            ],
            [
                'name' => 'label',
                'type' => 'VARCHAR',
                'length' => 255,
                'null' => 0,
            ],
            [
                'name' => 'published',
                'type' => 'TINYINT',
                'null' => 1,
                'default' => 1,
            ],
            [
                'name' => 'status',
                'type' => 'INT',
                'null' => 1,
            ],
            [
                'name' => 'profile_id',
                'type' => 'INT',
                'null' => 1,
            ],
        ];
        $foreign_keys = [
            [
                'name'           => 'jos_emundus_classement_hierarchy_profiles_id_fk',
                'from_column'    => 'profile_id',
                'ref_table'      => 'jos_emundus_setup_profiles',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
            [
                'name'           => 'jos_emundus_classement_hierarchy_status_fk',
                'from_column'    => 'status',
                'ref_table'      => 'jos_emundus_setup_status',
                'ref_column'     => 'step',
                'update_cascade' => true,
                'delete_cascade' => true,
            ]
        ];

        $response = EmundusHelperUpdate::createTable('jos_emundus_ranking_hierarchy', $columns, $foreign_keys);
        $tasks[] = $response['status'];


        $columns = [
            [
                'name' => 'hierarchy_id',
                'type' => 'INT',
                'null' => 0,
            ],
            [
                'name' => 'visible_hierarchy_id',
                'type' => 'INT',
                'null' => 0,
            ]
        ];
        $foreign_keys = [
            [
                'name'           => 'jos_emundus_ranking_hierarchy_view_hierarchy_id_fk',
                'from_column'    => 'hierarchy_id',
                'ref_table'      => 'jos_emundus_ranking_hierarchy',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
            [
                'name'           => 'jos_emundus_ranking_hierarchy_view_visible_hierarchy_id_fk',
                'from_column'    => 'visible_hierarchy_id',
                'ref_table'      => 'jos_emundus_ranking_hierarchy',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
        ];

        $response = EmundusHelperUpdate::createTable('jos_emundus_ranking_hierarchy_view', $columns, $foreign_keys);
        $tasks[] = $response['status'];

        $columns = [
            [
                'name' => 'ccid',
                'type' => 'INT',
                'null' => 0,
            ],
            [
                'name' => 'user_id',
                'type' => 'INT',
                'null' => 0,
            ],
            [
                'name' => 'hierarchy_id',
                'type' => 'INT',
                'null' => 0,
            ],
            [
                'name' => 'locked',
                'type' => 'TINYINT',
                'null' => 1,
                'default' => 0,
            ],
            [
                'name' => $db->quoteName('rank'),
                'type' => 'int',
                'null' => 0,
                'default' => -1,
            ],
        ];
        $foreign_keys = [
            [
                'name'           => 'jos_emundus_classement_ccid__fk',
                'from_column'    => 'ccid',
                'ref_table'      => 'jos_emundus_campaign_candidature',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
            [
                'name'           => 'jos_emundus_ranking_jos_emundus_classement_hierarchy_id_fk',
                'from_column'    => 'hierarchy_id',
                'ref_table'      => 'jos_emundus_ranking_hierarchy',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
            [
                'name'           => 'jos_emundus_ranking_jos_users_id_fk',
                'from_column'    => 'user_id',
                'ref_table'      => 'jos_users',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
        ];
        $unique_keys = [
            [
                'name' => 'jos_emundus_classement_unicity_pk',
                'columns' => ['ccid', 'user_id', 'hierarchy_id'],
            ]
        ];

        $response = EmundusHelperUpdate::createTable('jos_emundus_ranking', $columns, $foreign_keys, 'Table de classement', $unique_keys);
        $tasks[] = $response['status'];


        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('extension_id')
            ->from('#__extensions')
            ->where('type = ' . $db->quote('component'))
            ->where('element = ' . $db->quote('com_emundus'))
            ->where('enabled = 1')
            ->where('name = ' . $db->quote('com_emundus'));

        $db->setQuery($query);
        $component_id = $db->loadResult();

        $datas = [
            'menutype'     => 'adminmenu',
            'title'        => 'Classement',
            'alias'        => 'classement',
            'path'         => 'classement',
            'link'         => 'index.php?option=com_emundus&view=ranking',
            'type'         => 'component',
            'component_id' => $component_id,
            'params'       => [
                'show_title' => 0,
                'menu_image_css' => 'format_list_numbered',
            ]
        ];
        $response = EmundusHelperUpdate::addJoomlaMenu($datas);
        $tasks[] = $response['status'];

        if (!in_array(false, $tasks)) {
            $installed = true;
        }

        return $installed;
    }
}