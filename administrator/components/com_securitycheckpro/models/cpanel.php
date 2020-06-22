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
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

// Load library
require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'loader.php';

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelCpanel extends SecuritycheckproModel
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

    private $defaultConfig = array(
    'blacklist'            => '',
    'whitelist'        => '',
    'dynamic_blacklist'        => 1,
    'dynamic_blacklist_time'        => 600,
    'dynamic_blacklist_counter'        => 5,
    'blacklist_email'        => 0,
    'priority'        => 'Blacklists first',
    'methods'            => 'GET,POST,REQUEST',
    'mode'            => 1,
    'logs_attacks'            => 1,
    'log_limits_per_ip_and_day'            => 0,
    'redirect_after_attack'            => 1,
    'redirect_options'            => 1,
    'second_level'            => 1,
    'second_level_redirect'            => 1,
    'second_level_limit_words'            => 3,
    'second_level_words'            => 'drop,update,set,admin,select,user,password,concat,login,load_file,ascii,char,union,from,group by,order by,insert,values,pass,where,substring,benchmark,md5,sha1,schema,version,row_count,compress,encode,information_schema,script,javascript,img,src,input,body,iframe,frame,$_POST,eval,$_REQUEST,base64_decode,gzinflate,gzuncompress,gzinflate,strtrexec,passthru,shell_exec,createElement',
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
    );

    function __construct()
    {
        parent::__construct();
    
        // Initialize variables
        $server = 'unknow';
    
        $mainframe = JFactory::getApplication();
    
        // Chequeamos si existe el fichero filemanager, necesario para lanzar las tareas de integridad y permisos
        $exists_filemanager = $mainframe->getUserState("exists_filemanager", true);
    
        // Si no existe, deshabilitamos el Cron para evitar una página en blanco
        if (!$exists_filemanager) {
            $this->disable_plugin("cron");        
        }    
    
        if ((strstr(strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_SANITIZE_STRING)), 'apache')) || (strstr(strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_SANITIZE_STRING)), 'litespeed')) || (strstr(strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_SANITIZE_STRING)), 'wisepanel'))) {
            $server = 'apache';
        } else if (strstr(strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_SANITIZE_STRING)), 'nginx')) {
            $server = 'nginx';
        } else if (strstr(strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_SANITIZE_STRING)), 'microsoft-iis')) {        
            $server = 'iis';
        }
        
        $mainframe->SetUserState("server", $server);
    }

    /*
    Busca las extensiones (componentes, plugins y módulos) instaladas en el equipo sin comprobar el estado del plugin ni las actualizaciones. Esta función es usada
    por el módulo 'Securitycheck Pro Info Module'.  */
    function buscarQuickIcons()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('element', 'manifest_cache')))
            ->from($db->quoteName('#__extensions'))
            ->where(($db->quoteName('type') . ' = ' . $db->quote('component')) or ($db->quoteName('type') . ' = ' . $db->quote('module')) or ($db->quoteName('type') . ' = ' . $db->quote('plugin')));      
        $db->setQuery($query);
        $result = $db->loadObjectList();

        // Importamos el modelo Securitycheckpros
        JLoader::import('joomla.application.component.model');
        JLoader::import('securitycheckpros', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR. 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models');
        $securitycheckpro_model = JModelLegacy::getInstance('securitycheckpros', 'SecuritycheckprosModel');
        $securitycheckpro_model->actualizarbbdd($result);
        $logs_pending = $this->LogsPending();
    }

    /* Función que obtiene el id del plugin de: '1' -> Securitycheck Pro , '2' -> Securitycheck Pro Cron */
    function get_plugin_id($opcion)
    {

        $db = JFactory::getDBO();
        if ($opcion == 1) {
            $query = 'SELECT extension_id FROM #__extensions WHERE name="System - Securitycheck Pro" and type="plugin"';
        } else if ($opcion == 2) {
            $query = 'SELECT extension_id FROM #__extensions WHERE name="System - Securitycheck Pro Cron" and type="plugin"';
        } else if ($opcion == 3) {
            $query = 'SELECT extension_id FROM #__extensions WHERE name="System - Securitycheck Pro Update Database" and type="plugin"';
        } else if ($opcion == 4) {
            $query = 'SELECT extension_id FROM #__extensions WHERE name="System - Securitycheck Spam Protection" and type="plugin"';
        } else if ($opcion == 5) {
            $query = 'SELECT extension_id FROM #__extensions WHERE name="System - url inspector" and type="plugin"';
        }
    
        $db->setQuery($query);
        $db->execute();
        $id = $db->loadResult();
    
        return $id;
    }

    /* Función que busca logs por fecha */
    function LogsByDate($opcion)
    {
    
        // Inicializamos la variable
        $query = null;
    
        $db = JFactory::getDBO();
        switch ($opcion)
        {
        case 'last_year':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE YEAR(`time`) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 YEAR))';
            break;
        case 'this_year':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE YEAR(`time`) = YEAR(CURDATE())';
            break;
        case 'last_month':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE (MONTH(`time`) = MONTH(CURDATE())-1) AND (YEAR(`time`) = YEAR(CURDATE()))';
            break;
        case 'this_month':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE (MONTH(`time`) = MONTH(CURDATE())) AND (YEAR(`time`) = YEAR(CURDATE()))';
            break;
        case 'last_7_days':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE `time` BETWEEN DATE_SUB(NOW(),INTERVAL 1 WEEK) AND NOW()';
            break;
        case 'yesterday':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE (DAYOFMONTH(`time`) = DAYOFMONTH(CURDATE())-1) AND (MONTH(`time`) = MONTH(CURDATE())) AND (YEAR(`time`) = YEAR(CURDATE())) ';
            break;
        case 'today':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE `time` > DATE_SUB(NOW(), INTERVAL 1 DAY)';
            break;
        }
    
        $db->setQuery($query);
        $db->execute();
        $result = $db->loadResult();
    
        return $result;
    }

    /* Función que busca logs por tipo */
    function LogsByType($opcion)
    {
    
        // Inicializamos la variable
        $query = null;
    
        $db = JFactory::getDBO();
        switch ($opcion)
        {
        case 'total_firewall_rules':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE (`type` = "XSS" OR `type` = "SQL_INJECTION" OR `type` = "LFI" OR `type` = "SECOND_LEVEL" OR `type` LIKE \'%_BASE64\')';
            break;
        case 'total_blocked_access':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE (`type` = "IP_BLOCKED" OR `type` = "IP_BLOCKED_DINAMIC")';
            break;
        case 'total_user_session_protection':
            $query = 'SELECT COUNT(*) FROM #__securitycheckpro_logs WHERE (`type` = "USER_AGENT_MODIFICATION" OR `type` = "REFERER_MODIFICATION" OR `type` = "SESSION_PROTECTION" OR `type` = "SESSION_HIJACK_ATTEMPT")';
            break;
        
        }
    
        $db->setQuery($query);
        $db->execute();
        $result = $db->loadResult();
    
        return $result;
    }

    /* Función que modifica los valores del Firewall web para aplicar una configuración básica de los filtros */
    function Set_Easy_Config()
    {
    
        // Inicializamos las variables
        $query = null;
        $applied = true;
    
        $db = JFactory::getDBO();
    
        // Obtenemos los valores de las distintas opciones del Firewall Web
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('pro_plugin'));
        $db->setQuery($query);
        $params = $db->loadResult();
        $params = json_decode($params, true);
    
        if (!empty($params)) {
            // Guardamos la configuración anterior
            $previous_params = $params;
        } else
        {
            // Establecemos los parámetros por defecto
            $previous_params = $this->defaultConfig;
        }
        
        // Parámetros que se desactivan o cuyo valor se deja en blanco para evitar falsos positivos
        $params['check_header_referer'] = "0";
        $params['duplicate_backslashes_exceptions'] = "*";
        $params['line_comments_exceptions'] = "*";
        $params['using_integers_exceptions'] = "*";
        $params['escape_strings_exceptions'] = "*";
		$params['session_protection_active'] = 0;
		$params['session_hijack_protection'] = 0;
		$params['session_hijack_protection_what_to_check'] = 0;
        $params['strip_all_tags'] = 0;
		
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
                
        // Actualizamos el valor del campo que contendrá si se ha aplicado o no esta configuración
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('easy_config'));
        $db->setQuery($query);
        $db->execute();
        
        $object = (object)array(
        'storage_key'    => 'easy_config',
        'storage_value'    => utf8_encode(
            json_encode(
                array(
                'applied'        => true,
                'previous_config'        => $previous_params
                )
            )
        )
        );
            
        try
        {
            $db->insertObject('#__securitycheckpro_storage', $object);
        } catch (Exception $e)
        {        
            $applied = false;
        }
        
        return $applied;
    }

    /* Función que obtiene si se ha aplicado la opción 'Easy config' */
    function Get_Easy_Config()
    {
    
        // Inicializamos las variables
        $query = null;
        $result = false;
    
        $db = JFactory::getDBO();
    
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('easy_config'));
        $db->setQuery($query);
        $applied = $db->loadResult();
        $applied = json_decode($applied, true);
        
        if (!(empty($applied)) && ($applied['applied'])) {
            $result = true;
        }
    
        return $result;
    }

    /* Función que modifica los valores del Firewall web para aplicar la configuración previa de los filtros */
    function Set_Default_Config()
    {
    
        // Inicializamos las variables
        $query = null;
        $applied = true;
    
        $db = JFactory::getDBO();
    
        // Obtenemos los valores de las distintas opciones del Firewall Web
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('pro_plugin'));
        $db->setQuery($query);
        $params = $db->loadResult();
        $params = json_decode($params, true);
    
        // Obtenemos los valores de configuración previos
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select(array($db->quoteName('storage_value')))
            ->from($db->quoteName('#__securitycheckpro_storage'))
            ->where($db->quoteName('storage_key').' = '.$db->quote('easy_config'));
        $db->setQuery($query);
        $previous_params = $db->loadResult();
        $previous_params = json_decode($previous_params, true);
    
        if (!empty($previous_params)) {
        
            // Parámetros que se desactivan o cuyo valor se deja en blanco para evitar falsos positivos
            $params['check_header_referer'] = $previous_params['previous_config']['check_header_referer'];
            $params['duplicate_backslashes_exceptions'] = $previous_params['previous_config']['duplicate_backslashes_exceptions'];
            $params['line_comments_exceptions'] = $previous_params['previous_config']['line_comments_exceptions'];
            $params['using_integers_exceptions'] = $previous_params['previous_config']['using_integers_exceptions'];
            $params['escape_strings_exceptions'] = $previous_params['previous_config']['escape_strings_exceptions'];
			$params['session_protection_active'] = $previous_params['previous_config']['session_protection_active'];
			$params['session_hijack_protection'] = $previous_params['previous_config']['session_hijack_protection'];
			$params['session_hijack_protection_what_to_check'] = $previous_params['previous_config']['session_hijack_protection_what_to_check'];
			$params['strip_all_tags'] = $previous_params['previous_config']['strip_all_tags'];
        
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
         
            // Actualizamos el valor del campo que contendrá si se ha aplicado o no esta configuración
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote('easy_config'));
            $db->setQuery($query);
            $db->execute();
        
            $object = (object)array(
            'storage_key'    => 'easy_config',
            'storage_value'    => utf8_encode(
                json_encode(
                    array(
                    'applied'        => false,
                    'previous_config'        => null
                    )
                )
            )
            );
            
            try 
            {
                $db->insertObject('#__securitycheckpro_storage', $object);
            } catch (Exception $e)
            {        
                $applied = false;
            }
        } else 
        {
            $applied = false;
        }
    
        return $applied;
    }

    /* Acciones al pulsar el botón 'Disable' del Firewall Web o Cron */
    function disable_plugin($plugin)
    {
        (int) $plugin_id = 0;
    
        // Obtenemos el id del plugin a deshabilitar
        if ($plugin == 'firewall') {
            $plugin_id = $this->get_plugin_id(1);
        } else if ($plugin == 'cron') {
            $plugin_id = $this->get_plugin_id(2);
        } else if ($plugin == 'update_database') {
            $plugin_id = $this->get_plugin_id(3);
        } else if ($plugin == 'spam_protection') {
            $plugin_id = $this->get_plugin_id(4);
        }
    
        // Actualizamos los parámetros del plugin en la BBDD
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set('enabled = 0')
            ->where('extension_id = '.$db->quote($plugin_id));
        $db->setQuery($query);    
        $db->execute();        
    }

    /* Acciones al pulsar el botón 'Enable' del Firewall Web o Cron */
    function enable_plugin($plugin)
    {
        (int) $plugin_id = 0;
    
        // Obtenemos el id del plugin a deshabilitar
        if ($plugin == 'firewall') {
            $plugin_id = $this->get_plugin_id(1);
        } else if ($plugin == 'cron') {
            $plugin_id = $this->get_plugin_id(2);
        } else if ($plugin == 'update_database') {
            $plugin_id = $this->get_plugin_id(3);
        } else if ($plugin == 'spam_protection') {
            $plugin_id = $this->get_plugin_id(4);
        } else if ($plugin == 'url_inspector') {
            $plugin_id = $this->get_plugin_id(5);
        }
    
        // Actualizamos los parámetros del plugin en la BBDD
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set('enabled = 1')
            ->where('extension_id = '.$db->quote($plugin_id));
        $db->setQuery($query);    
        $db->execute();        
    }

    /* Función que establece las actualizaciones automáticas de Geolite2 */
    function enable_automatic_updates()
    {
    
        // Get the params and set the new values
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $params->set('geoip_automatic_updates', 1);
            
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
    }

    /* Función que obtiene la versión del componente pasado como argumento */
    function get_version($extension)
    {

        $version = '0.0.0';
    
        $db = JFactory::getDBO();
        if ($extension == 'securitycheckpro') {
            $query = 'SELECT manifest_cache FROM #__extensions WHERE name="Securitycheck Pro"';
        } else if ($extension == 'databaseupdate') {
            $query = 'SELECT manifest_cache FROM #__extensions WHERE name="System - Securitycheck Pro Update Database" and type="plugin"';
        } else if ($extension == 'trackactions') {
            $query = 'SELECT manifest_cache FROM #__extensions WHERE name="Track Actions Package" and type="package"';
        } 
    
        $db->setQuery($query);
        $db->execute();
        $manifest_json = $db->loadResult();
    
        if (!empty($manifest_json)) {
            $manifest_decoded = json_decode($manifest_json);
            $version = $manifest_decoded->version;
        }
        
        return $version;
    }

    public static function modal($selector='a.modal', $params = array())
    {
        static $modals;
        static $included;

        $document = &JFactory::getDocument();

        // Load the necessary files if they haven't yet been loaded
        if (!isset($included)) {
            // Load the javascript and css            
            JHTML::_('script', 'system/modal.js', false, true);
            JHTML::_('stylesheet', 'system/modal.css', array(), true);

            $included = true;
        }

        if (!isset($modals)) {
                $modals = array();
        }

        $sig = md5(serialize(array($selector,$params)));
        if (isset($modals[$sig]) && ($modals[$sig])) {
            return;
        }

        // Setup options object
        $opt['ajaxOptions']     = (isset($params['ajaxOptions']) && (is_array($params['ajaxOptions']))) ? $params['ajaxOptions'] : null;
        $opt['size']            = (isset($params['size']) && (is_array($params['size']))) ? $params['size'] : null;
        $opt['shadow']          = (isset($params['shadow'])) ? $params['shadow'] : null;
        $opt['onOpen']          = (isset($params['onOpen'])) ? $params['onOpen'] : null;
        $opt['onClose']         = (isset($params['onClose'])) ? $params['onClose'] : null;
        $opt['onUpdate']        = (isset($params['onUpdate'])) ? $params['onUpdate'] : null;
        $opt['onResize']        = (isset($params['onResize'])) ? $params['onResize'] : null;
        $opt['onMove']          = (isset($params['onMove'])) ? $params['onMove'] : null;
        $opt['onShow']          = (isset($params['onShow'])) ? $params['onShow'] : null;
        $opt['onHide']          = (isset($params['onHide'])) ? $params['onHide'] : null;

        $options = JHtml::_getJSObject($opt);

        // Attach modal behavior to document
        $document->addScriptDeclaration(
            "
        window.addEvent('domready', function() {

                SqueezeBox.initialize(".$options.");
                SqueezeBox.assign($$('".$selector."'), {
                        parse: 'rel'
                });
        });"
        );

        // Set static array
        $modals[$sig] = true;
        return;
    }

    /* Función que crea un trigger con las parámetros pasados como argumento */
    function create_trigger_scp($option,$component_name,$table_name)
    {
        $db = JFactory::getDBO();
        try 
        {        
            $message = JText::_('COM_SECURITYCHECKPRO_LOCKED_MESSAGE');
            $trigger_name = $component_name . "_" . $option . "_trigger";
            $table_name_joomla_format = "#__" . $table_name;
                
            if ($option == "delete") {            
                  $query = "CREATE TRIGGER {$trigger_name}
			BEFORE DELETE ON {$table_name_joomla_format}
			FOR EACH ROW
			BEGIN
			IF OLD.storage_key = 'locked' THEN 
				SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
			END IF;
			END;";    
            } else 
            {
                $query = "CREATE TRIGGER {$trigger_name}
			BEFORE {$option} ON {$table_name_joomla_format}
			FOR EACH ROW
			BEGIN
			IF NEW.storage_key = 'locked' THEN 
				SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
			END IF;
			END;";    
            }
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e)
        {            
            //dump($e->getMessage(),"error");
        }    
    }

    /* Función que crea un trigger con las parámetros pasados como argumento */
    function create_trigger($option,$component_name,$table_name)
    {
        $db = JFactory::getDBO();
        try 
        {        
            $message = JText::_('COM_SECURITYCHECKPRO_LOCKED_MESSAGE');
            $trigger_name = $component_name . "_" . $option . "_trigger";
            $table_name_joomla_format = "#__" . $table_name;
        
            if ($table_name == "users") {
                if ($option == "update") {
                    $query = "CREATE TRIGGER {$trigger_name}
					BEFORE {$option} ON {$table_name_joomla_format}
					FOR EACH ROW
					BEGIN
					DECLARE locked integer;
					SET @locked=(SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key = 'locked');
							
					IF @locked = 1 and ((OLD.name != NEW.name) OR (OLD.username != NEW.username) OR (OLD.email != NEW.email) OR (OLD.password != NEW.password) OR (OLD.block != NEW.block) OR (OLD.otpKey != NEW.otpKey) OR (OLD.otep != NEW.otep)) THEN 
						SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
					END IF;
					END;";    
                } else {
                    $query = "CREATE TRIGGER {$trigger_name}
					BEFORE {$option} ON {$table_name_joomla_format}
					FOR EACH ROW
					BEGIN
					DECLARE locked integer;
					SET @locked=(SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key = 'locked');
							
					IF @locked = 1 THEN 
						SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
					END IF;
					END;";    
                }
            } else if ($table_name == "content") {
                if ($option == "update") {
                    $query = "CREATE TRIGGER {$trigger_name}
					BEFORE {$option} ON {$table_name_joomla_format}
					FOR EACH ROW
					BEGIN
					DECLARE locked integer;
					SET @locked=(SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key = 'locked');
							
					IF @locked = 1 and ((OLD.introtext != NEW.introtext) OR (OLD.fulltext != NEW.fulltext)) THEN 
						SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
					END IF;
					END;";    
                } else {
                    $query = "CREATE TRIGGER {$trigger_name}
					BEFORE {$option} ON {$table_name_joomla_format}
					FOR EACH ROW
					BEGIN
					DECLARE locked integer;
					SET @locked=(SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key = 'locked');
							
					IF @locked = 1 THEN 
						SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
					END IF;
					END;";    
                }
            } else if ($table_name == "extensions") {
                if ($option == "update") {
                    $query = "CREATE TRIGGER {$trigger_name}
					BEFORE {$option} ON {$table_name_joomla_format}
					FOR EACH ROW
					BEGIN
					DECLARE locked integer;
					SET @locked=(SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key = 'locked');
							
					IF @locked = 1 and ((OLD.extension_id != NEW.extension_id) OR (OLD.name != NEW.name) OR (OLD.element != NEW.element) OR (OLD.folder != NEW.folder) OR (OLD.access != NEW.access) OR (OLD.protected != NEW.protected) OR (OLD.state != NEW.state)) THEN 
						SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
					END IF;
					END;";    
                } else {
                    $query = "CREATE TRIGGER {$trigger_name}
					BEFORE {$option} ON {$table_name_joomla_format}
					FOR EACH ROW
					BEGIN
					DECLARE locked integer;
					SET @locked=(SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key = 'locked');
							
					IF @locked = 1 THEN 
						SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
					END IF;
					END;";    
                }
            } else 
            {
                $query = "CREATE TRIGGER {$trigger_name}
				BEFORE {$option} ON {$table_name_joomla_format}
				FOR EACH ROW
				BEGIN
				DECLARE locked integer;
				SET @locked=(SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key = 'locked');
						
				IF @locked = 1 THEN 
					SIGNAL SQLSTATE '02000' SET MESSAGE_TEXT = '{$message}';
				END IF;
				END;";    
            }        
        
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e)
        {                    
        }    
    }

    /* Función que bloquea las tablas seleccionadas */
    function lock_tables()
    {
        // Check component's parameters
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $lock_tables_easy = $params->get('lock_tables_easy');    
        $tables_to_block = $params->get('block_tables_easy');
    
        // Set default values if empty
        if (is_null($lock_tables_easy)) {
            $lock_tables_easy = true;
        }
    
        if (is_null($tables_to_block)) {
            $tables_to_block = array("users","content");
        }
    
        if ($lock_tables_easy) {        
            foreach ($tables_to_block as $table_name)
            {
                if ($table_name == "users") {
                    // 'Users' table triggers
                    $this->create_trigger("insert", "users", "users");
                    $this->create_trigger("update", "users", "users");
                    $this->create_trigger("delete", "users", "users");
                
                    // 'user_usergroup_map' table triggers
                    $this->create_trigger("insert", "user_usergroup_map", "user_usergroup_map");
                    $this->create_trigger("update", "user_usergroup_map", "user_usergroup_map");
                    $this->create_trigger("delete", "user_usergroup_map", "user_usergroup_map");
                    
                    // 'K2' user triggers
                    /*$this->create_trigger("insert","k2_users","k2_users");
                    $this->create_trigger("update","k2_users","k2_users");
                    $this->create_trigger("delete","k2_users","k2_users");*/
                                        
                    
                } else if ($table_name == "content") {
                    // 'Content' table triggers
                    $this->create_trigger("insert", "content", "content");
                    $this->create_trigger("update", "content", "content");
                    $this->create_trigger("delete", "content", "content");
                
                    // 'redirect_links' table triggers
                    $this->create_trigger("insert", "redirect_links", "redirect_links");
                    $this->create_trigger("update", "redirect_links", "redirect_links");
                    $this->create_trigger("delete", "redirect_links", "redirect_links");
                
                    // 'extensions' table triggers
                    $this->create_trigger("insert", "extensions", "extensions");
                    $this->create_trigger("update", "extensions", "extensions");
                    $this->create_trigger("delete", "extensions", "extensions");
                
                    /*// 'K2' table triggers
                    $this->create_trigger("insert","k2_items","k2_items");
                    $this->create_trigger("update","k2_items","k2_items");
                    $this->create_trigger("delete","k2_items","k2_items");*/
                }
            }        
        } else {
            $tables_to_block = $params->get('block_tables_plus');
            if (!empty($tables_to_block)) {
                $tables_to_block = explode(",", $tables_to_block);
                foreach ($tables_to_block as $table_name)
                {
                    $table_name = filter_var($table_name, FILTER_SANITIZE_STRING);
                    // New table triggers
                    $this->create_trigger("insert", $table_name, $table_name);
                    $this->create_trigger("update", $table_name, $table_name);
                    $this->create_trigger("delete", $table_name, $table_name);
                }
            }
        }
    
        $db = JFactory::getDBO();
        // Block scp tables
        try 
        {        
            $query = "UPDATE #__securitycheckpro_storage SET `storage_value` = '1' WHERE `storage_key` = 'locked'";
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e)
        {            
        }
    
        // Securitycheck Pro table triggers
        $this->create_trigger_scp("insert", "scp", "securitycheckpro_storage");
        $this->create_trigger_scp("update", "scp", "securitycheckpro_storage");
        $this->create_trigger_scp("delete", "scp", "securitycheckpro_storage");    
        
    }

    /* Función que borra un trigger */
    function drop_trigger($trigger_name)
    {
        $db = JFactory::getDBO();
    
        try 
        {        
            $query = "DROP TRIGGER {$trigger_name};";    
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e)
        {    
        
        }    
    }

    /* Función que desbloquea las tablas seleccionadas */
    function unlock_tables()
    {
    
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $lock_tables_easy = $params->get('lock_tables_easy');

        // Securitycheck Pro triggers
        $this->drop_trigger("scp_insert_trigger");
        $this->drop_trigger("scp_update_trigger");
        $this->drop_trigger("scp_delete_trigger");    
    
        $db = JFactory::getDBO();
        try 
        {        
            $query = "SHOW TRIGGERS;";    
            $db->setQuery($query);
            $db->execute();
            $triggers = $db->loadObjectList("Trigger");            
        } catch (Exception $e)
        {    
            // Trying to delete triggers using the info stored in the component
            $tables_to_block = $params->get('block_tables_plus');
            if (!empty($tables_to_block)) {
                $tables_to_block = explode(",", $tables_to_block);
                foreach ($tables_to_block as $table_name)
                {
                       $table_name = filter_var($table_name, FILTER_SANITIZE_STRING);
                       $this->drop_trigger($table_name . "_insert_trigger");
                       $this->drop_trigger($table_name . "_update_trigger");
                       $this->drop_trigger($table_name . "_delete_trigger");
                }
            }
        }    
        
        if (!empty($triggers)) {
            foreach ($triggers as $trigger)
            {
                // "Trigger" field contains trigger name
                $this->drop_trigger($trigger->Trigger);
            }
        }        
        
        $db = JFactory::getDBO();
        try 
        {        
            $query = "UPDATE #__securitycheckpro_storage SET `storage_value` = '0' WHERE `storage_key` = 'locked'";
            $db->setQuery($query);
            $db->execute();
        } catch (Exception $e)
        {            
        }
    
    }

    /* Función que chequea el estado de la tabla que controla los triggers */
    function lock_status()
    {
        $locked = 0;
        $db = JFactory::getDBO();    
        try 
        {        
            $query = 'SELECT storage_value FROM #__securitycheckpro_storage WHERE storage_key="locked"';
            $db->setQuery($query);
            $db->execute();
            $locked = $db->loadResult();
        } catch (Exception $e)
        {    
            return 0;
        }    
        return $locked;
    }

}
