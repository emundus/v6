<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

if ( ! class_exists('RegularLabs\Library\SystemPlugin'))
{
	/**
	 * Class Plugin
	 * @package    RegularLabs\Library
	 * @deprecated Use SystemPlugin
	 */
	class Plugin
	{
	}
}
else
{
	/**
	 * Class Plugin
	 * @package    RegularLabs\Library
	 * @deprecated Use SystemPlugin
	 */
	class Plugin extends SystemPlugin
	{
	}
}

