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
class EmundusModelFabrik extends JModelAdmin {

	/**
     *
	 * 1) Deletes Fabrik elements that are not found in the dbTable list of columns
     * 2) Deletes The dbTable column if it is not found in the list of fabrik elements
     *
     * Only look at the Fabrik elements used by the Applicants !
     * @return  boolean
     *
	 */
	public function deleteColumns() {

        // LOGGER
        jimport('joomla.log.log');
        JLog::addLogger(['text_file' => 'com_emundus.fabelementclean.info.php'], JLog::INFO, 'com_emundus');
        JLog::addLogger(['text_file' => 'com_emundus.fabelementclean.error.php'], JLog::ERROR, 'com_emundus');

        $db = JFactory::getDBO();
        $dbName = JFactory::getConfig()->get('db');

        $query = "SELECT  fe.id
                    FROM #__fabrik_elements fe
                       LEFT JOIN #__fabrik_formgroup ffg ON  ffg.group_id = fe.group_id
                       LEFT JOIN #__fabrik_lists fl ON fl.form_id = ffg.form_id
                       WHERE fl.db_table_name IN (
                          SELECT db_table_name FROM #__fabrik_lists WHERE form_id IN (
                             SELECT SUBSTRING_INDEX(link,'formid=',-1) FROM #__menu jm
                             LEFT JOIN #__emundus_setup_profiles jesp on jm.menutype = jesp.menutype
                             WHERE jesp.published = 1
                             AND jm.link LIKE '%com_fabrik&view=form&formid%'
                             )
                       )
                       AND fe.name NOT IN (
                          SELECT COLUMN_NAME
                          FROM INFORMATION_SCHEMA.COLUMNS
                          WHERE TABLE_NAME IN (
                             SELECT db_table_name FROM #__fabrik_lists WHERE form_id IN (
                             SELECT SUBSTRING_INDEX(link,'formid=',-1) FROM #__menu jm
                             LEFT JOIN #__emundus_setup_profiles jesp on jm.menutype = jesp.menutype
                             WHERE jesp.published = 1
                             AND jm.link LIKE '%com_fabrik&view=form&formid%'
                             )
                          )
                          AND TABLE_SCHEMA LIKE '" . $dbName . "'
                       )
                        AND fe.name NOT LIKE 'id'  AND  fe.name NOT LIKE 'parent_id'";

        try {
            $db->setQuery($query);

            $fabElms = $db->loadColumn();

            if (!empty($fabElms)) {
                $query = "DELETE FROM #__fabrik_elements WHERE id IN (" . implode(', ', $fabElms) . ")";
                $db->setQuery($query);

                if ($db->execute()) {
                    $query = "DELETE FROM #__fabrik_joins WHERE element_id !=0 AND element_id not in (SELECT id FROM #__fabrik_elements)";
                    $db->setQuery($query);
                    $db->execute();
                }
                JLog::add($db->getNumRows() . ' Fabrik elements deleted (IDs : ' . implode(', ', $fabElms) .')', JLog::INFO, 'com_emundus');
            }
        }
        catch (Exception $e) {
            JLog::add('Error deleting fabrik elements at query '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }

        $query = "SELECT TABLE_NAME, GROUP_CONCAT(' DROP ', COLUMN_NAME) AS COLUMNS
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_NAME IN (
                        SELECT db_table_name FROM #__fabrik_lists WHERE form_id IN (
                       SELECT SUBSTRING_INDEX(link,'formid=',-1) FROM #__menu jm
                       INNER JOIN #__emundus_setup_profiles jesp on jm.menutype = jesp.menutype
                       WHERE jesp.published = 1
                       AND jm.link LIKE '%com_fabrik&view=form&formid%'
                    )
                    )
                    AND TABLE_SCHEMA = '" . $dbName . "'
                    AND COLUMN_NAME NOT IN
                    (
                        SELECT fe.name from #__fabrik_elements fe
                        INNER JOIN #__fabrik_formgroup ffg ON  ffg.group_id = fe.group_id
                        INNER JOIN #__fabrik_lists fl ON fl.form_id = ffg.form_id
                        WHERE fl.db_table_name IN (
                           SELECT db_table_name FROM #__fabrik_lists WHERE form_id IN (
                           SELECT SUBSTRING_INDEX(link,'formid=',-1) FROM #__menu jm
                           INNER JOIN #__emundus_setup_profiles jesp on jm.menutype = jesp.menutype
                           WHERE jesp.published = 1
                           AND jm.link LIKE '%com_fabrik&view=form&formid%'
                            )
                        )
                    )
                    GROUP BY TABLE_NAME";

        try {
            $db->setQuery($query);
            $tables = $db->loadObjectList();

            if (!empty($tables)) {
                $query = "";
                $res = false;

                foreach ($tables as $table) {
                    $query = "ALTER TABLE " . $table->TABLE_NAME . $table->COLUMNS.";";
                    $db->setQuery($query);
                    $res = $db->execute();
                }

                if ($res) {
                    JLog::add($db->getNumRows() . ' columns dropped (' . implode(', ', $table->TABLE_NAME . '.' . $table->COLUMNS . ')', JLog::INFO, 'com_emundus'));
                } else {
                    JLog::add('Error dropping table columns at query '.$query, JLog::ERROR, 'com_emundus');
                }

                return $res;
            }
            return true;
        }
        catch (Exception $e) {
            JLog::add('Error dropping table columns at query '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }
	}

    /**
     * @inheritDoc
     */
    public function getForm($data = array(), $loadData = true)
    {
        // TODO: Implement getForm() method.
    }
}