<?php
/**
 * @version   $Id: moduletypejs.php 4806 2012-10-31 01:03:01Z steph $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_BASE') or die;

jimport('joomla.form.formfield');

/**
 * Supports a modal article picker.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class JFormFieldModuleTypeJS extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ModuleTypeJS';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
        $lang = JFactory::getLanguage();
        $lang->load('mod_roknavmenu', JPATH_SITE, null, true, false)
        || $lang->load('mod_roknavmenu', JPATH_SITE.'/modules/mod_roknavmenu', null, true, false)
        || $lang->load('mod_roknavmenu', JPATH_SITE, $lang->getDefault(), true, false)
        || $lang->load('mod_roknavmenu', JPATH_SITE.'/modules/mod_roknavmenu', $lang->getDefault(), true, false);

        $doc =JFactory::getDocument();
        $doc->addScript(JURI::Root(true)."/modules/mod_roknavmenu/fields/childtype.js");
        return '';
	}

    protected function getLabel(){
        return '';
    }
}
