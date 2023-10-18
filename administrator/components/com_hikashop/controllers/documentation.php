<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class documentationController extends HikashopController{

	function listing() {
		hikashop_setTitle(JText::_('DOCUMENTATION'),'life-ring','documentation');
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'dashboard', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$config =& hikashop_config();
		$level = $config->get('level');
		$url = HIKASHOP_HELPURL.'documentation&level='.$level;
		if(hikashop_isSSL())
			$url = str_replace('http://', 'https://', $url);
		$config =& hikashop_config();
		$menu_style = $config->get('menu_style','title_bottom');
		if(HIKASHOP_J30) $menu_style = 'content_top';
		if($menu_style == 'content_top'){
			echo hikashop_getMenu('',$menu_style);
		}
?>
				<div id="hikashop_div">
						<iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="hikashop_frame" id="hikashop_frame" src="<?php echo $url; ?>">
						</iframe>
				</div>
<?php

	}
}
