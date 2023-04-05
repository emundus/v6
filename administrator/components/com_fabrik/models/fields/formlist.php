<?php
/**
 * Renders a repeating drop down list of forms
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

//JFormHelper::loadFieldClass('list');

/**
 * Renders a repeating drop down list of forms
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldFormList extends ListField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'Formlist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array	The field option objects.
	 */

	protected function getOptions()
	{
		$app = Factory::getApplication();
		if ($app->input->get('option') != 'com_fabrik') {
			/* Load the fabrik language */
			$lang = Factory::getLanguage();
			$lang->load('com_fabrik', JPATH_SITE . '/components/com_fabrik');
		}

		if ($this->element['package'])
		{
			$package = $app->setUserState('com_fabrik.package', $this->element['package']);
		}

		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id AS value, label AS ' . $db->quote('text') . ', published');
		$query->from('#__fabrik_forms');

		if (!$this->element['showtrashed'])
		{
			$query->where('published <> -2');
		}

		$query->order('published DESC, label ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (!empty($this->element) && !empty($this->element->option)) {
			$option = $this->element->option;
			if (is_array($option)) array_shift($option);
			$options[] = HTMLHelper::_('select.option', '', Text::_($option));
		} else {
			$options[] = HTMLHelper::_('select.option', '', Text::_("COM_FABRIK_PLEASE_SELECT"));
		}
		
		foreach ($rows as $row)
		{
			switch ($row->published)
			{
				case '0':
					$row->text .= ' [' . Text::_('JUNPUBLISHED') . ']';
					break;
				case '-2':
					$row->text .= ' [' . Text::_('JTRASHED') . ']';
					break;
			}
			$options[] = HTMLHelper::_('select.option', htmlspecialchars($row->value), htmlspecialchars($row->text));
		}

		return $options;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */

	protected function getInput()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$option = $input->get('option');

		if (!in_array($option, array('com_modules', 'com_menus', 'com_advancedmodules')) && empty($this->value))
		{
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$query->select('form_id')->from('#__fabrik_formgroup')->where('group_id = ' . (int) $this->form->getValue('id'));
			$db->setQuery($query);
			$this->value = $db->loadResult();
			$this->form->setValue('form', null, $this->value);
		}

		if ((int) $this->form->getValue('id') == 0 || !$this->element['readonlyonedit'])
		{
			return parent::getInput();
		}
		else
		{
			$options = (array) $this->getOptions();
			$v       = '';

			foreach ($options as $opt)
			{
				if ($opt->value == $this->value)
				{
					$v = $opt->text;
				}
			}
		}

//		return '<input type="hidden" value="' . $this->value . '" name="' . $this->name . '" />' . '<input type="text" value="' . $v
//		. '" name="form_justalabel" class="readonly" readonly="true" />';
		return '<input type="hidden" value="' . $this->value . '" name="' . $this->name . '" />' . '<input type="text" value="' . $v
		. '" name="form_justalabel" class="form-control" readonly />';
	}
}