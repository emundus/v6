<?php
/**
 * @version   $Id: moduleposition.php 4795 2012-10-30 23:22:49Z steph $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('text');

/**
 * Supports a modal article picker.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class JFormFieldModulePosition extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ModulePosition';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
        $lang = JFactory::getLanguage();
        $lang->load('mod_roknavmenu', JPATH_BASE, null, false, false)
        || $lang->load('mod_roknavmenu', JPATH_SITE.'/modules/mod_roknavmenu', null, false, false)
        || $lang->load('mod_roknavmenu', JPATH_BASE, $lang->getDefault(), false, false)
        || $lang->load('mod_roknavmenu', JPATH_SITE.'/modules/mod_roknavmenu', $lang->getDefault(), false, false);

		// Get the client id.
		$clientId = $this->element['client_id'];
		if (!isset($clientId))
		{
			$clientName = $this->element['client'];
			if (isset($clientName))
			{
				$client = JApplicationHelper::getClientInfo($clientName, true);
				$clientId = $client->id;
			}
		}
		if (!isset($clientId) && $this->form instanceof JForm) {
			$clientId = $this->form->getValue('client_id');
		}
		$clientId = (int) $clientId;

		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script.
		$script = array();
		$script[] = '	function jSelectPosition_'.$this->id.'(name) {';
		$script[] = '		document.id("'.$this->id.'").value = name;';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display.
		$html	= array();
		$link	= 'index.php?option=com_modules&amp;view=positions&amp;layout=modal&amp;tmpl=component&amp;function=jSelectPosition_'.$this->id.'&amp;client_id='.$clientId;

		// The current user display field.
		$html[] = '<div class="fltlft">';
		$html[] = parent::getInput();
		$html[] = '</div>';

		// The user select button.
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '	<a class="modal" title="'.JText::_('MOD_ROKNAVMENU_SELECT_MODULE_POSITION').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">'.JText::_('MOD_ROKNAVMENU_SELECT_MODULE_POSITION').'</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}
