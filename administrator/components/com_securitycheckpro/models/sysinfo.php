<?php
/**
* Modelo Securitycheckpros para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();


/**
* Modelo Securitycheck
*/
class SecuritycheckprosModelSysinfo extends SecuritycheckproModel
{

/* @var array somme system values  */
protected $info = null;

/**
 * method to get the system information
 *
 * @return array system information values
 */
public function &getInfo()
{
	if (is_null($this->info)){
		$this->info = array();
		$version = new JVersion;
		$platform = new JPlatform;
		$db = JFactory::getDBO();
						
		// Obtenemos el tamaño de la variable 'max_allowed_packet' de Mysql
		$db->setQuery('SHOW VARIABLES LIKE \'max_allowed_packet\'');
		$keys = $db->loadObjectList();
		$array_val = get_object_vars($keys[0]);
		$tamanno_max_allowed_packet = (int) ($array_val["Value"]/1024/1024);
		
		// Obtenemos el tamaño máximo de memoria establecido
		$params = JComponentHelper::getParams('com_securitycheckpro');
		$memory_limit = $params->get('memory_limit','512M');
		
		// Obtenemos las opciones de configuración
		require_once JPATH_ROOT.'/components/com_securitycheckpro/models/json.php';
		$values = new SecuritycheckProsModelJson();
		$values->getStatus(false);
		
		// Obtenemos las opciones del Cpanel
		require_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/cpanel.php';
		$CpanelOptions = new SecuritycheckprosModelCpanel();
		$firewall_plugin_enabled = $CpanelOptions->PluginStatus(1);
		$cron_plugin_enabled = $CpanelOptions->PluginStatus(2);
		$spam_protection_plugin_enabled = $CpanelOptions->PluginStatus(5);
		
		// Obtenemos los parámetros del Firewall
		require_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/library/model.php';
		$FirewallOptions = new SecuritycheckproModel();
		$FirewallOptions = $FirewallOptions->getConfig();		
				
		// Obtenemos las opciones de protección .htaccess
		require_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/protection.php';
		$ConfigApplied = new SecuritycheckprosModelProtection();
		$ConfigApplied = $ConfigApplied->GetConfigApplied();
						
		$this->info['phpversion']	= phpversion();
		$this->info['version']		= $version->getLongVersion();
		$this->info['platform']		= $platform->getLongVersion();
		$this->info['max_allowed_packet']		= $tamanno_max_allowed_packet;
		$this->info['memory_limit']		= $memory_limit;
		//Security
		$this->info['coreinstalled']		= $values->data['coreinstalled'];
		$this->info['corelatest']		= $values->data['corelatest'];
		$this->info['files_with_incorrect_permissions']		= $values->data['files_with_incorrect_permissions'];
		$this->info['files_with_bad_integrity']		= $values->data['files_with_bad_integrity'];
		$this->info['vuln_extensions']		= $values->data['vuln_extensions'];
		$this->info['suspicious_files']		= $values->data['suspicious_files'];
		// Si el directorio de administración está protegido con contraseña, marcamos la opción de protección del backend como habilitada
		if ( !$ConfigApplied['hide_backend_url'] ) {
			if ( file_exists(JPATH_ADMINISTRATOR. DIRECTORY_SEPARATOR . '.htpasswd') ) {				
				$ConfigApplied['hide_backend_url'] = '1';
			}
		}
		$this->info['backend_protection']		= $ConfigApplied['hide_backend_url'];
		// Existe el fichero kickstart.php
		$this->info['kickstart_exists']		= $this->check_kickstart();	
		$this->info['firewall_options']		= $FirewallOptions;
		$this->info['twofactor_enabled']	= $this->get_two_factor_status();
		$this->info['overall_joomla_configuration']		= $this->getOverall($this->info,1);
		//Extension status
		$this->info['cron_plugin_enabled']		= $cron_plugin_enabled;
		$this->info['firewall_plugin_enabled']		= $firewall_plugin_enabled;
		$this->info['spam_protection_plugin_enabled']		= $spam_protection_plugin_enabled;
		$this->info['firewall_options']		= $FirewallOptions;
		$this->info['last_check']		= $values->data['last_check'];
		$this->info['last_check_integrity']		= $values->data['last_check_integrity'];		
		//Htaccess protection
		$this->info['htaccess_protection']		= $ConfigApplied;
		$this->info['overall_web_firewall']		= $this->getOverall($this->info,2);		
		
	}
	return $this->info;
}

// Obtiene el estado del segundo factor de autenticación de Joomla (Google y Yubikey)
function get_two_factor_status() {
	$enabled = 0;
	
	$db = $this->getDbo();
	$query = $db->getQuery(true)
		->select(array($db->quoteName('enabled')))
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('name').' = '.$db->quote('plg_twofactorauth_totp'));
	$db->setQuery($query);
	$enabled = $db->loadResult();
	
	if ( $enabled == 0 ) {
		$query = $db->getQuery(true)
			->select(array($db->quoteName('enabled')))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('name').' = '.$db->quote('plg_twofactorauth_yubikey'));
		$db->setQuery($query);
		$enabled = $db->loadResult();
	}
	
	return $enabled;
}

// Chequea si el fichero kickstart.php existe en la raíz del sitio. Esto sucede cuando se restaura un sitio y se olvida (junto con algún backup) eliminarlo.
public function check_kickstart() {
	$found = false;	
	$akeeba_kickstart_file = JPATH_ROOT . DIRECTORY_SEPARATOR . "kickstart.php";
	
	if ( file_exists($akeeba_kickstart_file) ){
		if ( strpos(file_get_contents($akeeba_kickstart_file),"AKEEBA") !== false ) {
			$found = true;
		}		
	}
	
	return $found;
	
}

// Obtiene el porcentaje general de cada una de las barras de progreso
public function getOverall($info,$opcion) {
	// Inicializamos variables
	$overall = 0;
	
	switch ($opcion) {
		// Porcentaje de progreso de  Joomla Configuration
		case 1:
			if ( $info['kickstart_exists'] ) {
				return 2;
			}
			if ( version_compare($info['coreinstalled'],$info['corelatest'],'==') ) {
				$overall = $overall + 10;
			}
			if ( $info['files_with_incorrect_permissions'] == 0 ) {
				$overall = $overall + 5;
			}
			if ( $info['files_with_bad_integrity'] == 0 ) {
				$overall = $overall + 10;
			}
			if ( $info['vuln_extensions'] == 0 ) {
				$overall = $overall + 30;
			}
			if ( $info['suspicious_files'] == 0 ) {
				$overall = $overall + 20;
			}
			if ( $info['backend_protection'] ) {
				$overall = $overall + 10;
			}
			if ( $info['firewall_options']['forbid_new_admins'] == 1 ) {
				$overall = $overall + 5;
			}
			if ( $info['twofactor_enabled'] == 1 ) {
				$overall = $overall + 10;
			}
			break;
		case 2:
			if ( $info['firewall_plugin_enabled'] ) {
				$overall = $overall + 10;				
				// Configuración del firewall
				if ( $info['firewall_options']['dynamic_blacklist'] ) {
					$overall = $overall + 10;					
				}
				if ( $info['firewall_options']['logs_attacks'] ) {
					$overall = $overall + 2;					
				}
				if ( $info['firewall_options']['second_level'] ) {
					$overall = $overall + 2;					
				}
				if ( !(strstr($info['firewall_options']['strip_tags_exceptions'],'*')) ) {
					$overall = $overall + 4;					
				}
				if ( !(strstr($info['firewall_options']['sql_pattern_exceptions'],'*')) ) {
					$overall = $overall + 4;										
				}
				if ( !(strstr($info['firewall_options']['lfi_exceptions'],'*')) ) {
					$overall = $overall + 4;										
				}
				if ( $info['firewall_options']['session_protection_active'] ) {
					$overall = $overall + 2;					
				}
				if ( $info['firewall_options']['session_hijack_protection'] ) {
					$overall = $overall + 2;					
				}
				if ( $info['firewall_options']['upload_scanner_enabled'] ) {
					$overall = $overall + 4;					
				}
				if ( $info['spam_protection_plugin_enabled'] ) {
					$overall = $overall + 2;					
				}
				
				// Cron 
				$last_check = new DateTime(date('Y-m-d',strtotime($this->info['last_check'])));
				$now = new DateTime(date('Y-m-d',strtotime(date('Y-m-d H:i:s'))));
					
				// Extraemos los días que han pasado desde el último chequeo
				(int) $interval = $now->diff($last_check)->format("%a");
																		
				if ( $interval < 2 ) {
					$overall = $overall + 10;					
				} else {
					
				}
				
				$last_check_integrity = new DateTime(date('Y-m-d',strtotime($this->info['last_check_integrity'])));
				$now = new DateTime(date('Y-m-d',strtotime(date('Y-m-d H:i:s'))));
					
				// Extraemos los días que han pasado desde el último chequeo
				(int) $interval = $now->diff($last_check_integrity)->format("%a");
																		
				if ( $interval < 2 ) {
					$overall = $overall + 10;					
				} else {
					
				}
				// Htaccess protection
				if ( $info['htaccess_protection']['prevent_access'] ) {
					$overall = $overall + 6;					
				}
				if ( $info['htaccess_protection']['prevent_unauthorized_browsing'] ) {
					$overall = $overall + 4;
				}
				if ( $info['htaccess_protection']['file_injection_protection'] ) {
					$overall = $overall + 4;
				}
				if ( $info['htaccess_protection']['self_environ'] ) {
					$overall = $overall + 4;
				}
				if ( $info['htaccess_protection']['xframe_options'] ) {
					$overall = $overall + 2;
				}
				if ( $info['htaccess_protection']['prevent_mime_attacks'] ) {
					$overall = $overall + 2;
				}
				if ( $info['htaccess_protection']['default_banned_list'] ) {
					$overall = $overall + 3;
				}
				if ( $info['htaccess_protection']['disable_server_signature'] ) {
					$overall = $overall + 3;
				}
				if ( $info['htaccess_protection']['disallow_php_eggs'] ) {
					$overall = $overall + 3;					
				}
				if ( $info['htaccess_protection']['disallow_sensible_files_access'] ) {
					$overall = $overall + 3;					
				}
					
			} else {
				return 2;
			}
			break;		
	}
	return $overall;
}

}