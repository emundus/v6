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

use RegularLabs\Library\FieldGroup;

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_AkeebaSubs extends FieldGroup
{
	public $default_group = 'Levels';
	public $type          = 'AkeebaSubs';

	public function getLevels()
	{
		$query = $this->db->getQuery(true)
			->select('l.akeebasubs_level_id as id, l.title AS name, l.enabled as published')
			->from('#__akeebasubs_levels AS l')
			->where('l.enabled > -1')
			->order('l.title, l.akeebasubs_level_id');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list, ['id']);
	}

	protected function getInput()
	{
		$error = $this->missingFilesOrTables(['levels']);

		return $error ?: $this->getSelectList();
	}
}
