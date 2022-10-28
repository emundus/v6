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

class JFormFieldRL_EasyBlog extends FieldGroup
{
	public $type = 'EasyBlog';

	public function getCategories()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(*)')
			->from('#__easyblog_category AS c')
			->where('c.published > -1');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$query->clear('select')
			->select('c.id, c.parent_id, c.title, c.published')
			->order('c.ordering, c.title');
		$this->db->setQuery($query);
		$items = $this->db->loadObjectList();

		return $this->getOptionsTreeByList($items);
	}

	public function getItems()
	{
		$query = $this->db->getQuery(true)
			->select('i.id, i.title as name, c.title as cat, i.published')
			->from('#__easyblog_post AS i')
			->join('LEFT', '#__easyblog_category AS c ON c.id = i.category_id')
			->where('i.published > -1')
			->order('i.title, c.title, i.id');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list, ['cat', 'id']);
	}

	public function getTags()
	{
		$query = $this->db->getQuery(true)
			->select('t.alias as id, t.title as name')
			->from('#__easyblog_tag AS t')
			->where('t.published > -1')
			->where('t.title != ' . $this->db->quote(''))
			->group('t.title')
			->order('t.title');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list);
	}

	protected function getInput()
	{
		$error = $this->missingFilesOrTables(['categories' => 'category', 'items' => 'post', 'tags' => 'tag']);

		return $error ?: $this->getSelectList();
	}
}
