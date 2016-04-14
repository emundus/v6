<?php
/**
 * @package    gantry
 * @subpackage core
 * @version    4.1.31 April 11, 2016
 * @author     RocketTheme http://www.rockettheme.com
 * @copyright  Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');


if (version_compare(JVERSION, '3.0', '<')) {
	abstract class GantryModelTemplateIntermediate extends JModelAdmin
	{
		protected function prepareTable(&$table)
		{
			$this->gPrepareTable($table);
		}
		abstract protected function gPrepareTable($table);
	}
} else {
	abstract class GantryModelTemplateIntermediate extends JModelAdmin
	{
		protected function prepareTable($table)
		{
			$this->gPrepareTable($table);
		}

		abstract protected function gPrepareTable($table);
	}
}

/**
 * Template style model.
 *
 * @package        Joomla.Administrator
 * @subpackage     com_templates
 * @since          1.6
 */
class GantryModelTemplate extends GantryModelTemplateIntermediate
{
	/**
	 * @var        string    The help screen key for the module.
	 * @since    1.6
	 */
	protected $helpKey = 'JHELP_EXTENSIONS_TEMPLATE_MANAGER_STYLES_EDIT';

	/**
	 * @var        string    The help screen base URL for the module.
	 * @since    1.6
	 */
	protected $helpURL;

	/**
	 * Item cache.
	 */
	private $_cache = array();

	private $_formCache = array();

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since    1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$pk = (int)$app->input->getInt('id');
		$this->setState('template.id', $pk);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_gantry');
		$this->setState('params', $params);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param    array      $data        An optional array of data for the form to interogate.
	 * @param    boolean    $loadData    True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// The folder and element vars are passed when saving the form.
		if (empty($data)) {
			$item     = $this->getItem();
			$clientId = $item->client_id;
			$template = $item->template;
		} else {
			$clientId = JArrayHelper::getValue($data, 'client_id');
			$template = JArrayHelper::getValue($data, 'template');
		}

		// These variables are used to add data from the plugin XML files.
		$this->setState('item.client_id', $clientId);
		$this->setState('item.template', $template);

		// Get the form.
		$form = $this->loadForm('com_gantry.template', 'template', array(
		                                                                'control'  => 'jform', 'load_data' => $loadData
		                                                           ));
		if (empty($form)) {
			return false;
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object)$data)) {
			// Disable fields for display.
			$form->setFieldAttribute('home', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('home', 'filter', 'unset');
		}

		return $form;
	}

	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.edit.state', 'com_templates');
	}

	public function getGantryForm()
	{
		gantry_import('core.config.gantryform');
		gantry_import('core.config.gantryformnaminghelper');

		$pk                         = (!empty($pk)) ? $pk : (int)$this->getState('template.id');
		$item                       = $this->getItem($pk);
		$item->params['current_id'] = $pk;
		if (!isset($this->_formCache[$pk])) {
			$naming_helper = GantryFormNamingHelper::getInstance();
			$form          = GantryForm::getInstance($naming_helper, 'template-options', 'template-options', array(), true, "//form");
			$form->bind($item->params);
			$this->_formCache[$pk] = $form;
		}
		return $this->_formCache[$pk];
	}


	public function checkForGantryUpdate()
	{
		try {
			gantry_import('core.gantryupdates');
			$gantry_updates = GantryUpdates::getInstance();

			$last_updated = $gantry_updates->getLastUpdated();
			$diff         = time() - $last_updated;
			if ($diff > (60 * 60 * 24)) {
				jimport('joomla.updater.updater');
				// check for update
				$updater = JUpdater::getInstance();
				$results = @$updater->findUpdates($gantry_updates->getGantryExtensionId());
				$gantry_updates->setLastChecked(time());
			}
		} catch (Exception $e) {
			if (!($e->getCode() == 0 && $e->getMessage() == 'No HTTP response received.'))
			{
				throw $e;
			}
		}
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_templates.edit.style.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param    integer    The id of the primary key.
	 *
	 * @return    mixed    Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int)$this->getState('template.id');

		if (!isset($this->_cache[$pk])) {
			$false = false;

			// Get a row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return $false;
			}

			// Convert to the JObject before adding other data.
			$table_props       = $table->getProperties(1);
			$this->_cache[$pk] = JArrayHelper::toObject($table_props, 'JObject');

			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadString($table->params);

			$item_params = $registry->toArray();
			if (array_key_exists('master', $item_params) && $item_params['master'] != 'true') {
				$master_params = $this->getItem((int)$item_params['master'])->params;
				if (count($master_params) <= 1)
				{
					$default_params = $this->getDefaultTemplateParams($table->template);
					$master_params = $this->array_join($master_params, $default_params);
				}
				$item_params   = $this->array_join($master_params, $item_params);
			}
            if(@ini_get('magic_quotes_gpc')=='1'){
                $item_params = self::_stripSlashesRecursive($item_params);
            }

			$this->_cache[$pk]->params = $item_params;
		}

		return $this->_cache[$pk];
	}

	protected function getDefaultTemplateParams($template_name)
	{
       //   xpath for names //form//field|//form//fields[@default]|//form//fields[@value]
        //   xpath for parents  ancestor::fields[@name][not(@ignore-group)]/@name|ancestor::set[@name]/@name
        $xml = JFactory::getXML(JPATH_SITE . '/templates/' . $template_name . '/template-options.xml');

        $params   = $xml->xpath('//form//field|//form//fields[@default]|//form//fields[@value]');
        $defaults = array();
        foreach ($params as $param) {
            $attrs    = $param->xpath('ancestor::fields[@name][not(@ignore-group)]/@name|ancestor::set[@name]/@name');
            $groups   = array_map('strval', $attrs ? $attrs : array());
            $groups[] = (string)$param['name'];
	        array_walk($groups, create_function('&$value,$key', '$value = \'[\\\'\'.$value.\'\\\']\';'));
            //array_walk($groups,  create_function('&$item,$k', '$item = "[\'" . $item . "\']");'));
            $def_array_eval = '$defaults' . implode('', $groups) . ' = (string)$param[\'default\'];';
            if ($param['default']) @eval($def_array_eval);
        }
        //$defaults = $this->arrayToObject($defaults);
        return $defaults;
	}

	public function getOverride($pk = null)
	{
		$pk     = (!empty($pk)) ? $pk : (int)$this->getState('template.id');
		$params = $this->getItem($pk)->params;
		if (array_key_exists('master', $params) && $params['master'] != 'true') {
			return true;
		}
		return false;
	}

	public function getBaseData($pk = null)
	{
		$pk     = (!empty($pk)) ? $pk : (int)$this->getState('template.id');
		$params = $this->getItem($pk)->params;
		if ($params->get('master') != 'true') {
			$params = $this->getItem($params->get('master'));
		}
		return $params;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param    type      The table type to instantiate
	 * @param    string    A prefix for the table class name. Optional.
	 * @param    array     Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 */
	public function getTable($type = 'Style', $prefix = 'TemplatesTable', $config = array())
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 */
	protected function gPrepareTable($table)
	{
		$table;
	}

	/**
	 * @param    object    A form object.
	 * @param    mixed     The data expected for the form.
	 *
	 * @throws    Exception if there is an error in the form event.
	 * @since    1.6
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		// Initialise variables.
		$clientId = $this->getState('item.client_id');
		$template = $this->getState('item.template');
		$lang     = JFactory::getLanguage();
		$client   = JApplicationHelper::getClientInfo($clientId);
		if (!$form->loadFile('template_' . $client->name, true)) {
			throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$formFile = JPath::clean($client->path . '/templates/' . $template . '/templateDetails.xml');

		// Load the core and/or local language file(s).
		$lang->load('tpl_' . $template, $client->path, null, false, false) || $lang->load('tpl_' . $template, $client->path . '/templates/' . $template, null, false, false) || $lang->load('tpl_' . $template, $client->path, $lang->getDefault(), false, false) || $lang->load('tpl_' . $template, $client->path . '/templates/' . $template, $lang->getDefault(), false, false);

		if (file_exists($formFile)) {
			// Get the template form.
			if (!$form->loadFile($formFile, false, '//config')) {
				throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
			}
		}

		// Disable home field if it is default style

		if ((is_array($data) && array_key_exists('home', $data)) || ((is_object($data) && $data->home))) {
			$form->setFieldAttribute('home', 'readonly', 'true');
		}

		// Attempt to load the xml file.
		if (!$xml = simplexml_load_file($formFile)) {
			throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
		}

		// Get the help data from the XML file if present.
		$help = $xml->xpath('/extension/help');
		if (!empty($help)) {
			$helpKey = trim((string)$help[0]['key']);
			$helpURL = trim((string)$help[0]['url']);

			$this->helpKey = $helpKey ? $helpKey : $this->helpKey;
			$this->helpURL = $helpURL ? $helpURL : $this->helpURL;
		}

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param    array    The form data.
	 *
	 * @return    boolean    True on success.
	 */
	public function save($data)
	{
		require_once(JPATH_LIBRARIES . "/gantry/gantry.php");

		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$table      = $this->getTable();
		$pk         = (!empty($data['id'])) ? $data['id'] : (int)$this->getState('template.id');
		$isNew      = true;

		// Include the extension plugins for the save events.
		JPluginHelper::importPlugin('extension');

		// Load the row if saving an existing record.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		if (!array_key_exists('home', $data)) {
			$data['home'] = 0;
		}

		// see if its a override and set params to only different data
		if (array_key_exists('master', $data['params']) && $data['params']['master'] != 'true') {
			$master_params  = $this->getItem($data['params']['master'])->params;
			$data['params'] = $this->array_diff($data['params'], $master_params);
		}

        if(@ini_get('magic_quotes_gpc')=='1'){
            $data['params'] = self::_stripSlashesRecursive($data['params']);
        }

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Prepare the row for saving
		$this->prepareTable($table);

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onExtensionBeforeSave event.
		$result = $dispatcher->trigger('onExtensionBeforeSave', array('com_templates.style', &$table, $isNew));
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		$user = JFactory::getUser();
		if ($user->authorise('core.edit', 'com_menus') && $table->client_id == 0) {
			$n    = 0;
			$db   = JFactory::getDbo();
			$user = JFactory::getUser();

			if (empty($data['assigned']) || $data['assigned'] !== false) {
				if (!empty($data['assigned'])) {
					JArrayHelper::toInteger($data['assigned']);

					// Update the mapping for menu items that this style IS assigned to.
					$query = $db->getQuery(true);
					$query->update('#__menu');
					$query->set('template_style_id=' . (int)$table->id);
					$query->where('id IN (' . implode(',', $data['assigned']) . ')');
					$query->where('template_style_id!=' . (int)$table->id);
					$query->where('checked_out in (0,' . (int)$user->id . ')');
					$db->setQuery($query);
					$db->query();
					$n += $db->getAffectedRows();
				}

				// Remove style mappings for menu items this style is NOT assigned to.
				// If unassigned then all existing maps will be removed.
				$query = $db->getQuery(true);
				$query->update('#__menu');
				$query->set('template_style_id=0');
				if (!empty($data['assigned'])) {
					$query->where('id NOT IN (' . implode(',', $data['assigned']) . ')');
				}

				$query->where('template_style_id=' . (int)$table->id);
				$query->where('checked_out in (0,' . (int)$user->id . ')');
				$db->setQuery($query);
				$db->query();

				$n += $db->getAffectedRows();
				if ($n > 0) {
					$app = JFactory::getApplication();
					$app->enQueueMessage(JText::plural('COM_TEMPLATES_MENU_CHANGED', $n));
				}
			}
		}

		// Clean the cache.
		$cache = JFactory::getCache();
		$cache->clean('com_templates');
		$gcache = GantryCache::getCache(GantryCache::GROUP_NAME);
		$gcache->getCacheLib()->getDriver()->getCache()->cache->_options['cachebase'] = JPATH_ROOT.'/cache';
		$gcache->clearGroupCache();
		$gacache = GantryCache::getCache(GantryCache::ADMIN_GROUP_NAME);
		$gacache->clearGroupCache();
		gantry_admin_setup();

		// Trigger the onExtensionAfterSave event.
		$dispatcher->trigger('onExtensionAfterSave', array('com_templates.style', &$table, $isNew));

		$this->setState('template.id', $table->id);

		return true;
	}

	/**
	 * Get the necessary data to load an item help screen.
	 *
	 * @return    object    An object with key, url, and local properties for loading the item help screen.
	 * @since    1.6
	 */
	public function getHelp()
	{
		return (object)array('key' => $this->helpKey, 'url' => $this->helpURL);
	}

	/**
	 * Method to get a form object.
	 *
	 * @param    string         $name          The name of the form.
	 * @param    string         $source        The form source. Can be XML string if file flag is set to false.
	 * @param    array          $options       Optional array of options for the form creation.
	 * @param    boolean        $clear         Optional argument to force load a new form.
	 * @param    string         $xpath         An optional xpath to search for the fields.
	 *
	 * @return    mixed        JForm object on success, False on error.
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear) {
			return $this->_forms[$hash];
		}

		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try {
			$form = JForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data']) {
				// Get the data for the form.
				$data = $this->loadFormData();
			} else {
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);

		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}


	protected function array_join()
	{
		// Get array arguments
		$arrays = func_get_args();

		// Define the original array
		$original = array_shift($arrays);

		// Loop through arrays
		foreach ($arrays as $array) {
			// Loop through array key/value pairs
			foreach ($array as $key => $value) {
				// Value is an array
				if (is_array($value)) {
					// Traverse the array; replace or add result to original array
					@$original[$key] = $this->array_join($original[$key], $array[$key]);
				} // Value is not an array
				else {
					// Replace or add current value to original array
					@$original[$key] = $value;
				}
			}
		}

		// Return the joined array
		return $original;
	}

	protected function array_diff($aArray1, $aArray2)
	{
		$aReturn = array();

		foreach ($aArray1 as $mKey => $mValue) {
			if (array_key_exists($mKey, $aArray2)) {
				if (is_array($mValue)) {
					$aRecursiveDiff = $this->array_diff($mValue, $aArray2[$mKey]);
					if (count($aRecursiveDiff)) {
						$aReturn[$mKey] = $aRecursiveDiff;
					}
				} else {
					if ($mValue != $aArray2[$mKey]) {
						$aReturn[$mKey] = $mValue;
					}
				}
			} else {
				$aReturn[$mKey] = $mValue;
			}
		}

		return $aReturn;
	}

	/**
	 * Method to duplicate styles.
	 *
	 * @param    array    An array of primary key IDs.
	 *
	 * @return    boolean    True if successful.
	 * @throws    Exception
	 */
	public function duplicate(&$pks)
	{
		// Initialise variables.
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		// Access checks.
		if (!$user->authorise('core.create', 'com_templates')) {
			throw new Exception(JText::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		foreach ($pks as $pk) {
			if ($table->load($pk, true)) {
				// Reset the id to create a new record.
				$table->id = 0;

				// Reset the home (don't want dupes of that field).
				$table->home = 0;

				// Alter the title.
				$m = null;
				if (preg_match('#\((\d+)\)$#', $table->title, $m)) {
					$table->title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $table->title);
				} else {
					$table->title .= ' (2)';
				}

				if ($this->isGantryTemplate($table)) {
					$template_params = new JRegistry();
					$template_params->loadString($table->params);
					if ($template_params->get('master') == 'true') {
						$base_params = $template_params->toArray();
						$template_params->set('master', $pk);
						$copy_params     = $template_params->toArray();
						$copy_params     = $this->array_diff($copy_params, $base_params);
						$template_params = new JRegistry();
						$template_params->loadArray($copy_params);
					}
					$table->params = $template_params->toString();
				}

				if (!$table->check() || !$table->store()) {
					throw new Exception($table->getError());
				}
			} else {
				throw new Exception($table->getError());
			}
		}

		$cache = JFactory::getCache();
		$cache->clean('com_templates');
		$cache->clean('_system');

		return true;
	}

	/**
	 * Method to delete rows.
	 *
	 * @param    array    An array of item ids.
	 *
	 * @return    boolean    Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		// Initialise variables.
		$pks      = (array)$pks;
		$user     = JFactory::getUser();
		$table    = $this->getTable();
		$language = JFactory::getLanguage();
		$language->load('com_gantry');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				// Access checks.
				if (!$user->authorise('core.delete', 'com_templates')) {
					throw new Exception(JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}

				if ($this->isGantryTemplate($table)) {
					$template_params = new JRegistry();
					$template_params->loadString($table->params);
					if ($template_params->get('master') == 'true') {
						$this->setError(JText::_('Cannot delete a Gantry Template Master Style'));
						return false;
					}
				}
				if (!$table->delete($pk)) {
					$this->setError($table->getError());
					return false;
				}
			} else {
				$this->setError($table->getError());
				return false;
			}
		}

		$cache = JFactory::getCache();
		$cache->clean('com_templates');
		$cache->clean('_system');

		return true;
	}

	/**
	 * Check if template is based on gantry
	 *
	 * @param string $id
	 *
	 * @return boolean
	 */
	private function isGantryTemplate($table)
	{
		$template = $table->template;
		return file_exists(JPATH_SITE . '/' . 'templates' . '/' . $template . '/' . 'lib' . '/' . 'gantry' . '/' . 'gantry.php');
	}

    /**
   	 *
   	 * @param $value
   	 *
   	 * @return array|string
   	 */
   	protected static function _stripSlashesRecursive($value)
   	{
           $value = is_array($value) ? array_map(array( 'GantryModelTemplate', '_stripSlashesRecursive'), $value) : stripslashes($value);
           return $value;
   	}

}
