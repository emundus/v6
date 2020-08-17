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

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            'ip', 'geolocation', 'time', 'description', 'component', 'type', 'marked'
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
        $query->where('(a.ip LIKE '.$search.' OR a.time LIKE '.$search.' OR a.username LIKE '.$search.' OR a.description LIKE '.$search.' OR a.uri LIKE '.$search.' OR a.geolocation LIKE '.$search.')');
    
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
            $query->where('a.marked = '.(int) $leido);
        }    
    
    
        // Filtramos el rango de fechas
       
        $fltDateFrom = $this->getState('datefrom', null, 'string');
    
        if (!empty($fltDateFrom)) {
            $is_valid = $this->checkIsAValidDate($fltDateFrom);
            if ($is_valid) {
                $date = new JDate($fltDateFrom);
                $query->where($db->quoteName('time').' >= '.$db->Quote($date->toSql()));
            } else 
            {
                if ($fltDateFrom != "0000-00-00 00:00:00") {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_DATE_NOT_VALID'), 'notice');
                }            
            }    
        }
    
        if (!empty($fltDateTo)) {
            $is_valid = $this->checkIsAValidDate($fltDateTo);
            if ($is_valid) {
                $date = new JDate($fltDateTo);
                $query->where($db->quoteName('time').' <= '.$db->Quote($date->toSql()));
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
				$sql = "UPDATE `#__securitycheckpro_logs` SET marked=1 WHERE id='{$uid}'";
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
				$sql = "UPDATE `#__securitycheckpro_logs` SET marked=0 WHERE id='{$uid}'";
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
				$sql = "DELETE FROM `#__securitycheckpro_logs` WHERE id='{$uid}'";
				$db->setQuery($sql);
				$db->execute();    
			}
		} else {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_LOG_SELECTED'), 'warning');
		}
    }

    /* Función para chequear si una ip pertenece a una lista en la que podemos especificar rangos. Podemos tener una ip del tipo 192.168.*.* y una ip 192.168.1.1 entraría en ese rango */
    function chequear_ip_en_lista($ip,$lista)
    {
        $aparece = false;
        $array_ip_peticionaria = explode('.', $ip);
        
        if (strlen($lista) > 0) {
            // Eliminamos los caracteres en blanco antes de introducir los valores en el array
            $lista = str_replace(' ', '', $lista);
            $array_ips = explode(',', $lista);
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
                        if ($igual) { // $igual será true cuando hayamos recorrido el array y todas las partes del mismo coincidan con la ip que realiza la petición
                              $aparece = true;
                              return $aparece;
                        }
                    }
                }
            }
        }
        return $aparece;
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
            $sql = "SELECT ip FROM `#__securitycheckpro_logs` WHERE id='{$uid}'";
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
                break;
            }
                
            $aparece_lista_negra = $this->chequear_ip_en_lista($ip, $params['blacklist']);
            if (!$aparece_lista_negra) {
                if (!empty($params['blacklist'])) {
                    $params['blacklist'] .= ',' .$ip;
                } else 
                {
                    $params['blacklist'] = $ip;
                }            
                $added_elements++;
            }
        }
        $not_added = $array_size - $added_elements;
    
        if ($added_elements > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_ADDED_TO_LIST', $added_elements));
        }
        if ($not_added > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_IGNORED', $not_added), 'notice');
        }
    
        // Codificamos de nuevo los parámetros y los introducimos en la BBDD
        $params = utf8_encode(json_encode($params));
        
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('pro_plugin'));
        $db->setQuery($query);
        $db->execute();
        
        $object = (object)array(
        'storage_key'        => 'pro_plugin',
        'storage_value'        => $params
        );
        
        try
        {
            $result = $db->insertObject('#__securitycheckpro_storage', $object);            
        } catch (Exception $e)
        {    
            $applied = false;
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
        if (interface_exists('JModel')) {
            $params = JModelLegacy::getInstance('FirewallConfig', 'SecuritycheckProsModel');
        } else 
        {
            $params = JModel::getInstance('FirewallConfig', 'SecuritycheckProsModel');
        }
    
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
        $sql = "TRUNCATE `#__securitycheckpro_logs`";
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
            $sql = "SELECT ip FROM `#__securitycheckpro_logs` WHERE id='{$uid}'";
            $db->setQuery($sql);
            $db->execute();
            $ip = $db->loadResult();
        
                
            $aparece_lista_blanca = $this->chequear_ip_en_lista($ip, $params['whitelist']);
            if (!$aparece_lista_blanca) {
                if (!empty($params['blacklist'])) {
                    $params['whitelist'] .= ',' .$ip;
                } else
                {
                    $params['whitelist'] = $ip;
                }                        
                $added_elements++;
            }
        }
        $not_added = $array_size - $added_elements;
    
        if ($added_elements > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_ADDED_TO_LIST', $added_elements));
        }
        if ($not_added > 0) {
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_IGNORED', $not_added), 'notice');
        }
    
        // Codificamos de nuevo los parámetros y los introducimos en la BBDD
        $params = utf8_encode(json_encode($params));
        
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('pro_plugin'));
        $db->setQuery($query);
        $db->execute();
        
        $object = (object)array(
        'storage_key'        => 'pro_plugin',
        'storage_value'        => $params
        );
        
        try 
        {
            $result = $db->insertObject('#__securitycheckpro_storage', $object);            
        } catch (Exception $e)
        {    
            $applied = false;
        }
    
        // Marcamos los elementos como leidos
        $this->mark_read($uids);
        
    }

}
