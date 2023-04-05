<?php
/**
 * Fabrik Admin Visualization Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

require_once 'fabmodeladmin.php';

/**
 * Fabrik Admin Visualization Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */
class FabrikAdminModelVisualization extends FabModelAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 */
	protected $text_prefix = 'COM_FABRIK_VISUALIZATION';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table	A database object
	 */
	public function getTable($type = 'Visualization', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabrikWorker::getDbo(true);

		return FabTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array  $data      Data for the form.
	 * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A Form object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fabrik.visualization', 'visualization', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$form->model = $this;

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed	The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = $this->app->getUserState('com_fabrik.edit.visualization.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * get html form fields for a plugin (filled with
	 * current element's plugin data
	 *
	 * @param   string  $plugin  plugin name
	 *
	 * @return  string	html form fields
	 */
	public function getPluginHTML($plugin = null)
	{
		$input = $this->app->input;
		$item = $this->getItem();

		if (is_null($plugin))
		{
			$plugin = $item->plugin;
		}

		$input->set('view', 'visualization');
		PluginHelper::importPlugin('fabrik_visualization', $plugin);

		if ($plugin == '')
		{
			$str = Text::_('COM_FABRIK_SELECT_A_PLUGIN');
		}
		else
		{
			$plugin = $this->pluginManager->getPlugIn($plugin, 'Visualization');
			$mode = 'nav-tabs';
			$str = $plugin->onRenderAdminSettings(ArrayHelper::fromObject($item), null, $mode);
		}

		return $str;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   Form   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     FormRule
	 * @see     InputFilter
	 */
	public function validate($form, $data, $group = null)
	{
        $params = $data['params'];
		$data = parent::validate($form, $data);

		// Standard jForm validation failed so we shouldn't test further as we can't be sure of the data
		if (!$data)
		{
			return false;
		}

        // Hack - must be able to add the plugin xml fields file to $form to include in validation but cant see how at the moment
        $data['params'] = $params;

        return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 */
	public function save($data)
	{
		$input = $this->app->input;
		$date = Factory::getDate();
		$row = $this->getTable();
		$id = FArrayHelper::getValue($data, 'id');
		$row->load($id);

		$row->bind($data);

		if (FabrikWorker::isNullDate($row->get('created'))) {
			$row->set('created', $date->toSql());
		}

		$isNew = false;

		if ($row->get('id') == 0) {
			if (FabrikWorker::isNullDate($row->get('created'))) {
				$row->set('created', $date->toSql());
				$row->set('created_by', $this->user->get('id'));
				$row->set('created_by_alias', $this->user->get('username'));
			}
			$isNew = true;
		}

		if (empty($row->get('created_by'))) {
			$row->set('created_by', $this->user->get('id'));
			$row->set('created_by_alias', $this->user->get('username'));
		}
		
		$row->set('modified', $date->toSql());
		$row->set('modified_by', $this->user->get('id'));

		// Set the publish date
		if (FabrikWorker::isNullDate($row->get('publish_up'))) {
			if ($row->get('published') == 1) {
				$row->set('publish_up', Factory::getDate()->toSql());
			} else {
				$row->set('publish_up', null);
			}
		}

		if (FabrikWorker::isNullDate($row->get('publish_down'))) {
			$row->set('publish_down', null);
		}

		$row->store();

		$this->setState('visualization.id', $row->get('id'));
		$this->setState('visualization.new', $isNew);

//		parent::cleanCache('com_fabrik');

		return true;
	}
}
