<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_emundus_panel
 *
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_emundus_panel
 *
 * @since  1.5
 */
class ModEmunduspanelHelper
{
    public function getFeaturesList(){
        $eMConfig = JComponentHelper::getParams('com_emundus');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $features = array();

        // Hikashop
        $hikashop = new stdClass;
        $hikashop->label = 'MOD_EMUNDUS_PANEL_HIKASHOP';
        $hikashop->enabled = 0;
        $hikashop->help = 'https://emundus.atlassian.net/wiki/spaces/EKB/pages/1548877825/Hikashop';

        if($eMConfig->get('application_fee') == 1){
            $hikashop->enabled = 1;
        }
        $features[] = $hikashop;
        //

        // Gotenberg
        $gotenberg = new stdClass;
        $gotenberg->label = 'MOD_EMUNDUS_PANEL_GOTENBERG';
        $gotenberg->enabled = 0;
        $gotenberg->help = 'https://emundus.atlassian.net/wiki/spaces/EID/pages/387416073/2.a+Gotenberg';

        if($eMConfig->get('gotenberg_activation') == 1){
            $gotenberg->enabled = 1;
        }
        $features[] = $gotenberg;
        //

        // Addpipe
        $add_pipe = new stdClass;
        $add_pipe->label = 'MOD_EMUNDUS_PANEL_ADDPIPE';
        $add_pipe->enabled = 0;
        $add_pipe->help = 'https://emundus.atlassian.net/wiki/spaces/EID/pages/385941551/2.b+Addpipe';

        if($eMConfig->get('addpipe_activation') == 1){
            $add_pipe->enabled = 1;
        }
        $features[] = $add_pipe;
        //

        // Yousign
        $yousign = new stdClass;
        $yousign->label = 'MOD_EMUNDUS_PANEL_YOUSIGN';
        $yousign->enabled = 0;
        $yousign->help = 'https://emundus.atlassian.net/wiki/spaces/EKB/pages/832077848/Yousign';

        if(!empty($eMConfig->get('yousign_api_key'))){
            $yousign->enabled = 1;
        }
        $features[] = $yousign;
        //

        // Ametys
        $ametys = new stdClass;
        $ametys->label = 'MOD_EMUNDUS_PANEL_AMETYS';
        $ametys->enabled = 0;
        $ametys->help = '';

        if($eMConfig->ametys_integration == 1){
            $ametys->enabled = 1;
        }
        $features[] = $ametys;
        //

        // Siscole
        $siscole = new stdClass;
        $siscole->label = 'MOD_EMUNDUS_PANEL_SISCOLE';
        $siscole->enabled = 0;
        $siscole->help = '';

        if(!empty($eMConfig->get('filename'))){
            $siscole->enabled = 1;
        }
        $features[] = $siscole;
        //

        // ZOOM
        $zoom = new stdClass;
        $zoom->label = 'MOD_EMUNDUS_PANEL_ZOOM';
        $zoom->enabled = 0;
        $zoom->help = 'https://emundus.atlassian.net/wiki/spaces/OI/pages/2151579651/API+Zoom+et+CELSA+Dev+Reference';

        if(!empty($eMConfig->get('zoom_jwt'))){
            $zoom->enabled = 1;
        }
        $features[] = $zoom;
        //

        // CAS
        $cas = new stdClass;
        $cas->label = 'MOD_EMUNDUS_PANEL_CAS';
        $cas->enabled = 0;
        $cas->help = 'https://emundus.atlassian.net/wiki/spaces/EKB/pages/763494402/Mettre+en+place+le+systeme+CAS';

        $query->select('enabled')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' LIKE ' . $db->quote('externallogin'))
            ->andWhere($db->quoteName('folder') . ' LIKE ' . $db->quote('authentication'));
        $db->setQuery($query);
        $cas->enabled = (int)$db->loadResult();
        $features[] = $cas;
        //

        // LDAP
        $ldap = new stdClass;
        $ldap->label = 'MOD_EMUNDUS_PANEL_LDAP';
        $ldap->enabled = 0;
        $ldap->help = '';

        $query->clear()
            ->select('enabled')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' LIKE ' . $db->quote('ldap'))
            ->andWhere($db->quoteName('folder') . ' LIKE ' . $db->quote('authentication'));
        $db->setQuery($query);
        $ldap->enabled = (int)$db->loadResult();
        $features[] = $ldap;
        //

        // EVENT Handler
        $event_handler = new stdClass;
        $event_handler->label = 'MOD_EMUNDUS_PANEL_EVENT_HANDLER';
        $event_handler->enabled = 0;
        $event_handler->help = 'https://emundus.atlassian.net/wiki/spaces/EKB/pages/2077392897/eMundus+event+handler';

        $query->clear()
            ->select('extension_id,params')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' LIKE ' . $db->quote('custom_event_handler'))
            ->andWhere($db->quoteName('folder') . ' LIKE ' . $db->quote('emundus'));
        $db->setQuery($query);
        $extension = $db->loadObject();
        $event_handler->id = $extension->extension_id;

        $params = json_decode($extension->params);

        foreach ($params->event_handlers as $event) {
	        if(!empty($event) && $event->published == 1) {
                $event_handler->enabled += 1;
            }
        }
        $features[] = $event_handler;
        //

        return $features;
    }

    /**
     * Display messages about the current state of the system
     * @return void
     */
    public function checkup(): void
    {
        $app = JFactory::getApplication();

        // verify session sql mode
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('@@sql_mode');

        try {
            $db->setQuery($query);
            $sql_mode = $db->loadResult();
        } catch (Exception $e) {
            $app->enqueueMessage(JText::_('MOD_EMUNDUS_PANEL_SQL_MODE_ERROR'), 'error');
        }

        if (!empty($sql_mode)) {
            if (strpos($sql_mode, 'NO_ZERO_DATE') !== false) {
                $app->enqueueMessage(JText::_('MOD_EMUNDUS_PANEL_SQL_MODE_NO_ZERO_DATE'), 'warning');
            }
            if (strpos($sql_mode, 'NO_ZERO_IN_DATE') !== false) {
                $app->enqueueMessage(JText::_('MOD_EMUNDUS_PANEL_SQL_MODE_NO_IN_ZERO_DATE'), 'warning');
            }
            if (strpos($sql_mode, 'ONLY_FULL_GROUP_BY') !== false) {
                $app->enqueueMessage(JText::_('MOD_EMUNDUS_PANEL_SQL_MODE_ONLY_FULL_GROUP_BY'), 'warning');
            }
        }
    }
}
