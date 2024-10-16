<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 28/01/15
 * Time: 16:28
 */
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');
require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

class EmundusControllerModules extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'modules';
			JRequest::setVar('view', $default );
		}
		parent::display();
	}

    function install() {
        $result = ['status' => false,'message' => ''];

        $jinput = JFactory::getApplication()->input;
        $module = $jinput->getString('module');

        switch($module){
            case 'qcm':
                $result['status'] = $this->installQcm();
                break;
            case 'anonym_user_sessions':
                $result = $this->installAnonymUserForms();
                break;
            case 'homepage':
                $result['status'] = $this->installHomepage();
                break;
            case 'checklist':
                $result['status'] = $this->installChecklist();
                break;
	        case 'events':
				$result['status'] = $this->installEvents();
				break;
            default:
                $result['message'] = 'Module not found';
        }

        echo json_encode((object)$result);
        exit;

    }

    function installQcm() {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusModelModules();
        $mModules->installQCM();

        $params = [
            'menutype' => 'coordinatormenu',
            'title' => 'QCM',
            'alias' => 'qcm',
            'path' => 'qcm',
            'params' => [
                'menu_image' => 'images/emundus/menus/qcm.png'
            ]
        ];
        $parent = EmundusHelperUpdate::addJoomlaMenu($params);

        //TODO : Create menu with Fabrik list just created or found
        $params = [
            'menutype' => 'coordinatormenu',
            'title' => 'Questions',
            'alias' => 'questions',
            'path' => 'questions',
            'type' => 'component',
        ];
        EmundusHelperUpdate::addJoomlaMenu($params,$parent['id']);


        return true;
    }

    function installHomepage() {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusModelModules();
        return $mModules->installHomepage();
    }

    function installChecklist() {
        require_once(JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusModelModules();
        return $mModules->installChecklist();
    }

    function installAnonymUserForms()
    {
        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/models/modules.php');
        $mModules = new EmundusModelModules();
        $installed = $mModules->installAnonymUserForms();

        if ($installed['status']) {
            require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'fabrik.php');
            $params = EmundusHelperFabrik::prepareFabrikMenuParams();

            if (!empty($installed['send_anonym_form_id'])) {
                $datas = [
                    'menutype' => 'topmenu',
                    'title' => 'Déposer un dossier',
                    'link' => 'index.php?option=com_fabrik&view=form&formid=' . $installed['send_anonym_form_id'],
                    'path' => 'deposer-un-dossier',
                    'type' => 'component',
                    'component_id' => 10041,
                    'params' => $params
                ];
                $result = EmundusHelperUpdate::addJoomlaMenu($datas, 1, 0);

                if (!$result['status']) {
                    $installed['status'] = false;
                    $installed['message'] = 'Forms have been created but Menu has not';
                }
            }

            if (!empty($installed['connect_from_token_form_id'])) {
                $datas = [
                    'menutype' => 'topmenu',
                    'title' => 'Se connecter depuis ma clé d\'authentification',
                    'link' => 'index.php?option=com_fabrik&view=form&formid=' . $installed['connect_from_token_form_id'],
                    'path' => 'connexion-avec-token',
                    'type' => 'component',
                    'component_id' => 10041,
                    'params' => $params
                ];
                $result = EmundusHelperUpdate::addJoomlaMenu($datas, 1, 0);

                if (!$result['status']) {
                    $installed['status'] = false;
                    $installed['message'] = 'Forms have been created but Menu has not';
                }
            }
        }

        return $installed;
    }

	function installEvents()
	{
		require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

		EmundusHelperUpdate::installExtension('MOD_EMUNDUS_EVENTS', 'mod_emundus_events', '{"name":"MOD_EMUNDUS_EVENTS","type":"module","creationDate":"October 2024","author":"eMundus","copyright":"Copyright (C) 2024 eMundus. All rights reserved.","authorEmail":"dev@emundus.fr","authorUrl":"www.emundus.fr","version":"1.39.8","description":"MOD_EMUNDUS_EVENTS_DESCRIPTION","group":"","filename":"mod_emundus_events"}', 'module', 1, '', '{"table":"data_events"}');

		$columns      = [
			[
				'name'   => 'date_time',
				'type'   => 'DATETIME'
			],
			[
				'name' => 'title',
				'type' => 'VARCHAR',
				'length' => 255,
				'null' => 0,
			],
			[
				'name'    => 'start_date',
				'type'    => 'DATETIME',
				'null'    => 1,
			],
			[
				'name'    => 'end_date',
				'type'    => 'DATETIME',
				'null'    => 1,
			],
			[
				'name'    => 'description',
				'type'    => 'TEXT',
				'null'    => 1,
			],
			[
				'name'    => 'link',
				'type'    => 'VARCHAR',
				'length'  => 255,
				'null'    => 1,
			],
			[
				'name'    => 'published',
				'type'    => 'TINYINT',
				'length'  => 1,
				'null'    => 0,
				'default' => 1
			]
		];

		EmundusHelperUpdate::createTable('data_events', $columns, [], 'Event for websites')['status'];

		$datas        = [
			'label'              => 'FORM_DATA_EVENTS',
			'form_template'      => 'emundus',
			'view_only_template' => 'emundus',
		];
		$form_created = EmundusHelperUpdate::addFabrikForm($datas);
		if ($form_created['status'])
		{
			$form_id = $form_created['id'];

			$datas  = [
				'label'         => 'LIST_DATA_EVENTS',
				'form_id'       => $form_id,
				'db_table_name' => 'data_events',
				'access'        => 7,
				'template'      => 'emundus'
			];
			$params = [
				'hidecheckbox'            => 1,
				'alter_existing_db_cols'  => 'addonly',
				'group_by_access'         => 10,
				'allow_drop'              => 10,
				'menu_access_only'        => 1,
			];
			$list   = EmundusHelperUpdate::addFabrikList($datas, $params);

			if ($list['status'])
			{
				$datas = [
					'name' => 'FORM_DATA_EVENTS'
				];
				$params = [
					'repeat_group_show_first' => 1,
				];
				$group = EmundusHelperUpdate::addFabrikGroup($datas, $params, 1, true);

				if ($group['status'])
				{
					$group_id = $group['id'];

					EmundusHelperUpdate::joinFormGroup($form_id, [$group_id]);

					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_TITLE', 'Nom');
					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_TITLE', 'Name', 'override', null, null, null, 'en-GB');

					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_START_DATE', 'Date de début');
					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_START_DATE', 'Start date', 'override', null, null, null, 'en-GB');

					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_END_DATE', 'Date de fin');
					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_END_DATE', 'End date', 'override', null, null, null, 'en-GB');

					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_DESC', 'Description');
					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_DESC', 'Description', 'override', null, null, null, 'en-GB');

					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_LINK', 'Lien');
					EmundusHelperUpdate::insertTranslationsTag('ELEMENT_DATA_EVENTS_LINK', 'Link', 'override', null, null, null, 'en-GB');

					$datas = [
						'name'                 => 'id',
						'group_id'             => $group_id,
						'plugin'               => 'internalid',
						'label'                => 'id',
						'show_in_list_summary' => 0
					];
					EmundusHelperUpdate::addFabrikElement($datas);

					$datas = [
						'name'                 => 'title',
						'group_id'             => $group_id,
						'plugin'               => 'field',
						'label'                => 'ELEMENT_DATA_EVENTS_TITLE',
						'show_in_list_summary' => 1
					];
					EmundusHelperUpdate::addFabrikElement($datas);

					$datas = [
						'name'                 => 'start_date',
						'group_id'             => $group_id,
						'plugin'               => 'date',
						'label'                => 'ELEMENT_DATA_EVENTS_START_DATE',
						'show_in_list_summary' => 1
					];

					$params = [
						'date_store_as_local' => 1,
						'date_table_format'   => 'd\/m\/Y H:i',
					];
					EmundusHelperUpdate::addFabrikElement($datas,$params);

					$datas = [
						'name'                 => 'end_date',
						'group_id'             => $group_id,
						'plugin'               => 'date',
						'label'                => 'ELEMENT_DATA_EVENTS_END_DATE',
						'show_in_list_summary' => 1
					];
					EmundusHelperUpdate::addFabrikElement($datas,$params);

					$datas  = [
						'name'                 => 'description',
						'group_id'             => $group_id,
						'plugin'               => 'textarea',
						'label'                => 'ELEMENT_DATA_EVENTS_DESC',
						'show_in_list_summary' => 1
					];
					EmundusHelperUpdate::addFabrikElement($datas, $params);

					$datas = [
						'name'                 => 'link',
						'group_id'             => $group_id,
						'plugin'               => 'field',
						'label'                => 'ELEMENT_DATA_EVENTS_LINK',
						'show_in_list_summary' => 1
					];
					EmundusHelperUpdate::addFabrikElement($datas);

					$datas = [
						'name'                 => 'published',
						'group_id'             => $group_id,
						'plugin'               => 'yesno',
						'label'                => 'PUBLISHED',
						'show_in_list_summary' => 1
					];
					EmundusHelperUpdate::addFabrikElement($datas);
				}
			}
		}

		// Insert new translations in overrides files
		EmundusHelperUpdate::languageBaseToFile();

		return true;
	}
}
