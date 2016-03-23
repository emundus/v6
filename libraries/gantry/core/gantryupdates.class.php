<?php
/**
 * @version   $Id: gantryupdates.class.php 4060 2012-10-02 18:03:24Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();


/**
 *
 */
class GantryUpdates
{
	/**
	 * @var
	 */
	protected static $instance;

	/**
	 * @static
	 * @return GantryUpdates
	 */
	public static function &getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new GantryUpdates();
		}
		return self::$instance;
	}

	/**
	 * @var null
	 */
	private $extensionInfo = null;

	/**
	 * @var null
	 */
	private $updateInfo = null;

	/**
	 *
	 */
	public function __construct()
	{
		$this->populateExtensionInfo();
	}

	/**
	 * @return string
	 */
	public function getCurrentVersion()
	{
		if ($this->extensionInfo && array_key_exists('version', $this->extensionInfo->manifest_cache)) {
			return $this->extensionInfo->manifest_cache['version'];
		} else {
			//TODO: move to translation
			return 'unknown';
		}
	}

	/**
	 * @return string
	 */
	public function getLatestVersion()
	{
		$this->populateUpdateInfo();
		if ($this->updateInfo) {
			return $this->updateInfo->version;
		} else {
			return $this->getCurrentVersion();
		}
	}

	/**
	 * @return int|bool
	 */
	public function getLastUpdated()
	{
		$this->populateUpdateInfo();
		if ($this->extensionInfo && array_key_exists('last_update', $this->extensionInfo->custom_data)) {
			return $this->extensionInfo->custom_data['last_update'];
		} else
			return 0;
	}

	/**
	 * @return void
	 */
	protected function populateExtensionInfo()
	{
		$table = JTable::getInstance('extension');
		$id    = $table->find(array('type' => 'library', 'element' => 'lib_gantry'));
		if (empty($id)) {
			return;
		}
		$table->load($id);

		// convert manifest_cache to array
		$registry = new JRegistry();
		$registry->loadString($table->manifest_cache);
		$table->manifest_cache = $registry->toArray();

		// convert custom_data to array
		$registry = new JRegistry();
		$registry->loadString($table->custom_data);
		$table->custom_data = $registry->toArray();

		$this->extensionInfo = $table;
	}

	/**
	 *
	 */
	protected function populateUpdateInfo()
	{
		if (empty($this->updateInfo)) {
			$table    = JTable::getInstance('update');
			$updateid = @$table->find(array('extension_id' => $this->extensionInfo->extension_id));
			if (empty($updateid)) {
				return;
			}
			$table->load($updateid);
			$this->updateInfo = $table;
		}

	}

	/**
	 * @param  $last_checked
	 *
	 * @return void
	 */
	public function setLastChecked($last_checked)
	{
		if (!empty($this->extensionInfo)) {
			$this->extensionInfo->custom_data['last_update'] = $last_checked;

			$registry = new JRegistry();
			$registry->loadArray($this->extensionInfo->custom_data);
			$this->extensionInfo->custom_data = $registry->toString();

			$registry = new JRegistry();
			$registry->loadArray($this->extensionInfo->manifest_cache);
			$this->extensionInfo->manifest_cache = $registry->toString();

			$this->extensionInfo->store();
			$this->populateExtensionInfo();
		}
	}

	/**
	 * @return int
	 */
	public function getGantryExtensionId()
	{
		return $this->extensionInfo->extension_id;
	}


}
