<?php
/**
* Modelo FilesStatus para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );
jimport( 'joomla.version' );
jimport( 'joomla.access.rule' );
jimport( 'joomla.application.component.helper' );
jimport('joomla.updater.update' );
jimport('joomla.installer.helper' );
jimport('joomla.installer.installer' );
jimport( 'joomla.application.component.controller' );
jimport('joomla.filesystem.file');

/**
* Modelo Filemanager
*/
class SecuritycheckprosModelFilesStatus extends SecuritycheckproModel
{

/** @var object Pagination */
var $_pagination = null;

/** @var int Total number of files of Pagination */
var $total = 0;

/** @var array The files to process */
private $Stack = array();

/** @var int Total numbers of file/folders in this site */
public $files_scanned = 0;

/** @var int Numbers of files/folders with  incorrect permissions*/
public $files_with_incorrect_permissions = 0;

/** @var string Path to the folder where scans will be stored */
private $folder_path = '';

/** @var string 10 chars strings to add to the filemanager's name */
private $filemanager_name = '';


function __construct()
{
	parent::__construct();
	
	// Establecemos la ruta donde se almacenarán los escaneos
	$this->folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
	
	// Obtenemos el nombre de los escaneos anteriores
	$db = $this->getDbo();
	$query = $db->getQuery(true)
		->select(array($db->quoteName('storage_value')))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
	$db->setQuery($query);
	$stack = $db->loadResult();	
	$stack = json_decode($stack, true);
	
	if(!empty($stack)) {
		$this->filemanager_name = $stack['filename'];
	}
	
	// Establecemos el tamaño máximo de memoria que el script puede consumir
	$params = JComponentHelper::getParams('com_securitycheckpro');
	$memory_limit = $params->get('memory_limit','512M');
	if ( preg_match('/^[0-9]*M$/',$memory_limit) ) {
		ini_set('memory_limit',$memory_limit);
	} else {
		ini_set('memory_limit','512M');
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_VALID_MEMORY_LIMIT'),'error');
	}

	$mainframe = JFactory::getApplication();
 
	// Obtenemos las variables de paginación de la petición
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
	$limitstart = JRequest::getVar('limitstart', 0, '', 'int');

	// En el caso de que los límites hayan cambiado, los volvemos a ajustar
	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

	/* Limitamos a 100 el número de archivos mostrados para evitar que el array desborde la memoria máxima establecida por PHP */
	if ( $limit == 0 ){
		$this->setState('limit', 100);
		$this->setState('showall', 1);
	} else {
		$this->setState('limit', $limit);
	}
	$this->setState('limitstart', $limitstart);
}

/* Función que obtiene un array con los datos que serán mostrados en la opción 'filestatus' */
function loadStack($opcion,$field)
{
	(int) $lower_limit = 0; 
	
	// Establecemos el tamaño máximo de memoria que el script puede consumir
	$params = JComponentHelper::getParams('com_securitycheckpro');
	$memory_limit = $params->get('memory_limit','512M');
	if ( preg_match('/^[0-9]*M$/',$memory_limit) ) {
		ini_set('memory_limit',$memory_limit);
	} else {
		ini_set('memory_limit','512M');
		JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_VALID_MEMORY_LIMIT'),'error');
	}
	
	$db = $this->getDbo();
	
	switch ($opcion) {
		case "permissions":
			
			// Leemos el contenido del fichero
			if ( JFile::exists($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name) ) {
				$stack = JFile::read($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name);
				// Eliminamos la parte del fichero que evita su lectura al acceder directamente
				$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
			}
			
			if(empty($stack)) {
				$this->Stack = array();
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
			
			if(empty($stack)) {
				$this->files_scanned = 0;
				$this->files_with_incorrect_permissions = 0;
				return;
			}
			break;
	
	}
	
	$stack = json_decode($stack, true);
		
	/* Obtenemos el número de registros del array que hemos de mostrar. Si el límite superior es '0', entonces devolvemos todo el array */
	$upper_limit = $this->getState('limitstart');
	$lower_limit = $this->getState('limit');
	
	/* Obtenemos los valores de los filtros */
	$filter_permissions_status = $this->state->get('filter.filemanager_permissions_status');
	$filter_kind = $this->state->get('filter.filemanager_kind');
	$search = htmlentities($this->state->get('filter.filemanager_search'));
	
	switch ($field) {
		case "file_manager":
			$filtered_array = array();
			/* Si el campo 'search' no está vacío, buscamos en todos los campos del array */			
			if (!empty($search) ) {
				$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind,$search) { return ( ($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind) && ( (strstr($element['path'],$search)) || (strstr($element['last_modified'],$search)) || (strstr($element['permissions'],$search)) ) );} ));
			} else {
				$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind) { return ( ($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind) );} ));				
			}
			$this->total = count($filtered_array);
			/* Cortamos el array para mostrar sólo los valores mostrados por la paginación */
			$this->Stack = array_splice($filtered_array, $upper_limit, $lower_limit);
			return ($this->Stack);
		case "files_scanned":
			$this->files_scanned = $stack['files_scanned'];
			return ($this->files_scanned);
		case "files_with_incorrect_permissions":
			$this->files_with_incorrect_permissions = $stack['files_with_incorrect_permissions'];
			return ($this->files_with_incorrect_permissions);
	}

}

protected function populateState()
{
	// Inicializamos las variables
	$app		= JFactory::getApplication();

	$search = $app->getUserStateFromRequest('filter.filemanager_search', 'filter_filemanager_search');
	$this->setState('filter.filemanager_search', $search);
	$filemanager_kind = $app->getUserStateFromRequest('filter.filemanager_kind', 'filter_filemanager_kind');
	$this->setState('filter.filemanager_kind', $filemanager_kind);
	$filemanager_permissions_status = $app->getUserStateFromRequest('filter.filemanager_permissions_status', 'filter_filemanager_permissions_status');
	$this->setState('filter.filemanager_permissions_status', $filemanager_permissions_status);
	$filemanager_permissions_status = $app->getUserStateFromRequest('filter.filemanager_permissions_status', 'filter_filemanager_permissions_status');
						
	parent::populateState();
}

/* Función para la paginación */
function getPagination()
{
// Cargamos el contenido si es que no existe todavía
if (empty($this->_pagination)) {
	jimport('joomla.html.pagination');
$this->_pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit') );
}
return $this->_pagination;
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
	$db->setQuery( $query );
	$db->execute();
}

/* Función para obtener el valor de un campo de la tabla '#_securitycheckpro_file_manager' */
function get_campo_filemanager($campo)
{
	try {
		// Creamos el nuevo objeto query
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		$campo = filter_var($campo, FILTER_SANITIZE_STRING);
		
		// Sanitizamos las entradas
		$campo_sanitizado = $db->Quote($db->escape($campo));
		
		// Construimos la consulta...
		$query->select($campo);
		$query->from('#__securitycheckpro_file_manager');
		$query->where('id=1');
		
		// ... y la lanzamos
		$db->setQuery( $query );
		$result = $db->loadResult();
	} catch (Exception $e) {
		$result = "ERROR";
	}
	
	if ( (is_null($result)) && ($campo=='estado') ) {
		$result = "ERROR";
	}
	
	// Devolvemos el resultado
	return $result;	
}

/* Función para escribir una entrada en el fichero de logs de cambio de permisos */
function write_permission_log($log_array){
	jimport('joomla.log.log');
	JLog::addLogger(array(
		'text_file' => 'change_permissions.log.php',
		'text_entry_format' => '{DATETIME} {SEPARATOR} {MESSAGE}'
	));
	foreach($log_array as $log) {
		$logEntry = new JLogEntry(array_pop($log_array));
		$logEntry->separator = '|';
		JLog::add($logEntry);
	}
}


/* Función para cambiar los permisos de los archivos o carpetas con permisos mal configurados */
function repair(){
	// Inicializamos las variables que contendrán el nivel y la entrada que se escribirán en el fichero de logs
	$entrada = '';
	$nivel = '';
	$log_array = array();
	
	$db = $this->getDbo();
	
	// Cargamos el array de archivos
	if ( JFile::exists($this->folder_path.$this->filemanager_name) ) {
		$stack = JFile::read($this->folder_path.$this->filemanager_name);
		// Eliminamos la parte del fichero que evita su lectura al acceder directamente
		$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
	}
	
	if(empty($stack)) {
		$this->Stack = array();
		$this->files_scanned = 0;
		$this->files_with_incorrect_permissions = 0;
		return;
	}

	$stack = json_decode($stack, true);
	
	// Inicializamos el array que contendrá los ficheros/directorios con los permisos mal configurados
	$filtered_array= array();
	
	$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) { return ( $element['safe'] == 0 );} ));
		
	// ¿ Qué método vamos a usar para cambiar los permisos?
	$params = JComponentHelper::getParams('com_securitycheckpro');
	$change_permissions_option = $params->get('change_permissions_option','chmod');
	
	foreach($filtered_array as $element) {
		$entrada = '';
		$nivel = '';
		(int) $permisos = 0644;
		if ( $element['kind'] == JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_DIRECTORY') ) {
			$permisos = 0755;
		}
		
		if ( $change_permissions_option == 'chmod' ){  // Cambiamos los permisos vía chmod
		
			$change_result = chmod($element['path'],$permisos);
			if ( $change_result == 0 ){
				$nivel = "ERROR";
				$entrada = $element['path'] . JText::_( 'COM_SECURITYCHECKPRO_REPAIR_CHANGE_PERMISSIONS_FAILED' );					
			} else {
				$nivel = "OK";
				$entrada = $element['path'] . JText::_( 'COM_SECURITYCHECKPRO_REPAIR_CHANGE_PERMISSIONS_OK' );				
			}
		} else if ( $change_permissions_option == 'ftp' ){  // Cambiamos los permisos vía ftp
			// Obtenemos los parámetros de conexión al FTP del fichero 'configuration.php'
			jimport('joomla.client.helper');
			$ftpOptions = JClientHelper::getCredentials('ftp');
					
			if ($ftpOptions['enabled'] == 1) {
				// Conectamos al cliente FTP
				jimport('joomla.client.ftp');
				$ftp = &JFTP::getInstance(
					$ftpOptions['host'], $ftpOptions['port'], null,
					$ftpOptions['user'], $ftpOptions['pass']
				);
				
				$result = $ftp->chmod($element['path'],$permisos);
				if ( $result ) {
					$nivel = "OK";
					$entrada = $element['path'] . JText::_( 'COM_SECURITYCHECKPRO_REPAIR_CHANGE_PERMISSIONS_OK' );					
				} else {
					$nivel = "ERROR";
					$entrada = $element['path'] . JText::_( 'COM_SECURITYCHECKPRO_REPAIR_CHANGE_PERMISSIONS_FAILED' );
				}
				
			}				
		}
		
		// Añadimos una entrada al array del fichero de logs
		array_push($log_array,$nivel .'|' .$entrada);			
	} 
	$this->write_permission_log($log_array);
	$this->set_campo_filemanager('estado_cambio_permisos','ENDED');
	
	// Importamos el modelo 'filemanager'...
	JLoader::import('joomla.application.component.model');
	JLoader::import('cpanel', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
	// ... y lanzamos un escaneo para actualizar los resultados
	$filemanager_model = JModelLegacy::getInstance( 'filemanager', 'SecuritycheckprosModel');
	$filemanager_model->set_campo_filemanager('estado','IN_PROGRESS'); 
	$filemanager_model->scan("permissions");	
	$filemanager_model->set_campo_filemanager('estado','ENDED');
}

}