<?php
/**
 * @version		$Id: checklist.php 14401 2022-09-09 14:10:00Z brice.hubinet@emundus.fr $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2022 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

/**
 * Content Component Checklist Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 3.10
 */
class EmundusHelperChecklist {

    /**
     * Set filename for uploaded attachment send by applicant
     *
     * @param string $file filename received
     * @param string $lbl system name defined in emundus_setup_attachments
     * @param array $fnumInfos infos from fnum
     * @return string
     */
    function setAttachmentName(string $file, string $lbl, array $fnumInfos): string {

        $file_array = explode(".", $file);

        $eMConfig = JComponentHelper::getParams('com_emundus');
        $applicant_file_name = $eMConfig->get('applicant_file_name', null);

        if (!empty($applicant_file_name)) {
            require_once(JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'emails.php');
            $m_emails = new EmundusModelEmails;

            $tags = $m_emails->setTags($fnumInfos['applicant_id'], null, $fnumInfos['fnum'], '', $applicant_file_name);
            $application_form_name = preg_replace($tags['patterns'], $tags['replacements'], $applicant_file_name);
            $application_form_name = $m_emails->setTagsFabrik($application_form_name, array($fnumInfos['fnum']));

            // Format filename
            $application_form_name = $m_emails->stripAccents($application_form_name);
            $application_form_name = preg_replace('/[^A-Za-z0-9 _.-]/','', $application_form_name);
            $application_form_name = preg_replace('/\s/', '_', $application_form_name);
            $application_form_name = strtolower($application_form_name);
            $filename = $application_form_name.'_'.trim($lbl, ' _').'-'.rand().'.'.end($file_array);

        } else {
            $filename = $fnumInfos['applicant_id'].'-'.$fnumInfos['id'].'-'.trim($lbl, ' _').'-'.rand().'.'.end($file_array);
        }

        return $filename;
    }
}
