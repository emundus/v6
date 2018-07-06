<?php
/**
 * @package         Joomla
 * @subpackage      eMundus
 * @link            http://www.emundus.fr
 * @copyright       Copyright (C) 2015 eMundus. All rights reserved.
 * @license         GNU/GPL
 * @author          James Dean
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class EmundusModelStats extends JModelLegacy {

    public function viewExist($view) {
        
        $db = JFactory::getDbo();
        $dbName = JFactory::getConfig()->get('db');
        $query = $db->getQuery(true);
        $query = 'SELECT IF( EXISTS(
                    SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = "'.$dbName.'" AND TABLE_TYPE ="VIEW" AND TABLE_NAME = "'.$view.'"
                    ), 1, 0)';
        $db->setQuery($query);
        try {
            return $db->loadResult();
        } catch(Exception $e) {
            JLog::add('Error getting stats on account types at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function addView($view) {
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        switch($view) {
            case 'jos_emundus_stats_nombre_candidature_offre':
                $query =  "CREATE VIEW jos_emundus_stats_nombre_candidature_offre AS
                                    SELECT uuid() AS `id`,
                                            count(`el`.`id`) AS `nombre`,
                                            `el`.`fnum_to` AS `num_offre`,
                                            date_format(`el`.`timestamp`,'%Y%m%d') AS `_date`,
                                            date_format(`el`.`timestamp`,'%Y-%m-%d') AS `_day`,
                                            date_format(`el`.`timestamp`,'%u') AS `_week`,
                                            date_format(`el`.`timestamp`,'%b') AS `_month`,
                                            date_format(`el`.`timestamp`,'%Y') AS `_year`,
                                        (SELECT `jos_emundus_projet`.`titre`
                                        FROM `jos_emundus_projet`
                                        WHERE (convert(`jos_emundus_projet`.`fnum`
                                        USING utf8) LIKE `el`.`fnum_to`) limit 1) AS `titre`
                                    FROM `jos_emundus_logs` `el`
                                    WHERE (`el`.`action_id` = 32)
                                    GROUP BY  `el`.`fnum_to`,date_format(`el`.`timestamp`,'%Y%m%d')";
            break;

            case 'jos_emundus_stats_nombre_comptes':
                $query =  "CREATE VIEW jos_emundus_stats_nombre_comptes AS
                                SELECT uuid() AS `id`,
                                        count(`eu`.`profile`) AS `nombre`,
                                        date_format(`eu`.`registerDate`,'%Y%m%d') AS `_date`,
                                        date_format(`eu`.`registerDate`,'%Y-%m-%d') AS `_day`,
                                        date_format(`eu`.`registerDate`,'%u') AS `_week`,
                                        date_format(`eu`.`registerDate`,'%b') AS `_month`,
                                        date_format(`eu`.`registerDate`,'%Y') AS `_year`,
                                        `sp`.`id` AS `profile_id`,`sp`.`label` AS `profile_label`
                                FROM (`jos_emundus_users` `eu`
                                LEFT JOIN `jos_emundus_setup_profiles` `sp` on((`sp`.`id` = `eu`.`profile`)))
                                WHERE `eu`.`profile` IN 
                                    (SELECT `jos_emundus_setup_profiles`.`id`
                                    FROM `jos_emundus_setup_profiles`
                                    WHERE (`jos_emundus_setup_profiles`.`published` = 1))
                                GROUP BY  `eu`.`profile`,date_format(`eu`.`registerDate`,'%Y%m%d')";
            break;

            case 'jos_emundus_stats_nombre_connexions':
                $query =   "CREATE VIEW jos_emundus_stats_nombre_connexions AS
                                    SELECT uuid() AS `id`,
                                            count(`el`.`id`) AS `nombre_connexions`,
                                            date_format(`el`.`timestamp`,'%Y%m%d') AS `_date`,
                                            date_format(`el`.`timestamp`,'%Y-%m-%d') AS `_day`,
                                            date_format(`el`.`timestamp`,'%u') AS `_week`,
                                            date_format(`el`.`timestamp`,'%b') AS `_month`,
                                            date_format(`el`.`timestamp`,'%Y') AS `_year`
                                    FROM `jos_emundus_logs` `el`
                                    WHERE (`el`.`action_id` = -(2))
                                    GROUP BY  date_format(`el`.`timestamp`,'%Y%m%d')";
            break;

            case 'jos_emundus_stats_nombre_consult_offre':
                $query =    "CREATE VIEW jos_emundus_stats_nombre_consult_offre AS
                                        SELECT uuid() AS `id`,
                                                count(`el`.`id`) AS `nombre`,
                                                `el`.`fnum_to` AS `num_offre`,
                                                date_format(`el`.`timestamp`,'%Y%m%d') AS `_date`,
                                                date_format(`el`.`timestamp`,'%Y-%m-%d') AS `_day`,
                                                date_format(`el`.`timestamp`,'%u') AS `_week`,
                                                date_format(`el`.`timestamp`,'%b') AS `_month`,
                                                date_format(`el`.`timestamp`,'%Y') AS `_year`,
                                            (SELECT `jos_emundus_projet`.`titre`
                                            FROM `jos_emundus_projet`
                                            WHERE (convert(`jos_emundus_projet`.`fnum`USING utf8) LIKE `el`.`fnum_to`) limit 1) AS `titre`
                                        FROM `jos_emundus_logs` `el`
                                        WHERE (`el`.`action_id` = 33)
                                        GROUP BY  `el`.`fnum_to`,date_format(`el`.`timestamp`,'%Y%m%d')";
            break;

            case 'jos_emundus_stats_nombre_relations_etablies':
                $query =   "CREATE VIEW jos_emundus_stats_nombre_relations_etablies AS
                                    SELECT uuid() AS `id`,
                                            count(`er`.`id`) AS `nombre_rel_etablies`,
                                            date_format(`er`.`timestamp`,'%Y%m%d') AS `_date`,
                                            date_format(`er`.`timestamp`,'%Y-%m-%d') AS `_day`,
                                            date_format(`er`.`timestamp`,'%u') AS `_week`,
                                            date_format(`er`.`timestamp`,'%b') AS `_month`,
                                            date_format(`er`.`timestamp`,'%Y') AS `_year`
                                    FROM `jos_emundus_relations` `er`
                                    GROUP BY  date_format(`er`.`timestamp`,'%Y%m%d')";
            break;

            case 'jos_emundus_stats_nationality':
                $query =    "CREATE VIEW jos_emundus_stats_nationality AS
                                        SELECT `ecc`.`id` AS `id`,
                                                `esc`.`year` AS `schoolyear`,
                                                count(distinct `ecc`.`applicant_id`) AS `nb`,
                                                `epd`.`nationality` AS `nationality`,
                                                `esc`.`label` AS `campaign`,
                                                `esc`.`training` AS `course`
                                        FROM (((`jos_emundus_declaration` `ed`
                                        JOIN `jos_emundus_campaign_candidature` `ecc` on((`ed`.`user` = `ecc`.`applicant_id`)))
                                        LEFT JOIN `jos_emundus_setup_campaigns` `esc` on((`esc`.`id` = `ecc`.`campaign_id`)))
                                        LEFT JOIN `jos_emundus_personal_detail` `epd` on((`ecc`.`applicant_id` = `epd`.`user`)))
                                        WHERE ((`epd`.`nationality` is NOT null)
                                            AND (`ecc`.`submitted` = 1))
                                        GROUP BY  `epd`.`nationality`";
            break;

            case 'jos_emundus_stats_gender':
                $query =   "CREATE VIEW jos_emundus_stats_gender AS
                                SELECT `ecc`.`id` AS `id`,
                                        `esc`.`year` AS `schoolyear`,
                                        count(distinct `ecc`.`applicant_id`) AS `nb`,
                                        `epd`.`gender` AS `gender`,
                                        `esc`.`label` AS `campaign`,
                                        `esc`.`training` AS `course`
                                FROM (((`jos_emundus_declaration` `ed`
                                JOIN `jos_emundus_campaign_candidature` `ecc` on((`ed`.`user` = `ecc`.`applicant_id`)))
                                LEFT JOIN `jos_emundus_setup_campaigns` `esc` on((`esc`.`id` = `ecc`.`campaign_id`)))
                                LEFT JOIN `jos_emundus_personal_detail` `epd` on((`ecc`.`applicant_id` = `epd`.`user`)))
                                WHERE ((`epd`.`gender` is NOT null)
                                    AND (`ecc`.`submitted` = 1))
                                GROUP BY  `epd`.`gender`";
            break;

            case 'jos_emundus_stats_files':
                $query =    "CREATE VIEW jos_emundus_stats_files AS
                                SELECT `ecc`.`id` AS `id`,
                                        count(distinct `ecc`.`fnum`) AS `nb`,
                                        `esc`.`year` AS `schoolyear`,
                                        `esc`.`label` AS `campaign`,
                                        `esc`.`training` AS `course`,
                                        `ecc`.`submitted` AS `submitted`,
                                        `ecc`.`status` AS `status`,
                                        `ess`.`value` AS `value`,
                                        `ecc`.`campaign_id` AS `campaign_id`,
                                        `ecc`.`published` AS `published`
                                FROM (((`jos_emundus_campaign_candidature` `ecc`
                                LEFT JOIN `jos_emundus_setup_campaigns` `esc` on((`esc`.`id` = `ecc`.`campaign_id`)))
                                LEFT JOIN `jos_emundus_setup_status` `ess` on((`ess`.`step` = `ecc`.`status`)))
                                LEFT JOIN `jos_users` `u` on((`u`.`id` = `ecc`.`user_id`)))
                                GROUP BY  `ecc`.`campaign_id`,`ecc`.`status`";
            break;
        }
        $db->setQuery($query);

        try {
            $db->execute();
            //$this->createFabrik($view);
            return true;
        } catch(Exception $e) {

            JLog::add('Error getting stats on account types at m/stats in query: '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }

    }

  /*  public function createFabrik($view) {
        $db = JFactory::getDbo();
        $currentTime = JFactory::getDate();
        $date0 = new JDate('0000-00-00 00:00:00');
        $user = JFactory::getUser(); // get user
        $query = $db->getQuery(true);

        $columns = array('label', 'record_in_database', 'error', 'intro', 'created', 'created_by',
                        'created_by_alias', 'modified', 'modified_by', 'checked_out', 'checked_out_time', 'publish_up', 'publish_down', 'reset_button_label', 'submit_button_label', 'form_template', 'view_only_template', 'published', 'private', 'params');

        $values = array($db->quote($view), 1, $db->quote(''), $db->quote(''), $db->quote($currentTime),$user->id,
        $db->quote($user->name), $db->quote($currentTime), $user->id, 0, $db->quote('0000-00-00 00:00:00'), $db->quote('0000-00-00 00:00:00'), $db->quote('0000-00-00 00:00:00'), $db->quote(''), $db->quote('Sauvegarder'), $db->quote('bootstrap'),  $db->quote('bootstrap'), 1, 1,  $db->quote('{"outro":"","reset_button":"0","reset_button_label":"Remise \u00e0 z\u00e9ro","reset_button_class":"btn-warning","reset_icon":"","reset_icon_location":"before","copy_button":"0","copy_button_label":"Save as copy","copy_button_class":"","copy_icon":"","copy_icon_location":"before","goback_button":"0","goback_button_label":"Retour","goback_button_class":"","goback_icon":"","goback_icon_location":"before","apply_button":"0","apply_button_label":"Appliquer","apply_button_class":"","apply_icon":"","apply_icon_location":"before","delete_button":"0","delete_button_label":"Effacer","delete_button_class":"btn-danger","delete_icon":"","delete_icon_location":"before","submit_button":"1","submit_button_label":"Sauvegarder","save_button_class":"btn-primary","save_icon":"","save_icon_location":"before","submit_on_enter":"0","labels_above":"0","labels_above_details":"0","pdf_template":"admin","pdf_orientation":"portrait","pdf_size":"letter","show_title":"1","print":"","email":"","pdf":"","admin_form_template":"","admin_details_template":"","note":"","show_referring_table_releated_data":"0","tiplocation":"tip","process_jplugins":"2","ajax_validations":"0","ajax_validations_toggle_submit":"0","submit_success_msg":"","suppress_msgs":"0","show_loader_on_submit":"0","spoof_check":"1","multipage_save":"0"}'));

        $query
            ->insert($db->quoteName('#__fabrik_forms'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);
            
        try {
            $db->execute();
            
        } catch(Exception $e) {
            die($e->getMessage());
            JLog::add('Error getting stats on account types at m/stats in query: '.$result->__toString(), JLog::ERROR, 'com_emundus');
        }

    }

    public function linkToFabrik($view){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query = 'SELECT id FROM `jos_fabrik_lists` WHERE `db_table_name` LIKE "'.$view.'"';
        $db->setQuery($query);

        try {
            return $db->loadRow();
        } catch(Exception $e) {
            JLog::add('Error getting stats on account types at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }

    }
    */
    
    public function getPeriodeData($periode) {
        if ($periode == 0)
            $query = ' 1 WEEK ';
        elseif ($periode == 1)
            $query = ' 2 WEEK ';
        elseif ($periode == 2)
            $query = ' 1 MONTH ';
        elseif ($periode == 3)
            $query = ' 3 MONTH ';
        elseif ($periode == 4)
            $query = ' 6 MONTH ';
        elseif ($periode == 5)
            $query = ' 1 YEAR ';
        return $query;
    }

    public function getAccountType($value, $periode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_comptes'))->where($db->quoteName('_day').' >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND '.$db->quoteName('_day').' <= CURDATE() AND '.$db->quoteName('profile_id').' = '.$value);
        $db->setQuery($query);

        try {
            return $db->loadAssocList();
        } catch(Exception $e) {
            JLog::add('Error getting stats on account types at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }

    public function consultationOffre($periode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $p = self::getPeriodeData($periode);



        $query = 'SELECT `titre`,`num_offre`, SUM(`nombre`) AS nb FROM
                (
                    SELECT * FROM jos_emundus_stats_nombre_consult_offre 
                    WHERE _day >= DATE_SUB(CURDATE(), INTERVAL'.$p.')
                    AND _day <= CURDATE()
                ) AS groupDate
                GROUP BY `num_offre`';
        $db->setQuery($query);
        try {
	        return $db->loadAssocList();
        } catch(Exception $e) {
	        JLog::add('Error getting stats on offer consultations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	        return false;
        }
    }

    public function candidatureOffres($periode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

        $query = 'SELECT `titre`,`num_offre`, SUM(`nombre`) AS nb FROM
                (
                    SELECT * FROM jos_emundus_stats_nombre_candidature_offre 
                    WHERE _day >= DATE_SUB(CURDATE(), INTERVAL'.$p.')
                    AND _day <= CURDATE()
                ) AS groupDate
                GROUP BY `num_offre`';
        $db->setQuery($query);
        try {
	        return $db->loadAssocList();
        } catch(Exception $e) {
	        JLog::add('Error getting stats on offer consultations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
	        return false;
        }
    }

    public function getConnections($periode) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_connexions'))->where($db->quoteName('_day').' >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND '.$db->quoteName('_day').' <= CURDATE()');
        $db->setQuery($query);

	    try {
		    return $db->loadAssocList();
	    } catch(Exception $e) {
		    JLog::add('Error getting stats on number of connections at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
		    return false;
	    }
    }

    public function getNbRelations($periode) {
        $db = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $p = self::getPeriodeData($periode);

        $query->select('*')->from($db->quoteName('#__emundus_stats_nombre_relations_etablies'))->where($db->quoteName('_day').' >= DATE_SUB(CURDATE(), INTERVAL '.$p.') AND '.$db->quoteName('_day').' <= CURDATE()');
        $db->setQuery($query);
        
	    try {
		    return $db->loadAssocList();
	    } catch(Exception $e) {
		    JLog::add('Error getting stats on number of relations at m/stats in query: '.$query->__toString(), JLog::ERROR, 'com_emundus');
		    return false;
	    }
    }
}

