<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.emundus_profile
 *
 * @copyright   Copyright (C) 2017 eMundus, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldCampaign extends JFormField
{
    protected $type = 'campaign';

    protected function getInput() {
        $course = JRequest::getVar('course', '', '', 'str');
        $course = !empty($course)?$course:"%";

        $offset = JFactory::getApplication()->get('offset', 'UTC');
        $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
        $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
        $now = $dateTime->format('Y-m-d H:i:s');

        $db = JFactory::getDBO();
        $query = "SELECT esc.id, CONCAT(esc.label,' (',esc.year,')') AS label 
                    FROM #__emundus_setup_campaigns as esc
                    LEFT JOIN #__emundus_setup_programmes as esp ON esp.code=esc.training
                    WHERE esc.published=1 
                    AND esp.apply_online=1 
                    AND ".$db->Quote($now)."  >= esc.start_date 
                    AND esc.end_date >= ".$db->Quote($now)." 
                    AND esc.training like ".$db->Quote($course)." 
                    ORDER BY esc.label";
        $db->setQuery($query);
        $campaigns = $db->loadAssocList();

        $list = '<select id="jform_emundus_profile_'.$this->element['name'].'" class="required" name="jform[emundus_profile]['.$this->element['name'].']">';
        $list .= '<option value="">'.JText::_('PLEASE_SELECT').'</option>';
        foreach ($campaigns as $campaign) {
            $list .= '<option value="'.$campaign['id'].'">'.$campaign['label'].'</option>';
        }
        $list .= '</select>';
        
        $div = '<div id="em_campaign_info"><div>';

        return $list.$div;
    }
}
?>