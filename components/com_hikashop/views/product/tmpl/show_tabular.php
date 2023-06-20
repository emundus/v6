<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
hikashop_loadJslib('jquery');
$js = '';
$params = null;
$this->params->set('vote_type','product');
if(isset($this->element->main)){
	$product_id = $this->element->main->product_id;
}else{
	$product_id = $this->element->product_id;
}
$this->params->set('vote_ref_id',$product_id);
$this->params->set('productlayout','show_tabular');
$layout_vote_mini = hikashop_getLayout('vote', 'mini', $this->params, $js);
$layout_vote_listing = hikashop_getLayout('vote', 'listing', $this->params, $js);
$comments_count = $this->params->get('comments_count', -1);
$layout_vote_form = hikashop_getLayout('vote', 'form', $this->params, $js);
$config =& hikashop_config();
$status_vote = $config->get('enable_status_vote');
$this->setLayout('show_block_dimensions');
$specif_tab_content = trim($this->loadTemplate());
if(!empty($this->fields)){
	$this->setLayout('show_block_custom_main');
	$specif_tab_content = trim($this->loadTemplate()) . $specif_tab_content;
}
$description = trim(JHTML::_('content.prepare',preg_replace('#<hr *id="system-readmore" */>#i','',$this->element->product_description)));
$selected = '';

?>
<div id="hikashop_product_top_part" class="hikashop_product_top_part">
<!-- TOP BEGIN EXTRA DATA -->
<?php if(!empty($this->element->extraData->topBegin)) { echo implode("\r\n", $this->element->extraData->topBegin); } ?>
<!-- EO TOP BEGIN EXTRA DATA -->
	<h1>
<!-- PRODUCT NAME -->
		<span id="hikashop_product_name_main" class="hikashop_product_name_main" itemprop="name"><?php
			if (hikashop_getCID('product_id')!=$this->element->product_id && isset ($this->element->main->product_name))
				echo $this->element->main->product_name;
			else
				echo $this->element->product_name;
		?></span>
<!-- EO PRODUCT NAME -->
<!-- PRODUCT CODE -->
<?php if ($this->config->get('show_code')) { ?>
		<span id="hikashop_product_code_main" class="hikashop_product_code_main">
			<?php
				echo $this->element->product_code;
			?>
		</span>
<?php } ?>
<!-- EO PRODUCT CODE -->
		<meta itemprop="sku" content="<?php echo $this->element->product_code; ?>">
		<meta itemprop="productID" content="<?php echo $this->element->product_code; ?>">
	</h1>
<!-- TOP END EXTRA DATA -->
<?php if(!empty($this->element->extraData->topEnd)) { echo implode("\r\n", $this->element->extraData->topEnd); } ?>
<!-- EO TOP END EXTRA DATA -->
<!-- SOCIAL NETWORKS BUTTONS -->
<?php
	$this->setLayout('show_block_social');
	echo $this->loadTemplate();
?>
<!-- EO SOCIAL NETWORKS BUTTONS -->
</div>
<div class="hk-row-fluid">
<div id="hikashop_product_left_part" class="hikashop_product_left_part hkc-md-6">
<!-- LEFT BEGIN EXTRA DATA -->
<?php
	if(!empty($this->element->extraData->leftBegin)) { echo implode("\r\n", $this->element->extraData->leftBegin); }
?>
<!-- EO LEFT BEGIN EXTRA DATA -->
<!-- IMAGES -->
<?php
	$this->row =& $this->element;
	$this->setLayout('show_block_img');
	echo $this->loadTemplate();
?>
<!-- EO IMAGES -->
<!-- MINI DESCRIPTION -->
	<div id="hikashop_product_description_main_mini" class="hikashop_product_description_main_mini"><?php
		if(!empty($this->element->product_description)) {
			$function = 'mb_substr';
			if(!function_exists($function))
				$function = 'substr';
			$resume = $function(strip_tags(preg_replace('#<hr *id="system-readmore" */>.*#is','',$this->element->product_description)),0,300);
			if (!empty($this->element->product_description) && strlen($this->element->product_description)>300)
				$resume .= " ...<a href='#hikashop_show_tabular_description'>".JText::_('READ_MORE')."</a>";
			echo JHTML::_('content.prepare',$resume);
		}
	?></div>
<!-- EO MINI DESCRIPTION -->
<!-- LEFT END EXTRA DATA -->
<?php
	if(!empty($this->element->extraData->leftEnd))
		echo implode("\r\n",$this->element->extraData->leftEnd);
?>
<!-- EO LEFT END EXTRA DATA -->
</div>
<div id="hikashop_product_right_part" class="hikashop_product_right_part hkc-md-6">
<?php
	$itemprop_offer = '';
	if (!empty($this->element->prices))
		$itemprop_offer = 'itemprop="offers" itemscope itemtype="https://schema.org/Offer"';
	$form = ',0';
	if(!$this->config->get('ajax_add_to_cart', 1)) {
		$form = ',\'hikashop_product_form\'';
	}
?>
<!-- RIGHT BEGIN EXTRA DATA -->
<?php
	if(!empty($this->element->extraData->rightBegin))
		echo implode("\r\n",$this->element->extraData->rightBegin);
?>
<!-- EO RIGHT BEGIN EXTRA DATA -->
<!-- PRICES -->
	<span id="hikashop_product_price_main" class="hikashop_product_price_main" <?php echo $itemprop_offer; ?>>
<?php
	if($this->params->get('show_price') && (empty($this->displayVariants['prices']) || $this->params->get('characteristic_display') != 'list')) {
		$this->row =& $this->element;
		$this->setLayout('listing_price');
		echo $this->loadTemplate();


		$CurrId = hikashop_getCurrency();
		$null = null;
		$currency = $this->currencyHelper->getCurrencies($CurrId, $null);
		$CurrCode = $currency[$CurrId]->currency_code;
	if (!empty($this->element->prices)) {
?>
		<meta itemprop="price" content="<?php echo $this->itemprop_price; ?>" />
		<meta itemprop="availability" content="https://schema.org/<?php echo ($this->row->product_quantity != 0) ? 'InStock' : 'OutOfstock' ;?>" />
		<meta itemprop="priceCurrency" content="<?php echo $CurrCode; ?>" />
<?php
		}
	}
?>
	</span>
<!-- EO PRICES -->
	<br />
<!-- VOTE MINI -->
	<div id="hikashop_product_vote_mini" class="hikashop_product_vote_mini">
<?php
	if($this->params->get('show_vote_product') == '-1') {
		$this->params->set('show_vote_product', $config->get('show_vote_product'));
	}
	if($this->params->get('show_vote_product')) {
		echo $layout_vote_mini;
	}
?>
	</div>
<!-- EO VOTE MINI -->
<!-- CHARACTERISTICS -->
<?php
	if($this->params->get('characteristic_display') != 'list') {
		$this->setLayout('show_block_characteristic');
		echo $this->loadTemplate();
?>
		<br />
<?php
	}
?>
<!-- EO CHARACTERISTICS -->
<!-- OPTIONS -->
<?php
	if(hikashop_level(1) && !empty ($this->element->options)) {
?>
		<div id="hikashop_product_options" class="hikashop_product_options"><?php
			$this->setLayout('option');
			echo $this->loadTemplate();
		?></div>
		<br />
<?php
		$form = ',\'hikashop_product_form\'';
		if ($this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
?>
		<input type="hidden" name="popup" value="1"/>
<?php
		}
	}
?>
<!-- EO OPTIONS -->
<!-- CUSTOM ITEM FIELDS -->
<?php
	if (!$this->params->get('catalogue') && ($this->config->get('display_add_to_cart_for_free_products') ||  ($this->config->get('display_add_to_wishlist_for_free_products', 1) && hikashop_level(1) && $this->params->get('add_to_wishlist') && $config->get('enable_wishlist', 1)) || !empty ($this->element->prices))) {
		if (!empty ($this->itemFields)) {
			$form = ',\'hikashop_product_form\'';
			if ($this->config->get('redirect_url_after_add_cart', 'stay_if_cart') == 'ask_user') {
?>
		<input type="hidden" name="popup" value="1"/>
<?php
			}
			$this->setLayout('show_block_custom_item');
			echo $this->loadTemplate();
		}
	}
?>
<!-- EO CUSTOM ITEM FIELDS -->
<!-- TOTAL PRICE WITH OPTIONS -->
<?php
	if($this->params->get('show_price')) {
?>
		<span id="hikashop_product_price_with_options_main" class="hikashop_product_price_with_options_main"></span>
<?php
	}
?>
<!-- EO TOTAL PRICE WITH OPTIONS -->
<!-- CONTACT BUTTON -->
	<div id="hikashop_product_contact_main" class="hikashop_product_contact_main">
<?php
	$contact = $this->config->get('product_contact', 0);
	if(hikashop_level(1) && ($contact == 2 || ($contact == 1 && !empty($this->element->product_contact)))) {
		$css_button = $this->config->get('css_button', 'hikabtn');
		$attributes = 'class="'.$css_button.'"';
		$fallback_url = hikashop_completeLink('product&task=contact&cid=' . (int)$this->element->product_id . $this->url_itemid);
		$content = JText::_('CONTACT_US_FOR_INFO');

		echo $this->loadHkLayout('button', array( 'attributes' => $attributes, 'content' => $content, 'fallback_url' => $fallback_url));
	}
?>
	</div>
<!-- EO CONTACT BUTTON -->
<!-- RIGHT MIDDLE EXTRA DATA -->
<?php
	if(!empty($this->element->extraData->rightMiddle))
		echo implode("\r\n",$this->element->extraData->rightMiddle);
?>
<!-- EO RIGHT MIDDLE EXTRA DATA -->
<?php
	$this->formName = $form;
?>
	<span id="hikashop_product_id_main" class="hikashop_product_id_main">
		<input type="hidden" name="product_id" value="<?php echo $this->element->product_id; ?>" />
	</span>
	<br />
<!-- ADD TO CART -->
<?php if(empty ($this->element->characteristics) || $this->params->get('characteristic_display') != 'list') { ?>
		<div id="hikashop_product_quantity_main" class="hikashop_product_quantity_main"><?php
			$this->row = & $this->element;
			$this->ajax = 'if(hikashopCheckChangeForm(\'item\',\'hikashop_product_form\')){ return hikashopModifyQuantity(\'' . $this->row->product_id . '\',field,1' . $form . ',\'cart\'); } else { return false; }';
			$this->setLayout('quantity');
			echo $this->loadTemplate();
		?></div>
		<div id="hikashop_product_quantity_alt" class="hikashop_product_quantity_main_alt" style="display:none;">
			<?php echo JText::_('ADD_TO_CART_AVAILABLE_AFTER_CHARACTERISTIC_SELECTION'); ?>
		</div>
<?php
	}
?>
<!-- EO ADD TO CART -->
<!-- FILES -->
<?php
	$this->setLayout('show_block_product_files');
	echo $this->loadTemplate();
?>
<!-- EO FILES -->
<!-- TAGS -->
<?php
	if(HIKASHOP_J30) {
		$this->setLayout('show_block_tags');
		echo $this->loadTemplate();
	}
?>
<!-- EO TAGS -->
<!-- RIGHT END EXTRA DATA -->
<?php
	if(!empty($this->element->extraData->rightEnd))
		echo implode("\r\n",$this->element->extraData->rightEnd);
?>
<!-- EO RIGHT END EXTRA DATA -->
</div>
</div>
	<input type="hidden" name="cart_type" id="type" value="cart"/>
	<input type="hidden" name="add" value="<?php echo !$this->config->get('synchronized_add_to_cart', 0); ?>"/>
	<input type="hidden" name="ctrl" value="product"/>
	<input type="hidden" name="task" value="updatecart"/>
	<input type="hidden" name="return_url" value="<?php echo urlencode(base64_encode(urldecode($this->redirect_url)));?>"/>
</form>
<!-- END GRID -->
<div id="hikashop_product_bottom_part" class="hikashop_product_bottom_part show_tabular">
<!-- BOTTOM BEGIN EXTRA DATA -->
<?php
	if(!empty($this->element->extraData->bottomBegin))
		echo implode("\r\n",$this->element->extraData->bottomBegin);
?>
<!-- EO BOTTOM BEGIN EXTRA DATA -->
	<div id="hikashop_tabs_div">
		<ul class="hikashop_tabs_ul">
<!-- DESCRIPTION TAB TITLE -->
<?php if(!empty($description) || !empty ($this->element->product_url)) {
		if(empty($selected)) $selected = 'hikashop_show_tabular_description'; ?>
			<li id="hikashop_show_tabular_description_li" class="hikashop_tabs_li ui-corner-top"><?php echo JText::_('PRODUCT_DESCRIPTION');?></li>
<?php } ?>
<!-- EO DESCRIPTION TAB TITLE -->
<!-- SPECIFICATION TAB TITLE -->
<?php if(!empty($specif_tab_content)) {
		if(empty($selected)) $selected = 'hikashop_show_tabular_specification'; ?>
			<li id="hikashop_show_tabular_specification_li" class="hikashop_tabs_li ui-corner-top"><?php echo JText::_('SPECIFICATIONS');?></li>
<?php } ?>
<!-- EO SPECIFICATION TAB TITLE -->
<!-- VOTE TAB TITLE -->
<?php if(in_array($status_vote, array('comment', 'two', 'both'))) {
		if(empty($selected)) $selected = 'hikashop_show_tabular_comment';
		if($comments_count != 0) { ?>
			<li id="hikashop_show_tabular_comment_li" class="hikashop_tabs_li ui-corner-top"><?php echo JText::_('PRODUCT_COMMENT');?><?php if($comments_count>0) echo ' ('.$comments_count.')'; ?></li>
<?php } ?>
			<li id="hikashop_show_tabular_new_comment_li" class="hikashop_tabs_li ui-corner-top"><?php echo JText::_('PRODUCT_NEW_COMMENT');?></li>
<?php } ?>
<!-- EO VOTE TAB TITLE -->
		</ul>
<?php if(!empty($description) || !empty ($this->element->product_url)) { ?>
		<div class="hikashop_tabs_content" id="hikashop_show_tabular_description">
<!-- DESCRIPTION -->
<?php if(!empty($description)) { ?>
			<div id="hikashop_product_description_main" class="hikashop_product_description_main" itemprop="description"><?php
				echo $description;
			?></div>
<?php } ?>
<!-- EO DESCRIPTION -->
<!-- MANUFACTURER URL -->
			<span id="hikashop_product_url_main" class="hikashop_product_url_main"><?php
				if (!empty ($this->element->product_url)) {
					echo JText::sprintf('MANUFACTURER_URL', '<a href="' . $this->element->product_url . '" target="_blank">' . $this->element->product_url . '</a>');
				}
			?></span>
<!-- EO MANUFACTURER URL -->
<!-- BOTTOM MIDDLE EXTRA DATA -->
<?php
			if(!empty($this->element->extraData->bottomMiddle))
				echo implode("\r\n",$this->element->extraData->bottomMiddle);
?>
<!-- EO BOTTOM MIDDLE EXTRA DATA -->
		</div>
<?php } ?>
<?php if(!empty($specif_tab_content)) { ?>
		<div class="hikashop_tabs_content" id="hikashop_show_tabular_specification">
<!-- SPECIFICATIONS -->
		<?php
				echo $specif_tab_content;
		?>
<!-- EO SPECIFICATIONS -->
		</div>
<?php }
?>
<!-- VOTE TAB -->
<?php
	if($status_vote == "comment" || $status_vote == "two" || $status_vote == "both" ) { ?>
		<form action="<?php echo hikashop_currentURL() ?>" method="post" name="adminForm_hikashop_comment_form" id="hikashop_comment_form">
<?php
			if($comments_count != 0) {
?>
			<div class="hikashop_tabs_content" id="hikashop_show_tabular_comment">
				<div id="hikashop_vote_listing" data-votetype="product" class="hikashop_product_vote_listing"><?php
					echo $layout_vote_listing;
				?></div>
			</div>
<?php } ?>
			<div class="hikashop_tabs_content" id="hikashop_show_tabular_new_comment">
				<div id="hikashop_vote_form" data-votetype="product" class="hikashop_product_vote_form"><?php
					echo $layout_vote_form;
				?></div>
			</div>
		</form>
<?php } ?>
<!-- EO VOTE TAB -->
<!-- BOTTOM END EXTRA DATA -->
<?php
	if(!empty($this->element->extraData->bottomEnd))
		echo implode("\r\n",$this->element->extraData->bottomEnd);
?>
<!-- EO BOTTOM END EXTRA DATA -->
<input type="hidden" name="selected_tab" id="selected_tab" value="<?php echo $selected; ?>"/>
	</div>
</div>
<script type="text/javascript">
if(typeof(hkjQuery) == "undefined") window.hkjQuery = window.jQuery;
window.hikashop.ready(function(){
	var selectedTab = hkjQuery( "#selected_tab" ).val();
	hkjQuery("#hikashop_tabs_div").children("div").css("display","none");
	hkjQuery("#"+selectedTab+"_li").addClass("hikashop_tabs_li_selected");
	hkjQuery("#"+selectedTab).css("display","inherit");
	hkjQuery("#hikashop_tabs_div .hikashop_tabs_ul li").click(function(){
		var currentLi = hkjQuery(this).attr("id");
		var currentDiv = currentLi.replace("_li","");
		hkjQuery("#hikashop_tabs_div").children("div").css("display","none");
		hkjQuery("#hikashop_tabs_div").children("form").children("div").css("display","none");
		hkjQuery("#"+currentDiv).css("display","inherit");
		hkjQuery(".hikashop_tabs_li_selected" ).removeClass("hikashop_tabs_li_selected");
		hkjQuery("#"+currentLi).addClass("hikashop_tabs_li_selected");
	});
});
</script>
