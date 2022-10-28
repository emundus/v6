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
 * A cron task to create a reference when status change to CA
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusrecall
 * @since       3.0
 */

class PlgEmundusHopitaux_paris_create_reference extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.emundushopitaux_paris_create_reference.php'), JLog::ALL, array('com_emundus'));
    }


    function onAfterStatusChange($fnum,$state) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $status_to_check = explode(',',$this->params->get('reference_status_step', ''));

        $status = array_search($state, $status_to_check);

        if ($status === false) {
            return false;
        }

        try{
            $query->select('cc.applicant_id,cc.campaign_id,sc.training,sc.year')
                ->from($db->quoteName('#__emundus_campaign_candidature','cc'))
                ->leftJoin($db->quoteName('#__emundus_setup_campaigns','sc').' ON '.$db->quoteName('sc.id').' = '.$db->quoteName('cc.campaign_id'))
                ->where($db->quoteName('cc.fnum') . ' = ' . $db->quote($fnum));
            $db->setQuery($query);
            $file = $db->loadObject();

            $query->clear()
                ->select('id')
                ->from($db->quoteName('data_references_dossiers'))
                ->where($db->quoteName('fnum') . ' LIKE ' . $db->quote($fnum));
            $db->setQuery($query);
            $fnum_reference = $db->loadResult();

            if(empty($fnum_reference)) {
                $reference = null;
                switch ($file->training) {
                    case 'AAP_ESPACE_00':
                        if ($state == 5) {
                            $reference = 'COVID/ES/' . $file->year;
                        }
                        break;
                    case 'PROGRAMME__00':
                        if ($state == 5) {
                            $reference = 'COVID/PA/' . $file->year;
                        }
                        break;
                    case 'FONDS_D_AI_01':
                        if ($state == 17) {
                            $reference = 'FAU/AS/' . $file->year;
                        }
                        break;
                    default:
                        $reference = null;
                }

                if (!empty($reference)) {
                    $query->clear()
                        ->select('cast(substring_index(reference,'/',-1) as signed) as reference_number')
                        ->from($db->quoteName('data_references_dossiers'))
                        ->where($db->quoteName('reference') . ' LIKE ' . $db->quote($reference . '%'))
                        ->order('reference_number');
                    $db->setQuery($query);
                    $references = $db->loadColumn();

                    if (!empty($references)) {
                        $last = end($references);
                        $new_reference_number = (int)$last + 1;
                    } else {
                        $new_reference_number = 1;
                    }

                    $new_reference = $reference . '/' . $new_reference_number;

                    $query->clear()
                        ->insert($db->quoteName('data_references_dossiers'))
                        ->set($db->quoteName('fnum') . ' = ' . $db->quote($fnum))
                        ->set($db->quoteName('reference') . ' = ' . $db->quote($new_reference));
                    $db->setQuery($query);
                    $db->execute();
                }
            }

        } catch(Exception $e) {
            JLog::add('plugins/emundus/hopitaux_paris_create_reference | Error when try to create the reference : ' . $e->getMessage(), JLog::ERROR, 'com_emundus');
            return false;
        }

        return true;
    }
}
