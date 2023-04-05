<?php
/**
 * Modelo Logs para el Componente Securitycheckpro
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Chequeamos si el archivo está incluído en Joomla!
defined('_JEXEC') or die();

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Date\Date as JDate;
use Joomla\CMS\Table\Table as JTable;

/**
 * Modelo Vulninfo
 */
class SecuritycheckprosModelTrackActions_Logs extends JModelList
{

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
            'a.id', 'id',
            'a.extension', 'extension',
            'a.user_id', 'user',
            'a.message', 'message',
            'a.log_date', 'log_date',
            'a.ip_address', 'ip_address'
            );
        }
    
        parent::__construct($config);
    
    }

    protected function populateState($ordering = null,$direction = null)
    {
        // Inicializamos las variables
        $app        = JFactory::getApplication();
    
        $search = $app->getUserStateFromRequest('filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $user = $app->getUserStateFromRequest('filter.user', 'filter_user');
        $this->setState('filter.user', $user);
        $extension = $app->getUserStateFromRequest('filter.extension', 'filter_extension');
        $this->setState('filter.extension', $extension);
        $ip_address = $app->getUserStateFromRequest('filter.ip_address', 'filter_ip_address');
        $this->setState('filter.ip_address', $ip_address);
        $daterange = $app->getUserStateFromRequest('daterange', 'daterange');
        $this->setState('daterange', $daterange);
    
        parent::populateState('id', 'DESC');
    }

    public function getListQuery()
    {
        
        // Chequeamos el rango para borrar logs
        $this->checkIn();

        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->from($db->quoteName('#__securitycheckpro_trackactions', 'a'));

        // Get ordering
        $fullorderCol = $this->state->get('list.fullordering', 'a.id DESC');

        // Apply ordering
        if (!empty($fullorderCol)) {
            $query->order($db->escape($fullorderCol));
        }

        // Get filter by user
        $user = $this->getState('filter.user');

        // Apply filter by user
        if (!empty($user)) {
            $query->where($db->quoteName('a.user_id') . ' = ' . (int) $user);
        }

        // Get filter by extension
        $extension = $this->getState('filter.extension');

        // Apply filter by extension
        if (!empty($extension)) {
            $query->where($db->quoteName('a.extension') . ' = ' . $db->quote($extension));
        }

        // Get filter by date range
        $dateRange = $this->getState('filter.dateRange');

        // Apply filter by date range
        if (!empty($dateRange)) {
            $date = $this->buildDateRange($dateRange);

            // If the chosen range is not more than a year ago
            if ($date['dNow'] != false) {
                $query->where(
                    $db->qn('a.log_date') . ' >= ' . $db->quote($date['dStart']->format('Y-m-d H:i:s')) .
                    ' AND ' . $db->qn('a.log_date') . ' <= ' . $db->quote($date['dNow']->format('Y-m-d H:i:s'))
                );
            }
        }

        // Filter the items over the search string if set.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where('(a.message LIKE ' . $search . ')');
        }

        return $query;
    }
        
    /**
     * Check for old logs that needs to be deleted_comment
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    protected function checkIn()
    {
        include_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_securitycheckpro'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'model.php';
        $model = new SecuritycheckproModel();
        
        //  Parámetros del componente
        $items= $model->getConfig();
        $daysToDeleteAfter = (int) $items['delete_period'];
        
        if ($daysToDeleteAfter > 0) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $conditions = array($db->quoteName('log_date') . ' < DATE_SUB(NOW(), INTERVAL ' . $daysToDeleteAfter . ' DAY)');

            $query->delete($db->quoteName('#__securitycheckpro_trackactions'))->where($conditions);
            $db->setQuery($query);

            try
            {
                $db->execute();
            }
            catch (RuntimeException $e)
            {
                JFactory::getApplication()->enqueueMessage($db->getMessage(), 'warning');
                return false;
            }
        }
        
    }        
    
    /**
     * Construct the date range to filter on.
     *
     * @param string $range The textual range to construct the filter for.
     *
     * @return string  The date range to filter on.
     *
     * @since __DEPLOY_VERSION__
     */
    private function buildDateRange($range)
    {
        // Get UTC for now.
        $dNow   = new JDate;
        $dStart = clone $dNow;

        switch ($range)
        {
        case 'past_week':
            $dStart->modify('-7 day');
            break;

        case 'past_1month':
            $dStart->modify('-1 month');
            break;

        case 'past_3month':
            $dStart->modify('-3 month');
            break;

        case 'past_6month':
            $dStart->modify('-6 month');
            break;

        case 'post_year':
            $dNow = false;
        case 'past_year':
            $dStart->modify('-1 year');
            break;

        case 'today':
            // Ranges that need to align with local 'days' need special treatment.
            $offset = JFactory::getApplication()->get('offset');

            // Reset the start time to be the beginning of today, local time.
            $dStart = new JDate('now', $offset);
            $dStart->setTime(0, 0, 0);

            // Now change the timezone back to UTC.
            $tz = new DateTimeZone('GMT');
            $dStart->setTimezone($tz);
            break;

        case 'never':
            $dNow = false;
            $dStart = $this->_db->getNullDate();
            break;

        default:
            return $range;
            break;
        }

        return array('dNow' => $dNow, 'dStart' => $dStart);
    }

    /* Función para borrar un array de logs */
    function delete()
    {
       	$input = JFactory::getApplication()->input;
		$uids = $input->get('cid', null, 'array');
    
        JArrayHelper::toInteger($uids, array());
    
        // Chequeamos si se ha seleccionado algún elemento
        if (empty($uids)) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_SECURITYCHECKPRO_NO_ELEMENTS_SELECTED"), 'warning');
            return false;
        }
    
        $db = $this->getDbo();
        foreach($uids as $uid)
        {
            $sql = "DELETE FROM #__securitycheckpro_trackactions WHERE id='{$uid}'";
            $db->setQuery($sql);
            $db->execute();    
        }
    }

    /* Función para runcar una tabla */
    function delete_all()
    {
        $db = $this->getDbo();
    
        $sql = "TRUNCATE table #__securitycheckpro_trackactions";
        $db->setQuery($sql);
        $db->execute();        
    }

    /**
     * Get logs data into JTable object
     *
     * @return Array  All logs in the table
     *
     * @since __DEPLOY_VERSION__
     */
    public function getLogsData($pks = null)
    {
        if ($pks == null) {
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                ->select('a.*')
                ->from($db->quoteName('#__securitycheckpro_trackactions', 'a'));
            $db->setQuery($query);

            return $db->loadObjectList();
        }
        else
        {
            $items = array();
            JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_securitycheckpro/tables');
            $table = $this->getTable('TrackActions', 'JTable');

            foreach ($pks as $i => $pk)
            {
                $table->load($pk);
                $items[] = (object) array(
                'id'         => $table->id,
                'message'    => $table->message,
                'log_date'   => $table->log_date,
                'extension'  => $table->extension,
                'user_id'    => $table->user_id,
                'ip_address' => $table->ip_address,
                );
            }

            return $items;
        }
    }


}
