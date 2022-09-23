<?php
/**
 * @package         Sourcerer
 * @version         9.2.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Sourcerer;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

class Security
{
	protected static $security = null;

	public static function get()
	{
		if ( ! is_null(self::$security))
		{
			return self::$security;
		}

		self::$security = (object) [
			'pass'     => true,
			'pass_css' => true,
			'pass_js'  => true,
			'pass_php' => true,
		];

		return self::$security;
	}

	public static function set($article = null)
	{
		if ( ! isset($article->created_by))
		{
			return;
		}

		$params = Params::get();

		$security_level = (array) $params->articles_security_level;
		$security_css   = $params->articles_security_level_default_css
			? (array) $params->articles_security_level
			: (array) $params->articles_security_level_css;
		$security_js    = $params->articles_security_level_default_js
			? (array) $params->articles_security_level
			: (array) $params->articles_security_level_js;
		$security_php   = $params->articles_security_level_default_php
			? (array) $params->articles_security_level
			: (array) $params->articles_security_level_php;

		$user  = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
		$table = $user->getTable();

		if ($table->load($article->created_by))
		{
			$user = JFactory::getUser($article->created_by);
		}

		$groups = $user->getAuthorisedGroups();
		array_unshift($groups, -1);

		// Set if security is passed
		// passed = creator is equal or higher than security group level
		$security           = (object) [];
		$pass               = array_intersect($security_level, $groups);
		$security->pass     = ( ! empty($pass));
		$pass               = array_intersect($security_css, $groups);
		$security->pass_css = ( ! empty($pass));
		$pass               = array_intersect($security_js, $groups);
		$security->pass_js  = ( ! empty($pass));
		$pass               = array_intersect($security_php, $groups);
		$security->pass_php = ( ! empty($pass));

		self::$security = $security;
	}
}
