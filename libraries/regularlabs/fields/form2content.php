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

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

class JFormFieldRL_Form2Content extends \RegularLabs\Library\FieldGroup
{
	public $default_group = 'Projects';
	public $type          = 'Form2Content';

	public function getProjects()
	{
		$query = $this->db->getQuery(true)
			->select('t.id, t.title as name')
			->from('#__f2c_project AS t')
			->where('t.published = 1')
			->order('t.title, t.id');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list);
	}

	protected function getInput()
	{
		$error = $this->missingFilesOrTables(['projects' => 'project'], '', 'f2c');

		return $error ?: $this->getSelectList();
	}
}
