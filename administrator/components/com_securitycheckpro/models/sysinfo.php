<?php
/**
 * Modelo Securitycheckpros para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo est� inclu�do en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Version as JVersion;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

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
        if (is_null($this->info)) {
            $this->info = array();
            $version = new JVersion;
            $db = JFactory::getDBO();
                        
            // Obtenemos el tama�o de la variable 'max_allowed_packet' de Mysql
            $db->setQuery('SHOW VARIABLES LIKE \'max_allowed_packet\'');
            $keys = $db->loadObjectList();
            $array_val = get_object_vars($keys[0]);
            $tamanno_max_allowed_packet = (int) ($array_val["Value"]/1024/1024);
                
            // Obtenemos el tama�o m�ximo de memoria establecido
            $params = JComponentHelper::getParams('com_securitycheckpro');
            $memory_limit = $params->get('memory_limit', '512M');
        
            // Obtenemos las opciones de configuraci�n
            include_once JPATH_ROOT.'/components/com_securitycheckpro/models/json.php';
            $values = new SecuritycheckProsModelJson();
            $values->getStatus(false);
        
            // Obtenemos las opciones del Cpanel
            include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/cpanel.php';
            $CpanelOptions = new SecuritycheckprosModelCpanel();
            $firewall_plugin_enabled = $CpanelOptions->PluginStatus(1);
            $cron_plugin_enabled = $CpanelOptions->PluginStatus(2);
            $spam_protection_plugin_enabled = $CpanelOptions->PluginStatus(5);
        
            // Obtenemos los par�metros del Firewall
            include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/library/model.php';
            $FirewallOptions = new SecuritycheckproModel();
            $FirewallOptions = $FirewallOptions->getConfig();        
                
            // Obtenemos las opciones de protecci�n .htaccess
            include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/protection.php';
            $ConfigApplied = new SecuritycheckprosModelProtection();
            $ConfigApplied = $ConfigApplied->GetConfigApplied();
                        
            $this->info['phpversion']    = phpversion();
            $this->info['version']        = $version->getLongVersion();
            //$this->info['platform']        = $platform->getLongVersion();
            $this->info['platform']        = "Not defined";
            $this->info['max_allowed_packet']        = $tamanno_max_allowed_packet;
            $this->info['memory_limit']        = $memory_limit;
            //Security
            $this->info['coreinstalled']        = $values->data['coreinstalled'];
            $this->info['corelatest']        = $values->data['corelatest'];
            $this->info['files_with_incorrect_permissions']        = $values->data['files_with_incorrect_permissions'];
            $this->info['files_with_bad_integrity']        = $values->data['files_with_bad_integrity'];
            $this->info['vuln_extensions']        = $values->data['vuln_extensions'];
            $this->info['suspicious_files']        = $values->data['suspicious_files'];
            $this->info['backend_protection']    = $values->data['backend_protection'];
            // Existe el fichero kickstart.php
            $this->info['kickstart_exists']        = $values->data['kickstart_exists'];
            $this->info['firewall_options']        = $FirewallOptions;
            $this->info['twofactor_enabled']    = $values->data['twofactor_enabled'];
            $this->info['overall_joomla_configuration']        = $values->data['overall'];
            //Extension status
            $this->info['cron_plugin_enabled']        = $cron_plugin_enabled;
            $this->info['firewall_plugin_enabled']        = $firewall_plugin_enabled;
            $this->info['spam_protection_plugin_enabled']        = $spam_protection_plugin_enabled;
            //$this->info['firewall_options']        = $FirewallOptions;
            $this->info['last_check']        = $values->data['last_check'];
            $this->info['last_check_integrity']        = $values->data['last_check_integrity'];        
            //Htaccess protection
            $this->info['htaccess_protection']        = $ConfigApplied;
            $this->info['overall_web_firewall']        = $this->getOverall($this->info, 2);        
        
        }
        return $this->info;
    }

    // Obtiene el porcentaje general de cada una de las barras de progreso
    public function getOverall($info,$opcion)
    {
        // Inicializamos variables
        $overall = 0;
    
        switch ($opcion)
        {
        // Porcentaje de progreso de  Joomla Configuration
        case 1:
            if ($info['kickstart_exists']) {
                return 2;
            }
            if (version_compare($info['coreinstalled'], $info['corelatest'], '==')) {
                $overall = $overall + 4;
            }
            if ($info['files_with_incorrect_permissions'] == 0) {
                $overall = $overall + 5;
            }
            if ($info['files_with_bad_integrity'] == 0) {
                $overall = $overall + 10;
            }
            if ($info['vuln_extensions'] == 0) {
                $overall = $overall + 30;
            }
            if ($info['suspicious_files'] == 0) {
                $overall = $overall + 20;
            }
            if ($info['backend_protection']) {
                $overall = $overall + 10;
            }
            if ($info['firewall_options']['forbid_new_admins'] == 1) {
                $overall = $overall + 5;
            }            
            if ($info['twofactor_enabled'] >= 1) {
                $overall = $overall + 10;
            }
            if ($info['htaccess_protection']['xframe_options'] == 1) {
                $overall = $overall + 1;
            }
            if ($info['htaccess_protection']['sts_options'] == 1) {
                $overall = $overall + 1;
            }
            if ($info['htaccess_protection']['xss_options'] == 1) {
                $overall = $overall + 1;
            }
            if ($info['htaccess_protection']['csp_policy'] == 1) {
                $overall = $overall + 1;
            }
            if ($info['htaccess_protection']['referrer_policy'] == 1) {
                $overall = $overall + 1;
            }
            if ($info['htaccess_protection']['prevent_mime_attacks'] == 1) {
                $overall = $overall + 1;
            }
            break;
        case 2:
            if ($info['firewall_plugin_enabled']) {
                $overall = $overall + 10;                
                // Configuraci�n del firewall
                if ($info['firewall_options']['dynamic_blacklist']) {
                    $overall = $overall + 10;                    
                }
                if ($info['firewall_options']['logs_attacks']) {
                    $overall = $overall + 2;                    
                }
                if ($info['firewall_options']['second_level']) {
                    $overall = $overall + 2;                    
                }
                if (!(strstr($info['firewall_options']['strip_tags_exceptions'], '*'))) {
                    $overall = $overall + 4;                    
                }
                if (!(strstr($info['firewall_options']['sql_pattern_exceptions'], '*'))) {
                    $overall = $overall + 4;                                        
                }
                if (!(strstr($info['firewall_options']['lfi_exceptions'], '*'))) {
                    $overall = $overall + 4;                                        
                }
                if ($info['firewall_options']['session_protection_active']) {
                    $overall = $overall + 2;                    
                }
                if ($info['firewall_options']['session_hijack_protection']) {
                    $overall = $overall + 2;                    
                }
                if ($info['firewall_options']['upload_scanner_enabled']) {
                    $overall = $overall + 4;                    
                }
                if ($info['spam_protection_plugin_enabled']) {
                    $overall = $overall + 2;                    
                }
                
                // Cron 
                $last_check = $this->info['last_check'];
				$now = $this->get_Joomla_timestamp();
				
				$seconds = strtotime($now) - strtotime($last_check);
				// Extraemos los d�as que han pasado desde el �ltimo chequeo
				$interval = intval($seconds/86400);	
                                   
                if ($interval < 2) {
                    $overall = $overall + 10;                    
                } else
                {
                    
                }
                
                $last_check_integrity = $this->info['last_check_integrity'];
				$seconds = strtotime($now) - strtotime($last_check_integrity);
				// Extraemos los d�as que han pasado desde el �ltimo chequeo
				$interval = intval($seconds/86400);
                                                                                        
                if ($interval < 2) {
                    $overall = $overall + 10;                    
                } else
                {
                    
                }
                // Htaccess protection
                if ($info['htaccess_protection']['prevent_access']) {
                    $overall = $overall + 6;                    
                }
                if ($info['htaccess_protection']['prevent_unauthorized_browsing']) {
                    $overall = $overall + 4;
                }
                if ($info['htaccess_protection']['file_injection_protection']) {
                    $overall = $overall + 4;
                }
                if ($info['htaccess_protection']['self_environ']) {
                    $overall = $overall + 4;
                }
                if ($info['htaccess_protection']['xframe_options']) {
                    $overall = $overall + 2;
                }
                if ($info['htaccess_protection']['prevent_mime_attacks']) {
                    $overall = $overall + 2;
                }
                if ($info['htaccess_protection']['default_banned_list']) {
                    $overall = $overall + 3;
                }
                if ($info['htaccess_protection']['disable_server_signature']) {
                    $overall = $overall + 3;
                }
                if ($info['htaccess_protection']['disallow_php_eggs']) {
                    $overall = $overall + 3;                    
                }
                if ($info['htaccess_protection']['disallow_sensible_files_access']) {
                    $overall = $overall + 3;                    
                }                    
            } else 
            {
                return 2;
            }
            break;        
        }
        return $overall;
    }

}
