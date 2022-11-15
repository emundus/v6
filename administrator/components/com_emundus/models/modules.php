<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

/**
 * @package     com_emundus
 *
 * @since version 1.34.0
 */
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

            if(!in_array('jos_emundus_setup_qcm_campaign', $tables)) {
                $db->setQuery("create table jos_emundus_setup_qcm_campaign
                    (
                        id        int auto_increment
                            primary key,
                        date_time datetime null,
                        campaign  int      null,
                        label     text     null,
                        status    int(2)   null,
                        template  text     null,
                        profile   int      null
                    ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();

                $db->setQuery("create table jos_emundus_setup_qcm_campaign_1052_repeat
                    (
                        id        int auto_increment
                            primary key,
                        parent_id int null,
                        category  int null
                    ) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;")->execute();

                $db->setQuery("create index fb_parent_fk_parent_id_INDEX
                        on jos_emundus_setup_qcm_campaign_1052_repeat (parent_id);")->execute();
            }

            $buffer = file_get_contents(JPATH_SITE . '/modules/mod_emundus_qcm/install.sql');
            $queries = \JDatabaseDriver::splitSql($buffer);
            foreach ($queries as $query) {
                $db->setQuery($db->convertUtf8mb4QueryToUtf8($query));
                try {
                    $db->execute();
                } catch (Exception $e) {
                    JLog::add(basename(__FILE__) . ' | Error when install QCM setup : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
                }
            }

            EmundusHelperUpdate::insertTranslationsTag('QCM_QUESTIONS','Questions','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_QUESTIONS','Questions','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM','QCM','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM','QCM','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_SECTION','Catégorie','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_SECTION','Category','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_TIME_QUESTION','Temps (en s)','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_TIME_QUESTION','Time (in s)','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_QUESTION','Question','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_QUESTION','Question','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_ANSWERS','Réponse(s)','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_ANSWERS','Answer(s)','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_NAME','Nom','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_NAME','Name','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_FORM','Formulaire','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_FORM','Form','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_QUESTIONS_COUNT','Nombre de questions','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_QUESTIONS_COUNT','Number of questions','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_QUESTION_OR_SECTIONS','Choisir un tye','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_QUESTION_OR_SECTIONS','Choose a type','override',null,'fabrik_elements','label','en-GB');

            EmundusHelperUpdate::insertTranslationsTag('QCM_GROUP','Formulaire','override',null,'fabrik_elements','label');
            EmundusHelperUpdate::insertTranslationsTag('QCM_GROUP','Form','override',null,'fabrik_elements','label','en-GB');

            //TODO: Create Joomla menus
            $query = $db->getQuery(true);

            $datas = [
                'menutype' => 'coordinatormenu',
                'title' => 'QCM',
                'alias' => 'qcm',
                'path' => 'qcm',
                'link' => '#',
                'type' => 'url',
                'component_id' => 0,
            ];
            $header = EmundusHelperUpdate::addJoomlaMenu($datas);

            $query->select('id')
                ->from($db->quoteName('#__fabrik_lists'))
                ->where($db->quoteName('db_table_name') . ' LIKE ' . $db->quote('jos_emundus_qcm_section'));
            $db->setQuery($query);
            $section_list = $db->loadResult();
            $datas = [
                'menutype' => 'coordinatormenu',
                'title' => 'Catégories',
                'alias' => 'categories',
                'path' => 'qcm/categories',
                'link' => 'index.php?option=com_fabrik&view=list&listid=' . $section_list,
                'type' => 'component',
                'component_id' => 10041,
            ];
            EmundusHelperUpdate::addJoomlaMenu($datas,$header['id']);

            $query->clear()
                ->select('id')
                ->from($db->quoteName('#__fabrik_lists'))
                ->where($db->quoteName('db_table_name') . ' LIKE ' . $db->quote('jos_emundus_qcm_questions'));
            $db->setQuery($query);
            $questions_list = $db->loadResult();
            $datas = [
                'menutype' => 'coordinatormenu',
                'title' => 'Questions',
                'alias' => 'questions',
                'path' => 'qcm/questions',
                'link' => 'index.php?option=com_fabrik&view=list&listid=' . $questions_list,
                'type' => 'component',
                'component_id' => 10041,
            ];
            EmundusHelperUpdate::addJoomlaMenu($datas,$header['id']);

            $query->clear()
                ->select('id')
                ->from($db->quoteName('#__fabrik_lists'))
                ->where($db->quoteName('db_table_name') . ' LIKE ' . $db->quote('jos_emundus_setup_qcm_campaign'));
            $db->setQuery($query);
            $setup_campaign_list = $db->loadResult();
            $datas = [
                'menutype' => 'coordinatormenu',
                'title' => 'Configuration',
                'alias' => 'configuration',
                'path' => 'qcm/configuration',
                'link' => 'index.php?option=com_fabrik&view=list&listid=' . $setup_campaign_list,
                'type' => 'component',
                'component_id' => 10041,
            ];
            EmundusHelperUpdate::addJoomlaMenu($datas,$header['id']);
		} catch (Exception $e) {
			return false;
		}

		return true;
	}
}
