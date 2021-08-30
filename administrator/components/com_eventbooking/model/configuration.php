<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\File;

class EventbookingModelConfiguration extends RADModel
{
	/**
	 * Store the configuration data
	 *
	 * @param   array  $data
	 *
	 * @return bool
	 */
	public function store($data)
	{
		$db = $this->getDbo();
		$db->truncateTable('#__eb_configs');
		$row = $this->getTable('Config');

		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$value = implode(',', $value);
			}
			$row->id           = 0;
			$row->config_key   = $key;
			$row->config_value = $value;
			$row->store();
		}

		if (isset($data['custom_css']))
		{
			File::write(JPATH_ROOT . '/media/com_eventbooking/assets/css/custom.css', trim($data['custom_css']));
		}

		if (isset($data['event_custom_fields']))
		{
			File::write(JPATH_ROOT . '/components/com_eventbooking/fields.xml', trim($data['event_custom_fields']));
		}

		return true;
	}
}
