<?php
/**
 * @version   $Id: groupedlist.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformfield');
gantry_import('core.config.gantryhtmlselect');


class GantryFormFieldGroupedList extends GantryFormField
{


	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'groupedlist';
	protected $basetype = 'select';

	/**
	 * Method to get the field option groups.
	 *
	 * @return    array    The field option objects as a nested array in groups.
	 * @since    1.6
	 */
	protected function getGroups()
	{
		// Initialize variables.
		$groups = array();
		$label  = 0;

		foreach ($this->element->children() as $element) {

			switch ($element->getName()) {
				// The element is an <option />
				case 'option':

					// Initialize the group if necessary.
					if (!isset($groups[$label])) {
						$groups[$label] = array();
					}

					// Create a new option object based on the <option /> element.
					$tmp = GantryHTMLSelect::option(($element['value']) ? (string)$element['value'] : trim((string)$element), JText::_(trim((string)$element)), 'value', 'text', ((string)$element['disabled'] == 'true'));

					// Set some option attributes.
					$tmp->class = (string)$element['class'];

					// Set some JavaScript option attributes.
					$tmp->onclick = (string)$element['onclick'];

					// Add the option.
					$groups[$label][] = $tmp;
					break;

				// The element is a <group />
				case 'group':

					// Get the group label.
					if ($groupLabel = (string)$element['label']) {
						$label = $groupLabel;
					}

					// Initialize the group if necessary.
					if (!isset($groups[$label])) {
						$groups[$label] = array();
					}

					// Iterate through the children and build an array of options.
					foreach ($element->children() as $option) {
						// Only add <option /> elements.
						if ($option->getName() != 'option') {
							continue;
						}

						// Create a new option object based on the <option /> element.
						$tmp = GantryHTMLSelect::option(($element['value']) ? (string)$element['value'] : JText::_(trim((string)$element)), JText::_(trim((string)$element)), 'value', 'text', ((string)$element['disabled'] == 'true'));

						// Set some option attributes.
						$tmp->class = (string)$element['class'];

						// Set some JavaScript option attributes.
						$tmp->onclick = (string)$element['onclick'];

						// Add the option.
						$groups[$label][] = $tmp;
					}

					if ($groupLabel) {
						$label = count($groups);
					}
					break;

				// Unknown element type.
				default:
					//TODO Handle Error
					//JError::raiseError(500, JText::sprintf('JLIB_FORM_ERROR_FIELDS_GROUPEDLIST_ELEMENT_NAME', $element->getName()));
					break;
			}
		}

		reset($groups);

		return $groups;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	public function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
		$attr .= ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '"' : '';

		// Get the field groups.
		$groups = (array)$this->getGroups();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string)$this->element['readonly'] == 'true') {
			$html[] = GantryHTMLSelect::groupedlist($groups, null, array('list.attr'         => $attr,
			                                                            'id'                 => $this->id,
			                                                            'list.select'        => $this->value,
			                                                            'group.items'        => null,
			                                                            'option.key.toHtml'  => false,
			                                                            'option.text.toHtml' => false
			                                                       ));
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		} // Create a regular list.
		else {
			$html[] = GantryHTMLSelect::groupedlist($groups, $this->name, array('list.attr'         => $attr,
			                                                                   'id'                 => $this->id,
			                                                                   'list.select'        => $this->value,
			                                                                   'group.items'        => null,
			                                                                   'option.key.toHtml'  => false,
			                                                                   'option.text.toHtml' => false
			                                                              ));
		}

		return implode($html);
	}
}
