<?php
/**
 * @version   $Id: gantrypositions.class.php 2468 2012-08-17 06:16:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

gantry_import('core.gantryflatfile');

if (!defined('POSITIONS_MD5')) {
	/**
	 *
	 */
	define('POSITIONS_MD5', 0);
	/**
	 *
	 */
	define('POSITIONS_LAYPUT', 1);
}

/**
 *
 */
class GantryPositions
{

	/**
	 * @var array
	 */
	private static $instances = array();

	/**
	 * @static
	 *
	 * @param $grid
	 *
	 * @return mixed
	 */
	public static function getInstance($grid)
	{
		if (!array_key_exists($grid, self::$instances)) {
			$instances[$grid] = new GantryPositions($grid);
		}
		return $instances[$grid];
	}


	/**
	 * @var null
	 */
	private $_db = null;

	/**
	 * @var null
	 */
	private $_db_file = null;

	/**
	 * @var array
	 */
	private $_cache = array();

	/**
	 * @var
	 */
	private $_gridSystem;

	/**
	 * @param $grid
	 */
	protected function __construct($grid)
	{
		$this->_gridSystem = $grid;
	}

	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array(
			'_cache', '_gridSystem'
		);
	}

	/**
	 *
	 */
	private function _init()
	{
		/** @var $gantry Gantry */
		global $gantry;

		if (null == $this->_db) {
			$this->_db          = new Flatfile();
			$this->_db->datadir = $gantry->gantryPath . '/' . 'admin' . '/' . 'cache' . '/';
		}

		$this->_db_file = $this->_gridSystem . '.cache.txt';
	}

	/**
	 * @param $md5
	 *
	 * @return null
	 */
	public function get($md5)
	{
		$this->_init();
		$ret = null;

		if (array_key_exists($md5, $this->_cache)) {
			return $this->_cache[$md5];
		}
		$retarray = $this->_db->selectUnique($this->_db_file, POSITIONS_MD5, $md5);
		if (null != $retarray && is_array($retarray) && count($retarray) > 0) {
			$ret = $retarray[POSITIONS_LAYPUT];
		}
		$this->_cache[$md5] = $ret;
		return $ret;
	}

	/**
	 * @param $md5
	 * @param $permutation
	 */
	public function set($md5, $permutation)
	{
		$this->_init();
		$this->_db->insert($this->_db_file, array($md5, $permutation));
	}
}