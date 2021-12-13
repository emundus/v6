<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">		
function openExplorer() {
  var element = document.getElementById('product_listing_otree');
  var button = document.getElementById('hikashop_category_explorer_title');
	if (element.classList.contains('open_tree')) {
		element.classList.remove("open_tree");
		button.classList.remove("open_tree");
	}	
	else {
		element.classList.add("open_tree");
		button.classList.add("open_tree");
	}
};

</script>

<div id="hikashop_category_explorer_title" class="hikashop_category_explorer_title">
	<span><?php echo JText::_( 'EXPLORER' ); ?></span>
	<a class="hikashop_explorer_button" onclick="openExplorer(); return false;">
		<span class="close_tree"><i class="fas fa-2x fa-sort-down close_tree"></i></span>
		<span class="open_tree"><i class="fas fa-2x fa-sort-up open_tree"></i></span>
	</a>
</div>
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

