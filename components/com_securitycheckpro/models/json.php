<?php
/**
 * @ author Jose A. Luque
 * @copyright Copyright (c) 2013 - Jose A. Luque
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Component\Users\Administrator\Model\UserModel;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\Filesystem\File as JFile;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use Joomla\CMS\Installer\Installer as JInstaller;
use Joomla\CMS\Updater\Update as JUpdate;
use Joomla\CMS\User\UserHelper as JUserHelper;
use Joomla\CMS\Client\FtpClient as JFtpClient;
use Joomla\Filesystem\Path as JPath;
use Joomla\CMS\Http\HttpFactory as JHttpFactory;
use Joomla\CMS\Table\Table as JTable;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

class SecuritycheckProsModelJson extends SecuritycheckproModel
{

	const    STATUS_OK                    = 200;    // Normal reply
	const    STATUS_NOT_AUTH                = 401;    // Invalid credentials
	const    STATUS_NOT_ALLOWED            = 403;    // Not enough privileges
	const    STATUS_NOT_FOUND            = 404;  // Requested resource not found
	const    STATUS_INVALID_METHOD        = 405;    // Unknown JSON method
	const    STATUS_ERROR                = 500;    // An error occurred
	const    STATUS_NOT_IMPLEMENTED        = 501;    // Not implemented feature
	const    STATUS_NOT_AVAILABLE        = 503;    // Remote service not activated

	const    CIPHER_RAW            = 1;    // Data in plain-text JSON
	const    CIPHER_AESCBC256        = 2;    // Data in AES-256 standard (CBC) mode encrypted JSON

	private    $json_errors = array(
	'JSON_ERROR_NONE' => 'No error has occurred (probably emtpy data passed)',
	'JSON_ERROR_DEPTH' => 'The maximum stack depth has been exceeded',
	'JSON_ERROR_CTRL_CHAR' => 'Control character error, possibly incorrectly encoded',
	'JSON_ERROR_SYNTAX' => 'Syntax error'
	);

		// Inicializamos las variables
	private    $status = 200;  // Estado de la petición
	private $cipher = 2;    // Método usado para cifrar los datos
	private $clear_data = '';        // Datos enviados en la petición del cliente (ya en claro)
	public $data = '';        // Datos devueltos al cliente
	private $password = null;
	private $method_name = null;
	private $log_buffer = '******* Start of file ******* </br>';    // Buffer para almacenar el continido del fichero de logs
	private $createfolder = false;    // ¿Se ha creado el directorio para guardar los resultados?
	private $remote_site = '';
	private $same_branch = true;    // ¿Pertenecen los dos sitios a la misma versión de Joomla?
	private $stored_filename = '';    // Fichero remoto descargado
	private $database_name = '';    // Nombre del fichero .out
	private $maintain_db_structure = 0;    // Indica si hemos de mantener la estructura (establecida en configuration.php) del sitio local
	private $database_prefix = null;    // Prefijo de la BBDD local, necesaria si hemos de mantener la estructura de la BBDD local
	private $remote_database_prefix = null;    // Prefijo de la BBDD remota, necesaria si hemos de mantener la estructura de la BBDD local
	private $delete_existing_db = 0;    // Indica si hemos de borrar la BBDD local (aplicable sólo si no hemos de mantener la estructura del sitio)
	private $cipher_file = 0;    // Indica si el fichero remoto está cifrado
	private $backupinfo = array('product' => '', 'latest' => '', 'latest_status' => '', 'latest_type' => '');
	private $update_database_plugin_needs_update = 0;   // Indica si el plugin 'Update Database' necesita actualizarse
	private $info = null;  // Contendrá información sobre el sistema: versión de php, mysql y servidor
	private $site = null;  // Contendrá la url a la que hemos de devolver el callback
	private $site_id = null;  // Contendrá la id de la web en Control Center
	private $log_filename = '';    // Nombre del fichero de logs
	// Establecemos la ruta donde se almacenarán los escaneos
    private $folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans';
	private $array_result = array(); // Contendrá el resultado de las actualizaciones
	
	public function register_task($json)
	{
		$cron_enabled = $this->PluginStatus(2);
		
		if ($cron_enabled == 0)
		{
			return "Error: Cron task is disabled"; 
		} else
		{
		
			$db = $this->getDbo();	
					
			$object = (object)array(
			'storage_key'        => 'remote_task',
			'storage_value'        => $json
			);				
			
			try 
			{
				$result = $db->insertObject('#__securitycheckpro_storage', $object);            
			} catch (Exception $e)
			{    			
				return "Error:" . $e->getMessage(); 
			}
			
			return "Task added";
		}
	}
	
	// Función que realiza una determinada función según los parámetros especificados en la variable pasada como argumento
	public function execute($json)
	{
		$db = JFactory::getDBO();
		$query = "DELETE FROM #__securitycheckpro_storage WHERE storage_key='remote_task'";
		$db->setQuery($query);
		$db->execute();
				
		// Decodificamos el string json
		$json_trimmed = rtrim($json, chr(0));
		
		// Comprobamos que el string JSON es válido y que tiene al menos 12 caracteres (longitud mínima de un mensaje válido)
		if ((strlen($json_trimmed) < 12) || (substr($json_trimmed, 0, 1) != '{') || (substr($json_trimmed, -1) != '}'))
		{
			// El string JSON no es válido, no podemos hacer nada ya que no sabemos a qué dirección devolver la petición
			$this->log_filename = "error.php";
			$message = "Function Execute. JSON not valid.";
			$this->write_log($message,"ERROR");
			return;
		}
		else
		{
			// Decodificamos la petición
			$request = json_decode($json, true);
		}	
		
							
		if (is_null($request))
		{
			// El string JSON no es válido, no podemos hacer nada ya que no sabemos a qué dirección devolver la petición
			$this->log_filename = "error.php";
			$message = "Function Execute. JSON is null.";
			$this->write_log($message,"ERROR");
			return;
		}
		
		// Extraemos los parámetros necesarios para mandar las peticiones en caso de error		
		$this->cipher = $request['cipher'];
		// Site id
		$this->site_id = $request['body']['id'];
		if ( empty($this->site_id) )
		{
			// El site_id no es válido, no podemos hacer nada ya que no sabemos a qué sitio devolver la petición
			return;
		}
		
		// Comprobamos si el frontend está habilitado
		$config = $this->Config('controlcenter');
		
		if (is_null($config))
		{
			// Vamos a usar el referrer como url a la que devolver la petición
			$this->site = $request['referrer'];
			$this->data = "Can't get configuration";
			$this->status = self::STATUS_ERROR;
			$this->cipher = self::CIPHER_RAW;
			
			$this->log_filename = "error.php";
			$message = "Function Execute. Can't get configuration.";
			$this->write_log($message,"ERROR");

			return $this->sendResponse();
		}

		if (!array_key_exists('control_center_enabled', $config))
		{
			$enabled = false;
		}
		else
		{
			$enabled = $config['control_center_enabled'];
		}

		if (array_key_exists('secret_key', $config))
		{
			$this->password = $config['secret_key'];
		}
		else
		{
			// Vamos a usar el referrer como url a la que devolver la petición
			$this->site = $request['referrer'];
			$this->data = 'Remote password not configured';
			$this->status = self::STATUS_NOT_AUTH;
			$this->cipher = self::CIPHER_RAW;
			
			$this->log_filename = "error.php";
			$message = "Function Execute. Remote password not configured.";
			$this->write_log($message,"ERROR");

			return $this->sendResponse();
		}

		// Si el frontend no está habilitado, devolvemos un error 503
		if (!$enabled)
		{
			// Vamos a usar el referrer como url a la que devolver la petición
			$this->site = $request['referrer'];
			$this->data = 'Access denied';
			$this->status = self::STATUS_NOT_AVAILABLE;
			$this->cipher = self::CIPHER_RAW;
						
			$this->log_filename = "error.php";
			$message = "Function Execute. Frontend disabled.";
			$this->write_log($message,"ERROR");

			return $this->sendResponse();
		}
		
		
		// Site to return the callback to; let's decypher it
		if ( !empty($request['body']['site']) )
		{
			$this->site = $request['body']['site'];				
			$this->site = $this->decrypt($this->site, $this->password);
			
			if ( (empty($this->site)) || (strstr($this->site,"Internal") !== false ) )
			{
				if ( empty($this->site) ){
					$this->data = 'Error decrypting data. Are both secret keys equals?';
				} else 
				{
					$this->data = $this->site . '. Are both secret keys equals?';
					$this->log_filename = "error.php";
					$message = "Getting site error. Error decrypting data. Are both secret keys equals?";
					$this->write_log($message,"ERROR");
				}
				// Vamos a usar el referrer como url a la que devolver la petición
				if ( (array_key_exists('referrer',$request)) && (!empty($request['referrer'])) ) 
				{
					$this->site = $request['referrer'];
					$this->status = self::STATUS_ERROR;
					$this->cipher = self::CIPHER_RAW;				
										
					return $this->sendResponse();
				}
			}
				
		} else
		{
			$this->log_filename = "error.php";
			$message = "Function Execute. Error decrypting data. Are both secret keys equals?";
			$this->write_log($message,"ERROR");
			
			if ( (array_key_exists('referrer',$request)) && (!empty($request['referrer'])) ) 
			{
				// Vamos a usar el referrer como url a la que devolver la petición
				$this->site = $request['referrer'];
				
				$this->data = 'Error decrypting data. Are both secret keys equals?';
				$this->status = self::STATUS_ERROR;
				$this->cipher = self::CIPHER_RAW;				
										
				return $this->sendResponse();
			}
		}			
		
		
					
		// Decodificamos el 'body' de la petición
		if (isset($request['cipher']) && isset($request['body']))
		{
			switch ($request['cipher'])
			{
				case self::CIPHER_RAW:
					if (($request['body']['task'] == "getStatus") || ($request['body']['task'] == "checkVuln") || ($request['body']['task'] == "checkLogs") || ($request['body']['task'] == "checkPermissions") || ($request['body']['task'] == "checkIntegrity") || ($request['body']['task'] == "deleteBlocked") || ($request['body']['task'] == "checkmalware") || ($request['body']['task'] == "UpdateExtension") || ($request['body']['task'] == "Backup") || ($request['body']['task'] == "unlocktables") || ($request['body']['task'] == "locktables") || ($request['body']['task'] == "server_statistics") || ($request['body']['task'] == "enable_analytics") || ($request['body']['task'] == "disable_analytics"))
					{
						/* Los resultados de todas las tareas se devuelven cifrados; si recibimos una petición para devolverlos sin cifrar, la rechazamos
						porque será fraudulenta */
						$this->data = 'Go away, hacker!';
						$this->status = self::STATUS_NOT_ALLOWED;
						$this->cipher = self::CIPHER_RAW;

						return $this->sendResponse();
					}
				break;				

				case self::CIPHER_AESCBC256:
					if (!is_null($request['body']['data']))
					{
						// $this->clear_data = $this->mc_decrypt_256($request->body->data, $this->password);
					}
				break;
			}	
				
			// Let's update the url from which we have received the task and prepare the log file
			try
			{
				$params = JComponentHelper::getParams('com_securitycheckpro');
				$max_log_size = $params->get('controlcenter_log_size', 2048);
				$cc_config = $this->Config('controlcenter');
				$cc_config['control_center_url'] = $this->site;
				$this->SaveStorageParams($cc_config,'controlcenter');	
				JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
				$filemanager_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('filemanager', 'SecuritycheckprosModel');
				$this->log_filename = $filemanager_model->get_log_filename("controlcenter_log", true);
				if (empty($this->log_filename)) {
					$this->log_filename = $filemanager_model->prepareLog("controlcenter",true);					
				} else if ( (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->log_filename)) && (filesize($this->folder_path.DIRECTORY_SEPARATOR.$this->log_filename) > ($max_log_size * 1024)) ) {
					//Rotate log file
					JFile::delete($this->folder_path.DIRECTORY_SEPARATOR.$this->log_filename);
					$this->log_filename = $filemanager_model->prepareLog("controlcenter",true);
				}								
				
				
			} catch (Exception $e)
			{
				$this->log_filename = "error.php";
				$message = "Function Execute. " . $e->getMessage();
				$this->write_log($message,"ERROR");				
			} 			
						
			switch ($request['body']['task'])
			{
				case "getStatus":
					$this->getStatus();
					break;

				case "checkVuln":
					$this->checkVuln();
					break;

				case "checkLogs":
					$this->checkLogs();
					break;

				case "checkPermissions":
					$this->checkPermissions();
					break;

				case "checkIntegrity":
					$this->checkIntegrity();
					break;

				case "deleteBlocked":
					$this->deleteBlocked();
					break;

				case "checkmalware":
					$this->checkMalware();
					break;

				case "UpdateComponent":
					$this->UpdateComponent();
					break;

				case "UpdateExtension":
					$this->UpdateExtension($request['body']['data']);
					break;

				case "Backup":
					$this->Backup($request['body']['data']);
					break;

				case "Uploadinstall":
					$this->Upload_install($request['body']['data']);
					break;

				case "Connect":
					$this->Connect();
					break;

				case "UpdateConnect":					
					$this->UpdateConnect($request['body']['data']);
					break;

				case "unlocktables":
					$this->write_log("UNLOCKTABLES task received");
					$this->unlocktables();
					break;

				case "locktables":
					$this->locktables();
					break;

				case "server_statistics":
					$this->server_statistics();
					break;
					
				case "enable_analytics":
					$this->write_log("ENABLE_ANALYTICS task received");
					$this->enable_analytics($request['body']['data']);
					break;
					
				case "disable_analytics":
					$this->write_log("DISABLE_ANALYTICS task received");
					$this->disable_analytics($request['body']['data']);
					break;

				case self::CIPHER_AESCBC256:
					break;
					
				default:
					$this->data = 'Method not configured';
					$this->status = self::STATUS_NOT_FOUND;
					$this->cipher = self::CIPHER_RAW;
					return $this->sendResponse();
			}

			return $this->sendResponse();
		}
	}

		// Función que empaqueta una respuesta en formato JSON codificado, cifrando los datos si es necesario

	public function sendResponse($connect_back_url=null)
	{
		
		// Inicializamos la respuesta
		$response = array(
			'cipher'    => $this->cipher,
			'body'        => array(
				'status'        => $this->status,
				'data'            => null,
				'id'            => $this->site_id
			)
		);
		
		
		// Codificamos los datos enviados en formato JSON
		$data = json_encode($this->data);
		
		$this->write_log("Sending response. Data: " . $data);		
				
		// Ciframos o no los datos según el método establecido en la petición
		switch ($this->cipher)
		{
			case self::CIPHER_RAW:
			break;		

			case self::CIPHER_AESCBC256:
				$data = $this->encrypt($data, $this->password);
			break;
		}

		// Guardamos los datos...
		$response['body']['data'] = $data;
		
		$response = json_encode($response);
		
		// If 'connect_back_url' is not empty will contain the url to which return the result. Used in the "Connect" task
		if (!empty($connect_back_url)) {
			$this->site = $connect_back_url;
		}
						
		// ... y los devolvemos al cliente
		$ch = curl_init($this->site . "index.php?option=com_securitycheckprocontrolcenter&view=json&format=raw&json=" . urlencode($response));
		
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);	
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			
		$response = curl_exec($ch);
		
		$this->write_log("Response sent to " . $this->site);
		if ($response === false) {
			$message = curl_error($ch);
			$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);		
			$this->write_log("RESPONSE: Error " . $httpcode . " " . $message);	
		} else {
			$this->write_log("Curl reply " . $response);
		}
	}

	// Extraemos los parámetros del componente
	private function Config($key_name)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName('storage_value'))
			->from($db->quoteName('#__securitycheckpro_storage'))
			->where($db->quoteName('storage_key') . ' = ' . $db->quote($key_name));
		$db->setQuery($query);
		$res = $db->loadResult();
		$res = json_decode($res, true);

		return $res;
	}
	
	// Guardamos los parámetros del componente
	private function SaveStorageParams($params,$key_name)
	{
		$db = JFactory::getDBO();
		
		$storage_value = json_encode($params);
		// Instanciamos un objeto para almacenar los datos que serán sobreescritos/añadidos
        $object = new StdClass();                    
        $object->storage_key = $key_name;
        $object->storage_value = $storage_value;
		
		try {
			$db->updateObject('#__securitycheckpro_storage', $object, 'storage_key');
		} catch (Exception $e)
		{
			$this->log_filename = "error.php";
			$message = "Function SaveStorageParams. " . $e->getMessage();
			$this->write_log($message,"ERROR");
		} 		
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
	
	/* Crea un log de una tarea lanzada */
    function write_log($message,$level="INFO")
    {
				
		$fp2 = @fopen($this->folder_path.DIRECTORY_SEPARATOR.$this->log_filename, 'ab');		
		
		if (empty($fp2)) {
            return;
        }
	
		$string = $level . "    |   ";
		$timestamp = $this->get_Joomla_timestamp();
		$string .= $timestamp . "   |   $message |\r\n";	

		@fwrite($fp2, $string);
		@fclose($fp2);
    }
	

	// Función que verifica una fecha
	public function verifyDate($date, $strict = true)
	{
		$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);

		if ($strict)
		{
			$errors = DateTime::getLastErrors();

			if (!empty($errors['warning_count']))
			{
				return false;
			}
		}

		return $dateTime !== false;
	}

	// Función que devuelve el estado de la extensión remota

	public function getStatus($opcion=true)
	{
		
		$this->write_log("Launching GETSTATUS task");
		
		// Inicializamos las variables
		$extension_updates = null;
		$installed_version = "0.0.0";
		$hasUpdates = 0;

		$db = JFactory::getDBO();

		// Buscamos la versión de SCP instalada
		$query = $db->getQuery(true)
			->select($db->quoteName('manifest_cache'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('name') . ' = ' . $db->quote('Securitycheck Pro'));
		$db->setQuery($query);
		$result = $db->loadResult();
		$manifest = json_decode($result);
		$installed_version = isset($manifest->version) ? $manifest->version : "0.0.0";
		
		$this->write_log("Importing models...");
		// Import Securitycheckpros model
		JLoader::import('cpanel', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
		JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
		JLoader::import('databaseupdates', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'helpers');

		$cpanel_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('cpanel', 'SecuritycheckprosModel');
		$filemanager_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('filemanager', 'SecuritycheckprosModel');
		$update_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('databaseupdates', 'SecuritycheckprosModel');
		
		if ((empty($cpanel_model)) || (empty($filemanager_model)) || (empty($update_model)))
		{
			$this->write_log("Error retreiving external models","ERROR");
			return;
		}

		$this->write_log("Getting update database plugin status...");
		// Comprobamos el estado del plugin Update Database
		$update_database_plugin_installed = $update_model->PluginStatus(4);
		$update_database_plugin_version = $update_model->get_database_version();
		$update_database_plugin_last_check = $update_model->last_check();

		// Check for vulnerable components
		// $cpanel_model->buscarQuickIcons();
		
		$this->write_log("Checking vulnerable extensions...");
		// Vulnerable components
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM #__securitycheckpro WHERE 'Vulnerable'='Si'";
		$db->setQuery($query);
		$db->execute();
		$vuln_extensions = $db->loadResult();
		
		$this->write_log("Checking unread logs...");
		// Check for unread logs
		(int) $logs_pending = $cpanel_model->LogsPending();
		
		$this->write_log("Getting info from permissions, integrity and malware scan...");
		// Get files with incorrect permissions from database
		$files_with_incorrect_permissions = $filemanager_model->loadStack("filemanager_resume", "files_with_incorrect_permissions");

		// If permissions task has not been launched, we set a '0' value.
		if (is_null($files_with_incorrect_permissions))
		{
			$files_with_incorrect_permissions = 0;
		}

		// FileManager last check
		$last_check = $filemanager_model->loadStack("filemanager_resume", "last_check");

		// Get files with incorrect integrity from database
		$files_with_bad_integrity = $filemanager_model->loadStack("fileintegrity_resume", "files_with_bad_integrity");

		// If permissions task has not been launched, whe set a '0' value.
		if (is_null($files_with_bad_integrity))
		{
			$files_with_bad_integrity = 0;
		}

		// FileIntegrity last check
		$last_check_integrity = $filemanager_model->loadStack("fileintegrity_resume", "last_check_integrity");

		// Malwarescan last check
		$last_check_malwarescan = $filemanager_model->loadStack("malwarescan_resume", "last_check_malwarescan");

		// Get suspicious files
		$suspicious_files = $filemanager_model->loadStack("malwarescan_resume", "suspicious_files");

		// Última optimización bbdd
		$last_check_database_optimization = $this->get_campo_filemanager('last_check_database');

		// If malwarescan has not been launched, we set a '0' value.
		if (is_null($suspicious_files))
		{
			$suspicious_files = 0;
		}
		
		$this->write_log("Getting backup info...");
		// Comprobamos el estado del backup
		$this->getBackupInfo();

		// Verificamos si el core está actualizado (obviando la caché)
		if (version_compare(JVERSION, '3.20', 'lt'))
		{
			include_once JPATH_ROOT . '/administrator/components/com_joomlaupdate/models/default.php';
			$updatemodel = new JoomlaupdateModelDefault;
		}
		else
		{
			include_once JPATH_ROOT . '/administrator/components/com_joomlaupdate/src/Model/UpdateModel.php';
			$updatemodel = new UpdateModel;
		}

		$updatemodel->refreshUpdates(true);
		$coreInformation = $updatemodel->getUpdateInformation();

		// Si el plugin 'Update Batabase' está instalado, comprobamos si está actualizado
		if ($update_database_plugin_installed)
		{
			$this->update_database_plugin_needs_update = $this->checkforUpdate();
		}
		else
		{
			$this->update_database_plugin_needs_update = 0;
		}
		
		$this->write_log("Getting system info...");
		// Añadimos la información del sistema
		$this->getInfo();
		
		$this->write_log("Getting htaccess protection config...");
		// Obtenemos las opciones de protección .htaccess
		include_once JPATH_ROOT . '/administrator/components/com_securitycheckpro/models/protection.php';
		$ConfigApplied = new SecuritycheckprosModelProtection;
		$ConfigApplied = $ConfigApplied->GetConfigApplied();

		// Si el directorio de administración está protegido con contraseña, marcamos la opción de protección del backend como habilitada
		if (!$ConfigApplied['hide_backend_url'])
		{
			if (file_exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . '.htpasswd'))
			{
				$ConfigApplied['hide_backend_url'] = '1';
			}
		}

		// Si se ha seleccionado la opción de "backend protected using other options" ponemos "hide_backend_url" como enable porque esta opción marca si el backend está habilitado
		if ($ConfigApplied['backend_protection_applied'] == 1)
		{
			$ConfigApplied['hide_backend_url'] = '1';
		}
		
		$this->write_log("Getting firewall config...");
		// Obtenemos los parámetros del Firewall
		include_once JPATH_ROOT . '/administrator/components/com_securitycheckpro/library/model.php';
		$FirewallOptions = new SecuritycheckproModel;
		$FirewallOptions = $FirewallOptions->getConfig();
		
		$this->write_log("Checking if kickstart exists...");
		// Chequeamos si existe el fichero kickstart
		$kickstart = $this->check_kickstart();

		$this->write_log("Getting 2FA status...");
		// Chequeamos si el segundo factor de autenticación está habilitado
		$two_factor = $this->get_two_factor_status(true);

		$this->write_log("Getting info about outdated extensions...");
		// Añadimos la información sobre las extensiones no actualizadas. Esta opción no es necesaria cuando escogemos la opción 'System Info'
		if ($opcion)
		{
			$extension_updates = $this->getNotUpdatedExtensions();
			$outdated_extensions = json_decode($extension_updates, true);
			$sc_to_find = "Securitycheck Pro";
			$key_sc = array_search($sc_to_find, array_column($outdated_extensions, 2));

			if ($key_sc !== false)
			{
				$installed_version = $outdated_extensions[$key_sc][4];
				$hasUpdates = 1;
			}
		}

		// Si no hay backup establecemos la fecha actual para evitar un error en la bbdd al insertar el valor
		$is_valid_date = $this->verifyDate($this->backupinfo['latest']);

		if (!$is_valid_date)
		{
			$this->backupinfo['latest'] = "0000-00-00 00:00:00";
		}
		
		$this->write_log("Getting lock tables status...");
		// Chequeamos si las tablas están bloqueadas
		$tables_locked = $this->check_locked_tables();

		$this->data = array(
			'vuln_extensions'        => $vuln_extensions,
			'logs_pending'    => $logs_pending,
			'files_with_incorrect_permissions'        => $files_with_incorrect_permissions,
			'last_check' => $last_check,
			'files_with_bad_integrity'        => $files_with_bad_integrity,
			'last_check_integrity' => $last_check_integrity,
			'installed_version'    => $installed_version,
			'hasUpdates'    => $hasUpdates,
			'coreinstalled'    => $coreInformation['installed'],
			'corelatest'    => $coreInformation['latest'],
			'last_check_malwarescan' => $last_check_malwarescan,
			'suspicious_files'        => $suspicious_files,
			'update_database_plugin_installed'    => $update_database_plugin_installed,
			'update_database_plugin_version'    => $update_database_plugin_version,
			'update_database_plugin_last_check'    => $update_database_plugin_last_check,
			'update_database_plugin_needs_update'    => $this->update_database_plugin_needs_update,
			'backup_info_product'    => $this->backupinfo['product'],
			'backup_info_latest'    => $this->backupinfo['latest'],
			'backup_info_latest_status'    => $this->backupinfo['latest_status'],
			'backup_info_latest_type'    => $this->backupinfo['latest_type'],
			'php_version'    => $this->info['phpversion'],
			'database_version'    => $this->info['dbversion'],
			'web_server'    => $this->info['server'],
			'extension_updates'    => $extension_updates,
			'last_check_database_optimization'    => $last_check_database_optimization,
			'overall'    => 200,
			'twofactor_enabled'    => $two_factor,
			'backend_protection'    => $ConfigApplied['hide_backend_url'],
			'forbid_new_admins'        => $FirewallOptions['forbid_new_admins'],
			'kickstart_exists'    => $kickstart,
			'tables_blocked'    => $tables_locked
		);

		// Obtenemos el porcentaje para 'Overall security status'
		$overall = $this->getOverall($this->data);
		$this->data['overall'] = $overall;
		
		$this->write_log("GETSTATUS task finished");

	}

	// Chequea si la opción "Lock tables" está habilitada
	function check_locked_tables()
	{
		$locked = false;

		try
		{
			$db = $this->getDbo();
			$query = 'SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key="locked"';
			$db->setQuery($query);
			$db->execute();
			$locked = $db->loadResult();
		}
		catch (Exception $e)
		{
			$this->log_filename = "error.php";
			$message = "Function check_locked_tables. " . $e->getMessage();
			$this->write_log($message,"ERROR");
			return 0;
		}

		return $locked;
	}

	// Chequea si el fichero kickstart.php existe en la raíz del sitio. Esto sucede cuando se restaura un sitio y se olvida (junto con algún backup) eliminarlo.
	function check_kickstart()
	{
		$found = false;
		$akeeba_kickstart_file = JPATH_ROOT . DIRECTORY_SEPARATOR . "kickstart.php";

		if (file_exists($akeeba_kickstart_file))
		{
			if (strpos(file_get_contents($akeeba_kickstart_file), "AKEEBA") !== false)
			{
				$found = true;
			}
		}

		return $found;

	}

	// Obtiene el estado del segundo factor de autenticación de Joomla (Google y Yubikey)
	function get_two_factor_status($overall=false)
	{
		$enabled = 0;

		// Si la variable "overall" es false utilizamos el método getTwoFactorMethods para obtener la información de los plugins; si es true no podemos usar ese método ya que necesitamos que el usuario esté logado

		if (!$overall)
		{
			$methods = JAuthenticationHelper::getTwoFactorMethods();

			if (count($methods) > 1)
			{
				$enabled = 1;

				// Chequeamos que al menos un Super usuario tenga el método habilitado
				try
				{
					$db = JFactory::getDBO();
					$query = 'SELECT user_id FROM #__user_usergroup_map WHERE group_id="8"';
					$db->setQuery($query);
					$db->execute();
					$super_users_ids = $db->loadColumn();
				}
				catch (Exception $e)
				{
					$this->log_filename = "error.php";
					$message = "Function get_two_factor_status. " . $e->getMessage();
					$this->write_log($message,"ERROR");
					return 1;
				}

				if (version_compare(JVERSION, '3.20', 'lt'))
				{
					JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models', 'UsersModel');

					// @var UsersModelUser $model
					$model = JModelLegacy::getInstance('User', 'UsersModel', array('ignore_request' => true));
				}
				else
				{
					$model = new UserModel(array('ignore_request' => true));
				}

				foreach ($super_users_ids as $user_id)
				{
					$otpConfig = $model->getOtpConfig($user_id);

					// Check if the user has enabled two factor authentication
					if (!empty($otpConfig->method) && !($otpConfig->method === 'none'))
					{
						$enabled = 2;
					}
				}
			}
		}
		else
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select(array($db->quoteName('enabled')))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('name') . ' = ' . $db->quote('plg_twofactorauth_totp'));
				$db->setQuery($query);
				$enabled = $db->loadResult();
			}
			catch (Exception $e)
			{
				$this->log_filename = "error.php";
				$message = "Function get_two_factor_status - second else condition. " . $e->getMessage();
				$this->write_log($message,"ERROR");
			}

			if ($enabled == 0)
			{
				try
				{
					$query = $db->getQuery(true)
						->select(array($db->quoteName('enabled')))
						->from($db->quoteName('#__extensions'))
						->where($db->quoteName('name') . ' = ' . $db->quote('plg_twofactorauth_yubikey'));
					$db->setQuery($query);
					$enabled = $db->loadResult();
				}
				catch (Exception $e)
				{
					$this->log_filename = "error.php";
					$message = "Function get_two_factor_status - third condition. " . $e->getMessage();
					$this->write_log($message,"ERROR");
				}
			}
		}

		return $enabled;
	}

		// Obtiene el porcentaje general de cada una de las barras de progreso
	function getOverall($info)
	{
		// Inicializamos variables
		$overall = 0;

		if ($info['kickstart_exists'])
		{
			return 2;
		}

		if (version_compare($info['coreinstalled'], $info['corelatest'], '=='))
		{
			$overall = $overall + 10;
		}

		if ($info['files_with_incorrect_permissions'] == 0)
		{
			$overall = $overall + 5;
		}

		if ($info['files_with_bad_integrity'] == 0)
		{
			$overall = $overall + 10;
		}

		if ($info['vuln_extensions'] == 0)
		{
			$overall = $overall + 30;
		}

		if ($info['suspicious_files'] == 0)
		{
			$overall = $overall + 20;
		}

		if ($info['backend_protection'])
		{
			$overall = $overall + 10;
		}

		if ($info['forbid_new_admins'] == 1)
		{
			$overall = $overall + 5;
		}

		if ($info['twofactor_enabled'] >= 1)
		{
			$overall = $overall + 10;
		}

		return $overall;
	}

		// Función que comprueba si existen extensiones vulnerables

	private function checkVuln()
	{
		$this->write_log("Launching CHECKVULN task");
		
		$this->write_log("Getting models...");
		// Import Securitycheckpros model
		JLoader::import('securitycheckpros', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
		JLoader::import('databaseupdates', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'helpers');

		$securitycheckpros_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('securitycheckpros', 'SecuritycheckprosModel');
		$update_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('databaseupdates', 'SecuritycheckprosModel');
		
		$this->write_log("Looking for updates...");
		// Comprobamos si existen nuevas actualizaciones
		$result = $update_model->tarea_comprobacion();

		// Comprobamos el estado del plugin Update Database
		$update_database_plugin_installed = $update_model-> PluginStatus(4);
		$update_database_plugin_version = $update_model->get_database_version();
		$update_database_plugin_last_check = $update_model->last_check();
		
		$this->write_log("Looking for vulnerable extensions...");
		// Hacemos una nueva comprobación de extensiones vulnerables
		$securitycheckpros_model->chequear_vulnerabilidades();

		// Vulnerable components
		$db = JFactory::getDBO();
		$query = 'SELECT COUNT(*) FROM #__securitycheckpro WHERE Vulnerable="Si"';
		$db->setQuery($query);
		$db->execute();
		$vuln_extensions = $db->loadResult();

		$this->data = array(
		'vuln_extensions'        => $vuln_extensions,
		'update_database_plugin_installed'    => $update_database_plugin_installed,
		'update_database_plugin_version'    => $update_database_plugin_version,
		'update_database_plugin_last_check'    => $update_database_plugin_last_check
		);
		
		$this->write_log("CHECKVULN task finished");
	}

		// Función que comprueba si existen logs por leer

	private function checkLogs()
	{
		$this->write_log("Launching CHECKLOGS task");
		
		$this->write_log("Getting models...");
		
		// Import Securitycheckpros model
		JLoader::import('joomla.application.component.model');
		JLoader::import('cpanel', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');

		$cpanel_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('cpanel', 'SecuritycheckprosModel');
		
		$this->write_log("Checking unread logs...");
		// Check for unread logs
		(int) $logs_pending = $cpanel_model->LogsPending();

		$this->data = array(
		'logs_pending'    => $logs_pending
		);
		
		$this->write_log("CHECKLOGS task finished");

	}

	// Función que lanza un chequeo de permisos
	private function checkPermissions()
	{
		$this->write_log("Launching CHECKPERMISSIONS task");
		
		$this->write_log("Getting models...");
		
		// Import Securitycheckpros model
		JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');

		$filemanager_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('filemanager', 'SecuritycheckprosModel');
		
		$this->write_log("Launching permissions scan...");
		
		$filemanager_model->set_campo_filemanager('files_scanned', 0);
		$timestamp = $this->get_Joomla_timestamp();
		$filemanager_model->set_campo_filemanager('last_check', $timestamp);
		$filemanager_model->set_campo_filemanager('estado', 'IN_PROGRESS');
		$filemanager_model->scan("permissions");
		
		$this->write_log("Retrieving status...");
		
		// Get files with incorrect permissions from database
		$files_with_incorrect_permissions = $filemanager_model->loadStack("filemanager_resume", "files_with_incorrect_permissions");

		// If permissions task has not been launched, we set a '0' value.
		if (is_null($files_with_incorrect_permissions))
		{
			$files_with_incorrect_permissions = 0;
		}

		// FileManager last check
		$last_check = $filemanager_model->loadStack("filemanager_resume", "last_check");

		$this->data = array(
		'files_with_incorrect_permissions'        => $files_with_incorrect_permissions,
		'last_check' => $last_check
		);
		
		$this->write_log("CHECKPERMISSIONS task finished");

	}

		// Función que lanza un chequeo de integridad

	private function checkIntegrity()
	{
		$this->write_log("Launching CHECKINTEGRITY task");
		
		$this->write_log("Getting models...");
		
		// Import Securitycheckpros model
		JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');

		$filemanager_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('filemanager', 'SecuritycheckprosModel');
		
		$this->write_log("Launching integrity scan...");

		$filemanager_model->set_campo_filemanager('files_scanned_integrity', 0);
		$timestamp = $this->get_Joomla_timestamp();
		$filemanager_model->set_campo_filemanager('last_check_integrity', $timestamp);
		$filemanager_model->set_campo_filemanager('estado_integrity', 'IN_PROGRESS');
		$filemanager_model->scan("integrity");
		
		$this->write_log("Retrieving status...");

		// Get files with incorrect permissions from database
		$files_with_bad_integrity = $filemanager_model->loadStack("fileintegrity_resume", "files_with_bad_integrity");

		// If permissions task has not been launched, we set a '0' value.
		if (is_null($files_with_bad_integrity))
		{
			$files_with_bad_integrity = 0;
		}

		// FileIntegrity last check
		$last_check_integrity = $filemanager_model->loadStack("fileintegrity_resume", "last_check_integrity");

		$this->data = array(
		'files_with_bad_integrity'        => $files_with_bad_integrity,
		'last_check_integrity' => $last_check_integrity
		);
		
		$this->write_log("CHECKINTEGRITY task finished");

	}

	// Borra los logs pertenecientes a intentos de acceso bloqueados
	private function deleteBlocked()
	{
		$this->write_log("Launching DELETEBLOCKED task");
		
		$this->write_log("Getting models...");
		
		// Import Securitycheckpros model
		JLoader::import('cpanel', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');

		$cpanel_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('cpanel', 'SecuritycheckprosModel');
		
		// Vulnerable components
		$db = JFactory::getDBO();
		$query = 'DELETE FROM #__securitycheckpro_logs';
		$db->setQuery($query);
		$db->execute();

		// Check for unread logs
		(int) $logs_pending = $cpanel_model->LogsPending();

		$this->data = array(
			'logs_pending'    => $logs_pending
		);
		
		$this->write_log("DELETEBLOCKED task finished");
	}

	

	// Obtiene información de los requisitos necesarios para clonar una web
	private function CheckPrereq()
	{

		// Inicializamos las variables
		$server_type = 0;  // Sistema operativo 'Linux'
		$safe_mode = 0;
		$mysqldump = null;
		$tar = null;

		/*
         Chequeamos los requisitos */
		// Tipo de servidor
		$os = php_uname("s");

		if (strstr($os, 'Windows'))
		{
			$server_type = 1;
		}
		elseif (strstr($os, 'Mac'))
		{
			$server_type = 2;
		}

		// 'Safe_mode'
		if (ini_get('safe_mode'))
		{
			$safe_mode = 1;
		}

		$this->data = array(
			'server_type'    => $server_type,
			'safe_mode'    => $safe_mode,
			'mysqldump'    => $mysqldump,
			'tar'    => $tar
		);

	}

	// Función que actualiza el Core de Joomla a la última versión disponible
	private function UpdateCore()
	{
		$this->write_log("Updating CORE...");
			
		// Cargamos el lenguaje del componente 'com_installer'
		$lang = JFactory::getLanguage();
		$lang->load('com_installer', JPATH_ADMINISTRATOR);

		// Inicializamos la variable $result, que será un array con el resultado y el mensaje devuelto en el proceso
		$result = array();

		// Cargamos las librerías necesarias
		if (version_compare(JVERSION, '3.20', 'lt'))
		{
			include_once JPATH_ROOT . '/administrator/components/com_joomlaupdate/models/default.php';
			// Instanciamos el modelo
			$model = new JoomlaupdateModelDefault;
		}
		else
		{
			include_once JPATH_ROOT . '/administrator/components/com_joomlaupdate/src/Model/UpdateModel.php';
			// Instanciamos el modelo
			$model = new UpdateModel;
		}

		// Refrescamos la información de las actualizaciones ignorando la caché
		$model->refreshUpdates(true);

		// Extraemos la url de descarga
		$coreInformation = $model->getUpdateInformation();
						
		try
		{
			// Descargamos el archivo
			$file = $this->download_core($coreInformation['object']->downloadurl->_data);
			
			// Extract the downloaded package file
			$config   = JFactory::getConfig();
			$tmp_dest = $config->get('tmp_path');

			if ( !class_exists('ZipArchive') )
			{
				$msg = JText::sprintf('COM_SECURITYCHECKPRO_MISSING_CLASS', 'ZipArchive');
				$result[0][1] = $msg;
				$result[0][0] = 2;				
				return $result;
			}
			
			$zip = new ZipArchive;					
			$res = $zip->open($tmp_dest . DIRECTORY_SEPARATOR . $file);				

			if ($res === true)
			{
				$zip->extractTo(JPATH_SITE);
				$zip->close();
			}
			
			// Cargamos las librerías necesarias
			if (version_compare(JVERSION, '3.20', 'lt'))
			{
				$this->createRestorationFile($file);			
				$install_result = $this->finaliseUpgrade();
			}
			else
			{
				$install_result = $model->finaliseUpgrade();
				\JLoader::register('JNamespacePsr4Map', JPATH_LIBRARIES . '/namespacemap.php');
				// Re-create namespace map. It is needed when updating to a Joomla! version has new extension added
				(new \JNamespacePsr4Map)->create();	
			}
			
			if (!$install_result)
			{
				$msg = JText::_('COM_INSTALLER_MSG_UPDATE_ERROR');
				$result[0][1] = $msg;
				$result[0][0] = 2;
			}
			else
			{
				$result[0][1] = 'Core updated';
				$result[0][0] = 1;
			}

				// Clean the site
			JFile::delete($tmp_dest . DIRECTORY_SEPARATOR . $file);
		}
		catch (Exception $e)
		{
			$this->log_filename = "error.php";
			$message = "Function UpdateCore. " . $e->getMessage();
			$this->write_log($message,"ERROR");
			$result[0][1] = $e->getMessage();
			$result[0][0] = 2;
		}
		
		// Devolvemos el resultado
		return $result;
	}
	
	

	/**
	 * Install an extension from either folder, url or upload.
	 *
	 * @return boolean result of install
	 *
	 * @since 1.5
	 */
	public function install($url)
	{
		$this->setState('action', 'install');

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');
		$app = JFactory::getApplication();

		// Load installer plugins for assistance if required:
		JPluginHelper::importPlugin('installer');
		$dispatcher = \JFactory::getApplication();

		$package = null;

		// This event allows an input pre-treatment, a custom pre-packing or custom installation (e.g. from a JSON description)
		$results = $dispatcher->triggerEvent('onInstallerBeforeInstallation', array($this, &$package));

		if (in_array(true, $results, true))
		{
			return true;
		}
		elseif (in_array(false, $results, true))
		{
			return false;
		}

		$installType = 'url';

		if ($package === null)
		{
			switch ($installType)
			{
				case 'folder':
					// Remember the 'Install from Directory' path.
					$app->getUserStateFromRequest($this->_context . '.install_directory', 'install_directory');
					$package = $this->_getPackageFromFolder();
				break;

				case 'upload':
					$package = $this->_getPackageFromUpload();
				break;

				case 'url':
					$package = $this->_getPackageFromUrl($url);
				break;

				default:
					$app->setUserState('com_installer.message', JText::_('COM_INSTALLER_NO_INSTALL_TYPE_FOUND'));

				return false;
					break;
			}
		}

		// This event allows a custom installation of the package or a customization of the package:
		$results = $dispatcher->triggerEvent('onInstallerBeforeInstaller', array($this, &$package));

		if (in_array(true, $results, true))
		{
			return true;
		}
		elseif (in_array(false, $results, true))
		{
			if (in_array($installType, array('upload', 'url')))
			{
				//JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}

			return false;
		}

		// Was the package unpacked?
		if (!$package || !$package['type'])
		{
			if (in_array($installType, array('upload', 'url')))
			{
				//JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}

			$app->setUserState('com_installer.message', JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'));

			return false;
		}

		// Get an installer instance
		$installer = JInstaller::getInstance();

		// Install the package
		if (!$installer->install($package['dir']))
		{
			// There was an error installing the package
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = false;
		}
		else
		{
			// Package installed sucessfully
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = true;
		}

		// This event allows a custom a post-flight:
		$dispatcher->triggerEvent('onInstallerAfterInstaller', array($this, &$package, $installer, &$result, &$msg));

		// Set some model state values
		$app    = JFactory::getApplication();
		$app->enqueueMessage($msg);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
		$app->setUserState('com_installer.redirect_url', $installer->get('redirect_url'));

		// Cleanup the install files
		/*if (!is_file($package['packagefile']))
		{
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);*/

		return $result;
	}


		/**
		 * Install an extension from a URL
		 *
		 * @return Package details or false on failure
		 *
		 * @since 1.5
		 */
	protected function _getPackageFromUrl($url)
	{
		$input = JFactory::getApplication()->input;

		// Get the URL of the package to install
		// $url = $input->getString('install_url');

		// Did you give us a URL?
		if (!$url)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), 'warning');

			return false;
		}

		// Handle updater XML file case:
		if (preg_match('/\.xml\s*$/', $url))
		{
			$update = new JUpdate;
			$update->loadFromXML($url);
			$package_url = trim($update->get('downloadurl', false)->_data);

			if ($package_url)
			{
				$url = $package_url;
			}

			unset($update);
		}

		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), 'warning');

			return false;
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file, true);

		return $package;
	}

		// Función que lanza un chequeo en busca de malware

	private function checkMalware()
	{
		$this->write_log("Launching CHECKMALWARE task");
		
		$this->write_log("Getting models...");
		
		// Import Securitycheckpros model
		JLoader::import('joomla.application.component.model');
		JLoader::import('filemanager', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');

		$filemanager_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('filemanager', 'SecuritycheckprosModel');
		
		$this->write_log("Launching malware scan...");
		
		$filemanager_model->set_campo_filemanager('files_scanned_malwarescan', 0);
		$timestamp = $this->get_Joomla_timestamp();
		$filemanager_model->set_campo_filemanager('last_check_malwarescan', $timestamp);
		$filemanager_model->set_campo_filemanager('estado_malwarescan', 'IN_PROGRESS');
		$filemanager_model->scan("malwarescan");
		
		$this->write_log("Retrieving info...");

		// Get suspicious files
		$suspicious_files = $filemanager_model->loadStack("malwarescan_resume", "suspicious_files");

		// If malwarescan task has not been launched, we set a '0' value.
		if (is_null($suspicious_files))
		{
			$suspicious_files = 0;
		}

		// Malwarescan last check
		$last_check_malwarescan = $filemanager_model->loadStack("malwarescan_resume", "last_check_malwarescan");

		$this->data = array(
			'suspicious_files'        => $suspicious_files,
			'last_check_malwarescan' => $last_check_malwarescan
		);
		
		$this->write_log("CHECKMALWARE task finished");

	}

		// Función que obtiene información del estado del backup

	private function getBackupInfo()
	{

		// Instanciamos la consulta
		$db = JFactory::getDBO();
		
		$joomla_version = "3";
		$query = "SELECT COUNT(*) FROM #__extensions WHERE element='com_akeeba'";		
		if (version_compare(JVERSION, '4.0', 'gt'))
		{
			$joomla_version = "4";
			$query = "SELECT COUNT(*) FROM #__extensions WHERE element='com_akeebabackup'";
		}		
		
		try {
			// Consultamos si Akeeba Backup está instalado
			$db->setQuery($query);
			$db->execute();
			$akeeba_installed = $db->loadResult();			
		} catch (Exception $e)
        {    			
            $akeeba_installed = 0;
        }     
		

		if ($akeeba_installed == 1)
		{
			$this->backupinfo['product'] = 'Akeeba Backup';
			$this->AkeebaBackupInfo($joomla_version);
		}
		else
		{
			try {
				// Consultamos si Xcloner Backup and Restore está instalado
				$query = 'SELECT COUNT(*) FROM #__extensions WHERE element="com_xcloner-backupandrestore"';
				$db->setQuery($query);
				$db->execute();
				$xcloner_installed = $db->loadResult();
			} catch (Exception $e)
			{    			
				$xcloner_installed = 0;
			} 			

			if ($xcloner_installed == 1)
			{
				$this->backupinfo['product'] = 'Xcloner - Backup and Restore';
				$this->XclonerbackupInfo();
			}
			else
			{
				// Consultamos si Easy Joomla Backup está instalado
				$query = "SELECT COUNT(*) FROM #__extensions WHERE element='com_easyjoomlabackup'";
				$db->setQuery($query);
				$db->execute();
				$ejb_installed = $db->loadResult();

				if ($ejb_installed == 1)
				{
					$this->backupinfo['product'] = 'Easy Joomla Backup';
					$this->EjbInfo();
				}
			}
		}

	}

	// Función que obtiene información del estado del último backup creado por Akeeba Backup
	private function AkeebaBackupInfo($joomla_version)
	{
		if ($joomla_version == "3") {
			$akeeba_database = "#__ak_stats";
		} else {
			$akeeba_database = "#__akeebabackup_backups";
		}
		
		// Instanciamos la consulta
		$db = JFactory::getDBO();
		try{
			$query = $db->getQuery(true)
				->select('MAX(' . $db->qn('id') . ')')
				->from($db->qn('' . $akeeba_database . ''))
				->where($db->qn('origin') . ' != ' . $db->q('restorepoint'));
			$db->setQuery($query);
			$id = $db->loadResult();
		} catch (Exception $e)
		{
			$this->write_log("Error trying to get Akeeba database id: " . $e->getMessage(),"ERROR");
		}
			

		// Hay al menos un backup creado
		if (!empty($id))
		{
			try{
				$query = $db->getQuery(true)
					->select(array('*'))
					->from($db->quoteName('' . $akeeba_database .''))
					->where('id = ' . $id);
				$db->setQuery($query);
				$backup_statistics = $db->loadAssocList();
			} catch (Exception $e)
			{
				$this->write_log("Error trying to get Akeeba backup statistics: " . $e->getMessage(),"ERROR");
			}

			// Almacenamos el resultado
			$this->backupinfo['latest'] = $backup_statistics[0]['backupend'];
			$this->backupinfo['latest_status'] = $backup_statistics[0]['status'];
			$this->backupinfo['latest_type'] = $backup_statistics[0]['type'];
		}
		
		
	}

	// Función que obtiene información del estado del último backup creado por Xcloner - Backup and Restore
	private function XclonerbackupInfo()
	{

		// Incluimos el fichero de configuración de la extensión
		include JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "components" . DIRECTORY_SEPARATOR . "com_xcloner-backupandrestore" . DIRECTORY_SEPARATOR . "cloner.config.php";

		// Extraemos el directorio donde se encuentran almacenados los backups...
		$backup_dir = $_CONFIG['clonerPath'];

				// ... y buscamos dentro los ficheros existentes, ordenándolos por fecha
		$files_name = JFolder::files($backup_dir, '.', true, true);
		$files_name = array_combine($files_name, array_map("filemtime", $files_name));
		arsort($files_name);

		// El primer elemento del array será el que se ha creado el último. Formateamos la fecha para guardarlo en la BBDD.
		$latest_backup = date("Y-m-d H:i:s", filemtime(key($files_name)));

		// Almacenamos el resultado
		$this->backupinfo['latest'] = $latest_backup;
		$this->backupinfo['latest_status'] = 'complete';

	}

		// Función que obtiene información del estado del último backup creado por Easy Joomla Backup

	private function EjbInfo()
	{

		// Instanciamos la consulta
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('MAX(' . $db->qn('id') . ')')
			->from($db->qn('#__easyjoomlabackup'));
		$db->setQuery($query);
		$id = $db->loadResult();

		// Hay al menos un backup creado
		if (!empty($id))
		{
			$query = $db->getQuery(true)
				->select(array('*'))
				->from($db->quoteName('#__easyjoomlabackup'))
				->where('id = ' . $id);
			$db->setQuery($query);
			$backup_statistics = $db->loadAssocList();

									// Almacenamos el resultado
			$this->backupinfo['latest'] = $backup_statistics[0]['date'];
			$this->backupinfo['latest_status'] = 'complete';
			$this->backupinfo['latest_type'] = $backup_statistics[0]['type'];
		}

	}

		// Función que indica si el plugin 'Update Database' está actualizado

	private function checkforUpdate()
	{

		// Inicializmaos las variables
		$needs_update = 0;

		$db = JFactory::getDBO();

		// Extraemos el id de la extensión..
		$query = 'SELECT extension_id FROM #__extensions WHERE name="System - Securitycheck Pro Update Database"';
		$db->setQuery($query);
		$db->execute();
		(int) $extension_id = $db->loadResult();

				// ... y hacemos una consulta a la tabla 'updates' para ver si el 'extension_id' figura como actualizable
		if (!empty($extension_id))
		{
			$query = "SELECT COUNT(*) FROM #__updates WHERE extension_id={$extension_id}";
			$db->setQuery($query);
			$db->execute();
			$result = $db->loadResult();

			if ($result == '1')
			{
				$needs_update = 1;
			}
		}

		// Devolvemos el resultado
		return $needs_update;

	}

	// Función que actualiza el plugin 'Update Database'
	private function UpdateComponent()
	{
		
		$this->write_log("Launching UPDATECOMPONENT task");
		
		$this->write_log("Getting Securitycheck Pro Update Database update info");
		
		// Inicializamos las variables
		$needs_update = 1;
		jimport('joomla.updater.update');

		$db = JFactory::getDBO();

		// Extraemos el id de la extensión..
		$query = 'SELECT extension_id FROM #__extensions WHERE name="System - Securitycheck Pro Update Database"';
		$db->setQuery($query);
		$db->execute();
		(int) $extension_id = $db->loadResult();

		$query = "SELECT detailsurl FROM #__updates WHERE extension_id={$extension_id}";
		$db->setQuery($query);
		$db->execute();
		$detailsurl = $db->loadResult();

		// Instanciamos el objeto JUpdate y cargamos los detalles de la actualización
		$update = new JUpdate;
		$update->loadFromXML($detailsurl);
		
		$this->write_log("Passing data to the 'install_update method...");
		
		// Le pasamos a la función de actualización el objeto con los detalles de la actualización
		$this->install_update($update);

		// Si la actualización ha tenido éxito, actualizamos la variable 'needs_update', que indica si el plugin necesita actualizarse.
		if ($this->array_result)
		{
			$needs_update = 0;
		}

		// Devolvemos el resultado
		$this->data = array(
			'update_plugin_needs_update' => $needs_update
		);
	}

	// Función para actualizar los componentes. Extraída del core de Joomla (administrator/components/com_installer/models/update.php | administrator\components\com_installer\src\Model\UpdateModel.php)
	private function install_update($update,$dlid=false)
	{
		$this->write_log("Installing update...");
		
								
		/* Cargamos el lenguaje del componente 'com_installer' */
		$lang = JFactory::getLanguage();
		$lang->load('com_installer',JPATH_ADMINISTRATOR);
				
					
		// Inicializamos la variable $update_result, que será un array con el resultado y el mensaje devuelto en el proceso
		$update_result = array();
		$extension_name = '';
		$app = JFactory::getApplication();
				
		if (isset($update->get('downloadurl')->_data)) {			
			$url = trim($update->downloadurl->_data);
			$extension_name = $update->get('name')->_data;
					
			if (!empty($dlid))
			{
				if ( is_array($dlid) ) {
					$this->write_log("Dlid is an array. Extracting values...");
					foreach($dlid as $key => $value) {
						$url .= '&amp;' . $key . '=' . $value;
						$this->write_log("Url: " . $url);
					}
				} else {
					$url .= '&amp;dlid=' . $dlid;
					$this->write_log("Url: " . $url);
				}
								
			}
			
				
		} else {
			$this->write_log(JText::_('COM_INSTALLER_INVALID_EXTENSION_UPDATE'));
			$update_result[0][1] = $extension_name . ' ' .  JText::_('COM_INSTALLER_INVALID_EXTENSION_UPDATE');
			$update_result[0][0] = 2;
			return $update_result;
		}
		
		try{
			$p_file = JInstallerHelper::downloadPackage($url);
		} catch (Exception $e)
		{
			$this->write_log("Error downloading package: " . $e->getMessage(),"ERROR");
		}
		
		// Was the package downloaded?
		if (!$p_file)
		{
			$this->write_log(JText::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url),"ERROR");
			$update_result[0][1] = $extension_name . ' ' . JText::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url);
			$update_result[0][0] = 2;
						
			return $update_result;
		} 
						
		$config        = JFactory::getConfig();
		$tmp_dest    = $config->get('tmp_path');
		
		// Unpack the downloaded package file
		$package    = JInstallerHelper::unpack($tmp_dest . '/' . $p_file);
		
		// Get an installer instance
		$installer    = JInstaller::getInstance();
		$update->set('type', $package['type']);
		
		// TODO: Checksum validation
								
		try {
			$install_result = $installer->update($package['dir']);
			
		} catch (Exception $e)
		{
			$this->write_log("Error installing package: " . $e->getMessage(),"ERROR");
		}
						
		// Install the package
		if (!$install_result)
		{
			// There was an error updating the package
			if (is_null($package['type']))
			{
				$package['type'] = "COMPONENT";
			}
			
			$msg = $extension_name . ' ' . JText::sprintf('COM_INSTALLER_MSG_UPDATE_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$this->write_log($msg,"ERROR");
			$update_result = $msg;
			$update_result = 2;
			
			return $update_result;
		}
		else
		{
			// Package updated successfully
			if (is_null($package['type']))
			{
				$package['type'] = "COMPONENT";
			}

			$msg = $extension_name . ' ' . JText::sprintf('COM_INSTALLER_MSG_UPDATE_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$this->write_log($msg);
			$update_result[0][1] = $msg;
			$update_result[0][0] = 1;			
		}
		
		// Quick change
		$this->type = $package['type'];
		
		if (array_key_exists('packagefile', $package))
		{
			// Cleanup the install files
			if (!is_file($package['packagefile']))
			{
				$config = JFactory::getConfig();
				$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
			}

			JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
		}
		
		return $update_result;
	}

	// Función que obtiene información del sistema (extraída del core)
	private function getInfo()
	{
		if (is_null($this->info))
		{
			$this->info = array();
			$version = new JVersion;
			$db = JFactory::getDbo();

			if (isset($_SERVER['SERVER_SOFTWARE']))
			{
				$sf = $_SERVER['SERVER_SOFTWARE'];
			}
			else
			{
				$sf = getenv('SERVER_SOFTWARE');
			}

			$this->info['php']            = php_uname();
			$this->info['dbversion']    = $db->getVersion();
			$this->info['dbcollation']    = $db->getCollation();
			$this->info['phpversion']    = phpversion();
			$this->info['server']        = $sf;
			$this->info['sapi_name']    = php_sapi_name();
			$this->info['version']        = $version->getLongVersion();

			// $this->info['platform']        = $platform->getLongVersion();
			$this->info['platform']        = "Not defined";
			$this->info['useragent']    = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
		}
	}

	// Función que devuelve información sobre las extensiones no actualizadas
	private function getNotUpdatedExtensions()
	{

		// Habilitamos los sitios deshabilitados
		//$enable = $this->enableSites();

		// Purgamos la caché y lanzamos la tarea
		$find = $this->findUpdates();

		$db = JFactory::getDBO();

		// Grab updates ignoring new installs (extraido de \administrator\components\com_installer\models\update.php)
		$query = $db->getQuery(true)
			->select('u.update_id,u.extension_id,u.name,u.type,u.version')
			->select($db->quoteName('e.manifest_cache'))
			->from($db->quoteName('#__updates', 'u'))
			->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('u.extension_id'))
			->where($db->quoteName('u.extension_id') . ' != ' . $db->quote(0));
		$db->setQuery($query);
		$result = $db->loadObjectList();

		// Creamos un nuevo array que contendrá arrays con a información requerida
		$extensions = array();

		foreach ($result as $i => $item)
		{
			$value = array();
			$manifest        = json_decode($item->manifest_cache);
			$current_version = isset($manifest->version) ? $manifest->version : JText::_('JLIB_UNKNOWN');
			$value[0] = $item->update_id;
			$value[1] = $item->extension_id;
			$value[2] = $item->name;
			$value[3] = $item->type;
			$value[4] = $item->version;
			$value[5] = $current_version;
			array_push($extensions, $value);
		}

		// Devolvemos el resultado en formato JSON
		return json_encode($extensions);

	}

		/**
		 * Finds updates for an extension.
		 *
		 * @param   int $eid           Extension identifier to look for
		 * @param   int $cache_timeout Cache timout
		 *
		 * @return boolean Result
		 *
		 * @since 1.6
		 *
		 * Original en /administrator/components/com_installer/models/update.php
		 */
	public function findUpdates($eid = 0, $cache_timeout = 0)
	{
		// Purge the updates list
		$this->purge();

		$updater = JUpdater::getInstance();
		$updater->findUpdates($eid, $cache_timeout);

		return true;
	}

		/**
		 * Removes all of the updates from the table.
		 *
		 * @return boolean result of operation
		 *
		 * @since 1.6
		 *
		 * Original en /administrator/components/com_installer/models/update.php
		 */
	public function purge()
	{
		$db = JFactory::getDbo();

		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		$db->setQuery('TRUNCATE TABLE #__updates');

		if ($db->execute())
		{
			// Reset the last update check timestamp
			$query = $db->getQuery(true)
				->update($db->quoteName('#__update_sites'))
				->set($db->quoteName('last_check_timestamp') . ' = ' . $db->quote(0));
			$db->setQuery($query);
			$db->execute();
		}
	}

		/**
		 * Enables any disabled rows in #__update_sites table
		 *
		 * @return boolean result of operation
		 *
		 * @since 1.6
		 *
		 * Original en /administrator/components/com_installer/models/update.php
		 */
	public function enableSites()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update('#__update_sites')
			->set('enabled = 1')
			->where('enabled = 0');
		$db->setQuery($query);
		$db->execute();
	}

	// Función que busca si una extensión pasada como argumento utiliza el mecanismo de actualización de Akeeba LiveUpdate
	private function LookForPro($extension_id,$extension_name,$update) {
				
		// Inicializamos las variables
		$dlid = '';
		
		// Según el campo buscamos el campo 'dlid'
		switch($extension_name)
		{
			case "pkg_akeeba":
				$params = JComponentHelper::getParams('com_akeeba');
				if (!empty($params)) {
					$dlid = $params->get('update_dlid','');		
				}
				break;
			case "pkg_admintools":
				$params = JComponentHelper::getParams('com_admintools');
				if (!empty($params)) {
					$dlid = $params->get('downloadid','');
				}
				break;
			case "com_rstbox":
				$plugin = JPluginHelper::getPlugin('system', 'nrframework');
				if (!empty($plugin)) {
					$params = new JRegistry($plugin->params);
					$dlid = $params->get('key','');
				}
				break;
			case "com_jch_optimize":
				$plugin = JPluginHelper::getPlugin('system', 'jch_optimize');
							
				if (!empty($plugin)) {					
					$params = new JRegistry($plugin->params);
					$dlid = $params->get('pro_downloadid','');
				}
				break;
			// Version 7 of Jch optimize
			case "pkg_jchoptimize":
				$params = JComponentHelper::getParams('com_jchoptimize');
							
				if (!empty($params)) {
					$dlid = $params->get('pro_downloadid','');
				}
				break;			
			case "com_sppagebuilder":
				$params = JComponentHelper::getParams('com_sppagebuilder');
							
				if (!empty($params)) {
					$dlid = array();
					$dlid['joomshaper_email'] = $params->get('joomshaper_email','');
					$dlid['joomshaper_license_key'] = $params->get('joomshaper_license_key','');
				}
				break;
		}		
				
		if (!empty($dlid))
		{
			$msg = "Found Pro version of " . $extension_name . " with a valid dlid.";
			$this->write_log($msg);
			$update_result = $this->install_update($update,$dlid);
			// Guardamos el id de la extensión junto con el resultado
			array_push($this->array_result, array($extension_id,$extension_name,$update_result));
		} else {
			$msg = "Found Pro version of " . $extension_name . " but not a valid dlid. Is the extension/plugin enabled and have a valid download id?";
			$this->write_log($msg);
			$update_result = array();
			$update_result[0][1] = $msg;
			$update_result[0][0] = 2;
			// Guardamos el id de la extensión junto con el resultado
			array_push($this->array_result, array($extension_id,$extension_name,$update_result));			
		}
		
	
	}	

	// Función que actualiza un array de extensiones (en formato json) pasado como argumento
	private function UpdateExtension($extension_id_array)
	{
		$this->write_log("Launching UPDATEEXTENSIONS task");
				
		// Inicializamos las variables
		
		$db = JFactory::getDBO();
		jimport('joomla.updater.update');

		// Si las tablas están bloqueadas abortamos la instalación
		$locked_tables = $this->check_locked_tables();

		if ($locked_tables)
		{
			$msg = JText::_('COM_SECURITYCHECKPRO_LOCKED_MESSAGE');

			array_push($this->array_result, array($msg,$msg));

			// Devolvemos el resultado
			$this->data = array(
				'update_result'        => $this->array_result
			);
		}
		else
		{
			// Para cada extensión, realizamos su actualización
			foreach ($extension_id_array as $extension_id)
			{
				// Extraemos los datos la extensión, que contendrán la información de actualización
				try{		
					$query = "SELECT name,detailsurl,element,extra_query FROM #__updates WHERE extension_id={$extension_id}";
					$db->setQuery($query);
					$db->execute();
					$extension_data = $db->loadAssoc();
				} catch (Exception $e)
				{
					
				}
														
				if ( is_array($extension_data) ) {					
					$extension_name = $extension_data['name'];
					$detailsurl = $extension_data['detailsurl'];
					$extension_element = $extension_data['element'];
					$extra_query = $extension_data['extra_query'];
					
									
					if (strtolower($extension_element) == "joomla")
					{
						
						// Core de Joomla. Lo tratamos de forma diferente.
						$result_core = $this->UpdateCore();
						array_push($this->array_result, array($extension_id,'Core',$result_core));
					}else
					{	
						// Instanciamos el objeto JUpdate y cargamos los detalles de la actualización
						$update = new JUpdate;
						$update->loadFromXML($detailsurl);					

						// Le pasamos a la función de actualización el objeto con los detalles de la actualización
						if (!empty($extra_query)) {
							// Quitamos el texto "dlid="
							$extra_query = str_replace("dlid=", "",$extra_query);
							$update_result = $this->install_update($update,$extra_query);
						} else {
							$update_result = $this->install_update($update);
						}
						
						// Update failed
						if ( (!$update_result) || ($update_result[0][0] == 2) )
						{
							$pro_versions_to_look_for = array('pkg_akeeba','pkg_admintools','com_rstbox','com_jch_optimize','pkg_jchoptimize','com_sppagebuilder');
							
							if (in_array($extension_element, $pro_versions_to_look_for)) {
								// Se ha producido un error y la extensión puede ser de pago. Intentamos actualizarla buscando su dlid
								$this->LookForPro($extension_id,$extension_element,$update);
							}												
						}
						else
						{
							// Guardamos el id de la extensión junto con el resultado
							array_push($this->array_result, array($extension_id,$extension_name,$update_result));
						}
					}
				} else {
					// Guardamos el id de la extensión junto con el resultado
					array_push($this->array_result, array($extension_id,"","Error retrieving extension data"));
				}				
			}
			
			// Devolvemos el resultado
			$this->data = array(
				'update_result'        => $this->array_result
			);
		}

	}

		// Función que realiza una copia de seguridad usando Akeeba y su función de copias de seguridad vía frontend. La clave usada se pasa como argumento

	private function Backup($data)
	{
		$this->write_log("Launching BACKUP task");
		
		// URI del sitio
		$uri = JURI::root();
		
		$this->write_log("Decrypting Akeeba public key...");
		
		// Desencriptamos los datos recibidos, que vendrán como un array (véase data[0]) y en formato json
		$response = $this->decrypt($data, $this->password);
		$response = json_decode($response, true);

		// Extraemos la clave pública de Akeeba, que vendrá en el elemento 'akeeba_key' del array
		$akeeba_key = $response['frontend_key'];

		// Extraemos el perfil, que por defecto será 1
		$akeeba_profile = $response['akeeba_profile'];
		
		// Componente (com_akeeba para J3 y com_akeebackup para J4)
		$akeeba_component = "com_akeeba";
		
		if (version_compare(JVERSION, '4.0', 'gt'))
		{
			$akeeba_component = "com_akeebabackup";
		}
		
		$this->write_log("Launching curl: " . $uri . "?option=" . $akeeba_component . "&view=backup&key=removed_for_security&profile=" . $akeeba_profile);
		
		// Inicializamos la tarea
		$ch = curl_init($uri . "?option=" . $akeeba_component . "&view=backup&key=" . $akeeba_key . "&profile=" . $akeeba_profile);
		
		// Configuración extraída de https://www.akeebabackup.com/documentation/akeeba-backup-documentation/automating-your-backup.html
		curl_setopt($ch, CURLOPT_HEADER, false);  // Este valor es false para que no incluya en la respuesta la cabecera HTTP
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10000); // Fix by Nicholas
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);
		curl_close($ch);
		
		$this->write_log("Akeeba response: " . $response);

		// Devolvemos el resultado
		$this->data = array(
		'backup'        => $response
		);
	}

	// Función que instala una extensión desde una url. La url se pasa como argumento
	private function Upload_install($data)
	{
		$this->write_log("Launching UPLOADINSTALL task");
		
		// Inicialiamos las variables
		$result = true;
		$enqueued_messages = "";

		// Cargamos el lenguaje del componente 'com_installer'

		$lang = JFactory::getLanguage();
		$lang->load('com_installer', JPATH_ADMINISTRATOR);
		
		$this->write_log("Decrypting data...");

		// Desencriptamos los datos recibidos, que vendrán como un array (véase data[0]) y en formato json
		$response = $this->decrypt($data[0], $this->password);
		$response = json_decode($response, true);

		// Url del paquete a instalar
		$url = $response['path_to_file'];
		
		$this->write_log("Url: " . $url);
		
		$package = null;
		
		// Si las tablas están bloqueadas abortamos la instalación
		$locked_tables = $this->check_locked_tables();

		if ($locked_tables)
		{
			$this->write_log("Tables are blocked. Can't install the extension.");
			$msg = JText::_('COM_SECURITYCHECKPRO_LOCKED_MESSAGE');
			$result = false;
		}
		else
		{
			// Extraemos el paquete desde la url pasada
			$package = $this->getPackageFromUrl($url);

			// Was the package unpacked?
			if (!$package || !$package['type'])
			{				
				if (in_array($installType, array('upload', 'url')))
				{
					//JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
				}
				
				$msg = JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE');
				$this->write_log($msg);

				return false;
			}

			// Get an installer instance
			$installer = JInstaller::getInstance();

			// Install the package
			if (!$installer->install($package['dir']))
			{
				// There was an error installing the package
				$msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
				$this->write_log($msg);
				$result = false;				
			}
			else
			{
				// Package installed sucessfully
				$msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
				$this->write_log($msg);
			}

			// Cleanup the install files
			/*if (!is_file($package['packagefile']))
			{
				$config = JFactory::getConfig();
				$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
			}

			$cleanup_resume = JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

			// Si el borrado de los ficheros de instalación falla, lo hacemos 'artesanalmente'
			if (!$cleanup_resume)
			{
				$config = JFactory::getConfig();
				$root = $config->get('tmp_path');
				$files_name = JFolder::files($root, '.', true, true);

				foreach ($files_name as $file)
				{
					try{		
					JFile::delete($root . DIRECTORY_SEPARATOR . $file);
					} catch (Exception $e)
					{
					}					
				}
			}*/

			// Recogemos los mensajes encolados para mostrar más información
			$enqueued_messages = JFactory::getApplication()->getMessageQueue();
		}
		
		
		// Devolvemos el resultado
		$this->data = array(
			'upload_install'        => $result,
			'message'    => $msg,
			'enqueued_messages'    => $enqueued_messages
		);
	}

		/**
		 * Install an extension from a URL
		 *
		 * @return Package details or false on failure
		 *
		 * @since 1.5
		 */
	protected function getPackageFromUrl($url)
	{

		// Did you give us a URL?
		if (!$url)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), 'warning');

			return false;
		}

		// Handle updater XML file case:
		if (preg_match('/\.xml\s*$/', $url))
		{
			$update = new JUpdate;
			$update->loadFromXML($url);
			$package_url = trim($update->get('downloadurl', false)->_data);

			if ($package_url)
			{
				$url = $package_url;
			}

			unset($update);
		}

		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), 'warning');

			return false;
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file, true);

		return $package;
	}

		/**
		 * Downloads the update package to the site.
		 *
		 * @return boolean|string False on failure, basename of the file in any other case.
		 *
		 * @since 2.5.4
		 */
	public function download_core($packageURL)
	{
		$basename = basename($packageURL);
						

		// Find the path to the temp directory and the local package.
		$config = JFactory::getConfig();
		$tempdir = $config->get('tmp_path');
		$target = $tempdir . '/' . $basename;

		// Do we have a cached file?
		$exists = file_exists($target);

		if (!$exists)
		{
			// Not there, let's fetch it.
			return $this->downloadPackage($packageURL, $target);
		}
		else
		{
			// Is it a 0-byte file? If so, re-download please.
			$filesize = @filesize($target);

			if (empty($filesize))
			{
				return $this->downloadPackage($packageURL, $target);
			}

			// Yes, it's there, skip downloading.
			return $basename;
		}
	}

	/**
	 * Downloads a package file to a specific directory
	 *
	 * @param   string $url    The URL to download from
	 * @param   string $target The directory to store the file
	 *
	 * @return boolean True on success
	 *
	 * @since 2.5.4
	 */
	protected function downloadPackage($url, $target)
	{
			
		// Make sure the target does not exist.
		if (file_exists($target)) {
			JFile::delete($target);
		}
		
		// Download the package
		try
		{
			if (version_compare(JVERSION, '3.20', 'lt'))
			{
				$result = JHttpFactory::getHttp(null, array('curl', 'stream'))->get($url);
			}
			else
			{
				$result = JHttpFactory::getHttp([], ['curl', 'stream'])->get($url);
			}			
		}
		catch (\RuntimeException $e)
		{			
			return false;
		}

		if (!$result || ($result->code != 200 && $result->code != 310))
		{
			return false;
		}

		// Write the file to disk
		JFile::write($target, $result->body);

		return basename($target);
	}

		/**
		 * Create restoration file.
		 *
		 * @param   string $basename Optional base path to the file.
		 *
		 * @return boolean True if successful; false otherwise.
		 *
		 * @since 2.5.4
		 */
	public function createRestorationFile($basename = null)
	{
		// Get a password
		$password = JUserHelper::genRandomPassword(32);
		$app = JFactory::getApplication();
		$app->setUserState('com_joomlaupdate.password', $password);

		// Do we have to use FTP?
		$method = $app->input->get('method', 'direct');

		// Get the absolute path to site's root.
		$siteroot = JPATH_SITE;

		// If the package name is not specified, get it from the update info.
		if (empty($basename))
		{
			$updateInfo = $this->getUpdateInformation();
			$packageURL = $updateInfo['object']->downloadurl->_data;
			$basename = basename($packageURL);
		}

		// Get the package name.
		$config = JFactory::getConfig();
		$tempdir = $config->get('tmp_path');
		$file = $tempdir . '/' . $basename;

		$filesize = @filesize($file);
		$app->setUserState('com_joomlaupdate.password', $password);
		$app->setUserState('com_joomlaupdate.filesize', $filesize);

		$data = "<?php\ndefined('_AKEEBA_RESTORATION') or die('Restricted access');\n";
		$data .= '$restoration_setup = array(' . "\n";
		$data .= <<<ENDDATA
	'kickstart.security.password' => '$password',
	'kickstart.tuning.max_exec_time' => '5',
	'kickstart.tuning.run_time_bias' => '75',
	'kickstart.tuning.min_exec_time' => '0',
	'kickstart.procengine' => '$method',
	'kickstart.setup.sourcefile' => '$file',
	'kickstart.setup.destdir' => '$siteroot',
	'kickstart.setup.restoreperms' => '0',
	'kickstart.setup.filetype' => 'zip',
	'kickstart.setup.dryrun' => '0'
ENDDATA;

		if ($method == 'ftp')
		{
			/*
            * Fetch the FTP parameters from the request. Note: The password should be
            * allowed as raw mode, otherwise something like !@<sdf34>43H% would be
            * sanitised to !@43H% which is just plain wrong.
            */
			$ftp_host = $app->input->get('ftp_host', '');
			$ftp_port = $app->input->get('ftp_port', '21');
			$ftp_user = $app->input->get('ftp_user', '');
			$ftp_pass = $app->input->get('ftp_pass', '', 'default', 'none', 2);
			$ftp_root = $app->input->get('ftp_root', '');

			// Is the tempdir really writable?
			$writable = @is_writeable($tempdir);

			if ($writable)
			{
				// Let's be REALLY sure.
				$fp = @fopen($tempdir . '/test.txt', 'w');

				if ($fp === false)
				{
					$writable = false;
				}
				else
				{
					fclose($fp);
					unlink($tempdir . '/test.txt');
				}
			}

			// If the tempdir is not writable, create a new writable subdirectory.
			if (!$writable)
			{
				$FTPOptions = JClientHelper::getCredentials('ftp');
				$ftp = JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
				$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $tempdir . '/admintools'), '/');

				if (!@mkdir($tempdir . '/admintools'))
				{
					$ftp->mkdir($dest);
				}

				if (!@chmod($tempdir . '/admintools', 511))
				{
					$ftp->chmod($dest, 511);
				}

				$tempdir .= '/admintools';
			}

			// Just in case the temp-directory was off-root, try using the default tmp directory.
			$writable = @is_writeable($tempdir);

			if (!$writable)
			{
				$tempdir = JPATH_ROOT . '/tmp';

				// Does the JPATH_ROOT/tmp directory exist?
				if (!is_dir($tempdir))
				{
					JFolder::create($tempdir, 511);
					$tempdir2 = $tempdir . '/.htaccess';
					$text_to_write = "order deny,allow\ndeny from all\nallow from none\n";
					JFile::write($tempdir2, $text_to_write);
				}

				// If it exists and it is unwritable, try creating a writable admintools subdirectory.
				if (!is_writable($tempdir))
				{
					$FTPOptions = JClientHelper::getCredentials('ftp');
					$ftp = JClientFtp::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
					$dest = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $tempdir . '/admintools'), '/');

					if (!@mkdir($tempdir . '/admintools'))
					{
						$ftp->mkdir($dest);
					}

					if (!@chmod($tempdir . '/admintools', 511))
					{
						$ftp->chmod($dest, 511);
					}

					$tempdir .= '/admintools';
				}
			}

			// If we still have no writable directory, we'll try /tmp and the system's temp-directory.
			$writable = @is_writeable($tempdir);

			if (!$writable)
			{
				if (@is_dir('/tmp') && @is_writable('/tmp'))
				{
					$tempdir = '/tmp';
				}
				else
				{
					// Try to find the system temp path.
					$tmpfile = @tempnam("dummy", "");
					$systemp = @dirname($tmpfile);
					@unlink($tmpfile);

					if (!empty($systemp))
					{
						if (@is_dir($systemp) && @is_writable($systemp))
						{
							   $tempdir = $systemp;
						}
					}
				}
			}

			$data .= <<<ENDDATA
	,
	'kickstart.ftp.ssl' => '0',
	'kickstart.ftp.passive' => '1',
	'kickstart.ftp.host' => '$ftp_host',
	'kickstart.ftp.port' => '$ftp_port',
	'kickstart.ftp.user' => '$ftp_user',
	'kickstart.ftp.pass' => '$ftp_pass',
	'kickstart.ftp.dir' => '$ftp_root',
	'kickstart.ftp.tempdir' => '$tempdir'
ENDDATA;
		}

		$data .= ');';

		// Remove the old file, if it's there...
		$configpath = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_joomlaupdate' . DIRECTORY_SEPARATOR . 'restoration.php';

		if (file_exists($configpath))
		{
			try{		
				JFile::delete($configpath);
			} catch (Exception $e)
			{
	}
			
		}

				// Previous versions of SCP created the restoration file into an administrator folder into administrator folder. Let's check if exists and delete it.
		if (JFolder::exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'administrator'))
		{
			JFolder::delete(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'administrator');
		}

		// Write new file. First try with JFile.
		$result = JFile::write($configpath, $data);

		// In case JFile used FTP but direct access could help.
		if (!$result)
		{
			if (function_exists('file_put_contents'))
			{
				$result = @file_put_contents($configpath, $data);

				if ($result !== false)
				{
					$result = true;
				}
			}
			else
			{
				$fp = @fopen($configpath, 'wt');

				if ($fp !== false)
				{
					$result = @fwrite($fp, $data);

					if ($result !== false)
					{
						$result = true;
					}

					@fclose($fp);
				}
			}
		}

		return $result;
	}

		/**
		 * Runs the schema update SQL files, the PHP update script and updates the
		 * manifest cache and #__extensions entry. Essentially, it is identical to
		 * JInstallerFile::install() without the file copy.
		 *
		 * @return boolean True on success.
		 *
		 * @since 2.5.4
		 */
	public function finaliseUpgrade()
	{
		$installer = JInstaller::getInstance();

		$manifest = $installer->isManifest(JPATH_MANIFESTS . '/files/joomla.xml');

		if ($manifest === false)
		{
			$installer->abort(JText::_('JLIB_INSTALLER_ABORT_DETECTMANIFEST'));

			return false;
		}

		$installer->manifest = $manifest;

		$installer->setUpgrade(true);
		$installer->setOverwrite(true);

		$installer->extension = JTable::getInstance('extension');
		$installer->extension->load(700);

		$installer->setAdapter($installer->extension->type);

		$installer->setPath('manifest', JPATH_MANIFESTS . '/files/joomla.xml');
		$installer->setPath('source', JPATH_MANIFESTS . '/files');
		$installer->setPath('extension_root', JPATH_ROOT);

		$manifestPath = JPath::clean($installer->getPath('manifest'));

		// Run the script file.
		JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

		$manifestClass = new JoomlaInstallerScript;

		ob_start();
		ob_implicit_flush(false);

		if ($manifestClass && method_exists($manifestClass, 'preflight'))
		{
			if ($manifestClass->preflight('update', $installer) === false)
			{
				$installer->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Create msg object; first use here.
		$msg = ob_get_contents();
		ob_end_clean();

		// Get a database connector object.
		$db = $this->getDbo();

		/*
        * Check to see if a file extension by the same name is already installed.
        * If it is, then update the table because if the files aren't there
        * we can assume that it was (badly) uninstalled.
        * If it isn't, add an entry to extensions.
        */
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('file'))
			->where($db->quoteName('element') . ' = ' . $db->quote('joomla'));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			// Install failed, roll back changes.
			$installer->abort(
				JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', JText::_('JLIB_INSTALLER_UPDATE'), $e->getMessage())
			);

			return false;
		}

		$id = $db->loadResult();
		$row = JTable::getInstance('extension');

		if ($id)
		{
			// Load the entry and update the manifest_cache.
			$row->load($id);

			// Update name.
			$row->set('name', 'files_joomla');

			// Update manifest.
			$row->manifest_cache = $installer->generateManifestCache();

			if (!$row->store())
			{
				// Install failed, roll back changes.
				$installer->abort(
					JText::sprintf('JLIB_INSTALLER_ABORT_FILE_ROLLBACK', JText::_('JLIB_INSTALLER_UPDATE'), $row->getError())
				);

				return false;
			}
		}
		else
		{
			// Add an entry to the extension table with a whole heap of defaults.
			$row->set('name', 'files_joomla');
			$row->set('type', 'file');
			$row->set('element', 'joomla');

			// There is no folder for files so leave it blank.
			$row->set('folder', '');
			$row->set('enabled', 1);
			$row->set('protected', 0);
			$row->set('access', 0);
			$row->set('client_id', 0);
			$row->set('params', '');
			$row->set('system_data', '');
			$row->set('manifest_cache', $installer->generateManifestCache());

			if (!$row->store())
			{
				// Install failed, roll back changes.
				$installer->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_INSTALL_ROLLBACK', $row->getError()));

				return false;
			}

			// Set the insert id.
			$row->set('extension_id', $db->insertid());

			// Since we have created a module item, we add it to the installation step stack
			// so that if we have to rollback the changes we can undo it.
			$installer->pushStep(array('type' => 'extension', 'extension_id' => $row->extension_id));
		}

		$result = $installer->parseSchemaUpdates($manifest->update->schemas, $row->extension_id);

		if ($result === false)
		{
			// Install failed, rollback changes.
			$installer->abort(JText::sprintf('JLIB_INSTALLER_ABORT_FILE_UPDATE_SQL_ERROR', $db->stderr(true)));

			return false;
		}

		// Start Joomla! 1.6.
		ob_start();
		ob_implicit_flush(false);

		if ($manifestClass && method_exists($manifestClass, 'update'))
		{
			if ($manifestClass->update($installer) === false)
			{
				// Install failed, rollback changes.
				$installer->abort(JText::_('JLIB_INSTALLER_ABORT_FILE_INSTALL_CUSTOM_INSTALL_FAILURE'));

				return false;
			}
		}

		// Append messages.
		$msg .= ob_get_contents();
		ob_end_clean();

		// Clobber any possible pending updates.
		$update = JTable::getInstance('update');
		$uid = $update->find(
			array('element' => 'joomla', 'type' => 'file', 'client_id' => '0', 'folder' => '')
		);

		if ($uid)
		{
			$update->delete($uid);
		}

		// And now we run the postflight.
		ob_start();
		ob_implicit_flush(false);

		if ($manifestClass && method_exists($manifestClass, 'postflight'))
		{
			$manifestClass->postflight('update', $installer);
		}

		// Append messages.
		$msg .= ob_get_contents();
		ob_end_clean();

		if ($msg != '')
		{
			$installer->set('extension_message', $msg);
		}

		// Refresh versionable assets cache.
		JFactory::getApplication()->flushAssets();

		return true;
	}
	
	

	// Función que devuelve información sobre ips a añadir y ataques detenidos para el plugin "Connect"
	public function Connect($url=null)
	{
		

		include_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
		$cpanel_model = new SecuritycheckprosModelCpanel;

		$attacks_today = $cpanel_model->LogsByDate('today');
		$attacks_yesterday = $cpanel_model->LogsByDate('yesterday');
		$attacks_last_7_days = $cpanel_model->LogsByDate('last_7_days');
		$attacks_last_month = $cpanel_model->LogsByDate('last_month');
		$attacks_this_month = $cpanel_model->LogsByDate('this_month');
		$attacks_last_year = $cpanel_model->LogsByDate('last_year');
		$attacks_this_year = $cpanel_model->LogsByDate('this_year');

		$attacks = array(
			'today'    => $attacks_today,
			'yesterday'        => $attacks_yesterday,
			'last_7_days'        => $attacks_last_7_days,
			'this_month'        => $attacks_this_month,
			'last_month'        => $attacks_last_month,
			'this_year'        => $attacks_this_year,
			'last_year'        => $attacks_last_year
			);

		// Ruta al fichero de información
		$file_path = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'scans' . DIRECTORY_SEPARATOR . 'cc_info.php';

		// Hay información que consumir
		if (file_exists($file_path))
		{
			$str = file_get_contents($file_path);

			// Eliminamos la parte del fichero que evita su lectura al acceder directamente
			$ips = str_replace("#<?php die('Forbidden.'); ?>", '', $str);

			// Una vez extraida la información eliminamos el fichero
			unlink($file_path);
		}
		else
		{
			$ips = null;
		}

		$this->data = array(
			'ips'        => $ips,
			'attacks'    => $attacks
			);
		
		if (!empty($url)) {
			$this->sendResponse($url);
		}		
		
	}
	

	// Función que añade una IP a la lista negra dinámica
	function actualizar_lista_dinamica($attack_ip)
	{

		// Creamos el nuevo objeto query
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Chequeamos si la IP tiene un formato válido
		$ip_valid = filter_var($attack_ip, FILTER_VALIDATE_IP);

		// Sanitizamos la entrada
		$attack_ip = $db->escape($attack_ip);

		// Validamos si el valor devuelto es una dirección IP válida
		if ((!empty($attack_ip)) && ($ip_valid))
		{
			try
			{
				$query = "INSERT INTO `#__securitycheckpro_dynamic_blacklist` (`ip`, `timeattempt`) VALUES ('{$attack_ip}', NOW()) ON DUPLICATE KEY UPDATE `timeattempt` = NOW(), `counter` = `counter` + 1;";

				$db->setQuery($query);
				$result = $db->execute();
			}
			catch (Exception $e)
			{
			}
		}
		else
		{
			return JText::_('COM_SECURITYCHECKPRO_INVALID_FORMAT');			
		}
	}

	// Función que añade ips a la lista negra pasados por el plugin "Connect"
	private function UpdateConnect($data)
	{
		// Desencriptamos los datos recibidos, que vendrán en formato json
		$response = $this->decrypt($data, $this->password);				
		$ips_passed = json_decode($response, true);	
				
		$message = "";
		
		include_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'firewallconfig.php';
		$firewall_config_model = new SecuritycheckprosModelFirewallConfig;
		
		try {
			if ( is_array($ips_passed) )
			{
				if ( array_key_exists('whitelist', $ips_passed) ) {
					if (count($ips_passed['whitelist']))
					{
						$message .= JText::_('COM_SECURITYCHECKPRO_WHITELIST') . " ";
					}

					foreach ($ips_passed['whitelist'] as $whitelist)
					{
						$returned_message = $firewall_config_model->manage_list('whitelist', 'add', $whitelist, true, true);

						if (!empty($returned_message))
						{
							$message .= $whitelist . ": " . $returned_message . " ";
						}
						else
						{
							$message .= $whitelist . ": OK ";
						}
					}
				}
				
				
				if ( array_key_exists('blacklist', $ips_passed) ) {
					if (count($ips_passed['blacklist']))
					{
						$message .= JText::_('COM_SECURITYCHECKPRO_BLACKLIST') . " ";
					}

					foreach ($ips_passed['blacklist'] as $blacklist)
					{
						$returned_message = $firewall_config_model->manage_list('blacklist', 'add', $blacklist, true, true);

						if (!empty($returned_message))
						{
							$message .= $blacklist . ": " . $returned_message . " ";
						}
						else
						{
							$message .= $blacklist . ": OK ";
						}
					}
				}
				
				if ( array_key_exists('dynamic_blacklist', $ips_passed) ) {
					if (count($ips_passed['dynamic_blacklist']))
					{
						$message .= JText::_('COM_SECURITYCHECKPRO_DYNAMIC_BLACKLIST') . " ";
					}

					foreach ($ips_passed['dynamic_blacklist'] as $dynamic_blacklist)
					{
						$returned_message = $this->actualizar_lista_dinamica($dynamic_blacklist);

						if (!empty($returned_message))
						{
							$message .= $dynamic_blacklist . ": " . $returned_message . " ";
						}
						else
						{
							$message .= $dynamic_blacklist . ": OK ";
						}
					}
				}
			}
			
		} catch (Exception $e) {					
			$message = $e->getMessage();
		} 
		
		// Devolvemos el resultado
		$this->data = array(
			'UpdateConnect'        => $message
			);
	}		

	// Función para desbloquear las tablas (Lock tables feature)
	private function unlocktables()
	{		
		$this->write_log("Launching UNLOCKTABLES task");
		
		include_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
		$cpanel_model = new SecuritycheckprosModelCpanel;

		$cpanel_model->unlock_tables();

		$this->data = array(
			'tables_blocked'        => 0
		);
		$this->write_log("UNLOCKTABLES task finished");

	}

	// Función para desbloquear las tablas (Lock tables feature)
	private function locktables()
	{
		$this->write_log("Launching LOCKTABLES task");
		
		include_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
		$cpanel_model = new SecuritycheckprosModelCpanel;

		$cpanel_model->lock_tables();

		$this->data = array(
			'tables_blocked'        => 1
		);
		
		$this->write_log("LOCKTABLES task finished");

	}
	
	/* Función para formatear un entero en unidades de almacenamiento */
	function formatBytes($size, $precision = 2)
	{
		$base = log($size, 1024);
		$suffixes = array('', 'K', 'M', 'G', 'T');   

		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
	}
	
	function percent_to_color($p){
		if($p < 30) return 'success';
		if($p < 45) return 'info';
		if($p < 60) return 'primary';
		if($p < 75) return 'warning';
		return 'danger';
	}
	
	/* Get memory usage - https://www.php.net/manual/es/function.memory-get-usage.php */
	private function server_statistics()
    {
        $memoryTotal = null;
        $memoryFree = null;
		$memory_array = array();
		$uptime = null;
		// Inicializamos la variable $result, que será un array con el resultado y el mensaje devuelto en el proceso
		$result = array();
		
		// Memory usage
        if (stristr(PHP_OS, "win")) {
            // Get total physical memory (this is in bytes)
            $cmd = "wmic ComputerSystem get TotalPhysicalMemory";
            @exec($cmd, $outputTotalPhysicalMemory);

            // Get free physical memory (this is in kibibytes!)
            $cmd = "wmic OS get FreePhysicalMemory";
            @exec($cmd, $outputFreePhysicalMemory);

            if ($outputTotalPhysicalMemory && $outputFreePhysicalMemory) {
                // Find total value
                foreach ($outputTotalPhysicalMemory as $line) {
                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
                        $memoryTotal = $line;
                        break;
                    }
                }

                // Find free value
                foreach ($outputFreePhysicalMemory as $line) {
                    if ($line && preg_match("/^[0-9]+\$/", $line)) {
                        $memoryFree = $line;
                        $memoryFree *= 1024;  // convert from kibibytes to bytes
                        break;
                    }
                }
            }
        }
        else
        {
            if (is_readable("/proc/meminfo"))
            {
                $stats = @file_get_contents("/proc/meminfo");

                if ($stats !== false) {
                    // Separate lines
                    $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
                    $stats = explode("\n", $stats);

                    // Separate values and find correct lines for total and free mem
                    foreach ($stats as $statLine) {
                        $statLineData = explode(":", trim($statLine));

                        //
                        // Extract size (TODO: It seems that (at least) the two values for total and free memory have the unit "kB" always. Is this correct?
                        //

                        // Total memory
                        if (count($statLineData) == 2 && trim($statLineData[0]) == "MemTotal") {
                            $memoryTotal = trim($statLineData[1]);
                            $memoryTotal = explode(" ", $memoryTotal);
                            $memoryTotal = $memoryTotal[0];
                            $memoryTotal *= 1024;  // convert from kibibytes to bytes
                        }

                        // Free memory
                        if (count($statLineData) == 2 && trim($statLineData[0]) == "MemFree") {
                            $memoryFree = trim($statLineData[1]);
                            $memoryFree = explode(" ", $memoryFree);
                            $memoryFree = $memoryFree[0];
                            $memoryFree *= 1024;  // convert from kibibytes to bytes
                        }
                    }
                }
            }
        }

        if (is_null($memoryTotal) || is_null($memoryFree)) {
            $memory_array = null;
        } else {
			$used = $this->formatBytes( $memoryTotal - $memoryFree);
			$used_raw = $memoryTotal - $memoryFree;
			$total = $this->formatBytes($memoryTotal);
			$memory_percentage = round(($used_raw/$memoryTotal)*100,2);
			$memory_color = $this->percent_to_color($memory_percentage);
			
			$memory_array = array(
				"memory_total" => $total,
				"memory_used" => $used,
				"memory_percentage" => $memory_percentage,
				"memory_color" => $memory_color,
			);			
        }
		
		if ( (empty($memory_array["memory_total"])) && (empty($memory_array["memory_used"])) )
		{
			$memory_array = null;
		}
		
		$result['memory_array'] = $memory_array;
		
		// Uptime
		if (function_exists('system')) {
			try
			{
				$uptime = @system('uptime');
				$uptime_array = explode(",",$uptime);
				if ( (empty($uptime_array[0])) && (empty($uptime_array[1])) )
				{
					$result['uptime'] = null;
				}else
				{
					$result['uptime'] = $uptime_array[0] . "," . $uptime_array[1];	
				}		
						
				$pos = strpos($uptime_array[2],":");
				$load_average = substr($uptime_array[2],$pos+1,strlen($uptime_array[2])-$pos);
				if ( (empty($load_average)) && (empty($uptime_array[3])) && (empty($uptime_array[4])) )
				{
					$result['server_load'] = null;
				}else
				{
					$result['server_load'] = $load_average . "," . $uptime_array[3] . "," . $uptime_array[4];
				}
				
			}catch (Exception $e)	
			{
				$result['uptime'] = null;
				$result['server_load'] = null;			
			}
		} else
		{
			$result['uptime'] = null;
			$result['server_load'] = null;
		}
		
		// Devolvemos el resultado
		$this->data = $result;
    }
	
	// Función para habilitar las estadísticas
	private function enable_analytics($data)
	{		
		$this->write_log("Launching ENABLE_ANALYTICS task");
		
		if (!file_exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'securitycheckproanalytics'))
		{
			$this->write_log("Analytics is not installed.");
			$this->data = 'Analytics is not installed';
			$this->status = self::STATUS_ERROR;
			$this->cipher = self::CIPHER_RAW;
		} else {
			// Desencriptamos los datos recibidos, que vendrán en formato json
			$response = $this->decrypt($data, $this->password);				
			$response = json_decode($response, true);
			
			// Extraemos el código de la web
			$website_code = $response['website_code'];

			include_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
			$cpanel_model = new SecuritycheckprosModelCpanel;

			$success = $cpanel_model->enable_analytics($website_code,$this->site);

			$this->data = array(
				'analytics_enabled'        => $success
			);
			$this->write_log("ENABLE_ANALYTICS task finished");
		}
	}
	
	// Función para deshabilitar las estadísticas
	private function disable_analytics($data)
	{		
		$this->write_log("Launching DISABLE_ANALYTICS task");
		
		// Desencriptamos los datos recibidos, que vendrán en formato json
		$response = $this->decrypt($data, $this->password);				
		$response = json_decode($response, true);
		
		// Extraemos el código de la web
		$website_code = $response['website_code'];

		include_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'cpanel.php';
		$cpanel_model = new SecuritycheckprosModelCpanel;

		$success = $cpanel_model->disable_analytics($website_code,$this->site);

		$this->data = array(
			'analytics_disabled'        => $success
		);
		$this->write_log("ENABLE_ANALYTICS task finished");

	}

}
