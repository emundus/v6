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

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Field;
use RegularLabs\Library\StringHelper as RL_String;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

/**
 * @deprecated  2018-10-30  Use ConditionSelection instead
 */
class JFormFieldRL_AssignmentSelection extends Field
{
	public $type = 'AssignmentSelection';

	protected function getInput()
	{
		require_once __DIR__ . '/toggler.php';
		$toggler = new RLFieldToggler;

		$this->value     = (int) $this->value;
		$label           = $this->get('label');
		$param_name      = $this->get('name');
		$use_main_toggle = $this->get('use_main_toggle', 1);
		$showclose       = $this->get('showclose', 0);

		$html = [];

		if ( ! $label)
		{
			if ($use_main_toggle)
			{
				$html[] = $toggler->getInput(['div' => 1]);
			}

			$html[] = $toggler->getInput(['div' => 1]);

			return '</div>' . implode('', $html);
		}

		$label = RL_String::html_entity_decoder(JText::_($label));

		$html[] = '</div>';
		if ($use_main_toggle)
		{
			$html[] = $toggler->getInput(['div' => 1, 'param' => 'show_assignments|' . $param_name, 'value' => '1|1,2']);
		}

		$class = 'well well-small rl_well';
		if ($this->value === 1)
		{
			$class .= ' alert-success';
		}
		else if ($this->value === 2)
		{
			$class .= ' alert-error';
		}
		$html[] = '<div class="' . $class . '">';

		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		if ($showclose && $user->authorise('core.admin'))
		{
			$html[] = '<button type="button" class="close rl_remove_assignment" aria-label="Close">&times;</button>';
		}

		$html[] = '<div class="control-group">';

		$html[] = '<div class="control-label">';
		$html[] = '<label><h4 class="rl_assignmentselection-header">' . $label . '</h4></label>';
		$html[] = '</div>';

		$html[] = '<div class="controls">';
		$html[] = '<fieldset id="' . $this->id . '"  class="radio btn-group">';

		$onclick = ' onclick="RegularLabsForm.setToggleTitleClass(this, 0)"';
		$html[]  = '<input type="radio" id="' . $this->id . '0" name="' . $this->name . '" value="0"' . (( ! $this->value) ? ' checked="checked"' : '') . $onclick . '>';
		$html[]  = '<label class="rl_btn-ignore" for="' . $this->id . '0">' . JText::_('RL_IGNORE') . '</label>';

		$onclick = ' onclick="RegularLabsForm.setToggleTitleClass(this, 1)"';
		$html[]  = '<input type="radio" id="' . $this->id . '1" name="' . $this->name . '" value="1"' . (($this->value === 1) ? ' checked="checked"' : '') . $onclick . '>';
		$html[]  = '<label class="rl_btn-include" for="' . $this->id . '1">' . JText::_('RL_INCLUDE') . '</label>';

		$onclick = ' onclick="RegularLabsForm.setToggleTitleClass(this, 2)"';
		$onclick .= ' onload="RegularLabsForm.setToggleTitleClass(this, ' . $this->value . ', 7)"';
		$html[]  = '<input type="radio" id="' . $this->id . '2" name="' . $this->name . '" value="2"' . (($this->value === 2) ? ' checked="checked"' : '') . $onclick . '>';
		$html[]  = '<label class="rl_btn-exclude" for="' . $this->id . '2">' . JText::_('RL_EXCLUDE') . '</label>';

		$html[] = '</fieldset>';
		$html[] = '</div>';

		$html[] = '</div>';
		$html[] = '<div class="clearfix"> </div>';

		$html[] = $toggler->getInput(['div' => 1, 'param' => $param_name, 'value' => '1,2']);
		$html[] = '<div><div>';

		return '</div>' . implode('', $html);
	}

	protected function getLabel()
	{
		return '';
	}
}
