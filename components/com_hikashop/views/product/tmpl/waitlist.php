<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_product_waitlist_<?php echo hikaInput::get()->getInt('cid');?>_page" class="hikashop_product_waitlist_page">
	<div class="hikashop_product_waitlist_title">
<!-- TITLE -->
<?php
		$name = '';
		if(!empty($this->product->product_name)) {
			$name = $this->product->product_name;
		}
		if(!empty($this->product_url)) {
			$name = '<a href="'. $this->product_url.'">'. $name.'</a>';
		}
		if(!empty($name)) {
			echo Jtext::sprintf('WAITLIST_FOR_PRODUCT', $name);
		}
?>
<!-- EO TITLE -->
	</div>
	<fieldset>
		<div class="toolbar" id="toolbar" style="float: right;">
<!-- OK BUTTON -->
			<button class="hikabtn hikabtn-success" type="button" onclick="submitform('add_waitlist');"><i class="fa fa-check"></i> <?php echo JText::_('OK'); ?></button>
<!-- EO OK BUTTON -->
<!-- CANCEL BUTTON -->
			<button class="hikabtn hikabtn-danger" type="button" onclick="history.back();"><i class="fa fa-times"></i> <?php echo JText::_('HIKA_CANCEL'); ?></button>
<!-- EO CANCEL BUTTON -->
		</div>
	</fieldset>
	<div class="iframedoc" id="iframedoc"></div>
	<form action="<?php echo hikashop_completeLink('product'.$this->url_itemid, hikaInput::get()->getString('tmpl') == 'tmpl'); ?>" method="post"  name="adminForm" id="adminForm">
		<table>
<!-- NAME -->
			<tr>
				<td class="key">
					<label for="data[register][name]">
						<?php echo JText::_( 'HIKA_USER_NAME' ); ?>
					</label>
				</td>
				<td>
					<?php
					$attributes = 'value="'.$this->escape((string)@$this->element->name).'"';
					if(!empty($this->element->user_cms_id)) {
						$attributes = 'value="" placeholder="'.$this->escape((string)@$this->element->name).'"';
					}
					?>
					<input type="text" name="data[register][name]" class="<?php echo HK_FORM_CONTROL_CLASS; ?>" size="40" <?php echo $attributes;?> />
				</td>
			</tr>
<!-- EO NAME -->
<!-- EMAIL -->
			<tr>
				<td class="key">
					<label for="data[register][email]">
						<?php echo JText::_( 'HIKA_EMAIL' ); ?> <span class="hikashop_field_required_label">*</span>
					</label>
				</td>
				<td>
					<?php
					$attributes = 'value="'.$this->escape((string)@$this->element->email).'"';
					if(!empty($this->element->user_cms_id)) {
						$attributes = 'value="" placeholder="'.$this->escape((string)@$this->element->email).'"';
					}
					?>
					<input type="text" name="data[register][email]" class="<?php echo HK_FORM_CONTROL_CLASS; ?>" size="40" <?php echo $attributes;?> />
				</td>
			</tr>
<!-- EO EMAIL -->
<!-- CONFIRM CONSENT -->
<?php
	if(!empty($this->privacy)) {
		$text = JText::_( 'PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL' ) . ' <span class="hikashop_field_required_label">*</span>';
		if(!empty($this->privacy['id']) || !empty($this->privacy['url'])) {
			$popupHelper = hikashop_get('helper.popup');
			$text = $popupHelper->display(
				$text,
				'PLG_CONTENT_CONFIRMCONSENT_CONSENTBOX_LABEL',
				JRoute::_('index.php?option=com_hikashop&ctrl=checkout&task=privacyconsent&type=contact&tmpl=component'),
				'contact_privacyconsent',
				800, 500, '', '', 'link'
			);
		}
?>
			<tr>
				<td class="key">
					<label><?php echo $text; ?></label>
				</td>
				<td>
					<label class="checkbox">
						<input type="checkbox" id="hikashop_waitlist_consent" name="data[register][consent]" value="1"/> <?php echo $this->privacy['text']; ?>
					</label>
					<input type="hidden" name="data[register][consentcheck]" value="1"/>
				</td>
			</tr>
<?php
	}
?>
<!-- EO CONFIRM CONSENT -->
		</table>
		<input type="hidden" name="data[register][product_id]" value="<?php echo hikaInput::get()->getInt('cid');?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="cid" value="<?php echo hikaInput::get()->getInt('cid');?>" />
		<input type="hidden" name="ctrl" value="product" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
