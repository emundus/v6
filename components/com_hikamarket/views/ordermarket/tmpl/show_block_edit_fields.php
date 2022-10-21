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
if(empty($this->fields['order']) || !$this->editable_order || !hikamarket::acl('order/show/customfields') || !hikamarket::acl('order/edit/customfields'))
	return;

if(empty($this->ajax)) { ?>
<div id="hikamarket_order_block_fields">
<?php } ?>
<div class="hikamarket_order_edit_block">
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>

	<dl class="hikam_options large dl_glue">
<?php
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikamarket_order_additional_customfield hikamarket_order_additional_customfield_<?php echo $fieldName; ?>"><?php
			echo $this->fieldsClass->getFieldName($oneExtraField, true);
		?></dt>
		<dd class="hikamarket_order_additional_customfield hikamarket_order_additional_customfield_<?php echo $fieldName; ?>"><span><?php
			echo $this->fieldsClass->display($oneExtraField, @$this->order->$fieldName, 'order[field]['.$fieldName.']', false, '', false, $this->fields['order'], $this->order, false);
		?></span></dd>
<?php
		}
?>
	</dl>

	<div style="clear:both;margin-top:4px;"></div>
	<div style="float:right">
		<button onclick="return window.orderMgr.submitBlock('fields', {update:true});" class="hikabtn hikabtn-success"><i class="fas fa-check"></i> <?php echo JText::_('HIKA_OK'); ;?></button>
	</div>
	<button onclick="return window.orderMgr.refreshBlock('fields', false);" class="hikabtn hikabtn-danger"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ;?></button>
	<div style="clear:both">
</div>
<?php

if(!empty($this->ajax))
	return;
?>
</div>
