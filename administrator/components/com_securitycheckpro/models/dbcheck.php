<?php
/**
 * @package   RSFirewall!
 * @copyright (C) 2009-2014 www.rsjoomla.com
 * @license   GPL, http://www.gnu.org/licenses/gpl-2.0.html
 * @ modified by Jose A. Luque for Securitycheck Pro Control Center extension
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;

use Joomla\CMS\Component\ComponentHelper as JComponentHelper;

class SecuritycheckprosModelDbCheck extends SecuritycheckproModel
{


    public function __construct()
    {
        parent::__construct();
    }
    
    /* Función que comprueba si la base de datos es mysql y existen tablas que optimizar */
    public function getIsSupported() 
    {
        return (strpos(JFactory::getApplication()->getCfg('dbtype'), 'mysql') !== false && $this->getTables());
    }
    
    /* Función que obtiene las tablas a optimizar */
    public function getTables() 
    {
        static $cache;
        
        // Extraemos la configuración de qué tablas mostrar
        $params = JComponentHelper::getParams('com_securitycheckpro');
        $tables_to_check = $params->get('tables_to_check', 'All');
    
        if (is_null($cache)) {
            $db = $this->getDbo();
            $db->setQuery("SHOW TABLE STATUS");
            $tables = $db->loadObjectList();
            // Si sólo tenemos que mostrar las tablas 'MyISAM', excluimos las demás
            if ($tables_to_check == 'Myisam') {
                foreach ($tables as $i => $table)
                {
                    if (isset($table->Engine) && $table->Engine != 'MyISAM') {
                        unset($tables[$i]);
                    }
                }
            }
            
            $cache = array_values($tables);
        }
        
        return $cache;
    }
    
    /* Función para optimizar y reparar tablas */
    public function optimizeTables()
    {
        $app     = JFactory::getApplication();
        $db     = $this->getDbo();
        $query    = $db->getQuery(true);
        $table     = $app->input->getVar('table');
        $engine     = $app->input->getVar('engine');
        $return = array(
        'optimize' => '',
        'repair' => ''
        );
        
        if ($engine == 'MyISAM') {        
            try 
            {
                // Optimize
                $db->setQuery("OPTIMIZE TABLE ".$db->qn($table));
                $result = $db->loadObject();
                $return['optimize'] = $result->Msg_text;
            } catch (Exception $e) 
            {
                $this->setError($e->getMessage());
                return false;
            }
            
            try
            {
                // Repair
                $db->setQuery("REPAIR TABLE ".$db->qn($table));
                $result = $db->loadObject();
                $return['repair'] = $result->Msg_text;
            } catch (Exception $e)
            {
                return false;
            }
        }
        
		$timestamp = $this->get_Joomla_timestamp();
		
        /* Actualizamos el campo que indica la última optimización de la bbdd */
        $this->set_campo_filemanager('last_check_database', $timestamp);
                
        return $return;
    }
}
