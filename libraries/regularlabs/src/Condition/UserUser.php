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

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

/**
 * Class UserUser
 * @package RegularLabs\Library\Condition
 */
class UserUser extends User
{
	public function pass()
	{
		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		return $this->passSimple($user->get('id'));
	}
}
