<?php
/**
 * @package       eMundus
 * @version       6.6.5
 * @author        eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license       GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */


// Check to ensure this file is within the rest of the framework
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
jimport('joomla.application.component.controller');

/**
 * Renders a list of elements
 *
 * @package       Joomla.Framework
 * @subpackage    Parameter
 * @since         2.5
 */
class JFormFieldStatus extends JFormField
{
	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	protected $type = 'Status';

	public function getLabel()
	{
		// code that returns HTML that will be shown as the label
		return '<span style="text-decoration: underline;">' . parent::getLabel() . '</span>';
	}

	public function getInput()
	{
		// code that returns HTML that will be shown as the form field
		$class   = '';
		$options = array();
		$db      = JFactory::getDBO();

		$query = 'SELECT ess.step, ess.value 
					FROM #__emundus_setup_status ess
					WHERE published=1
					ORDER BY ess.ordering';
		$db->setQuery($query);
		$organisations = $db->loadObjectList();

		$options[] = JHTML::_('select.optgroup', JText::_(""));
		foreach ($organisations as $organisation) {
			$options[] = JHTML::_('select.option', $organisation->step, JText::_($organisation->value), 'value', 'text');
		}

		return JHTML::_('select.genericlist', $options, $this->name, 'value', 'text');
	}

}
