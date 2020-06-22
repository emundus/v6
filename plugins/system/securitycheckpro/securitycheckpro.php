<?php
/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Access\Access as JAccess;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

class plgSystemSecuritycheckpro extends JPlugin
{
    private $pro_plugin = null;
    private $geoblock_config = null;
    
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        
        // Cargamos los par�metros del componente
        include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/models/protection.php';
        if(interface_exists('JModel')) {
            $this->pro_plugin = JModelLegacy::getInstance('Protection', 'SecuritycheckProsModel');
        }else{
            $this->pro_plugin = JModel::getInstance('Protection', 'SecuritycheckProsModel');
        }       
              
    }
    
    /* Funci�n para borrar logs */
    function delete_logs()
    {
        $db = JFactory::getDBO();
        
        (int) $track_actions_delete_period = $this->pro_plugin->getValue('delete_period', 0, 'pro_plugin');
        (int) $scp_delete_period = $this->pro_plugin->getValue('scp_delete_period', 60, 'pro_plugin');
        
        // Borramos los logs de Track Actions si el par�metro est� establecido as�
        if ($track_actions_delete_period > 0) {
            try
            {
                $sql = "DELETE FROM `#__securitycheckpro_trackactions` WHERE log_date < NOW() - INTERVAL '{$track_actions_delete_period}' DAY";
                $db->setQuery($sql);
                $db->execute();
            }catch (Exception $e)
            {
                
            }
        }
        
        // Borramos los logs capturados por el firewall
        if ($scp_delete_period > 0) {
            try
            {
                $sql = "DELETE FROM `#__securitycheckpro_logs` WHERE time < NOW() - INTERVAL '{$scp_delete_period}' DAY";
                $db->setQuery($sql);
                $db->execute();
            }catch (Exception $e)
            {
                
            }
        }
                
    }
    
    /* Funci�n para grabar los logs en la BBDD */
    function grabar_log($logs_attacks,$ip,$tag_description,$description,$type,$uri,$original_string,$username,$component)
    {
        
        if ($logs_attacks) {
            $db = JFactory::getDBO();
            
            /* El par�metro 'blacklist_email' indica si se manda un correo cuando una ip aparece en la lista negra. Inicialmente lo forzamos a '1' para que siempre se mande un email, excepto cuando el par�metro '$tag_description' sea igual a 'IP_BLOCKED', que se cuando se comprueba y, en su caso, modifica este valor */
            $blacklist_email = 1;
        
            /* El par�metro 'send_email_inspector' indica si hay que mandar un correo en las redirecciones 404 */
            $send_email_inspector = 0;
            
            // Sanitizamos las entradas
            $ip = filter_var($ip, FILTER_SANITIZE_STRING);
            $ip = $db->escape($ip);
            $username = filter_var($username, FILTER_SANITIZE_STRING);
            $username = $db->escape($username);
            $tag_description = filter_var($tag_description, FILTER_SANITIZE_STRING);
            $tag_description = $db->escape($tag_description);
            $description = filter_var($description, FILTER_SANITIZE_STRING);
            $description = $db->escape($description);
            $type = filter_var($type, FILTER_SANITIZE_STRING);
            $type = $db->escape($type);
            $uri = filter_var($uri, FILTER_SANITIZE_STRING);
            $uri = $db->escape($uri);
            $component = filter_var($component, FILTER_SANITIZE_STRING);            
            $component = $db->escape($component);
            // Guardamos el string original en formato base64 para evitar problemas de seguridad; adem�s, lo debemos filtrar en el archivo default.php
            //$original_string = filter_var($original_string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);                
            $original_string = base64_encode($original_string);
        
            // Consultamos el �ltimo log para evitar duplicar entradas
            $query = "SELECT tag_description,original_string,ip from `#__securitycheckpro_logs` WHERE id=(SELECT MAX(id) from `#__securitycheckpro_logs`)" ;            
            $db->setQuery($query);
            $row = $db->loadRow();
            
            // Consultamos el n�mero de logs para ver si se supera el l�mite establecido en el apartado 'log_limits_per_ip_and_day'
            (int) $logs_per_ip = $this->pro_plugin->getValue('log_limits_per_ip_and_day', 30, 'pro_plugin');
            try
            {
                $query = "SELECT COUNT(*) from `#__securitycheckpro_logs` WHERE ip='{$ip}' AND (DATE(NOW()) = DATE(`time`))" ;
                $db->setQuery($query);
                (int) $logs_recorded = $db->loadResult();
            }catch (Exception $e)
            {
                $logs_recorded = 0;
            }
			
			if (!empty($row))
			{                                                
				$result_tag_description = $row['0'];
				$result_original_string = $row['1'];
				$result_ip = $row['2'];
			} else
			{
				$result_tag_description = '---';
				$result_original_string = '---';
				$result_ip = '---';				
			}
			
			           
            /* Cargamos el lenguaje del sitio */
            $lang = JFactory::getLanguage();
            $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
            
            		
			// Obtenemos el timezone de Joomla y sobre esa informaci�n calculamos el timestamp
			$config = JFactory::getConfig();
			$offset = $config->get('offset');
			
			if (empty($offset))
			{
				$offset = 'UTC';
			}
			
			$date = new DateTime("now", new DateTimeZone($offset) );
			$timestamp_joomla_timezone = $date->format('Y-m-d H:i:s');
			            
                        
            if (((!($result_tag_description == $tag_description)) || (!($result_original_string == $original_string)) || (!($result_ip == $ip))) && (($logs_recorded < $logs_per_ip) || ($logs_per_ip == 0))) {
                
                try
                {
                    $sql = "INSERT INTO `#__securitycheckpro_logs` (`ip`, `username`, `time`, `tag_description`, `description`, `type`, `uri`, `component`, `original_string`) VALUES ('{$ip}', '{$username}', '{$timestamp_joomla_timezone}', '{$tag_description}', '{$description}', '{$type}', '{$uri}', '{$component}', '{$original_string}')";
                    $db->setQuery($sql);
                    $db->execute();
                }catch (Exception $e)
                {
                    return false;
                }
                                
                /* Si el par�metro '$tag_description' es 'IP_BLOCKED', comprobamos el campo 'blacklist_email' para ver si tenemos que mandar un correo 
                electr�nico cuando se bloquea un ip en la lista negra */
                if (($tag_description == 'IP_BLOCKED') || ($tag_description == 'IP_BLOCKED_DINAMIC')) {
                    $blacklist_email = $this->pro_plugin->getValue('blacklist_email', 0, 'pro_plugin');
                }
                
                $send_email_inspector = $this->pro_plugin->getValue('send_email_inspector', 0, 'pro_plugin');
                                
                // �Mandar email?
                $email_active = $this->pro_plugin->getValue('email_active', 0, 'pro_plugin');
                
                /* Cargamos el lenguaje del sitio */
                $lang = JFactory::getLanguage();
                $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        
                if ($email_active) {    
                    if ((($tag_description != 'IP_BLOCKED') && ($tag_description != 'URL_FORBIDDEN_WORDS')) || (($tag_description == 'IP_BLOCKED') && ($blacklist_email))  || (($tag_description == 'URL_FORBIDDEN_WORDS') && ($send_email_inspector))) {
                        $email_subject = $lang->_('COM_SECURITYCHECKPRO_RULE') . $lang->_('COM_SECURITYCHECKPRO_' .$tag_description) . "<br />" . $lang->_('COM_SECURITYCHECKPRO_USERNAME') . $username . "<br />" . "IP: " . $ip;
                        $this->mandar_correo($email_subject);
                    }
                    
                }
            }
        }    
        
    }
    
    /* Funci�n para grabar los logs de la propia aplicaci�n*/
    function grabar_log_propio($description)
    {
        
        // Obtenemos la configuraci�n del plugin
        $methods = $this->pro_plugin->getValue('methods', 'GET,POST,REQUEST', 'pro_plugin');
        $priority = $this->pro_plugin->getValue('priority', 'Blacklists first', 'pro_plugin');
        $logs_attacks = $this->pro_plugin->getValue('logs_attacks', 'pro_plugin');
        $mode = $this->pro_plugin->getValue('mode', '', 'pro_plugin');
        $blacklist_ips = $this->pro_plugin->getValue('blacklist', '', 'pro_plugin');
        $dynamic_blacklist_on = $this->pro_plugin->getValue('dynamic_blacklist', '', 'pro_plugin');
        $dynamic_blacklist_time = $this->pro_plugin->getValue('dynamic_blacklist_time', '', 'pro_plugin');
        $dynamic_blacklist_counter = $this->pro_plugin->getValue('dynamic_blacklist_counter', '', 'pro_plugin');
        $whitelist_ips = $this->pro_plugin->getValue('whitelist', '', 'pro_plugin');
        $secondlevel = $this->pro_plugin->getValue('second_level', '', 'pro_plugin');
        $check_base_64 = $this->pro_plugin->getValue('check_base_64', '', 'pro_plugin');
        
        $description = "Mensaje: " . $description . "  | Prioridad: " . $priority . " ,Grabar ataques: " . $logs_attacks 
        . " ,Modo: " . $mode . " ,Lista negra: " . $blacklist_ips 
        . " ,Lista negra dinamica (activa/tiempo/contador): " . $dynamic_blacklist_on . "/" . $dynamic_blacklist_time
        . "/" . $dynamic_blacklist_counter . " ,Lista blanca: " . $whitelist_ips . " ,Segundo nivel: " . $secondlevel
        . " ,Chequear base64: " . $check_base_64;
            
        $db = JFactory::getDBO();
        
        // Sanitizamos la entrada
        $description = filter_var($description, FILTER_SANITIZE_STRING);
        $description = $db->escape($description);
        
        // Borramos las entradas con m�s de un mes de antig�edad
        $sql = "DELETE FROM `#__securitycheckpro_own_logs` WHERE (DATE_ADD(`time`, INTERVAL 1 MONTH)) < NOW();";
        $db->setQuery($sql);
        $db->execute();
            
        $sql = "INSERT INTO `#__securitycheckpro_own_logs` (`time`, `description`) VALUES (now(), '{$description}')";
        $db->setQuery($sql);
        $db->execute();
        
    }
        
    /* Determina si un valor est� codificado en base64 */    
    function is_base64($value)
    {
        $res = false; // Determines if any character of the decoded string is between 32 and 126, which should indicate a non valid european ASCII character
    
        $min_len = mb_strlen($value)>7;
                
        if ($min_len) {
            
            $decoded = base64_decode(chunk_split($value));
            $string_caracteres = str_split($decoded); 
            if (empty($string_caracteres)) {
                return false;  // It�s not a base64 string!
            }else
            {
                foreach ($string_caracteres as $caracter)
                {
                    if ((empty($caracter)) || (ord($caracter)<32) || (ord($caracter)>126)) { // Non-valid ASCII value
                        return false; // It�s not a base64 string!
                    }
                }
            }
            
            $res = true; // It�s a base64 string!
        }
        
        return $res;
    }
    
    /* Determina si un string tiene caracteres ascii no v�lidos */    
    function is_ascii_valid($string)
    {
        $res = true; // Determines if any character of the decoded string is between 32 and 126, which should indicate a non valid european ASCII character
        
        // Trim all blank spaces
        $string = str_replace(' ', '', $string);
        $string_caracteres = str_split($string);         
        if (empty($string_caracteres)) {
            return true;  // There are no chars
        }else 
        {
            foreach ($string_caracteres as $caracter)
            {
                if ((empty($caracter)) || (ord($caracter)<32) || (ord($caracter)>126)) { // Non-valid ASCII value                    
                    return false; // There are non-valid chars
                }
            }
        }
                        
        return $res;
    }
    
    /* Funci�n para convertir en string una cadena hexadecimal */
    function hexToStr($hex)
    {
        
        $hex = trim(preg_replace("/(\%|0x)/", "", $hex));
                        
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2)
        {
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        
        return $string;         
    }
    
    /* Funci�n que realiza la misma funci�n que mysql_real_escape_string() pero sin necesidad de una conexi�n a la BBDD */
    function escapa_string($value)
    {
    
        $search = array("\x00", "'", "\"", "\x1a");
        $replace = array("\\x00", "\'", "\\\"", "\\\x1a");
    
        return str_replace($search, $replace, $value);
    }
    
    // Chequea si la extensi�n pasada como argumento es vulnerable
    private function check_extension_vulnerable($option)
    {
        
        // Inicializamos las variables
        $vulnerable = false;
        
        // Creamos el nuevo objeto query
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        // Sanitizamos el argumento
        $sanitized_option = $db->Quote($db->escape($option));
    
        // Construimos la consulta
        $query = "SELECT COUNT(*) from `#__securitycheckpro_db` WHERE (vuln_type = {$sanitized_option})" ;        
                
        $db->setQuery($query);
        $result = $db->loadResult();
        
        if ($result > 0) {
            $vulnerable = true;
        } 
        
        // Devolvemos el resultado
        return $vulnerable;
    
    }    
    
    /* Apply firewall filters */
    function apply_filters($ip,$string,$methods_options,$a,$request_uri,&$modified,$check,$logs_attacks,$option) 
    {
        $string_sanitized='';
        $base64=false;
        $pageoption='';
        $existe_componente = false;
        $username = '---';
        $component = '';
        $extension_vulnerable = false;
        $is_array = false;
                
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
        }else
        {
            $user_agent = 'Not set';
        }
        
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }else
        {
            $referer = 'Not set';
        }
        
        $app = JFactory::getApplication();
        $is_admin = $app->isClient('administrator');
        
        $user = JFactory::getUser();
        if (!$user->guest) {
            $username = $user->username;
        }
        
        $pageoption = $option;
                
        // Chequeamos si hemos de excluir los componentes vulnerables de las excepciones
        $exclude_exceptions_if_vulnerable = $this->pro_plugin->getValue('exclude_exceptions_if_vulnerable', 1, 'pro_plugin');
        
        // Si hemos podido extraer el componente implicado en la petici�n, vemos si la versi�n instalada es vulnerable
        if ((!empty($option)) && ($exclude_exceptions_if_vulnerable)) {
            $extension_vulnerable = $this->check_extension_vulnerable($option);                                    
        }
                
        /* Excepciones */
        $base64_exceptions = $this->pro_plugin->getValue('base64_exceptions', '', 'pro_plugin');
        $strip_tags_exceptions = $this->pro_plugin->getValue('strip_tags_exceptions', '', 'pro_plugin');
        $duplicate_backslashes_exceptions = $this->pro_plugin->getValue('duplicate_backslashes_exceptions', '', 'pro_plugin');
        $line_comments_exceptions = $this->pro_plugin->getValue('line_comments_exceptions', '', 'pro_plugin');
        $sql_pattern_exceptions = $this->pro_plugin->getValue('sql_pattern_exceptions', '', 'pro_plugin');
        $if_statement_exceptions = $this->pro_plugin->getValue('if_statement_exceptions', '', 'pro_plugin');
        $using_integers_exceptions = $this->pro_plugin->getValue('using_integers_exceptions', '', 'pro_plugin');
        $escape_strings_exceptions = $this->pro_plugin->getValue('escape_strings_exceptions', '', 'pro_plugin');
        $lfi_exceptions = $this->pro_plugin->getValue('lfi_exceptions', '', 'pro_plugin');
        $check_header_referer = $this->pro_plugin->getValue('check_header_referer', 1, 'pro_plugin');
        $strip_all_tags = $this->pro_plugin->getValue('strip_all_tags', 1, 'pro_plugin');
        $tags_to_filter = $this->pro_plugin->getValue('tags_to_filter', 'applet,body,bgsound,base,basefont,embed,frame,frameset,head,html,id,iframe,ilayer,layer,link,meta,name,object,script,style,title,xml,svg,input', 'pro_plugin');
        
        /* Base64 check */
        if ($check) {
            /* Chequeamos si el componente est� en la lista de excepciones */
            if (!(strstr($base64_exceptions, $pageoption))) {
                $is_base64 = $this->is_base64($string);
                if ($is_base64) {                    
                    $decoded = base64_decode(chunk_split($string));
                    $base64=true;
                    $string = $decoded;
                }
            }
        }
                        
        /* Hexadecimal check */
        if (preg_match("/(\%[a-zA-Z0-9]{2}|0x{4,})/", $string)) {
            $string = $this->hexToStr($string);            
            /*$is_valid = $this->is_ascii_valid($string_temp);            
            if ($is_valid) {  // El string contiene caracteres hexadecimales y su conversi�n a caracteres ASCII es v�lida                
            $string = $string_temp;                    
            }*/
        }   
                                    
        /* XSS Prevention */
        //Strip html and php tags from string
        if ((!(strstr($strip_tags_exceptions, $pageoption)) || $extension_vulnerable) && !(strstr($strip_tags_exceptions, '*'))) {
            // If we are in backend, we must not filter all tags to avoid false positives even when creating/modifying articles.
            if ($is_admin) {                
                $strip_all_tags = 2;
                $tags_to_filter = 'applet,body,bgsound,base,basefont,embed,frame,frameset,head,html,id,iframe,ilayer,layer,link,meta,name,object,script,xml,svg';
            }
        
            if ($strip_all_tags == 1) {
                // Filtering all tags    
                $string_sanitized = strip_tags($string);                
            }else
            {                        
                // Decoding of html entities (if any) to match patterns (less and more than signs)
                $string = html_entity_decode($string);
                $tags_to_filter_final = array();
                $tags_array = explode(",", $tags_to_filter);                            
                foreach ($tags_array as $tag)
                {
                    $tags_to_filter_final[] = "<" . $tag;
                    $tags_to_filter_final[] = $tag . "/>";                        
                }            
                
                $string_sanitized = str_replace($tags_to_filter_final, "", $string);                    
            }
            
            if (strcmp($string_sanitized, $string) !== 0) { //Se han eliminado caracteres; escribimos en el log
                if ($base64) {
                    $this->grabar_log($logs_attacks, $ip, 'TAGS_STRIPPED', '[' .$methods_options .':' .$a .']', 'XSS_BASE64', $request_uri, $string, $username, $pageoption);
                }else
                {
                    $angle_position = strpos($string, "<");                    
                    if ($angle_position !== false) {
                        $longitud = strlen($string);                                        
                        $string = substr($string, $angle_position, $longitud - $angle_position);                        
                    }            
                    $this->grabar_log($logs_attacks, $ip, 'TAGS_STRIPPED', '[' .$methods_options .':' .$a .']', 'XSS', $request_uri, $string, $username, $pageoption);
                }
                $string = $string_sanitized;    
                $modified = true;
                /* Hemos de cortar la conexi�n, independientemente de lo que tengamos configurado en el par�metro "redirect_after_attack". Esto es necesario porque algunos ataques XSS llegan a producirse, aunque sean detectados, al haber una redirecci�n */
                $this->redirection(403, "", true);
            } else 
            {
                $xss_forbidden_words_array = array("onload","onfocus","autofocus","javascript:","onmouseover","onerror","FSCommand","onAbort","onActivate","onAfterPrint","onAfterUpdate","onBeforeActivate","onBeforeCopy","onBeforeCut","onBeforeDeactivate","onBeforeEditFocus","onBeforePaste","onBeforePrint","onBeforeUnload","onBeforeUpdate","onBegin","onBlur","onBounce","onCellChange","onChange","onClick","onContextMenu","onControlSelect","onCopy","onCut","onDataAvailable","onDataSetChanged","onDataSetComplete","onDblClick","onDeactivate","onDrag","onDragEnd","onDragLeave","onDragEnter","onDragOver","onDragDrop","onDragStart","onDrop","onEnd","onErrorUpdate","onFilterChange","onFinish","onFocusIn","onFocusOut","onHashChange","onHelp","onInput","onKeyDown","onKeyPress","onKeyUp","onLayoutComplete","onLoseCapture","onMediaComplete","onMediaError","onMessage","onMouseDown","onMouseEnter","onMouseLeave","onMouseMove","onMouseOut","onMouseOut","onMouseUp","onMouseWheel","onMove","onMoveEnd","onMoveStart","onOffline","onOnline","onOutOfSync","onPaste","onPause","onPopState","onProgress","onPropertyChange","onReadyStateChange","onRedo","onRepeat","onReset","onResize","onResizeEnd","onResizeStart","onResume","onReverse","onRowsEnter","onRowExit","onRowDelete","onRowInserted","onScroll","onSeek","onSelect","onSelectionChange","onSelectStart","onStart","onStop","onStop","onSyncRestored","onSubmit","onTimeError","onTimeError","onUndo","onUnload","onURLFlip","seekSegmentTime");
                                
                foreach($xss_forbidden_words_array as $word) {
                    if ((is_string($string)) && (!empty($word))) {
                        if (substr_count(strtolower($string), strtolower($word))) {
                            $string_sanitized = preg_replace($word, "", $string);
                            $this->grabar_log($logs_attacks, $ip, 'TAGS_STRIPPED', '[' .$methods_options .':' .$a .']', 'XSS', $request_uri, $string, $username, $pageoption);
                            $modified = true;                                                    
                            /* Hemos de cortar la conexi�n, independientemente de lo que tengamos configurado en el par�metro "redirect_after_attack". Esto es necesario porque algunos ataques XSS llegan a producirse, aunque sean detectados, al haber una redirecci�n */
                            $this->redirection(403, "", true);
                        }
                    }                    
                }                
            }
        }
                        
        /* SQL Injection Prevention */
        if (!$modified) {
            if ($is_admin) {                
                $duplicate_backslashes_exceptions = "*";
                $line_comments_exceptions = "*";
                $if_statement_exceptions = "*";
                $using_integers_exceptions = "*";                
            }
            
            if (!(strstr($duplicate_backslashes_exceptions, $pageoption)) && !(strstr($duplicate_backslashes_exceptions, '*'))) {
                // Prevents duplicate backslashes
                if (PHP_VERSION_ID < 50400 && get_magic_quotes_gpc())
				{
                    $string_sanitized = stripslashes($string);
                    if (strcmp($string_sanitized, $string) !== 0) { //Se han eliminado caracteres; escribimos en el log
                        if ($base64) {
                            $this->grabar_log($logs_attacks, $ip, 'DUPLICATE_BACKSLASHES', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION_BASE64', $request_uri, $string, $username, $pageoption);
                        }else
                        {
                            $this->grabar_log($logs_attacks, $ip, 'DUPLICATE_BACKSLASHES', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION', $request_uri, $string, $username, $pageoption);
                        }
                                        
                        if (strlen($string_sanitized)>0) {
                              $string = $string_sanitized;
                        }
                    }
                }
            }
                            
            if (!(strstr($line_comments_exceptions, $pageoption)) && !(strstr($line_comments_exceptions, '*')) && ($pageoption != 'com_users')) {
                // Line Comments
                $lineComments = array("/--/","/[^\=]#/","/\/\*/","/\*\//");
                $string_sanitized = preg_replace($lineComments, "", $string);
                                                    
                if (strcmp($string_sanitized, $string) !== 0) { //Se han eliminado caracteres; escribimos en el log
                    if ($base64) {
                        $this->grabar_log($logs_attacks, $ip, 'LINE_COMMENTS', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION_BASE64', $request_uri, $string, $username, $pageoption);
                    }else
                    {
                        $this->grabar_log($logs_attacks, $ip, 'LINE_COMMENTS', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION', $request_uri, $string, $username, $pageoption);
                    }
                                    
                    $string = $string_sanitized;
                    $modified = true;
                }
            }
                            
            $sqlpatterns = array("/delete(?=(\s|\+|%20|%u0020|%uff00)).+(from)(?=(\s|\+|%20|%u0020|%uff00))/i","/update(?=(\s|\+|%20|%u0020|%uff00)).+(set)(?=(\s|\+|%20|%u0020|%uff00))/i",
            "/drop(?=(\s|\+|%20|%u0020|%uff00)).+(database|user|table|index)(?=(\s|\+|%20|%u0020|%uff00))/i",
            "/insert(?=(\s|\+|%20|%u0020|%uff00)).+(values|set|select)(?=(\s|\+|%20|%u0020|%uff00))/i", "/union(?=(\s|\+|%20|%u0020|%uff00)).+(select)(?=(\s|\+|%20|%u0020|%uff00))/i",
            "/select(?=(\s|\+|%20|%u0020|%uff00)).+(from|ascii|char|concat)(?=(\s|\+|%20|%u0020|%uff00))/i","/benchmark\(.*\)/i",
            "/md5\(.*\)/i","/sha1\(.*\)/i","/ascii\(.*\)/i","/concat\(.*\)/i","/char\(.*\)/i",
            "/substring\(.*\)/i","/(\s|\+|%20|%u0020|%uff00)(or|and)(\s|\+|%20|%u0020|%uff00)(\w+)(=|<|>|<=|>=)(\w+)/i");                        
                                            
            if ((!(strstr($sql_pattern_exceptions, $pageoption)) || $extension_vulnerable) && !(strstr($sql_pattern_exceptions, '*'))) {                        
                $string_sanitized = preg_replace($sqlpatterns, "", $string);
                        
                if (strcmp($string_sanitized, $string) !== 0) { //Se han eliminado caracteres; escribimos en el log    
                    if ($base64) {
                        $this->grabar_log($logs_attacks, $ip, 'SQL_PATTERN', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION_BASE64', $request_uri, $string, $username, $pageoption);
                    }else
                    {
                        $this->grabar_log($logs_attacks, $ip, 'SQL_PATTERN', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION', $request_uri, $string, $username, $pageoption);
                    }
                                    
                    $string = $string_sanitized;
                    $modified = true;                    
                }    
            }
                            
            //IF Statements
            $ifStatements = array("/if\(.*,.*,.*\)/i");
                                
            if ((!(strstr($if_statement_exceptions, $pageoption)) || $extension_vulnerable) && !(strstr($if_statement_exceptions, '*'))) {    
                $string_sanitized = preg_replace($ifStatements, "", $string);
                        
                if (strcmp($string_sanitized, $string) <> 0) { //Se han eliminado caracteres; escribimos en el log
                    if ($base64) {
                        $this->grabar_log($logs_attacks, $ip, 'IF_STATEMENT', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION_BASE64', $request_uri, $string, $username, $pageoption);
                    }else
                    {
                        $this->grabar_log($logs_attacks, $ip, 'IF_STATEMENT', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION', $request_uri, $string, $username, $pageoption);
                    }                        
                                    
                    $string = $string_sanitized;
                    $modified = true;
                }
            }
                            
            //Using Integers
            $usingIntegers = array("/0x(?=[0-9])/i");
                                
            if (!(strstr($using_integers_exceptions, $pageoption)) && !(strstr($using_integers_exceptions, '*'))) {    
                $string_sanitized = preg_replace($usingIntegers, "", $string);
                                
                if (strcmp($string_sanitized, $string) !== 0) { //Se han eliminado caracteres; escribimos en el log
                    if ($base64) {
                          $this->grabar_log($logs_attacks, $ip, 'INTEGERS', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION_BASE64', $request_uri, $string, $username, $pageoption);
                    }else
                    {
                           $this->grabar_log($logs_attacks, $ip, 'INTEGERS', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION', $request_uri, $string, $username, $pageoption);
                    }
                                    
                        $string = $string_sanitized;
                        $modified = true;
                }
            }
                            
            if (!(strstr($escape_strings_exceptions, $pageoption)) && !(strstr($escape_strings_exceptions, '*')) && ($modified)) {
                $string_sanitized = $this->escapa_string($string);
                            
                if (strcmp($string_sanitized, $string) !== 0) { //Se han a�adido barras invertidas a ciertos caracteres; escribimos en el log                            
                    if ($base64) {
                        $this->grabar_log($logs_attacks, $ip, 'BACKSLASHES_ADDED', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION_BASE64', $request_uri, $string, $username, $pageoption);
                    }else
                    {
                        $this->grabar_log($logs_attacks, $ip, 'BACKSLASHES_ADDED', '[' .$methods_options .':' .$a .']', 'SQL_INJECTION', $request_uri, $string, $username, $pageoption);
                    }
                                    
                    if (strlen($string_sanitized)>0) {
                        $string = $string_sanitized;
                    }
                }
            }
                                
        }    
                        
        /* LFI Prevention */
        $lfiStatements = array("/\.\.\//","/\?\?\?/");
        if ((!(strstr($lfi_exceptions, $pageoption)) || $extension_vulnerable) && !(strstr($lfi_exceptions, '*'))) {
            if (!$modified) {                        
                $string_sanitized = preg_replace($lfiStatements, '', $string);
                if (strcmp($string_sanitized, $string) !== 0) { //Se han eliminado caracteres; escribimos en el log
                    if ($base64) {
                             $this->grabar_log($logs_attacks, $ip, 'LFI', '[' .$methods_options .':' .$a .']', 'LFI_BASE64', $request_uri, $string, $username, $pageoption);
                    }else
                    {
                            $this->grabar_log($logs_attacks, $ip, 'LFI', '[' .$methods_options .':' .$a .']', 'LFI', $request_uri, $string, $username, $pageoption);
                    }
                                    
                    $string = $string_sanitized;
                    $modified = true;
                }
            }
        }
                        
                /* Header and user-agent check */
        if ((!$modified) && ($check_header_referer)) {
            $modified = $this->check_header_and_user_agent($logs_attacks, $user, $user_agent, $referer, $ip, $methods_options, $a, $request_uri, $sqlpatterns, $ifStatements, $usingIntegers, $lfiStatements, $username, $pageoption);
        }
    }
    
    /* Funci�n para 'sanitizar' un string. Devolvemos el string "sanitizado" y modificamos la variable "modified" si se ha modificado el string */
    function cleanQuery($ip,$string,$methods_options,$a,$request_uri,&$modified,$check,$logs_attacks,$option)
    {
                
        $app = JFactory::getApplication();
        $is_admin = $app->isClient('administrator');
                
        $pageoption = $option;
            
        if (is_array($string)) {                
            foreach ($string as $string)
            {                        
                if ((!(is_array($string))) && (mb_strlen($string)>0) && ($pageoption != '')) {                    
                    $this->apply_filters($ip, $string, $methods_options, $a, $request_uri, $modified, $check, $logs_attacks, $option);                        
                }
            }
        } else
        {
            if ((!(is_array($string))) && (mb_strlen($string)>0) && ($pageoption != '')) {
                $this->apply_filters($ip, $string, $methods_options, $a, $request_uri, $modified, $check, $logs_attacks, $option);
            }                
        }
        
        return $string;
    }
    
    /* Funci�n que chequea el 'Header' y el 'user-agent' en busca de ataques */
    function check_header_and_user_agent($logs_attacks,$user,$user_agent,$referer,$ip,$methods_options,$a,$request_uri,$sqlpatterns,$ifStatements,$usingIntegers,$lfiStatements,$username,$pageoption)
    {
        $modified = false; 
        
        if ($user->guest) {
            /*****
    
       * User-agent checks 
*****/
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                /* XSS Prevention in USER_AGENT*/
                //Strip html and php tags from string
                $header_sanitized = strip_tags($user_agent);
                                
                if (strcmp($header_sanitized, $user_agent) !== 0) { //Se han eliminado caracteres; escribimos en el log
                    $this->grabar_log($logs_attacks, $ip, 'TAGS_STRIPPED', '[' .$methods_options .':' .$a .']', 'USER_AGENT_MODIFICATION', $request_uri, $user_agent, $username, $pageoption);
                    
                    $modified = true;
                }
                /* SQL Injection in USER_AGENT*/
                $header_sanitized = preg_replace($sqlpatterns, "", $user_agent);
                if (strcmp($header_sanitized, $user_agent) !== 0) { //Se han eliminado caracteres; escribimos en el log
                    $this->grabar_log($logs_attacks, $ip, 'SQL_PATTERN', '[' .$methods_options .':' .$a .']', 'USER_AGENT_MODIFICATION', $request_uri, $user_agent, $username, $pageoption);
                    
                    $modified = true;
                }
                /* SQL Injection in USER_AGENT*/
                $header_sanitized = preg_replace($ifStatements, "", $user_agent);
                if (strcmp($header_sanitized, $user_agent) !== 0) { //Se han eliminado caracteres; escribimos en el log
                    $this->grabar_log($logs_attacks, $ip, 'IF_STATEMENT', '[' .$methods_options .':' .$a .']', 'USER_AGENT_MODIFICATION', $request_uri, $user_agent, $username, $pageoption);
                    
                    $modified = true;
                } 
                /* SQL Injection in USER_AGENT*/
                $header_sanitized = preg_replace($usingIntegers, "", $user_agent);
                if (strcmp($header_sanitized, $user_agent) !== 0) { //Se han eliminado caracteres; escribimos en el log
                    $this->grabar_log($logs_attacks, $ip, 'INTEGERS', '[' .$methods_options .':' .$a .']', 'USER_AGENT_MODIFICATION', $request_uri, $user_agent, $username, $pageoption);
                    
                    $modified = true;
                } 
                /* LFI in USER_AGENT*/
                $header_sanitized = preg_replace($lfiStatements, '', $user_agent);
                if (strcmp($header_sanitized, $user_agent) !== 0) { //Se han eliminado caracteres; escribimos en el log
                    $this->grabar_log($logs_attacks, $ip, 'LFI', '[' .$methods_options .':' .$a .']', 'USER_AGENT_MODIFICATION', $request_uri, $user_agent, $username, $pageoption);
                    
                    $modified = true;
                }
            }
            /*****
    
       * Referer checks 
*****/
            if (!$modified) {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    /* XSS Prevention in REFERER*/
                    //Strip html and php tags from string
                    $header_sanitized = strip_tags($referer);
                    if (strcmp($header_sanitized, $referer) !== 0) { //Se han eliminado caracteres; escribimos en el log
                        $this->grabar_log($logs_attacks, $ip, 'TAGS_STRIPPED', '[' .$methods_options .':' .$a .']', 'REFERER_MODIFICATION', $request_uri, $referer, $username, $pageoption);
                    
                        $modified = true;
                    } 
                    /* SQL Injection in REFERER*/
                    $header_sanitized = preg_replace($sqlpatterns, "", $referer);
                    if (strcmp($header_sanitized, $referer) !== 0) { //Se han eliminado caracteres; escribimos en el log
                        $this->grabar_log($logs_attacks, $ip, 'SQL_PATTERN', '[' .$methods_options .':' .$a .']', 'REFERER_MODIFICATION', $request_uri, $referer, $username, $pageoption);
                    
                        $modified = true;
                    }
                    /* SQL Injection in REFERER*/
                    $header_sanitized = preg_replace($ifStatements, "", $referer);
                    if (strcmp($header_sanitized, $referer) !== 0) { //Se han eliminado caracteres; escribimos en el log
                        $this->grabar_log($logs_attacks, $ip, 'IF_STATEMENT', '[' .$methods_options .':' .$a .']', 'REFERER_MODIFICATION', $request_uri, $referer, $username, $pageoption);
                    
                        $modified = true;
                    } 
                    /* LFI in REFERER*/
                    $header_sanitized = preg_replace($lfiStatements, '', $referer);
                    if (strcmp($header_sanitized, $referer) !== 0) { //Se han eliminado caracteres; escribimos en el log
                        $this->grabar_log($logs_attacks, $ip, 'LFI', '[' .$methods_options .':' .$a .']', 'REFERER_MODIFICATION', $request_uri, $referer, $username, $pageoption);
                    
                        $modified = true;
                    }
                }
            }
        }
        return $modified;
    }
    
    /* Funci�n para contar el n�mero de palabras "prohibidas" de un string*/
    function second_level($request_uri,$string,$a,&$found,$option)
    {
        $occurrences=0;
        $string_sanitized=$string;
        $application = JFactory::getApplication();
        $user = JFactory::getUser();
        $dbprefix = $application->getCfg('dbprefix');
        $pageoption='';
        $existe_componente = false;
        $extension_vulnerable = false;
        
        $app = JFactory::getApplication();
        $is_admin = $app->isClient('administrator');
        
        // Consultamos si hemos de aplicar las reglas al usuario en funci�n de su pertenencia a grupos.
        $user = JFactory::getUser();
        $apply_rules_to_user = $this->check_rules($user);
        
        $pageoption = $option;
        
        // Chequeamos si hemos de excluir los componentes vulnerables de las excepciones
        $exclude_exceptions_if_vulnerable = $this->pro_plugin->getValue('exclude_exceptions_if_vulnerable', 1, 'pro_plugin');
        
        // Si hemos podido extraer el componente implicado en la peticion, vemos si la versin instalada es vulnerable
        if ((!empty($option)) && ($exclude_exceptions_if_vulnerable)) {
            $extension_vulnerable = $this->check_extension_vulnerable($option);                                        
        }
        
        /* Excepciones */
        $second_level_exceptions = $this->pro_plugin->getValue('second_level_exceptions', '', 'pro_plugin');
        
        /* Lista de palabras sospechosas */
        $second_level_words = $this->pro_plugin->getValue('second_level_words', '', 'pro_plugin');
        
        // Desde la versi�n 3.1.6 la lista de palabras sospechosas se codifica en base64 para evitar problemas con una regla de mod_security.
        if (substr_count($second_level_words, ",") < 2) {    
            $second_level_words = base64_decode($second_level_words);
        }
                    
        if ($apply_rules_to_user) { 
            if ((!($is_admin)) && ($pageoption != '') && !(is_array($string))) {  // No estamos en la parte administrativa
                if (!(strstr($second_level_exceptions, $pageoption)) || $extension_vulnerable) {
                    /* SQL Injection Prevention */
                    // Prevents duplicate backslashes
                    if (PHP_VERSION_ID < 50400 && get_magic_quotes_gpc())
					{
                        $string_sanitized = stripslashes($string);
                    }
                    // Line Comments
                    $lineComments = array("/--/","/[^\=]#/","/\/\*/","/\*\//");
                    $string_sanitized = preg_replace($lineComments, "", $string_sanitized);
                
                    $string_sanitized = $this->escapa_string($string);
                                                            
                    $suspect_words = explode(',', $second_level_words);
                    foreach ($suspect_words as $word)
                    {
                        if ((is_string($string_sanitized)) && (!empty($word)) && (!empty($string_sanitized))) {
                            if (substr_count(strtolower($string_sanitized), strtolower($word))) {
                                if (empty($found)) {
                                    $found .= $word;
                                } else
                                {
                                    $found .= ', ' .$word;
                                }                                
                                $occurrences++;
                            }
                        }
                    }
                }
            }
        }
        return $occurrences;
        
    }
        
        
    /* Funci�n para chequear si una ip pertenece a una lista din�mica almacenada en una BBDD */
    function chequear_ip_en_lista_dinamica($ip,$blacklist_counter)
    {
        // Creamos el nuevo objeto query
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
    
        // Chequeamos si la IP tiene un formato v�lido
        $ip_valid = filter_var($ip, FILTER_VALIDATE_IP);
        
        // Sanitizamos las entradas
        $ip = $db->escape($ip);
                        
        // Validamos si el valor devuelto es una direcci�n v�lida
        if ((!empty($ip)) && ($ip_valid)) {
            // Construimos la consulta
            try 
            {
                $query = "SELECT COUNT(*) from `#__securitycheckpro_dynamic_blacklist` WHERE (`ip` = '{$ip}' AND `counter` >= {$blacklist_counter})" ;                
                $db->setQuery($query);
                $result = $db->loadResult();                
            } catch (Exception $e)
            {
                return false;
            }            
                    
            if ($result) {
                return true;
            } else
            {
                return false;
            }
        } else {
            return false;
        }
        
    }
    
    /* Si el tiempo transcurrido desde que se grab� la entrada supera el establecido en el plugin, eliminamos esa entrada de la base de datos */
    function pasar_a_historico($counter_time)
    {
    
        // Creamos el nuevo objeto query
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        // Sanitizamos la entrada
        (int) $counter_time = $db->escape($counter_time);
        
        if (is_numeric($counter_time)) {
            $query = "DELETE FROM `#__securitycheckpro_dynamic_blacklist` WHERE (DATE_ADD(`timeattempt`, INTERVAL {$counter_time} SECOND)) < NOW();";
            $db->setQuery($query);
            $db->execute();            
        }

        
        
    }
    
    /* Funci�n que a�ade una IP a la lista negra din�mica */
    function actualizar_lista_dinamica($attack_ip)
    {
    
        // Creamos el nuevo objeto query
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $dynamic_blacklist = $this->pro_plugin->getValue('dynamic_blacklist', 1, 'pro_plugin');
        
        // Chequeamos si la IP tiene un formato v�lido
        $ip_valid = filter_var($attack_ip, FILTER_VALIDATE_IP);
        
        // Sanitizamos la entrada
        $attack_ip = $db->escape($attack_ip);
                
        // Validamos si el valor devuelto es una direcci�n IP v�lida y la lista negra din�mica est� habilitada
        if ((!empty($attack_ip)) && ($ip_valid) && ($dynamic_blacklist)) {
            try 
            {                
                $query = "INSERT INTO `#__securitycheckpro_dynamic_blacklist` (`ip`, `timeattempt`) VALUES ('{$attack_ip}', NOW()) ON DUPLICATE KEY UPDATE `timeattempt` = NOW(), `counter` = `counter` + 1;";
                
                $db->setQuery($query);        
                $result = $db->execute();
                
                include_once JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'firewallconfig.php';
                $firewall_model = new SecuritycheckprosModelFirewallConfig();
                
                // Chequeamos si hemos de a�adir la ip al fichero que ser� consumido por el plugin 'connect'
                $control_center_enabled = $firewall_model->control_center_enabled();
            
                if ($control_center_enabled) {
                    $firewall_model->a�adir_info_control_center($attack_ip, 'dynamic_blacklist');
                }
            } catch (Exception $e)
            {                
            }            
            
        } else
        {
            return false;
        }
    }
    
    /* Funci�n que chequea la sesi�n para usar la funcionalidad otp de Securitycheck Pro */
    private function check_environment($session_username)
    {
        $is_ok = true;
        
        $app = JFactory::getApplication();
        
        //Si no estamos en el backend salimos
        if (!$app->isClient('administrator')) {            
            $is_ok = false;
        }
        
        // El usuario logado debe coincidir con el almacenado en la sesi�n o ser el invitado (antes de logarse en el backend)
        $currentUser = JFactory::getUser();
                
        if (!$currentUser->guest && (strtoupper($currentUser->username) != strtoupper($session_username))) {
            $is_ok = false;
        }
        
        return $is_ok;
    }
    
    /* Funci�n que obtiene el id de un usuario a trav�s de la variable pasada como argumento. El usuario no puede estar bloqueado. */
    private function get_user_id($username)
    {
        
        if (empty($username)) {
            return null;
        }
        
        try
        {
            // Get a database object
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from('#__users')
                ->where('username=' . $db->quote($username))
                ->where($db->qn('block') . ' = ' . $db->q(0));

            $db->setQuery($query);
            $userID = $db->setQuery($query)->loadResult();
        } catch (Exception $e)
        {
            return null;
        }
        
        return $userID;
        
    }
    
    /* Funci�n que chequea si la url usa la funci�n otp de Securitycheck Pro para desbloquear el acceso */
    private function check_otp_params()
    {
        $is_otp = false;
        
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $otp_enabled = $params->get('otp', 1);
        
        // Si la funcionalidad OTP est� habilitada realizamos las secuencia
        if ($otp_enabled) {        
            $session = JFactory::getSession();                
            $session_username = $session->get('otp_username', '', 'com_securitycheckpro');
                    
            if (empty($session_username)) {
                    
                $jinput = JFactory::getApplication()->input;
                $url_username = trim($jinput->get('username', '', 'username'));
                $url_otp = $jinput->get('otp', '', 'string');
                        
                $userID = self::get_user_id($url_username);
                    
                if (!empty($userID)) {                
                    $user = JFactory::getUser($userID);
                    if ($user->authorise('core.admin')) {
                        $check = self::match_otps($userID, $url_otp);                                
                        if ($check) {                    
                            $session->set('otp_username', $url_username, 'com_securitycheckpro');
                        }
                    }
                }
            } else 
            {
                $is_ok = self::check_environment($session_username);                
                if ($is_ok) {
                    $is_otp = true;
                }
            }
        }
        
        return $is_otp;
    
    }
    
    /* Funci�n que chequea si el otp introducido por el usuario coincide con el de Google */
    private function match_otps($userID,$url_otp)
    {
        
        if (empty($url_otp)) {
            // The url_otp is empty
            return false;
        }
        
        $methods = JAuthenticationHelper::getTwoFactorMethods();

        if (count($methods) <= 1) {
            // No two factor authentication method is enabled
            return false;
        }
        
        if (version_compare(JVERSION, '3.20', 'lt')) {
            JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models', 'UsersModel');
            // @var UsersModelUser $model
            $model = JModelLegacy::getInstance('User', 'UsersModel', array('ignore_request' => true));
        } else
        {
            $model = new UserModel(array('ignore_request' => true));
        }
                            
        $otpConfig = $model->getOtpConfig($userID);
        
        // Check if the user has enabled two factor authentication
        if (empty($otpConfig->method) || ($otpConfig->method === 'none')) {
            return false;
        } else
        {
            if ($otpConfig->method === 'totp') {
                // El usuario tiene configurado Google Authenticator TOTP Plugin como 2FA
                // Create a new TOTP class with Google Authenticator compatible settings
                $totp = new FOFEncryptTotp(30, 6, 10);
                $code = $totp->getCode($otpConfig->config['code']);
                                    
                $check = $code === $url_otp;
                
                /*
                * If the check fails, test the previous 30 second slot. This allow the
                * user to enter the security code when it's becoming red in Google
                * Authenticator app (reaching the end of its 30 second lifetime)
                */
                if (!$check) {
                    $time = time() - 30;
                    $code = $totp->getCode($otpConfig->config['code'], $time);
                    $check = $code === $url_otp;
                }

                /*
                * If the check fails, test the next 30 second slot. This allows some
                * time drift between the authentication device and the server
                */
                if (!$check) {
                    $time = time() + 30;
                    $code = $totp->getCode($otpConfig->config['code'], $time);
                    $check = $code === $url_otp;
                }
                
                return $check;
            } else if ($otpConfig->method === 'yubikey') {
                // El usuario tiene configurado Yubikey como 2FA
                // Check if the Yubikey starts with the configured Yubikey user string
                $yubikey_valid = $otpConfig->config['yubikey'];
                $yubikey       = substr($url_otp, 0, -32);

                $check = $yubikey === $yubikey_valid;

                if ($check) {
                    include_once JPATH_ROOT. DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'twofactorauth' . DIRECTORY_SEPARATOR . 'yubikey' . DIRECTORY_SEPARATOR . 'yubikey.php';
                    $yubimodel = new PlgTwofactorauthYubikey();
                    $check = $yubimodel->validateYubikeyOtp($url_otp);
                }
                return $check;                
            }                
        }                                
                
    }
    
    /* Acciones a realizar si la IP est� en la lista negra din�mica*/
    function acciones_lista_negra_dinamica($dynamic_blacklist_time,$attack_ip,$dynamic_blacklist_counter,$logs_attacks,$request_uri,$not_applicable)
    {
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        
        /* Actualizamos la lista din�mica */
        $this->pasar_a_historico($dynamic_blacklist_time);
        
        $aparece_lista_negra_dinamica = $this->chequear_ip_en_lista_dinamica($attack_ip, $dynamic_blacklist_counter);
        $add_access_attempts_logs = $this->pro_plugin->getValue('add_access_attempts_logs', 0, 'pro_plugin');
                        
        if ($aparece_lista_negra_dinamica) {
            
            // Url con otp de Securitycheck Pro?
            $is_otp = self::check_otp_params();
                
            if (!$is_otp) {            
                // Grabamos una entrada en el log con el intento de acceso de la ip prohibida
                if ($add_access_attempts_logs) {
                    $access_attempt = $lang->_('COM_SECURITYCHECKPRO_ACCESS_ATTEMPT');
                    $this->grabar_log($logs_attacks, $attack_ip, 'IP_BLOCKED_DINAMIC', $access_attempt, 'IP_BLOCKED_DINAMIC', $request_uri, $not_applicable, '---', '---');
                }
                                
                // Redirecci�n a nuestra p�gina de "Prohibido" 
                $error_403 = $lang->_('COM_SECURITYCHECKPRO_403_ERROR');
                $this->redirection(403, $error_403, true);
            }
        }        
    }
    
    /* Acciones a realizar si la ip est� en la lista negra*/
    function acciones_lista_negra($logs_attacks,$attack_ip,$access_attempt,$request_uri,$not_applicable)
    {
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        $not_applicable = $lang->_('COM_SECURITYCHECKPRO_NOT_APPLICABLE');
        
        $add_access_attempts_logs = $this->pro_plugin->getValue('add_access_attempts_logs', 0, 'pro_plugin');
        
        // Url con otp de Securitycheck Pro?
        self::check_otp_params();
        
        // Url con otp de Securitycheck Pro?
        $is_otp = self::check_otp_params();
                
        if (!$is_otp) {                
            // Grabamos una entrada en el log con el intento de acceso de la ip prohibida si est� seleccionada la opci�n para ello
            if ($add_access_attempts_logs) {
                $access_attempt = $lang->_('COM_SECURITYCHECKPRO_ACCESS_ATTEMPT');
                $this->grabar_log($logs_attacks, $attack_ip, 'IP_BLOCKED', $access_attempt, 'IP_BLOCKED', $request_uri, $not_applicable, '---', '---');
            }
                
            // Redirecci�n a nuestra p�gina de "Prohibido"
            $error_403 = $lang->_('COM_SECURITYCHECKPRO_403_ERROR');
            $this->redirection(403, $error_403, true);    
        }
    }
    
    /* Opciones de redirecci�n: p�gina de error (de Joomla o personalizada) o rechazar la conexi�n. El par�metro blacklist indica si venimos de una lista negra; en ese caso, no podemos hacer la redirecci�n ya que entrar�amos en un bucle infinito. Lo que hacemos es mostrar el c�digo que haya establecido el administrador */
    function redirection($code,$message,$blacklist=false)
    {
        $redirect_after_attack = $this->pro_plugin->getValue('redirect_after_attack', 1, 'pro_plugin');
        $redirect_options = $this->pro_plugin->getValue('redirect_options', 1, 'pro_plugin');
        $redirect_url = $this->pro_plugin->getValue('redirect_url', '', 'pro_plugin');
        $custom_code = $this->pro_plugin->getValue('custom_code', 'The webmaster has forbidden your access to this site', 'pro_plugin');
        
        $app = JFactory::getApplication();
        $is_admin = $app->isClient('administrator');
                
        if ($redirect_after_attack) {            
            // Tenemos que redigir
            if (!$blacklist ) {
                // Si estamos en la parte administrativa nunca hemos de hacer la redirecci�n para evitar vulnerabilidades
                if ($is_admin) {
                    // Mostramos el c�digo establecido por el administrador, una cabecera de Forbidden y salimos                    
                    echo $custom_code;
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }
                
                if ($redirect_options == 1) {
                    // Redirigimos a la p�gina de error de Joomla
                    JFactory::getApplication()->enqueueMessage($message, 'error');
                } else if ($redirect_options == 2) {
                    // Redirigimos a la p�gina establecida por el administrador
                    JFactory::getApplication()->redirect(JURI::root() . $redirect_url);    
                }
                    
            } else 
            {
                // Mostramos el c�digo establecido por el administrador, una cabecera de Forbidden y salimos                    
                echo $custom_code;
                header('HTTP/1.1 403 Forbidden');
                exit;
            }            
        } else 
        { // Rechazamos la conexi�n mostrando el c�digo establecido por el administrador, una cabecera de Forbidden y salimos
            echo $custom_code;
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    
    }
    
    /* Acciones a realizar si la ip est� no est� en ninguna de las listas*/
    function acciones_no_listas($methods,$attack_ip,$methods_options,$request_uri,$check_base_64,$logs_attacks,$secondlevel,$mode)
    {
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        
        // Obtenemos los valores del plugin para la protecci�n de sesi�n del usuario
        $session_hijack_protection = $this->pro_plugin->getValue('session_hijack_protection', 1, 'pro_plugin');
        $session_protection_active = $this->pro_plugin->getValue('session_protection_active', 1, 'pro_plugin');
                
        /* Protecci�n de la sesi�n del usuario y contra secuestros de sesi�n */
        if ($session_protection_active || $session_hijack_protection) {
            $this->sesiones_activas($logs_attacks, $attack_ip, $request_uri, $session_protection_active, $session_hijack_protection);
        }
        // Consultamos si hemos de aplicar las reglas al usuario en funci�n de su pertenencia a grupos.
        $user = JFactory::getUser();
        $apply_rules_to_user = $this->check_rules($user);
                
        if ($apply_rules_to_user) {            
            foreach(explode(',', $methods) as $methods_options)
            {
                switch ($methods_options)
                {
                case 'GET':
                    $method = $_GET;
                    break;
                case 'POST':
                    $method = $_POST;
                    break;
                case 'REQUEST':
                       $method = $_REQUEST;
                    break;                        
                }
                
                foreach($method as $a => &$req)
                {
                
                    if(is_numeric($req)) { continue;
                    }
                                        
                    $modified = false;
                        
                    $option = $this->get_component();

                    $req = $this->cleanQuery($attack_ip, $req, $methods_options, $a, $request_uri, $modified, $check_base_64, $logs_attacks, $option);
                    
                    if ($modified) {
                        /* Actualizamos la lista negra din�mica */
                        $this->actualizar_lista_dinamica($attack_ip);
                            
                        if ($mode) { // Modo estricto: redireccion
                            /* Redirecci�n a nuestra p�gina de "Hacking Attempt" */                            
                            $error_400 = $lang->_('COM_SECURITYCHECKPRO_400_ERROR');
                            $this->redirection(400, $error_400);                                            
                        } // Modo alerta: no hacemos nada ya que la funci�n 'cleanQuery' sanitiza el string
                    } else if ($secondlevel) {  // Second level protection
                        // N� m�ximo de palabras sospechosas
                        $second_level_limit_words = intval($this->pro_plugin->getValue('second_level_limit_words', 3, 'pro_plugin'));
                        $words_found='';
                        $num_keywords = $this->second_level($request_uri, $req, $a, $words_found, $option);
                        if ($num_keywords >= $second_level_limit_words) {
                              /* Actualizamos la lista negra din�mica */
                              $this->actualizar_lista_dinamica($attack_ip);                        
                              $this->grabar_log($logs_attacks, $attack_ip, 'FORBIDDEN_WORDS', $words_found, 'SECOND_LEVEL', $request_uri, $req, $user->username, $option);
                                
                              $error_401 = $lang->_('COM_SECURITYCHECKPRO_401_ERROR');
                              $this->redirection(401, $error_401);
                        }
                    }
                }
            }
        }
    }
    
    /*  Funci�n para mandar correos electr�nicos */
    function mandar_correo($alerta)
    {
        // Variables del correo electr�nico  y l�mite de correos a enviar cada d�a
        $subject = $this->pro_plugin->getValue('email_subject', '', 'pro_plugin');
        $body = $this->pro_plugin->getValue('email_body', '', 'pro_plugin');
        $email_add_applied_rule = $this->pro_plugin->getValue('email_add_applied_rule', 1, 'pro_plugin');
        $email_to = $this->pro_plugin->getValue('email_to', '', 'pro_plugin');
        $to = explode(',', $email_to);
        $email_from_domain = $this->pro_plugin->getValue('email_from_domain', '', 'pro_plugin');
        $email_from_name = $this->pro_plugin->getValue('email_from_name', '', 'pro_plugin');
        $from = array($email_from_domain,$email_from_name);
        $email_limit = $this->pro_plugin->getValue('email_max_number', 20, 'pro_plugin');
        $today = date("Y-m-d");
        $send = true;
        
        // Consultamos el n�mero de correos mandados
        $db = JFactory::getDBO();
        
        $query = "UPDATE `#__securitycheckpro_emails` SET envoys=0, send_date='{$today}' WHERE (send_date < '{$today}')";
        $db->setQuery($query);
        $db->execute();
        
        
        $query = "SELECT envoys FROM `#__securitycheckpro_emails` WHERE (send_date = '{$today}')";
        $db->setQuery($query);
        (int) $envoys = $db->loadResult();
        
        if ($envoys < $email_limit) {  // No se ha alcanzado el l�mite m�ximo de emails por d�a
            /* Cargamos el lenguaje del sitio */
            $lang = JFactory::getLanguage();
            $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
                            
            // A�adimos la regla aplicada al cuerpo del correo
            if ($email_add_applied_rule) {
                $body = $body . '<br />' . $alerta;
            }
        
            try 
            {
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
            } catch (Exception $e)
            {
                $send = false;
            }
                        
            if ($send !== true) {
                //$this->grabar_log_propio("Error al enviar un email: " . $send->message);
            }else
            {
                $db = JFactory::getDBO();
                $query = "UPDATE `#__securitycheckpro_emails` SET envoys=envoys+1 WHERE (send_date = '{$today}')";
                $db->setQuery($query);
                $db->execute();
            }
        }
    }
    
    /* Chequea la direcci�n ip y el user-agent de una sesi�n activa para comprobar que no ha habido ninguna modificaci�n */
    protected function chequeo_suplantacion($user_id)
    {
        // Obtenemos los valores necesarios
        $changed = false;
        $ip = $this->get_ip();
		
		$session_hijack_protection_what_to_check = $this->pro_plugin->getValue('session_hijack_protection_what_to_check', 1, 'pro_plugin');
        
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $db = JFactory::getDBO();
        
        // Obtenemos el id del usuario logado
        $query = "SELECT * FROM `#__securitycheckpro_sessions` WHERE (userid = '{$user_id}')";
        $db->setQuery($query);
        $user_data = $db->loadRow();        
                                
        if (!is_null($user_data)) {
			if ( $session_hijack_protection_what_to_check == 1 )
			{
				if ((strcmp($user_data[3], $ip) !== 0) || (strcmp($user_data[4], $user_agent) !== 0)) {
					 // Han cambiado la direcci�n IP o el User-agent
					$changed = true;
				}
			} else if ( $session_hijack_protection_what_to_check == 2 )
			{
				if ((strcmp($user_data[3], $ip) !== 0) && (strcmp($user_data[4], $user_agent) !== 0)) {
					 // Han cambiado tanto la direcci�n IP como el User-agent                
					$changed = true;
				}
			}	
            
        } else { //No hay datos (esto, en teor�a, no deber�a ser posible); devolvemos el valor 'false' para evitar falsos positivos
            $changed = false;
        }
        
        return $changed;
        
    }
    
    /*  Funci�n que chequea el n�mero de sesiones activas del usuario y, si existe m�s de una, toma el comportamiento pasado como argumento*/
    protected function sesiones_activas($logs_attacks,$attack_ip,$request_uri,$session_protection_active,$session_hijack_protection)
    {
        
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        
        // Chequeamos si la opci�n de compartir sesiones est� activa; en este caso no aplicaremos esta opci�n para evitar una denegaci�n de entrada
        $params          = JFactory::getConfig();        
        $shared_session_enabled = $params->get('shared_session');
        
        if ($shared_session_enabled) {
            return;
        }
        
        // Cargamos los grupos a los que se ha de aplicar la protecci�n; por defecto se aplica al grupo Super Users, con un id igual a 8 (el valor por defecto debe estar en un array)
        $session_protection_groups = $this->pro_plugin->getValue('session_protection_groups', array('0' => '8'), 'pro_plugin');
                
        // Variable que indicar� si el usuario logado pertenece a un grupo al que haya que aplicar la protecci�n
        $apply_to_user = false;
                
        $user = JFactory::getUser();
        $user_id = (int) $user->id;
        $user_groups = $user->groups;
                
        // Creamos el nuevo objeto query
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
                
        if ($user->guest) {
            /* El usuario no se ha logado; no hacemos nada */                        
        } else 
        {            
            /* En alg�n caso no se pueden determinar los grupos a los que pertenece el usuario. Controlamos que la variable no est� vac�a */
            if (!is_null($user_groups)) {
                // Chequeamos si el usuario pertenece a un grupo al que haya que aplicar la protecci�n
                foreach ($session_protection_groups as $group)
                {
                    $included = in_array($group, $user_groups);
                    if ($included) {
                        $apply_to_user = true;
                        break;
                    }            
                }
            }
                                    
            // Construimos la consulta
            $query = "SELECT COUNT(*) from `#__session` WHERE (userid = {$user_id})" ;            
            $db->setQuery($query);
            $result = $db->loadResult();
                        
            if (($result > 1) && ($apply_to_user)) {  // Ya existe m�s de una sesi�n activa del usuario y el usuario est� incluido en un grupo al que hay que aplicar la protecci�n                
                if ($session_protection_active) {
                    /*Cerramos todas las sesiones activas del usuario, tanto del frontend (clientid->0) como del backend (clientid->1); este c�digo es necesario porque no queremos modificar los archivos de Joomla , pero esta comprobaci�n podr�a incluirse en la funci�n onUserLogin*/
                    $mainframe= JFactory::getApplication();
                    $mainframe->logout($user_id, array("clientid" => 0));
                    $mainframe->logout($user_id, array("clientid" => 1));
                    
                    $session_protection_description = $lang->_('COM_SECURITYCHECKPRO_SESSION_PROTECTION_DESCRIPTION');
                    $username = $lang->_('COM_SECURITYCHECKPRO_USERNAME');
                    
                    // Grabamos el log correspondiente...
                    $this->grabar_log($logs_attacks, $attack_ip, 'SESSION_PROTECTION', $session_protection_description, 'SESSION_PROTECTION', $request_uri, $username .$user->username, $user->username, '---');
                    
                    // ... y redirigimos la petici�n para realizar las acciones correspondientes
                    $session_protection_error = $lang->_('COM_SECURITYCHECKPRO_SESSION_PROTECTION_ERROR');
                    $this->redirection(403, $session_protection_error);
                }    
            } else if (($result == 1) && ($apply_to_user)) {
                //Existe una sesi�n activa del usuario; comprobamos que no ha sido suplantada
                if ($session_hijack_protection) {
                    $session_hijacked = $this->chequeo_suplantacion($user_id);                    
                    if ($session_hijacked) {                        
                        $session_hijack_attempt_description = $lang->_('COM_SECURITYCHECKPRO_SESSION_HIJACK_ATTEMPT_DESCRIPTION');
                        $username = $lang->_('COM_SECURITYCHECKPRO_USERNAME');
                    
                        // Grabamos el log correspondiente...
                        $this->grabar_log($logs_attacks, $attack_ip, 'SESSION_PROTECTION', $session_hijack_attempt_description, 'SESSION_HIJACK_ATTEMPT', $request_uri, $username .$user->username, $user->username, '---');
                    
                        // ... y redirigimos la petici�n para realizar las acciones correspondientes
                        $session_protection_error = $lang->_('COM_SECURITYCHECKPRO_SESSION_PROTECTION_ERROR');
                        $this->redirection(403, $session_protection_error);
                    }
                }
            }
        }
    }
    
    /* Complementa la funci�n original de Joomla a�adiendo a la tabla `#__securitycheckpro_sessions` informaci�n sobre la sesi�n del usuario */
    function onUserLogin($user, $options = array())
    {
        // Obtenemos un manejador a la BBDD
        $db = JFactory::getDBO();
        
        // Chequeamos los ids de los grupos 'Public' y 'Guest'
        $query = "SELECT id FROM `#__usergroups` WHERE title='Public'";
        $db->setQuery($query);
        (int) $public_group_id = $db->loadResult();
        
        $query = "SELECT id FROM `#__usergroups` WHERE title='Guest'";
        $db->setQuery($query);
        (int) $guest_acl_security = $db->loadResult();        
        
        // Obtenemos la longitud de la clave que tenemos que generar
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $check_acl_security = $params->get('check_acl_security', 1);

        if ($check_acl_security == 1) {
            $app = JFactory::getApplication();    
            
            //core.login.site, core.login.admin, core.login.offline, core.admin, core.manage, core.create, core.delete, core.edit, core.edit.state, core.edit.own
            $permissions_to_check = array (
            'core.login.site'    => 'JACTION_LOGIN_SITE',
            'core.login.admin'    => 'JACTION_LOGIN_ADMIN',
            'core.login.offline'    =>    'JACTION_LOGIN_OFFLINE',
            'core.admin'    =>    'JACTION_ADMIN_GLOBAL',
            'core.manage'    =>    'JACTION_MANAGE',
            'core.create'    =>    'JACTION_CREATE',
            'core.delete'    =>    'JACTION_DELETE',
            'core.edit'    =>    'JACTION_EDIT',
            'core.edit.state'    =>    'JACTION_EDITSTATE',
            'core.edit.own'    =>    'JACTION_EDITOWN');
            foreach ($permissions_to_check as $key => $value)
            {
                $public_acl = JAccess::checkGroup($public_group_id, $key);
                if ($public_acl) {
                    if (in_array($app->getName(), array('administrator','admin'))) {
                        $app->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_INSECURE_ACL_CONFIG_DETECTED', JText::_('COM_SECURITYCHECKPRO_PUBLIC'), JText::_($value)), 'error');
                    }
                }
                
                $guest_acl = JAccess::checkGroup($guest_acl_security, $key);
                if ($guest_acl) {
                    if (in_array($app->getName(), array('administrator','admin'))) {
                        $app->enqueueMessage(JText::sprintf('COM_SECURITYCHECKPRO_INSECURE_ACL_CONFIG_DETECTED', JText::_('COM_SECURITYCHECKPRO_GUEST'), JText::_($value)), 'error');
                    }
                }
            }            
        }                
        
        // Limpiamos las sesiones no v�lidas
        $this->chequeo_sesiones();
        
        // Obtenemos las entradas
        $username = $db->Quote($db->escape($user['username']));
        $name = $user['username'];
        $session_id = $db->Quote($db->escape($_COOKIE[session_name()]));
        $ip = $this->get_ip();
        $user_agent = $db->Quote($db->escape($_SERVER['HTTP_USER_AGENT']));
        
        // Obtenemos el id del usuario logado
        $query = "SELECT id FROM `#__users` WHERE (username = {$username})";
        $db->setQuery($query);
        $userid = $db->loadResult();
        
        // Insertamos los datos en la tabla 'securitycheckpro_sessions' ignorando los errores de entradas duplicadas
        $query = "INSERT IGNORE INTO `#__securitycheckpro_sessions` (`userid`,  `session_id`, `username`, `ip`, `user_agent`) VALUES ('{$userid}', {$session_id}, {$username}, '{$ip}', {$user_agent})";
        $db->setQuery($query);
        $db->execute();
        
        /* Controlamos el acceso de los administradores al backend */        
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        $not_applicable = $lang->_('COM_SECURITYCHECKPRO_NOT_APPLICABLE');
        
        $email_on_admin_login = $this->pro_plugin->getValue('email_on_admin_login', 0, 'pro_plugin');
        $forbid_admin_frontend_login = $this->pro_plugin->getValue('forbid_admin_frontend_login', 0, 'pro_plugin');
                        
        // Controlamos el acceso al backend
        $app = JFactory::getApplication();
        if (in_array($app->getName(), array('administrator','admin'))) {
            
            // Borramos los logs no necesarios
            $this->delete_logs();
            
            // Chequeamos el estado de las subscripciones
            include_once JPATH_ROOT.'/administrator/components/com_securitycheckpro/library/model.php';
            $model = new SecuritycheckproModel();
            $model->get_subscriptions_status();
                        
            if ($email_on_admin_login) {            
                // Extraemos los datos que se mandar�n por correo
                $ip = $this->get_ip();                               
                $email_subject = $lang->_('COM_SECURITYCHECKPRO_RULE') . $lang->_('COM_SECURITYCHECKPRO_ADMIN_LOGIN_TO_BACKEND') . "<br />" . $lang->_('COM_SECURITYCHECKPRO_USERNAME') . $username . "<br />" . "IP: " . $ip;
                $this->mandar_correo($email_subject);                                        
            }            
        } else 
        {
            // Controlamos el acceso al frontend de los Super usuarios
            if ($forbid_admin_frontend_login) {    
                // El grupo Super Users tiene un id igual a 8 (el valor por defecto debe estar en un array)
                $forbidden_groups = array('0' => '8');
                
                $apply_to_user = false;
                                    
                // Instanciamos un nuevo objeto usuario con la id del usuario logado para obtnere los grupos a los que pertenece
                $user = JFactory::getUser($userid);
                $user_groups = $user->groups;
                                
                // Chequeamos si el usuario pertenece a un grupo al que haya que aplicar la protecci�n
                foreach ($user_groups as $group)
                {
                    $included = in_array($group, $forbidden_groups);
                    if ($included) {
                        $apply_to_user = true;
                        break;
                    }            
                }
                
                if ($apply_to_user) {
                                    
                    $attack_ip = $this->get_ip();        
                    $request_uri = $_SERVER['REQUEST_URI'];
                    $logs_attacks = $this->pro_plugin->getValue('logs_attacks', 1, 'pro_plugin');                    
                    $fordib_frontend_login_description = $lang->_('COM_SECURITYCHECKPRO_FRONTEND_LOGIN_FORBIDDEN');
                    $username_string = $lang->_('COM_SECURITYCHECKPRO_USERNAME');
                                        
                    $mainframe= JFactory::getApplication();
                    // Cerramos la sesi�n del frontend
                    $mainframe->logout($userid, array("clientid" => 0));                    
                    
                    // Grabamos el log correspondiente...
                    $this->grabar_log($logs_attacks, $attack_ip, 'SESSION_PROTECTION', $fordib_frontend_login_description, 'SESSION_PROTECTION', $request_uri, $username_string .$name, $name, '---');
                                                            
                    // ... y redirigimos la petici�n para realizar las acciones correspondientes
                    $this->redirection(403, $fordib_frontend_login_description);
                    
                }                
            }
        }    
        
    }
    
    /* Complementa la funci�n original de Joomla eliminando de la tabla `#__securitycheckpro_sessions` informaci�n sobre la sesi�n del usuario */
    function onUserLogout($user, $options = array())
    {
        
        // Obtenemos un manejador a la BBDD
        $db = JFactory::getDBO();
        
        // Nombre del usuario logado
        $username = $db->Quote($db->escape($user['username']));
                                    
        // Borramos el usuario de la tabla
        $query = "DELETE FROM `#__securitycheckpro_sessions` WHERE (username = {$username})";
        $db->setQuery($query);
        $db->execute();
        
        // Limpiamos las sesiones no v�lidas
        $this->chequeo_sesiones();
    }
    
    /* Funci�n que chequea si existen sesiones de usuario en la tabla `#__securitycheckpro_sessions` que ya no son v�lidas. Esto sucede, por ejemplo, cuando la sesi�n del usuario se cierra por inactividad */
    protected function chequeo_sesiones()
    {
        // Variables que usamos en la funci�n
        $user = JFactory::getUser();
        $user_id = (int) $user->id;
        $db = JFactory::getDBO();
        
        if (!$user->guest) {
        
            $session_id = $db->Quote($db->escape($_COOKIE[session_name()]));
                            
            // Consultamos si existe alguna sesi�n en `#__session` con el mismo 'session_id' que las de la cookie. Eso significa que la sesi�n est� activa
            $query = "SELECT session_id FROM `#__session` WHERE (session_id = {$session_id})";
            $db->setQuery($query);
            $result = $db->loadResult();
                        
            // Si la cookie ya no existe en la tabla  `#__session, significa que no es v�lida. Borramos la entrada en la tabla `#__securitycheckpro_sessions`
            if (is_null($result)) {
                $query = "DELETE IGNORE FROM `#__securitycheckpro_sessions` WHERE (session_id = {$session_id})";
                $db->setQuery($query);
                $db->execute();
            } else
            { 
                /* La cookie existe, por lo que la sesi�n es v�lida. Debemos chequear si la ip de origen y el user-agent de la petici�n actual son los mismos que los almacenados al iniciar la sesi�n.  Lo hacemos en la funci�n sesiones_activas() para evitar lanzarlo cuando no se ha iniciado ninguna sesi�n*/
                
            }
        }
        
        /* Sessions garbage collector */
        // Consultamos todas las sesiones creadas por el plugin.
        $query = "SELECT userid FROM `#__securitycheckpro_sessions`";
        $db->setQuery($query);
        $userids_array = $db->loadColumn();
        
        // Existen sesiones en la tabla `#__securitycheckpro_sessions`. Comprobamos si est�n activas en la tabla `#__sessions`
        if (!(is_null($userids_array))) {
            foreach ($userids_array as $id)
            {
                // Consultamos si existe alguna sesi�n del usuario activa en `#__session`.
                $query = "SELECT session_id FROM `#__session` WHERE (userid = {$id})";
                $db->setQuery($query);
                $result = $db->loadResult();
                // Si no existen sesiones, significa que las existentes en la tabla `#__securitycheckpro_sessions` no son v�lidas. Las borramos.
                if (is_null($result)) {
                    $query = "DELETE IGNORE FROM `#__securitycheckpro_sessions` WHERE (userid = {$id})";
                    $db->setQuery($query);
                    $db->execute();
                }                
            }
        }
    }
    
    /* Funci�n que chequea si las reglas han de aplicarse al usuario pasado como argumento. Se comprobar� la pertenencia a grupos y se aplicar� la configuraci�n de la tabla "#__securitycheckpro_rules" */
    protected function check_rules($user_object)
    {
        
        $apply = false;
        
        if ($user_object->guest) {
            $apply = true;
        } else {
            // Consultamos la variable de sesi�n "apply_rules", que nos indicar� si hay que aplicar las reglas al usuario.
            $mainframe = JFactory::getApplication();
            $apply_rules = $mainframe->getUserState("apply_rules", 'not_set');        
            
            switch ($apply_rules)
            {
            case "not_set": // Si no se ha establecido la variable, lanzamos el procedimiento "set_session_rules", que se encargar� de establecerla.                
                $this->set_session_rules();
                $apply_rules = $mainframe->getUserState("apply_rules", 'not_set');                    
                switch ($apply_rules)
                {
                case "yes":
                    $apply = true;
                    break;
                case "no":
                    $apply = false;
                    break;
                }
                case "yes":
                    $apply = true;
                break;
            case "no":
                $apply = false;
                break;
            }
        }
        
        return $apply;
    }
    
    
    /* Funci�n para establecer en la sesi�n del usuario si hay que aplicarle las reglas del firewall */
    function set_session_rules()
    {
        $apply = "yes";
        
        $db = JFactory::getDBO();
        $user = JFactory::getUser();
                
        foreach ($user->groups as $grupo)
        {
            // Consultamos si hay que aplicar la regla al grupo
            $query = "SELECT rules_applied FROM `#__securitycheckpro_rules` WHERE (group_id = {$grupo})";
            $db->setQuery($query);
            $apply_rule_to_group = $db->loadResult();
                                    
            // Si hay que aplicar la regla, actualizamos la variable '$apply' y abandonamos el bucle
            if (!$apply_rule_to_group) {
                $apply = "no";
                $this->actualizar_rules_log($user, $grupo);
                break;
            }
        }
        
        // Creamos la variable en el entorno del usuario
        $mainframe = JFactory::getApplication();
        $mainframe->SetUserState("apply_rules", $apply);        
    }
    
    /* Funci�n para actualizar los logs de las reglas del firewall */
    function actualizar_rules_log($user,$grupo)
    {
        
        // Inicializamos las variables necesarias
        $ip = 'Not set';
        
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        // Obtenemos el t�tulo del grupo al que se le aplica la excepci�n
        $query = "SELECT title FROM `#__usergroups` WHERE (id = {$grupo})";
        $db->setQuery($query);
        $group_title = $db->loadResult();
        
        // Obtenemos la IP del cliente
        $ip = $this->get_ip();
                
        // Obtenemos un timestamp
        $date = JFactory::getDate();
        
        // Rellenamos el objeto que vamos a insertar en la tabla '#__securitycheckpro_rules_logs'
        $valor = (object) array(
        'ip' => $ip,
        'username' => $user->username,
        'last_entry' => $date->format("Y-m-d H:i:s"),
        'reason' => JText::plural('COM_SECURITYCHECKPRO_RULES_LOGS_REASON', $group_title),
                    );
        $insert_result = $db->insertObject('#__securitycheckpro_rules_logs', $valor, 'id');
        
        // Borramos las entradas con m�s de un mes de antig�edad
        $sql = "DELETE FROM `#__securitycheckpro_rules_logs` WHERE (DATE_ADD(`last_entry`, INTERVAL 1 MONTH)) < NOW();";
        $db->setQuery($sql);
        $db->execute();
        
    }
    
        
    function onAfterInitialise()
    {
        
        $plugin_enabled = false;
        $tables_locked = false;
        
        $db = JFactory::getDBO();
        
        try 
        {
            $query = "SELECT enabled from `#__extensions` WHERE element='securitycheckpro' and type='plugin'" ;            
            $db->setQuery($query);
            $plugin_enabled= $db->loadResult();
        } catch (Exception $e)
        {
            
        }            

        try 
        {
            $query = "SELECT storage_value from `#__securitycheckpro_storage` WHERE storage_key = 'locked'";        
            $db->setQuery($query);
            $tables_locked= $db->loadResult();
        } catch (Exception $e)
        {
            
        }        
        
        // Is the plugin enabled?
        if ($plugin_enabled) {
    
            /* Cargamos el lenguaje del sitio */
            $lang = JFactory::getLanguage();
            $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
            $not_applicable = $lang->_('COM_SECURITYCHECKPRO_NOT_APPLICABLE');
            $access_attempt = $lang->_('COM_SECURITYCHECKPRO_ACCESS_ATTEMPT');

            $methods = $this->pro_plugin->getValue('methods', 'GET,POST,REQUEST', 'pro_plugin');
            $logs_attacks = $this->pro_plugin->getValue('logs_attacks', 1, 'pro_plugin');
            $mode = $this->pro_plugin->getValue('mode', 1, 'pro_plugin');
            $blacklist_ips = $this->pro_plugin->getValue('blacklist', 'pro_plugin');
            $dynamic_blacklist_on = $this->pro_plugin->getValue('dynamic_blacklist', 1, 'pro_plugin');
            $dynamic_blacklist_time = $this->pro_plugin->getValue('dynamic_blacklist_time', 600, 'pro_plugin');
            $dynamic_blacklist_counter = $this->pro_plugin->getValue('dynamic_blacklist_counter', 5, 'pro_plugin');
            $whitelist_ips = $this->pro_plugin->getValue('whitelist', 'pro_plugin');
            $secondlevel = $this->pro_plugin->getValue('second_level', 1, 'pro_plugin');
            $check_base_64 = $this->pro_plugin->getValue('check_base_64', 1, 'pro_plugin');
            $add_geoblock_logs = $this->pro_plugin->getValue('add_geoblock_logs', 0, 'pro_plugin');
            $priority1 = $this->pro_plugin->getValue('priority1', 'Whitelist', 'pro_plugin');
            $priority2 = $this->pro_plugin->getValue('priority2', 'Blacklist', 'pro_plugin');
            $priority3 = $this->pro_plugin->getValue('priority3', 'DynamicBlacklist', 'pro_plugin');
           
            
            $attack_ip = $this->get_ip();        
            $request_uri = $_SERVER['REQUEST_URI'];
            
            // Chequeamos los nuevos usuarios administradores/super usuarios
            $this->forbid_new_admins();
            
            // Cargamos las librerias necesarias para realizar comprobaciones
            include_once JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/library/model.php';
            $model = new SecuritycheckproModel;
            
            $aparece_lista_negra = $model->chequear_ip_en_lista($attack_ip, $blacklist_ips);
            $aparece_lista_blanca = $model->chequear_ip_en_lista($attack_ip, $whitelist_ips);
            
			// If priority1 was set to "geoblock" we must set it to other value (i.e Blacklist) or no actions will be taken
			if ($priority1 == "Geoblock") {		
				$priority1 = "Blacklist";
			}
                        
            // Prioridad            
            if ($priority1 == "Whitelist") {
                if ($aparece_lista_blanca) {
                    return;
                }            
            } else if ($priority1 == "DynamicBlacklist") {
                // Chequeamos si la ip remota se encuentra en la lista negra din�mica
                if ($dynamic_blacklist_on) {
                    $this->acciones_lista_negra_dinamica($dynamic_blacklist_time, $attack_ip, $dynamic_blacklist_counter, $logs_attacks, $request_uri, $not_applicable);
                }
            } else if ($priority1 == "Blacklist") {
                // Chequeamos si la ip remota se encuentra en la lista negra
                if ($aparece_lista_negra) {
                    $this->acciones_lista_negra($logs_attacks, $attack_ip, $access_attempt, $request_uri, $not_applicable);
                }
            }
            
            
            if ($priority2 == "Whitelist") {
                if ($aparece_lista_blanca) {
                    return;
                }
            }  else if ($priority2 == "DynamicBlacklist") {
                // Chequeamos si la ip remota se encuentra en la lista negra din�mica
                if ($dynamic_blacklist_on) {
                    $this->acciones_lista_negra_dinamica($dynamic_blacklist_time, $attack_ip, $dynamic_blacklist_counter, $logs_attacks, $request_uri, $not_applicable);
                }
            } else if ($priority2 == "Blacklist") {
                // Chequeamos si la ip remota se encuentra en la lista negra
                if ($aparece_lista_negra) {
                    $this->acciones_lista_negra($logs_attacks, $attack_ip, $access_attempt, $request_uri, $not_applicable);
                }
            }
                    
            if ($priority3 == "Whitelist") {
                if ($aparece_lista_blanca) {
                    return;
                }
            }  else if ($priority3 == "DynamicBlacklist") {
                // Chequeamos si la ip remota se encuentra en la lista negra din�mica
                if ($dynamic_blacklist_on) {
                    $this->acciones_lista_negra_dinamica($dynamic_blacklist_time, $attack_ip, $dynamic_blacklist_counter, $logs_attacks, $request_uri, $not_applicable);
                }
            } else if ($priority3 == "Blacklist") {
                // Chequeamos si la ip remota se encuentra en la lista negra
                if ($aparece_lista_negra) {
                    $this->acciones_lista_negra($logs_attacks, $attack_ip, $access_attempt, $request_uri, $not_applicable);
                }
            }
            
            
            if (!$aparece_lista_blanca) {
                // La IP no se encuentra en ninguna lista
                $this->acciones_no_listas($methods, $attack_ip, $methods, $request_uri, $check_base_64, $logs_attacks, $secondlevel, $mode);
            }            
        }
        // Si las tablas est�n bloqueadas prohibimos el acceso a 'com_installer'
        if ($tables_locked) {
            $app = JFactory::getApplication();
            $is_admin = $app->isClient('administrator');
            
            if ($is_admin) {
                $app = JFactory::getApplication();
                $option = $app->input->get('option');
                if (($option == "com_installer") || ($option == "com_joomlaupdate")) {
                    $app->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_INSTALLER_ACCESS_FORBIDDEN'), 'error');
                    // Redirigimos a la p�gina establecida por el administrador
                    JFactory::getApplication()->redirect(JURI::base());    
                }
            }
            
        }
    } 

    
    public function onAfterDispatch()
    {
        // �Tenemos que eliminar el meta tag?
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $remove_meta_tag = $params->get('remove_meta_tag', 1);
        
        $code  = JFactory::getDocument();
        if ($remove_meta_tag) {
            $code->setGenerator('');
        }
    }
    
    // Obtiene el componente de Joomla implicado en una petici�n al servidor
    private function get_component()
    {
        
        //Inicializamos variables
        $option = '';
        
        // �C�mo hemos de extraer el componente?
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $determine_option_accurately = $params->get('determine_option_accurately', 1);
                            
        if ($determine_option_accurately == 1) {
                    
            // Obtenemos el componente de la petici�n
            $app = JFactory::getApplication();
            $uri = clone JUri::getInstance();
            $router = $app->getRouter();
            $parsed = $router->parse($uri);
            if (isset($parsed['option'])) {
                $option = $parsed['option'];
            } else
            {
                $option = 'com_content';
            }
            
        } else 
        {
                        
            // Obtenemos el componente de la petici�n
            $app = JFactory::getApplication()->input;
            $uriQuery = $app->getArray();
            if (array_key_exists('option', $uriQuery)) {
                $option = $uriQuery['option'];
            } else {
                // No hemos podido obtener el componente; lo establecemos por defecto
                $option = 'com_content';
            }            
        }        
                                
        $new_option = '';
                        
        //Si obtenemos 'com_content' como contenido activo, quiz� el parseo no ha podido extraer el componente. Lo intentamos con 'JInput'
        if ($option == 'com_content') {
            $input = new JInput();
            $new_option = $input->getCmd('option', 'Not_defined');
            if ($new_option != 'Not_defined') {
                $option = $new_option;
            }
        }
        
        // Sanitizamos la salida        
        return (filter_var($option, FILTER_SANITIZE_STRING));
    }
    
    /* Funci�n que chequea si un fichero tiene m�ltiples extensiones o pertenece a una lista de extensiones prohibidas. Seg�n el valor de la variable $delete_files, el fichero ser� borrado */
    protected function check_file($check_multiple_extensions,$extensions_blacklist,$delete_files,$file,$actions_upload_scanner)
    {
        // Inicializamos variables
        $safe = true;
        $malware_type = '';
        $malware_description = '';
        $logs_attacks = $this->pro_plugin->getValue('logs_attacks', 1, 'pro_plugin');
        $attack_ip = $this->get_ip();
        $request_uri = $_SERVER['REQUEST_URI'];
        $tag_description = '';
                
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        $not_applicable = $lang->_('COM_SECURITYCHECKPRO_NOT_APPLICABLE');
        $access_attempt = $lang->_('COM_SECURITYCHECKPRO_ACCESS_ATTEMPT');
        $action = $lang->_('COM_SECURITYCHECKPRO_FILE_DELETED');
		$custom_code = $this->pro_plugin->getValue('custom_code', 'The webmaster has forbidden your access to this site', 'pro_plugin');
                
        // Extensiones de ficheros que ser�n analizadas
        // Eliminamos los espacios en blanco
        $extensions_blacklist = str_replace(' ', '', $extensions_blacklist);
        $ext = explode(',', $extensions_blacklist);
        
        // Obtenemos el usuario
        $user = JFactory::getUser();
        
        // Obtenemos el componente de la petici�n
        $component = $this->get_component();
            
        if ((!empty($file['name'])) && (is_string($file['name']))) {
            
            // Buscamos extensiones m�ltiples
            if ($check_multiple_extensions) {        
                
                // Buscamos la verdadera extensi�n del fichero (esto es, buscamos archivos tipo .php.xxx o .php.xxx.yyy)
                $explodedName = explode('.', $file['name']);
                array_reverse($explodedName);
                                                
                if((count($explodedName) > 3) && (strtolower($explodedName[1]) == 'php')) {  // Archivo tipo .php.xxx.yyy
                    $malware_description = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_EXTENSION') . $explodedName[2] . "." . $explodedName[3] ;
                    $tag_description = 'MULTIPLE_EXTENSIONS';
                    $safe = false;
                } else if ((count($explodedName) > 2) && (strtolower($explodedName[1]) == 'php')) {  // Archivo tipo .php.xxx
                    $malware_description = $lang->_('COM_SECURITYCHECKPRO_SUSPICIOUS_FILENAME_EXTENSION') . $explodedName[2];
                    $type = 'MULTIPLE_EXTENSIONS';
                    $safe = false;                    
                } 
            }
            
            // Buscamos si la extensi�n est� en la lista de las extensiones prohibidas
            if ((!empty($extensions_blacklist)) && ($safe)) {
                            
                if (in_array(pathinfo($file['name'], PATHINFO_EXTENSION), $ext) && ($file['size'] > 0)) {
                    // Archivo en la lista de extensiones prohibidas
                    $type = 'FORBIDDEN_EXTENSION';
                    $malware_description = $lang->_('COM_SECURITYCHECKPRO_TITLE_FORBIDDEN_EXTENSION');
                    $safe = false;
                }
            }
            
            // Si alguna de las dos comprobaciones es positiva, borramos el fichero subido (si as� est� marcado)
            if (!$safe) {
                if ($delete_files) {                    
                    @unlink($file['tmp_name']);                    
                } else {
                    $action = $lang->_('COM_SECURITYCHECKPRO_FILE_NOT_DELETED');
                }
                
                // Si est� marcada la opci�n, a�adimos la IP a la lista negra din�mica
                if ($actions_upload_scanner == 1) {
                    $this->actualizar_lista_dinamica($attack_ip);                    
                }
                        
                $this->grabar_log($logs_attacks, $attack_ip, 'UPLOAD_SCANNER', $action, $type, $request_uri, $file['name'] . PHP_EOL . $malware_description, $user->username, $component);
                $error_403 = $lang->_('COM_SECURITYCHECKPRO_403_ERROR');
                echo $custom_code;
                header('HTTP/1.1 403 Forbidden');
                exit;     
            }
        }
    }
    
    public function onAfterRoute()
    {
        /* Chequeamos los archivos subidos al servidor usando cabeceras HTTP y m�todo POST. Los archivos son arrays con el siguiente formato:
        [integer] error = 0
        [string] name = "k.txt"
        [integer] size = 4674
        [string] tmp_name = "/tmp/phpkhm2Jz"
        [string] type = "text/plain"
        */
        
        // Extraemos la configuraci�n del escaner de subidas
        $upload_scanner_enabled = $this->pro_plugin->getValue('upload_scanner_enabled', 1, 'pro_plugin');
        $check_multiple_extensions = $this->pro_plugin->getValue('check_multiple_extensions', 1, 'pro_plugin');
        $extensions_blacklist = $this->pro_plugin->getValue('extensions_blacklist', 'php,js,exe,xml', 'pro_plugin');
        $delete_files = $this->pro_plugin->getValue('delete_files', 1, 'pro_plugin');
        $actions_upload_scanner = $this->pro_plugin->getValue('actions_upload_scanner', 0, 'pro_plugin');
        
        // Si el esc�ner est� habilitado y existen archivos subidos, los comprobamos
        if (($upload_scanner_enabled) && ($_FILES)) {
            foreach ($_FILES as $file)
            { 
                $this->check_file($check_multiple_extensions, $extensions_blacklist, $delete_files, $file, $actions_upload_scanner);            
            }
            
        }
        
    }
    
    /* Auditamos las entradas fallidas de los usuarios */
    public function onUserLoginFailure($response)
    {
        // Extraemos la configuraci�n del plugin
        $track_failed_logins = $this->pro_plugin->getValue('track_failed_logins', 1, 'pro_plugin');
        $write_log = $this->pro_plugin->getValue('write_log', 1, 'pro_plugin');
        $logins_to_monitorize = $this->pro_plugin->getValue('logins_to_monitorize', 2, 'pro_plugin');
        $actions_failed_login = $this->pro_plugin->getValue('actions_failed_login', 1, 'pro_plugin');
        
        $logs_attacks = $this->pro_plugin->getValue('logs_attacks', 1, 'pro_plugin');
        $attack_ip = $this->get_ip();
        $request_uri = $_SERVER['REQUEST_URI'];
                        
        /* Cargamos el lenguaje del sitio */
        $lang = JFactory::getLanguage();
        $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
        $not_applicable = $lang->_('COM_SECURITYCHECKPRO_NOT_APPLICABLE');
                        
        if($track_failed_logins) {
            $login_info = $this->trackFailedLogin();
            // Controlamos el acceso al backend
            $app = JFactory::getApplication();
            if (in_array($app->getName(), array('administrator','admin'))) {
                // Escribimos un log si se produce un intento de acceso fallido    al backend                
                if ($logins_to_monitorize != 1) {
                    $description = $lang->_('COM_SECURITYCHECKPRO_USERNAME') . $login_info[0];
                    if($write_log) {
                        $this->grabar_log($write_log, $attack_ip, 'FAILED_LOGIN_ATTEMPT_LABEL', $lang->_('COM_SECURITYCHECKPRO_FAILED_ADMINISTRATOR_LOGIN_ATTEMPT_LABEL'), 'SESSION_PROTECTION', $request_uri, $description, $login_info[0], '---');                        
                    }
                    // Si est� marcada la opci�n, a�adimos la IP a la lista negra din�mica
                    if ($actions_failed_login == 1) {
                        $this->actualizar_lista_dinamica($attack_ip);                    
                    }
                }                                        
            } else
            {
                // Escribimos en log si se produce un intento de acceso fallido    al frontend
                if ($logins_to_monitorize != 2) {
                    $description = $lang->_('COM_SECURITYCHECKPRO_USERNAME') . $login_info[0];                    
                    if($write_log) {
                        $this->grabar_log($write_log, $attack_ip, 'FAILED_LOGIN_ATTEMPT_LABEL', $lang->_('COM_SECURITYCHECKPRO_FAILED_LOGIN_ATTEMPT_LABEL'), 'SESSION_PROTECTION', $request_uri, $description, $login_info[0], '---');
                    }
                    // Si est� marcada la opci�n, a�adimos la IP a la lista negra din�mica
                    if ($actions_failed_login == 1) {
                        $this->actualizar_lista_dinamica($attack_ip);                    
                    }
                }
            }    
            
        }
        
        // Limpiamos las sesiones no v�lidas
        $this->chequeo_sesiones();
    }
    
    /* Funci�n que recoje los datos de los intentos de acceso fallidos */
    private function trackFailedLogin()
    {
            
        $user = JRequest::getCmd('username', null);
        
        $extraInfo = array();
        if(!empty($user)) {    
            $extraInfo[] = $user;        
        }
        
        return $extraInfo;        
    }
      
    
    /* Obtiene la IP remota que realiza las peticiones */
    public function get_ip()
    {
        // Inicializamos las variables 
        $clientIpAddress = 'Not set';
        $ip_valid = false;
        
        // �C�mo determinamos la IP?
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $avoid_proxies = $params->get('avoid_proxies', 1);
                
        if ($avoid_proxies) {
            // Ignoramos todas las cabeceras; usamos s�lo "remote_addr" para determinar la direcci�n ip
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $clientIpAddress = $_SERVER['REMOTE_ADDR'];            
            }
            
            $ip_valid = filter_var($clientIpAddress, FILTER_VALIDATE_IP);
            // Si la ip no es v�lida entonces bloqueamos la petici�n y mostramos un error 403
            if (!$ip_valid) {
                // Cargamos el lenguaje del sitio 
                $lang = JFactory::getLanguage();
                $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
                $error_403 = $lang->_('COM_SECURITYCHECKPRO_403_ERROR');
                $this->redirection(403, $error_403, true);                
            } else 
            {
                return $clientIpAddress;
            }        
        } else 
        {    
			// Contribution of George Acu - thanks!
			if (isset($_SERVER['HTTP_TRUE_CLIENT_IP']))
			{
				# CloudFlare specific header for enterprise paid plan, compatible with other vendors
				$clientIpAddress = $_SERVER['HTTP_TRUE_CLIENT_IP']; 
			} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
			{
				# another CloudFlare specific header available in all plans, including the free one
				$clientIpAddress = $_SERVER['HTTP_CF_CONNECTING_IP']; 
			} elseif (isset($_SERVER['HTTP_INCAP_CLIENT_IP'])) 
			{
				// Users of Incapsula CDN
				$clientIpAddress = $_SERVER['HTTP_INCAP_CLIENT_IP']; 
			} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
			{
				# specific header for proxies
				$clientIpAddress = $_SERVER['HTTP_X_FORWARDED_FOR']; 
				$result_ip_address = explode(', ', $clientIpAddress);
                $clientIpAddress = $result_ip_address[0];
			} elseif (isset($_SERVER['REMOTE_ADDR']))
			{
				# this one would be used, if no header of the above is present
				$clientIpAddress = $_SERVER['REMOTE_ADDR']; 
			}
            
            $ip_valid = filter_var($clientIpAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            
            // Si la ip no es v�lida intentamos extraer la direcci�n IP remota
            if (!$ip_valid) {
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $clientIpAddress = $_SERVER['REMOTE_ADDR'];            
                }
                
                $ip_valid = filter_var($clientIpAddress, FILTER_VALIDATE_IP);
                // Si la ip no es v�lida entonces bloqueamos la petici�n y mostramos un error 403
                if (!$ip_valid) {
                    // Cargamos el lenguaje del sitio
                    $lang = JFactory::getLanguage();
                    $lang->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
                    $error_403 = $lang->_('COM_SECURITYCHECKPRO_403_ERROR');
                    $this->redirection(403, $error_403, true);                
                }            
            }
            
            // Devolvemos el resultado
            return $clientIpAddress;            
        }
    }
    
    // Chequeamos los usuarios administradores/super usuarios
    private function forbid_new_admins()
    {
        // Inicializamos las variables
        $admin_groups = array();        
        $logs_attacks = $this->pro_plugin->getValue('logs_attacks', 1, 'pro_plugin');
        $forbid_new_admins = $this->pro_plugin->getValue('forbid_new_admins', 0, 'pro_plugin');
        $db = JFactory::getDBO();
        
        if ($forbid_new_admins) {
                        
            // Extraemos todos los grupos existentes...
            $query = $db->getQuery(true)
                ->select(array($db->quoteName('group_id')))
                ->from($db->quoteName('#__user_usergroup_map'));        
            $db->setQuery($query);
            $groups = $db->loadColumn();
            
            // ... y chequeamos los que tienen permisos de administraci�n, ya sean propios o heredados
            if(!empty($groups)) { foreach($groups as $group)
                {
                    // First try to see if the group has explicit backend login privileges
                    $backend = JAccess::checkGroup($group, 'core.login.admin');
                    if(is_null($backend)) { $backend = JAccess::checkGroup($group, 'core.admin');
                    }
                                
                    // Si el grupo tiene privilegios de administraci�n, lo a�adimos al array 
                    if ($backend) {
                        $admin_groups[] = $group;
                    }                
            }
            }
                        
            // Consultamos el n�mero actual de usuarios con permisos de administraci�n
			try 
            {
				$query = "SELECT COUNT(*) from `#__user_usergroup_map` WHERE group_id IN (" . implode(',', array_map('intval', $admin_groups)) . ")" ;
				$db->setQuery($query);
				(int) $actual_admins = $db->loadResult();
			}catch (Exception $e)
            {
                return;
            }
                        
            // Consultamos el n�mero previo de usuarios pertenencientes al grupo super-users
            try
            {
                $query = "SELECT contador from `#__securitycheckpro_users_control` WHERE id='1'" ;
                $db->setQuery($query);
                (int) $previous_admins = $db->loadResult();
            } catch (Exception $e)
            {
                if (strstr($e->getMessage(), "doesn't exist")) {
                    $previous_admins = null;
                }
            }
                            
            if (is_null($previous_admins)) { // No hay datos almacenados (o es la primera vez que se lanza o se ha desactivado esta opci�n y ahora est� activa)
                // Extraemos los ids de los usuarios con permisos de administraci�n
				try 
				{
					$query = "SELECT user_id from `#__user_usergroup_map` WHERE group_id IN (" . implode(',', array_map('intval', $admin_groups)) . ")" ;
					$db->setQuery($query);
					$actual_admins = $db->loadColumn();
				}catch (Exception $e)
				{
					return;
				}
                
                // Instanciamos un objeto para almacenar los datos que ser�n sobreescritos
                $object = new StdClass();                    
                $object->id = 1;
                $object->users = json_encode($actual_admins);
                $object->contador = count($actual_admins);
                
                try 
                {
                    // A�adimos los datos a la BBDD
                    $res = $db->insertObject('#__securitycheckpro_users_control', $object);    
                        
                } catch (Exception $e) {    
                    
                }
            } else if ($actual_admins > $previous_admins) {
                // Se ha a�adido un nuevo usuario con permisos de administraci�n
                // Extraemos los ids de los usuarios con permisos de administraci�n
                $query = "SELECT user_id from `#__user_usergroup_map` WHERE group_id IN (" . implode(',', array_map('intval', $admin_groups)) . ")" ;
                $db->setQuery($query);
                $actual_admins = $db->loadColumn();
                                
                // Extraemos los ids de los usuarios con permisos de administraci�n anteriores
                try
                {
                    $query = "SELECT users from `#__securitycheckpro_users_control`" ;
                    $db->setQuery($query);
                    $previous_admins = $db->loadResult();
                } catch (Exception $e)
                {    
                    if (strstr($e->getMessage(), "doesn't exist")) {
                        $app = JFactory::getApplication();                                    
                        $app->enqueueMessage(JText::_('A mandatory table of Securitycheck Pro has not been created. Please, install the extension again and everything should work fine. Please, close this message.'), 'error');
                    }
                }
                
                // Decodificamos el array, que vendr� en formato json_decode
                $previous_admins = json_decode($previous_admins, true);
            
                // Extraemos el id del nuevo usuario creado
                $new_user_added = array_diff($actual_admins, $previous_admins);
                                
                foreach ($new_user_added as $new_user)
                {                        
                    // Creamos una instancia del usuario
                    $instance = JUser::getInstance($new_user);
                    $username = $instance->username;
                                
                    if ($instance) {
                        // Borramos el usuario
                        $instance->delete();
                        $this->grabar_log($logs_attacks, '---', 'SESSION_PROTECTION', JText::_('COM_SECURITYCHECKPRO_FORBID_NEW_ADMINS_LABEL'), 'SESSION_PROTECTION', JText::_('COM_SECURITYCHECKPRO_NOT_APPLICABLE'), JText::_('COM_SECURITYCHECKPRO_USER_DELETED'), $username, '---');
                        // Si hay alguien logado al backend, mostramos un mensaje de error
                        $app = JFactory::getApplication();
                        if (in_array($app->getName(), array('administrator','admin'))) {                    
                               $app->enqueueMessage(JText::_('COM_SECURITYCHECKPRO_USER_DELETED_EXPLAINED'), 'error');                        
                        }
                    }
                }
                
                
            }
            
        } else
        {
            // Borramos los datos de la tabla
            // Consultamos el n�mero de logs para ver si se supera el l�mite establecido en el apartado 'log_limits_per_ip_and_day'
            try 
            {
                $query = "DELETE from `#__securitycheckpro_users_control` WHERE id='1'" ;
                $db->setQuery($query);
                $db->execute();
            } catch (Exception $e)
            {
                if (strstr($e->getMessage(), "doesn't exist")) {                    
                    $app = JFactory::getApplication();                                    
                    $app->enqueueMessage(JText::_('A mandatory table of Securitycheck Pro has not been created. Please, install the extension again and everything should work fine. Please, close this message.'), 'error');                
                }
                
            }
        }
        
        
    }
    /*  Chequeamos las extensions instaladas/actualizadas para usar esa info en la gesti�n de integtridad de los archivos     
    *
    * @name   string  Nombre de la extensi�n extraido del manifest (i.e [string] name "Akeeba Backup package")
    *
    * @type  string  Tipo de extensi�n (component, package...)
    *
    * $files    array  array de los ficheros incluidos en el paquete (i.e [string] 1 = "plg_quickicon_akeebabackup.zip" [string] 2 = "plg_system_akeebaupdatecheck.zip")
    *
    *
    * $description    string  Descripci�n incluida por el desarrollador (i.e J!Dump Plugin -- This plugin requires the J!Dump component to function. )
    *
    */
    function onExtensionAfterInstall($installer, $eid)
    {
        $installs = null;
        $empty = true;
        
        $db = JFactory::getDBO();
        
        try {
                        
            // Comprobamos si hay alg�n dato a�adido o la tabla es null; dependiendo del resultado haremos un 'update' o un 'insert'
            $query = $db->getQuery(true)
                ->select(array('storage_value'))
                ->from($db->quoteName('#__securitycheckpro_storage'))
                ->where($db->quoteName('storage_key').' = '.$db->quote("installs"));
            $db->setQuery($query);
            $installs = $db->loadResult();
            
            $name = $installer->manifest->name->__toString();
            $type = $installer->manifest->attributes()->type->__toString();
            /*$packagename = $installer->manifest->packagename->__toString(); 
            $files = $installer->manifest->files->file;
            $description = $installer->manifest->description->__toString();*/
            
            if (!empty($installs)) {
                $empty = false;
                $installs_array = json_decode($installs, true);
                
                // Obtenemos s�lo el array de nombre para comprobar si ya hemos a�adido la extensi�n            
                $array_names = array_column($installs_array, 'name');
                
                if (!in_array($name, $array_names)) {
                    $extension_data = array(
                    'name' => $name,
                    'type' => $type
                    );
                    
                    $installs_array[] = $extension_data;
                }                
            } else 
            {
                $extension_data = array(
                'name' => $name,
                'type' => $type
                );
                        
                $installs_array[] = $extension_data;
            }
            
            // Codificamos el array en formato json
            $installs_array = json_encode($installs_array);
                                    
            // Instanciamos un objeto para almacenar los datos que ser�n sobreescritos/a�adidos
            $object = new StdClass();                    
            $object->storage_key = "installs";
            $object->storage_value = $installs_array;
            
            if ($empty) {
                $res = $db->insertObject('#__securitycheckpro_storage', $object);
            } else {
                $res = $db->updateObject('#__securitycheckpro_storage', $object, 'storage_key');
            }
        } catch (Exception $e) {                
            return false;
        }
    }
        
}
