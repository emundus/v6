<?php
/**
 * @version   $Id: gantrystylelink.class.php 2996 2012-09-01 15:14:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */
defined('GANTRY_VERSION') or die();

/**
 * @package    gantry
 * @subpackage core
 */
class GantryStyleLink
{
	/**
	 * type
	 * @access private
	 * @var string (url or local)
	 */
	protected $type;


	/**
	 * The local filesystem path for the style link
	 * @access private
	 * @var string
	 */
	protected  $path;

	/**
	 * The url for the style link, local or full
	 * @access private
	 * @var string
	 */
	protected $url;


	/**
	 * @param $type
	 * @param $path
	 * @param $url
	 */
	function __construct($type, $path, $url)
	{
		$this->type = $type;
		$this->path = $path;
		$this->url  = $url;
	}


	/**
	 * Gets the type for gantry
	 * @access public
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets the type for gantry
	 * @access public
	 *
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}


	/**
	 * Gets the path for gantry
	 * @access public
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Sets the path for gantry
	 * @access public
	 *
	 * @param string $path
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}


	/**
	 * Gets the url for gantry
	 * @access public
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Sets the url for gantry
	 * @access public
	 *
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}


}