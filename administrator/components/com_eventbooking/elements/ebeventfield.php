<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class JFormFieldEbeventfield extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var string
	 */
	protected $type = 'ebeventfield';

	protected function getOptions()
	{
		JLoader::register('EventbookingHelper', JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');

		$config = EventbookingHelper::getConfig();

		$options = [];

		if ($config->event_custom_field)
		{
			// Get List Of defined custom fields
			$xml    = simplexml_load_file(JPATH_ROOT . '/components/com_eventbooking/fields.xml');
			$fields = $xml->fields->fieldset->children();

			foreach ($fields as $field)
			{
				$name      = $field->attributes()->name;
				$label     = Text::_($field->attributes()->label);
				$options[] = HTMLHelper::_('select.option', $name, $label);
			}
		}

		return $options;
	}
}
