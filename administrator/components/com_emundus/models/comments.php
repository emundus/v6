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

        $installation_steps = [];
        $installation_steps[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'ccid', 'INT', null, 0);
        $installation_steps[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'opened', 'TINYINT', 1, 0, 1);
        $installation_steps[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'updated', 'DATETIME');
        $installation_steps[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'updated_by', 'INT', null, 0);
        $installation_steps[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'target_type', 'VARCHAR', 255);
        $installation_steps[] = EmundusHelperUpdate::addColumn('jos_emundus_comments', 'target_id', 'INT', null, 0);

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
                    $installation_steps[] = $db->execute();
                } catch (Exception $e) {
                    $installation_steps[] = false;
                }
            }
        }

        if (!in_array(false, $installation_steps)) {
            $installed = true;
        }

        return $installed;
    }
}
