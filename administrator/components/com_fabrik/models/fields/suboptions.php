<?php
/**
 * Used in radios/checkbox elements for adding <options> to the element
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\Utilities\ArrayHelper;

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Used in radios/checkbox elements for adding <options> to the element
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldSuboptions extends FormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'Suboptions';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */

	protected function getInput()
	{
		Text::script('COM_FABRIK_SUBOPTS_VALUES_ERROR');

		$default = new stdClass;
		$default->sub_values = array();
		$default->sub_labels = array();
		$default->sub_initial_selection = array();
		$opts = $this->value == '' ? $default : ArrayHelper::toObject($this->value);

		$delButton  = '<div class="btn-group">';
		$delButton .= '<a class="btn-sm btn-success" href="#" data-button="addSuboption"><i class="icon-plus"></i> </a>';
		$delButton .= '<a class="btn-sm btn-danger" href="#" data-button="deleteSuboption"><i class="icon-minus"></i> </a>';
		$delButton .= '</div>';

		if (is_array($opts))
		{
			$opts['delButton'] = $delButton;
		}
		else
		{
			$opts->delButton = $delButton;
		}

		$opts->id = $this->id;
		$opts->j3 = true;
		$opts->defaultMax = (int) $this->getAttribute('default_max', 0);
		$opts = json_encode($opts);
		$script[] = "window.addEvent('domready', function () {";
		$script[] = "\tnew Suboptions('$this->name', $opts);";
		$script[] = "});";
		FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/suboptions.js', implode("\n", $script));
		$html = array();

		$html[] = '<table class="table table-striped" style="width: 100%" id="' . $this->id . '">';
		$html[] = '<thead>';
		$html[] = '<tr style="text-align:left">';
		$html[] = '<th style="width: 5%"></th>';
		$html[] = '<th style="width: 30%">' . Text::_('COM_FABRIK_VALUE') . '</th>';
		$html[] = '<th style="width: 30%">' . Text::_('COM_FABRIK_LABEL') . '</th>';
		$html[] = '<th style="width: 10%">' . Text::_('COM_FABRIK_DEFAULT') . '</th>';

		$html[] = '<th style="width: 20%"><a style="color:white" class="btn-sm btn-success" data-button="addSuboption"><i class="icon-plus"></i> </a></th>';

		$html[] = '</tr>';
		$html[] = '</thead>';
		$html[] = '<tbody></tbody>';
		$html[] = '</table>';

		FabrikHelperHTML::framework();
		FabrikHelperHTML::iniRequireJS();

		return implode("\n", $html);
	}
}
