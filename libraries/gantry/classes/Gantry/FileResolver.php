<?php
/**
 * @version   $Id: FileResolver.php 4532 2012-10-26 16:42:16Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die;

/**
 *
 */
class Gantry_FileResolver
{

	const DEFAULT_PRIORITY = 10;
	/**
	 * @var string
	 */
	protected $paths;

	/**
	 * @param array $paths

	 */
	public function __construct(array &$paths = array())
	{
		$this->paths = $paths;
	}

	public function addPath($path, $priority = self::DEFAULT_PRIORITY)
	{
		$this->paths[$priority][] = $path;
	}

	/**
	 * Get the path of the highest priority package file with the context in the context paths;
	 *
	 * @param $file
	 *
	 * @return bool|string
	 */
	public function get($file)
	{
		return self::findFile($file, $this->paths);
	}

	/**
	 * @param $file
	 *
	 * @return array
	 */
	public function getAll($file)
	{
		return self::findAllFiles($file, $this->paths);
	}

	/**
	 * @param $file
	 *
	 * @return array
	 */
	public function getAllSubFiles($file)
	{
		return self::findSubFiles($file, $this->paths);
	}


	/**
	 * @param $file
	 * @param $context
	 * @param $basepaths
	 *
	 * @return bool|string
	 */
	protected static function findFile($file, $basepaths)
	{
		krsort($basepaths);
		foreach ($basepaths as $priority => $paths) {
			foreach ($paths as $path) {

				$find_path = $path;
				$find_path .= DS . $file;

				if (file_exists($find_path) && is_file($find_path)) {
					return $find_path;
				}
			}
		}
		return false;
	}

	/**
	 * @param $file
	 * @param $basepaths
	 *
	 * @return array
	 */
	protected static function findAllFiles($file, $basepaths)
	{
		krsort($basepaths);
		$ret = array();
		foreach ($basepaths as $priority => $paths) {
			foreach ($paths as $path) {

				$find_path = $path;
				$find_path .= DS . $file;

				if (file_exists($find_path) && is_file($find_path)) {
					$ret[] = $find_path;
				}
			}
		}
		return $ret;
	}

	/**
	 * @static
	 *
	 * @param $file
	 * @param $basepaths
	 *
	 * @return array
	 */
	protected static function findSubFiles($file, $basepaths)
	{
		krsort($basepaths);
		$ret = array();
		foreach ($basepaths as $priority => $paths) {
			foreach ($paths as $path) {
				$find_path = $path;
				if (defined("RecursiveDirectoryIterator::FOLLOW_SYMLINKS")) {
					$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($find_path, RecursiveDirectoryIterator::FOLLOW_SYMLINKS), RecursiveIteratorIterator::LEAVES_ONLY);
				} else {
					$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($find_path), RecursiveIteratorIterator::LEAVES_ONLY);
				}
				foreach ($iterator as $subpath) {
					/** @var $subpath FilesystemIterator  */
					if ($subpath->getFilename() == $file) {
						$ret[] = $subpath->__toString();
					}
				}
			}
		}
		return $ret;
	}
}
