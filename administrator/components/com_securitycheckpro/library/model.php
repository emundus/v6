<?php
/**
 * @version 1.5.0
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No Permission
defined('_JEXEC') or die('Restricted access');


use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;


define('SCP_CACERT_PEM', __DIR__ . '/cacert.pem');
define('SCP_USER_AGENT', 'Securitycheck Pro User agent');

class SecuritycheckproModel extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{

    /**
     Array de datos
     *
     @var array
     */
    var $_data;
    /**
     Total items
     *
     @var integer
     */
    var $_total = null;
    /**
     Objeto Pagination
     *
     @var object
     */
    var $_pagination = null;
    /**
     Columnas de #__securitycheck
     *
     @var integer
     */
    var $_dbrows = null;

    private $config = null;

    private $defaultConfig = array(
    'dynamic_blacklist'        => 1,
    'dynamic_blacklist_time'        => 600,
    'dynamic_blacklist_counter'        => 5,
    'blacklist_email'        => 0,
    'priority1'        => 'Whitelist',
    'priority2'        => 'DynamicBlacklist',
    'priority3'        => 'Blacklist',
    'methods'            => 'GET,POST,REQUEST',
    'mode'            => 1,
    'logs_attacks'            => 1,
    'scp_delete_period'            => 60,    
    'log_limits_per_ip_and_day'            => 0,
    'redirect_after_attack'            => 1,
    'redirect_options'            => 1,
    'redirect_url'            => '',
    'custom_code'            => 'The webmaster has forbidden your access to this site',
    'second_level'            => 1,
    'second_level_redirect'            => 1,
    'second_level_limit_words'            => 3,
    'second_level_words'            => 'ZHJvcCx1cGRhdGUsc2V0LGFkbWluLHNlbGVjdCx1c2VyLHBhc3N3b3JkLGNvbmNhdCxsb2dpbixs
b2FkX2ZpbGUsYXNjaWksY2hhcix1bmlvbixmcm9tLGdyb3VwIGJ5LG9yZGVyIGJ5LGluc2VydCx2
YWx1ZXMscGFzcyx3aGVyZSxzdWJzdHJpbmcsYmVuY2htYXJrLG1kNSxzaGExLHNjaGVtYSx2ZXJz
aW9uLHJvd19jb3VudCxjb21wcmVzcyxlbmNvZGUsaW5mb3JtYXRpb25fc2NoZW1hLHNjcmlwdCxq
YXZhc2NyaXB0LGltZyxzcmMsaW5wdXQsYm9keSxpZnJhbWUsZnJhbWUsJF9QT1NULGV2YWwsJF9S
RVFVRVNULGJhc2U2NF9kZWNvZGUsZ3ppbmZsYXRlLGd6dW5jb21wcmVzcyxnemluZmxhdGUsc3Ry
dHJleGVjLHBhc3N0aHJ1LHNoZWxsX2V4ZWMsY3JlYXRlRWxlbWVudA==',
    'email_active'            => 0,
    'email_subject'            => 'Securitycheck Pro alert!',
    'email_body'            => 'Securitycheck Pro has generated a new alert. Please, check your logs.',
    'email_add_applied_rule'            => 1,
    'email_to'            => 'youremail@yourdomain.com',
    'email_from_domain'            => 'me@mydomain.com',
    'email_from_name'            => 'Your name',
    'email_max_number'            => 20,
    'check_header_referer'            => 1,
    'check_base_64'            => 1,
    'base64_exceptions'            => 'com_hikashop',
    'strip_tags_exceptions'            => 'com_jdownloads,com_hikashop,com_phocaguestbook',
    'duplicate_backslashes_exceptions'            => 'com_kunena,com_securitycheckprocontrolcenter',
    'line_comments_exceptions'            => 'com_comprofiler',
    'sql_pattern_exceptions'            => '',
    'if_statement_exceptions'            => '',
    'using_integers_exceptions'            => 'com_dms,com_comprofiler,com_jce,com_contactenhanced,com_securitycheckprocontrolcenter',
    'escape_strings_exceptions'            => 'com_kunena,com_jce',
    'lfi_exceptions'            => '',
    'second_level_exceptions'            => '',    
    'session_protection_active'            => 1,
    'session_hijack_protection'            => 1,
	'session_hijack_protection_what_to_check'            => 0,
    'tasks'            => 'integrity',
    'launch_time'            => 2,
    'periodicity'            => 24,
    'control_center_enabled'    => '0',
    'secret_key'    => '',
	'control_center_url'    => '',
    'add_geoblock_logs'            => 0,
    'upload_scanner_enabled'    =>    1,
    'check_multiple_extensions'    =>    1,
    'extensions_blacklist'            => 'php,js,exe,xml',
	'mimetypes_blacklist'	=>	'application/x-dosexec,application/x-msdownload ,text/x-php,application/x-php,application/x-httpd-php,application/x-httpd-php-source,application/javascript,application/xml',
    'delete_files'            => 1,
    'actions_upload_scanner'    =>    0,
    'exclude_exceptions_if_vulnerable'    =>    1,
    'track_failed_logins'    =>    1,
    'write_log'    =>    1,
    'logins_to_monitorize'    =>    2,    
    'actions_failed_login'    =>    1,
    'session_protection_groups'    => array('0' => '8'),
    'backend_exceptions'    =>    '',
    'email_on_admin_login'    =>    0,
    'forbid_admin_frontend_login'    =>    0,
    'add_access_attempts_logs'    =>    0,
    'check_if_user_is_spammer'    =>    1,
    'spammer_action'    =>    1,
    'spammer_write_log'    =>    0,
    'spammer_limit'    =>    3,
    'forbid_new_admins'    => 0,
    'spammer_what_to_check'    => array('Email','IP','Username'),
    'strip_all_tags'    =>    1,
    'tags_to_filter'            => 'applet,body,bgsound,base,basefont,embed,frame,frameset,head,html,id,iframe,ilayer,layer,link,meta,name,object,script,style,title,xml,svg,input,a',
    'inspector_forbidden_words'    => 'wp-login.php,.git,owl.prev,tmp.php,home.php,Guestbook.php,aska.cgi,default.asp,jax_guestbook.php,bbs.cg,gastenboek.php,light.cgi,yybbs.cgi,wsdl.php,wp-content,cache_aqbmkwwx.php,.suspected,seo-joy.cgi,google-assist.php,wp-main.php,sql_dump.php,xmlsrpc.php',
    'write_log_inspector'    => 1,
    'action_inspector'    =>    2,
    'send_email_inspector'    =>    0,
    'delete_period'    => 0,
    'ip_logging'    =>    0,
    'loggable_extensions'    => array('0' => 'com_banners','1' => 'com_cache','2' => 'com_categories','3' => 'com_config','4' => 'com_contact','5' => 'com_content','6' => 'com_installer','7' => 'com_media','8' => 'com_menus','9' => 'com_messages','10' => 'com_modules','11' => 'com_newsfeeds','12' => 'com_plugins','13' => 'com_redirect','14' => 'com_tags','15' => 'com_templates','16' => 'com_users')
    );


    function __construct()
    {
        parent::__construct();

        global $mainframe, $option;
        
        $mainframe = JFactory::getApplication();
		
		// This is needed to avoid errors getting the file from cli
		if ( (!empty($mainframe)) && (method_exists($mainframe,"getUserStateFromRequest")) ) {
			$jinput = $mainframe->input;
	 
			// Obtenemos las variables de paginación de la petición
			$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		
			$data = $jinput->get('post');
			$limitstart = $jinput->get('limitstart', 0, 'int');
		
			// En el caso de que los límites hayan cambiado, los volvemos a ajustar
			$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		
			$this->setState('limit', $limit);
			$this->setState('limitstart', $limitstart);     
		}
    }

    protected function populateState()
    {
        // Inicializamos las variables
        $app        = JFactory::getApplication();
    
        $extension_type = $app->getUserStateFromRequest('filter.extension_type', 'filter_extension_type');
        $this->setState('filter.extension_type', $extension_type);
        $lists = $app->getUserStateFromRequest('filter.lists_search', 'filter_lists_search');
        $this->setState('filter.lists_search', $lists);
                
        parent::populateState();
    }
	
	/* Obtiene los elementos de una tabla pasada como argumento */
    function getTableData($table)
    {
        $db = JFactory::getDBO();
		$database = "#__securitycheckpro_" . $table;
		$array_ips = null;
		
		try{
			$query = "SELECT * FROM " . $database;
			$db->setQuery($query);
			$db->execute();
			$array_ips = $db->loadColumn();		
		} catch (Exception $e)
        {    		
			return null;
        }
        return $array_ips;    
    }

    /* Obtiene el valor de una opción de configuración */
    public function getValue($key, $default = null, $key_name = 'cparams')
    {
        if (is_null($this->config)) { $this->load($key_name);
        }
    
        if (version_compare(JVERSION, '3.0', 'ge')) {
            return $this->config->get($key, $default);
        } else
        {
            return $this->config->getValue($key, $default);
        }
    }

    /* Establece el valor de una opción de configuración ' */
    public function setValue($key, $value, $save = false, $key_name = 'cparams')
    {
        if (is_null($this->config)) {
            $this->load($key_name);
        }
        
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $x = $this->config->set($key, $value);			
        } else
        {
            $x = $this->config->setValue($key, $value);			
        }
    
        if($save) { $this->save($key_name);
        }
        return $x;
    }

    /* Hace una consulta a la tabla espacificada como parámetro ' */
    public function load($key_name)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query 
            ->select($db->quoteName('storage_value'))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote($key_name));
        $db->setQuery($query);
        $res = $db->loadResult();
        
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $this->config = new JRegistry();
        } else
        {
            $this->config = new JRegistry('securitycheckpro');
        }
        if (!empty($res)) {
            $res = json_decode($res, true);
            $this->config->loadArray($res);
        }
    }

    /* Guarda la configuración en la tabla pasada como parámetro */
    public function save($key_name)
    {
        if (is_null($this->config)) {
            $this->load($key_name);
        }
        
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
		$query2 = $db->getQuery(true);
    
        $data = $this->config->toArray();
		
		if ($key_name != 'inspector') {        
            // Chequeamos si los valores de prioridad son nulos; si lo son, les asignamos un valor
            if ((array_key_exists("priority1", $data)) && (is_null($data['priority1'])) || (!array_key_exists("priority1", $data))) {
                $data['priority1'] = 'Whitelist';
            }
            if ((array_key_exists("priority2", $data)) && (is_null($data['priority2'])) || (!array_key_exists("priority2", $data))) {
                $data['priority2'] = 'Blacklist';
            }
            if ((array_key_exists("priority3", $data)) && (is_null($data['priority3'])) || (!array_key_exists("priority3", $data))) {
                $data['priority3'] = 'DynamicBlacklist';
            }            
            
            if (($data['priority1'] == $data['priority2']) || ($data['priority1'] == $data['priority3']) || ($data['priority2'] == $data['priority3'])) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_DUPLICATE_OPTIONS'), 'warning');
                return;
            }
        
            // Borramos el índice 'priority', correspondiente a versiones anteriores a la 2.8.5
            if (array_key_exists("priority", $data)) {
                unset($data['priority']);
            }
        }
        
        $data = json_encode($data);
		
		if ($data !== false) {
			//Json is valid			
			
			//Get the previous value
			$query2->select('storage_value');
            $query2->from('#__securitycheckpro_storage');
            $query2->where($db->quoteName('storage_key').' = '.$db->quote($key_name));
			$db->setQuery($query2);
			$db->execute();
			$previous_data = $db->loadResult();
									
			try {
				//delete stored value
				$query
					->delete($db->quoteName('#__securitycheckpro_storage'))
					->where($db->quoteName('storage_key').' = '.$db->quote($key_name));
				$db->setQuery($query);
				$db->execute();
								
				$object = (object)array(
				'storage_key'        => $key_name,
				'storage_value'        => $data
				);
					
				$db->insertObject('#__securitycheckpro_storage', $object);
			
				JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_CONFIGSAVED'));
			} catch (Exception $e)
			{    
				// Let's restore the previous config
				$object = (object)array(
				'storage_key'        => $key_name,
				'storage_value'        => $previous_data
				);
					
				$db->insertObject('#__securitycheckpro_storage', $object);
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				
			} 
		} else {
			JFactory::getApplication()->enqueueMessage("Error", 'error');
		}
        
    }

    /* Obtiene la configuración de los parámetros del Firewall Web */
    function getConfig()
    {
        $params = JModel::getInstance('FirewallConfig', 'SecuritycheckProsModel');
            
        /* Si por alguna razón la variable $params no está definida, creamos un objeto para definirla */
        if (!$params) {        
            include_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'firewallconfig.php';
            $params = new SecuritycheckprosModelFirewallConfig();                
        } else
        {        
        }
    
        $config = array();
        foreach($this->defaultConfig as $k => $v)
        {			
            $config[$k] = $params->getValue($k, $v, 'pro_plugin');
        }		
        return $config;    
    }

    /* Obtiene la configuración de los parámetros del Cron */
    function getCronConfig()
    {
        $params = JModel::getInstance('FirewallConfig', 'SecuritycheckProsModel');
           
        $config = array();
        foreach($this->defaultConfig as $k => $v)
        {
            $config[$k] = $params->getValue($k, $v, 'cron_plugin');
        }
        return $config;
    }

    /* Obtiene la configuración de los parámetros del Control Center */
    public function getControlCenterConfig()
    {
        $params = JModel::getInstance('FirewallConfig', 'SecuritycheckProsModel');
           
        $config = array();
        foreach($this->defaultConfig as $k => $v)
        {
            $config[$k] = $params->getValue($k, $v, 'controlcenter');
        }
        return $config;
    }

    /* Guarda la modificación de los parámetros de la opción 'Mode' */
    function saveConfig($newParams, $key_name = 'cparams')
    {
        $params = JModel::getInstance('FirewallConfig', 'SecuritycheckProsModel');
		           
        foreach($newParams as $key => $value)
        {
            // Do not save unnecessary parameters
            if(!array_key_exists($key, $this->defaultConfig)) { continue;
            }        
            $params->setValue($key, $value, '', $key_name);
        }
    
        $params->save($key_name);    
    }

    /* Limpia un string de caracteres no válidos según la opción especificada */
    function clearstring($string_to_clear, $option = 1)
    {
        // Eliminamos espacios y retornos de carro entre los elementos
        switch ($option)
        {
        case 1:
            // Transformamos el string array para poder manejarlo mejor
            $string_to_array = explode(',', $string_to_clear);
            // Eliminamos los espacios en blanco al principio y al final de cada elemento
            $string_to_array = array_map(
                function ($element) {
                    return trim($element); 
                }, $string_to_array
            );
            // Eliminamos los retornos de carro, nuevas líneas y tabuladores de cada elemento
            $string_to_array = array_map(
                function ($element) {
                    return str_replace(array("\n", "\t", "\r"), '', $element); 
                }, $string_to_array
            );
            // Volvemos a convertir el array en string
            $string_to_clear = implode(',', $string_to_array);
            break;
        case 2:
            $string_to_clear = str_replace(array(" ", "\n", "\t", "\r"), '', $string_to_clear);
            break;
        } 
        
        return $string_to_clear;
    }

    /* Función para chequear si una ip pertenece a una lista en la que podemos especificar rangos. Podemos tener una ip del tipo 192.168.*.* y una ip 192.168.1.1 entraría en ese rango */
    function chequear_ip_en_lista($ip,$lista)
    {
        $aparece = false;
        $igual = false;
		$array_ips = null;
        $array_ip_peticionaria = explode('.', $ip);
		
		$db = JFactory::getDBO();
		$database = "#__securitycheckpro_" . $lista;
		
		try{
			$query = "SELECT * FROM " . $database;
			$db->setQuery($query);
			$db->execute();
			$array_ips = $db->loadColumn();		
		} catch (Exception $e)
        {    		
			return false;
        }
		    
        if ( !empty($array_ips) ) {            
            if (is_int(array_search($ip, $array_ips))) {    // La ip aparece tal cual en la lista
                  $aparece = true;
            } else
            {
                foreach ($array_ips as &$indice)
                {                                        
                    if (strrchr($indice, '*')) { // Chequeamos si existe el carácter '*' en el string; si no existe podemos ignorar esta ip
                        $array_ip_lista = explode('.', $indice); // Formato array:  $array_ip_lista[0] = '192' , $array_ip_lista[1] = '168'
                        $k = 0;
                        $igual = true;
                        while (($k <= 3) && ($igual))
                        {
                            if ($array_ip_lista[$k] == '*') {
                                $k++;
                            }else
                               {
                                if ($array_ip_lista[$k] == $array_ip_peticionaria[$k]) {
                                    $k++;
                                } else
                                {
                                    $igual = false;
                                }
                            }
                        }
                    }
                    if (strstr($indice, "/") != false) { // Chequeamos si existe el carácter '/' en el string (formato CIDR); si no existe podemos ignorar esta ip
                        include_once JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/helpers/ip.php';
        
                        $model = new SecuritycheckProsModelIP;
                            
                        // Extraemos la información del rango
                        $ip_range_info = $model->get_ip_info($indice);
                        
                        // Comprobamos si la IP tiene formato v4
                        $ip_v4 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
                        if ($ip_v4) {
                            // Comprobamos si la ipv4 ya aparece en las listas
                            //$aparece = $model->cidr_match($ip,$ip_range_info["network"],strstr($indice,"/"));                            
                            $aparece = $model->ip_in_range($ip, $indice);
                        }
                        // Comprobamos si la IP tiene formato v6
                        $ip_v6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
                        if ($ip_v6) {
                            // Comprobamos si la ipv6 ya aparece en las listas
                            $aparece =  $model->checkIPv6WithinRange($ip, $ip_range_info["network"] . strstr($indice, "/"));                            
                        }                                                

                    }
                    if ($igual) { // $igual será true cuando hayamos recorrido el array y todas las partes del mismo coincidan con la ip que realiza la petición
                           $aparece = true;
                           return $aparece;
                    }
                
                }
            }
        }
        return $aparece;
    }

	/**
		* Encrypt data using OpenSSL (AES-256-CBC)
		* Based on code from: https://stackoverflow.com/questions/3422759/php-aes-encrypt-decrypt
	*/
    function encrypt($plaindata, $encryption_key)
	{
		$method = "AES-256-CBC";
			
		$iv = openssl_random_pseudo_bytes(16);
			
		$hash_pbkdf2 = hash_pbkdf2("sha512", $encryption_key, "", 5000);
		$key = substr($hash_pbkdf2, 0, 256);
		$hashkey = substr($hash_pbkdf2, 256, 512);
			
		$cipherdata = openssl_encrypt($plaindata, $method, $key, OPENSSL_RAW_DATA, $iv);

		if ($cipherdata === false)
		{
			$cryptokey = "**REMOVED**";
			$hashkey = "**REMOVED**";
			throw new \Exception("Internal error: openssl_encrypt() failed:".openssl_error_string());
		}

		$hash = hash_hmac('sha256', $cipherdata.$iv, $hashkey, true);

		if ($hash === false)
		{
			$cryptokey = "**REMOVED**";
			$hashkey = "**REMOVED**";
			throw new \Exception("Internal error: hash_hmac() failed");
		}

		return base64_encode($iv.$hash.$cipherdata);
	}
	
	/**
		* Decrypt data using OpenSSL (AES-256-CBC)
		* Based on code from: https://stackoverflow.com/questions/3422759/php-aes-encrypt-decrypt
	*/
	function decrypt($encrypteddata, $encryption_key)
	{
		$method = "AES-256-CBC";
			
		$encrypteddata = base64_decode($encrypteddata);
			
		$iv = substr($encrypteddata, 0, 16);
		$hash = substr($encrypteddata, 16, 32);
		$cipherdata = substr($encrypteddata, 48);
							
		$hash_pbkdf2 = hash_pbkdf2("sha512", $encryption_key, "", 5000);
		$key = substr($hash_pbkdf2, 0, 256);
		$hashkey = substr($hash_pbkdf2, 256, 512);
			
		if (!hash_equals(hash_hmac('sha256', $cipherdata.$iv, $hashkey, true), $hash))
		{
			/*$cryptokey = "**REMOVED**";
			$hashkey = "**REMOVED**";
			throw new \Exception("Internal error: Hash verification failed");*/
			return "Internal error: Hash verification failed";
		}

		$plaindata = openssl_decrypt($cipherdata, $method, $key, OPENSSL_RAW_DATA, $iv);

		if ($plaindata === false)
		{
			/*$cryptokey = "**REMOVED**";
			$hashkey = "**REMOVED**";
			throw new \Exception("Internal error: openssl_decrypt() failed:".openssl_error_string());*/
			return "Internal error: openssl_decrypt() failed";
		}

		return $plaindata;
	}

    /* Función que modifica el valor de algún parámetro de un componente */
    function modify_component_value($param_name,$value,$option)
    {

        // Inicializamos las variables
        $added = true;
        $deleted = true;
        $already_exists = false;
        $new_value = null;
    
        // Get the params and set the new values
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $actual_values = $params->get($param_name, null);
            
        if ($option == "add") {        
            if (is_null($actual_values)) {
                  $actual_values =  $value;            
            } else 
            {
                if (strstr($actual_values, $value)) {  // El path ya se encuentra incluido como excepcion
                       $already_exists = true;
                }
                $actual_values .= "," . $value;
            }
        
            if (!$already_exists) {  // El elemento no existe en la lista
        
                $params->set($param_name, $actual_values);
            
                $componentid = JComponentHelper::getComponent('com_securitycheckpro')->id;
                $table = JTable::getInstance('extension');
                $table->load($componentid);
                $table->bind(array('params' => $params->toString()));
            
                // check for error
                if (!$table->check()) {
                    JFactory::getApplication()->enqueueMessage($table->getError(), 'error');
                    return false;
                }
                // Save to database
                if (!$table->store()) {
                    JFactory::getApplication()->enqueueMessage($table->getError(), 'error');
                    return false;
                }
            
                // Clean the component cache. Without these lines changes will not be reflected until cache expired.
                parent::cleanCache('_system', 0);
                parent::cleanCache('_system', 1);
            
            } else 
            {
                $added = false;
            }
        
            return $added;
        } else if ($option == "delete") {    
            if (is_null($actual_values)) {
                $actual_values =  $value;
            } else
            {            
                // Convertimos todas las excepciones en un array
                $array_values = explode(',', $actual_values);
            
                // Buscamos el índice del array que contiene la ruta que queremos borrar...
                $indice_elemento = array_search($value, $array_values);
            
                // ... y lo eliminamos
                unset($array_values[$indice_elemento]);
            
                // Reorganizamos el array...
                $new_array = array_values($array_values);            
            
                // ... y lo volvemos a convertir en string
                $new_value = implode(',', $new_array);
            
                // El valor se ha encontrado
                if (is_int($indice_elemento)) {
                    $params->set($param_name, $new_value);
                
                    $componentid = JComponentHelper::getComponent('com_securitycheckpro')->id;
                    $table = JTable::getInstance('extension');
                    $table->load($componentid);
                    $table->bind(array('params' => $params->toString()));
                
                    // check for error
                    if (!$table->check()) {
                         JFactory::getApplication()->enqueueMessage($table->getError(), 'error');
                         return false;
                    }
                    // Save to database
                    if (!$table->store()) {
                            JFactory::getApplication()->enqueueMessage($table->getError(), 'error');
                            return false;
                    }
            
                    // Clean the component cache. Without these lines changes will not be reflected until cache expired.
                    parent::cleanCache('_system', 0);
                    parent::cleanCache('_system', 1);                
                } else 
                {
                           $deleted = false;
                }
            
                return $deleted;
            }
        }
    }

    /* Función que añade una ruta  a la lista de excepciones */
    function addfile_exception($type)
    {
        // Inicializamos las variables
        $added_elements = 0;
        $already_exists_elements = 0;
        $option = 'file_integrity_path_exceptions';
    
        // Parámetros de la aplicación
        $params = JComponentHelper::getParams('com_securitycheckpro');
    
        $db = JFactory::getDBO();
    
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        if ($type == 'malwarescan') {
            // Obtenemos las rutas de los ficheros que serán añadidas como excepciones
            $paths = $jinput->get('malwarescan_status_table', '0', 'array');
        
            // ¿Usamos nuestra propia lista de excepciones o la del escaneo de integridad?
            $use_filemanager_exceptions = $params->get('use_filemanager_exceptions', 1);
            if (!$use_filemanager_exceptions) {
                  $option = 'malwarescan_path_exceptions';
            }
        } else if ($type == 'permissions') {
            // Obtenemos las rutas de los ficheros que serán añadidas como excepciones
            $paths = $jinput->get('filesstatus_table', '0', 'array');
        
            $option = 'file_manager_path_exceptions';
        } else if ($type == 'integrity') {
            // Obtenemos las rutas de los ficheros que serán añadidas como excepciones
            $paths = $jinput->get('filesintegritystatus_table', '0', 'array');        
        }
    
        if (!empty($paths)) {    
            foreach($paths as $path)
            {
                // Path sanitizada
                //$sanitized_path = $db->escape($path);
                // Agregamos el archivo a la lista de excepciones
                $added = $this->modify_component_value($option, $path, 'add');
                if ($added) {
                    $added_elements++;
                } else 
                {
                    $already_exists_elements++;
                }
            }
        }
    
        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_ADDED_TO_LIST', $added_elements));
        if ($added_elements > 0) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_ELEMENTS_LAUNCH_NEW_SCAN'), 'notice');
        }
        if ($already_exists_elements > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_ALREADY_EXISTS', $already_exists_elements), 'warning');
        }
    }

    /* Función que borra una ruta de la lista de excepciones */
    function deletefile_exception($type)
    {
        // Inicializamos las variables
        $deleted_elements = 0;
        $option = 'file_integrity_path_exceptions';
    
        // Parámetros de la aplicación
        $params = JComponentHelper::getParams('com_securitycheckpro');
    
        $db = JFactory::getDBO();
    
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        if ($type == 'malwarescan') {
            // Obtenemos las rutas de los ficheros que serán añadidas como excepciones
            $paths = $jinput->get('malwarescan_status_table', '0', 'array');
        
            // ¿Usamos nuestra propia lista de excepciones o la del escaneo de integridad?
            $use_filemanager_exceptions = $params->get('use_filemanager_exceptions', 1);
            if (!$use_filemanager_exceptions) {
                  $option = 'malwarescan_path_exceptions';
            }
        } else if ($type == 'permissions') {
            // Obtenemos las rutas de los ficheros que serán añadidas como excepciones
            $paths = $jinput->get('filesstatus_table', '0', 'array');
                
            $option = 'file_manager_path_exceptions';
        } else if ($type == 'integrity') {
            // Obtenemos las rutas de los ficheros que serán añadidas como excepciones
            $paths = $jinput->get('filesintegritystatus_table', '0', 'array');        
        }
    
        if (!empty($paths)) {    
            foreach($paths as $path)
            {
                // Path sanitizada
                $sanitized_path = $db->escape($path);
                // Agregamos el archivo a la lista de excepciones
                $deleted = $this->modify_component_value($option, $sanitized_path, 'delete');
                if ($deleted) {
                    $deleted_elements++;
                }
            }
        }
    
        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_DELETED_FROM_LIST', $deleted_elements));
        if ($deleted_elements > 0) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_ELEMENTS_LAUNCH_NEW_SCAN'), 'notice');
        }    
    }

    /*Genera un nombre de fichero .php  de 20 caracteres */
    function generateKey()
    {
    
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"; //available characters
        srand((double) microtime() * 1000000); //random seed
        $pass = '' ;
        
        for ($i = 1; $i <= 20; $i++) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
        }

        return $pass.'.php';    
    }

    /* Función para determinar si el plugin pasado como argumento ('1' -> Securitycheck Pro, '2' -> Securitycheck Pro Cron, '3' -> Securitycheck Pro Update Database) está habilitado o deshabilitado. También determina si el plugin Securitycheck Pro Update Database (opción 4)  está instalado */
    function PluginStatus($opcion)
    {
        
        $db = JFactory::getDBO();
        if ($opcion == 1) {
            $query = "SELECT enabled FROM #__extensions WHERE name='System - Securitycheck Pro'";
        } else if ($opcion == 2) {
            $query = "SELECT enabled FROM #__extensions WHERE name='System - Securitycheck Pro Cron'";
        } else if ($opcion == 3) {
            $query = "SELECT enabled FROM #__extensions WHERE name='System - Securitycheck Pro Update Database'";
        } else if ($opcion == 4) {
            $query = "SELECT COUNT(*) FROM #__extensions WHERE name='System - Securitycheck Pro Update Database'";
        } else if ($opcion == 5) {
            $query = "SELECT enabled FROM #__extensions WHERE name='System - Securitycheck Spam Protection'";
        } else if ($opcion == 6) {
            $query = "SELECT COUNT(*) FROM #__extensions WHERE name='System - Securitycheck Spam Protection'";
        } else if ($opcion == 7) {
            $query = "SELECT enabled FROM #__extensions WHERE name='System - url Inspector'";
        } else if ($opcion == 8) {
            $query = "SELECT COUNT(*) FROM #__extensions WHERE name='System - Track Actions'";
        }
		try {
			$db->setQuery($query);
			$db->execute();
			$enabled = $db->loadResult();
		} catch (Exception $e)
        {    			
            $enabled = 0;
        }      
    
        return $enabled;
    }

    /* Función que consulta el valor de una bbdd pasados como argumentos */
    function get_campo_bbdd($bbdd,$campo)
    {
        // Creamos el nuevo objeto query
        $db = JFactory::getDbo();
    
        $bbdd = filter_var($bbdd, FILTER_SANITIZE_STRING);
        $campo = filter_var($campo, FILTER_SANITIZE_STRING);
        
		try {
			// Consultamos el campo de la bbdd
			$query = $db->getQuery(true)
				->select($db->quoteName($campo))
				->from($db->quoteName('#__' . $bbdd));
			$db->setQuery($query);
			$valor = $db->loadResult();
		} catch (Exception $e)
        {    			
            $valor = null;
        }       
    
        return $valor;
    }

    /* Función para establecer el valor de un campo de la tabla '#_securitycheckpro_file_manager' */
    function set_campo_filemanager($campo,$valor)
    {
        // Creamos el nuevo objeto query
        $db = $this->getDbo();
        $query = $db->getQuery(true);
    
        // Sanitizamos las entradas
        $campo_sanitizado = $db->escape($campo);
        $valor_sanitizado = $db->Quote($db->escape($valor));

        // Construimos la consulta...
        $query->update('#__securitycheckpro_file_manager');
        $query->set($campo_sanitizado .'=' .$valor_sanitizado);
        $query->where('id=1');

        // ... y la lanzamos
        $db->setQuery($query);
        $db->execute();
    }

    /* Función para obtener el valor de un campo de la tabla '#_securitycheckpro_file_manager' */
    function get_campo_filemanager($campo)
    {
        try 
        {
            // Creamos el nuevo objeto query
            $db = $this->getDbo();
            $query = $db->getQuery(true);
        
            // Sanitizamos las entradas
            $campo_sanitizado = $db->Quote($db->escape($campo));
        
            // Construimos la consulta...
            $query->select($campo);
            $query->from('#__securitycheckpro_file_manager');
            $query->where('id=1');
        
            // ... y la lanzamos
            $db->setQuery($query);
            $result = $db->loadResult();
        } catch (Exception $e)
        {
            $result = "ERROR";
        }
    
        if ((is_null($result)) && ($campo=='estado')) {
            $result = "ERROR";
        }
    
        // Devolvemos el resultado
        return $result;    
    }

    /* Función que obtiene un array con los datos que serán mostrados en la opción 'file manager' */
    function loadStack($opcion,$field)
    {
        $db = $this->getDbo();
        //$stack = null;
    
        /**
* 
         *
    * @var array The files to process 
*/
        $stack = array();

        /**
* 
         *
    * @var array The files to process 
*/
        $Stack_Integrity = array();

        /**
* 
         *
    * @var int Total numbers of file/folders in this site. Permissions option 
*/
        $files_scanned = 0;

        /**
* 
         *
    * @var int Total numbers of file/folders in this site. Integrity option
*/
        $files_scanned_integrity = 0;

        /**
* 
         *
    * @var int Numbers of files/folders with  incorrect permissions 
*/
        $files_with_incorrect_permissions = 0;

        /**
* 
         *
    * @var int Numbers of files/folders with  incorrect integrity 
*/
        $files_with_incorrect_integrity = 0;
    
        // Establecemos el tamaño máximo de memoria que el script puede consumir
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $memory_limit = $params->get('memory_limit', '512M');
        if (preg_match('/^[0-9]*M$/', $memory_limit)) {
            ini_set('memory_limit', $memory_limit);
        } else 
        {
            ini_set('memory_limit', '512M');
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_VALID_MEMORY_LIMIT'), 'error');
        }
            
        switch ($opcion)
        {
        case "permissions":            
            // Leemos el contenido del fichero
            $stack = file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name);
            
            if (empty($stack)) {
                $stack = array();
                return;
            }
            break;
        case "integrity":            
            // Leemos el contenido del fichero
            $stack = file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
            
            if (empty($stack)) {
                $this->Stack_Integrity = array();
                return;
            }
            break;
        case "filemanager_resume":
            $query = $db->getQuery(true)
                ->select(array($db->quoteName('storage_value')))
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
            $db->setQuery($query);
            $stack = $db->loadResult();
            
            if (empty($stack)) {
                $this->files_scanned = 0;
                $this->files_with_incorrect_permissions = 0;
                return;
            }
            break;
        case "fileintegrity_resume":
            $query = $db->getQuery(true)
                ->select(array($db->quoteName('storage_value')))
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
            $db->setQuery($query);
            $stack = $db->loadResult();
                        
            if (empty($stack)) {
                $files_scanned_integrity = 0;
                $files_with_incorrect_integrity = 0;
                return;
            }
            break;
        case "malwarescan_resume":
            $query = $db->getQuery(true)
                ->select(array($db->quoteName('storage_value')))
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('malwarescan_resume'));
            $db->setQuery($query);
            $stack = $db->loadResult();
            
            if (empty($stack)) {
                $this->files_scanned_malwarescan = 0;
                $this->suspicious_files = 0;
                return;
            }
            break;        
        }
    
        $stack = json_decode($stack, true);
    
        switch ($field)
        {
        case "file_manager":
            $this->Stack = array_splice($stack['files_folders'], $this->getState('limitstart'), $this->getState('limit'));
            return ($this->Stack);
        case "files_scanned":
            $this->files_scanned = $stack['files_scanned'];
            return ($this->files_scanned);
        case "files_with_incorrect_permissions":
            if(empty($stack)) {
                $this->files_with_incorrect_permissions = 0;
            } else
            {
                $this->files_with_incorrect_permissions = $stack['files_with_incorrect_permissions'];            
            }    
            return ($this->files_with_incorrect_permissions);            
        case "last_check":
            return ($stack['last_check']);
        case "files_scanned_integrity":
            $this->files_scanned_integrity = $stack['files_scanned_integrity'];
            return ($this->files_scanned_integrity);
        case "files_with_bad_integrity":
            if(empty($stack)) {
                $this->files_with_incorrect_integrity = 0;
            } else 
            {
                $this->files_with_incorrect_integrity = $stack['files_with_incorrect_integrity'];            
            }
            return ($this->files_with_incorrect_integrity);
        case "last_check_integrity":
            return ($stack['last_check_integrity']);
        case "last_check_malwarescan":
            return ($stack['last_check_malwarescan']);
        case "files_scanned_malwarescan":
            $this->files_scanned_malwarescan = $stack['files_scanned_malwarescan'];
            return ($this->files_scanned_malwarescan);
        case "suspicious_files":
            if (empty($stack)) {
                $this->suspicious_files = 0;
            } else 
            {
                $this->suspicious_files = $stack['suspicious_files'];            
            }    
            return ($this->suspicious_files);        
        }
    }

    /* Función que extrae las entradas de la BBDD '#__securitycheckpro_dynamic_blacklist' */
    function get_dynamic_blacklist_ips()
    {
    
        // Inicializamos las variables
        $query = null;
        $db = JFactory::getDBO();
        $blacklist_ips = array();
        
        // Obtenemos el 'extension_id' del Firewall Web, disponible en la tabla '#__extensions'
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('ip')))
            ->from($db->quoteName('#__securitycheckpro_dynamic_blacklist'));
        $db->setQuery($query);
        $blacklist_ips = $db->loadColumn();    
    
        return $blacklist_ips;
    }

    /* Función que extrae las entradas de la BBDD '#__securitycheckpro_dynamic_blacklist' */
    function get_subscriptions_status()
    {
        // Inicializamos las variables
        $downloadid = '';
        $mainframe = JFactory::getApplication();
        
        // Chequeamos si el plugin 'update database' está instalado
        $update_database_plugin_exists = $this->PluginStatus(4);    
        $trackactions_plugin_exists = $this->PluginStatus(8);
    
        // Buscamos el Download ID 
        $plugin = JPluginHelper::getPlugin('system', 'securitycheckpro_update_database');
        if (!empty($plugin)) {
            $params = new JRegistry($plugin->params);
            $downloadid = $params->get('downloadid');        
        }
        if (empty($downloadid)) {
            $app = JComponentHelper::getParams('com_securitycheckpro');
            $downloadid = $app->get('downloadid');
        }
    
        // Si el Download id está vacío actualizamos las variables
        if (empty($downloadid)) {
            $mainframe->setUserState("scp_update_database_subscription_status", JText::_('COM_SECURITYCHECKPRO_UPDATE_DATABASE_DOWNLOAD_ID_EMPTY'));        
            $mainframe->setUserState("scp_subscription_status", JText::_('COM_SECURITYCHECKPRO_UPDATE_DATABASE_DOWNLOAD_ID_EMPTY'));
            $mainframe->setUserState("trackactions_subscription_status", JText::_('COM_SECURITYCHECKPRO_UPDATE_DATABASE_DOWNLOAD_ID_EMPTY'));
        } else
        {
            if (function_exists('curl_init')) {
                // Obtenemos la respuesta de cada url
                $this->get_response("scp", $downloadid);
                if ($update_database_plugin_exists) {
                    $this->get_response("update", $downloadid);
                } else
                {
                    $mainframe->setUserState("scp_update_database_subscription_status", JText::_('COM_SECURITYCHECKPRO_PLUGIN_NOT_INSTALLED'));
                }
                if ($trackactions_plugin_exists) {
                    $this->get_response("trackactions", $downloadid);
                } else
                {
                    $mainframe->setUserState("trackactions_subscription_status", JText::_('COM_SECURITYCHECKPRO_PLUGIN_NOT_INSTALLED'));
                }
            } else 
            {
                   $mainframe->setUserState("scp_update_database_subscription_status", JText::_('COM_SECURITYCHECKPRO_CURL_NOT_DEFINED'));        
                   $mainframe->setUserState("scp_subscription_status", JText::_('COM_SECURITYCHECKPRO_CURL_NOT_DEFINED'));
                   $mainframe->setUserState("trackactions_subscription_status", JText::_('COM_SECURITYCHECKPRO_CURL_NOT_DEFINED'));
            }
        
        }    
    }

    function get_response($product,$downloadid)
    {
    
        $mainframe = JFactory::getApplication();
    
        $endpoint = "https://securitycheck.protegetuordenador.com/status.php";    
        $plan_id = 0;
		$product_name = "Securitycheck Pro";
    
        
        // Url que contendrá el fichero xml, que a su vez contendrá la url de acceso al elemento
        if ($product == "update") {                
            $plan_id = 14;
			$product_name = "Update Database";
        } else     if ($product == "scp") {
            $plan_id = 12;    
        } else     if ($product == "trackactions") {
            $plan_id = 17;
			$product_name = "Track Actions";
        }
                            
        // Establecemos el valor de las variables que se incorporarán a la url    
        $params = array('dlid' => $downloadid, 'plan_id' => $plan_id);    
        $url = $endpoint . '?' . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, SCP_USER_AGENT);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);                
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        @curl_setopt($ch, CURLOPT_CAINFO, SCP_CACERT_PEM);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    
        $response = curl_exec($ch);    
    
       // Si el campo obtenido no es numérico salimos
        if (!is_numeric($response)) {
			$message = curl_error($ch);			
			JFactory::getApplication()->enqueueMessage("Unable to retrieve " . $product_name . " subscription's status. Message: " . $message, 'error');
            return;        

        }                           

        // Si el resultado de la petición es 'false' no podemos hacer nada
        if ($response === false) {  
			$message = curl_error($ch);			
			JFactory::getApplication()->enqueueMessage("Unable to retrieve " . $product_name . " subscription's status. Message: " . $message, 'error');

        }  else
        {
            if ($response == "5") {
                /* Hemos contactado y el código devuelto es '5'; establecemos la variable correspondiente a 'Active' */
                if ($product == "update") {
                    $mainframe->setUserState("scp_update_database_subscription_status", JText::_('COM_SECURITYCHECKPRO_ACTIVE'));
                } else if ($product == "scp") {
                    $mainframe->setUserState("scp_subscription_status", JText::_('COM_SECURITYCHECKPRO_ACTIVE'));
                } else if ($product == "trackactions") {
                    $mainframe->setUserState("trackactions_subscription_status", JText::_('COM_SECURITYCHECKPRO_ACTIVE'));
                }
            } else if ($response == "4") {
                /* Hemos contactado y el código devuelto es '4'; establecemos la variable correspondiente a 'Expired' */
                if ($product == "update") {
                       $mainframe->setUserState("scp_update_database_subscription_status", JText::_('COM_SECURITYCHECKPRO_EXPIRED'));
                } else if ($product == "scp") {
                    $mainframe->setUserState("scp_subscription_status", JText::_('COM_SECURITYCHECKPRO_EXPIRED'));
                } else if ($product == "trackactions") {
                    $mainframe->setUserState("trackactions_subscription_status", JText::_('COM_SECURITYCHECKPRO_EXPIRED'));
                }
            }
        }
        
        // Cerramos el manejador
        curl_close($ch);
    }

    /* Función que determina el número de logs marcados como "no leido"*/
    function LogsPending()
    {
        
        $db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE marked='0'";		
        $db->setQuery($query);
        $db->execute();
        $enabled = $db->loadResult();
    
        return $enabled;
    }

    /* Borra las tablas #_sessions y #_securitycheckpro_sessions */
    function purge_sessions()
    {
        $db = JFactory::getDBO();
        
        // Tabla 'sessions'
        $query = 'TRUNCATE TABLE #__session' ;
        $db->setQuery($query);
        $db->execute();    
    
        // Tabla 'securitycheckpro_sessions'
        $query = 'TRUNCATE TABLE #__securitycheckpro_sessions' ;
        $db->setQuery($query);
        $db->execute();
    
        // For Joomla 4 we must also close the current session 
        $user = JFactory::getUser();
        $user_id = $user->id;    
    
        $app = JFactory::getApplication();
        $app->logout($user_id);
    
    }
	
	/* Devuelve una fecha datetime usando el offset establecido en Joomla */
	public function get_Joomla_timestamp()
	{
		// Obtenemos el timezone de Joomla y sobre esa información calculamos el timestamp
		$config = JFactory::getConfig();
		$offset = $config->get('offset');
						
		if (empty($offset))
		{
			$offset = 'UTC';
		}
		
		$date = new DateTime("now", new DateTimeZone($offset) );
		$timestamp_joomla_timezone = $date->format('Y-m-d H:i:s');
			
		return $timestamp_joomla_timezone;
	}

}
