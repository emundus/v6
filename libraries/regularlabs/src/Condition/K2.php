<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

use RegularLabs\Library\Condition;
use RegularLabs\Library\ConditionContent;

defined('_JEXEC') or die;

// If controller.php exists, assume this is K2 v3
defined('RL_K2_VERSION') or define('RL_K2_VERSION', file_exists(JPATH_ADMINISTRATOR . '/components/com_k2/controller.php') ? 3 : 2);

/**
 * Class K2
 * @package RegularLabs\Library\Condition
 */
abstract class K2 extends Condition
{
	use ConditionContent;

	public function getItem($fields = [])
	{
		$query = $this->db->getQuery(true)
			->select($fields)
			->from('#__k2_items')
			->where('id = ' . (int) $this->request->id);
		$this->db->setQuery($query);

		return $this->db->loadObject();
	}
}
