<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Date\Date;

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_workflow/models');

class EmundusworkflowModelworkflow extends JModelList
{
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    //create workflow -> campaign_id, user_id, created_at, updated_at
    public function createWorkflow($data) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        if(!empty($data)) {
            try {
                $query->clear()
                    ->insert($db->quoteName('#__emundus_workflow'))
                    ->columns($db->quoteName(array_keys($data)))
                    ->values(implode(',', $db->quote(array_values($data))));

                $db->setQuery($query);
                $db->execute();
                return $db->insertid();

            } catch (Exception $e) {
                JLog::add('component/com_emundus_workflow/models/item | Cannot create new workflow : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus_workflow');
                return $e->getMessage();
            }
        }
        else {
            return false;
        }
    }
}
