<?php
/**
 * Modelo Firewalllists para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Pagination\Pagination as JPagination;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Http\HttpFactory as JHttpFactory;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;

JLoader::register('SecuritycheckproModel', JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model.php');

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelFirewallConfig extends SecuritycheckproModel
{
    
    private $config = null;

    /* Función que filtra un array según un parámetro de búsqueda y paginación */
    function filter_data($array_data,&$pagination)
    {
        /* Inicializamos las variables */
        $this->total = 0;
    
        /* Obtenemos el número de registros del array que hemos de mostrar. Si el límite superior es '0', entonces devolvemos todo el array (limitado a 500 para evitar un desbordamiento) */
        $upper_limit = $this->getState('limitstart');
        $lower_limit = $this->getState('limit');
        
        if ($lower_limit == 0) {
            $lower_limit=500;
        }
		    
        /* Obtenemos los valores de los filtros */
        $search = htmlentities($this->state->get('filter.lists_search'));
    
        /* Número total de elementos en el array (necesario para la paginación) */
        $this->total = count($array_data);
        
        $filtered_array = array();
        /* Si el campo 'search' no está vacío, buscamos en todos los campos del array */            
        if (!empty($search)) {
            $filtered_array = array_values(
                array_filter(
                    $array_data, function ($element) use ($search) {
                        return (strstr($element, $search));
                    }
                )
            );
            /* Cortamos el array para mostrar sólo los valores mostrados por la paginación */
            $array_data = array_splice($filtered_array, $upper_limit, $lower_limit);
        } else {
            $array_data = array_splice($array_data, $upper_limit, $lower_limit);
        
        }
        $pagination = new JPagination($this->total, $upper_limit, $lower_limit);
        //sort($array_data,SORT_NUMERIC);
        natsort($array_data);
        return ($array_data);
    }

    /* Función que elimina IPs de la lista negra dinámica */
    function deleteip_dynamic_blacklist()
    {
        // Inicializamos las variables
        $deleted_elements = 0;
        $db = JFactory::getDBO();
    
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos los valores de las IPs que serán eliminados de la lista negra dinámica
        $uids = $jinput->get('dynamic_blacklist_table', '0', 'array');
        
        foreach($uids as $uid) {
            // IP sanitizada
            $ip_to_delete = $db->Quote($db->escape($uid));
            // Borramos la IP de la tabla
            $query = "DELETE FROM #__securitycheckpro_dynamic_blacklist WHERE (ip = {$ip_to_delete})";
            $db->setQuery($query);
            $result = $db->execute();
            if ($result) {
                  $deleted_elements++;
            }        
        }
        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_DELETED_FROM_LIST', $deleted_elements));
    }

    /* Función que chequea si la opción de control center está habilitada en el firewall */
    function control_center_enabled()
    {
        $db = JFactory::getDBO();
        try {
            $query = $db->getQuery(true);
            $query 
                ->select($db->quoteName('storage_value'))
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('controlcenter'));
            $db->setQuery($query);
            $res = $db->loadResult();
        } catch (Exception $e) {
            return false;    
        }
		        
        if(!empty($res)) {
            try {
				$res = json_decode($res, true);
				return $res['control_center_enabled'];
			} catch (Exception $e) {
				return false;    
			}			
        }
        
		return false;         
        
    }

    /* Función que añade una ip al fichero que será consumido por el control center si el plugin 'Connect' está habilitado */
    function añadir_info_control_center($ip,$option) 
    {
        // Ruta al fichero de información
        $folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans';
        
        if (@file_exists(($folder_path . DIRECTORY_SEPARATOR . 'cc_info.php'))) {            
            $str=file_get_contents($folder_path . DIRECTORY_SEPARATOR . 'cc_info.php');
            // Eliminamos la parte del fichero que evita su lectura al acceder directamente
            $str = str_replace("#<?php die('Forbidden.'); ?>", '', $str);
            $info_to_add = json_decode($str, true);
            
            if (!$info_to_add) {
                $info_to_add = array(
                 'dynamic_blacklist'    =>    array(),
                 'blacklist'        => array(),        
                 'whitelist'        => array()
                );
                
                array_push($info_to_add[$option], $ip);
                $info_to_add = json_encode($info_to_add);
            } else
            {
                try
                 {
                    array_push($info_to_add[$option], $ip);                    
                    $info_to_add = json_encode($info_to_add);
                } catch (Exception $e)
                {                
                    return false;    
                }
            }           
                
        } else 
		{
			$info_to_add = array(
                 'dynamic_blacklist'    =>    array(),
                 'blacklist'        => array(),        
                 'whitelist'        => array()
            );
                
            array_push($info_to_add[$option], $ip);
            $info_to_add = json_encode($info_to_add);
		}
		// Sobreescribimos el contenido del fichero
        file_put_contents($folder_path . DIRECTORY_SEPARATOR . 'cc_info.php', "#<?php die('Forbidden.'); ?>" . PHP_EOL . $info_to_add);
		
		// Let's get control center url to send the data
		$control_center_config = $this->getControlCenterConfig();
		$control_center_url = $control_center_config['control_center_url'];	
		
		// Launch 'Connect' task to add the ips to remote managed websites
		include_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'json.php';
		$frontend_model = new SecuritycheckProsModelJson;
		$frontend_model->Connect($control_center_url);		
    }

    /* Función para añadir una ip a una lista */
    function manage_list($type,$action,$ip=null,$check_own=true,$remote=false)
    {

        // Inicializamos las variables
        $query = null;
        $array_size = 0;
        $added_elements = 0;
        $deleted_elements = 0;
        $ip_to_add = null;
        $uids = null;
        $database = "#__securitycheckpro_" . $type;  
		 
        $db = JFactory::getDBO();
    
        // Podemos pasar la IP como argumento; en ese caso no necesitamos capturar los valores del formulario
        if (is_null($ip)) {
            // Creamos el objeto JInput para obtener las variables del formulario
            $jinput = JFactory::getApplication()->input;
        } 
    
                
        switch ($action) {
        case "add":
            // Obtenemos el valor de la IP introducida
            if ($type == 'blacklist') {
                if (is_null($ip)) {
                    $ip_to_add = $jinput->get('blacklist_add_ip', '0.0.0.0', 'string');
                } else {
                    $ip_to_add = $ip;
                }                
            } else if ($type == 'whitelist') {
                if (is_null($ip)) {
                    $ip_to_add = $jinput->get('whitelist_add_ip', '0.0.0.0', 'string');
                } else {
                    $ip_to_add = $ip;
                }                
            }
            
            // Chequeamos el formato de la entrada
            //IPv4
            if (strstr($ip_to_add, '*')) { // Si existe algún comodín, lo reemplazamos por el dígito '0'
                $ip_to_add_filtered= str_replace('*', '0', $ip_to_add);
                $ip_valid = filter_var($ip_to_add_filtered, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            } //IPv4/IPv6 CIDR
            else if (strstr($ip_to_add, '/')) { // Formato CIDR
                $ip_without_cidr = strstr($ip_to_add, '/', true);
                $ip_valid = filter_var($ip_without_cidr, FILTER_VALIDATE_IP);                
            }        
            else {
                $ip_valid = filter_var($ip_to_add, FILTER_VALIDATE_IP);                
            }
            
            if (!$ip_valid) {
                if (!$remote) {
                    JFactory::getApplication()->enqueueMessage(JText::_("COM_SECURITYCHECKPRO_INVALID_FORMAT"), 'warning');
                    break;
                } else {
                    return JText::_('COM_SECURITYCHECKPRO_INVALID_FORMAT');
                }
            }
                       
			// Contribution of George Acu - thanks!
            // Get the client IP to see if the user wants to block his own IP
            $client_ip = "";
            if (isset($_SERVER['HTTP_TRUE_CLIENT_IP']))
			{
				# CloudFlare specific header for enterprise paid plan, compatible with other vendors
				$client_ip = $_SERVER['HTTP_TRUE_CLIENT_IP']; 
			} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
			{
				# another CloudFlare specific header available in all plans, including the free one
				$client_ip = $_SERVER['HTTP_CF_CONNECTING_IP']; 
			} elseif (isset($_SERVER['HTTP_INCAP_CLIENT_IP'])) 
			{
				// Users of Incapsula CDN
				$client_ip = $_SERVER['HTTP_INCAP_CLIENT_IP']; 
			} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
			{
				# specific header for proxies
				$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR']; 
				$result_ip_address = explode(', ', $client_ip);
                $client_ip = $result_ip_address[0];
			} elseif (isset($_SERVER['REMOTE_ADDR']))
			{
				# this one would be used, if no header of the above is present
				$client_ip = $_SERVER['REMOTE_ADDR']; 
			}
                        
            // Si la IP es la del cliente no la añadimos para no bloquearnos, excepto cuando la petición provenga del url inspector
            if ($check_own) {
                if (($ip_to_add == $client_ip) && ($type == 'blacklist')) {
                    if (is_null($ip)) {
                         JFactory::getApplication()->enqueueMessage(JText::_("COM_SECURITYCHECKPRO_CANT_ADD_YOUR_OWN_IP"), 'warning');
                         break;
                    } else {
                        if ($remote) {
                            return JText::_('COM_SECURITYCHECKPRO_CANT_ADD_YOUR_OWN_IP');
                        }
                    }
                    
                }
            }                
                        
            $aparece_lista = $this->chequear_ip_en_lista($ip_to_add, $type);
            if (!$aparece_lista) {
                $object = (object)array(
					'ip'        => $ip_to_add
				);
				
				try{
					$db->insertObject($database, $object);
					$added_elements++;
				} catch (Exception $e)
				{    		
					return false;
				}
                
            }
            
            if ($added_elements > 0) {                
                if (!$remote) {
                    JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_ADDED_TO_LIST', $added_elements));
                    // Chequeamos si hemos de añadir la ip al fichero que será consumido por el plugin 'connect'
                    $control_center_enabled = $this->control_center_enabled();                
                    if ($control_center_enabled) {						
                         $this->añadir_info_control_center($ip_to_add, $type);
                    }
                } 
            } else {
                if (is_null($ip)) {
                    if (!$remote) {
                         JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_IGNORED', 1), 'notice');
                    }
                }
            }
            break;
        case "delete":
            // Obtenemos los valores de las IPs que serán introducidas en la lista negra
            if ($type == 'blacklist') {
                $uids = $jinput->get('cid', '0', 'array');
            } else if ($type == 'whitelist') {
                $uids = $jinput->get('whitelist_cid', '0', 'array');
            }
                        
            
            if ($uids != 0) {
                foreach($uids as $uid) {
					$ip_to_delete = $db->Quote($db->escape($uid));
					// Borramos la IP de la tabla
					$query = "DELETE FROM " . $database . " WHERE (ip = {$ip_to_delete})";
					$db->setQuery($query);
					$result = $db->execute();
					if ($result) {
						  $deleted_elements++;
					}                            
                }
                if ($deleted_elements > 0) {
                    JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_DELETED_FROM_LIST', $deleted_elements));
					if (!empty($list_to_array)) {
						sort($list_to_array, SORT_NUMERIC);
						$params[$type] = implode(',', $list_to_array);
					}                    
                }
            }
            break;
        }        
    }   

    /* Función que cambia los wildcards para extraer la geolocalización */
    function change_wildcards($ip)
    {
        $ip_without_wildcards = '';
        $array_ip_peticionaria = explode('.', $ip); // Formato array:  $array_ip_lista[0] = '192' , $array_ip_lista[1] = '168'
        
        if (strrchr($ip, '*')) { // Chequeamos si existe el carácter '*' en el string; si no existe podemos ignorar esta ip
            $k = 0;
            while ($k <= 3)  {
                if ($array_ip_peticionaria[$k] == '*') {
                    $array_ip_peticionaria[$k] = 0;                    
                }
                $k++;
            }
            $ip_without_wildcards = implode('.', $array_ip_peticionaria);
            return $ip_without_wildcards;
        } else {
            return $ip;
        }
        
    }
	
    /* Función que sube un fichero de IPs de la extensión Securitycheck Pro (previamente exportado) y lo añade a la bbdd */
    function import_list()
    {
        $res = true;
        $secret_key = "";
    
        // Get the uploaded file information
        $jinput = JFactory::getApplication()->input;    
        $lista = $jinput->get('import', null);
		$file_to_import = "file_to_import_" . $lista;				
		$userfile = $jinput->files->get($file_to_import);
		$database = "#__securitycheckpro_" . $lista;
			
		
		// Not know which list sahll we use
        if ( empty($lista) ) {
            JFactory::getApplication()->enqueueMessage(JText::_("No list defined"), 'warning');
            return false;
        }
        
        // Make sure that file uploads are enabled in php
        if (!(bool) ini_get('file_uploads')) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE"), 'warning');
            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!is_array($userfile)) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED"), 'warning');
            return false;
        }
    
        //First check if the file has the right extension, we need txt only
        if (!(strtolower(JFile::getExt($userfile['name'])) == 'txt')) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_SECURITYCHECKPRO_INVALID_FILE_EXTENSION"), 'warning');
            return false;
        }

        // Check if there was a problem uploading the file.
        if ($userfile['error'] || $userfile['size'] < 1) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR"), 'warning');
            return false;
        }

        // Build the appropriate paths
        $config        = JFactory::getConfig();
        $tmp_dest    = $config->get('tmp_path') . '/' . $userfile['name'];
        $tmp_src    = $userfile['tmp_name'];

        // Move uploaded file
        jimport('joomla.filesystem.file');
        $upload_res = JFile::upload($tmp_src, $tmp_dest);
    
        // El fichero se ha subido correctamente
        if ($upload_res) {
            // Inicializamos las variables
            $insert = false;
            // Leemos el contenido del fichero
            $file_content = file_get_contents($tmp_dest);
            $file_content = filter_var($file_content, FILTER_SANITIZE_SPECIAL_CHARS);
            // Transformamos el contenido el array para validar las IPS
			$ip_to_validate = explode(",", $file_content);		
            $valid = true;
						
			$db = JFactory::getDBO();
        
            // Chequeamos si las IPs son válidas
            foreach($ip_to_validate as $ip) {
                $ip_valid = filter_var($ip, FILTER_VALIDATE_IP);				
                if (!$ip_valid) {                
                    JFactory::getApplication()->enqueueMessage(JText::_("COM_SECURITYCHECKPRO_INVALID_FILE_FORMAT") . $ip_valid, 'warning');
                    return false;
                } else {
					$object = (object)array(
						'ip'        => $ip_valid
					);
				
					try{
						$db->insertObject($database, $object);					
					} catch (Exception $e)
					{    		
						JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
					}
				}
            }
             // Borramos el archivo subido...
			try{		
				JFile::delete($tmp_dest);
			} catch (Exception $e)
			{
			}                
             // ... y mostramos un mensaje de éxito
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_IMPORT_SUCCESSFULLY'));        
            
        } else {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR"), 'warning');
            return false;            
        }
    
        return $res;
    }

    /* Función que manda un email de prueba utilizando los parámetros establecidos */
    function send_email_test()
    {
        // Obtenemos las variables del formulario...
        $jinput = JFactory::getApplication()->input;
        $data = $jinput->getArray($_POST);
    
        //... y las filtramos
        $subject = filter_var($data['email_subject'], FILTER_SANITIZE_STRING);
        $body = JText::_('COM_SECURITYCHECKPRO_EMAIL_TEST_BODY');
    
        $email_to = $data['email_to'];
        $to = explode(',', $email_to);
    
        $email_from_domain = filter_var($data['email_from_domain'], FILTER_SANITIZE_EMAIL);
        $email_from_name = filter_var($data['email_from_name'], FILTER_SANITIZE_STRING);
        $from = array($email_from_domain,$email_from_name);

        $send = true;
    
        try {
            // Invocamos la clase JMail
            $mailer = JFactory::getMailer();
            // Emisor
            $mailer->setSender($from);
            // Destinatario -- es una array de direcciones
            $mailer->addRecipient($to);
            // Asunto
            $mailer->setSubject($subject);
            // Cuerpo
            $mailer->setBody($body);
            // Opciones del correo
            $mailer->isHTML(true);
            $mailer->Encoding = 'base64';
            // Enviamos el mensaje
            $send = $mailer->Send();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e, 'notice');
            $send = false;
        }
                    
        // Añadimos un mensaje de que todo ha funcionado correctamente
        if ($send === true) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EMAIL_SENT_SUCCESSFULLY', $email_to));
        }
    }

    /* Función que chequea si el plugin pasado como argumento está instalado */
    public function is_plugin_installed($folder,$plugin_name)
    {
        // Inicializamos las variables
        $installed= false;
    
        $plugin = JpluginHelper::getPlugin($folder, $plugin_name);
    
        // Si el valor devuelto es un array, entonces el plugin no existe o no está habilitado
        if (!is_array($plugin)) {
            $installed = true;        
        }
    
        return $installed;
    }

}
