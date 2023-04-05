<?php
/**
 * Modelo Securitycheckpros para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\Registry as JRegistry;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

// Load library
require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'loader.php';

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelDatabaseUpdates extends SecuritycheckproModel
{

    // Variable que contendrá el tipo de componente de securitycheck instalado 
    private $securitycheck_type = 'Not_defined';
    // Variable que almacena la tabla en la que insertar las nuevas vulnerabilidades
    private $vuln_table = 'Not_defined';
    // Variable que contiene la versión de la bbdd local (contendrá el mayor valor del campo 'dbversion' del archivo xml leído)
    private $higher_database_version = '0.0.0';
	private $scan_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
	
	var $global_model = null;

    function __construct()
    {
        parent::__construct();
		
		require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model.php';
		$this->global_model = new SecuritycheckproModel();

    }

    /* Chequea qué tipo de componente de securitycheck está instalado */
    function check_securitycheck_type()
    {

        $db = JFactory::getDbo();
    
        // Consultamos si está instalada la versión Pro
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('name').' = '.$db->quote('System - Securitycheck pro'));
        $db->setQuery($query);
        $result = $db->loadResult();
    
        // La extensión Pro está instalada; actualizamos la variable $securitycheck_type y $table
        if ($result == '1') {
            $this->securitycheck_type = 'com_securitycheckpro';
            $this->vuln_table = '#__securitycheckpro_db';
        } else 
        {        
            // Consultamos si está instalada la versión free
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('name').' = '.$db->quote('System - Securitycheck'));
            $db->setQuery($query);
            $result = $db->loadResult();
        
            // La extensión free está instalada; actualizamos la variable $securitycheck_type y $table
            if ($result == '1') {
                $this->securitycheck_type = 'com_securitycheck';
                $this->vuln_table = '#__securitycheck_db';
            }    
        }
    }

    /* Función que añade vulnerabilidades a la bbdd del componente securitycheck */
    function add_vuln($array_complete,$local_database_version)
    {

        // La versión mayor de la bbdd corresponderá, al principio, a la almacenada.
        $this->higher_database_version = $this->get_database_version();
    
        // Comprobamos si hemos de insertar cada vulnerabilidad
        foreach ($array_complete as $vulnerability)
        {
            /* Consultamos la rama para la que es válida la vulnerabilidad. Para ello dividimos los strings en el formato array[0]=3, array[1]=0... Así, el primer valor contendrá la rama para la que es válida la vulnerabilidad y la rama de joomla instalada */
            $jversion_exists = array_key_exists("jversion", $vulnerability);
            if ($jversion_exists) {
                  $vulnerabillity_branch = explode(".", $vulnerability['jversion']);            
            } else
            {
                $vulnerabillity_branch = "3.0.0";            
            }
            $local_joomla_branch = explode(".", JVERSION);
        
            // La versión de la vulnerabilidad debe ser mayor que la de la bbdd local para almacenarla/eliminarla
            if (version_compare($vulnerability['dbversion'], $local_database_version, 'gt')) {    
                // Actualizamos la variable que contiene la mayor versión de la bbdd leída del xml. Este valor se almacenará luego en la bbdd local.
                $this->higher_database_version = $vulnerability['dbversion'];
                // Método para insertar una vulnerabilidad
                $key_exists = array_key_exists("method", $vulnerability);
                if (($key_exists && $vulnerability['method'] == 'add') || (!$key_exists)) {
                    // La vulnerabilidad debe corresponder con la rama de Joomla local
                    if ($vulnerabillity_branch[0] == $local_joomla_branch[0]) {
                        // Rellenamos el objeto que vamos a insertar en la tabla '#__securitycheck(pro)_db', según la opción instalada
                        if ($this->securitycheck_type == 'com_securitycheckpro') {                        
                            $nueva_vulnerabilidad = (object) array(
                             'Product' => filter_var($vulnerability['product'], FILTER_SANITIZE_STRING),
                             'vuln_type' => filter_var($vulnerability['type'], FILTER_SANITIZE_STRING),
                             'Vulnerableversion' => filter_var($vulnerability['vulnerableversion'], FILTER_SANITIZE_STRING),
                             'modvulnversion' => $vulnerability['modvulnversion'],
                             'Joomlaversion' => filter_var($vulnerability['joomlaversion'], FILTER_SANITIZE_STRING),
                             'modvulnjoomla' => $vulnerability['modvulnjoomla'],
                             'description' => filter_var($vulnerability['description'], FILTER_SANITIZE_STRING),
                             'vuln_class' => filter_var($vulnerability['class'], FILTER_SANITIZE_STRING),
                             'published' => filter_var($vulnerability['published'], FILTER_SANITIZE_STRING),
                             'vulnerable' => filter_var($vulnerability['vulnerable'], FILTER_SANITIZE_STRING),
                             'solution_type' => filter_var($vulnerability['solution_type'], FILTER_SANITIZE_STRING),
                             'solution' => filter_var($vulnerability['solution'], FILTER_SANITIZE_STRING),
                            );
                        } else if ($this->securitycheck_type == 'com_securitycheck') {
                            $nueva_vulnerabilidad = (object) array(
                            'Product' => filter_var($vulnerability['product'], FILTER_SANITIZE_STRING),
                            'type' => filter_var($vulnerability['type'], FILTER_SANITIZE_STRING),
                            'Vulnerableversion' => filter_var($vulnerability['vulnerableversion'], FILTER_SANITIZE_STRING),
                            'modvulnversion' => $vulnerability['modvulnversion'],
                            'Joomlaversion' => filter_var($vulnerability['joomlaversion'], FILTER_SANITIZE_STRING),
                            'modvulnjoomla' => $vulnerability['modvulnjoomla'],
                            );
                        }
                    
                        $insert_result = JFactory::getDbo()->insertObject($this->vuln_table, $nueva_vulnerabilidad, 'id');
						// Let's write a file to tell securitycheck that new entried have been added to database. This is needed by /com_securitycheckpro/backend/models/securitycheckpros.php		
						$this->write_file();
						
                    }    
                } else if (($key_exists) && ($vulnerability['method'] == 'delete')) {
                    // Método para eliminar una vulnerabilidad
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);
                
                    $product_sanitized = filter_var($vulnerability['product'], FILTER_SANITIZE_STRING);
                    $published_sanitized = filter_var($vulnerability['published'], FILTER_SANITIZE_STRING);
                
                    $conditions = array(
                    $db->quoteName('Product') . ' = ' . $db->quote($product_sanitized),
                    $db->quoteName('published') . ' = ' . $db->quote($published_sanitized)
                    );
                
                    $query->delete($db->quoteName($this->vuln_table));
                    $query->where($conditions);
                
                    $db->setQuery($query);
                    $delete_result = $db->execute();
                }
            }
        }

    }    

    /* Devuelve la versión de la bbdd local */
    function get_database_version()
    {
    
        $db = JFactory::getDbo();
    
        // Consultamos la última comprobación
        $query = $db->getQuery(true)
            ->select($db->quoteName('version'))
            ->from($db->quoteName('#__securitycheckpro_update_database'));
        $db->setQuery($query);
        $version = $db->loadResult();
    
        return $version;
    }

    /* Chequea la última vez que se lanzó una comprobación de nuevas versiones */
    function last_check() 
    {
    
        // Inicializamos las variables
        $last_check = null;

        $db = JFactory::getDbo();
    
        // Consultamos la última comprobación
        $query = $db->getQuery(true)
            ->select($db->quoteName('last_check'))
            ->from($db->quoteName('#__securitycheckpro_update_database'));
        $db->setQuery($query);
        $last_check = $db->loadResult();
		
		// Si el campo está vacío, devolvemos la hora/día actual formateada
        /*if ((isset($last_check)) && (!empty($last_check))) {
			         
        } else {
			$last_check = $this->global_model->get_Joomla_timestamp();   
		}*/
    
        return $last_check;
    }

    /* Función que realiza todo el proceso de comprobación de nuevas vulnerabilidades */
    function tarea_comprobacion()
    {
        
        // Inicializamos las variables
        $result = true;
        $downloadid = null;
        $xml = null;
    
        // Chequeamos el tipo de componente instalado
        $this->check_securitycheck_type();
    
        $mainframe = JFactory::getApplication();
    
        if ($this->securitycheck_type == 'Not_defined') {
            // No hay ninguna versión de Securitycheck instalada!
            $result = false;
        } else
        {    
            // Buscamos el Download ID 
            $plugin = JPluginHelper::getPlugin('system', 'securitycheckpro_update_database');
            if (!empty($plugin)) {
                $params = new JRegistry($plugin->params);
                $downloadid = $params->get('downloadid');
            }
        
            // Si el 'Download ID' está vacío, intentamos extraerlo de SCP
            if (empty($downloadid)) {            
                $app = JComponentHelper::getParams('com_securitycheckpro');
                $downloadid = $app->get('downloadid');            
            }
        
            if (empty($downloadid)) {    
                // Si el 'Download ID' está vacío, escribimos una entrada en el campo 'message' y no realizamos ninguna acción
                $this->set_campo_bbdd('message', 'COM_SECURITYCHECKPRO_UPDATE_DATABASE_DOWNLOAD_ID_EMPTY');
                $result = false;            
            } else 
            {            
                // Url que contendrá el fichero xml (debe contener el Download ID del usuario para poder acceder a ella)
                $xmlfile = "https://securitycheck.protegetuordenador.com/index.php/downloads/securitycheck-pro-database-updates-xml/securitycheck-pro-database-updates-xml-1-0-0/databases-xml?dlid=" . $downloadid;
                        
                // Array que contendrá todo el archivo xml 
                $array_complete = array();
            
                // Leemos el contenido del archivo xml (si existe la función curl_init)
                if (function_exists('curl_init')) {
                    $ch = curl_init($xmlfile);
                    curl_setopt($ch, CURLOPT_USERAGENT, SCP_USER_AGENT);
                    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                    curl_setopt($ch, CURLOPT_FAILONERROR, true);                
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    @curl_setopt($ch, CURLOPT_CAINFO, SCP_CACERT_PEM);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                
                    $xmlresponse = curl_exec($ch);    
                
                    // Si el resultado de la petición es 'false' obtenemos el error para ver qué está pasando
                    if ($xmlresponse === false) {
                        $result = false;
						$message = curl_error($ch);
						
						JFactory::getApplication()->enqueueMessage("Securitycheck Pro Database Update: " . $message, 'error');
                    } else
                    {
                        /* Chequeamos si hay una etiqueta html de redirección, que tendrá el formato '<html><meta http-equiv="refresh" content="0;/.well-known/captcha/?b=http://192.168.56.50/index.php/downloads/securitycheck-pro-database-updates-xml/securitycheck-pro-database-updates-xml-1-0-0/databases-xml?dlid=xx"></meta></head></html>' */
                        $redirection = strpos($xmlresponse, 'meta http-equiv="refresh"');
                    
                        if ($redirection === false) {
                            // No hay etiqueta; leemos el contenido del archivo xml
                            $xml = simplexml_load_string($xmlresponse);
                        } 
                    
                    }                
                    // Cerramos el manejador
                    curl_close($ch);                
                } else
                {
                    JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_CURL_NOT_DEFINED'));
                }
            
                // Comprobamos que hemos leido el archivo xml (esta variable será FALSE, por ejemplo, si no puede conectar con el servidor)
                if ($xml) {                                        
                    // Obtenemos todos los nodos hijos del archivo xml
                    $children  = $xml->children();
                    
                    foreach ($children as $child)
                    {                    
                        // Inicializamos el array de elementos de cada vulnerabilidad
                        $element = array();
                    
                        foreach ($child as $key => $value)
                        {                        
                            // Para cada elemento, convertimos el par clave - valor en un string para poder manejarlo
                            (string) $valores = $key . "#" . $value;
                            $valores = explode("#", $valores);
                                    
                            // Guardamos los elementos en el array , de tal forma que cada array tendrá los conjuntos clave -valor de cada vulnerabilidad
                            $element[$valores[0]] = $valores[1];                    
                        
                        }        
                        // Guardamos todo el contenido del array en el array global
                        array_push($array_complete, $element);    
                    }
                
                    //Extraemos la versión de la bbdd local
                    $local_database_version = $this->get_database_version();
                
                    // Añadimos las nuevas vulnerabilidades a la BBDD
                    $this->add_vuln($array_complete, $local_database_version);    
                } else
                {
                        $result = false;
                
                        $scp_update_database_subscription_status = $mainframe->getUserState("scp_update_database_subscription_status", null);
                    if (empty($scp_update_database_subscription_status)) {
                        /* Establecemos la variable scp_update_database_subscription_status a 'No definida' */    
                        $mainframe->setUserState("scp_update_database_subscription_status", JText::_('COM_SECURITYCHECKPRO_NOT_DEFINED'));
                    }                
                }
            
                // Si el proceso ha sido correcto, actualizamos la bbdd
				$timestamp = $this->global_model->get_Joomla_timestamp();
                if ($result) {					
                    // Actualizamos la fecha de la última comprobación y la versión de la bbdd local					
                    $this->set_campo_bbdd('last_check', $timestamp);
                    $this->set_campo_bbdd('version', $this->higher_database_version);
                    $this->set_campo_bbdd('message', 'PLG_SECURITYCHECKPRO_UPDATE_DATABASE_DATABASE_UPDATED');
				// Si no lo hacemos actualizamos la bbdd para hacer la petición en la siguiente ventana
                } else {					
                    $this->set_campo_bbdd('last_check', $timestamp);
				}
            }
        }    
    }

    /* Función que actualiza un campo de la bbdd '#_securitycheckpro_update_database' con el valor pasado como argumento */
    function set_campo_bbdd($campo,$valor)
    {
        // Creamos el nuevo objeto query
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
    
        // Sanitizamos las entradas
        $campo_sanitizado = $db->escape($campo);
        $valor_sanitizado = $db->Quote($db->escape($valor));

        // Construimos la consulta...
        $query->update('#__securitycheckpro_update_database');
        $query->set($campo_sanitizado .'=' .$valor_sanitizado);
        $query->where('id=1');

        // ... y la lanzamos
        $db->setQuery($query);
        $db->execute();
    }

    
    function check_for_updates()
    {
        // Inicializamos las variables
        $interval = 0;
    
        // Último chequeo realizado
        $last_check = $this->last_check();
		
		// Si no hay consultas previas, establecemos el intervalo a '20' para lanzar una.
        if ((!isset($last_check)) || (empty($last_check))) {
            $interval = 20;
        } else
        {
			$now = $this->global_model->get_Joomla_timestamp();
			
			$seconds = strtotime($now) - strtotime($last_check);
			// Extraemos las horas que han pasado desde el último chequeo
			$interval = intval($seconds/3600);	    
        }
		        
        if ($interval > 12) {
            // Comprobamos si existen nuevas actualizaciones
            $this->tarea_comprobacion();        
        }
            
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
	
	// Writes a file into the scan folder to know that we must update the vulnerabilities database
	function write_file()
    {
				
		$file_manag = @fopen($this->scan_path."update_vuln_table.php", 'ab');		
		
		if (empty($file_manag)) {
            return;
        }
	
		@fclose($file_manag);
    }

}
