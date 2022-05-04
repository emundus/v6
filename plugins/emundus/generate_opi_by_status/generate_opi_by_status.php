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
 * @subpackage  Fabrik.cron.generate_opi_by_status
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

        $user = JFactory::getUser()->id;

        if (!in_array($state, $status_to_generate_opi)) {
            return false;
        }

        /// first, get fnum info
        require_once(JPATH_SITE.DS.'components'.DS.'com_emundus' . DS . 'models' . DS . 'files.php');
        $_mFile = new EmundusModelFiles;

        $fnum_infos = $_mFile->getFnumInfos($fnum);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $getLastOpiQuery = $db->getQuery(true);

        try {
            $getLastOpiQuery->clear()
                ->select('#__emundus_final_grade.code_opi')
                ->from($db->quoteName('#__emundus_final_grade'))
                ->where($db->quoteName('#__emundus_final_grade.code_opi') . ' is not null')
                ->andWhere($db->quoteName('#__emundus_final_grade.code_opi') . " != ''")
                ->order('code_opi desc limit 1');

            $db->setQuery($getLastOpiQuery);
            $lastOpi = $db->loadResult();       // (1) row or (null)

            // check exist fnum and opi (1 query)
            $checkFnumQuery = "SELECT * FROM #__emundus_final_grade WHERE #__emundus_final_grade.fnum = " . $fnum;
            $db->setQuery($checkFnumQuery);
            $checkFnum = $db->loadObject();

            /// opi start digit (always 0)
            $opi_suffix = 0;

            if (is_null($lastOpi)) {
                /// no opi exists
                $opi_suffix += 1;
                $opi_full_code = $opi_prefix . str_pad((int)$opi_suffix, 7, '0', STR_PAD_LEFT);

                if($checkFnum == null) {
                    // fnum does not exist, create new decision with opi
                    $_rawData = array('time_date' => date('Y-m-d H:i:s'), 'user' => $user, 'student_id' => $fnum_infos['uid'], 'campaign_id' => $fnum_infos['id'], 'fnum' => $fnum, 'final_grade' => 2, 'code_opi' => $opi_full_code);

                    $query->clear()->insert($db->quoteName('#__emundus_final_grade'))
                        ->columns($db->quoteName(array_keys($_rawData)))
                        ->values(implode(',', $db->quote(array_values($_rawData))));

                } else {
                    /// fnum exist, update it with opi
                    if (is_null($checkFnum->code_opi)) {
                        $query = 'UPDATE #__emundus_final_grade SET code_opi = ' . $db->quote($opi_full_code) . ' WHERE #__emundus_final_grade.fnum = ' . $fnum;
                    }
                }
            }
            else {
                /// check if this fnum has OPI
                $lastOpi = (int)end(explode($opi_prefix, $lastOpi));
                $opi_full_code = $opi_prefix . str_pad((int)$lastOpi += 1, 7, '0', STR_PAD_LEFT);

                if($checkFnum == null) {
                    /// fnum does not exist --> create new decision with OPI
                    $_rawData = array('time_date' => date('Y-m-d H:i:s'), 'user' => $user, 'student_id' => $fnum_infos['uid'], 'campaign_id' => $fnum_infos['id'], 'fnum' => $fnum, 'final_grade' => 2, 'code_opi' => $opi_full_code);

                    $query->clear()->insert($db->quoteName('#__emundus_final_grade'))
                        ->columns($db->quoteName(array_keys($_rawData)))
                        ->values(implode(',', $db->quote(array_values($_rawData))));
                } else {
                    /// fnum already exists, but code_opi does not exists (call another SQL query)
                    if (is_null($checkFnum->code_opi)) {
                        $query = 'UPDATE #__emundus_final_grade SET code_opi = ' . $db->quote($opi_full_code) . ' WHERE #__emundus_final_grade.fnum = ' . $fnum;
                    }
                }
            }

            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e) {
            JLog::add('Error generating OPI code : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }
    }
}

