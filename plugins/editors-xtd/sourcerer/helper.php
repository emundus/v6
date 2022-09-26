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

defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject as JObject;
use RegularLabs\Library\EditorButtonHelper as RL_EditorButtonHelper;

/**
 * Plugin that places the Button
 */
class PlgButtonSourcererHelper extends RL_EditorButtonHelper
{
	/**
	 * Display the button
	 *
	 * @param string $editor_name
	 *
	 * @return JObject|null A button object
	 */
	public function render($editor_name)
	{
		return $this->renderPopupButton($editor_name);
	}
}
