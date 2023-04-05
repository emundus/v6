<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Checkin\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Checkin Model
 *
 * @since  1.6
 */
class CheckinModel extends ListModel
{
    /**
     * Count of the total items checked out
     *
     * @var  integer
     */
    protected $total;

    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'table',
                'count',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note: Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'table', $direction = 'asc')
    {
        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Checks in requested tables
     *
     * @param   array  $ids  An array of table names. Optional.
     *
     * @return  mixed  The database results or 0
     *
     * @since   1.6
     */
    public function checkin($ids = [])
    {
        $db = $this->getDatabase();

        if (!is_array($ids)) {
            return 0;
        }

        // This int will hold the checked item count.
        $results = 0;

        $app = Factory::getApplication();

        foreach ($ids as $tn) {
            // Make sure we get the right tables based on prefix.
            if (stripos($tn, $app->get('dbprefix')) !== 0) {
                continue;
            }

            $fields = $db->getTableColumns($tn, false);

            if (!(isset($fields['checked_out']) && isset($fields['checked_out_time']))) {
                continue;
            }

            $query = $db->getQuery(true)
                ->update($db->quoteName($tn))
                ->set($db->quoteName('checked_out') . ' = DEFAULT');

            if ($fields['checked_out_time']->Null === 'YES') {
                $query->set($db->quoteName('checked_out_time') . ' = NULL');
            } else {
                $nullDate = $db->getNullDate();

                $query->set($db->quoteName('checked_out_time') . ' = :checkouttime')
                    ->bind(':checkouttime', $nullDate);
            }

            if ($fields['checked_out']->Null === 'YES') {
                $query->where($db->quoteName('checked_out') . ' IS NOT NULL');
            } else {
                $query->where($db->quoteName('checked_out') . ' > 0');
            }

            $db->setQuery($query);

            if ($db->execute()) {
                $results = $results + $db->getAffectedRows();
                $app->triggerEvent('onAfterCheckin', [$tn]);
            }
        }

        return $results;
    }

    /**
     * Get total of tables
     *
     * @return  integer  Total to check-in tables
     *
     * @since   1.6
     */
    public function getTotal()
    {
        if (!isset($this->total)) {
            $this->getItems();
        }

        return $this->total;
    }

    /**
     * Get tables
     *
     * @return  array  Checked in table names as keys and checked in item count as values.
     *
     * @since   1.6
     */
    public function getItems()
    {
        if (!isset($this->items)) {
            $db     = $this->getDatabase();
            $tables = $db->getTableList();
            $prefix = Factory::getApplication()->get('dbprefix');

            // This array will hold table name as key and checked in item count as value.
            $results = [];

            foreach ($tables as $tn) {
                // Make sure we get the right tables based on prefix.
                if (stripos($tn, $prefix) !== 0) {
                    continue;
                }

                if ($this->getState('filter.search') && stripos($tn, $this->getState('filter.search')) === false) {
                    continue;
                }

                $fields = $db->getTableColumns($tn, false);

                if (!(isset($fields['checked_out']) && isset($fields['checked_out_time']))) {
                    continue;
                }

                $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from($db->quoteName($tn));

                if ($fields['checked_out']->Null === 'YES') {
                    $query->where($db->quoteName('checked_out') . ' IS NOT NULL');
                } else {
                    $query->where($db->quoteName('checked_out') . ' > 0');
                }

                $db->setQuery($query);
                $count = $db->loadResult();

                if ($count) {
                    $results[$tn] = $count;
                }
            }

            $this->total = count($results);

            // Order items by table
            if ($this->getState('list.ordering') == 'table') {
                if (strtolower($this->getState('list.direction')) == 'asc') {
                    ksort($results);
                } else {
                    krsort($results);
                }
            } else {
                // Order items by number of items
                if (strtolower($this->getState('list.direction')) == 'asc') {
                    asort($results);
                } else {
                    arsort($results);
                }
            }

            // Pagination
            $limit = (int) $this->getState('list.limit');

            if ($limit !== 0) {
                $this->items = array_slice($results, $this->getState('list.start'), $limit);
            } else {
                $this->items = $results;
            }
        }

        return $this->items;
    }
}
