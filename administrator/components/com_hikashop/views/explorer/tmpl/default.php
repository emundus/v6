<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikashop_category_explorer_title"><?php echo JText::_( 'EXPLORER' ); ?></div>
<?php
	$control = hikaInput::get()->getCmd('control');
	if(!empty($control)){
		$control='&control='.$control;
	}
	$tree = hikashop_get('type.categorysub');
	$type = null;
	if($this->type == 'status')
		$type = array('status');
	echo $tree->displayTree('product_listing', 0, $type, true, true, $this->defaultId, hikashop_completeLink($this->task.'&type='.$this->type.$control,$this->popup,false,true));
?>
