<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgEmundusAdd_tag extends JPlugin {

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);

        jimport('joomla.log.log');
        JLog::addLogger(array('text_file' => 'com_emundus.add_tag.php'), JLog::ALL, array('com_emundus.add_tag'));
    }



    /**
     * When a file changes to a certain status, we need to generate a zip archive and send it to the user.
     *
     * @param $fnum
     *
     * @param $state
     *
     * @return bool
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    function onAfterStatusChange($fnum, $state) {

        if (empty($fnum)) {
            return false;
        }

        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'files.php');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $m_files 	= new EmundusModelFiles;
        $assoc_tags = $m_files->getTagsAssocStatus($state);

        $query
            ->clear()
            ->select($db->quoteName('eta.id_tag'))
            ->from($db->quoteName('#__emundus_tag_assoc', 'eta'))
            ->join('LEFT',$db->quoteName('#__emundus_campaign_candidature', 'cc').' ON cc.fnum = eta.fnum')
            ->where($db->quoteName('eta.fnum'). ' LIKE ' . $db->quote($fnum));

        $db->setQuery($query);

        if (array_intersect($assoc_tags, $db->loadColumn())) {
            return false;
        }

        $query->clear()
            ->select($db->quoteName('year'))
            ->from($db->quoteName('#__emundus_setup_campaigns', 'esc'))
            ->join('LEFT',$db->quoteName('#__emundus_campaign_candidature', 'cc').' ON esc.id = cc.campaign_id')
            ->where($db->quoteName('cc.fnum'). ' LIKE ' . $db->quote($fnum));

        $db->setQuery($query);
        $schoolyear = $db->loadResult();

        $aid = intval(substr($fnum, 21, 7));
        $query
            ->clear()
            ->select($db->quoteName('eta.id_tag'))
            ->from($db->quoteName('#__emundus_tag_assoc', 'eta'))
            ->join('LEFT',$db->quoteName('#__emundus_campaign_candidature', 'cc').' ON cc.fnum = eta.fnum')
            ->join('LEFT', $db->quoteName('#__emundus_setup_campaigns', 'esc') .' ON esc.id = cc.campaign_id')
            ->join('LEFT',$db->quoteName('#__emundus_users', 'eu').' ON eu.user_id = cc.applicant_id')
            ->where($db->quoteName('eu.user_id'). ' = ' . $db->quote($aid).' AND '.$db->quoteName('esc.year'). ' = ' . $db->quote($schoolyear));

        $db->setQuery($query);

        foreach ($assoc_tags as $key => $assoc_tag){
            if (in_array($assoc_tag, $db->loadColumn())) {
                unset($assoc_tags[$key]);
                break;
            }
        }

        return $m_files->tagFile([$fnum],[$assoc_tags[array_keys($assoc_tags)[0]]]);
    }

}
