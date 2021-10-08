<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * A cron task to email records to a give set of users (incomplete application)
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgEmundusGenerate_opi_by_status extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.emundusreferent_status.php'), JLog::ALL, array('com_emundus'));
    }


    function onAfterStatusChange($fnum,$state) {
        $status_to_generate_opi = explode(',', $this->params->get('opi_status_step', ''));
        $opi_prefix = $this->params->get('opi_prefix', '');

        if (!in_array($state, $status_to_generate_opi)) {
            return false;
        }

        $db = JFactory::getDbo();
        $getLastOpiQuery = $db->getQuery(true);

        try {
            $getLastOpiQuery->clear()
                ->select('#__emundus_final_grade.code_opi')
                ->from($db->quoteName('#__emundus_final_grade'))
                ->where($db->quoteName('#__emundus_final_grade.code_opi') . ' is not null')
                ->andWhere($db->quoteName('#__emundus_final_grade.code_opi') . " != ''")
                ->order('code_opi desc limit 1');

            $db->setQuery($getLastOpiQuery);
            $lastOpiCode = $db->loadResult();       // (1) row or (null)

            $opi_suffix = 0;

            if ($lastOpiCode == null) {
                $opi_suffix += 1;
                $opi_full_code = $opi_prefix . str_pad((int)$opi_suffix, 7, '0', STR_PAD_LEFT);

                /// insert this $opi_full_code to table [jos_emundus_final_grade]
                $insertQuery = 'UPDATE #__emundus_final_grade SET code_opi = ' . $db->quote($opi_full_code) . ' WHERE #__emundus_final_grade.fnum = ' . $fnum;
                $db->setQuery($insertQuery);
                $db->execute();
            } else {
                /// check if this fnum has OPI
                $checkFnumOpi = "SELECT #__emundus_final_grade.code_opi FROM #__emundus_final_grade WHERE #__emundus_final_grade.fnum = " . $fnum;
                $db->setQuery($checkFnumOpi);

                $_res = $db->loadResult();

                if (is_null($_res)) {
                    /// opi does not exists before
                    $lastOpiCode = (int)end(explode($opi_prefix, $lastOpiCode));
                    $opi_full_code = $opi_prefix . str_pad((int)$lastOpiCode += 1, 7, '0', STR_PAD_LEFT);

                    /// insert this $opi_full_code for each record of table [jos_emundus_final_grade]
                    $insertQuery = 'UPDATE #__emundus_final_grade SET code_opi = ' . $db->quote($opi_full_code) . ' WHERE #__emundus_final_grade.fnum = ' . $fnum;
                    $db->setQuery($insertQuery);
                    $db->execute();
                }

            }
        } catch (Exception $e) {
            JLog::add('Error generating OPI code : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}
