<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
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
