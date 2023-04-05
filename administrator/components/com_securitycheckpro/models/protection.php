<?php
/**
 * Modelo Protection para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelProtection extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{

    /* Definimos las variables */
    var $defaultConfig = array(
    'disable_server_signature'    => 0,
    'prevent_access'    => 0,
    'prevent_unauthorized_browsing'    => 0,
    'file_injection_protection'    => 0,
    'self_environ'    => 0,
    'xframe_options'    =>    0,
    'prevent_mime_attacks'    =>    0,
    'default_banned_list'    => 0,
    'own_banned_list'    => '',
    'disallow_php_eggs'    => 0,
    'disallow_sensible_files_access' => '',
    'hide_backend_url' => '',
    'own_code'    =>    '',
    'backend_exceptions'    =>    '',
    'optimal_expiration_time'    =>    0,
    'redirect_to_www'    =>    0,
    'redirect_to_non_www'    =>    0,
    'compress_content'    =>    0,
    'backend_protection_applied'    =>    0,
    'hide_backend_url_redirection'    =>    '',
    'sts_options'    =>    0,
    'xss_options'    =>    0,
    'csp_policy'    =>    '',
    'referrer_policy'    =>    '',
	'permissions_policy'    =>    ''
    );

    var $ConfigApplied = array(
    'disable_server_signature'    => 0,
    'prevent_access'    => 0,
    'prevent_unauthorized_browsing'    => 0,
    'file_injection_protection'    => 0,
    'self_environ'    => 0,
    'xframe_options'    =>    0,
    'prevent_mime_attacks'    =>    0,
    'default_banned_list'    => 0,
    'own_banned_list'    => 0,
    'disallow_php_eggs'    => 0,
    'disallow_sensible_files_access' => 0,
    'hide_backend_url' => 0,
    'own_code'    =>    '',
    'backend_exceptions'    =>    '',
    'optimal_expiration_time'    =>    0,
    'redirect_to_www'    =>    0,
    'redirect_to_non_www'    =>    0,
    'compress_content'    =>    0,
    'backend_protection_applied'    =>    0,
    'hide_backend_url_redirection'    =>    0,
    'sts_options'    =>    0,
    'xss_options'    =>    0,
    'csp_policy'    =>    '',
    'referrer_policy'    =>    '',
	'permissions_policy'    =>    ''
    );

    private $config = null;

    /* Obtiene el valor de una opción de configuración de 'htaccess protection' */
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

    /* Establece el valor de una opción de configuración de 'htaccess protection' */
    public function setValue($key, $value, $save = false, $key_name = 'cparams')
    {
        if(is_null($this->config)) {
            $this->load($key_name);
        }
    
        if(version_compare(JVERSION, '3.0', 'ge')) {
            $x = $this->config->set($key, trim($value));
        } else 
        {
            $x = $this->config->setValue($key, trim($value));
        }
        if($save) { $this->save($key_name);
        }
        return $x;
    }

    /* Hace una consulta a la tabla #__securitycheckpro_storage, que contiene la configuración de 'htaccess protection' */
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
        if(!empty($res)) {
            $res = json_decode($res, true);
            $this->config->loadArray($res);
        }
    }

    /* Guarda la configuración de 'htaccess protection' con a la tabla #__securitycheckpro_storage */
    public function save($key_name)
    {
        if (is_null($this->config)) {
            $this->load($key_name);
        }
    
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
    
            
        $data = $this->config->toArray();
        $data = json_encode($data);

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
    }

    /* Obtiene la configuración de los parámetros de la opción 'Protection' */
    function getConfig()
    {
        $config = array();
        foreach($this->defaultConfig as $k => $v) {
            $config[$k] = $this->getValue($k, $v);
        }
        return $config;
    }

    /* Guarda la modificación de los parámetros de la opción 'Protection' */
    function saveConfig($newParams, $key_name = 'cparams')
    {
        foreach($newParams as $key => $value)
        {
            $this->setValue($key, $value, '', $key_name);
        }

        $this->save($key_name);
    }

    /* Devuelve TRUE si el fichero pasado como argumento existe en la raíz del sitio. */
    public function ExistsFile($filename)
    {
        return file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.$filename);
    }

    /* Hace una copia del archivo .htaccess si existe*/
    function Make_Backup($name)
    {
        return JFile::copy(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess', JPATH_SITE . DIRECTORY_SEPARATOR . $name);
    }

    /* Modificamos los valores del array 'ConfigApplied' según las opciones que ya hayan sido aplicadas al archivo .htaccess existentes */
    public function GetConfigApplied()
    {
        /* Variable que almacenará el contenido del archivo .htaccess */
        $rules_applied = null;
        /* Variable que indicará si existe el/los strings en el archivo .htaccess */
        $exists = false;    
    
        // Get actual config
        $actual_config = $this->getConfig();
    
        if (file_exists(JPATH_SITE.DIRECTORY_SEPARATOR.'.htaccess')) {
            $rules_applied = file_get_contents(JPATH_SITE.DIRECTORY_SEPARATOR.'.htaccess');
        
            /* 'prevent_access' habilitado? */
            if (stripos($rules_applied, "<FilesMatch \"^\\.ht\">")) {
                  $this->ConfigApplied['prevent_access'] = 1;
            }
            /* 'prevent_unauthorized_browsing' habilitado? Esta opción ya viene por defecto en los nuevos htaccess de Joomla */
            if ((stripos($rules_applied, "Options All -Indexes")) || (stripos($rules_applied, "Options -Indexes"))) {
                $this->ConfigApplied['prevent_unauthorized_browsing'] = 1;
            }
            /* 'default_banned_list' habilitado? */
            if (stripos($rules_applied, "## Begin Securitycheck Pro Default Blacklist")) {
                $this->ConfigApplied['default_banned_list'] = 1;
            }
            /* 'file_injection_protection' habilitado? */
            if (stripos($rules_applied, "RewriteCond %{REQUEST_METHOD} GET")) {
                $this->ConfigApplied['file_injection_protection'] = 1;
            }
            /* 'self_environ' habilitado? */
            if (stripos($rules_applied, "RewriteCond %{QUERY_STRING} proc/self/environ [NC,OR]")) {
                $this->ConfigApplied['self_environ'] = 1;
            }
            /* 'xframe_options' habilitado? */
            if (stripos($rules_applied, "set X-Frame-Options")) {
                $this->ConfigApplied['xframe_options'] = 1;
            }
            /* 'sts_options' habilitado? */
            if (stripos($rules_applied, "Strict-Transport-Security")) {
                $this->ConfigApplied['sts_options'] = 1;
            }
            /* 'xss_options' habilitado? */
            if (stripos($rules_applied, "X-Xss-Protection")) {
                $this->ConfigApplied['xss_options'] = 1;
            }
            /* 'csp policy' habilitado? */
            if (stripos($rules_applied, "Content-Security-Policy")) {
                $this->ConfigApplied['csp_policy'] = 1;
            }
            /* 'referrer policy' habilitado? */
            if (stripos($rules_applied, "Referrer-Policy")) {
                $this->ConfigApplied['referrer_policy'] = 1;
            }
			/* 'referrer policy' habilitado? */
            if (stripos($rules_applied, "Permissions-Policy")) {
                $this->ConfigApplied['permissions_policy'] = 1;
            }
            /* 'prevent_mime_attacks' habilitado? */
            if (stripos($rules_applied, 'set X-Content-Type-Options "nosniff"')) {
                $this->ConfigApplied['prevent_mime_attacks'] = 1;
            }
            /* 'own_banned_list' habilitado? */
            $current_own_banned_list = explode(PHP_EOL, $this->getValue("own_banned_list"));
            if (! empty($current_own_banned_list) && ! (sizeof($current_own_banned_list) == 1 && trim($current_own_banned_list[0]) == '' )) {
                $exists = true;
                foreach ($current_own_banned_list as $agent_applied)
                {
                    $search_string = null;
                    $search_string .= "RewriteCond %{HTTP_USER_AGENT} " . trim($agent_applied);
                    if (!stripos($rules_applied, $search_string)) {
                        /* Si no existe el string, actualizamos la variable '$exists' y salimos del bucle 'foreach' */
                        $exists = false;
                        break;
                    }
                }            
            }
            if ($exists) {
                $this->ConfigApplied['own_banned_list'] = 1;
            }
                    
            /* 'own_code' habilitado? */
            $exists = false;
            $current_own_code = explode(PHP_EOL, $this->getValue("own_code"));
                
            if (! empty($current_own_code) && ! (sizeof($current_own_code) == 1 && trim($current_own_code[0]) == '' )) {
                $exists = true;
                foreach ($current_own_code as $code)
                {
                    $search_string = null;
                    $search_string .= trim($code);
                    if (!stripos($rules_applied, $search_string)) {
                        /* Si no existe el string, actualizamos la variable '$exists' y salimos del bucle 'foreach' */
                        $exists = false;
                        break;
                    }
                }            
            }
            if ($exists) {
                $this->ConfigApplied['own_code'] = 1;
            }
            /* 'disable_server_signature' habilitado? */
            if (stripos($rules_applied, "ServerSignature Off")) {
                $this->ConfigApplied['disable_server_signature'] = 1;
            }
            /* 'disallow_php_eggs' habilitado? */
            if (stripos($rules_applied, "RewriteCond %{QUERY_STRING} \=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12} [NC]")) {
                $this->ConfigApplied['disallow_php_eggs'] = 1;
            }
            /* 'disallow_sensible_files_access' habilitado? */
            $disallow_sensible_files_access = explode(PHP_EOL, $this->getValue("disallow_sensible_files_access"));
            if (! empty($disallow_sensible_files_access) && ! (sizeof($disallow_sensible_files_access) == 1 && trim($disallow_sensible_files_access[0]) == '' )) {
                $exists = true;
                foreach ($disallow_sensible_files_access as $files) 
                {
                    $search_string = null;
                    $search_string .= trim($files);
                    if (!stripos($rules_applied, $search_string)) {
                        /* Si no existe el string, actualizamos la variable '$exists' y salimos del bucle 'foreach' */
                        $exists = false;
                        break;
                    }
                }            
            
            }
            
            if ($exists) {
                $this->ConfigApplied['disallow_sensible_files_access'] = 1;
            }        
            /* 'hide_backend_url' habilitado? */
            if (stripos($rules_applied, "RewriteCond %{QUERY_STRING} !" . $this->getValue("hide_backend_url"))) {
                $this->ConfigApplied['hide_backend_url'] = 1;
            }
            /* 'hide_backend_url_redirection' habilitado? */
            if (stripos($rules_applied, "RewriteRule ^.*administrator/? /" . $this->getValue("hide_backend_url_redirection"))) {
                $this->ConfigApplied['hide_backend_url_redirection'] = 1;            
            }
            /* exceptions to 'hide_backend_url'? */
            $backend_exceptions = explode(",", $this->getValue("backend_exceptions"));
            $exists = false;
            if (! empty($backend_exceptions) && ! (sizeof($backend_exceptions) == 1 && trim($backend_exceptions[0]) == '' )) {
                $exists = true;
                foreach ($backend_exceptions as $exception)
                {
                    $search_string = null;
                    $search_string .= trim($exception);                
                    if (!stripos($rules_applied, $search_string)) {
                        /* Si no existe el string, actualizamos la variable '$exists' y salimos del bucle 'foreach' */
                        $exists = false;
                        break;
                    }
                }            
            }
            if ($exists) {
                $this->ConfigApplied['backend_exceptions'] = 1;
            }    
            /* 'optimal expiration time' habilitado? */
            if (stripos($rules_applied, "<IfModule mod_expires.c>")) {
                $this->ConfigApplied['optimal_expiration_time'] = 1;
            }
            /* 'compress-content' habilitado? */
            if (stripos($rules_applied, "<IfModule mod_deflate.c>")) {
                $this->ConfigApplied['compress_content'] = 1;
            }
            /* 'redirect non-www to www' habilitado? */
            if (stripos($rules_applied, "RewriteCond %{HTTP_HOST} !^www\. [NC]")) {
                $this->ConfigApplied['redirect_to_www'] = 1;
            }
            /* 'redirect www to non-www' habilitado? */
            if (stripos($rules_applied, "RewriteCond %{HTTP_HOST} ^www.(.*)$ [NC]")) {
                $this->ConfigApplied['redirect_to_non_www'] = 1;
            }
        
        }
    
        if ($actual_config['backend_protection_applied'] == 1) {
            $this->ConfigApplied['backend_protection_applied'] = 1;
        } else if ($actual_config['backend_protection_applied'] == 0) {
            $this->ConfigApplied['backend_protection_applied'] = 0;
        }
    
        return $this->ConfigApplied;    
    }

    /* Modifica o crea el archivo .htaccess según las opciones escogidas por el usuario */
    public function protect()
    {
        // Site's url
        $site_url = str_replace('http://', "", JURI::base());
    
        $rules = null;
        $endsat = 0;
        $rules_end = null;
        $rules_applied = null;
            
        $ExistsHtaccess = $this->ExistsFile('.htaccess');  // Comprobamos si existe el archivo .htaccess
        if ($ExistsHtaccess) {  // Si existe, hacemos un backup
            if ($this->ExistsFile('.htaccess.original')) {
                  $backup_sucess = $this->Make_Backup('.htaccess.backup');
            } else
            {
                $backup_sucess = $this->Make_Backup('.htaccess.original');
            }
        
            //Leemos el contenido del fichero htaccess.txt existente y lo guardamos en el buffer.        
            $rules_applied .= file_get_contents(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess');
        
            // Obtenemos los valores que ya están aplicados para evitar duplicar valores
            $this-> ConfigApplied = $this->GetConfigApplied();
        
            // Longitud total del fichero
            $longitud = strlen($rules_applied);
            // Primera ocurrencia del string "## Begin Securitycheck Pro", que es el que da comienzo a las secciones añadidas por la protección
            $startsat = strpos($rules_applied, "## Begin Securitycheck Pro");
            $fin = $startsat;
        
            // Última ocurrencia del string "## End Securitycheck Pro", que es el que da fin a las secciones añadidas por la protección
            while ($endsat <= $longitud)
            {
                $endsat = strpos($rules_applied, "## End Securitycheck Pro", $fin);
                if ($endsat === false) {
                    $endsat = $fin;
                    break;
                }
                $fin = $endsat + strlen("## End Securitycheck Pro");                        
            }
                
            if (!$fin) {
                // Existe el fichero .htaccess pero no hay contenido de SCP; añadimos el contenido al final
                $rules = $rules_applied;            
            } else 
            {            
                // Obtenemos la primera parte del fichero (desde el comienzo del fichero hasta la aparición del string "## Begin Securitycheck Pro")
                $rules = substr($rules_applied, 0, $startsat);
                // Modificamos el valor para añadir el contenido hasta el final de la línea
                $endsat = strpos($rules_applied, PHP_EOL, $endsat);
            
                $rules_end = trim(substr($rules_applied, $endsat));
            }
        
        } else
        {  
            /* Si no existe el fichero, copiamos el que incorpora Joomla por defecto */        
            if ($this->ExistsFile('htaccess.txt')) {            
                // Leemos el contenido del fichero .htaccess existente y lo guardamos en el buffer.
                $rules .= file_get_contents(JPATH_SITE . DIRECTORY_SEPARATOR . 'htaccess.txt');
            } else
            {
                // Leemos el contenido del fichero .htaccess existente y lo guardamos en el buffer.
                $rules .= file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'default_joomla_htaccess.inc');
                $status = JFile::copy(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'default_joomla_htaccess.inc', JPATH_SITE.DIRECTORY_SEPARATOR.'.htaccess');
            }
        }
    
        /* Comprobamos si hay que proteger los archivos .ht */
        if ($this->getValue("prevent_access")) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Prevent access to .ht files";
            $rules .= PHP_EOL . "<FilesMatch \"^\.ht\">";
            $rules .= PHP_EOL . "Order deny,allow";
            $rules .= PHP_EOL . "Deny from all";
            $rules .= PHP_EOL . "</FilesMatch>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Prevent access to .ht files" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que proteger los directorios de navegación no autorizada. Esta opción ya viene por defecto en las últimas versiones de Joomla */
        if (($this->getValue("prevent_unauthorized_browsing")) && (!$this->ConfigApplied['prevent_unauthorized_browsing'])) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Prevent Unauthorized Browsing";
            $rules .= PHP_EOL . "Options All -Indexes";
            $rules .= PHP_EOL . "## End Securitycheck Pro Prevent Unauthorized Browsing" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que proteger frente a ataques de inclusión */
        if ($this->getValue("file_injection_protection")) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro File Injection Protection";
            $rules .= PHP_EOL . "RewriteCond %{REQUEST_METHOD} GET";
            $rules .= PHP_EOL . "RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=%{REQUEST_SCHEME}:// [OR]";
            $rules .= PHP_EOL . "RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=(\.\.//?)+ [OR]";
            $rules .= PHP_EOL . "RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=/([a-z0-9_.]//?)+ [NC]";
            $rules .= PHP_EOL . "RewriteRule .* - [F]";
            $rules .= PHP_EOL . "## End Securitycheck Pro File Injection Protection" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que proteger frente a ataques que intentan explotar la vulnerabilidad de /proc/self/environ */
        if ($this->getValue("self_environ")) {
            
            $rules .= PHP_EOL . "## Begin Securitycheck Pro self/environ protection";
            $rules .= PHP_EOL . "## /proc/self/environ? Go away!";
            $rules .= PHP_EOL . "RewriteCond %{QUERY_STRING} proc/self/environ [NC,OR]";    
            $rules .= PHP_EOL . "## End Securitycheck Pro self/environ protection" . PHP_EOL;        
        }
    
        /* Comprobamos si hay que proteger las cabeceras X-Frame del navegador */
        $xframe_options = $this->getValue("xframe_options");
        if ((!empty($xframe_options)) && ($this->getValue("xframe_options") != 'NO')) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Xframe-options protection";
            $rules .= PHP_EOL . "## Don't allow any pages to be framed - Defends against CSRF";
            $rules .= PHP_EOL . "<IfModule mod_headers.c>";
            $rules .= PHP_EOL . 'Header always set X-Frame-Options "' . $this->getValue("xframe_options") . '"';         
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Xframe-options protection" . PHP_EOL;    
        
        }
    
        /* Comprobamos si hay que establecer protección contra ataques basados en 'mime'*/
        if ($this->getValue("prevent_mime_attacks")) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Prevent mime based attacks";
            $rules .= PHP_EOL . "<IfModule mod_headers.c>";
            $rules .= PHP_EOL . 'Header always set X-Content-Type-Options "nosniff"';            
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Prevent mime based attacks" . PHP_EOL;    
        
        }
    
        /* Comprobamos si hay que establecer protección STS (Strict Transport Security) */
        $sts_options = $this->getValue("sts_options");
        if ($sts_options) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Strict Transport Security";
            $rules .= PHP_EOL . "<IfModule mod_headers.c>";
            $rules .= PHP_EOL . 'Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"';            
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Strict Transport Security" . PHP_EOL;    
        
        }
    
        /* Comprobamos si hay que establecer protección X-Xss-Protection */
        $xss_options = $this->getValue("xss_options");
        if ($xss_options) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro X-Xss-Protection";
            $rules .= PHP_EOL . "<IfModule mod_headers.c>";
            $rules .= PHP_EOL . 'Header always set X-Xss-Protection "1; mode=block"';            
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro X-Xss-Protection" . PHP_EOL;    
        
        }
    
        /* Comprobamos si hay que establecer protección Content-Security-Policy */
        $csp_policy = $this->getValue("csp_policy");
        $csp_policy = str_replace('"', '', $csp_policy);
        if (!empty($csp_policy)) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Content-Security-Policy protection";
            $rules .= PHP_EOL . "<IfModule mod_headers.c>";
            $rules .= PHP_EOL . 'Header always set Content-Security-Policy "' . $csp_policy . '"';        
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Content-Security-Policy protection" . PHP_EOL;    
        
        }
    
        /* Comprobamos si hay que establecer protección Referrer-Policy */
        $referrer_policy = $this->getValue("referrer_policy");
        $referrer_policy = str_replace('"', '', $referrer_policy);
        if (!empty($referrer_policy)) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Referrer policy protection";
            $rules .= PHP_EOL . "<IfModule mod_headers.c>";
            $rules .= PHP_EOL . 'Header always set Referrer-Policy "' . $referrer_policy . '"';            
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Referrer policy protection" . PHP_EOL;    
        
        }
		
		/* Comprobamos si hay que establecer protección Permissions-Policy */
        $permissions_policy = $this->getValue("permissions_policy");        
        if (!empty($permissions_policy)) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Permissions policy (old Feature-Policy) protection";
            $rules .= PHP_EOL . "<IfModule mod_headers.c>";
            $rules .= PHP_EOL . "Header always set Permissions-Policy '". $permissions_policy . "'";            
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Permissions policy protection" . PHP_EOL;    
        
        }
    
        /* Comprobamos si hay que aplicar la lista de user-agents por defecto */
        if ($this->getValue("default_banned_list")) {
        
            $user_agent_rules = file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'user_agent_blacklist.inc');
            // Añadimos el contenido del fichero por defecto al final del buffer
            $rules .= PHP_EOL . $user_agent_rules . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que aplicar la lista de user-agents establecida por el usuario */
        $own_user_agents = explode(PHP_EOL, $this->getValue("own_banned_list"));
        if (! empty($own_user_agents) && ! (sizeof($own_user_agents) == 1 && trim($own_user_agents[0]) == '' )) {
        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro User Own Blacklist";
            $count = 1;
                    
            foreach ($own_user_agents as $agent)
            {
                    
                $rules .= PHP_EOL . "RewriteCond %{HTTP_USER_AGENT} " . trim($agent);
                                
                if ($count < sizeof($own_user_agents)) {
                    $rules .= " [NC,OR]";
                    $count++;
                } else
                {
                    $rules .= " [NC]";
                }
            }
                
            $rules .= PHP_EOL . "RewriteRule ^(.*)$ - [F,L]";
            $rules .= PHP_EOL . "## End Securitycheck Pro User Own Blacklist" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que aplicar código del usuario */
        $own_code = explode(PHP_EOL, $this->getValue("own_code"));
        if (! empty($own_code) && ! (sizeof($own_code) == 1 && trim($own_code[0]) == '' )) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro User Own Code";
            $count = 1;
                    
            foreach ($own_code as $code)
            {                    
                $rules .= PHP_EOL . trim($code);                
            }                
            $rules .= PHP_EOL . "## End Securitycheck Pro User Own Code" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que deshabilitar la firma del servidor*/
        if ($this->getValue("disable_server_signature")) {        
            $rules .= PHP_EOL . "# Begin Securitycheck Pro Disable Server Signature";
            $rules .= PHP_EOL . "ServerSignature Off";
            $rules .= PHP_EOL . "## End Securitycheck Pro Disable Server Signature" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que prohibir los 'easter-eggs' de PHP */
        if ($this->getValue("disallow_php_eggs")) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Disallow Php Easter Eggs";
            $rules .= PHP_EOL . "RewriteCond %{QUERY_STRING} \=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12} [NC]";
            $rules .= PHP_EOL . "RewriteRule .* index.php [F]";
            $rules .= PHP_EOL . "## End Securitycheck Pro Disallow Php Easter Eggs" . PHP_EOL;        
        }
    
        /* Comprobamos si hay que prohibir el acceso a archivos que pueden contener información sensible o que tengan alguna vulnerabilidad */
        $disallow_sensible_files_access = explode(PHP_EOL, $this->getValue("disallow_sensible_files_access"));
        if (! empty($disallow_sensible_files_access) && ! (sizeof($disallow_sensible_files_access) == 1 && trim($disallow_sensible_files_access[0]) == '' )) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Disallow Access To Sensitive Files";
            $rules .= PHP_EOL . "RewriteRule ^(";
                    
            // El primer elemento no lleva el carácter |. Usamos esta variable para controlarlo.
            $number = 1;
            foreach ($disallow_sensible_files_access as $code)
            {        
                if ($number == 1) {
                    $rules .= trim($code);                
                } else
                {
                    $rules .= "|" . trim($code);
                }
                $number++;
            }
            
            $rules .= ")$ - [F]";
            $rules .= PHP_EOL . "## End Securitycheck Pro Disallow Access To Sensitive Files" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que ocultar la url del backend */
        if (!is_null($this->getValue("hide_backend_url"))) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Hide Backend Url";
            $rules .= PHP_EOL . "RewriteCond %{HTTP_REFERER} !" . $site_url;
            $rules .= PHP_EOL . "RewriteCond %{QUERY_STRING} !" . $this->getValue("hide_backend_url") . "$";
            $rules .= PHP_EOL . "RewriteCond %{QUERY_STRING} !com_securitycheckprocontrolcenter [NC]";
			// Added to avoid errors in Joomla 4.1
			$rules .= PHP_EOL . "RewriteCond %{REQUEST_URI} !templates/administrator [NC]";
            if (!is_null($this->getValue("hide_backend_url"))) {
                $backend_exceptions = explode(",", $this->getValue("backend_exceptions"));
                foreach ($backend_exceptions as $exception)
                {
                    if (!empty($exception)) {
                         $rules .= PHP_EOL . "RewriteCond %{QUERY_STRING} !" . $exception . " [NC]";
                    }                    
                } 

            }
            $rules .= PHP_EOL . "RewriteRule ^.*administrator/? /" . $this->getValue("hide_backend_url_redirection") ." [R,L]";
            $rules .= PHP_EOL . "## End Securitycheck Pro Hide Backend Url" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que establecer el tiempo óptimo de los recursos */
        if ($this->getValue("optimal_expiration_time")) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Optimal Expiration time";
            $rules .= PHP_EOL . "<IfModule mod_expires.c>";
            $rules .= PHP_EOL . "# Enable expiration control";
            $rules .= PHP_EOL . "ExpiresActive On";
            $rules .= PHP_EOL . "# Default expiration: 1 hour after request";
            $rules .= PHP_EOL . "ExpiresByType text/html \"now\"";
            $rules .= PHP_EOL . "ExpiresDefault \"now plus 1 hour\"";
            $rules .= PHP_EOL . "# CSS and JS expiration: 1 week after request";
            $rules .= PHP_EOL . "ExpiresByType text/css \"now plus 1 week\"";
            $rules .= PHP_EOL . "ExpiresByType application/javascript \"now plus 1 week\"";
            $rules .= PHP_EOL . "ExpiresByType application/x-javascript \"now plus 1 week\"";
            $rules .= PHP_EOL . "# Image files expiration: 1 month after request";
            $rules .= PHP_EOL . "ExpiresByType image/bmp \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType image/gif \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType image/jpeg \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType image/png \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType image/tiff \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType image/ico \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType image/icon \"now plus 1 month\"";
            $rules .= PHP_EOL . "# Audio files expiration: 1 month after request";
            $rules .= PHP_EOL . "ExpiresByType audio/ogg \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType application/ogg \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType audio/midi \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType audio/mpeg \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType audio/mp3 \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType audio/x-wav \"now plus 1 month\"";
            $rules .= PHP_EOL . "# Movie files expiration: 1 month after request";
            $rules .= PHP_EOL . "ExpiresByType application/x-shockwave-flash \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType video/x-msvideo \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType video/mpeg \"now plus 1 month\"";
            $rules .= PHP_EOL . "ExpiresByType video/quicktime \"now plus 1 month\"";
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Cache-Control Headers";
            $rules .= PHP_EOL . "<IfModule mod_headers.c>";
            $rules .= PHP_EOL . "<filesMatch \".(ico|jpe?g|png|gif|swf)$\">";
            $rules .= PHP_EOL . "Header set Cache-Control \"public\"";
            $rules .= PHP_EOL . "</filesMatch>";
            $rules .= PHP_EOL . "<filesMatch \".(css)$\">";
            $rules .= PHP_EOL . "Header set Cache-Control \"public\"";
            $rules .= PHP_EOL . "</filesMatch>";
            $rules .= PHP_EOL . "<filesMatch \".(js)$\">";
            $rules .= PHP_EOL . "Header set Cache-Control \"private\"";
            $rules .= PHP_EOL . "</filesMatch>";
            $rules .= PHP_EOL . "<filesMatch \".(x?html?|php)$\">";
            $rules .= PHP_EOL . " Header set Cache-Control \"private, must-revalidate\"";
            $rules .= PHP_EOL . "</filesMatch>";
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Optimal Expiration time" . PHP_EOL;        
        }
    
        /* Comprobamos si hay que comprimir contenido */
        if ($this->getValue("compress_content")) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro compress content";
            $rules .= PHP_EOL . "<IfModule mod_deflate.c>";
            $rules .= PHP_EOL . "AddOutputFilterByType DEFLATE text/html text/xml text/css text/plain";
            $rules .= PHP_EOL . "AddOutputFilterByType DEFLATE image/svg+xml application/xhtml+xml application/xml";
            $rules .= PHP_EOL . "AddOutputFilterByType DEFLATE application/rdf+xml application/rss+xml application/atom+xml";
            $rules .= PHP_EOL . "AddOutputFilterByType DEFLATE text/javascript application/javascript application/x-javascript application/json";
            $rules .= PHP_EOL . "AddOutputFilterByType DEFLATE application/x-font-ttf application/x-font-otf";
            $rules .= PHP_EOL . "AddOutputFilterByType DEFLATE font/truetype font/opentype";
            $rules .= PHP_EOL . "</IfModule>";
            $rules .= PHP_EOL . "## End Securitycheck Pro Redirect compress content" . PHP_EOL;
        
        }
    
        /* Comprobamos si hay que redirigir las peticiones no www a www */
        if ( ($this->getValue("redirect_to_www")) && (!$this->ConfigApplied['redirect_to_www']) ){        
            $isSecure = false;
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $isSecure = true;
            }
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
                $isSecure = true;
            }
            $REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';
                
            $str_to_insert = "RewriteEngine On" . PHP_EOL;
            $str_to_insert .= PHP_EOL . "## Securitycheck Pro Redirect non-www to www";
            $str_to_insert .= PHP_EOL . "RewriteCond %{HTTP_HOST} !^www\. [NC]";
            if ($isSecure) {
                $str_to_insert .= PHP_EOL . "RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/$1 [R=301,L]";
            } else
            {
                $str_to_insert .= PHP_EOL . "RewriteRule ^(.*)$ http://www.%{HTTP_HOST}/$1 [R=301,L]";
            }
            $str_to_insert .= PHP_EOL . "## Securitycheck Pro Redirect non-www to www" . PHP_EOL;                                    
            $rules = str_replace("RewriteEngine On", $str_to_insert, $rules);
            
        }
    
        /* Comprobamos si hay que redirigir las peticiones no www a www */
        if ( ($this->getValue("redirect_to_non_www")) && (!$this->ConfigApplied['redirect_to_non_www']) ){        
            $isSecure = false;
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
                $isSecure = true;
            }
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
                $isSecure = true;
            }
            $REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';
                
            $str_to_insert = "RewriteEngine On" . PHP_EOL;
            $str_to_insert .= PHP_EOL . "## Securitycheck Pro Redirect www to non-www";
            $str_to_insert .= PHP_EOL . "RewriteCond %{HTTP_HOST} ^www.(.*)$ [NC]";
            if ($isSecure) {
                $str_to_insert .= PHP_EOL . "RewriteRule ^(.*)$ https://%1/$1 [R=301,L]";
            } else
            {
                $str_to_insert .= PHP_EOL . "RewriteRule ^(.*)$ http://%1/$1 [R=301,L]";
            }
            $str_to_insert .= PHP_EOL . "## Securitycheck Pro Redirect www to non-www" . PHP_EOL;                                    
            $rules = str_replace("RewriteEngine On", $str_to_insert, $rules);
            
        }
    
        // Añadimos la parte final (si es necesario)
        if ($ExistsHtaccess) { 
            $rules .= $rules_end;
        }
    
        /* Comprobamos si hay algo que aplicar */
        if (!is_null($rules)) {
            // Escribimos el contenido del buffer en el fichero '.htaccess'
            $status = JFile::write(JPATH_SITE.DIRECTORY_SEPARATOR.'.htaccess', $rules);
        }
    
        return $status;
    }

    /* Borra el fichero .htaccess*/
    function delete_htaccess()
    {
		try{		
			$res = JFile::delete(JPATH_SITE.DIRECTORY_SEPARATOR.'.htaccess');
			return $res;
		} catch (Exception $e)
		{
			return false;
		}        
    }

    /*Genera las reglas equivalentes a .htaccess en ficheros NGINX */
    function generate_rules()
    {

        $rules = null;
    
        /* Comprobamos si hay que proteger los archivos .ht */
        if ($this->getValue("prevent_access")) {
            $rules .= PHP_EOL . "# Begin Securitycheck Pro Prevent access to .ht files" . PHP_EOL;
            $rules .= "\tlocation ~ /\.ht { deny all; }" . PHP_EOL;
            $rules .= "# End Securitycheck Pro Prevent access to .ht files" . PHP_EOL;        
        }
    
        /* Comprobamos si hay que aplicar la lista de user-agents por defecto */
        if ($this->getValue("default_banned_list")) {
            $user_agent_rules = file_get_contents(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'user_agent_blacklist_nginx.inc');
            // Añadimos el contenido del fichero por defecto al final del buffer
            $rules .= PHP_EOL . $user_agent_rules . PHP_EOL;
        }
    
        /* Comprobamos si hay que aplicar la lista de user-agents establecida por el usuario */
        $own_user_agents = explode(PHP_EOL, $this->getValue("own_banned_list"));
        if (! empty($own_user_agents) && ! (sizeof($own_user_agents) == 1 && trim($own_user_agents[0]) == '' )) {
            $rules .= PHP_EOL . "# Begin Securitycheck Pro User Own Blacklist" . PHP_EOL;
            $count = 1;
            $nginx_list = '';
                    
            foreach ($own_user_agents as $agent)
            {                
                $nginx_list .= trim($agent);                            
                if ($count < sizeof($own_user_agents)) {
                    $nginx_list .= '|';
                    $count++;
                } 
            }
                
            $rules .= "\tif (\$http_user_agent ~* " . $nginx_list . ") { return 403; }" . PHP_EOL;
            $rules .= "# End Securitycheck Pro User Own Blacklist" . PHP_EOL;
        }
    
        /* Comprobamos si hay que deshabilitar la firma del servidor*/
        if ($this->getValue("disable_server_signature")) {
            $rules .= PHP_EOL . "# Begin Securitycheck Pro Disable Server Signature" . PHP_EOL;
            $rules .= "server_tokens off;" . PHP_EOL;
            $rules .= "# End Securitycheck Pro Disable Server Signature" . PHP_EOL;
        }
    
        /* Comprobamos si hay que prohibir los 'easter-eggs' de PHP */
        if ($this->getValue("disallow_php_eggs")) {
            $rules .= PHP_EOL . "# Begin Securitycheck Pro Disallow Php Easter Eggs" . PHP_EOL;
            $rules .= "\tset \$susquery 0;" . PHP_EOL;
            $rules .= "\tif (\$args ~* \"=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\") { set \$susquery 1; }" . PHP_EOL;
            $rules .= "\tif (\$susquery = 1) { return 403; }" . PHP_EOL;
            $rules .= "# End Securitycheck Pro Disallow Php Easter Eggs" . PHP_EOL;
        }
    
        /* Comprobamos si hay que prohibir el acceso a archivos que pueden contener información sensible o que tengan alguna vulnerabilidad */
        $disallow_sensible_files_access = explode(PHP_EOL, $this->getValue("disallow_sensible_files_access"));
        if (! empty($disallow_sensible_files_access) && ! (sizeof($disallow_sensible_files_access) == 1 && trim($disallow_sensible_files_access[0]) == '' )) {
            if ((!$ExistsHtaccess) || (($ExistsHtaccess) &&  (!$this->ConfigApplied['disallow_sensible_files_access']))) {
                $rules .= PHP_EOL . "## Begin Securitycheck Pro Disallow Access To Sensitive Files";
                $rules .=  "\trewrite ^/(";            
                                
                foreach ($disallow_sensible_files_access as $code)
                {                    
                    $rules .= "|" . trim($code);                
                }            
                $rules .= ")$ /not_found last;";
                $rules .= PHP_EOL . "## End Securitycheck Pro Disallow Access To Sensitive Files" . PHP_EOL;
            }
        }
    
        /* Comprobamos si hay que aplicar código del usuario */
        $own_code = explode(PHP_EOL, $this->getValue("own_code"));
        if (! empty($own_code) && ! (sizeof($own_code) == 1 && trim($own_code[0]) == '' )) {
            if ((!$ExistsHtaccess) || (($ExistsHtaccess) &&  (!$this->ConfigApplied['own_code']))) {
                $rules .= PHP_EOL . "## Begin Securitycheck Pro User Own Code";
                $count = 1;
                    
                foreach ($own_code as $code)
                {                    
                    $rules .= PHP_EOL . trim($code);                
                }                
                $rules .= PHP_EOL . "## End Securitycheck Pro User Own Code" . PHP_EOL;
            }
        }
    
        /* Comprobamos si hay que proteger las cabeceras X-Frame del navegador */
        $xframe_options = $this->getValue("xframe_options");
        if ((!empty($xframe_options)) && ($this->getValue("xframe_options") != 'NO')) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Xframe-options protection";
            $rules .= PHP_EOL . "## Don't allow any pages to be framed - Defends against CSRF";
            $rules .= PHP_EOL . 'add_header X-Frame-Options "' . $this->getValue("xframe_options") . '";';            
            $rules .= PHP_EOL . "## End Securitycheck Pro Xframe-options protection" . PHP_EOL;            
        }
    
        /* Comprobamos si hay que establecer protección contra ataques basados en 'mime'*/
        if ($this->getValue("prevent_mime_attacks")) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Prevent mime based attacks";            
            $rules .= PHP_EOL . 'add_header X-Content-Type-Options "nosniff";';    
            $rules .= PHP_EOL . "## End Securitycheck Pro Prevent mime based attacks" . PHP_EOL;        
        }
    
        /* Comprobamos si hay que establecer protección STS (Strict Transport Security) */
        $sts_options = $this->getValue("sts_options");
        if ($sts_options) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro Strict Transport Security";
            $rules .= PHP_EOL . 'add_header Strict-Transport-Security "max-age=31536000; includeSubdomains";';    
            $rules .= PHP_EOL . "## End Securitycheck Pro Strict Transport Security" . PHP_EOL;            
        }
    
        /* Comprobamos si hay que establecer protección X-Xss-Protection */
        $xss_options = $this->getValue("xss_options");
        if ($xss_options) {        
            $rules .= PHP_EOL . "## Begin Securitycheck Pro X-Xss-Protection";
            $rules .= PHP_EOL . 'add_header X-Xss-Protection "1; mode=block"';            
            $rules .= PHP_EOL . "## End Securitycheck Pro X-Xss-Protection" . PHP_EOL;        
        }
    
        /* Comprobamos si hay que ocultar la url del backend */
        if (!is_null($this->getValue("hide_backend_url"))) {
            $rules .= "# Begin Securitycheck Pro Hide Backend Url" . PHP_EOL;
            $rules .= "\tset \$rule_1 0;" . PHP_EOL;
            $rules .= "\tif (\$http_referer !~* administrator) { set \$rule_1 6\$rule_1; }" . PHP_EOL;
            $rules .= "\tif (\$args !~ \"^" . $this->getValue("hide_backend_url") . "\") { set \$rule_1 9\$rule_1; }" . PHP_EOL;
            $rules .= "\tif (\$rule_1 = 960) {" . PHP_EOL;
            $rules .= "\t\trewrite ^(.*/)?administrator /not_found redirect;" . PHP_EOL;
            $rules .= "\t\trewrite ^/administrator(.*)$ /not_found redirect;" . PHP_EOL;
            $rules .= "\t}" . PHP_EOL;
            $rules .= "# End Securitycheck Pro Hide Backend Url" . PHP_EOL;
        }
        
        return $rules;
 
    }

    /* Restaura el fichero .htaccess.original */
    function restore_htaccess()
    {
        // Borramos el fichero .htaccess 
        $this->delete_htaccess();
        return JFile::copy(JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess.original', JPATH_SITE . DIRECTORY_SEPARATOR . '.htaccess');    
    }

}
