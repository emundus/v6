<?php
/**
 * Modelo Rules para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Pagination\Pagination as JPagination;

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelRules extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{

    /**
     * Objeto Pagination * @var object 
     */
    var $_pagination = null;

    /**
     * @var array Group list 
     */
    private $groups = array();

    /**
     * @var int Total number of files of Pagination 
     */
    var $total = 0;
	
	var $global_model = null;

    function __construct()
    {
        parent::__construct();
    
    
        $mainframe = JFactory::getApplication();
		
		require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model.php';
		$this->global_model = new SecuritycheckproModel();
    
        // Obtenemos las variables de paginación de la petición
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->input->get('limitstart', 0, 'int');

        // En el caso de que los límites hayan cambiado, los volvemos a ajustar
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    protected function populateState()
    {
        // Inicializamos las variables
        $app        = JFactory::getApplication();
    
        $search = $app->getUserStateFromRequest('filter.acl_search', 'filter_acl_search');
        $this->setState('filter.acl_search', $search);
    
        parent::populateState();
    }

    /*  Función para la paginación */
    function getPagination()
    {
        // Cargamos el contenido si es que no existe todavía
        if (empty($this->_pagination)) {            
            $this->_pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }

    /* Función para cargar los grupos del sistema */
    function load()
    {
        // Creamos un nuevo objeto query
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Obtenemos los grupos de Joomla
        $query_groups = "SELECT * FROM #__usergroups";
        $db->setQuery($query_groups);
        $joomla_groups = $db->loadObjectList();
    
        // Actualizamos el número total de grupos
        $this->total = count($joomla_groups);
    
        // Timestamp
        $timestamp = $this->global_model->get_Joomla_timestamp();
    
        // Comprobamos si hay que añadir algún grupo a la tabla '#__securitycheckpro_rules'
        foreach ($joomla_groups as $element)
        {
            $array_val = get_object_vars($element);
            $group_id = (int) $array_val["id"];
            $title = $array_val["title"];
            $rules_applied = (int) 1;
                
            $query_rules = "SELECT * FROM #__securitycheckpro_rules WHERE group_id = " . $db->escape($group_id);
        
            $db->setQuery($query_rules);
            $element_exists = $db->loadObjectList();
        
            // Si no existe, lo añadimos
            if (empty($element_exists)) {
                  $valor = (object) array(
                 'group_id' => $group_id,
                 'rules_applied' => $rules_applied,
                 'last_change' =>  $timestamp,
                  );
                  $insert_result = $db->insertObject('#__securitycheckpro_rules', $valor, 'id');
            }
        }
    
        // Volvemos a construir la consulta
        $query->select('a.id, a.lft, a.rgt, a.parent_id, a.title, b.rules_applied, b.last_change');
        $query->from($db->quoteName('#__usergroups') . ' AS a');
        
        // Añadimos los niveles de cada grupo...
        $query->select('COUNT(DISTINCT c2.id) AS level')
            ->join('LEFT OUTER', $db->quoteName('#__usergroups') . ' AS c2 ON a.lft > c2.lft AND a.rgt < c2.rgt')
            ->group('a.id, b.group_id, a.lft, a.rgt, a.parent_id, a.title, b.rules_applied, b.last_change');
    
        // ... y si las reglas han de aplicarse
        $query->select('b.group_id, b.rules_applied, b.last_change')
            ->join('LEFT OUTER', $db->quoteName('#__securitycheckpro_rules') . ' AS b ON a.id = b.group_id');
        
        // Filtramos los comentarios de las búsquedas si existen
        $search = $this->getState('filter.acl_search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            }
            else
            {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where('a.title LIKE ' . $search);
            }
        }
    
        // Ordenamos la consulta
        $query->order($db->escape($this->getState('list.ordering', 'a.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
		$db->setQuery($query);
        $groups = $db->loadObjectList();
        
        //Obtenemos el número de registros del array que hemos de mostrar. Si el límite superior es '0', entonces devolvemos todo el array
        $upper_limit = $this->getState('limitstart');
        $lower_limit = $this->getState('limit');
    
        // Devolvemos sólo el contenido delimitado por la paginación 
        $groups = array_splice($groups, $upper_limit, $lower_limit);
    
        return $groups;
    }


    /*  Función que cambia el estado de los grupos pasados como argumento. A dichos grupos se les aplicarán las reglas del Firewall. */
    function apply_rules()
    {
        $resultado = true;
    
        $jinput = JFactory::getApplication()->input;
        $uids = $jinput->getVar('cid', '', 'array');
    
        // Timestamp
        $timestamp = $this->global_model->get_Joomla_timestamp();
    
        Joomla\Utilities\ArrayHelper::toInteger($uids, array());
        
        $db = $this->getDbo();
        foreach($uids as $uid) 
        {
            try 
            {
                  $sql = "UPDATE #__securitycheckpro_rules SET rules_applied=1,last_change='" .$timestamp ."' WHERE group_id='{$uid}'";
                  $db->setQuery($sql);
                  $db->execute();    
            } catch (Exception $e)
            {
                $resultado = false;
                break(1);
            }
        
        }

    
        return $resultado;
    }

    /*  Función que cambia el estado de los grupos pasados como argumento. A dichos grupos NO se les aplicarán las reglas del Firewall. */
    function not_apply_rules()
    {
        $resultado = true;
    
        $jinput = JFactory::getApplication()->input;
        $uids = $jinput->getVar('cid', '', 'array');
    
        // Timestamp
        $timestamp = $this->global_model->get_Joomla_timestamp();
    
        Joomla\Utilities\ArrayHelper::toInteger($uids, array());
        
        $db = $this->getDbo();
        foreach($uids as $uid)
        {
            try 
            {
                  $sql = "UPDATE #__securitycheckpro_rules SET rules_applied=0,last_change='" .$timestamp ."' WHERE group_id='{$uid}'";
                  $db->setQuery($sql);
                  $db->execute();
            }    catch (Exception $e)
            {
                $resultado = false;
                break(1);
            }
        
        
        }

    
        return $resultado;
    }

}
