<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class JFormFieldSelectoptions extends JFormField {
	protected $type = 'selectoptions';

	protected function getInput() {
		if(!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!class_exists('hikamarket') && !include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikamarket'.DS.'helpers'.DS.'helper.php'))
			return 'This menu options cannot be displayed without the HikaMarket Component';

		$shopConfig = hikamarket::config(false);
		if(!hikamarket::isAllowed($shopConfig->get('acl_menus_manage', 'all')))
			return 'Access to the HikaMarket options of the menus is restricted';

		$id = hikaInput::get()->getInt('id');
		if(!empty($id)) {
			$text = '<a title="'.JText::_('HIKAMARKET_OPTIONS').'"  href="'.JRoute::_('index.php?option=com_hikamarket&ctrl=menus&fromjoomla=1&task=edit&cid[]='.$id).'">'.JText::_('HIKAMARKET_OPTIONS').'</a>';
		} else {
			$text = JText::_('HIKAMARKET_OPTIONS_EDIT');
		}
		return $text;
	}
}
