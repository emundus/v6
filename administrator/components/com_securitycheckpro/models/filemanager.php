<?php
/**
 * Modelo FileManager para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Session\Session as JSession;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Log\Log as JLog;
use Joomla\Filesystem\File as JFile;

/**
 * Modelo Filemanager
 */
class SecuritycheckprosModelFileManager extends SecuritycheckproModel
{
    /**
     * @var object Pagination 
     */
    var $_pagination = null;

    /**
     * @var int Total number of files of Pagination 
     */
    var $total = 0;

    /**
     * @var array The files to process 
     */
    private $Stack = array();

    /**
     * @var array The files to process 
     */
    private $Stack_Integrity = array();

    /**
     * @var int Total numbers of file/folders in this site. Permissions option 
     */
    public $files_scanned = 0;

    /**
     * @var int Total numbers of file/folders in this site. Integrity option
     */
    public $files_scanned_integrity = 0;

    /**
     * @var int Numbers of files/folders with  incorrect permissions 
     */
    public $files_with_incorrect_permissions = 0;

    /**
     * @var int Numbers of files/folders with  incorrect integrity 
     */
    public $files_with_incorrect_integrity = 0;

    /**
     * @var array Skip subdirectories and files of these directories. Permissions option 
     */
    private $skipDirsPermissions = array();

    /**
     * @var array Skip subdirectories and files of these directories. Integrity option 
     */
    private $skipDirsIntegrity = array();

    /**
     * @var int Percent of files processed each time 
     */
    public $last_percent = 0;

    /**
     * @var int Percent of files processed each time 
     */
    private $last_percent_permissions = 0;

    /**
     * @var int Percent of files processed each time 
     */
    private $files_processed_permissions = 0;

    /**
     * @var boolean Task completed 
     */
    private $task_completed = false;

    /**
     * @var string Path to the folder where scans will be stored 
     */
    private $folder_path = '';

    /**
     * @var string filemanager's name 
     */
    private $filemanager_name = '';

    /**
     * @var string fileintegrity's name 
     */
    private $fileintegrity_name = '';

    /**
     * @var int Numbers of files scanned looking for malware 
     */
    public $files_scanned_malwarescan = 0;

    /**
     * @var int Numbers of files suspicious of malware 
     */
    public $suspicious_files = 0;

    /**
     * @var int Percent of files processed each time 
     */
    private $files_processed_malwaresecan = 0;

    /**
     * @var int Percent of files processed each time 
     */
    private $last_percent_malwarescan = 0;

    /**
     * @var array The files to process 
     */
    private $Stack_malwarescan = array();

    /**
     * @var string malwarescan's name 
     */
    private $malwarescan_name = '';

    /**
     * @var string file content 
     */
    public $content = null;

    /**
     * @var array File extensions to analyze looking for malware 
     */
    private $fileExt = null;

    /**
     * @var array Use the exceptions stablished in File Manager option (Malware scan) 
     */
    private $use_filemanager_exceptions = 1;

    /**
     * @var array Skip subdirectories and files of these directories. Integrity option 
     */
    private $skipDirsMalwarescan = array();

    /**
     * @var int Percent of files processed each time 
     */
    private $files_processed = 0;

    /**
     * @var resource  The file pointer to the current log file 
     */
    protected $fp = null;

    /**
     * @var File name for permissions log 
     */
    private $filepermissions_log_name = null;

    /**
     * @var File name for integrity log 
     */
    private $fileintegrity_log_name = null;

    /**
     * @var File name for malware log 
     */
    private $filemalware_log_name = null;
	
	private $controlcenter_log_name = null;
	
	/**
     * @var object Pagination 
     */
    var $global_model = null;

    function __construct($config = array())
    {

        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            'malware_type', 'alert_level'
            );
        }

        parent::__construct();
    
        // Excepción
        $excepcion_escaneos = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'protection.php';
    
        // Establecemos la ruta donde se almacenarán los escaneos
        $this->folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans';
		
		require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model.php';
		$this->global_model = new SecuritycheckproModel();
    
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
        
        // Añadimos los directorios 'cache', 'tmp' y 'log' a la lista de excepciones
        $this->skipDirsPermissions[] = rtrim(JPATH_CACHE, DIRECTORY_SEPARATOR);
        $this->skipDirsPermissions[] = rtrim(JPATH_ROOT. DIRECTORY_SEPARATOR . 'cache', DIRECTORY_SEPARATOR);
        $this->skipDirsIntegrity[] = rtrim(JPATH_CACHE, DIRECTORY_SEPARATOR);
        $this->skipDirsIntegrity[] = rtrim(JPATH_ROOT. DIRECTORY_SEPARATOR . 'cache', DIRECTORY_SEPARATOR);
        $this->skipDirsMalwarescan[] = rtrim(JPATH_CACHE, DIRECTORY_SEPARATOR);
        $this->skipDirsMalwarescan[] = rtrim(JPATH_ROOT. DIRECTORY_SEPARATOR . 'cache', DIRECTORY_SEPARATOR);
        $this->skipDirsMalwarescan[] = $this->folder_path;
        if (version_compare(JVERSION, '3.0', 'ge')) {
            $this->skipDirsPermissions[] = rtrim(JFactory::getConfig()->get('tmp_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'tmp'), DIRECTORY_SEPARATOR);
            $this->skipDirsPermissions[] = rtrim(JFactory::getConfig()->get('log_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'logs'), DIRECTORY_SEPARATOR);
            $this->skipDirsIntegrity[] = rtrim(JFactory::getConfig()->get('tmp_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'tmp'), DIRECTORY_SEPARATOR);
            $this->skipDirsIntegrity[] = rtrim(JFactory::getConfig()->get('log_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'logs'), DIRECTORY_SEPARATOR);
            //$this->skipDirsMalwarescan[] = rtrim(JFactory::getConfig()->get('tmp_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'tmp'), DIRECTORY_SEPARATOR);
            $this->skipDirsMalwarescan[] = rtrim(JFactory::getConfig()->get('log_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'logs'), DIRECTORY_SEPARATOR);
        } else
        {
            $this->skipDirsPermissions[] = rtrim(JFactory::getConfig()->getValue('tmp_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'tmp'), DIRECTORY_SEPARATOR);
            $this->skipDirsPermissions[] = rtrim(JFactory::getConfig()->getValue('log_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'logs'), DIRECTORY_SEPARATOR);
            $this->skipDirsIntegrity[] = rtrim(JFactory::getConfig()->getValue('tmp_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'tmp'), DIRECTORY_SEPARATOR);
            $this->skipDirsIntegrity[] = rtrim(JFactory::getConfig()->getValue('log_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'logs'), DIRECTORY_SEPARATOR);
            //$this->skipDirsMalwarescan[] = rtrim(JFactory::getConfig()->getValue('tmp_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'tmp'), DIRECTORY_SEPARATOR);
            $this->skipDirsMalwarescan[] = rtrim(JFactory::getConfig()->getValue('log_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'logs'), DIRECTORY_SEPARATOR);
        }
    
        // Añadimos el fichero de escaneos como excepción al escaneo de integridad
        array_push($this->skipDirsIntegrity, $this->folder_path);
		
		// Añadimos el pat de Akeeba por defecto para almacenar los backups
        array_push($this->skipDirsIntegrity, '/administrator/components/com_akeeba/backup/');
		
		// Añadimos el fichero 'protection.php' como excepción a los escaneos de integridad y malware
        array_push($this->skipDirsIntegrity, $excepcion_escaneos);
        array_push($this->skipDirsMalwarescan, $excepcion_escaneos);
    
        // Obtenemos las excepciones extablecidas por el usuario para la opción 'File Manager' 
        $exceptions_permissions = $params->get('file_manager_path_exceptions', null);
    
        // Creamos un array que contendrá rutas de archivos o directorios exentos del chequeo de permisos
        $exceptions_permissions_array= null;
        if (!is_null($exceptions_permissions)) {
            $exceptions_permissions_array = explode(',', $exceptions_permissions);
            // Añadimos las excepciones al array de excepciones
            foreach($exceptions_permissions_array as $exception_path)
            {
                $this->skipDirsPermissions[] = rtrim($exception_path, DIRECTORY_SEPARATOR);
            }
        }
    
        // Obtenemos las excepciones extablecidas por el usuario para la opción 'File Integrity' 
        $exceptions_integrity = $params->get('file_integrity_path_exceptions', null);
    
        // Creamos un array que contendrá rutas de archivos o directorios exentos del chequeo de integridad
        $exceptions_integrity_array= null;
        if (!is_null($exceptions_integrity)) {
            $exceptions_integrity_array = explode(',', $exceptions_integrity);
            // Añadimos las excepciones al array de excepciones
            foreach($exceptions_integrity_array as $exception_path)
            {
                $this->skipDirsIntegrity[] = rtrim($exception_path, DIRECTORY_SEPARATOR);
            }
        }
    
        // Obtenemos las excepciones establecidas por el usuario para la opción 'File Manager' 
			
        $exceptions_malwarescan = $params->get('malwarescan_path_exceptions', null);
		    
        // Creamos un array que contendrá rutas de archivos o directorios exentos del chequeo de permisos
        $exceptions_malwarescan_array= null;
        if (!is_null($exceptions_malwarescan)) {
            $exceptions_malwarescan_array = explode(',', $exceptions_malwarescan);
            // Añadimos las excepciones al array de excepciones
            foreach($exceptions_malwarescan_array as $exception_path)
            {
                $this->skipDirsMalwarescan[] = rtrim($exception_path, DIRECTORY_SEPARATOR);
            }
        }
    
        // Obtenemos el nombre de los escaneos anteriores
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
        $db->setQuery($query);
        $stack = $db->loadResult();
        $stack = json_decode($stack, true);
    
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
        $db->setQuery($query);
        $stack_integrity = $db->loadResult();
        $stack_integrity = json_decode($stack_integrity, true);
        
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('malwarescan_resume'));
        $db->setQuery($query);
        $stack_malwarescan = $db->loadResult();
        $stack_malwarescan = json_decode($stack_malwarescan, true);
    
        // Obtenemos el nombre de los ficheros de logs
        $this->get_log_filename("filepermissions_log");
        $this->get_log_filename("fileintegrity_log");
        $this->get_log_filename("filemalware_log");
        
        if ((!empty($stack)) && (isset($stack['filename']))) {
            $this->filemanager_name = $stack['filename'];
        }
    
        if ((!empty($stack_integrity)) && (isset($stack_integrity['filename']))) {            
            $this->fileintegrity_name = $stack_integrity['filename'];
        }
    
        if ((!empty($stack_malwarescan)) && (isset($stack_malwarescan['filename']))) {
            $this->malwarescan_name = $stack_malwarescan['filename'];
        }
    
        // Obtenemos las extensiones de ficheros a analizar
        $this->fileExt = $params->get('malwarescan_file_extensions', null);
    
        // ¿El escaneo de malware usa las mismas excepciones que el de integridad?
        $this->use_filemanager_exceptions = $params->get('use_filemanager_exceptions', 1);
    
        // Chequeamos si ha pasado más de una hora desde el último escaneo online para inicializar la variable que la controla
        $this->check_last_onlinecheck();
    
        $mainframe = JFactory::getApplication();
 
        // Obtenemos las variables de paginación de la petición
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $jinput = JFactory::getApplication()->input;
        $limitstart = $jinput->get('limitstart', 0, 'int');

        // En el caso de que los límites hayan cambiado, los volvemos a ajustar
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        /* Limitamos a 100 el número de archivos mostrados para evitar que el array desborde la memoria máxima establecida por PHP */
        if ($limit == 0) {
            $this->setState('limit', 100);
            $this->setState('showall', 1);
        } else
        {
            $this->setState('limit', $limit);
        }
        $this->setState('limitstart', $limitstart);
    }

    /* When shutting down this class always close any open log files. */
    public function __destruct()
    {
        $this->close_Log();
    }

    protected function populateState()
    {
        // Inicializamos las variables
        $app        = JFactory::getApplication();
    

        $search = $app->getUserStateFromRequest('filter.filemanager_search', 'filter_filemanager_search');
        $this->setState('filter.filemanager_search', $search);
        $filemanager_kind = $app->getUserStateFromRequest('filter.filemanager_kind', 'filter_filemanager_kind');
        $this->setState('filter.filemanager_kind', $filemanager_kind);
        $filemanager_permissions_status = $app->getUserStateFromRequest('filter.filemanager_permissions_status', 'filter_filemanager_permissions_status');
        $this->setState('filter.filemanager_permissions_status', $filemanager_permissions_status);
        $filemanager_permissions_status = $app->getUserStateFromRequest('filter.filemanager_permissions_status', 'filter_filemanager_permissions_status');
    
        $fileintegrity_search = $app->getUserStateFromRequest('filter.fileintegrity_search', 'filter_fileintegrity_search');
        $this->setState('filter.fileintegrity_search', $fileintegrity_search);
        $fileintegrity_status = $app->getUserStateFromRequest('filter.fileintegrity_status', 'filter_fileintegrity_status');
        $this->setState('filter.fileintegrity_status', $fileintegrity_status);
    
        $malwarescan_search = $app->getUserStateFromRequest('filter.malwarescan_search', 'filter_malwarescan_search');
        $this->setState('filter.malwarescan_search', $malwarescan_search);
        $malwarescan_status = $app->getUserStateFromRequest('filter.malwarescan_status', 'filter_malwarescan_status');
        $this->setState('filter.malwarescan_status', $malwarescan_status);
        
        parent::populateState();
    }

    /* Función que obtiene todos los archivos del sitio */
    public function getFiles($root, $include_exceptions, $recursive, $opcion)
    {
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        
        if(empty($root)) { $root = JPATH_ROOT;
        }

        if(empty($root)) {
            $root = '..';
            $root = realpath($root);
        }
        
        jimport('joomla.filesystem.folder');
    
        // ¿Debemos escanear todos los archivos o sólo los ejecutables?
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $scan_executables_only = $params->get('scan_executables_only', 0);
		$excludedFiles = null;
    
        if ($opcion == "malwarescan_modified") {
            $files_name = $this->loadModifiedFiles();            
        } else 
        {
            // Extensiones excluidas (no serán analizadas)
            $excludedExtensions = array('\.aif','\.iff','\.conf','\.m3u','\.m4a','\.mid','\.mp3','\.mpa','\.wav','\.wma','\.3g2','\.3gp','\.asf','\.asx','\.avi','\.flv','\.m4v','\.mov','\.mp4','\.mpg','\.rm','\.srt','\.swf','\.vob','\.wmv','\.bmp','\.dds','\.gif','\.jpg','\.png','\.psd','\.pspimage','\.tga','\.thm','\.tif','\.tiff','\.yuv','\.eps','\.svg','\.txt','\.tar','\.zip','\.jpa','\.pdf');        
            $excludedExtensions = array_merge($excludedExtensions, array_map('strtoupper', $excludedExtensions));
        
			if (!$include_exceptions) {
				$text_for_log_exceptions = "Excluded files/folders - WILL NOT BE STORED IN DATABASE";		
			} else {
				$text_for_log_exceptions = "Excluded files/folders - WILL BE STORED IN DATABASE";
			}
			
			$this->write_log("****** " . $text_for_log_exceptions . " ******");
					
			
            /* Añadimos las excepciones de integridad para excluirlas del escaneo inicial */
            if ($opcion == "permissions") {
				foreach($this->skipDirsPermissions as $file)
                {					
					$this->write_log($file);
					if (!$include_exceptions) {
						$last_part = explode(DIRECTORY_SEPARATOR, $file);
						$excludedFiles[] = end($last_part);							
					}
                    
                }
            } else if ($opcion == "integrity") {
                foreach($this->skipDirsIntegrity as $file)
                {
                    $this->write_log($file);
					if (!$include_exceptions) {
						$last_part = explode(DIRECTORY_SEPARATOR, $file);
						$excludedFiles[] = end($last_part);							
					}
                }
            } else if ($opcion == "malwarescan") {
                $exceptions = $this->skipDirsIntegrity;
                if (!$this->use_filemanager_exceptions) {
                    $exceptions = $this->skipDirsMalwarescan;
                } 
                foreach($exceptions as $file)
                {					
                    $this->write_log($file);
					if (!$include_exceptions) {
						$last_part = explode(DIRECTORY_SEPARATOR, $file);
						$excludedFiles[] = end($last_part);							
					}
                }
            }
			
			$this->write_log("****** End Excluded files/folders ******");
			
			// This is needed to avoid an error in the JFolder procedure
			if ( is_null($excludedFiles) ) {
				$excludedFiles = array();
			}
        
            /* Comprobamos si tenemos que escanear todos los archivos o sólo los ejecutables */
            if ($scan_executables_only) {
                $files_name = JFolder::files($root, '.', true, true, $excludedFiles, $excludedExtensions);            
            } else
            {
				$files_name = JFolder::files($root, '.', true, true, $excludedFiles);
								
                // Buscamos si existe el archivo .htaccess o .htpasswd en la ruta a escanear (sólo lo buscamos en la ruta base, no en subdirectorios)
                if (file_exists($root . DIRECTORY_SEPARATOR . ".htaccess")) {
                    $files_name[] = $root . DIRECTORY_SEPARATOR . ".htaccess";
                }
				if (file_exists($root . DIRECTORY_SEPARATOR . ".htpasswd")) {
                    $files_name[] = $root . DIRECTORY_SEPARATOR . ".htpasswd";
                }
            }        
        }
    
        /* Reemplazamos los caracteres distintos del usado como DIRECTORY_SEPARATOR. Esto pasa, por ejemplo, en un servidor IIS:  */
        $files_name = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $files_name);
			            
        if ($opcion == "permissions") {
			
			$this->files_scanned += count($files_name);
        
            $files = array();
            if (!empty($files_name)) {
                try
                {
                    foreach($files_name as $file)
                    {
                        // Transformamos el nombre del archivo a UTF-8 - Eliminado en versión 3.4.6
                        //$file = mb_convert_encoding($file, "UTF-8");
                
                        $this->files_processed_permissions++;
                        $percent_permissions = intval(round(($this->files_processed_permissions / $this->files_scanned) * 100));
                        if ((($percent_permissions - $this->last_percent_permissions) >= 10) && ($percent_permissions < 100)) {
                            $this->set_campo_filemanager("files_scanned", $percent_permissions);
                            $this->last_percent_permissions = $percent_permissions;
                        } else if ($percent_permissions == 100) {
                            $this->task_completed = true;
                        }
                    
                        /* Dejamos sin efecto el tiempo máximo de ejecución del script. Esto es necesario cuando existen miles de archivos a escanear */
                        set_time_limit(0);
                        $safe = 1;
                        // Chequeamos si el archivo está incluido en las excepciones
                        if ($recursive == 1) {  // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones
                            if (!is_null($this->skipDirsPermissions)) {
                                   $i = 0;
                                foreach ($this->skipDirsPermissions as $excep)
                                   {
                                    if (strstr($file . DIRECTORY_SEPARATOR, $excep . DIRECTORY_SEPARATOR)) {  // Añadimos una barra invertida a la comparación por si la excepción es un directorio
                                             $safe = (int) 2;
                                    }
                                    $i++;
                                }
                            
                            }
                        } else
                        {  // Comprobamos que si el archivo está explícitamente en la lista de excepciones
                            if ((!is_null($this->skipDirsPermissions)) && (in_array($file, $this->skipDirsPermissions))) {
                                $safe = (int) 2;
                            } else
                            {
                                // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones, pero no a sus subdirectorios
                                if (!is_null($this->skipDirsPermissions)) {
                                    $i = 0;
                                    foreach ($this->skipDirsPermissions as $excep)
                                    {   
										// Search for the file in the exception's string
										$string_pos = strpos($file, $excep);										
										if ($string_pos !== false) {											
											// File is into the exception
											$length = strlen($excep);
																						
											// Search for new / char. If exists then the file is into a subfolder
											$next_directory_separator = strpos($file, DIRECTORY_SEPARATOR, $string_pos+$length+1);
																						
											// File is not included into a subdirectory
											if ($next_directory_separator === false) {
												$safe = (int) 2;
												$i++;
											} 
										}																			
                                    }
                                }
                            }
                        }
						
						if ($safe == 2) {	
							$this->write_log("FILE: " . $file . " -- In exception list");                            
                        } else {
							$this->write_log("FILE: " . $file);
						}
						
                        // Si el archivo se encuentra entre las excepciones y la opción 'añadir excepciones a la bbdd' está activada guardamos el archivo. 
                        if ((($safe == 2) && ($include_exceptions)) || ($safe!=2)) {
                            $permissions = $this->file_perms($file);
                             // Obtenemos la extensión del archivo
                            $last_part = explode('.', $file);
                            $extension = end($last_part);
                            if (($permissions > '0644') && ($safe!=2)) {
                                $safe = 0;
                                $this->files_with_incorrect_permissions = $this->files_with_incorrect_permissions+1;
                            }
                            $last_part = explode(DIRECTORY_SEPARATOR, $file);
                            $last_part_2 = explode('.', end($last_part));
                            $files[] = array(
                            'path'      => $file,                            
                            'kind'    => $lang->_('COM_SECURITYCHECKPRO_FILEMANAGER_FILE'),
                            'permissions' => $permissions,
                            'last_modified' => date('Y-m-d H:i:s', filemtime($file)),
                            'safe' => $safe
                            );                        
                        }
                    }
                } catch (Exception $e)
                {
                    $this->write_log("EXCEPTION CAUGHT!!!: " . $e->getMessage() . " " . $file, "ERROR");                
                }
            }
        
            if(!empty($files)) {
                $this->Stack = array_merge($this->Stack, $files);        
            } 
        } else if ($opcion == "integrity") {        
            // Esta variable indica si se ha cambiado el algoritmo con el que se ha calculado el valor hash de los ficheros
            $hash_alg_has_changed = false;
            // Esta variable contendrá el valor hash actual del fichero
            $hash_actual = null;
            // Contendrá si la integridad del archivo es correcta o si el fichero es nuevo. Por defecto es que está bien, que será la opción más común
            $texto_notes = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_OK'); 
            $new_file = (int) 0; // ¿Es nuevo el archivo? Por defecto es NO.
            $factor_corrector = (int) 0; // ¿Es nuevo el archivo? Por defecto es NO.
            // Array que contendrá las rutas de los archivos de escaneos anteriores
            $array_rutas_anterior = array();
            // Array que contendrá los archivos pertenecientes a excepciones
            $array_excepciones_actual = array();
                        
            //Inicializamos el porcentaje de ficheros escaneados
            $this->set_campo_filemanager("files_scanned_integrity", 0);        
        
            // Cargamos los datos de la BBDD, si existen, de escaneos anteriores.
            if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name)) {
                   $stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
                   // Eliminamos la parte del fichero que evita su lectura al acceder directamente
                   $stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
            }
        
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                ->select(array($db->quoteName('storage_value')))
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
            $db->setQuery($query);
            $stack_resume = $db->loadResult();
        
            if (empty($stack)) {
                $this->Stack_Integrity = array();            
            } else 
            {
                $this->Stack_Integrity = json_decode($stack, true);
                $this->stack_resume = json_decode($stack_resume, true);
                // Actualizamos el valor de los archivos modificados desde el último escaneo
                $this->files_with_incorrect_integrity = $this->stack_resume['files_with_incorrect_integrity'];
                // Cargamos los archivos que están almacenados en la BBDD
                $this->Stack_Integrity = $this->Stack_Integrity['files_folders'];
            }
        
            // Recorremos los datos que estaban almacenados en la BBDD y los comparamos con los obtenidos en el nuevo escaneo para eliminar los ficheros que ya no existan
            if (!is_null($this->Stack_Integrity)) {
				$tamanno_array = count($this->Stack_Integrity);
			} else {
				$tamanno_array = 0;
			}
                
            // Actualizamos la BBDD para mostrar información del estado del chequeo
            $this->set_campo_filemanager('estado_integrity', 'CHECKING_DELETED_FILES');
        
            if (!empty($stack)) {
                set_time_limit(0);
				if (!empty($this->Stack_Integrity)) {
					$array_rutas_anterior = array_map(
						function ($element) {
							return $element['path']; 
						}, $this->Stack_Integrity
					);
					$diff = array_diff($array_rutas_anterior, $files_name);
					$diff = array_keys($diff);
					$this->factor_corrector = 0;
					foreach($diff as $indice)
					{
						/* Si el archivo estaba marcado como 'inseguro', decrementamos el valor de la variable '$this->files_with_incorrect_integrity', puesto que el elemento ya no existe en el sistema de archivos */
						if (($this->Stack_Integrity[$indice - $this->factor_corrector]['safe_integrity'] == 0) && (($this->files_with_incorrect_integrity) > 0)) {
							$this->files_with_incorrect_integrity--;
						}
						// Eliminamos el elemento del array
						array_splice($this->Stack_Integrity, $indice - $this->factor_corrector, 1);
						// Hemos eliminado un elemento del array, así que decrementamos el valor de 'tamanno_array' e incrementamos el factor corrector para que no haya errores al referenciar los elementos del array
						$tamanno_array--;
						$this->factor_corrector++;
					}
					$array_rutas_anterior = array_map(
						function ($element) {
							return $element['path']; 
						}, $this->Stack_Integrity
					);
				}
            }
                
            // Actualizamos la BBDD para mostrar información del estado del chequeo
            $this->set_campo_filemanager('estado_integrity', 'IN_PROGRESS');
                
            // Obtenemos el algoritmo con el que crearemos el valor hash de los ficheros (extraido del fichero de configuración)
            $params = JComponentHelper::getParams('com_securitycheckpro');
            $hash_alg = $params->get('file_integrity_hash_alg', 'SHA1');
            // Obtenemos el algoritmo con el que se ha calculado el hash de los ficheros almacenados en la BBDD (extraido de la tabla '#__securitycheckpro_file_manager')
            $hash_alg_db = $this->get_campo_filemanager('hash_alg');
            /* Comparamos los dos valores anteriores para ver si se ha cambiado o no estaba establecido el algoritmo con el que se calcula el hash. En ese caso debemos volver a almacenar los valores obtenidos para cada fichero chequeado */
            if ((is_null($hash_alg_db)) || ($hash_alg != $hash_alg_db)) {
                $hash_alg_has_changed = true;
                $this->set_campo_filemanager('hash_alg', $hash_alg);
            }
                
            $this->files_scanned_integrity += count($files_name);
        
            $files = array();
        
            $array_hashes_actual = array();
						        
            if (!empty($files_name)) {
                try
                {
                    foreach($files_name as $file)
                    {                    
                        // Transformamos el nombre del archivo a UTF-8 - Eliminado en versión 3.4.6
                        //$file = mb_convert_encoding($file, "UTF-8");
                                                    
                        $this->files_processed++;
                        $percent = intval(round(($this->files_processed / $this->files_scanned_integrity) * 100));
                        if ((($percent - $this->last_percent) >= 10) && ($percent < 100)) {
                            $this->set_campo_filemanager("files_scanned_integrity", $percent);
                            $this->last_percent = $percent;
                        } else if ($percent == 100) {
                            $this->task_completed = true;
                        }
                                                
                        // Dejamos sin efecto el tiempo máximo de ejecución del script. Esto es necesario cuando existen miles de archivos a escanear 
                        set_time_limit(0);
                        $safe_integrity = 1;
                        // Chequeamos si el archivo está incluido en las excepciones
                        if ($recursive == 1) {  // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones
                            if (!is_null($this->skipDirsIntegrity)) {
                                $i = 0;
                                foreach ($this->skipDirsIntegrity as $excep)
                                {
                                    if (strstr($file . DIRECTORY_SEPARATOR, $excep . DIRECTORY_SEPARATOR)) {  // Añadimos una barra invertida a la comparación por si la excepción es un directorio
                                        $safe_integrity = (int) 2;
                                    }
                                    $i++;
                                }
                            
                            }
                        } else
                        {  // Comprobamos que si el archivo está explícitamente en la lista de excepciones
                            if ((!is_null($this->skipDirsIntegrity)) && (in_array($file, $this->skipDirsIntegrity))) {
                                $safe_integrity = (int) 2;
                            } else
                            {
                                 // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones, pero no a sus subdirectorios
                                if (!is_null($this->skipDirsIntegrity)) {
                                    $i = 0;
                                    foreach ($this->skipDirsIntegrity as $excep)
                                    {
										// Search for file position in the exception string
										$string_pos = strpos($file, $excep);										
										if ($string_pos !== false) {											
											// File is into the exception
											$length = strlen($excep);
																						
											// Search for new / char. If exists then the file is into a subfolder
											$next_directory_separator = strpos($file, DIRECTORY_SEPARATOR, $string_pos+$length+1);
																						
											// File is not included into a subdirectory
											if ($next_directory_separator === false) {
												$safe_integrity = (int) 2;
												$i++;
											} 
										}                                       
                                    }
                                }
                            }
                        }
                                               
                        //Si el archivo está en la lista de excepciones lo añadimos al array 'array_excepciones_actual'
                        if ($safe_integrity == 2) {	
							$this->write_log("FILE: " . $file . " -- In exception list");
                            $indice_excepcion = array_search($file, $array_rutas_anterior);
                            if (!($indice_excepcion === false)) {
                                array_push($array_excepciones_actual, $indice_excepcion);
                            }
							$hash_actual = "Notcalculated" . rand();
                        } else {
							// Calculamos el hash del archivo
							 $this->write_log("FILE: " . $file);
							 
							switch ($hash_alg_db)
							{
							case "SHA1":
								$hash_actual = sha1_file($file);
								break;
							case "MD5":
								$hash_actual = md5_file($file);
								break;
							}		
						}
						if (empty($stack)) {
							//$texto_notes = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_NEW_FILE');
							$texto_notes = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_OK');
							$new_file = (int) 1;
							if ($safe_integrity != 2) {  // El archivo es nuevo y no está en la lista de excepciones
								// Lo marcamos con integridad correcta porque es el primer escaneo
								$safe_integrity = 1;                            
							} else 
							{
								$texto_notes = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_IN_EXCEPTIONS_LIST');
							}
							// Add file to database if is not in exception list or if it is and include_exceptions is on
							if ( ($safe_integrity != 2) || (($safe_integrity == 2) && ($include_exceptions)) ) {
								$last_part = explode(DIRECTORY_SEPARATOR, $file);
								if (!empty($file)) {								
									$files[] = array(
									'path'      => $file,                            
									'hash' => $hash_actual,
									'notes' => $texto_notes,
									'new_file' => $new_file,
									'safe_integrity' => $safe_integrity
									);
								} 
							}
						} else
						{
							$array_hashes_actual[]  = $hash_actual;
						}  
						
                    }
                } catch (Exception $e)
                {
                    $this->write_log("EXCEPTION CAUGHT!!!: " . $e->getMessage() . " " . $file, "ERROR");                
                }
            
                $array_hashes_anterior = null;
				
				if (!empty($this->Stack_Integrity)) {
					$array_hashes_anterior = array_map(
						function ($element) {
							return $element['hash']; 
						}, $this->Stack_Integrity
					);
				} 
				
                if (is_null($array_hashes_anterior)) {
                    $array_hashes_anterior= array();
                }
            
                $diff = array_diff($array_hashes_actual, $array_hashes_anterior);
                $diff = array_keys($diff);
				
				$this->write_log("------- New/modified files --------");
								
               foreach($diff as $indice)
                {
                    try 
                    {
                        $file = $files_name[$indice]; 
						// Removed in 3.4.6 version to avoid storing rare filenames
                        //$file = utf8_encode($file);                    
                                                                             
                        $safe_integrity = 1;
                        // Chequeamos si el archivo está incluido en las excepciones
                        if ($recursive == 1) {  // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones
                            if (!is_null($this->skipDirsIntegrity)) {
                                         $i = 0;
                                foreach ($this->skipDirsIntegrity as $excep)
                                         {
                                    if (strstr($file . DIRECTORY_SEPARATOR, $excep . DIRECTORY_SEPARATOR)) {  // Añadimos una barra invertida a la comparación por si la excepción es un directorio
                                           $safe_integrity = (int) 2;
                                    }
                                    $i++;
                                }
                            
                            }
                        } else 
                           {  // Comprobamos que si el archivo está explícitamente en la lista de excepciones
                            if ((!is_null($this->skipDirsIntegrity)) && (in_array($file, $this->skipDirsIntegrity))) {
                                $safe_integrity = (int) 2;
                            } else 
                            {
                                 // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones, pero no a sus subdirectorios
                                if (!is_null($this->skipDirsIntegrity)) {
                                    $i = 0;
                                    foreach ($this->skipDirsIntegrity as $excep)
                                    {
                                        // Search for file position in the exception string
										$string_pos = strpos($file, $excep);										
										if ($string_pos !== false) {
											// File is into the exception											
											$length = strlen($excep);
																						
											// Search for new / char. If exists then the file is into a subfolder
											$next_directory_separator = strpos($file, DIRECTORY_SEPARATOR, $string_pos+$length+1);
																						
											// File is not included into a subdirectory
											if ($next_directory_separator === false) {
												$safe_integrity = (int) 2;
												$i++;
											} 
										}	
                                    }
                                }
                            }
                        }
                        
                        if (!in_array($file, $array_rutas_anterior)) {
                            $texto_notes = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_NEW_FILE');
                            $new_file = (int) 1;
                            if ($safe_integrity != 2) {  // El archivo es nuevo y no está en la lista de excepciones
								$safe_integrity = 0;    
								$this->write_log("FILE: " . $file . " -- New file");
									
								switch ($hash_alg_db)
								{
								case "SHA1":
									$hash_actual = sha1_file($file);
									break;
								case "MD5":
									$hash_actual = md5_file($file);
									break;
								}
								
                            } else
                            {
                                $texto_notes = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_IN_EXCEPTIONS_LIST');
								$hash_actual = "Not calculated";
								$this->write_log("FILE: " . $file . " -- In exceptions list");
                            }   
							$last_part = explode(DIRECTORY_SEPARATOR, $file);
							if (!empty($file)) {
								$files = array(
									'path'      => $file,                            
									'hash' => $hash_actual,                            
									'notes' => $texto_notes,
									'new_file' => $new_file,
									'safe_integrity' => $safe_integrity
								);                                                    
								$this->Stack_Integrity[] = $files;
							}
                        } else 
                        {    // El archivo existe pero su valor hash ha cambiado       
							$this->write_log("FILE: " . $file . " -- Hash changed");
                            $texto_notes = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_HASH_CHANGED');
                            $new_file = (int) 0;
                        
                            switch ($hash_alg_db)
                            {
                            case "SHA1":
                                $hash_actual = sha1_file($file);
                                break;
                            case "MD5":
                                $hash_actual = md5_file($file);
                                break;
                            }
                        
                            // Si el valor del hash actual está vacío, no modificamos el valor del campo 'safe_integrity' 
                            if (!empty($hash_actual)) {
                                $safe_integrity = (int) 0;
                            }
                        
                            // Buscamos el elemento en el array...
                            $indice = array_search($file, $array_rutas_anterior);
                                            
                            // ... y modificamos los valores
                            $this->Stack_Integrity[$indice]['size'] = filesize($file);
                            $this->Stack_Integrity[$indice]['last_modified'] = date('Y-m-d H:i:s', filemtime($file));
                            $this->Stack_Integrity[$indice]['notes'] = $texto_notes;
                            $this->Stack_Integrity[$indice]['new_file'] = $new_file;
                        
                            if (!empty($hash_actual)) {
                                $this->Stack_Integrity[$indice]['hash'] = $hash_actual;
                                $this->Stack_Integrity[$indice]['safe_integrity'] = $safe_integrity;
                            }
                        
                        }
                    } catch (Exception $e)
                    {
                         $this->write_log("EXCEPTION CAUGHT!!!: " . $e->getMessage() . " " . $file, "ERROR");                
                    }
                } 
            } 
        
            if (empty($stack)) {
                // Actualizamos el número de archivos con integridad incorrecta
                $this->files_with_incorrect_integrity = count(
                    array_filter(
                        $files, function ($element) {
                            return (($element['safe_integrity'] == 0));
                        }
                    )
                );
            
                $this->Stack_Integrity = array_replace_recursive($this->Stack_Integrity, $files);            
            } else 
            {
                /* Eliminamos los elementos que estaban establecidos como excepciones pero ya no. Debemos excluir la ruta donde hacemos los escaneos, puesto que el archivo de un escaneo posterior no figurará en las excepciones actuales porque se borra y se crea con otro nombre en cada escaneo */
                $array_excepciones_anterior = array_keys(
                    array_filter(
                        $this->Stack_Integrity, function ($element) {
                            return ((($element['safe_integrity'] == 2) && (!(strstr($element['path'], 'com_securitycheckpro/scans')))));
                        }
                    )
                );
                $diff = array_diff($array_excepciones_anterior, $array_excepciones_actual);
                foreach($diff as $indice_excepcion_anterior)
                {
                    $this->Stack_Integrity[$indice_excepcion_anterior]['safe_integrity'] = 1;
                    $this->Stack_Integrity[$indice_excepcion_anterior]['notes'] = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_OK');
                }
            
                // Actualizamos los elementos que pertenecen a la lista de excepciones
                foreach($array_excepciones_actual as $indice_excepcion)
                {
                    $this->Stack_Integrity[$indice_excepcion]['safe_integrity'] = 2;
                    $this->Stack_Integrity[$indice_excepcion]['notes'] = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_IN_EXCEPTIONS_LIST');
                }
            
                // Actualizamos el número de archivos con integridad incorrecta
                $this->files_with_incorrect_integrity = count(
                    array_filter(
                        $this->Stack_Integrity, function ($element) {
                            return (($element['safe_integrity'] == 0));
                        }
                    )
                );
            
            }
        } else if (($opcion == "malwarescan") || ($opcion == "malwarescan_modified")) {        
            // Inicializamos las variables
            $exceptions = $this->skipDirsIntegrity;
            // Establecemos la ruta donde está la cuarentena
            $quarantine_folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR.'quarantine';
        
            $this->files_scanned_malwarescan += count($files_name);
        
            // Extensiones de ficheros que serán analizadas
            // Eliminamos los espacios en blanco
            $this->fileExt = str_replace(' ', '', $this->fileExt);
            $ext = explode(',', $this->fileExt);
        
            // ¿Qué excepciones hemos de mirar? Por defecto; las de File integrity. 
            if (!$this->use_filemanager_exceptions) {
                     $exceptions = $this->skipDirsMalwarescan;
            } 
        
            // Consultamos la antigüedad de los archivos sobre los que buscar patrones sospechosos
            $params = JComponentHelper::getParams('com_securitycheckpro');
            $timeline = $params->get('timeline', 7);
            $files = array();
        
            // Establecemos la ruta donde se almacenan los escaneos
            $this->folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
            $malwarescan_name = "";
            $stack = "";
            $filtered_array = array();
        
            // Obtenemos el nombre de los escaneos anteriores
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                ->select(array($db->quoteName('storage_value')))
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('malwarescan_resume'));
            $db->setQuery($query);
            $stack_malwarescan = $db->loadResult();    
            $stack_malwarescan = json_decode($stack_malwarescan, true);
        
            if(!empty($stack_malwarescan)) {
                $malwarescan_name = $stack_malwarescan['filename'];
            }
        
            if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name)) {
                $stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name);            
                // Eliminamos la parte del fichero que evita su lectura al acceder directamente
                $stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
            }
            $stack = json_decode($stack, true);
        
            if (!empty($stack)) {
                // Extraemos la info de los ficheros que se han movido a cuarentena para añadirlos al nuevo fichero
                $filtered_array = array_values(
                    array_filter(
                        $stack['files_folders'], function ($element) {
                            return (($element['moved_to_quarantine'] == 1));
                        }
                    )
                );    
            }
			
			        
            // Añadimos los ficheros almacenados en la carpeta 'quarantine' al array de resultados
            if (!empty($filtered_array)) {
                $this->Stack = array_merge($this->Stack, $filtered_array);
            }
        
            if (!empty($files_name)) {
                try
                {
                    foreach($files_name as $file)
                    {
							// Removed in 3.4.6 version to avoid storing rare filenames
							//$file = utf8_encode($file);                    
                            $this->write_log("FILE: " . $file);
                    
                             /* Dejamos sin efecto el tiempo mximo de ejecucin del script. Esto es necesario cuando existen miles de archivos a escanear */
                             set_time_limit(0);
                             $this->files_processed_malwaresecan++;
                             $percent_malwarescan = intval(round(($this->files_processed_malwaresecan / $this->files_scanned_malwarescan) * 100));
                        if ((($percent_malwarescan - $this->last_percent_malwarescan) >= 10) && ($percent_malwarescan < 100)) {
                            $this->set_campo_filemanager("files_scanned_malwarescan", $percent_malwarescan);
                            $this->last_percent_malwarescan = $percent_malwarescan;
                        } else if ($percent_malwarescan == 100) {
                            $this->task_completed = true;
                        }
                    
                           // Inicializamos las variables
                           $safe_malwarescan = 1;
                           $malware_type = '';
                           $malware_description = '';
                           $malware_code = '';
                           $malware_alert_level = '';
                    
                           // Chequeamos si el archivo está incluido en las excepciones
                        if ($recursive == 1) {  // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones
                            if (!is_null($exceptions)) {
                                         $i = 0;
                                foreach ($exceptions as $excep)
                                         {
                                    if (strstr($file . DIRECTORY_SEPARATOR, $excep . DIRECTORY_SEPARATOR)) {  // Añadimos una barra invertida a la comparación por si la excepción es un directorio
								
                                               $safe_malwarescan = (int) 2;
                                    }
                                    $i++;
                                }
                            
                            }
                        } else 
                        {  // Comprobamos que si el archivo está explícitamente en la lista de excepciones
                            if ((!is_null($exceptions)) && (in_array($file, $exceptions))) {
								
                                $safe_malwarescan = (int) 2;
                            } else
                            {
                                // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones
                                if (!is_null($exceptions)) {
                                    $i = 0;
                                    foreach ($exceptions as $excep)
                                    {
                                        if (strstr($file . DIRECTORY_SEPARATOR, $excep . DIRECTORY_SEPARATOR)) {  // Añadimos una barra invertida a la comparación por si la excepción es un directorio
											
                                            $safe_malwarescan = (int) 2;
                                        }
                                        $i++;
                                    }
                                }
                            }
                        }					
						                                    
                        // Días desde que el fichero fue modificado
                        $days_since_last_mod = intval(abs((filemtime($file) - time())/86400));
                        // Si el fichero no está en la lista de excepciones, comprobamos si contiene malware
                        if (($safe_malwarescan != 2) && ($days_since_last_mod <= $timeline)) {
                    
                            // Buscamos la verdadera extensión del fichero (esto es, buscamos archivos tipo .php.xxx o .php.xxx.yyy)
                            $explodedName = explode('.', $file);
                            array_reverse($explodedName);
                                        
                            // Array que contiene todas las extensiones de ficheros de imagen
                            $imageExtensions = array("gif","jpeg","png","swf","psd","bmp","tiff","jpc","jp2","jpx","jb2","swc","iff","wbmp","xbm","ico","webp");
                            // Esta variable la inicializamos a true e indicará si el fichero, que tiene extensión de imagen, realmente lo es
                            $is_image = true;
                        
                            if ((array_key_exists(1, $explodedName)) && (in_array(strtolower($explodedName[1]), $imageExtensions))) {
                                // Chequeamos si el fichero es una imagen o no utilizando la función 'exif_imagetype', que devolverá un entero en caso afirmativo
                                if (function_exists("exif_imagetype")) {
									$is_image = is_int(exif_imagetype($file));
									if ($is_image) {
										$this->write_log("FILE: " . $file . " is an image file");
									}									
								} 
								                        
                            }                    
                                            
                            if ((count($explodedName) > 3) && (strtolower($explodedName[1]) == 'php')) {  // Archivo tipo .php.xxx.yyy
                                /* Cargamos el lenguaje del sitio */
                                $lang = JFactory::getLanguage();
                                $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
                            
                                $safe_malwarescan = 0;
                                $malware_type = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_MULTIPLE_EXTENSIONS');
                                $malware_description = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_EXTENSION') . $explodedName[2] . "." . $explodedName[3] ;
                                $malware_code =  $lang->_('COM_SECURITYCHECKPRO_LINE') . 'Undefined';
                                $malware_alert_level = 0;
                                $this->suspicious_files++;
								$this->write_log("FILE: " . $file . " has multiple extensions");
                            } else if ((count($explodedName) > 2) && (strtolower($explodedName[1]) == 'php')) {  // Archivo tipo .php.xxx
                                /* Cargamos el lenguaje del sitio */
                                $lang = JFactory::getLanguage();
                                $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
                            
                                $safe_malwarescan = 0;
                                $malware_type = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_MULTIPLE_EXTENSIONS');
                                $malware_description = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_EXTENSION') . $explodedName[2];
                                $malware_code =  $lang->_('COM_SECURITYCHECKPRO_LINE') . 'Undefined';
                                $malware_alert_level = 0;
                                $this->suspicious_files++;
								$this->write_log("FILE: " . $file . " has multiple extensions");
                            } else if ((in_array(pathinfo($file, PATHINFO_EXTENSION), $ext) || (!$is_image)) && filesize($file)) {  // Archivo en la lista de extensiones a analizar
                                $resultado = $this->scan_file($file);
                                if ($resultado[0][0]) {  // Se ha encontrado contenido malicioso!
									$this->write_log("FILE: " . $file . " has malicious content");
                                    $safe_malwarescan = 0;
                                    $malware_type = $resultado[0][1];
                                    $malware_description = $resultado[0][2];
                                    $malware_code = $resultado[0][3];
                                    $malware_alert_level = $resultado[0][4];
                                    $this->suspicious_files++;                        
                                }
                            }
                                    
                        }                
                        // Si hemos encontrado algo sospechoso o si el fichero está en la lista de excepciones, guardamos la información en el fichero
                        if ($safe_malwarescan != 1) {
                            // Inicializamos las variables
                            $quarantined_file_name = '';
                            $moved = 0;
                            // Indica si el fichero ha de ser movido a la carpeta de cuarentena. Será falso cuando ya exista un fichero movido desde una ubicación y se intente mover de nuevo
                            $to_move = true;
                        
                            // Ruta original del fichero; la necesitaremos para restaurarlo
                            $original_file = $file;
                        
                            $move_to_quarantine = $params->get('move_to_quarantine', 0);
                            // Hemos de mover los archivos catálogados con un nivel de alerta 'Alto' a la carpeta cuarentena
                            if (($move_to_quarantine == 1) && ($malware_alert_level == '0')) {
                                // Extraemos el nombre del fichero en la ruta de cuarentena
                                $last_part = explode(DIRECTORY_SEPARATOR, $file);
                                $quarantined_file_name = $quarantine_folder_path . DIRECTORY_SEPARATOR . end($last_part);
                                // Si el archivo existe lo renombramos añadiendole un '1'
                                if (file_exists($quarantined_file_name)) {
                                                    $value = array_search($file, array_column($filtered_array, 'path'));
                                    if (is_int($value)) {
                                        // La ruta del archivo ya exista en la carpeta 'quarantine'; en este caso no movemos el archivo para evitar sobreescribirlo.
                                        $to_move = false;
                                    } else 
                                                       {
                                        $quarantined_file_name .= $quarantined_file_name + "1";                                
                                    }                        
                                }
                                if ($to_move) {
                                            $moved = JFile::move($file, $quarantined_file_name);
											$this->write_log("FILE: " . $file . " has been moved to quarantine folder");											
                                            // La información a extraer estará en el archivo de cuarentena
                                            $file = $quarantined_file_name;            
                                            $safe_malwarescan = 3;
                                }
                            }
                        
                            $files[] = array(
                            'path'      => $original_file,
                            'size'      => filesize($file),
                            'last_modified' => date('Y-m-d H:i:s', filemtime($file)),
                            'malware_type' => $malware_type,
                            'malware_description' => $malware_description,
                            'malware_code' => $malware_code,
                            'malware_alert_level'    => $malware_alert_level,
                            'safe_malwarescan' => $safe_malwarescan,
                            'sha1_value' => sha1_file($file),
                            'data_id' => '',
                            'rest_ip' => '',
                            'online_check' => 200,
                            'moved_to_quarantine' => $moved,
                            'quarantined_file_name'    =>    $quarantined_file_name
                            );
                        }                           
                    }
                } catch (Exception $e) 
                {
                    $this->write_log("EXCEPTION CAUGHT!!!: " . $e->getMessage() . " " . $file, "ERROR");                
                }
            }            
            if(!empty($files)) {
                $this->Stack = array_merge($this->Stack, $files);
            }
        }
    }

    /* Función que obtiene el nombre de un fichero de logs */
    public function get_log_filename($opcion,$devolver=false) 
    {
    
        $db = $this->getDbo();
    
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote($opcion));
        $db->setQuery($query);
        $temp_name = $db->loadResult();
        $temp_name = json_decode($temp_name, true);
    
        if ((!empty($temp_name)) && (isset($temp_name['filename']))) {            
            switch ($opcion)
            {
            case "filepermissions_log":
                $this->filepermissions_log_name = $temp_name['filename'];
                if ($devolver) {
                         return $this->filepermissions_log_name;
                }
                break;
            case "fileintegrity_log":
                $this->fileintegrity_log_name = $temp_name['filename'];
                if ($devolver) {
                    return $this->fileintegrity_log_name;
                }
                break;
            case "filemalware_log":
                $this->filemalware_log_name = $temp_name['filename'];
                if ($devolver) {
                        return $this->filemalware_log_name;
                }
                break;
			case "controlcenter_log":
                $this->controlcenter_log_name = $temp_name['filename'];
                if ($devolver) {
                        return $this->controlcenter_log_name;
                }
                break;
            }        
        } 
		return null;
    }

    /* Función que obtiene todos los directorios del sitio */
    public function getDirectories($root, $include_exceptions, $recursive, $opcion)
    {
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
    
        if(empty($root)) { $root = JPATH_ROOT;
        }
		
		// Si por alguna razón el path está vacío ponemos la raíz como base
		if ($root=="") {
			$root = DIRECTORY_SEPARATOR;
		}
    
        jimport('joomla.filesystem.folder');
    
        if ($opcion == "permissions") {
            $folders_name = JFolder::folders($root, '.', true, true, $this->skipDirsPermissions);
        
            if (!is_null($folders_name)) {
				 $this->files_scanned += count($folders_name);
			} else {
				$this->files_scanned = 0;
			}
        
            //Inicializamos el porcentaje de ficheros escaneados
            $this->set_campo_filemanager("files_scanned", 0);
    
            // Actualizamos la BBDD para mostrar información del estado del chequo
            $this->set_campo_filemanager('estado', 'IN_PROGRESS');
        
            $folders = array();
            if (!empty($folders_name)) {
                try 
                  {
                    foreach($folders_name as $folder)
                    {                    
                               $this->files_processed_permissions++;
                               $percent_permissions = intval(round(($this->files_processed_permissions / $this->files_scanned) * 100));
                        if ((($percent_permissions - $this->last_percent_permissions) >= 10) && ($percent_permissions < 100)) {
                            $this->set_campo_filemanager("files_scanned", $percent_permissions);
                            $this->last_percent_permissions = $percent_permissions;
                        } else if ($percent_permissions == 100) {
                            $this->task_completed = true;
                        }
                    
                        $safe = 1;
                        // Chequeamos si el archivo está incluido en las excepciones
                        if ($recursive == 1) {  // Comprobamos si el archivo pertenece a un directorio que está incluido en la lista de excepciones
                            if (!is_null($this->skipDirsPermissions)) {
                                $i = 0;
                                foreach ($this->skipDirsPermissions as $excep)
                                {
                                    if (strstr($folder . DIRECTORY_SEPARATOR, $excep . DIRECTORY_SEPARATOR)) {  // Añadimos una barra invertida a la comparación por si la excepción es un directorio
                                        $safe = (int) 2;
                                    }
                                    $i++;
                                }
                            
                            }
                        } else 
                        {  // Comprobamos que si el archivo está explícitamente en la lista de excepciones
                            if ((!is_null($this->skipDirsPermissions)) && (in_array($folder, $this->skipDirsPermissions))) {
                                $safe = (int) 2;
                            }
                        }
                        // Si el archivo se encuentra entre las excepciones y la opción 'añadir excepciones a la bbdd' está activada guardamos el archivo. 
                        if ((($safe == 2) && ($include_exceptions)) || ($safe!=2)) {
                            $permissions = $this->file_perms($folder);
                            if (($permissions > '0755') && ($safe!=2)) {
                                            $safe = 0;
                                            $this->files_with_incorrect_permissions = $this->files_with_incorrect_permissions+1;
                            }
                                 $last_part = explode(DIRECTORY_SEPARATOR, $folder);
                                 $folders[] = array(
                                 'path'      => $folder,                        
                                 'kind'    => $lang->_('COM_SECURITYCHECKPRO_FILEMANAGER_DIRECTORY'),
                                 'permissions' => $permissions,
                                 'last_modified' => date('Y-m-d H:i:s', filemtime($folder)),
                                 'safe' => $safe
                                 );
                                 $this->write_log("FOLDER: " . $folder);
                        }
                    }
                } catch (Exception $e)
                {
                    $this->write_log("EXCEPTION CAUGHT!!!: " . $e->getMessage() . " " . $file, "ERROR");                
                }
            }
        
            if (!empty($folders)) {
                $this->Stack = array_merge($this->Stack, $folders);
            }
        } else if ($opcion == "integrity") {
            // No hacemos nada porque a los directorios no se les aplican los valores hash
        }
    }

    /* Función que guarda en la BBDD, en formato json, el contenido de un array con todos los ficheros y directorios */
    private function saveStack($opcion, $borrar=true)
    {
        // Inicializamos las variables
        $result_permissions = true;
        $result_permissions_resume = true;
        $result_integrity = true;
        $result_integrity_resume = true;
        $result_malwarescan = true;
        $result_malwarescan_resume = true;
        $array_exentos = array('index.html','web.config','.htaccess',$this->filemanager_name,$this->fileintegrity_name,$this->malwarescan_name);
    
        // Creamos el nuevo objeto query
        $db = $this->getDbo();
        
        if ($borrar) {
            // Extraemos la información de los archivos de escaneos online, que no deberán ser borrados...
            $query = $db->getQuery(true)
                ->select('filename')
                ->from($db->quoteName('#__securitycheckpro_online_checks'));
            $db->setQuery($query);
            $online_scan_filenames = $db->loadRowList();
        
            // ... y la añadimos al array de exentos
            foreach ($online_scan_filenames as $filename)
            {
                  array_push($array_exentos, $filename[0]);            
            }
        
            // Añadimos los ficheros de logs para que no sean borrados
            // Obtenemos el nombre de los ficheros de logs
            $this->get_log_filename("filepermissions_log");
            $this->get_log_filename("fileintegrity_log");
            $this->get_log_filename("filemalware_log");
			$this->get_log_filename("controlcenter_log");
        
            if (!empty($this->filepermissions_log_name)) {
                array_push($array_exentos, $this->filepermissions_log_name);
            }
        
            if (!empty($this->fileintegrity_log_name)) {
                array_push($array_exentos, $this->fileintegrity_log_name);
            }
        
            if (!empty($this->filemalware_log_name)) {
                array_push($array_exentos, $this->filemalware_log_name);
            }
			
			if (!empty($this->controlcenter_log_name)) {
                array_push($array_exentos, $this->controlcenter_log_name);
            }
			
			// Añadimos el fichero de error generado por las tareas del Control center y el que indica que hay que actualizar la bbdd de vulnerabilidades
			array_push($array_exentos, "error.php");
			array_push($array_exentos, "update_vuln_table.php");			
        
            // Buscamos ficheros antiguos que no hayan sido borrados...
            $old_files = JFolder::files($this->folder_path, '.', false, true, $array_exentos);
        
            // ... y los borramos
            foreach($old_files as $old_file)
            {
				try{		
					 JFile::delete($old_file);  
				} catch (Exception $e)
				{
				}                     
            }
        }
		
		$timestamp = $this->global_model->get_Joomla_timestamp();
    
        if ($opcion == "permissions") {        
            // Borramos el fichero del escaneo anterior...
            if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name)) {
				try{		
					 $delete_permissions_file = JFile::delete($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name);  
				} catch (Exception $e)
				{
				}                  
            }
        
            // ... y escribimos el contenido del array a un nuevo fichero
            $filename = $this->generateKey();
            try
            {
                $content_permissions = utf8_encode(json_encode(array('files_folders'    => $this->Stack)));
                $content_permissions = "#<?php die('Forbidden.'); ?>" . PHP_EOL . $content_permissions;
                $result_permissions = JFile::write($this->folder_path.DIRECTORY_SEPARATOR.$filename, $content_permissions);        
                // Nos aseguramos que los permisos de la carpeta 'scans' son los correctos
                chmod($this->folder_path, 0755);            
            } catch (Exception $e)
            {    
                $this->set_campo_filemanager('estado', 'DATABASE_ERROR');
                $result_permissions = false;
            }
        
            // Vamos a limpiar las variables que no necesitamos. No uso unset() porque así no necesitamos esperar al garbage collector
            $content_permissions = null;
            $this->Stack = null;
        
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
            $db->setQuery($query);
            $db->execute();
			        
            $object = (object)array(
            'storage_key'    => 'filemanager_resume',
            'storage_value'    => utf8_encode(
                json_encode(
                    array(
                    'files_scanned'        => $this->files_scanned,
                    'files_with_incorrect_permissions'    => $this->files_with_incorrect_permissions,
                    'last_check'    => $timestamp,
                    'filename'        => $filename
                    )
                )
            )
            );
        
            try 
            {
                $result_permissions_resume = $db->insertObject('#__securitycheckpro_storage', $object);
            } catch (Exception $e)
            {        
                $this->set_campo_filemanager('estado', 'DATABASE_ERROR');
                $result_permissions_resume = false;
            }
                
            if (($this->task_completed == true) && ($result_permissions) && ($result_permissions_resume)) {
                $this->set_campo_filemanager('estado', 'ENDED');
            }
            $this->set_campo_filemanager("files_scanned", 100);
        
        } else if ($opcion == "integrity") {
            // Borramos el fichero del escaneo anterior...
            if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name)) {
				try{		
					  $delete_integrity_file = JFile::delete($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
				} catch (Exception $e)
				{
				}     
               
            }
        
            // ... y escribimos el contenido del array a un nuevo fichero
            $filename = $this->generateKey();
        
            try
            {
                $content_integrity = utf8_encode(json_encode(array('files_folders'    => $this->Stack_Integrity)));            
                $content_integrity = "#<?php die('Forbidden.'); ?>" . PHP_EOL . $content_integrity;
                $result_integrity = JFile::write($this->folder_path.DIRECTORY_SEPARATOR.$filename, $content_integrity);    
                // Nos aseguramos que los permisos de la carpeta 'scans' son los correctos
                chmod($this->folder_path, 0755);
            } catch (Exception $e)
            {    
                $this->set_campo_filemanager('estado_integrity', 'DATABASE_ERROR');
                $result_integrity = 
                false;
            }
            // Vamos a limpiar las variables que no necesitamos. No uso unset() porque así no necesitamos esperar al garbage collector
            $content_integrity = null;
            $this->Stack_Integrity = null;
        
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
            $db->setQuery($query);
            $db->execute();
			        
            $object = (object)array(
            'storage_key'    => 'fileintegrity_resume',
            'storage_value'    => utf8_encode(
                json_encode(
                    array(
                    'files_scanned_integrity'        => $this->files_scanned_integrity,
                    'files_with_incorrect_integrity'    => $this->files_with_incorrect_integrity,
                    'last_check_integrity'    => $timestamp,
                    'filename'        => $filename
                    )
                )
            )
            );
        
            try 
            {
                $result_integrity_resume = $db->insertObject('#__securitycheckpro_storage', $object);
            } catch (Exception $e)
            {    
                $this->set_campo_filemanager('estado_integrity', 'DATABASE_ERROR');
                $result_integrity_resume = false;
            }
        
            if (($this->task_completed == true) && ($result_integrity) && ($result_integrity_resume)) {
                $this->set_campo_filemanager('estado_integrity', 'ENDED');
            }
            $this->set_campo_filemanager("files_scanned_integrity", 100);
                
        } else if (($opcion == "malwarescan") || ($opcion == "malwarescan_modified")) {
            // Borramos el fichero del escaneo anterior...
            if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name)) {
				try{		
					$delete_malwarescan_file = JFile::delete($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name);
				} catch (Exception $e)
				{
				}                   
            }
        
            // ... y escribimos el contenido del array a un nuevo fichero
            $filename = $this->generateKey();
        
            try 
            {
                $content_malwarescan = utf8_encode(json_encode(array('files_folders'    => $this->Stack)));
                $content_malwarescan = "#<?php die('Forbidden.'); ?>" . PHP_EOL . $content_malwarescan;
                $result_malwarescan = JFile::write($this->folder_path.DIRECTORY_SEPARATOR.$filename, $content_malwarescan);
                // Nos aseguramos que los permisos de la carpeta 'scans' son los correctos
                chmod($this->folder_path, 0755);
            } catch (Exception $e)
            {    
                $this->set_campo_filemanager('estado', 'DATABASE_ERROR');
                $result_malwarescan = false;
            }
        
            // Vamos a limpiar las variables que no necesitamos. No uso unset() porque así no necesitamos esperar al garbage collector
            $content_malwarescan = null;
            $this->Stack = null;
        
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('malwarescan_resume'));
            $db->setQuery($query);
            $db->execute();
			        
            $object = (object)array(
            'storage_key'    => 'malwarescan_resume',
            'storage_value'    => utf8_encode(
                json_encode(
                    array(
                    'files_scanned_malwarescan'        => $this->files_scanned_malwarescan,
                    'suspicious_files'    => $this->suspicious_files,
                    'last_check_malwarescan'    => $timestamp,
                    'filename'        => $filename
                    )
                )
            )
            );
        
            try
            {
                $result_malwarescan_resume = $db->insertObject('#__securitycheckpro_storage', $object);
            } catch (Exception $e)
            {        
                $this->set_campo_filemanager('estado_malwarescan', 'DATABASE_ERROR');
                $result_malwarescan_resume = false;
            }
                
            if (($this->task_completed == true) && ($result_malwarescan) && ($result_malwarescan_resume)) {
                $this->set_campo_filemanager('estado_malwarescan', 'ENDED');
            }
            $this->set_campo_filemanager("files_scanned_malwarescan", 100);
                
        }

    }

    /* Función que obtiene un array con los datos que serán mostrados en la opción 'file manager' */
    function loadStack($opcion,$field,$showall=false)
    {
        $db = $this->getDbo();
        $stack = null;
    
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
			if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name)) {
				// Leemos el contenido del fichero
				$stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name);
				// Eliminamos la parte del fichero que evita su lectura al acceder directamente
				$stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
			}
                        
            if (empty($stack)) {
                $this->Stack = array();
                return;
            }
            break;
        case "integrity":
            // Leemos el contenido del fichero
            if (!file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name)) {
                $query = $db->getQuery(true)
                    ->select(array($db->quoteName('storage_value')))
                    ->from($db->quoteName('#__securitycheckpro_storage'))
                    ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
                $db->setQuery($query);
                $stack_integrity = $db->loadResult();
                $stack_integrity = json_decode($stack_integrity, true);
    
                if ((!empty($stack_integrity)) && (isset($stack_integrity['filename']))) {            
                    $this->fileintegrity_name = $stack_integrity['filename'];
                }
            }
            
			if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name)) {
				$stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
				// Eliminamos la parte del fichero que evita su lectura al acceder directamente
				$stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
			}
                        
            if (empty($stack)) {
                $this->Stack_Integrity = array();
                return;
            }
            break;
        case "malwarescan":
            // Leemos el contenido del fichero            
            if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name)) {
                $stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name);
                // Eliminamos la parte del fichero que evita su lectura al acceder directamente
                $stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
            }
            
            if (empty($stack)) {
                $this->Stack_Malwarescan = array();
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
                $this->files_scanned_integrity = 0;
                $this->files_with_incorrect_integrity = 0;
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
		
    
        /* Obtenemos el número de registros del array que hemos de mostrar. Si el límite superior es '0', entonces devolvemos todo el array */
        $upper_limit = $this->getState('limitstart');
        $lower_limit = $this->getState('limit');
    
        switch ($field)
        {
        case "file_manager":
            /* Obtenemos los valores de los filtros */
            $filter_permissions_status = $this->state->get('filter.filemanager_permissions_status');
            $filter_kind = $this->state->get('filter.filemanager_kind');
            $search = htmlentities($this->state->get('filter.filemanager_search'));
            
            if (!is_null($stack['files_folders'])) {
                $filtered_array = array();
                /* Si el campo 'search' no está vacío, buscamos en todos los campos del array */            
                if (!empty($search)) {
                    $filtered_array = array_values(
                        array_filter(
                            $stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind,$search) {
                                return (($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind) && ((strstr($element['path'], $search)) || (strstr($element['last_modified'], $search)) || (strstr($element['permissions'], $search))));
                            }
                        )
                    );
                } else 
                {
                    $filtered_array = array_values(
                        array_filter(
                            $stack['files_folders'], function ($element) use ($filter_permissions_status,$filter_kind) {
                                return (($element['safe'] == $filter_permissions_status) && ($element['kind'] == $filter_kind));
                            }
                        )
                    );                
                }
                
                $this->total = count($filtered_array);            
                /* Cortamos el array para mostrar sólo los valores mostrados por la paginación */
                $this->Stack = array_splice($filtered_array, $upper_limit, $lower_limit);
                return ($this->Stack);
            }
        case "file_integrity":
            /* Obtenemos los valores de los filtros */
            $filter_fileintegrity_status = $this->state->get('filter.fileintegrity_status');
            $search = htmlentities($this->state->get('filter.fileintegrity_search'));
            
            if ( (!is_null($stack)) && (array_key_exists('files_folders',$stack)) ) {
                $filtered_array = array();
                /* Si el campo 'search' no está vacío, buscamos en todos los campos del array */            
                if (!empty($search)) {
                    $filtered_array = array_values(
                        array_filter(
                            $stack['files_folders'], function ($element) use ($filter_fileintegrity_status,$search) {
                                return (($element['safe_integrity'] == $filter_fileintegrity_status) && ((strstr($element['path'], $search)) || (strstr($element['hash'], $search)) || (strstr($element['notes'], $search))));
                            }
                        )
                    );
                } else
                {
                    $filtered_array = array_values(
                        array_filter(
                            $stack['files_folders'], function ($element) use ($filter_fileintegrity_status) {
                                return (($element['safe_integrity'] == $filter_fileintegrity_status));
                            }
                        )
                    );
                }
                $this->total = count($filtered_array);
                /* Cortamos el array para mostrar sólo los valores mostrados por la paginación */
                $this->Stack_Integrity = array_splice($filtered_array, $upper_limit, $lower_limit);
                return ($this->Stack_Integrity);
            }
        case "malwarescan":
            /* Obtenemos los valores de los filtros */
            $filter_malwarescan_status = $this->state->get('filter.malwarescan_status');
				
            $search = htmlentities($this->state->get('filter.malwarescan_search'));
            if ( (!is_null($stack)) && (array_key_exists('files_folders',$stack)) ) {        
                $filtered_array = array();
                /* Si el campo 'search' no está vacío, buscamos en todos los campos del array */            
                if (!empty($search)) {
                    $filtered_array = array_values(
                        array_filter(
                            $stack['files_folders'], function ($element) use ($filter_malwarescan_status,$search) {
                                return (($element['safe_malwarescan'] == $filter_malwarescan_status) && ((strstr($element['path'], $search)) || (strstr($element['size'], $search)) || (strstr($element['last_modified'], $search)) || (strstr($element['malware_type'], $search)) || (strstr($element['malware_description'], $search))));
                            }
                        )
                    );
                } else
                {                
                    $filtered_array = array_values(
                        array_filter(
                            $stack['files_folders'], function ($element) use ($filter_malwarescan_status) {
                                return (($element['safe_malwarescan'] == $filter_malwarescan_status));
                            }
                        )
                    );                
                }            
                // Ordenamos el array según el nivel de alerta
                $orderer_filtered_array = array();
                foreach ($filtered_array as $key => $row)
                {
                    $orderer_filtered_array[$key] = $row['malware_alert_level'];                    
                }
                array_multisort($orderer_filtered_array, SORT_ASC, $filtered_array);
                    
                $this->total = count($filtered_array);
                
                /* Cortamos el array para mostrar sólo los valores mostrados por la paginación, excepto si el campo showall es true. Esto es necesario para que funcione correctamente el escaneo contra Metadefender */
                if ($showall) {
                    $this->Stack_Malwarescan = $filtered_array;    
                } else
                {
                    $this->Stack_Malwarescan = array_splice($filtered_array, $upper_limit, $lower_limit);
                }            
                return ($this->Stack_Malwarescan);
            }
        case "files_scanned":
			if ( (!is_null($stack)) && (array_key_exists('files_scanned',$stack)) ) {
				$this->files_scanned = $stack['files_scanned'];
			} else {
				$this->files_scanned = 0;
			}
            return ($this->files_scanned);
        case "files_with_incorrect_permissions":
            if (empty($stack)) {
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
            if (empty($stack)) {
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
        case "files_with_incorrect_integrity":
            $this->files_with_incorrect_integrity = $stack['files_with_incorrect_integrity'];
            return ($this->files_with_incorrect_integrity);
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

    /* Función que escanea el sitio para obtener los permisos o la integridad de los archivos y directorios */
    function scan($opcion)
    {

        $include_exceptions = 0;
        $folder_exceptions = 0;
        
        // Obtenemos la ruta sobre la que vamos a hacer el chequeo
        $params = JComponentHelper::getParams('com_securitycheckpro');
				
        $file_check_path = $params->get('file_manager_path', JPATH_ROOT);
    
        if (($file_check_path == "JPATH_ROOT") || ($file_check_path == JPATH_ROOT)) {
            $file_check_path = JPATH_ROOT;
        } else 
        {
            $file_check_path = JPATH_ROOT . DIRECTORY_SEPARATOR . $file_check_path;
        }
    
        switch ($opcion)
        {
        case "permissions":            
            $this->files_processed_permissions = 0;
            // Obtenemos si debemos guardar las excepciones
            $include_exceptions = $params->get('file_manager_include_exceptions_in_database', 1);    
            // ¿El escaneo ha de ser recursivo?
            $folder_exceptions = $params->get('file_manager_recursive_folder_exceptions', 1);
            break;
        case "integrity":
            // Obtenemos si debemos guardar las excepciones
            $include_exceptions = $params->get('file_manager_include_exceptions_in_database', 1);    
            // ¿El escaneo ha de ser recursivo?
            $folder_exceptions = $params->get('file_manager_recursive_folder_exceptions', 1);
            break;
        case "malwarescan":
            // Obtenemos si debemos guardar las excepciones
            $include_exceptions = $params->get('file_manager_include_exceptions_in_database', 1);  
            // ¿El escaneo ha de ser recursivo?
            $folder_exceptions = $params->get('file_manager_recursive_folder_exceptions', 1);
            break;
        case "malwarescan_modified":
            // Obtenemos si debemos guardar las excepciones
            $include_exceptions = $params->get('file_manager_include_exceptions_in_database', 1);    
            // El escaneo ha de ser recursivo?
            $folder_exceptions = $params->get('file_manager_recursive_folder_exceptions', 1);
            break;
        }
    
        $this->prepareLog($opcion);
        $this->write_log("------- Begin scan: " . strtoupper($opcion) . " --------");
    
        $this->getDirectories($file_check_path, $include_exceptions, $folder_exceptions, $opcion);
        $this->getFiles($file_check_path, $include_exceptions, $folder_exceptions, $opcion);
        $this->saveStack($opcion);
    
		$this->write_log("------- End scan: " . strtoupper($opcion) . " --------");
    }

    /* Función para establecer el valor de un campo de la tabla '#_securitycheckpro_file_manager' */
    function set_campo_filemanager($campo,$valor)
    {
        // Creamos el nuevo objeto query
        $db = $this->getDbo();
        $query = $db->getQuery(true);
    
        $campo = filter_var($campo, FILTER_SANITIZE_STRING);
        $valor = filter_var($valor, FILTER_SANITIZE_STRING);
    
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
    
        // Devolvemos el resultado
        return $result;    
    }

    /* Obtiene los permisos de un archivo o directorio en formato octal */
    function file_perms($file)
    {
        // Obtenemos el tipo de servidor web
        $mainframe = JFactory::getApplication();
        $server = $mainframe->getUserState("server", 'apache');
    
        // Si el servidor es un IIS, devolvemos que los permisos son correctos.
        if (strstr($server, "iis")) {
            return "0644";
        }
        return substr(sprintf('%o', fileperms($file)), -4);

    }

    /* Destruye y crea la tabla '#__securitycheckpro_file_permissions' */
    function initialize_database()
    {
        // Creamos el nuevo objeto query
        $db = $this->getDbo();
    
        // Borramos la tabla...
        $query = 'DROP TABLE IF EXISTS #__securitycheckpro_file_permissions';
        $db->setQuery($query);
        $db->execute();

        // Actualizamos los campos de la tabla '#__securitycheckpro_file_manager'
		$query = "UPDATE #__securitycheckpro_file_manager SET last_check=null,last_check_integrity=null,last_check_malwarescan=null,files_scanned=0,files_scanned_integrity=0,files_with_incorrect_permissions=0,files_scanned_malwarescan=0,files_with_bad_integrity=0,suspicious_files=0,estado='ENDED',estado_integrity='ENDED',estado_malwarescan='ENDED',cron_tasks_launched=0 where id=1";
        $db->setQuery($query);
        $db->execute();
    
        // Obtenemos el nombre de los escaneos anteriores...
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('filemanager_resume'));
        $db->setQuery($query);
        $stack = $db->loadResult();
        $stack = json_decode($stack, true);
    
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
        $db->setQuery($query);
        $stack_integrity = $db->loadResult();
        $stack_integrity = json_decode($stack_integrity, true);
    
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('malwarescan_resume'));
        $db->setQuery($query);
        $stack_malwarescan = $db->loadResult();
        $stack_malwarescan = json_decode($stack_malwarescan, true);
    
        if (!empty($stack)) {
            $this->filemanager_name = $stack['filename'];
        }
    
        if (!empty($stack_integrity)) {
            $this->fileintegrity_name = $stack_integrity['filename'];
        }
    
        if(!empty($stack_malwarescan)) {
            $this->malwarescan_name = $stack_malwarescan['filename'];
        }
    
    
        // ... y borramos los ficheros
		try{		
			$delete_permissions_file = JFile::delete($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name);
			$delete_integrity_file = JFile::delete($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
			$delete_malwarescan_file = JFile::delete($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name);
		} catch (Exception $e)
		{
		}       
    
        // Nos aseguramos que los permisos de la carpeta 'scans' son los correctos
        chmod($this->folder_path, 0755);
    
        // Inicializamos la tabla  '#__securitycheckpro_storage'
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__securitycheckpro_storage'))
            ->where('(' .$db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume') .') OR (' .$db->quoteName('storage_key').' = '.$db->quote('filemanager_resume') .') OR (' .$db->quoteName('storage_key').' = '.$db->quote('malwarescan_resume') .')');
        $db->setQuery($query);
        $db->execute();
    }

    /* Función para grabar los logs de la propia aplicación*/
    function grabar_log_propio($description)
    {

        $db = JFactory::getDBO();
    
        // Sanitizamos la entrada
        $description = filter_var($description, FILTER_SANITIZE_STRING);
        $description = $db->getEscaped($description);
        
        $sql = "INSERT INTO #__securitycheckpro_own_logs (time, description) VALUES (now(), '{$description}')";
        $db->setQuery($sql);
        $db->execute();
        
    }

    /* Obtiene la diferencia en horas entre dos tareas */
    function get_timediff($opcion)
    {
        (int) $interval = 0;
    
        switch ($opcion) 
        {
        case "integrity":
            $last_check_integrity_start_time = $this->get_campo_filemanager('last_check_integrity');
            $now = $this->global_model->get_Joomla_timestamp();
            $seconds = strtotime($now) - strtotime($last_check_integrity_start_time);
			$days = intval($seconds/86400);
			$hours = intval($seconds/3600);
            // Extraemos el número total de días entre las dos fechas. Si es cero, no ha transcurrido ningún día, por lo que devolvemos la diferencia de horas. Si ha transcurrido un día o más, devolvemos un valor suficientemente alto para activar los disparadores necesarios
            if ($days == 0) {
                // Extraemos el número total de horas que han pasado desde el último chequeo
                $interval = $hours;
            } else
            {
                $interval = 20000;
            }    
            break;
        case "permissions":
			$last_check_start_time = $this->get_campo_filemanager('last_check');
            $now = $this->global_model->get_Joomla_timestamp();
            $seconds = strtotime($now) - strtotime($last_check_start_time);
			$days = intval($seconds/86400);
			$hours = intval($seconds/3600);            
            // Extraemos el número total de días entre las dos fechas. Si es cero, no ha transcurrido ningún día, por lo que devolvemos la diferencia de horas. Si ha transcurrido un día o más, devolvemos un valor suficientemente alto para activar los disparadores necesarios
            if ($days == 0) {
                // Extraemos el número total de horas que han pasado desde el último chequeo
                $interval = $hours;
            } else
            {
                $interval = 20000;
            }    
            break;
        case "malwarescan":
			$last_check_malwarescan_start_time = $this->get_campo_filemanager('last_check_malwarescan');
            $now = $this->global_model->get_Joomla_timestamp();
            $seconds = strtotime($now) - strtotime($last_check_malwarescan_start_time);
			$days = intval($seconds/86400);
			$hours = intval($seconds/3600);
			
            // Extraemos el número total de días entre las dos fechas. Si es cero, no ha transcurrido ningún día, por lo que devolvemos la diferencia de horas. Si ha transcurrido un día o más, devolvemos un valor suficientemente alto para activar los disparadores necesarios
            if ($days == 0) {
                // Extraemos el número total de horas que han pasado desde el último chequeo
                $interval = $hours;
            } else 
            {
                $interval = 20000;
            }    
            break;
        }
        return $interval;
    }

    /*Genera un nombre de fichero .php  de 20 caracteres */
    function generateKey()
    {
    
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"; //available characters
        srand((double) microtime() * 1000000); //random seed
        $pass = '' ;
        
        for ($i = 1; $i <= 20; $i++)
        {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
        }

        return $pass.'.php';    
    }

    /* Función que chequea si estamos en IIS */
    function on_iis()
    {
        $sSoftware = strtolower($_SERVER["SERVER_SOFTWARE"]);
        if (strpos($sSoftware, "microsoft-iis") !== false ) {
            return true;
        } else {
            return false;
        }
    }

    /* Función que chequea si hay código inyectado al principio de un archivo */
    function code_at_start($content,$path)
    {
        // Check if there is allowed content between 'php' string and '/*' string (for instance, namespace administrator\components\com_gdpr\controllers;
		$allowed_content = false;
        $ini = strpos($content, "<?php");
        $end = strpos($content, "/*");
		$allowed_content_pos = strpos($content, "namespace");
		
		$length = strlen($content);
        $number_of_spaces = substr_count($content, ' ', 0, $end-$ini);
        $number_of_new_lines = substr_count($content, PHP_EOL, 0, $end-$ini);
		
		if ( ($allowed_content_pos !== false) && ($allowed_content_pos < $end) )
		{
			$allowed_content = true;
		}
    
        // Check if we are on IIS. For some reason PHP_EOL doesn't return the number of new lines...
        $iis = $this->on_iis();    
    
        if (($ini !== false) && ($end !== false) && ($number_of_new_lines < 3) && ($end-$ini > 50) && (!$allowed_content) && (!$iis) ) {
            return true;
        }
        return false;
    }

    /**
     Scan given file for all malware patterns
    
     Based on the JAMSS - Joomla! Anti-Malware Scan Script
     *
     @version 1.0.7
    
     @author Bernard Toplak [WarpMax] <bernard@orion-web.hr>
     @link   http://www.orion-web.hr
    
     @global string $fileExt file extension list to be scanned
     @global array $patterns array of patterns to search for
     @param  string $path path of the scanned file
     */
    private function scan_file($path)
    {

        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);

        // Aadimos los strings sospechosos a la bsqueda de malware?
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $deep_scan = $params->get('deep_scan', 0);
        if ($deep_scan) {

            // Cargamos los strings que se buscan como malware desde el fichero de strings
            if(@file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'Malware_strings.dat')) {
                // Leemos el contenido del fichero, que estará en formato base64
                $Suspicious_Strings = @file_get_contents(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'Malware_strings.dat');

                // Lo decodificamos
                $Suspicious_Strings = base64_decode($Suspicious_Strings);        
            } 
        } 

        // Cargamos los patrones que se buscarán como malware desde el fichero de patrones
        if(@file_exists(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'Malware_patterns.dat')) {

            // Leemos el contenido del fichero, que estará en formato base64
            $malware_patterns = @file_get_contents(JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'Malware_patterns.dat');    
    
            // Lo decodificamos
            $malware_patterns = base64_decode($malware_patterns);
    
            // Creamos un array bidimensional con el contenido del fichero leído
            $Suspicious_Patterns  = array_map(
                function ($_) {
                    return explode('~', $_);
                },
                explode('¡', $malware_patterns)
            );    
    
        }


        $jamssFileNames = array(
        $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_OFC_UPLOAD_IMAGE')
        => 'ofc_upload_image.php',
        $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_R57')
        => 'r57.php',
        $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_PHPINFO')
        => 'phpinfo.php',
        );

        /* * * * * Patterns End * * * * */

        // Extensiones de ficheros que serán analizadas
        // Eliminamos los espacios en blanco
        $this->fileExt = str_replace(' ', '', $this->fileExt);
        $ext = explode(',', $this->fileExt);
    
        // Patrones y strings a buscar
        if ($deep_scan) {
            $patterns = array_merge($Suspicious_Patterns, explode('|', $Suspicious_Strings));
        } else
        {
            $patterns = $Suspicious_Patterns;
        }
        
        // Inicializamos las variables
        $resultado = array(array());
        $resultado[0][0] = false;
        $count = 0;
        $total_results = 0;
        $malware_found = false;
    
    
        if ($malic_file_descr = array_search(pathinfo($path, PATHINFO_BASENAME), $jamssFileNames)) {
            $resultado[0][0] = true;
            $resultado[0][1] = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME');
            $resultado[0][2] = $malic_file_descr;
            $resultado[0][3] = '';
            $resultado[0][4] = '0';
              
        } else 
        {
            $content = @file_get_contents($path);
            if (!$content) {
                /*$error = 'Could not check '.$path;
                echo formatError($error);*/
            } else
            { // do a search for fingerprints
                // Look for obfuscated code
                preg_match_all("/\\\x([0-9]{2})/", $content, $found);
                $pattern[1] = "Php obfuscated";
                $pattern[2] = "29";
                $pattern[3] = "Encoded representation of source code, commonly used to hide malware";
            
                $all_results = $found[0]; // remove outer array from results
                $results_count = count($all_results); // count the number of results
                $total_results += $results_count; // total results of all fingerprints                
                                
                if ( (!empty($all_results)) && ($results_count>50) && ( substr_count(strtolower($content), strtolower("global"))) ) {   
				    // Update the variable to stop looking for more malware patterns
                    $malware_found = true;
                    // Let's see if this seems a Joomla file, which usually forbids direct access using the JEXEC feature
                    $content_without_spaces = $this->clean_espaces($content);
                    //if ((!strstr($content_without_spaces,"defined('_JEXEC')ordie")) && (!strstr($content_without_spaces,"defined('JPATH_BASE')ordie"))) {
                    $count++;
                    $resultado[0][0] = true;
                    $resultado[0][1] = $lang->_('COM_SECURITYCHECKPRO_ENCODED_CONTENT');
                    $resultado[0][2] = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN_INFO'), $pattern[2], $pattern[1], $results_count, $pattern[3]);                    
                    $resultado[0][3] = $lang->_('COM_SECURITYCHECKPRO_LINE') . 'Undefined';
                    $resultado[0][4] = '0';                    
                    //}                
                }
            
                // Look for obfuscated code using conversions
                if (!$malware_found) {
                    $info = pathinfo($path);                    
                    if ((array_key_exists('extension', $info)) && ($info['extension'] == 'php')) {
                           $length = strlen($content);
                           $number_of_spaces = substr_count($content, ' ');
                           $number_of_new_lines = substr_count($content, PHP_EOL);
						   // Count the number of apostrophes ('). This is done to avoid false positives in J4 /libraries/vendor/voku/portable-ascii/src/voku/helper/data/
						   $number_of_apostrophes = substr_count($content, "'");
                           // Check if we are on IIS. For some reason PHP_EOL doesn't return the number of new lines...
                           $iis = $this->on_iis();					   
							
                        if (((($number_of_spaces/$length) < 0.001) && (($number_of_spaces/$length) > 0)) || ((($number_of_new_lines/$length) < 0.001) && (($number_of_new_lines/$length) > 0) && (($number_of_apostrophes) < 400)) || (($number_of_new_lines == 0) && (!$iis)) || ($number_of_spaces == 0)) {
                            // Update the variable to stop looking for more malware patterns
                            $malware_found = true;
                            $pattern[1] = "Obfuscated file";
                            $pattern[2] = "30";
                            $pattern[3] = "Encoded representation of source code, commonly used to hide malware";
                            $resultado[0][0] = true;
                            $resultado[0][1] = $lang->_('COM_SECURITYCHECKPRO_ENCODED_CONTENT');
                            $resultado[0][2] = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN_INFO'), $pattern[2], $pattern[1], 'Not applicable', $pattern[3]);
                            $resultado[0][3] = $lang->_('COM_SECURITYCHECKPRO_LINE') . 'Undefined';
                            $resultado[0][4] = '0';    
                        } 
                    }                    
                }
            
                // Look for obfuscated code injected
                if (!$malware_found) {
                    if ((array_key_exists('extension', $info)) && ($info['extension'] == 'php')) {
                        $injected = $this->code_at_start($content, $path);                
                        if ($injected) {
                            $malware_found = true;
                            $count++;
                            $pattern[1] = "Obfuscated content injected";
                            $pattern[2] = "30";
                            $pattern[3] = "Code injected at the beggining of the file";
                            $resultado[0][0] = true;
                            $resultado[0][1] = $lang->_('COM_SECURITYCHECKPRO_ENCODED_CONTENT_INJECTED');
                            $resultado[0][2] = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN_INFO'), $pattern[2], $pattern[1], 'Not applicable', $pattern[3]);
                            $resultado[0][3] = $lang->_('COM_SECURITYCHECKPRO_LINE') . 'Undefined';
                            $resultado[0][4] = '0';                
                        }
                    }
                }
                    
                // The file is not obfuscated
                if (!$malware_found) {        
                    foreach ($patterns As $pattern)
                    {
                        if (!$malware_found) {
                            if (is_array($pattern)) { // it's a pattern                    
                                // RegEx modifiers: i=case-insensitive; s=dot matches also newlines; S=optimization
                                preg_match_all('/' . $pattern[0] . '/sS', $content, $found, PREG_OFFSET_CAPTURE);                                                 
                            } else
                            { // it's a string
                                preg_match_all('/' . $pattern . '/isS', $content, $found, PREG_OFFSET_CAPTURE);
                            }
                        
                            $all_results = $found[0]; // remove outer array from results
                            $results_count = count($all_results); // count the number of results
                            $total_results += $results_count; // total results of all fingerprints    
                                                                                                    
                            if (!empty($all_results)) {    
                                // Update the variable to stop looking for more malware patterns
                                $malware_found = true;
                                // Let's see if this seems a Joomla file, which usually forbids direct access using the JEXEC feature
                                $content_without_spaces = $this->clean_espaces($content);
                                // Check the line of the ocurrence; on modified files it's usuallly the first line
								
                                foreach ($all_results as $match)
                                {
                                        $line = $this->calculate_line_number($match[1], $content);
                                }
                            
                                if (((!strstr($content_without_spaces, "defined('_JEXEC')ordie")) && (!strstr($content_without_spaces, "defined('JPATH_BASE')ordie")) && ($line==1)) || ($line==1)) {
                                                $count++;
                                    if (is_array($pattern)) { // then it has some additional comments
                                        $resultado[0][0] = true;
                                        $resultado[0][1] = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN');
                                        $resultado[0][2] = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN_INFO'), $pattern[2], $pattern[1], $results_count, utf8_decode($pattern[3]));
                                        $resultado[0][4] = '0';                                    
                                    } else
                                    { // it's a string, no comments available
                                        $resultado[0][0] = true;
                                        $resultado[0][1] = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN');
                                        $resultado[0][2] = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN_INFO_STRING'), $results_count, $pattern);
                                        $resultado[0][4] = '2';                            
                                    }
                                    // Añadimos el código sospechoso encontrado (previamente sanitizado)
                                    foreach ($all_results as $match)
                                    {
                                        $resultado[0][3] = $lang->_('COM_SECURITYCHECKPRO_LINE') . $line; 
                                        $resultado[0][3] .= "<br />";
                                        $resultado[0][3] .= htmlentities(substr($content, $match[1], 200), ENT_QUOTES);
                                    }
                                } else if (is_array($pattern)) {
                                                                 // Found a malware pattern; it's almost sure a malware even when it's hide into a valid Joomla file.
                                
                                                                 $count++;
                                                                 $resultado[0][0] = true;
                                                                 $resultado[0][1] = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN');
                                                                 $resultado[0][2] = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN_INFO'), $pattern[2], $pattern[1], $results_count, utf8_decode($pattern[3]));                                
                                                                 $resultado[0][4] = '0';                                    
                                                                 // Añadimos el código sospechoso encontrado (previamente sanitizado)
                                    foreach ($all_results as $match)
                                                                 {
                                        $resultado[0][3] = $lang->_('COM_SECURITYCHECKPRO_LINE') . $line; 
                                        $resultado[0][3] .= "<br />";
                                        $resultado[0][3] .= htmlentities(substr($content, $match[1], 200), ENT_QUOTES);
                                    }
                                } else if (!is_array($pattern)) {
                                        // Found a malware string; can't be sure this is not a false positive.
                                        $count++;
                                        $resultado[0][0] = true;
                                        $resultado[0][1] = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN');
                                        $resultado[0][2] = JText::sprintf($lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_PATTERN_INFO_STRING'), $results_count, $pattern);
                                        $resultado[0][4] = '2';    
                                
                                        // Añadimos el código sospechoso encontrado (previamente sanitizado)
                                    foreach ($all_results as $match)
                                        {
                                        $resultado[0][3] = $lang->_('COM_SECURITYCHECKPRO_LINE') . $line; 
                                        $resultado[0][3] .= "<br />";
                                        $resultado[0][3] .= htmlentities(substr($content, $match[1], 200), ENT_QUOTES);
                                    }
                                }
                            }
                        }
                    }
                }            
                unset($content);
            }
        }
        return $resultado;
    }

    /* Function to clean spaces of a given text */
    function clean_espaces($text)
    {
        $text = str_replace(' ', '', $text);
        return $text;
    }

    /**
      JAMSS - Joomla! Anti-Malware Scan Script
     *
     @version 1.0.7
    
     @author Bernard Toplak [WarpMax] <bernard@orion-web.hr>
     @link   http://www.orion-web.hr
    
     Calculates the line number where pattern match was found
    
     @param  int $offset  The offset position of found pattern match
     @param  str $content The file content in string format
     @return int Returns line number where the subject code was found
     */
    function calculate_line_number($offset, $file_content)
    {
        if (strlen($file_content) >= 1) {
            list($first_part) = str_split($file_content, $offset); // fetches all the text before the match
            $line_nr = strlen($first_part) - strlen(str_replace("\n", "", $first_part)) + 1;
            return $line_nr;
        } else 
        {
            return 0;
        }
    }

    /* Función que obtiene un array con los datos que seran mostrados en la opcion 'filestatus' */
    function loadModifiedFiles()
    {
    
        // Establecemos el tamao mximo de memoria que el script puede consumir
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $memory_limit = $params->get('memory_limit', '512M');
        if (preg_match('/^[0-9]*M$/', $memory_limit)) {
            ini_set('memory_limit', $memory_limit);
        } else
        {
            ini_set('memory_limit', '512M');
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_VALID_MEMORY_LIMIT'), 'error');
        }
        
        $db = $this->getDbo();
    
        // Consultamos la BBDD para extraer el nombre del fichero de escaneos de integridad.
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
        $db->setQuery($query);
        $stack_integrity = $db->loadResult();
        $stack_integrity = json_decode($stack_integrity, true);
    
        if ((!empty($stack_integrity)) && (isset($stack_integrity['filename']))) {
            $this->fileintegrity_name = $stack_integrity['filename'];
        }
    
        if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name)) {
            $stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
            // Eliminamos la parte del fichero que evita su lectura al acceder directamente
            $stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
        }
            
        if (empty($stack)) {
            $this->Stack_Integrity = array();
            return;
        }
        
        // Decodificamos el array
        $stack = json_decode($stack, true);
    
    
    
        // Extraemos slo los ficheros con integridad modificada
        $this->Stack_Integrity = array_values(
            array_filter(
                $stack['files_folders'], function ($element) {
                    return (($element['safe_integrity'] == 0));
                }
            )
        );
        // Mapeamos los los valores del campo 'path'
        $this->Stack_Integrity = array_map(
            function ($element) {
                return $element['path']; 
            }, $this->Stack_Integrity
        );
    
        return ($this->Stack_Integrity);
        

    }

    /* Función para escribir una entrada en el fichero de logs de cambio de permisos */
    function write_permission_log($log_array)
    {    
        // Borramos los ficheros de logs antiguos
		try{		
			JFile::delete(JPATH_ADMINISTRATOR. DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'change_permissions.log.php');
		} catch (Exception $e)
		{
		}
        JLog::addLogger(
            array(
            'text_file' => 'change_permissions.log.php',
            'text_entry_format' => '{DATETIME} {SEPARATOR} {MESSAGE}'
            )
        );
    
        foreach($log_array as $log)
        {
            $logEntry = new JLogEntry(array_pop($log_array));
            $logEntry->separator = '|';
            JLog::add($logEntry);
        }
    }

    /* Función que lee el fichero de log al realizarse una reparación */
    function get_repair_log()
    {
        // Obtenemos la ruta alfichero de logs, que vendrá marcada por la entrada 'log_path' del fichero 'configuration.php'
        $app = JFactory::getApplication();
        $logName = $app->getCfg('log_path');
        $logName = $logName . DIRECTORY_SEPARATOR ."change_permissions.log.php";

        if (!file_exists($logName)) {
            // El fichero no existe
            echo '<p>'.JText::_('COM_SECURITYCHECKPRO_LOG_ERROR_LOGFILENOTEXISTS').'</p>';
            return;
        }
        else
        {
            // Abrimos el fichero
            $fp = fopen($logName, "rt");
            if ($fp === false) {
                // El fichero no se puede leer
                echo '<p>'.JText::_('COM_SECURITYCHECKPRO_LOG_ERROR_UNREADABLE').'</p>';
                return;
            }
    
            $fmtString = "";

            while(!feof($fp))
            {
                // Indica si la línea del log tiene un formato válido, ya que en el fichero de logs existen líneas que no son propias de los logs, como la cabecera php 
                $valid = true;
                $line = fgets($fp);
                if(!$line) { break;
                }
                $exploded = explode("|", $line, 3);    
                if (count($exploded)>1) {  // Se han devuelto datos; los chequeamos para ver si son válidos
                    unset($line);
                    switch(trim($exploded[1]))
                    {
                    case "ERROR":
                         $fmtString .= "<span style=\"color: red; font-weight: bold;\">[";
                        break;
                    case "WARNING":
                        $fmtString .= "<span style=\"color: #D8AD00; font-weight: bold;\">[";
                        break;
                    case "INFO":
                              $fmtString .= "<span style=\"color: black;\">[";
                        break;
                    case "DEBUG":
                        $fmtString .= "<span style=\"color: #666666; font-size: small;\">[";
                        break;
                    case "OK":                    
                              $fmtString .= "<span style=\"color: green; font-weight: bold;\">[";
                        break;
                    default:
                          $valid = false;
                        break;
                    }
                    if ($valid) {    
                        $fmtString .= $exploded[0] . "] " . htmlspecialchars($exploded[2]) . "</span><br/>\n";                            
                    }
                }
            }
        
            if ($valid) {        
                return $fmtString;
                unset($fmtString);
                unset($exploded);
            }
        }


    }


    /* Función para cambiar los permisos de los archivos o carpetas con permisos mal configurados */
    function repair()
    {
        // Inicializamos las variables que contendrán el nivel y la entrada que se escribirán en el fichero de logs
        $entrada = '';
        $nivel = '';
        $log_array = array();
            
        /* Instanciamos el mainframe para guardar variables de estado de usuario */
        $mainframe = JFactory::getApplication();
        // Ponemos en la sesión de usuario que se ha lanzado una reparación de permisos
        $mainframe->setUserState("repair_launched", true);
        
        $db = $this->getDbo();
    
        // Cargamos el array de archivos
        if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name)) {
            $stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->filemanager_name);
            // Eliminamos la parte del fichero que evita su lectura al acceder directamente
            $stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
        }
            
        if (empty($stack)) {
            $this->Stack = array();
            $this->files_scanned = 0;
            $this->files_with_incorrect_permissions = 0;
            return;
        }

        $stack = json_decode($stack, true);
    
        // Inicializamos el array que contendrá los ficheros/directorios con los permisos mal configurados
        $filtered_array= array();
    
        $filtered_array = array_values(
            array_filter(
                $stack['files_folders'], function ($element) {
                    return ($element['safe'] == 0);
                }
            )
        );
        
        // ¿ Qué método vamos a usar para cambiar los permisos?
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $change_permissions_option = $params->get('change_permissions_option', 'chmod');
        
        foreach($filtered_array as $element)
        {
            $entrada = '';
            $nivel = '';
            (int) $permisos = 0644;
            if ($element['kind'] == JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_DIRECTORY')) {
                $permisos = 0755;
            }
        
            if ($change_permissions_option == 'chmod') {  // Cambiamos los permisos vía chmod
        
                $change_result = chmod($element['path'], $permisos);
                if ($change_result == 0) {
                    $nivel = "ERROR";
                    $entrada = $element['path'] . JText::_('COM_SECURITYCHECKPRO_REPAIR_CHANGE_PERMISSIONS_FAILED');                
                } else
                {
                    $nivel = "OK";
                    $entrada = $element['path'] . JText::_('COM_SECURITYCHECKPRO_REPAIR_CHANGE_PERMISSIONS_OK');                
                }
            } else if ($change_permissions_option == 'ftp') {  // Cambiamos los permisos vía ftp
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
                
                    $result = $ftp->chmod($element['path'], $permisos);
                    if ($result) {
                                    $nivel = "OK";
                                    $entrada = $element['path'] . JText::_('COM_SECURITYCHECKPRO_REPAIR_CHANGE_PERMISSIONS_OK');                    
                    } else {
                           $nivel = "ERROR";
                           $entrada = $element['path'] . JText::_('COM_SECURITYCHECKPRO_REPAIR_CHANGE_PERMISSIONS_FAILED');
                    }
                
                }                
            }
        
            // Añadimos una entrada al array del fichero de logs
            array_push($log_array, $nivel .'|' .$entrada);            
        } 
        
        $this->write_permission_log($log_array);
        $this->set_campo_filemanager('estado_cambio_permisos', 'ENDED');
    
        // Importamos el modelo 'filemanager'...
        JLoader::import('joomla.application.component.model');
        JLoader::import('cpanel', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
        // ... y lanzamos un escaneo para actualizar los resultados
        $filemanager_model = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('filemanager', 'SecuritycheckprosModel');
        $filemanager_model->set_campo_filemanager('estado', 'IN_PROGRESS'); 
        $filemanager_model->scan("permissions");    
        $filemanager_model->set_campo_filemanager('estado', 'ENDED');
    }

    /* Función para la paginación */
    function getPagination()
    {
        // Cargamos el contenido si es que no existe todavía
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');    
            $this->_pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));        
        }
        return $this->_pagination;
    }

    /* Función que cambia a '1' el valor del campo 'safe_integrity' de todos los ficheros de la BBDD cuyo valor actual sea '0' (están marcados como no seguros) */
    function mark_all_unsafe_files_as_safe()
    {
    
        // Cargamos los archivos de la BBDD
        $db = $this->getDbo();
    
        if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name)) {
            $stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
            // Eliminamos la parte del fichero que evita su lectura al acceder directamente
            $stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
        }
    
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
        $db->setQuery($query);
        $stack_resume = $db->loadResult();
        
        if (empty($stack)) {
            return;
        }

        $stack = json_decode($stack, true);
        $stack_resume = json_decode($stack_resume, true);
    
        // Si existen archivos con permisos incorrectos, les cambiamos su estado
        if ($stack_resume['files_with_incorrect_integrity'] > 0) {
    
            /* Cargamos el lenguaje del sitio */
            $lang = JFactory::getLanguage();
            $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        
            // Cargamos las variables con el contenido almacenado en la BBDD
            $this->Stack_Integrity = $stack['files_folders'];
            $this->files_scanned_integrity = $stack_resume['files_scanned_integrity'];
            $this->files_with_incorrect_integrity = 0;
            $this->last_check_integrity = $stack_resume['last_check_integrity'];
        
            $tamanno_array = count($this->Stack_Integrity);
            $indice = 0;
        
            while ($indice < $tamanno_array)
            {
                /* Dejamos sin efecto el tiempo máximo de ejecución del script. Esto es necesario cuando existen miles de archivos a escanear */
                set_time_limit(0);
                if ($this->Stack_Integrity[$indice]['safe_integrity'] == 0) {
                    $this->Stack_Integrity[$indice]['notes'] = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_OK');
                    $this->Stack_Integrity[$indice]['safe_integrity'] = (int) 1;                
                }
                $indice++;
            }
        
            // Guardamos los cambios
            $this->saveStack("integrity", false);
        }
    
        // Borramos la información de las instalaciones previas     
        try
        {
            $sql = "DELETE FROM #__securitycheckpro_storage WHERE storage_key = 'installs'";
            $db->setQuery($sql);
            $db->execute();
        }catch (Exception $e)
        {
        }
    }

    /* Función que cambia a '1' el valor del campo 'safe_integrity' de todos los ficheros seleccionados */
    function mark_checked_files_as_safe()
    {
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros a analizar
        $filenames = $jinput->get('filesintegritystatus_table', null, 'array');
    
        // Cargamos los archivos de la BBDD
        $db = $this->getDbo();
    
        // Leemos el contenido del fichero
        if (file_exists($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name)) {
            $stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->fileintegrity_name);
            // Eliminamos la parte del fichero que evita su lectura al acceder directamente
            $stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
        }
    
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('fileintegrity_resume'));
        $db->setQuery($query);
        $stack_resume = $db->loadResult();
    
        if (empty($stack)) {
            return;
        }

        $stack = json_decode($stack, true);
        $stack_resume = json_decode($stack_resume, true);
    
        // Si existen archivos con permisos incorrectos, les cambiamos su estado
        if ($stack_resume['files_with_incorrect_integrity'] > 0) {
            // Cargamos el lenguaje del sitio
            $lang = JFactory::getLanguage();
            $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        
            // Creamos un array de rutas
            $this->Stack_Integrity = $stack['files_folders'];
            $array_paths = array_map(
                function ($element) {
                    return $element['path']; 
                }, $this->Stack_Integrity
            );
            // Número de elementos del array        
            $tamanno_array = count($filenames);
            
            foreach ($filenames as $path)
            {
                // Buscamos el índice del array que contiene la información que queremos modificar...            
                $array_key = array_search($path, $array_paths);
                if (is_numeric($array_key)) {
                     // ... y actualizamos la información
                     $this->Stack_Integrity[$array_key]['safe_integrity'] = 1;    
                     $this->Stack_Integrity[$array_key]['notes'] = $lang->_('COM_SECURITYCHECKPRO_FILEINTEGRITY_OK');                            
                }
            
            }
            // Actualizamos los parámetros de archivos escaneados y con integridad incorrecta
            $this->files_scanned_integrity = $stack_resume['files_scanned_integrity'];
            $this->files_with_incorrect_integrity = $stack_resume['files_with_incorrect_integrity'] - $tamanno_array;
                        
            // Guardamos los cambios
            $this->saveStack("integrity", false);
        }
        
    }

    /* Chequea archivos contra el servicio OPWAST Metadefender Cloud */
    function online_check_files()
    {
        // Inicializamos las variables
        $this->analized_keys_array = array();
        $error = false;
    
        // Config.
        $api    = 'https://scan.metadefender.com/v2/file';    
    
        // Obtenemos la API key
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $apikey = $params->get('opswat_key', '');
    
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros a analizar
        $paths = $jinput->get('malwarescan_status_table', null, 'array');
    
        // Chequeamos si la función 'curl_init' está definida. Si no lo está mostramos un error y salimos de la función
        if (!function_exists('curl_init')) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_CURL_NOT_DEFINED'));
            return $error;
        }
            
        if (!empty($paths)) {            
            // Creamos el nuevo objeto query
            $db = $this->getDbo();
            $query = $db->getQuery(true);
        
            // Cargamos el contenido del fichero de archivos sospechosos
            $malwarescan_data = $this->loadStack("malwarescan", "malwarescan", true);
            // Creamos un array de rutas para modificar los elementos que hayan sido escaneados
            $array_paths = array_map(
                function ($element) {
                    return $element['path']; 
                }, $malwarescan_data
            );
        
            // Número de archivos escaneados en la última hora
            $this->analized_files_last_hour = $this->get_online_analyzed_values("files");
        
            // Chequeamos si sobrepasamos el límite de archivos a analizar por hora (25)
            if (($this->analized_files_last_hour) + (count($paths)) <= 25) {
                foreach($paths as $path) 
                {        
                    // Buscamos la clave del array a modificar
                    $array_key = array_search($path, $array_paths);
                
                    // Si tenemos un 'data_id' válido no volvemos a preguntar por uno al servicio online. Esto significa que ya hemos remitido el fichero para su analisis.
                    if (empty($malwarescan_data[$array_key]['data_id'])) {
                            
                        // Path sanitizada
                        $file = $db->escape($path);
                    
                        // Build headers array.
                        $headers = array(
                         'apikey: '.$apikey,
                         'filename: '.$file
                        );

                        // Build options array.
                        $options = array(
                         CURLOPT_URL     => $api,
                         CURLOPT_HTTPHEADER  => $headers,
                         CURLOPT_POST        => true,
                         CURLOPT_POSTFIELDS  => file_get_contents($file),
                         CURLOPT_RETURNTRANSFER  => true,
                         CURLOPT_CAINFO    =>    SCP_CACERT_PEM,
                         CURLOPT_SSL_VERIFYHOST    => 2,
                         CURLOPT_SSL_VERIFYPEER  => true
                        );

                        // Init & execute API call.
                        $ch = curl_init();
                        curl_setopt_array($ch, $options);
                        $response = json_decode(curl_exec($ch), true);
                    
                        // Obtenemos el resultado de la consulta. Cualquier código devuelto diferente a 200 indicará un error.
                        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    
                        if ($http_status == 200) {
                                    
                            // Buscamos la clave del array a modificar
                            $array_key = array_search($path, $array_paths);
                            // Almacenamos el valor encontrado para utilizarlo posteriormente
                            array_push($this->analized_keys_array, $array_key);
                        
                            // Y añadimos los campos 'data_id' y 'rest_ip'
                            $data_id = $response['data_id'];
                            $rest_ip = $response['rest_ip'];
                            $malwarescan_data[$array_key]['data_id'] = $data_id;
                            $malwarescan_data[$array_key]['rest_ip'] = $rest_ip;
                        
                            // Incrementamos el valor de la variable de archivos analizados
                            $this->analized_files_last_hour++;    
                        
                        } else
                         {
                            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ERROR_RETURNED', $http_status), 'error');
                        }
                    } else
                    {
                        // Almacenamos el valor encontrado para utilizarlo posteriormente
                        array_push($this->analized_keys_array, $array_key);                    
                    }
                }
            
                // Actualizamos los valores de los campos relacionados con el analisis online
                $this->set_campo_filemanager('online_checked_files', $this->analized_files_last_hour);
				$timestamp = $this->global_model->get_Joomla_timestamp();
                $this->set_campo_filemanager('last_online_check_malwarescan', $timestamp);        

                // Buscamos el resultado de los análisis. Para ello preguntamos al servicio Metadefender Cloud sobre cada 'result_id' devuelto.
                $this->look_for_results($apikey, $malwarescan_data, "files");    
            } else
            {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_REACHED_ONLINE_FILES'), 'error');
                $error = true;
            }
        } else 
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_FILES_SELECTED'), 'error');    
            $error = true;
        }
    
        return $error;
    
    }

    /* Chequea hashes contra el servicio OPWAST Metadefender Cloud */
    function online_check_hashes()
    {
    
        // Inicializamos las variables
        $this->analized_keys_array = array();
        $error = false;
    
        // Obtenemos la API key
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $apikey = $params->get('opswat_key', '');
    
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros a analizar
        $paths = $jinput->get('malwarescan_status_table', null, 'array');
                
        if (!empty($paths)) {    
        
            // Creamos el nuevo objeto query
            $db = $this->getDbo();
            $query = $db->getQuery(true);
        
            // Cargamos el contenido del fichero de archivos sospechosos
            $malwarescan_data = $this->loadStack("malwarescan", "malwarescan", true);
                
            // Creamos un array de rutas para modificar los elementos que hayan sido escaneados
            $array_paths = array_map(
                function ($element) {
                    return $element['path']; 
                }, $malwarescan_data
            );
        
            // Número de archivos escaneados en la última hora
            $this->analized_hashes_last_hour = $this->get_online_analyzed_values("hashes");
        
            // Chequeamos si sobrepasamos el límite de hashes a analizar por hora (1000)
            if (($this->analized_hashes_last_hour) + (count($paths)) <= 1000) {
                foreach($paths as $path)
                 {        
                    // Buscamos la clave del array a modificar
                    $array_key = array_search($path, $array_paths);
                    // Almacenamos el valor encontrado para utilizarlo posteriormente
                    array_push($this->analized_keys_array, $array_key);                    
                }
            
                 // Preguntamos directamente al servicio online por cada valor hash seleccionado.
                 $this->look_for_results($apikey, $malwarescan_data, "hashes");    
            } else
            {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_REACHED_ONLINE_FILES'), 'error');
                $error = true;
            }
    
        } else 
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_FILES_SELECTED'), 'error');    
            $error = true;
        }
    
        return $error;
        
    }

    /* Función que obtiene el número de archivos o hashes escaneados online durante la última hora */
    function get_online_analyzed_values($type)
    {

        // Inicializamos las variables
        $analyzed = 0;
        
		$last_check = $this->get_campo_filemanager('last_online_check_malwarescan');
        $now = $this->global_model->get_Joomla_timestamp();
		$seconds = strtotime($now) - strtotime($last_time_launched);
                    
        // Calculamos las horas que han pasado desde el último chequeo
        $interval = intval($seconds/3600);	
        
        // Si ha pasado una hora o más desde el último escaneo, inicializamos el valor almacenado en la BBDD. De lo contrario devolvemos el valor almacenado en la BBDD.
        if ($interval >= 1) {
            switch ($type)
            {
            case "files":
                $this->set_campo_filemanager('online_checked_files', 0);
                break;
            case "hashes":
                $this->set_campo_filemanager('online_checked_hashes', 0);
                break;
            }
        } else 
        {
            switch ($type)
            {
            case "files":
                $analyzed = $this->get_campo_filemanager('online_checked_files');
                break;
            case "hashes":
                     $analyzed = $this->get_campo_filemanager('online_checked_hashes');
                break;
            }
        
        }
    
        return $analyzed;
    }

    /* Función que obtiene el resultado de cada uno de los archivos o hashes escaneados online */
    private function look_for_results($apikey,$malwarescan_data,$opcion)
    {

        /* Inicializamos las variables */
        $array_infected_files = array();
        $json_infected_files = null;
    
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
    
        // Inicializamos las variables
        switch ($opcion)
        {
        case "files":
            $file_analysis_result = "#<?php die('Forbidden.'); ?>" . PHP_EOL . "<h3>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_FILE_SCAN') . "</h3>" . PHP_EOL;
            break;
        case "hashes":
            $file_analysis_result = "#<?php die('Forbidden.'); ?>" . PHP_EOL . "<h3>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_HASHES_SCAN') . "</h3>" . PHP_EOL;
            break;
        }
        $threats_found = 0;
    
        foreach ($this->analized_keys_array as $array_key)
        {    
            switch ($opcion)
            {
            case "files":
                //Config.
                // Cuando el escaneo del archivo se está realizando, hemos de hacer consultas al servidor proporcionado por la clave 'rest_ip'
                $api        = 'https://' . $malwarescan_data[$array_key]['rest_ip'] . '/file/' .$malwarescan_data[$array_key]['data_id'];
                
                //Build headers array.
                $headers = array(
                'apikey: '.$apikey
                );

                //Build options array.
                $options = array(                    
                CURLOPT_URL     => $api,
                CURLOPT_HTTPHEADER  => $headers,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CAINFO    =>    SCP_CACERT_PEM,
                CURLOPT_SSL_VERIFYHOST    => 2,
                CURLOPT_SSL_VERIFYPEER  => true
                );

                $response = "";
                //Init & execute API call.
                $ch = curl_init();
                curl_setopt_array($ch, $options);
                
                do
                {
                    $response = json_decode(curl_exec($ch), true);
                }
                while ($response["scan_results"]["progress_percentage"] != 100);
                
                // Una vez finalizado el escaneo, hacemos una petición más (esta vez al servicio en scan.metadefender.com) para obtener el resultado
                $api        = 'https://scan.metadefender.com/v2/file/' .$malwarescan_data[$array_key]['data_id'];
                
                //Build headers array.
                $headers = array(
                'apikey: '.$apikey
                );

                //Build options array.
                $options = array(
                CURLOPT_URL     => $api,
                CURLOPT_HTTPHEADER  => $headers,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CAINFO    =>    SCP_CACERT_PEM,
                CURLOPT_SSL_VERIFYHOST    => 2,
                CURLOPT_SSL_VERIFYPEER  => true
                );

                $response = "";
                //Init & execute API call.
                $ch = curl_init();
                curl_setopt_array($ch, $options);
                
                $response = json_decode(curl_exec($ch), true);
                break;
            case "hashes":
                     // Establecemos el valor del hash en la variable 'api'
                     $api = 'https://hashlookup.metadefender.com/v2/hash/' . $malwarescan_data[$array_key]['sha1_value'];
                            
                     // Build headers array.
                     $headers = array(
                     'apikey: '.$apikey
                     );

                     // Build options array.
                     $options = array(                    
                     CURLOPT_URL     => $api,
                     CURLOPT_HTTPHEADER  => $headers,
                     CURLOPT_RETURNTRANSFER  => true,
                     CURLOPT_CAINFO    =>    SCP_CACERT_PEM,
                     CURLOPT_SSL_VERIFYHOST    => 2,
                     CURLOPT_SSL_VERIFYPEER  => true
                     );

                     // Init & execute API call.
                     $ch = curl_init();
                     curl_setopt_array($ch, $options);
                
                     $response = json_decode(curl_exec($ch), true);
                
                     // Incrementamos el valor de la variable de archivos analizados
                     $this->analized_hashes_last_hour++;                
                
                break;
            }
        
            if (is_array($response)) {                    
                // Guardamos el resultado del escaneo online
                $malwarescan_data[$array_key]['online_check'] = $response["scan_results"]["scan_all_result_i"];
        
                // Guardamos el resultado del escaneo online
                $malwarescan_data[$array_key]['online_check'] = $response["scan_results"]["scan_all_result_i"];
            
                if (!array_key_exists("scan_results", $response)) {
                    // El hash no se ha encontrado pero el resultado del escaneo es un array con el formato "hash = Not found"
                    // Guardamos el resultado del escaneo online (le asignamos el valor '15')
                    $malwarescan_data[$array_key]['online_check'] = 15;
                    // Añadimos el resultado a la variable que será volcada en el fichero de resultados. Pasamos los datos del fichero ya que el hash no se ha encontrado en la BBDD
                    $file_analysis_result .= $this->format_data($response, true, $malwarescan_data[$array_key]);
                } else 
                {        
                    // Actualizamos la variable de amenazas encontradas si es que se han encontrado
                    if (($response["scan_results"]["scan_all_result_i"] == 1) || ($response["scan_results"]["scan_all_result_i"] == 2)) {
                        $threats_found++;
                    
                        /* Extraemos sólo el nombre del fichero. Como los valores hash pueden corresponder a ficheros con caracteres de separación (/ y \) de otros sistema operativo, hemos de buscar y reemplazar los que puedan existir por el del sistema operativo que opera (que vendrá dado por DIRECTORY_SEPARATOR) */
                        $nombre = $response["file_info"]["display_name"];
                        $to_change = array("/","\\");
                        $nombre = str_replace($to_change, DIRECTORY_SEPARATOR, $nombre);
                        $nombre = basename($nombre);
                    
                        // Añadimos el nombre al array de ficheros infectados
                        $array_infected_files[] = $nombre;    
                    } 
                
                    // Añadimos el resultado a la variable que será volcada en el fichero de resultados
                    $file_analysis_result .= $this->format_data($response);
                }
            } else 
            {
                  // Guardamos el resultado del escaneo online (le asignamos el valor '15')
                  $malwarescan_data[$array_key]['online_check'] = 15;
            
                  // Añadimos el resultado a la variable que será volcada en el fichero de resultados. Pasamos los datos del fichero ya que el hash no se ha encontrado en la BBDD
                  $file_analysis_result .= $this->format_data($response, true, $malwarescan_data[$array_key]);
            }
        }
        
        // Cambiamos el formato del array a json para almacenarlo en la bbdd
        if (!empty($array_infected_files)) {
            $json_infected_files = json_encode($array_infected_files);
        }
    
        // Si la opción seleccionada es el escaneo de hashes, actualizamos las variables correspondientes en la bbdd.
        if ($opcion == "hashes") {
            // Actualizamos los valores de los campos relacionados con el analisis online
            $this->set_campo_filemanager('online_checked_hashes', $this->analized_hashes_last_hour);
			$timestamp = $this->global_model->get_Joomla_timestamp();
            $this->set_campo_filemanager('last_online_check_malwarescan', $timestamp);    
        }
    
        // Borramos el fichero del escaneo anterior...
		try{		
			$delete_malwarescan_file = JFile::delete($this->folder_path.$this->malwarescan_name);
		} catch (Exception $e)
		{
		}
                    
        // ... y almacenamos el nuevo contenido
        try
        {
            $content_malwarescan = utf8_encode(json_encode(array('files_folders'    => $malwarescan_data)));
            $content_malwarescan = "#<?php die('Forbidden.'); ?>" . PHP_EOL . $content_malwarescan;
            $result_malwarescan = JFile::write($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name, $content_malwarescan);
        } catch (Exception $e)
        {
                
        }
    
        // Comprobamos si hay algo que escribir
        if (!is_null($file_analysis_result)) {
            // Escribimos el contenido del buffer en un fichero
            $status = $this->write_file($file_analysis_result, $threats_found, count($this->analized_keys_array), $json_infected_files);
        }
    }

    /* Función que formatea los datos de entrada (en un array) para adaptarlos al del fichero */
    private function format_data($response, $not_found = false, $file_data = null) 
    {    
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);

        // Inicializamos las variables
        $data = null;
        $scan_result = '';
    
        // El hash se ha encontrado en la BBDD
        if (!$not_found) {
            $data = "<h4>" . $response["file_info"]["display_name"] . "</h4>" . PHP_EOL;
            $data .= "<p>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_UPLOAD_TIMESTAMP') . ": " . $response["file_info"]["upload_timestamp"] . "</p>" . PHP_EOL;
            $data .= "<p>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_FILE_SIZE') . ": " . $response["file_info"]["file_size"] . "</p>" . PHP_EOL;
            $data .= "<p>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_DATA_ID') . ": " . $response["data_id"] . "</p>" . PHP_EOL;
            $data .= "<p>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SHA256') . ": " . $response["file_info"]["sha256"] . "</p>" . PHP_EOL;
            
            switch ($response["scan_results"]["scan_all_result_i"])
            {
            case 0:
                $scan_result = "<span style=\"color: #008000;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_CLEAN') . "</span></strong>";
                break;
            case 1:
                $scan_result = "<span style=\"color: #FF0000;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_INFECTED') . "</span></strong>";
                break;
            case 2:
                $scan_result = "<span style=\"color: #FF4000;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_SUSPICIOUS') . "</span></strong>";
                break;
            case 3:
                $scan_result = "<span style=\"color: #61380B;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_FAILED_TO_SCAN') . "</span></strong>";
                break;
            case 4:
                $scan_result = "<span style=\"color: #000000;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_CLEANED') . "</span></strong>";
                break;
            case 5:
                $scan_result = "<span style=\"color: #000000;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_UNKNOW') . "</span></strong>";
                break;
            case 6:
                $scan_result = "<span style=\"color: #000000;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_QUARANTINED') . "</span></strong>";
                break;
            case 7:
                $scan_result = "<span style=\"color: #000000;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_SKIPPED_CLEAN') . "</span></strong>";
                break;
            case 8:
                $scan_result = "<span style=\"color: #000000;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_SKIPPED_DIRTY') . "</span></strong>";
                break;
            case 9:
                $scan_result = "<span style=\"color: #61380B;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_EXCEEDED_DEPTH') . "</span></strong>";
            case 10:
                $scan_result = "<span style=\"color: #61380B;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_NOT_SCANNED') . "</span></strong>";
                break;
            case 11:
                $scan_result = "<span style=\"color: #61380B;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_ABORTED') . "</span></strong>";
                break;
            case 12:
                $scan_result = "<span style=\"color: #61380B;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_ENCRYPTED') . "</span></strong>";
                break;
            case 13:
                $scan_result = "<span style=\"color: #61380B;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_EXCEEDED_SIZE') . "</span></strong>";
                break;
            case 14:
                $scan_result = "<span style=\"color: #61380B;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_EXCEEDED_FILE_NUMBER') . "</span></strong>";
                break;
            }
            $data .= "<p>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS') . ": " . $scan_result . "</p>" . PHP_EOL;
            $data .= "<p>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_TOTAL_AVS') . ": " . $response["scan_results"]["total_avs"] . "</p>" . PHP_EOL . PHP_EOL;
            
            // Actualizamos la variable de amenazas encontradas si es que se han encontrado
            if ($response["scan_results"]["scan_all_result_i"] == 1) {
                $data .= "<h5 style=\"color: #2E64FE;\">" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_AVS_RESULT') . "</h5>" . PHP_EOL;
                $data .= "<table border=\"1\">" . PHP_EOL;
                $data .= "<thead>" . PHP_EOL;
                $data .= "<tr>" . PHP_EOL;
                $data .= "<th>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_AV_ENGINE') . "</th>" . PHP_EOL;
                $data .= "<th>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_DEF_TIME') . "</th>" . PHP_EOL;
                $data .= "<th>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_TIME') . "</th>" . PHP_EOL;
                $data .= "<th>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_THREAT_FOUND') . "</th>" . PHP_EOL;
                $data .= "</tr>";
                $data .= "</thead>";
                $data .= "</tbody>";        
            
                // Extraemos los nombres de los motores de antivirus usados
                $av_engines =  array_keys($response["scan_results"]['scan_details']);
                $indice = 0;
                foreach ($response["scan_results"]['scan_details'] as $av)
                {
                       $data .= "<tr>" . PHP_EOL;
                    if (empty($av['threat_found'])) {
                        $data .= "<td style=\"text-align: center; vertical-align: middle;\">" . $av_engines[$indice] . "</td>" . PHP_EOL;
                    } else 
                    {
                        $data .= "<td style=\"text-align: center; vertical-align: middle;\"><font color=#5858FA>" . $av_engines[$indice] . "</font></td>" . PHP_EOL;
                    }
                    $data .= "<td style=\"text-align: center; vertical-align: middle;\">" . $av['def_time'] . "</td>" . PHP_EOL;
                    $data .= "<td style=\"text-align: center; vertical-align: middle;\">" . $av['scan_time'] . "</td>" . PHP_EOL;
                    if (empty($av['threat_found'])) {
                        $data .= "<td style=\"text-align: center; vertical-align: middle;\">" . $av['threat_found'] . "</td>" . PHP_EOL;
                    } else
                    {
                        $data .= "<td style=\"text-align: center; vertical-align: middle;\"><font color=red>" . $av['threat_found'] . "</font></td>" . PHP_EOL;
                    }
                    $data .= "</tr>" . PHP_EOL;
                    $indice++;
                }
                $data .= "</tbody>";
                $data .= "</table>" . PHP_EOL;
            }
        } else
        {
            $data = "<h4>" . $file_data["path"] . "</h4>" . PHP_EOL;
            $data .= "<p>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SHA256') . ": " . $file_data["sha1_value"] . "</p>" . PHP_EOL;
            $scan_result = "<span style=\"color: #61380B;\"><strong>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS_HASH_NOT_FOUND') . "</span></strong>";
            $data .= "<p>" . $lang->_('COM_SECURITYCHECKPRO_MALWARESCAN_SCAN_RESULTS') . ": " . $scan_result . "</p>" . PHP_EOL;
        }
    
        return $data;

    }

    /* Función que guarda el resultado del escaneo en un fichero y actualiza la bbdd */
    private function write_file($file_analysis_result,$threats,$files_checked,$infected_files)
    {

        // Nombre del fichero
        $filename = $this->generateKey();
    
        // Comprobamos si hay que borrar archivos por alcanzar el límite establecido
        $this->check_logs_stored();
    
        // Escribrimos el fichero
        try
        {
            $file_result = JFile::write($this->folder_path.DIRECTORY_SEPARATOR.$filename, $file_analysis_result);
        } catch (Exception $e) {
                
        }
    
        // Actualizamos la bbdd con la información de este nuevo fichero
        if ($file_result) {
            $db = JFactory::getDBO();
            
            // Sanitizamos las entradas
            $filename = filter_var($filename, FILTER_SANITIZE_STRING);
            $files_checked = filter_var($files_checked, FILTER_SANITIZE_STRING);
            $threats = filter_var($threats, FILTER_SANITIZE_STRING);
            $filename = $db->escape($filename);
            $files_checked = $db->escape($files_checked);
            $threats = $db->escape($threats);
            $infected_files = $db->escape($infected_files);
            
            $sql = "INSERT INTO #__securitycheckpro_online_checks (filename, files_checked, threats_found, scan_date, infected_files) VALUES ('{$filename}', '{$files_checked}', '{$threats}', now(), '{$infected_files}')";
            $db->setQuery($sql);
            $db->execute();
        }

    }

    // Chequeamos si ha pasado más de una hora desde el último escaneo online para inicializar la variable que la controla
    private function check_last_onlinecheck()
    {

        // Último escaneo
        $last_check = $this->get_campo_filemanager("last_online_check_malwarescan");

        // Ahora
        $now = $this->global_model->get_Joomla_timestamp();	

        // Diferencia
        $seconds = strtotime($now) - strtotime($last_check);
		$hours = intval($seconds/3600);	

        // Si ha pasado más de una hora, inicializamos la variable
        if ( $hours >= 1) {
            $this->set_campo_filemanager("online_checked_files", 0);
        }

    }

    private function check_logs_stored()
    {

        // Inicializamos las variables
        $files_deleted = 0;
    
        // Consultamos los valores de configuración
        $params = JComponentHelper::getParams('com_securitycheckpro');
        (int) $log_files_to_store = $params->get('log_files_stored', 5);
    
        $db = JFactory::getDBO();
            
        $sql = "SELECT COUNT(*) FROM #__securitycheckpro_online_checks";
        $db->setQuery($sql);
        (int) $logs_stored = $db->loadResult();
        
        // Si se ha sobrepasado el límite de archivos que se deben guardar, los borramos del directorio y de la bbdd
        if ($logs_stored >= $log_files_to_store) {    
            // Extraemos el array de ficheros almacenados en orden descendente
            $query = $db->getQuery(true)
                ->select(array('filename'))
                ->from($db->quoteName('#__securitycheckpro_online_checks'))
                ->order('scan_date DESC');
            $db->setQuery($query);
            $filenames = $db->loadRowList();
        
            // Inicializamos el índice para recorrer el array
            $indice = 0;
            foreach ($filenames as $filename)
            {
                if ($indice >= ($log_files_to_store-1)) {
                    // Borramos el fichero
					try{		
						$delete_file = JFile::delete($this->folder_path.$filename[0]);
					} catch (Exception $e)
					{
						$delete_file = false;
					}
                    
                    // Si el fichero se ha borrado actualizamos la bbdd
                    if ($delete_file) {
                               $query = $db->getQuery(true)
                                   ->delete($db->quoteName('#__securitycheckpro_online_checks'))
                                   ->where($db->quoteName('filename').' = '.$db->quote($filename[0]));
                               $db->setQuery($query);
                               $db->execute();
                    }
                    $files_deleted++;
                }
                $indice++;
            }
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_DELETED_OLD_FILES', $files_deleted));                
        }    


    }

    /* Restaura a su ubicación original archivos movidos a la carpeta 'quarantine' */
    public function quarantined_file($opcion)
    {    
        // Establecemos la ruta donde está la cuarentena
        $quarantine_folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR.'quarantine';
    
        $stack = @file_get_contents($this->folder_path.DIRECTORY_SEPARATOR.$this->malwarescan_name);
        // Eliminamos la parte del fichero que evita su lectura al acceder directamente
        $stack = str_replace("#<?php die('Forbidden.'); ?>", '', $stack);
    
        $stack = json_decode($stack, true);
    
        // Datos del fichero en formato array
        $data = $stack['files_folders'];
    
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros que serán restaurados a su ubicación anterior
        $paths = $jinput->get('malwarescan_status_table', '0', 'array');
    
        // Inicializamos las variables
        if (!empty($paths)) {    
            foreach($paths as $path)
            {
                  // Buscamos el elemento en el array
                  $value = array_search($path, array_column($data, 'path'));
                if (is_int($value)) {
                    switch ($opcion)
                    {
                    case "restore":    
                        // Movemos el archivo a su ruta original                
                        $copy_resume = JFile::move($data[$value]['quarantined_file_name'], $path);
                        // Si se ha movido con éxito, actualizamos los datos
                        if ($copy_resume) {
                                 // Actualizamos los datos del fichero
                                 $data[$value]['moved_to_quarantine'] = 0;
                                 $data[$value]['safe_malwarescan'] = 0;
                                 $data[$value]['quarantined_file_name'] = "";
                        }
                        break;
                    case "delete":
                        // Movemos el archivo a su ruta original 
						try{		
								$delete_resume = JFile::delete($data[$value]['quarantined_file_name']);
							} catch (Exception $e)
							{
							}						
                        
                        // Si se borrado con éxito, actualizamos los datos
                        if ($delete_resume) {
                            unset($data[$value]);
                        }
                        break;
                    }
                        
                }
                
            }        
        }
    
        // Establecemos la ruta donde se almacenan los escaneos
        $this->folder_path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'scans'.DIRECTORY_SEPARATOR;
    
        
        // Obtenemos el nombre de los escaneos anteriores
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('malwarescan_resume'));
        $db->setQuery($query);
        $stack_malwarescan = $db->loadResult();    
        $stack_malwarescan = json_decode($stack_malwarescan, true);
        
        if (!empty($stack_malwarescan)) {
            $malwarescan_name = $stack_malwarescan['filename'];
        }
    
        try 
        {
            $malware_content = utf8_encode(json_encode(array('files_folders'    => $data)));
            $malware_content = "#<?php die('Forbidden.'); ?>" . PHP_EOL . $malware_content;
            $result_malware = JFile::write($this->folder_path.DIRECTORY_SEPARATOR.$malwarescan_name, $malware_content);            
            
        } catch (Exception $e)
        {    
        
        }
    
    }

    /* Función para borrar archivos sospechosos */
    function delete_files()
    {
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros a borrar
        $paths = $jinput->get('malwarescan_status_table', null, 'array');
    
        // Cargamos los datos almacenados en el fichero del escaneo
        $this->loadStack("malwarescan", "malwarescan");
        
        if (empty($paths)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_FILES_SELECTED'), 'error');
        } else 
        {
            $count=0;
            foreach ($paths as $path)
            {
				try{		
					$deleted = JFile::delete($path);
				} catch (Exception $e)
				{
				}
                
                if ($deleted) {                
                       $count++;
                    foreach ($this->Stack_Malwarescan as $key => $value) 
                       {                    
                        if ($value['path'] == $path) {
                            // Eliminamos la entrada del array...
                            unset($this->Stack_Malwarescan[$key]);
                            // ... y reorganizamos los índices del array
                            $this->Stack_Malwarescan = array_values($this->Stack_Malwarescan);
                        }
                    }
                } else 
                {
                        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_DELETE_FILE_ERROR', $path), 'error');
                }
            }
            // Obtenemos los datos del número de archivos escaneados, sospechosos y fecha de escaneo
            $this->loadStack("malwarescan_resume", "files_scanned_malwarescan");
            $this->loadStack("malwarescan_resume", "suspicious_files");
            $this->loadStack("malwarescan_resume", "last_check_malwarescan");
            // Actualizamos el número de archivos sospechosos según el número de archivos que hayamos borrado
            $this->suspicious_files = $this->suspicious_files - $count;
            // salvamos los datos
            $this->saveStack("malwarescan_modified");
            JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_ELEMENTS_DELETED_FROM_LIST', $count), 'message');    
        }
        
    }

    /* Función para borrar archivos sospechosos */
    function view_file()
    {
        // Creamos el objeto JInput para obtener las variables del formulario
        $jinput = JFactory::getApplication()->input;
    
        // Obtenemos las rutas de los ficheros a borrar
        $paths = $jinput->get('malwarescan_status_table', null, 'array');
            
        $mainframe = JFactory::getApplication();
        
        if (empty($paths)) {        
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_NO_FILES_SELECTED'), 'error');    
            $contenido = $mainframe->setUserState('contenido', "vacio");
        } else 
        {
            if (count($paths) > 1) {
                JFactory::getApplication()->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_SELECT_ONLY_ONE_FILE'), 'error');    
            } else {
                $file_content = @file_get_contents($paths[0]);
                $file_content = filter_var($file_content, FILTER_SANITIZE_SPECIAL_CHARS);            
                $contenido = $mainframe->setUserState('contenido', $file_content);                
            }        
        }
    }

    /* Crea un log de una tarea lanzada */
    function write_log($message,$level="INFO")
    {
    
        // If the log could not be opened we can't continue
        if (empty($this->fp)) {
            return;
        }
    
        $string = $level . "    |   ";
        $string .= @strftime("%y%m%d %H:%M:%S") . "   |   $message\r\n";

		@fwrite($this->fp, $string);		
    }

    function prepareLog($opcion)
    {
        // Generamos el nombre del nuevo fichero
        $filename_log = $this->generateKey();
    
        // Establecemos el valor que irán en el campo storage_value, según la opción pasada como argumento
        $storage_value = "";
    
        switch ($opcion)
        {
        case "permissions":            
            $storage_value = "filepermissions_log";
            break;
        case "integrity":
            $storage_value = "fileintegrity_log";
            break;
        case "malwarescan":
            $storage_value = "filemalware_log";
            break;
		case "controlcenter":
            $storage_value = "controlcenter_log";
            break;
        }
    
        // Borramos el fichero del escaneo anterior
        $db = $this->getDbo();        
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote($storage_value));
        $db->setQuery($query);
        $db->execute();
        
        $object = (object)array(
        'storage_key'    => $storage_value,
        'storage_value'    => json_encode(
            array(
            'filename'        => $filename_log
            )
        )
        );
    
        try 
        {
            $db->insertObject('#__securitycheckpro_storage', $object);
        } catch (Exception $e)
        {        
    
        }
		
		// Si preparamos el log para el control center devolvemos el nombre ya que serán necesario para el archivo /frontend/models/json.php
		if ($storage_value == "controlcenter_log") {
			return $filename_log;
		} else {
			// If another log is open, close it
			if (is_resource($this->fp)) {
				$this->close_Log();
			}

			// Touch the file
			@touch($this->folder_path.DIRECTORY_SEPARATOR.$filename_log);
		
		
			// Open the log file
			$this->fp = @fopen($this->folder_path.DIRECTORY_SEPARATOR.$filename_log, 'ab');
			
			// If we couldn't open the file set the file pointer to null
			if ($this->fp === false) {            
				$this->fp = null;
			}
		}
    }

    /* Close the currently active log */
    public function close_Log()
    {
        // The log file changed. Close the old log.
        if (is_resource($this->fp)) {
            @fclose($this->fp);
        }

        $this->fp = null;    
    }

    /* Extrae la información sobre las extensiones instaladas/actualizadas */
    function get_installs()
    {
        $installs = null;
    
        $db = $this->getDbo();
        try
        {
        
            $query = $db->getQuery(true)
                ->select(array($db->quoteName('storage_value')))
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('installs'));
            $db->setQuery($query);
            $installs = $db->loadResult();
            $installs = json_decode($installs, true);        
        } catch (Exception $e)
        {
            return false;                
        }
    
        return $installs;
    }

    /**
     * Delete a file or recursively delete a directory
     *
     * @param string $str Path to file or directory
     */
    function recursiveDelete($str)
    {
        if (is_array($str)) {
            return true;
        }
        if (is_file($str)) {        
            return @unlink($str);
        }
        elseif (is_dir($str)) {        
            $scan = glob(rtrim($str, '/').'/*');            
            foreach($scan as $index=>$path) {
                $this->recursiveDelete($path);
            }        
            return @rmdir($str);    
        }
    }

    /* Borra los archivos y directorios de la carpeta temporal */
    function acciones_clean_tmp_dir()
    {
        $mainframe = JFactory::getApplication();
        $mainframe->setUserState("clean_tmp_dir_state", 'start');
        $mainframe->setUserState("clean_tmp_dir_result", "");
    
        $tmp_path = rtrim(JFactory::getConfig()->get('tmp_path', JPATH_ROOT. DIRECTORY_SEPARATOR . 'tmp'), DIRECTORY_SEPARATOR);
    
        $folders = JFolder::folders($tmp_path, '.', true, true);
        $files = JFolder::files($tmp_path, '.', true, true, array('index.html','.htaccess'));
        $result = "";
        
        if (empty($files)) {
            $files = array();
        }
    
        if (count($files)) {
            foreach ($files as $file)
            {
                $file_delete_res = $this->recursiveDelete($file);            
                if (!$file_delete_res) {
                       $result .= $file . PHP_EOL;
                }
            }
        }
    
        if (empty($folders)) {
            $folders = array();
        }
    
        if (count($folders)) {
            foreach ($folders as $folder)
            {
                $folder_delete_res = $this->recursiveDelete($folder);
                if (!$folder_delete_res) {
                    $result .= $folder . PHP_EOL;
                }
            }
        }
    
        $folders = JFolder::folders($tmp_path, '.', true, true);
        $files = JFolder::files($tmp_path, '.', true, true, array('index.html','.htaccess'));
    
        if (empty($files) && empty($folders)) {
            $mainframe->setUserState("clean_tmp_dir_result", "");
        } else {
            $mainframe->setUserState("clean_tmp_dir_result", $result);
        }
    
        $mainframe->setUserState("clean_tmp_dir_state", JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_ENDED'));            
    }

}
