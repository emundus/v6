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
$config = hikashop_config();
$delay = (int)$config->get('switcher_cookie_retaining_period', 31557600);

if ($this->type == 'manufacturer') {
	$cookie_ref = 'manufacturer_exploreWidth_cookie';
	$cookie_value = 'explorer_close';
}
else {
	if ($this->task =='category&task=listing') {
		$cookie_ref = 'category_exploreWidth_cookie';
		$cookie_value = 'explorer_close';
	}
	else {
		$cookie_ref = 'product_exploreWidth_cookie';
		$cookie_value = 'explorer_open';
	}
}

if(isset($_COOKIE[$cookie_ref])) {
	$cookie_value = $_COOKIE[$cookie_ref];
}

?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};

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
function explorerWidth(delay) {
	var desc = document.querySelector('#hikashop_category_explorer_container');
	var button = document.querySelector('.hikashop_explorer_button_width');

	if (desc.classList.contains("explorer_close")) {
		desc.classList.remove("explorer_close");
		desc.classList.add("explorer_open");
		button.classList.remove("explorer_close");
		button.classList.add("explorer_open");

		window.hikashop.setCookie('<?php echo $cookie_ref; ?>','explorer_open',delay);
		return;
	}
	if (desc.classList.contains("explorer_open")) {
		desc.classList.remove("explorer_open");
		desc.classList.add("explorer_close");
		button.classList.remove("explorer_open");
		button.classList.add("explorer_close");

		window.hikashop.setCookie('<?php echo $cookie_ref; ?>','explorer_close',delay);
		return;
	}
}
</script>

<span onclick="explorerWidth('<?php echo $delay; ?>'); return false;" 
  data-bs-original-title="<?php echo JText::_( 'HIKA_EXPLORER_OPEN' ); ?>"
  class="hikashop_explorer_button_width hikabtn hikabtn-primary hasTooltip <?php echo $cookie_value; ?>">
	<i class="fas fa-chevron-left"></i>
	<i class="fas fa-chevron-right"></i>
</span>

<div id="hikashop_category_explorer_title" class="hikashop_category_explorer_title">
	<span><?php echo JText::_( 'EXPLORER' ); ?></span>
	<a class="hikashop_explorer_button" onclick="openExplorer(); return false;">
		<i class="fas fa-chevron-down"></i>
	</a>
</div>
<?php
	$control = hikaInput::get()->getCmd('control');
	if(!empty($control)){
		$control='&control='.$control;
	}
	$tree = hikashop_get('type.categorysub');
	$type = null;
	switch($this->type) {
		case 'status':
			$type = array('status');
			break;
		case 'manufacturer':
			$type = array('manufacturer');
			break;
		case 'product':
			$type = array('product','vendor');
			break;
		default:
			break;
	}
	echo $tree->displayTree('product_listing', 0, $type, true, true, $this->defaultId, hikashop_completeLink($this->task.'&type='.$this->type.$control,$this->popup,false,true));
?>
