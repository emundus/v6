<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of categories
 */
class JFormFieldTimeupdated extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'timeupdated';

	/**
	 * Method to get the field input markup.
	 *
	 * @return    string    The field input markup.
	 * @since    1.6
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html     = array();
		$config   = JFactory::getConfig();
		$timezone = new DateTimeZone($config->get('offset'));

		$old_time_updated = $this->value;
		$hidden           = (boolean) $this->element['hidden'];
		if ($hidden == null || !$hidden) {
			if (!strtotime($old_time_updated)) {
				$html[] = '-';
			}
			else {
				$jdate       = new JDate($old_time_updated);
				$pretty_date = $jdate->format(JText::_('DATE_FORMAT_LC2'));
				$html[]      = "<div>" . $pretty_date . "</div>";
			}
		}

		$time_updated = JFactory::getDate()->setTimezone($timezone);
		$html[]       = '<input type="hidden" name="' . $this->name . '" value="' . $time_updated . '" />';

		return implode($html);
	}
}