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
if(empty($this->fields['order']) || !hikamarket::acl('order/show/customfields'))
	return;

if(empty($this->ajax)) { ?>
<div id="hikamarket_order_block_fields">
<?php } ?>
	<div class="hikamarket_ajax_loading_elem"></div>
	<div class="hikamarket_ajax_loading_spinner"></div>

	<dl class="hikam_options large">
<?php
		foreach($this->fields['order'] as $fieldName => $oneExtraField) {
?>
		<dt class="hikamarket_order_additional_customfield hikamarket_order_additional_customfield_<?php echo $fieldName; ?>"><?php echo $this->fieldsClass->getFieldName($oneExtraField);?></dt>
		<dd class="hikamarket_order_additional_customfield hikamarket_order_additional_customfield_<?php echo $fieldName; ?>"><span><?php
			echo $this->fieldsClass->show($oneExtraField, @$this->order->$fieldName);
		?></span></dd>
<?php
		}
?>
	</dl>
<?php

if(!empty($this->ajax))
	return;
?>
</div>
<script type="text/javascript">
window.Oby.registerAjax('orderMgr.fields',function(params){ window.orderMgr.refreshBlock('fields'); });
</script>
