<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Messages Component Messages Model
 *
 * @since  1.6
 */
class MessagesModel extends ListModel
{
    /**
     * Override parent constructor.
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
                'message_id', 'a.id',
                'subject', 'a.subject',
                'state', 'a.state',
                'user_id_from', 'a.user_id_from',
                'user_id_to', 'a.user_id_to',
                'date_time', 'a.date_time',
                'priority', 'a.priority',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'a.date_time', $direction = 'desc')
    {
        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string    A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        $user = Factory::getUser();
        $id   = (int) $user->get('id');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a') . '.*',
                    $db->quoteName('u.name', 'user_from'),
                ]
            )
        );
        $query->from($db->quoteName('#__messages', 'a'));

        // Join over the users for message owner.
        $query->join(
            'INNER',
            $db->quoteName('#__users', 'u'),
            $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id_from')
        )
            ->where($db->quoteName('a.user_id_to') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        // Filter by published state.
        $state = $this->getState('filter.state');

        if (is_numeric($state)) {
            $state = (int) $state;
            $query->where($db->quoteName('a.state') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        } elseif ($state !== '*') {
            $query->whereIn($db->quoteName('a.state'), [0, 1]);
        }

        // Filter by search in subject or message.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $search = '%' . str_replace(' ', '%', trim($search)) . '%';
            $query->extendWhere(
                'AND',
                [
                    $db->quoteName('a.subject') . ' LIKE :subject',
                    $db->quoteName('a.message') . ' LIKE :message',
                ],
                'OR'
            )
                ->bind(':subject', $search)
                ->bind(':message', $search);
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.date_time')) . ' ' . $db->escape($this->getState('list.direction', 'DESC')));

        return $query;
    }

    /**
     * Purge the messages table of old messages for the given user ID.
     *
     * @param   int  $userId  The user id
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function purge(int $userId): void
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['cfg_name', 'cfg_value']))
            ->from($db->quoteName('#__messages_cfg'))
            ->where(
                [
                    $db->quoteName('user_id') . ' = :userId',
                    $db->quoteName('cfg_name') . ' = ' . $db->quote('auto_purge'),
                ]
            )
            ->bind(':userId', $userId, ParameterType::INTEGER);

        $db->setQuery($query);
        $config = $db->loadObject();

        // Default is 7 days
        $purge = 7;

        // Check if auto_purge value set
        if (\is_object($config) && $config->cfg_name === 'auto_purge') {
            $purge = $config->cfg_value;
        }

        // If purge value is not 0, then allow purging of old messages
        if ($purge > 0) {
            // Purge old messages at day set in message configuration
            $past = Factory::getDate(time() - $purge * 86400)->toSql();

            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__messages'))
                ->where(
                    [
                        $db->quoteName('date_time') . ' < :past',
                        $db->quoteName('user_id_to') . ' = :userId',
                    ]
                )
                ->bind(':past', $past)
                ->bind(':userId', $userId, ParameterType::INTEGER);

            $db->setQuery($query);
            $db->execute();
        }
    }
}
