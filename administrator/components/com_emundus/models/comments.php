<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
require_once(JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');

/**
 * @package     com_emundus
 *
 * @since version 1.34.0
 */
class EmundusAdministratorModelComments extends JModelList
{
    /**
     * @return void
     */
    public function checkup(): void
    {
        // TODO: Implement checkup() method.
    }


    /**
     * @return bool
     */
    public function install(): bool
    {
        $installed = false;

        $tasks = [];
        $tasks[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'ccid', 'INT', null, 0);
        $tasks[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'parent_id', 'INT', null, 0, 0);
        $tasks[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'opened', 'TINYINT', 1, 0, 1);
        $tasks[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'updated', 'DATETIME');
        $tasks[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'updated_by', 'INT');
        $tasks[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'target_type', 'VARCHAR', 255);
        $tasks[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'target_id', 'INT');
        $tasks[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'visible_to_applicant', 'TINYINT', 1, 0, 1);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COLUMN_NAME')
            ->from('INFORMATION_SCHEMA.COLUMNS')
            ->where('TABLE_NAME = ' . $db->quoteName('jos_emundus_comments'))
            ->andWhere('COLUMN_NAME = ' . $db->quoteName('status_from') . ' OR COLUMN_NAME = ' . $db->quoteName('status_to'));

        try {
            $db->setQuery($query);
            $columns = $db->loadColumn();
        } catch (Exception $e) {
            $columns = [];
        }

        if (!empty($columns)) {
            foreach ($columns as $column) {
                try {
                    $db->setQuery('ALTER TABLE ' . $db->quoteName('jos_emundus_comments') . ' DROP COLUMN ' . $db->quoteName($column));
                    $tasks[] = $db->execute();
                } catch (Exception $e) {
                    $tasks[] = false;
                }
            }
        }

        $columns = [
            [
                'name' => 'comment_id',
                'type' => 'INT',
                'null' => 0,
            ],
            [
                'name' => 'reader_id',
                'type' => 'INT',
                'null' => 0,
            ],
        ];
        $foreign_keys = [
            [
                'name'           => 'jos_emundus_comments_read_by_jos_emundus_comments_id_fk',
                'from_column'    => 'comment_id',
                'ref_table'      => 'jos_emundus_comments',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
            [
                'name'           => 'jos_emundus_comments_read_by_jos_users_id_fk',
                'from_column'    => 'reader_id',
                'ref_table'      => 'jos_users',
                'ref_column'     => 'id',
                'update_cascade' => true,
                'delete_cascade' => true,
            ],
        ];
        $tasks[] = EmundusHelperUpdate::createTable('jos_emundus_comments_read_by', $columns, $foreign_keys, 'Liste des utilisateurs ayant lu un commentaire');

        $tasks[] = EmundusHelperUpdate::addCustomEvents([
            ['label' => 'onAfterCommentAdded', 'category' => 'Comments'],
            ['label' => 'onAfterCommentDeleted', 'category' => 'Comments'],
            ['label' => 'onAfterCommentUpdated', 'category' => 'Comments']
        ]);

        $tasks_status = array_map(function ($task) { return $task['status']; }, $tasks);
        if (!in_array(false, $tasks_status)) {
            $installed = true;
        }

        return $installed;
    }
}
