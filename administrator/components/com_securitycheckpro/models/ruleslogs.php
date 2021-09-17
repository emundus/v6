<?php
/**
 * Modelo RulesLogs para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo est� inclu�do en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Pagination\Pagination as JPagination;

/**
 * Modelo Securitycheck
 */
class SecuritycheckprosModelRulesLogs extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{

    /**
     * Objeto Pagination * @var object 
     */
    var $_pagination = null;

    /**
     * @var int Total number of files of Pagination 
     */
    var $total = 0;

    function __construct()
    {
        parent::__construct();
    
    
        $mainframe = JFactory::getApplication();
    
        // Obtenemos las variables de paginaci�n de la petici�n
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $jinput = JFactory::getApplication()->input;
        $limitstart = $jinput->get('limitstart', 0, 'int');

        // En el caso de que los l�mites hayan cambiado, los volvemos a ajustar
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    protected function populateState()
    {
        // Inicializamos las variables
        $app        = JFactory::getApplication();
    
        $search = $app->getUserStateFromRequest('filter.rules_search', 'filter_rules_search');
        $this->setState('filter.rules_search', $search);
    
        parent::populateState();
    }

    /*  Funci�n para la paginaci�n */
    function getPagination()
    {
        // Cargamos el contenido si es que no existe todav�a
        if (empty($this->_pagination)) {           
            $this->_pagination = new JPagination($this->total, $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }

    /* Funci�n para cargar los logs de confianza */
    function load_rules_logs()
    {
        // Creamos un nuevo objeto query
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Obtenemos los grupos de Joomla
        $query->select('a.*');
        $query->from($db->quoteName('#__securitycheckpro_rules_logs') . ' AS a');
        
        // Filtramos los comentarios de las b�squedas si existen
        $search = $this->getState('filter.rules_search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%');
            $query->where('(a.ip LIKE ' . $search . ' OR a.username LIKE '. $search . ' OR a.last_entry LIKE '. $search . ' OR a.reason LIKE '. $search .')');
        }
    
    
        $db->setQuery($query);
        $items = $db->loadObjectList();
    
        // Actualizamos el n�mero total de elementos para la paginaci�n
        $this->total = count($items);
    
        /* Obtenemos el n�mero de registros del array que hemos de mostrar. Si el l�mite superior es '0', entonces devolvemos todo el array */
        $upper_limit = $this->getState('limitstart');
        $lower_limit = $this->getState('limit');
    
        /* Devolvemos s�lo el contenido delimitado por la paginaci�n */
        $items = array_splice($items, $upper_limit, $lower_limit);

        return $items;
    }

}
