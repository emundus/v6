<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 28/01/15
 * Time: 16:28
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

class EmundusModelModules extends JModelList {

	public function installQCM() {
		try {
			$db = JFactory::getDbo();
            $tables = $db->setQuery('SHOW TABLES')->loadColumn();

            if(!in_array('jos_emundus_setup_qcm',$tables)) {
                $db->setQuery("create table jos_emundus_setup_qcm
                    (
                        id int auto_increment
                            primary key,
                        date_time datetime null,
                        name varchar(255) null,
                        form_id int(4) null,
                        count int null,
                        group_id int(4) null,
                        questionid int null,
                        sectionid int null,
                        type_choices varchar(255) null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
                $db->setQuery("create index jos_emundus_setup_qcm_jos_fabrik_forms_id_fk on jos_emundus_setup_qcm (form_id);")->execute();
                $db->setQuery("create index jos_emundus_setup_qcm_jos_fabrik_groups_id_fk on jos_emundus_setup_qcm (group_id);")->execute();
                $db->setQuery("ALTER TABLE `jos_emundus_setup_qcm` 
                    ADD CONSTRAINT jos_emundus_setup_qcm_ibfk_1 FOREIGN KEY (`form_id`) REFERENCES `jos_fabrik_forms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT jos_emundus_setup_qcm_ibfk_2 FOREIGN KEY (`group_id`) REFERENCES `jos_fabrik_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
            }

            if(!in_array('jos_emundus_qcm_section',$tables)) {
                $db->setQuery("create table jos_emundus_qcm_section
                    (
                        id int auto_increment
                            primary key,
                        date_time datetime null,
                        name varchar(255) null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
            }

            if(!in_array('jos_emundus_qcm_questions',$tables)) {
                $db->setQuery("create table jos_emundus_qcm_questions
                    (
                        id int auto_increment
                            primary key,
                        date_time datetime null,
                        section int null,
                        code varchar(255) collate utf8mb4_unicode_ci null,
                        question mediumtext collate utf8mb4_unicode_ci null,
                        proposals varchar(255) collate utf8mb4_unicode_ci null,
                        time int null,
                        answers varchar(255) collate utf8mb4_unicode_ci null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
                $db->setQuery("create index jos_emundus_qcm_questions_jos_emundus_qcm_section_id_fk
                    on jos_emundus_qcm_questions (section);")->execute();
                $db->setQuery("ALTER TABLE `jos_emundus_qcm_questions`
                    ADD CONSTRAINT jos_emundus_qcm_questions_ibfk_1 FOREIGN KEY (`section`) REFERENCES `jos_emundus_qcm_section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
            }

            if(!in_array('jos_emundus_qcm_questions_765_repeat',$tables)) {
                $db->setQuery("create table jos_emundus_qcm_questions_765_repeat
                    (
                        id int auto_increment
                            primary key,
                        parent_id int null,
                        proposals varchar(255) null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
                $db->setQuery("create index fb_parent_fk_parent_id_INDEX
                    on jos_emundus_qcm_questions_765_repeat (parent_id);")->execute();
                $db->setQuery("ALTER TABLE `jos_emundus_qcm_questions_765_repeat`
                    ADD CONSTRAINT jos_emundus_qcm_questions_765_repeat_ibfk_1 FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_qcm_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
            }

            if(!in_array('jos_emundus_setup_qcm_repeat_questionid',$tables)) {
                $db->setQuery("create table jos_emundus_setup_qcm_repeat_questionid
                    (
                        id int auto_increment
                            primary key,
                        parent_id int null,
                        questionid int null,
                        params text null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
                $db->setQuery("create index fb_parent_fk_parent_id_INDEX
                    on jos_emundus_setup_qcm_repeat_questionid (parent_id);")->execute();
                $db->setQuery("create index fb_repeat_el_questionid_INDEX
                    on jos_emundus_setup_qcm_repeat_questionid (questionid);")->execute();
                $db->setQuery("ALTER TABLE `jos_emundus_setup_qcm_repeat_questionid`
                    ADD CONSTRAINT jos_emundus_setup_qcm_repeat_questionid_ibfk_1 FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_qcm` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT jos_emundus_setup_qcm_repeat_questionid_ibfk_2 FOREIGN KEY (`questionid`) REFERENCES `jos_emundus_qcm_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
            }

            if(!in_array('jos_emundus_setup_qcm_repeat_sectionid',$tables)) {
                $db->setQuery("create table jos_emundus_setup_qcm_repeat_sectionid
                    (
                        id int auto_increment
                            primary key,
                        parent_id int null,
                        sectionid int null,
                        params text null
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
                $db->setQuery("create index fb_parent_fk_parent_id_INDEX
                    on jos_emundus_setup_qcm_repeat_sectionid (parent_id);")->execute();
                $db->setQuery("create index fb_repeat_el_sectionid_INDEX
                    on jos_emundus_setup_qcm_repeat_sectionid (sectionid);")->execute();
                $db->setQuery("ALTER TABLE `jos_emundus_setup_qcm_repeat_sectionid`
                    ADD CONSTRAINT jos_emundus_setup_qcm_repeat_sectionid_ibfk_1 FOREIGN KEY (`parent_id`) REFERENCES `jos_emundus_setup_qcm` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                    ADD CONSTRAINT jos_emundus_setup_qcm_repeat_sectionid_ibfk_2 FOREIGN KEY (`sectionid`) REFERENCES `jos_emundus_qcm_section` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;")->execute();
            }

            if(!in_array('jos_emundus_qcm_applicants',$tables)) {
                $db->setQuery("create table jos_emundus_qcm_applicants
                    (
                        id int auto_increment
                            primary key,
                        date_time datetime default current_timestamp null,
                        fnum varchar(255) null,
                        user int null,
                        questions varchar(255) null,
                        qcmid int null,
                        step int null,
                        pending varchar(255) null,
                        constraint fnum unique (fnum)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();
                $db->setQuery("create index jos_emundus_qcm_applicants_jos_emundus_setup_qcm_id_fk
                    on jos_emundus_qcm_applicants (qcmid);")->execute();
                $db->setQuery("create index jos_emundus_qcm_applicants_jos_emundus_users_id_fk
                    on jos_emundus_qcm_applicants (user);")->execute();
                $db->setQuery("create index jos_emundus_qcm_applicants_jos_emundus_cc_id_fk
                    on jos_emundus_qcm_applicants (fnum);")->execute();
            }

            // TODO : Create Fabrik lists and elements
            $datas = [
                'label' => 'QCM - Questions',
            ];
            $form_questions = EmundusHelperUpdate::addFabrikForm($datas);

            if($form_questions['status']) {
                $datas = [
                    'label' => 'QCM - Questions',
                    'introduction' => '',
                    'form_id' => $form_questions['id'],
                    'db_table_name' => 'jos_emundus_qcm_questions',
                    'access' => 7,
                ];
                $list_questions = EmundusHelperUpdate::addFabrikList($datas);

                $datas = [
                    'name' => 'QCM - Questions',
                ];
                $group_questions = EmundusHelperUpdate::addFabrikGroup($datas);
                if($group_questions['status']){
                    EmundusHelperUpdate::joinFormGroup($form_questions['id'],[$group_questions['id']]);
                }

                $repeat_params = [
                    'repeat_group_button' => 1
                ];
                $datas = [
                    'name' => 'QCM - Proposals',
                    'is_join' => '1'
                ];
                $group_proposals = EmundusHelperUpdate::addFabrikGroup($datas,$repeat_params);
                if($group_proposals['status']){
                    EmundusHelperUpdate::joinFormGroup($form_questions['id'],[$group_proposals['id']]);

                    $datas = [
                        'list_id' => $list_questions['id'],
                        'join_from_table' => 'jos_emundus_qcm_questions',
                        'table_join' => 'jos_emundus_qcm_questions_765_repeat',
                        'table_key' => 'id',
                        'table_join_key' => 'parent_id',
                        'group_id' => $group_proposals['id'],
                    ];
                    $join_params = [
                        'type' => 'group',
                        'pk' => '`jos_emundus_qcm_questions_765_repeat`.`id`',
                    ];
                    EmundusHelperUpdate::addFabrikJoin($datas,$join_params);
                }

                if($group_questions['status']) {
                    $datas = [
                        'name' => 'id',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'internalid',
                        'label' => 'id',
                        'width' => '3',
                        'height' => '0',
                        'hidden' => '1',
                        'link_to_detail' => '1',
                        'primary_key' => '1',
                        'auto_increment' => '1',
                    ];
                    $elt_params = [
                        'edit_access' => 1,
                        'view_access' => 1,
                        'list_view_access' => 1,
                        'filter_access' => 1,
                        'sum_access' => 1,
                        'avg_access' => 1,
                        'median_access' => 1,
                        'count_access' => 1,
                        'custom_calc_access' => 1,
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas,$elt_params);

                    $datas = [
                        'name' => 'date_time',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'date',
                        'label' => 'date_time',
                        'width' => '0',
                        'height' => '0',
                        'hidden' => '1',
                        'link_to_detail' => '1'
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas);

                    $datas = [
                        'name' => 'section',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'databasejoin',
                        'label' => 'QCM_SECTION',
                    ];
                    $elt_params = [
                        'database_join_display_type' => 'dropdown',
                        'join_db_name' => 'jos_emundus_qcm_section',
                        'join_key_column' => 'id',
                        'join_val_column' => 'name',
                    ];
                    $elt_section = EmundusHelperUpdate::addFabrikElement($datas,$elt_params);

                    if($elt_section['status']) {
                        $datas = [
                            'element_id' => $elt_section['id'],
                            'join_from_table' => '',
                            'table_join' => 'jos_emundus_qcm_section',
                            'table_key' => 'section',
                            'table_join_key' => 'id',
                            'group_id' => $group_questions['id'],
                        ];
                        $join_params = [
                            'join-label' => 'name',
                            'type' => 'element',
                            'pk' => '`jos_emundus_qcm_section`.`id`',
                        ];
                        EmundusHelperUpdate::addFabrikJoin($datas, $join_params);
                    }

                    $datas = [
                        'name' => 'code',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'field',
                        'label' => 'CODE',
                        'hidden' => 1,
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas);

                    $datas = [
                        'name' => 'question',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'textarea',
                        'label' => 'QCM_QUESTION',
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas);

                    $datas = [
                        'name' => 'time',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'field',
                        'label' => 'QCM_TIME_QUESTION',
                    ];
                    $elt_params = [
                        'password' => 6,
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas,$elt_params);

                    $datas = [
                        'name' => 'answers',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'field',
                        'label' => 'QCM_ANSWERS',
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas);
                }
            }

            $datas = [
                'label' => 'QCM - Section',
            ];
            $form_sections = EmundusHelperUpdate::addFabrikForm($datas);

            if($form_sections['status']) {
                $datas = [
                    'label' => 'QCM - Section',
                    'introduction' => '',
                    'form_id' => $form_sections['id'],
                    'db_table_name' => 'jos_emundus_qcm_section',
                    'access' => 7,
                ];
                EmundusHelperUpdate::addFabrikList($datas);

                $datas = [
                    'name' => 'QCM - Section',
                ];
                $group_sections = EmundusHelperUpdate::addFabrikGroup($datas);
                if($group_sections['status']){
                    EmundusHelperUpdate::joinFormGroup($form_sections['id'],[$group_sections['id']]);

                    $datas = [
                        'name' => 'id',
                        'group_id' => $group_sections['id'],
                        'plugin' => 'internalid',
                        'label' => 'id',
                        'width' => '3',
                        'height' => '0',
                        'hidden' => '1',
                        'link_to_detail' => '1',
                        'primary_key' => '1',
                        'auto_increment' => '1',
                    ];
                    $elt_params = [
                        'edit_access' => 1,
                        'view_access' => 1,
                        'list_view_access' => 1,
                        'filter_access' => 1,
                        'sum_access' => 1,
                        'avg_access' => 1,
                        'median_access' => 1,
                        'count_access' => 1,
                        'custom_calc_access' => 1,
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas,$elt_params);

                    $datas = [
                        'name' => 'date_time',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'date',
                        'label' => 'date_time',
                        'width' => '0',
                        'height' => '0',
                        'hidden' => '1',
                        'link_to_detail' => '1'
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas);

                    $datas = [
                        'name' => 'name',
                        'group_id' => $group_questions['id'],
                        'plugin' => 'field',
                        'label' => 'QCM_NAME',
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas);
                }
            }

            $datas = [
                'label' => 'QCM - Setup',
            ];
            $form_setup = EmundusHelperUpdate::addFabrikForm($datas);

            if($form_setup['status']) {
                $datas = [
                    'label' => 'QCM - Setup',
                    'introduction' => '',
                    'form_id' => $form_setup['id'],
                    'db_table_name' => 'jos_emundus_qcm_section',
                    'access' => 7,
                ];
                EmundusHelperUpdate::addFabrikList($datas);

                $datas = [
                    'name' => 'QCM - Setup',
                ];
                $group_setup = EmundusHelperUpdate::addFabrikGroup($datas);
                if ($group_setup['status']) {
                    EmundusHelperUpdate::joinFormGroup($form_setup['id'], [$group_setup['id']]);

                    $datas = [
                        'name' => 'id',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'internalid',
                        'label' => 'id',
                        'width' => '3',
                        'height' => '0',
                        'hidden' => '1',
                        'link_to_detail' => '1',
                        'primary_key' => '1',
                        'auto_increment' => '1',
                    ];
                    $elt_params = [
                        'edit_access' => 1,
                        'view_access' => 1,
                        'list_view_access' => 1,
                        'filter_access' => 1,
                        'sum_access' => 1,
                        'avg_access' => 1,
                        'median_access' => 1,
                        'count_access' => 1,
                        'custom_calc_access' => 1,
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas, $elt_params);

                    $datas = [
                        'name' => 'date_time',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'date',
                        'label' => 'date_time',
                        'width' => '0',
                        'height' => '0',
                        'hidden' => '1',
                        'link_to_detail' => '1'
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas);

                    $datas = [
                        'name' => 'name',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'field',
                        'label' => 'QCM_NAME',
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas);

                    $datas = [
                        'name' => 'form_id',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'databasejoin',
                        'label' => 'QCM_FORM',
                    ];
                    $elt_params = [
                        'database_join_display_type' => 'dropdown',
                        'join_db_name' => 'jos_fabrik_forms',
                        'join_key_column' => 'id',
                        'join_val_column' => 'label',
                    ];
                    $elt_form_id = EmundusHelperUpdate::addFabrikElement($datas, $elt_params);

                    if ($elt_form_id['status']) {
                        $datas = [
                            'element_id' => $elt_form_id['id'],
                            'join_from_table' => '',
                            'table_join' => 'jos_fabrik_forms',
                            'table_key' => 'form_id',
                            'table_join_key' => 'id',
                            'group_id' => $group_setup['id'],
                        ];
                        $join_params = [
                            'join-label' => 'name',
                            'type' => 'element',
                            'pk' => '`jos_fabrik_forms`.`id`',
                        ];
                        EmundusHelperUpdate::addFabrikJoin($datas, $join_params);
                    }

                    $datas = [
                        'name' => 'questionid',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'databasejoin',
                        'label' => 'QCM_QUESTIONS',
                    ];
                    $elt_params = [
                        'database_join_display_type' => 'checkbox',
                        'join_db_name' => 'jos_emundus_qcm_questions',
                        'join_key_column' => 'id',
                        'join_val_column' => 'question',
                    ];
                    $elt_question_id = EmundusHelperUpdate::addFabrikElement($datas, $elt_params);

                    if ($elt_question_id['status']) {
                        $datas = [
                            'element_id' => $elt_question_id['id'],
                            'join_from_table' => 'jos_emundus_setup_qcm',
                            'table_join' => 'jos_emundus_setup_qcm_repeat_questionid',
                            'table_key' => 'questionid',
                            'table_join_key' => 'parent_id',
                            'group_id' => $group_setup['id'],
                        ];
                        $join_params = [
                            'type' => 'repeatElement',
                            'pk' => '`jos_emundus_setup_qcm_repeat_questionid`.`id`',
                        ];
                        EmundusHelperUpdate::addFabrikJoin($datas, $join_params);
                    }

                    $datas = [
                        'name' => 'sectionid',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'databasejoin',
                        'label' => 'QCM_SECTION',
                    ];
                    $elt_params = [
                        'database_join_display_type' => 'checkbox',
                        'join_db_name' => 'jos_emundus_qcm_section',
                        'join_key_column' => 'id',
                        'join_val_column' => 'name',
                    ];
                    $elt_section_id = EmundusHelperUpdate::addFabrikElement($datas, $elt_params);

                    if ($elt_section_id['status']) {
                        $datas = [
                            'element_id' => $elt_section_id['id'],
                            'join_from_table' => 'jos_emundus_setup_qcm',
                            'table_join' => 'jos_emundus_setup_qcm_repeat_sectionid',
                            'table_key' => 'sectionid',
                            'table_join_key' => 'parent_id',
                            'group_id' => $group_setup['id'],
                        ];
                        $join_params = [
                            'type' => 'repeatElement',
                            'pk' => '`jos_emundus_setup_qcm_repeat_sectionid`.`id`',
                        ];
                        EmundusHelperUpdate::addFabrikJoin($datas, $join_params);
                    }

                    $datas = [
                        'name' => 'type_choices',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'radiobutton',
                        'label' => 'QCM_QUESTION_OR_SECTIONS',
                    ];
                    $sub_options = [
                        'sub_values' => ["1", "2"],
                        'sub_labels' => ["QCM_QUESTIONS", "QCM_SECTION"],
                    ];
                    $elt_params = [
                        'sub_options' => json_encode($sub_options),
                        'options_per_row' => 2
                    ];
                    $elt_type_choices = EmundusHelperUpdate::addFabrikElement($datas, $elt_params);
                    if ($elt_type_choices['status']) {
                        $datas = [
                            'element_id' => $elt_type_choices,
                            'code' => 'var value = this.get(&#039;value&#039;);
                                       var fab = this.form.elements;
                                        
                                       var questions = fab.get(&#039;jos_emundus_setup_qcm___questionid&#039;);
                                       var sections = fab.get(&#039;jos_emundus_setup_qcm___sectionid&#039;);
                                        
                                       if(value == 1){
                                         sections.hide();
                                         questions.show();
                                       } else {
                                         questions.hide();
                                         sections.show();
                                       }',
                        ];

                        EmundusHelperUpdate::addJsAction($datas);
                    }

                    $datas = [
                        'name' => 'count',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'field',
                        'label' => 'QCM_QUESTIONS_COUNT',
                    ];
                    $elt_params = [
                        'password' => 6,
                    ];
                    EmundusHelperUpdate::addFabrikElement($datas, $elt_params);

                    $datas = [
                        'name' => 'group_id',
                        'group_id' => $group_setup['id'],
                        'plugin' => 'databasejoin',
                        'label' => 'QCM_GROUP',
                    ];
                    $elt_params = [
                        'database_join_display_type' => 'dropdown',
                        'join_db_name' => 'jos_fabrik_groups',
                        'join_key_column' => 'id',
                        'join_val_column' => 'label',
                    ];
                    $elt_group_id = EmundusHelperUpdate::addFabrikElement($datas, $elt_params);

                    if ($elt_question_id['status']) {
                        $datas = [
                            'element_id' => $elt_question_id['id'],
                            'join_from_table' => 'jos_emundus_setup_qcm',
                            'table_join' => 'jos_fabrik_groups',
                            'table_key' => 'group_id',
                            'table_join_key' => 'id',
                            'group_id' => $group_setup['id'],
                        ];
                        $join_params = [
                            'join-label' => 'label',
                            'type' => 'element',
                            'pk' => '`jos_fabrik_groups`.`id`',
                        ];
                        EmundusHelperUpdate::addFabrikJoin($datas, $join_params);
                    }
                }
            }


            $plugin_qcm_setup = [
                'plugin_state' => ['1'],
                'only_process_curl' => ['onBeforeStore'],
                'form_php_file' => ['emundus-qcm-setup.php'],
                'form_php_require_once' => ['0'],
                'plugins' => ['php'],
                'plugin_locations' => ['both'],
                'plugin_events' => ['both'],
                'plugin_description' => ['Setup QCM'],
            ];
            $datas = [
                'label' => 'QCM - Applicants',
            ];
            $form_applicants = EmundusHelperUpdate::addFabrikForm($datas,$plugin_qcm_setup);

            if($form_applicants['status']) {
                $datas = [
                    'label' => 'QCM - Applicants',
                    'introduction' => '',
                    'form_id' => $form_applicants['id'],
                    'db_table_name' => 'jos_emundus_qcm_applicants',
                    'access' => 7,
                ];
                EmundusHelperUpdate::addFabrikList($datas);

                $datas = [
                    'name' => 'QCM - Applicants',
                ];
                $group_applicants = EmundusHelperUpdate::addFabrikGroup($datas);
                if($group_applicants['status']){
                    EmundusHelperUpdate::joinFormGroup($form_applicants['id'],[$group_applicants['id']]);
                }
            }
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
}
