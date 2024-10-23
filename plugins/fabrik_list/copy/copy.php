<?php
/**
 * List Copy Row plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.copy
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 * Add an action button to the list to copy rows
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.copy
 * @since       3.0
 */
class PlgFabrik_ListCopy extends PlgFabrik_List
{
	/**
	 * Button prefix
	 *
	 * @var string
	 */
	protected $buttonPrefix = 'copy';

	/**
	 * Prep the button if needed
	 *
	 * @param   array  &$args  Arguments
	 *
	 * @return  bool;
	 */
	public function button(&$args)
	{
		parent::button($args);

		return true;
	}

	/**
	 * Get the button label
	 *
	 * @return  string
	 */
	protected function buttonLabel()
	{
		return FText::_($this->getParams()->get('copytable_button_label', parent::buttonLabel()));
	}

	/**
	 * Get button image
	 *
	 * @return   string  image
	 * @since   3.1b
	 *
	 */

	protected function getImageName()
	{
		$img = parent::getImageName();

		if (FabrikWorker::j3() && $img === 'copy.png')
		{
			$img = 'copy';
		}

		return $img;
	}

	/**
	 * Get the parameter name that defines the plugins acl access
	 *
	 * @return  string
	 */
	protected function getAclParam()
	{
		return 'copytable_access';
	}

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */
	public function canSelectRows()
	{
		return true;
	}

	/**
	 * Do the plug-in action
	 *
	 * @param   array  $opts  Custom options
	 *
	 * @return  bool
	 */
	public function process($opts = array())
	{
		$model         = $this->getModel();
		$ids           = $this->app->input->get('ids', array(), 'array');
		$formModel     = $model->getFormModel();
		$copied_rights = $this->getParams()->get('copytable_group_rights', 0);
		$status        = $model->copyRows($ids);

		if ($copied_rights && sizeof($ids) == 1)
		{
			$id     = reset($ids);
			$status = $this->copyGroupRights($id, $formModel->formData['rowid']);
		}

		return $status;
	}

	/**
	 * Copy group rights into another group
	 *
	 * @param   int  $id            group's id to copy rights from
	 * @param   int  $new_group_id  New group obtaining the copied rights
	 *
	 * @return bool
	 */
	public function copyGroupRights($id, $new_group_id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('action_id, c, r, u, d')
			->from($db->quoteName('#__emundus_acl'))
			->where($db->quoteName('group_id') . ' = ' . $db->quote($id));
		$db->setQuery($query);
		$acl = $db->loadObjectList();

		foreach ($acl as $aclItem)
		{
			$query->clear();

			if ($aclItem->action_id == 1)
			{
				$query->update($db->quoteName('#__emundus_acl'))
					->set($db->quoteName('action_id') . ' = ' . 1)
					->set($db->quoteName('c') . ' = ' . $aclItem->c)
					->set($db->quoteName('r') . ' = ' . $aclItem->r)
					->set($db->quoteName('u') . ' = ' . $aclItem->u)
					->set($db->quoteName('d') . ' = ' . $aclItem->d)
					->where($db->quoteName('group_id') . ' = ' . $db->quote($new_group_id));
			}
			else
			{
				$query->insert($db->quoteName('#__emundus_acl'))
					->set($db->quoteName('group_id') . ' = ' . $new_group_id)
					->set($db->quoteName('action_id') . ' = ' . $aclItem->action_id)
					->set($db->quoteName('c') . ' = ' . $aclItem->c)
					->set($db->quoteName('r') . ' = ' . $aclItem->r)
					->set($db->quoteName('u') . ' = ' . $aclItem->u)
					->set($db->quoteName('d') . ' = ' . $aclItem->d);
			}

			try
			{
				$db->setQuery($query);
				$db->execute();

			}
			catch (Exception $e)
			{
				Log::add('component/com_fabrik/models/list | Error : Group rights could not be copy', Log::ERROR, 'com_emundus');

				return false;
			}
		}

		return true;
	}


	/**
	 * Get the message generated in process()
	 *
	 * @param   int  $c  plugin render order
	 *
	 * @return  string
	 */
	public function process_result($c)
	{
		$ids = $this->app->input->get('ids', array(), 'array');

		return JText::sprintf('PLG_LIST_ROWS_COPIED', count($ids));
	}

	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   array  $args  Array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */
	public function onLoadJavascriptInstance($args)
	{
		parent::onLoadJavascriptInstance($args);
		$opts             = $this->getElementJSOptions();
		$opts             = json_encode($opts);
		$this->jsInstance = "new FbListCopy($opts)";

		return true;
	}

	/**
	 * Load the AMD module class name
	 *
	 * @return string
	 */
	public function loadJavascriptClassName_result()
	{
		return 'FbListCopy';
	}
}
