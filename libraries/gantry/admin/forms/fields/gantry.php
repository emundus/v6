<?php
/**
 * @version   $Id: gantry.php 6564 2013-01-16 17:13:36Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();
/**
 * @package     gantry
 * @subpackage  admin.elements
 */
gantry_import('core.config.gantryformfield');

class GantryFormFieldGANTRY extends GantryFormField
{

	protected $type = 'gantry';
	protected $basetype = 'none';

	public function getInput()
	{
		/** @global $gantry Gantry */
		global $gantry;
		$gantry->addInlineScript("var GantryTemplate = '" . $gantry->templateName . "', GantryParamsPrefix = 'jform_params_', GantryAjaxURL = '" . $gantry->getAjaxUrl() . "'; GantryURL = '" . $gantry->gantryUrl . "';");
		$gantry->addInlineScript($this->_gantryLang());

		return null;
	}

	protected function _gantryLang()
	{
		return "
			GantryLang = {
				'preset_title': '" . JText::_('PRESET_TITLE') . "',
				'preset_select': '" . JText::_('PRESET_SELECT') . "',
				'preset_name': '" . JText::_('PRESET_NAME') . "',
				'key_name': '" . JText::_('KEY_NAME') . "',
				'preset_naming': '" . JText::_('PRESET_NAMING') . "',
				'preset_skip': '" . JText::_('PRESET_SKIP') . "',
				'success_save': '" . JText::_('SUCCESS_SAVE') . "',
				'success_msg': '" . JText::_('SUCCESS_MSG') . "',
				'fail_save': '" . JText::_('FAIL_SAVE') . "',
				'fail_msg': '" . JText::_('FAIL_MSG') . "',
				'cancel': '" . JText::_('CANCEL') . "',
				'save': '" . JText::_('SAVE') . "',
				'retry': '" . JText::_('RETRY') . "',
				'close': '" . JText::_('CLOSE') . "',
				'show_parameters': '" . JText::_('SHOW_PARAMETERS') . "'
			};
		";
	}

	public function getLabel()
	{
		return "";
	}

}
