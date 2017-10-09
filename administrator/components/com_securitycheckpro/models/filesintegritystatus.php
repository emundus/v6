<?php
/**
* Modelo FilesIntegrityStatus para el Componente Securitycheckpro
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

/**
* Modelo FilesIntegrity
*/
class SecuritycheckprosModelFilesIntegrityStatus extends SecuritycheckproModel
{
/** @var object Pagination */
var $_pagination = null;

/** @var int Total number of files of Pagination */
var $total = 0;

/** @var array The files to process */
private $Stack_Integrity = array();

/** @var int Total numbers of file/folders in this site */
public $files_scanned_integrity = 0;

/** @var int Numbers of files/folders with  incorrect integrity */
public $files_with_incorrect_integrity = 0;

/** @var date Last integrity check */
public $last_check_integrity = null;

/** @var string Path to the folder where scans will be stored */
private $folder_path = '';

/** @var string fileintegrity's name */
private $fileintegrity_name = '';


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
		->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
	$db->setQuery($query);
	$stack_integrity = $db->loadResult();	
	$stack_integrity = json_decode($stack_integrity, true);
	
	if(!empty($stack_integrity)) {
		$this->fileintegrity_name = $stack_integrity['filename'];
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
		case "integrity":
			// Leemos el contenido del fichero
			if ( JFile::exists($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name) ) {
				$stack = JFile::read($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
				// Eliminamos la parte del fichero que evita su lectura al acceder directamente
				$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
			}
			
			if(empty($stack)) {
				$this->Stack_Integrity = array();
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
			
			if(empty($stack)) {
				$this->files_scanned_integrity = 0;
				$this->files_with_incorrect_integrity = 0;
				return;
			}
			break;
	
	}

	$stack = json_decode($stack, true);
	
	/* Obtenemos el número de registros del array que hemos de mostrar. Si el límite superior es '0', entonces devolvemos todo el array */
	$upper_limit = $this->getState('limitstart');
	$lower_limit = $this->getState('limit');
	
	/* Obtenemos los valores de los filtros */
	$filter_fileintegrity_status = $this->state->get('filter.fileintegrity_status');
	$search = htmlentities($this->state->get('filter.fileintegrity_search'));
		
	switch ($field) {
		case "file_integrity":
		if ( !is_null($stack['files_folders']) ) {
			$filtered_array = array();
			/* Si el campo 'search' no está vacío, buscamos en todos los campos del array */			
			if (!empty($search) ) {
				$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_fileintegrity_status,$search) { return ( ($element['safe_integrity'] == $filter_fileintegrity_status) && ( (strstr($element['path'],$search)) || (strstr($element['hash'],$search)) || (strstr($element['notes'],$search)) ) );} ));
			} else {
				$filtered_array = array_values(array_filter($stack['files_folders'], function ($element) use ($filter_fileintegrity_status) { return ( ($element['safe_integrity'] == $filter_fileintegrity_status) );} ));
			}
			$this->total = count($filtered_array);
			/* Cortamos el array para mostrar sólo los valores mostrados por la paginación */
			$this->Stack_Integrity = array_splice($filtered_array, $upper_limit, $lower_limit);
			return ($this->Stack_Integrity);
		}
		case "files_scanned_integrity":
			$this->files_scanned_integrity = $stack['files_scanned_integrity'];
			return ($this->files_scanned_integrity);
		case "files_with_incorrect_integrity":
			$this->files_with_incorrect_integrity = $stack['files_with_incorrect_integrity'];
			return ($this->files_with_incorrect_integrity);
	}

}

protected function populateState()
{
	// Inicializamos las variables
	$app		= JFactory::getApplication();

	$fileintegrity_search = $app->getUserStateFromRequest('filter.fileintegrity_search', 'filter_fileintegrity_search');
	$this->setState('filter.fileintegrity_search', $fileintegrity_search);
	$fileintegrity_status = $app->getUserStateFromRequest('filter.fileintegrity_status', 'filter_fileintegrity_status');
	$this->setState('filter.fileintegrity_status', $fileintegrity_status);
	
						
	parent::populateState();
}

/*  Función para la paginación */
function getPagination()
{
// Cargamos el contenido si es que no existe todavía
if (empty($this->_pagination)) {
	jimport('joomla.html.pagination');
$this->_pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit') );
}
return $this->_pagination;
}

/* Función que guarda en la BBDD, en formato json, el contenido de un array con todos los ficheros y directorios */
private function saveStack(){
	// Creamos el nuevo objeto query
	$db = $this->getDbo();
	
	// Borramos el fichero del escaneo anterior...
	$delete_integrity_file = JFile::delete($this->folder_path.$this->fileintegrity_name);
	
	try {
		$content_integrity = utf8_encode(json_encode(array('files_folders'	=> $this->Stack_Integrity)));
		$content_integrity = "#<?php die('Forbidden.'); ?>" . PHP_EOL . $content_integrity;
		$result_integrity = JFile::write($this->folder_path.$this->fileintegrity_name, $content_integrity);
	} catch (Exception $e) {
		
	}
	// Borramos el contenido previo de la BBDD
	$query = $db->getQuery(true)
		->delete($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
	$db->setQuery($query);
	$db->execute();
		
	$object = (object)array(
		'storage_key'	=> 'fileintegrity_resume',
		'storage_value'	=> json_encode(array(
			'files_scanned_integrity'		=> $this->files_scanned_integrity,
			'files_with_incorrect_integrity'	=> $this->files_with_incorrect_integrity,
			'last_check_integrity'	=> $this->last_check_integrity,
			'filename'		=>$this->fileintegrity_name
		))
	);
	$db->insertObject('#__securitycheckpro_storage', $object);
}


/* Función que cambia a '1' el valor del campo 'safe_integrity' de todos los ficheros de la BBDD cuyo valor actual sea '0' (están marcados como no seguros) */
function mark_all_unsafe_files_as_safe(){
	
	// Cargamos los archivos de la BBDD
	$db = $this->getDbo();
	
	// Leemos el contenido del fichero
	if ( JFile::exists($this->folder_path.$this->fileintegrity_name) ) {
		$stack = JFile::read($this->folder_path.$this->fileintegrity_name);
		// Eliminamos la parte del fichero que evita su lectura al acceder directamente
		$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
	}
	
	$query = $db->getQuery(true)
		->select(array($db->quoteName('storage_value')))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
	$db->setQuery($query);
	$stack_resume = $db->loadResult();
	
	if(empty($stack)) {
		return;
	}

	$stack = json_decode($stack, true);
	$stack_resume = json_decode($stack_resume, true);
	
	// Si existen archivos con permisos incorrectos, les cambiamos su estado
	if ($stack_resume['files_with_incorrect_integrity'] > 0 ) {
	
		/* Cargamos el lenguaje del sitio */
		$lang = JFactory::getLanguage();
		$lang->load('com_securitycheckpro',JPATH_ADMINISTRATOR);
		
		// Cargamos las variables con el contenido almacenado en la BBDD
		$this->Stack_Integrity = $stack['files_folders'];
		$this->files_scanned_integrity = $stack_resume['files_scanned_integrity'];
		$this->files_with_incorrect_integrity = 0;
		$this->last_check_integrity = $stack_resume['last_check_integrity'];
		
		$tamanno_array = count($this->Stack_Integrity);
		$indice = 0;
		
		while ( $indice < $tamanno_array ) {
			/* Dejamos sin efecto el tiempo máximo de ejecución del script. Esto es necesario cuando existen miles de archivos a escanear */
			set_time_limit(0);
			if ( $this->Stack_Integrity[$indice]['safe_integrity'] == 0 ) {
				$this->Stack_Integrity[$indice]['notes'] = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_OK');
				$this->Stack_Integrity[$indice]['safe_integrity'] = (int) 1;				
			}
			$indice++;
		}
		
		// Guardamos los cambios
		$this->saveStack();
	}
}

/* Función que cambia a '1' el valor del campo 'safe_integrity' de todos los ficheros seleccionados */
function mark_checked_files_as_safe(){
	// Creamos el objeto JInput para obtener las variables del formulario
	$jinput = JFactory::getApplication()->input;
	
	// Obtenemos las rutas de los ficheros a analizar
	$filenames = $jinput->get('filesintegritystatus_table',null,'array');
	
	// Cargamos los archivos de la BBDD
	$db = $this->getDbo();
	
	// Leemos el contenido del fichero
	if ( JFile::exists($this->folder_path.$this->fileintegrity_name) ) {
		$stack = JFile::read($this->folder_path.$this->fileintegrity_name);
		// Eliminamos la parte del fichero que evita su lectura al acceder directamente
		$stack = str_replace("#<?php die('Forbidden.'); ?>",'',$stack);
	}
	
	$query = $db->getQuery(true)
		->select(array($db->quoteName('storage_value')))
		->from($db->quoteName('#__securitycheckpro_storage'))
		->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
	$db->setQuery($query);
	$stack_resume = $db->loadResult();
	
	if(empty($stack)) {
		return;
	}

	$stack = json_decode($stack, true);
	$stack_resume = json_decode($stack_resume, true);
	
	// Si existen archivos con permisos incorrectos, les cambiamos su estado
	if ($stack_resume['files_with_incorrect_integrity'] > 0 ) {
		/* Cargamos el lenguaje del sitio */
		$lang = JFactory::getLanguage();
		$lang->load('com_securitycheckpro',JPATH_ADMINISTRATOR);
		
		// Creamos un array de rutas
		$this->Stack_Integrity = $stack['files_folders'];
		$array_paths = array_map( function ($element) { return $element['path']; },$this->Stack_Integrity );
		// Número de elementos del array		
		$tamanno_array = count($filenames);
			
		foreach ( $filenames as $path ) {
			// Buscamos el índice del array que contiene la información que queremos modificar...			
			$array_key = array_search($path,$array_paths);
			if (is_numeric($array_key) ) {
				// ... y actualizamos la información
				$this->Stack_Integrity[$array_key]['safe_integrity'] = 1;	
				$this->Stack_Integrity[$array_key]['notes'] = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_OK');							
			}
			
		}
		// Actualizamos los parámetros de archivos escaneados y con integridad incorrecta
		$this->files_scanned_integrity = $stack_resume['files_scanned_integrity'];
		$this->files_with_incorrect_integrity = $stack_resume['files_with_incorrect_integrity'] - $tamanno_array;
				
		// Guardamos los cambios
		$this->saveStack();
	}
		
}

}