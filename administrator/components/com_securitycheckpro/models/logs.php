<?php
/**
 * Modelo Logs para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Date\Date as JDate;

/**
 * Modelo Vulninfo
 */
class SecuritycheckprosModelLogs extends JModelList
{

    private $defaultConfig = array(
    'logs_attacks'            => 1,    
    );
	
	private $dbtype = "mysql";

    public function __construct($config = array())
    {
        $scp_config = JFactory::getConfig();
		$this->dbtype = $scp_config->get('dbtype');
		
		if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            'ip', 'time', 'description', 'component', 'type', 'marked'
            );
        }
    
        parent::__construct($config);
    
    }

    /***/
    protected function populateState($ordering = null,$direction = null)
    {
        // Inicializamos las variables
        $app        = JFactory::getApplication();
    
        $search = $app->getUserStateFromRequest('filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $description = $app->getUserStateFromRequest('filter.description', 'filter_description');
        $this->setState('filter.description', $description);
        $type = $app->getUserStateFromRequest('filter.type', 'filter_type');
        $this->setState('filter.type', $type);
        $leido = $app->getUserStateFromRequest('filter.leido', 'filter_leido');
        $this->setState('filter.leido', $leido);
        $datefrom = $app->getUserStateFromRequest('datefrom', 'datefrom');
        $this->setState('datefrom', $datefrom);
        $dateto = $app->getUserStateFromRequest('dateto', 'dateto');
        $this->setState('dateto', $dateto);
    
        parent::populateState('time', 'DESC');
    }

    public function getListQuery()
    {
        
        // Creamos el nuevo objeto query
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $app        = JFactory::getApplication();
        $search = $app->getUserState('filter.search', '');    
    
        // Sanitizamos la entrada
        if (!is_null($search)) {
            $search = $db->Quote('%' . $db->escape($search, true) . '%');
        }
        
        $query->select('a.*');
        $query->from('#__securitycheckpro_logs AS a');
		if (strstr($this->dbtype,"mysql")) {
			$query->where('(a.ip LIKE '.$search.' OR a.time LIKE '.$search.' OR a.username LIKE '.$search.' OR a.description LIKE '.$search.' OR a.uri LIKE '.$search.' OR a.geolocation LIKE '.$search.')');
		} else if (strstr($this->dbtype,"pgsql")) {
			$query->where('(a.ip LIKE '.$search.' OR CAST(a.time as TEXT) LIKE '.$search.' OR a.username LIKE '.$search.' OR a.description LIKE '.$search.' OR a.uri LIKE '.$search.' OR a.geolocation LIKE '.$search.')');
		}
            
        // Filtramos la descripcion
        if ($description = $this->getState('filter.description')) {
            $query->where('a.tag_description = '.$db->quote($description));
        }
    
        // Filtramos el tipo
        if ($log_type = $this->getState('filter.type')) {
            $query->where('a.type = '.$db->quote($log_type));
        }
        
        // Filtramos leido/no leido
        $leido = $this->getState('filter.leido');
        if (is_numeric($leido)) {
			if (strstr($this->dbtype,"mysql")) {
				 $query->where('a.marked = '.(int) $leido);
			} else if (strstr($this->dbtype,"pgsql")) {
				 $query->where("CAST(a.marked as TEXT) = '".(int) $leido . "'");
			}
           
        }    
    
    
        // Filtramos el rango de fechas
       
        $fltDateFrom = $this->getState('datefrom', null, 'string');
    
        if (!empty($fltDateFrom)) {
            $is_valid = $this->checkIsAValidDate($fltDateFrom);
            if ($is_valid) {
				$date = new JDate($fltDateFrom);
				if (strstr($this->dbtype,"mysql")) {					
					$query->where($db->quoteName('time').' >= '.$db->Quote($date->toSql()));
				} else if (strstr($this->dbtype,"pgsql")) {
					$query->where($db->quoteName('time').' >= '.$db->Quote($date));
				}
            } else 
            {
                if ($fltDateFrom != "0000-00-00 00:00:00") {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_DATE_NOT_VALID'), 'notice');
                }            
            }    
        }
		
		$fltDateTo = $this->getState('dateto', null, 'string');
		
        if (!empty($fltDateTo)) {
            $is_valid = $this->checkIsAValidDate($fltDateTo);			
            if ($is_valid) {
				$date = new JDate($fltDateTo);
				if (strstr($this->dbtype,"mysql")) {
					$query->where($db->quoteName('time').' <= '.$db->Quote($date->toSql()));
				} else if (strstr($this->dbtype,"pgsql")) {
					$query->where($db->quoteName('time').' <= '.$db->Quote($date));
				}
                
            } else 
            {
                if ($fltDateTo != "0000-00-00 00:00:00") {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_DATE_NOT_VALID'), 'notice');
                }
            }    
        }
    
        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'ip')) . ' ' . $db->escape($this->getState('list.direction', 'desc')));
				    
        return $query;
    }
        

    function checkIsAValidDate($myDateString)
    {
        return (bool)strtotime($myDateString);
    }

    /* Función para cambiar el estado de un array de logs de no leído a leído */
    function mark_read($uids=null)
    {
        if (empty($uids)) {
            $jinput = JFactory::getApplication()->input;
            $uids = $jinput->get('cid', 0, 'array');			
        }       
		
		if ( !empty($uids) )
		{
			Joomla\Utilities\ArrayHelper::toInteger($uids, array());
    
			$db = $this->getDbo();
			foreach($uids as $uid) {
				$sql = "UPDATE #__securitycheckpro_logs SET marked=1 WHERE id='{$uid}'";
				$db->setQuery($sql);
				$db->execute();
			}
		} else {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_LOG_SELECTED'), 'warning');
		}
        
    }

    /* Función para cambiar el estado de un array de logs de leído a no leído */
    function mark_unread()
    {
        $jinput = JFactory::getApplication()->input;
        $uids = $jinput->get('cid', 0, 'array');
		
		if ( !empty($uids) )
		{    
			Joomla\Utilities\ArrayHelper::toInteger($uids, array());
			
			$db = $this->getDbo();
			foreach($uids as $uid) {
				$sql = "UPDATE #__securitycheckpro_logs SET marked=0 WHERE id='{$uid}'";
				$db->setQuery($sql);
				$db->execute();            
			}
		} else {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_LOG_SELECTED'), 'warning');
		}
    }

    /* Función para borrar un array de logs */
    function delete()
    {
        $jinput = JFactory::getApplication()->input;
        $uids = $jinput->get('cid', 0, 'array');
		
		if ( !empty($uids) )
		{     
			Joomla\Utilities\ArrayHelper::toInteger($uids, array());
		
			$db = $this->getDbo();
			foreach($uids as $uid) 
			{
				$sql = "DELETE FROM #__securitycheckpro_logs WHERE id='{$uid}'";
				$db->setQuery($sql);
				$db->execute();    
			}
		} else {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_LOG_SELECTED'), 'warning');
		}
    }

    
    /* Función que añade un conjunto de Ips a la lista negra */
    function add_to_blacklist()
    {
    
        // Inicializamos las variables
        $query = null;
        $array_size = 0;
        $added_elements = 0;
		        
        $db = JFactory::getDBO();
    
        // Obtenemos los valores de las IPs que serán introducidas en la lista negra
        $jinput = JFactory::getApplication()->input;
        $uids = $jinput->get('cid', 0, 'array');
        Joomla\Utilities\ArrayHelper::toInteger($uids, array());
    
        // Número de elementos del array
        $array_size = count($uids);
        
        foreach($uids as $uid)
        {
            $sql = "SELECT ip FROM #__securitycheckpro_logs WHERE id='{$uid}'";
            $db->setQuery($sql);
            $db->execute();
            $ip = $db->loadResult();
            // Get the client IP to see if the user wants to block his own IP
            $client_ip = "";
			// Contribution of George Acu - thanks!
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
				$result_ip_address = explode(', ', $clientIpAddress);
                $client_ip = $result_ip_address[0];
			} elseif (isset($_SERVER['REMOTE_ADDR']))
			{
				# this one would be used, if no header of the above is present
				$client_ip = $_SERVER['REMOTE_ADDR']; 
			}
                    
            if ($ip == $client_ip) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_CANT_ADD_YOUR_OWN_IP'), 'warning');
                $array_size--;
                return;
            }
			
			// Cargamos las librerias necesarias para realizar comprobaciones
            include_once JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/library/model.php';
            $model = new SecuritycheckproModel;
			          
			$aparece_lista_negra = $model->chequear_ip_en_lista($ip, "blacklist"); 
			
			if (!$aparece_lista_negra) {
				$object = (object)array(
					'ip'        => $ip
				);
				
				try{
					$db->insertObject("#__securitycheckpro_blacklist", $object);
					$added_elements++;
				} catch (Exception $e)
				{  				
					$applied = false;
				}				
            }
        }
		
        $not_added = $array_size - $added_elements;
    
        if ($added_elements > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_ADDED_TO_LIST', $added_elements));
        }
        if ($not_added > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_IGNORED', $not_added), 'notice');
        }
        
        // Marcamos los elementos como leidos
        $this->mark_read($uids);
        
    }

    /* Obtiene el valor de una opción de configuración */
    public function getValue($key, $default = null, $key_name = 'cparams')
    {
        if(is_null($this->config)) { $this->load($key_name);
        }
    
        if(version_compare(JVERSION, '3.0', 'ge')) {
            return $this->config->get($key, $default);
        } else
        {
            return $this->config->getValue($key, $default);
        }
    }

    /* Hace una consulta a la tabla especificada como parámetro  */
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
        
        if(version_compare(JVERSION, '3.0', 'ge')) {
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

    /* Obtiene la configuración de los parámetros de la opción 'Mode' */
    function getConfig()
    {
        $params = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('FirewallConfig', 'SecuritycheckProsModel');
    
        $config = array();
        foreach($this->defaultConfig as $k => $v)
        {
            $config[$k] = $params->getValue($k, $v, 'pro_plugin');
        }
        return $config;
    }

    /* Función para borrar todos los logs */
    function delete_all()
    {
    
        $db = $this->getDbo();
        $sql = "TRUNCATE #__securitycheckpro_logs";
        $db->setQuery($sql);
        $db->execute();    
    }

    /* Función que añade un conjunto de Ips a la lista blanca */
    function add_to_whitelist() 
    {
    
        // Inicializamos las variables
        $query = null;
        $array_size = 0;
        $added_elements = 0;
        
        $db = JFactory::getDBO();
    
        // Obtenemos los valores de las IPs que serán introducidas en la lista negra
        $jinput = JFactory::getApplication()->input;
        $uids = $jinput->get('cid', 0, 'array');
        Joomla\Utilities\ArrayHelper::toInteger($uids, array());
    
        // Número de elementos del array
        $array_size = count($uids);
        
        foreach($uids as $uid)
        {
            $sql = "SELECT ip FROM #__securitycheckpro_logs WHERE id='{$uid}'";
            $db->setQuery($sql);
            $db->execute();
            $ip = $db->loadResult();
			
			// Cargamos las librerias necesarias para realizar comprobaciones
            include_once JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/library/model.php';
            $model = new SecuritycheckproModel;
			          
			$aparece_lista_blanca = $model->chequear_ip_en_lista($ip, "whitelist");
        
            if (!$aparece_lista_blanca) {
                $object = (object)array(
					'ip'        => $ip
				);
				
				try{
					$db->insertObject("#__securitycheckpro_whitelist", $object);
					$added_elements++;
				} catch (Exception $e)
				{    							
					$applied = false;
				}
            }
        }
        $not_added = $array_size - $added_elements;
    
        if ($added_elements > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_ADDED_TO_LIST', $added_elements));
        }
        if ($not_added > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_IGNORED', $not_added), 'notice');
        }
    
    
        // Marcamos los elementos como leidos
        $this->mark_read($uids);
        
    }
	
	/* Función para guardar en la tabla securitycheck_storage la configuración pasada como argumento. Se usa para añadir un componente como excepción desde los logs */
	function save_config($data) {
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query2 = $db->getQuery(true);
		
		$data = json_encode($data);
		$key_name = 'pro_plugin';
		
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
			} 
		} else {
			JFactory::getApplication()->enqueueMessage("Error", 'error');
		}
	}
	
	// Función que añade un elemento nuevo a una lista de elementos separados por comas
	function add_element($string,$new_element) {
		
		// Creamos un array con los elementos...
		$string_to_array = explode(",",$string);
		// ... borramos los valores vacíos ...
		$string_to_array = array_filter($string_to_array);
				
		// ... añadimos el elemento nuevo ...
		$string_to_array[] = filter_var($new_element, FILTER_SANITIZE_STRING);
		// ... y volvemos a trasnformar el array a string
		$final_string = implode(",",$string_to_array);
		return $final_string;		
		
	}
	
	/* Función que añade un componente como excepción */
    function add_exception() 
    {
    
        // Inicializamos las variables
        $query = null;
        $array_size = 0;
        $added_elements = 0;
		$exists = true;
        
        $db = JFactory::getDBO();
    
        // Obtenemos los valores de las IPs que serán introducidas en la lista negra
        $jinput = JFactory::getApplication()->input;
        $uids = $jinput->get('cid', 0, 'array');
        Joomla\Utilities\ArrayHelper::toInteger($uids, array());
    
        // Número de elementos del array
        $array_size = count($uids);
        
        // Obtenemos los valores de las distintas opciones del Firewall Web
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('pro_plugin'));
        $db->setQuery($query);
        $params = $db->loadResult();
        $params = json_decode($params, true);
		        
        foreach($uids as $uid)
        {
            $sql = "SELECT component,type,tag_description FROM #__securitycheckpro_logs WHERE id='{$uid}'";
            $db->setQuery($sql);
            $db->execute();
            $result = $db->loadObject();
			
			switch ($result->tag_description) {
				case 'TAGS_STRIPPED':
					if (stristr($params['strip_tags_exceptions'], $result->component) === FALSE) {
						$params['strip_tags_exceptions'] = $this->add_element($params['strip_tags_exceptions'],$result->component);
						$exists = false;
					} else {						
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}									
					break;
				case 'DUPLICATE_BACKSLASHES':
					if (stristr($params['duplicate_backslashes_exceptions'], $result->component) === FALSE) {
						$params['duplicate_backslashes_exceptions'] = $this->add_element($params['duplicate_backslashes_exceptions'],$result->component);
						$exists = false;
					} else {						
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}						
					break;
				case 'LINE_COMMENTS':
					if (stristr($params['line_comments_exceptions'], $result->component) === FALSE) {
						$params['line_comments_exceptions'] = $this->add_element($params['line_comments_exceptions'],$result->component);
						$exists = false;
					} else {
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}						
					break;	
				case 'SQL_PATTERN':
					if (stristr($params['sql_pattern_exceptions'], $result->component) === FALSE) {
						$params['sql_pattern_exceptions'] = $this->add_element($params['sql_pattern_exceptions'],$result->component);
						$exists = false;
					} else {						
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}						
					break;	
				case 'IF_STATEMENT':
					if (stristr($params['if_statement_exceptions'], $result->component) === FALSE) {
						$params['if_statement_exceptions'] = $this->add_element($params['if_statement_exceptions'],$result->component);
						$exists = false;
					} else {						
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}						
					break;	
				case 'INTEGERS':
					if (stristr($params['using_integers_exceptions'], $result->component) === FALSE) {
						$params['using_integers_exceptions'] = $this->add_element($params['using_integers_exceptions'],$result->component);
						$exists = false;
					} else {						
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}						
					break;	
				case 'BACKSLASHES_ADDED':
					if (stristr($params['escape_strings_exceptions'], $result->component) === FALSE) {
						$params['escape_strings_exceptions'] = $this->add_element($params['escape_strings_exceptions'],$result->component);
						$exists = false;
					} else {						
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}						
					break;	
				case 'LFI':
					if (stristr($params['lfi_exceptions'], $result->component) === FALSE) {
						$params['lfi_exceptions'] = $this->add_element($params['lfi_exceptions'],$result->component);
						$exists = false;
					} else {						
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}						
					break;	
				case 'FORBIDDEN_WORDS':
					if (stristr($params['second_level_exceptions'], $result->component) === FALSE) {
						$params['second_level_exceptions'] = $this->add_element($params['second_level_exceptions'],$result->component);
						$exists = false;
					} else {						
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_EXCEPTION_ALREADY_EXISTS', $result->component, $result->type), 'notice');
					}
					break;
			}        
        }  
		
		// Save the new config
		if (!$exists) {
			$this->save_config($params);
		}
        
    }

}
