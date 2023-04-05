<?php
/**
 * Renders a list of Bootstrap field class sizes
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\Field\ListField;

FormHelper::loadFieldClass('list');

/**
 * Renders a list of Bootstrap field class sizes
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.5
 */

class JFormFieldBootstrapfieldclass extends ListField
{
	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */

	protected function getOptions()
	{
		$sizes = array();
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-1');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-2');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-3');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-4');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-5');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-6');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-7');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-8');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-9');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-10');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-11');
		$sizes[] = HTMLHelper::_('select.option', 'col-sm-12');
		$sizes[] = HTMLHelper::_('select.option', 'input-mini');
		$sizes[] = HTMLHelper::_('select.option', 'input-small');
		$sizes[] = HTMLHelper::_('select.option', 'input-medium');
		$sizes[] = HTMLHelper::_('select.option', 'input-large');
		$sizes[] = HTMLHelper::_('select.option', 'input-xlarge');
		$sizes[] = HTMLHelper::_('select.option', 'input-xxlarge');

		return $sizes;
	}
}
