<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('No direct access');

require_once(JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php');

class JFormFieldPluginassignment extends JFormField
{

        public $type = 'pluginassignment';


        public function setup(SimpleXMLElement $element, $value, $group = NULL)
	{
		$script = "
			jQuery(document).ready(function()
			{
				menuHide(jQuery('#jform_assignment').val());
				jQuery('#jform_assignment').change(function()
				{
					menuHide(jQuery(this).val());
				})
			});
			function menuHide(val)
			{
				if (val == 0 || val == '-')
				{
					jQuery('#menuselect-group').hide();
				}
				else
				{
					jQuery('#menuselect-group').show();
				}
			}
		";

		// Add the script to the document head
		JFactory::getDocument()->addScriptDeclaration($script);

                return parent::setup($element, $value, $group);
	}

        protected function getInput()
        {
		$html = '<select name="jform[assignment]" id="jform_assignment"> ' .
				JHtml::_('select.options', ModulesHelper::getAssignmentOptions(0), 'value', 'text', $this->value, true) . 
			'</select>';

		return $html;
        }

}
