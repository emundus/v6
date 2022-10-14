<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_checkout_terms" class="hikashop_checkout_terms">
	<label>
		<input class="hikashop_checkout_terms_checkbox" id="hikashop_checkout_terms_checkbox" type="checkbox" name="hikashop_checkout_terms" value="1" <?php if(!empty($this->terms['shop'])) { echo ' checked="checked"'; } ?> />
<?php
	$text = JText::_('PLEASE_ACCEPT_TERMS');
	$link = '';

	$width = (int)@$this->params->options['popup_width'];
	if(empty($width))
		$width = 450;
	$height = (int)@$this->params->options['popup_height'];
	if(empty($height))
		$height = 480;

	$terms_article = (int)@$this->params->options['article_id'];
	if(empty($terms_article))
		$terms_article = (int)$this->shopConfig->get('checkout_terms', 0);
	if(!empty($this->terms_content[1]->vendor_terms)) {
		$link = hikamarket::completeLink('vendor&task=terms&cid=1', true);
	} else if(!empty($terms_article)) {
		if(isset($this->step))
			$link = hikamarket::completeLink('vendor&task=terms&cid=0&step='.$this->step.'&pos='.$this->module_position, true);
		else
			$link = hikamarket::completeLink('vendor&task=terms&cid=0', true);
	}

	if(!empty($link)) {
		echo $this->popupHelper->display(
			$text,
			'HIKASHOP_CHECKOUT_TERMS',
			$link,
			'shop_terms_and_cond',
			$width, $height, '', '', 'link'
		);
	} else {
		echo $text;
	}
?>
	</label>
<?php
	foreach($this->vendors as $vendor) {
		if(!empty($this->terms_content[$vendor]->vendor_terms)) {
?>
	<br/><label>
		<input class="hikashop_checkout_terms_checkbox" id="hikamarket_checkout_terms_checkbox_<?php echo $vendor; ?>" type="checkbox" name="hikamarket_checkout_terms[<?php echo $vendor; ?>]" value="1" <?php if(!empty($this->terms['market'][$vendor])) { echo ' checked="checked"'; } ?> />
<?php
			echo $this->popupHelper->display(
				JText::sprintf('PLEASE_ACCEPT_TERMS_FOR_VENDOR', $this->terms_content[$vendor]->vendor_name),
				'HIKASHOP_CHECKOUT_TERMS',
				hikamarket::completeLink('vendor&task=terms&cid=' . $vendor, true),
				'shop_terms_and_cond_'.$vendor,
				$width, $height, '', '', 'link'
			);
?>
	</label>
<?php
		}
	}
?>
	<input type="hidden" value="1" name="hikamarket_checkout_terms_block"/>
</div>
