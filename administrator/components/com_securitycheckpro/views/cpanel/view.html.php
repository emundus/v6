<?php
/**
* Securitycheck Pro Control Panel View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * Securitycheck Pro Control Panel view class
 *
 */
class SecuritycheckProsViewCpanel extends JViewLegacy
{
	function display($tpl = NULL)
	{
		JToolBarHelper::title( JText::_( 'Securitycheck Pro' ).' | ' .JText::_('COM_SECURITYCHECKPRO_CONTROLPANEL'), 'securitycheckpro' );
		
		
		// Obtenemos los datos del modelo...
		$model = $this->getModel();
		
		//  Parámetros del plugin
		$items= $model->getConfig();
								
		// Extraemos los elementos de las distintas listas...
		$blacklist_elements= array();
		$pagination_blacklist = null;
		if ( (!is_null($items['blacklist'])) && ($items['blacklist'] != '') ) {
			$items['blacklist'] = str_replace(' ','',$items['blacklist']);
			$blacklist_elements = explode(',',trim($items['blacklist']));
		}

		$dynamic_blacklist_elements= $model->get_dynamic_blacklist_ips();

		$whitelist_elements= array();
		$pagination_whitelist = null;

		if ( (!is_null($items['whitelist'])) && ($items['whitelist'] != '') ) {	
			$items['whitelist'] = str_replace(' ','',$items['whitelist']);
			$whitelist_elements = explode(',',trim($items['whitelist']));
		}
		
		$firewall_plugin_enabled = $model->PluginStatus(1);
		$cron_plugin_enabled = $model->PluginStatus(2);
		$update_database_plugin_enabled = $model->PluginStatus(3);
		$update_database_plugin_exists = $model->PluginStatus(4);
		$spam_protection_plugin_enabled = $model->PluginStatus(5);
		$spam_protection_plugin_exists = $model->PluginStatus(6);
		$trackactions_plugin_exists = $model->PluginStatus(8);
		$logs_pending = $model->LogsPending();
		$scpro_plugin_id = $model->get_plugin_id(1);
		$scprocron_plugin_id = $model->get_plugin_id(2);
		$params = JComponentHelper::getParams('com_securitycheckpro');
		// ... y el tipo de servidor web
		$mainframe = JFactory::getApplication();
		$server = $mainframe->getUserState("server",'apache');
		// ... y las estadísticas de los logs
		$last_year_logs = $model->LogsByDate('last_year');
		$this_year_logs = $model->LogsByDate('this_year');
		$last_month_logs = $model->LogsByDate('last_month');
		$this_month_logs = $model->LogsByDate('this_month');
		$last_7_days = $model->LogsByDate('last_7_days');
		$yesterday = $model->LogsByDate('yesterday');
		$today = $model->LogsByDate('today');
		$total_firewall_rules = $model->LogsByType('total_firewall_rules');
		$total_blocked_access = $model->LogsByType('total_blocked_access');
		$total_user_session_protection = $model->LogsByType('total_user_session_protection');
		$easy_config_applied = $model->Get_Easy_Config();
		// Versiones de los componentes instalados
		$version_scp = $model->get_version('securitycheckpro');
		if ($update_database_plugin_exists) {
			$version_update_database = $model->get_version('databaseupdate');
			$this->assignRef('version_update_database', $version_update_database);
		}
		if ($trackactions_plugin_exists) {
			$version_trackactions = $model->get_version('trackactions');
			$this->assignRef('version_trackactions', $version_trackactions);
		}
				
		// Obtenemos el status de la seguridad
		require_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/sysinfo.php';
		$overall = new SecuritycheckprosModelSysinfo();
		$overall = $overall->getInfo();		
		$overall = $overall['overall_joomla_configuration'];
		
		// Obtenemos los datos del modelo geoblock...
		require_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/geoblock.php';
		$instance = new SecuritycheckprosModelGeoblock();
		$geoip_database_update = $instance->get_latest_database_update();
		$geolite_automatic_updates = $params->get('geoip_automatic_updates');
		
		if ( ($geoip_database_update > 30) && ($geolite_automatic_updates == 1) ) {
			$instance->update_geoblock_database();
		}
		
		// Ponemos los datos en el template
		$this->assignRef('firewall_plugin_enabled', $firewall_plugin_enabled);
		$this->assignRef('cron_plugin_enabled', $cron_plugin_enabled);
		$this->assignRef('update_database_plugin_enabled', $update_database_plugin_enabled);
		$this->assignRef('update_database_plugin_exists', $update_database_plugin_exists);
		$this->assignRef('spam_protection_plugin_enabled', $spam_protection_plugin_enabled);
		$this->assignRef('spam_protection_plugin_exists', $spam_protection_plugin_exists);
		$this->assignRef('trackactions_plugin_exists', $trackactions_plugin_exists);
		$this->assignRef('logs_pending', $logs_pending);
		$this->assignRef('scpro_plugin_id', $scpro_plugin_id);
		$this->assignRef('scprocron_plugin_id', $scprocron_plugin_id);
		$this->assignRef('server', $server);
		$this->assignRef('last_year_logs', $last_year_logs);
		$this->assignRef('this_year_logs', $this_year_logs);
		$this->assignRef('last_month_logs', $last_month_logs);
		$this->assignRef('this_month_logs', $this_month_logs);
		$this->assignRef('last_7_days', $last_7_days);
		$this->assignRef('yesterday', $yesterday);
		$this->assignRef('today', $today);
		$this->assignRef('total_firewall_rules', $total_firewall_rules);
		$this->assignRef('total_blocked_access', $total_blocked_access);
		$this->assignRef('total_user_session_protection', $total_user_session_protection);
		$this->assignRef('easy_config_applied', $easy_config_applied);
		$this->assignRef('overall', $overall);
		$this->assignRef('blacklist_elements',$blacklist_elements);
		$this->assignRef('dynamic_blacklist_elements',$dynamic_blacklist_elements);
		$this->assignRef('whitelist_elements',$whitelist_elements);
		$this->assignRef('geoip_database_update',$geoip_database_update);
		$this->assignRef('geolite_automatic_updates',$geolite_automatic_updates);
		$this->assignRef('version_scp', $version_scp);
				
		parent::display();
	}
}