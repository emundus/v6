<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class EventbookingViewFieldRaw extends RADViewHtml
{
	public function display()
	{
		$this->setLayout('options');
		$db      = Factory::getDbo();
		$query   = $db->getQuery(true);
		$fieldId = Factory::getApplication()->input->getInt('field_id');
		$query->select('`values`')
			->from('#__eb_fields')
			->where('id=' . $fieldId);
		$db->setQuery($query);
		$options       = explode("\r\n", $db->loadResult());
		$this->options = $options;

		parent::display();
	}
}
