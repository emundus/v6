<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('order'); ?>" method="post" id="hikamarket_order_export_form" name="hikamarket_order_export_form">
	<dl class="hikam_options">
		<dt class="hikamarket_order_export_search"><label><?php echo JText::_('FILTER'); ?></label></dt>
		<dd class="hikamarket_order_export_search">
<?php if(!HIKASHOP_RESPONSIVE) { ?>
			<input type="text" name="search" id="hikamarket_order_listing_search" value="<?php echo $this->escape($this->pageInfo->search);?>" class=""/>
<?php } else { ?>
			<div class="input-prepend input-append">
				<span class="add-on"><i class="icon-filter"></i></span>
				<input type="text" name="search" id="hikamarket_order_listing_search" value="<?php echo $this->escape($this->pageInfo->search);?>" class=""/>
			</div>
<?php } ?>
		</dd>
		<dt class="hikamarket_order_export_status"><label><?php echo JText::_('ORDER_STATUS'); ?></label></dt>
		<dd class="hikamarket_order_export_status"><?php
			echo $this->orderStatusType->display('filter_status', $this->pageInfo->filter->filter_status, '', true);
		?></dd>
		<dt class="hikamarket_order_export_payment"><label><?php echo JText::_('HIKASHOP_PAYMENT_METHOD'); ?></label></dt>
		<dd class="hikamarket_order_export_payment"><?php
			echo $this->paymentType->display('filter_payment', $this->pageInfo->filter->filter_payment, false);
		?></dd>
		<dt class="hikamarket_order_export_startdate"><label><?php echo JText::_('START_DATE'); ?></label></dt>
		<dd class="hikamarket_order_export_startdate"><?php
			echo JHTML::_('calendar', hikamarket::getDate((!empty($this->pageInfo->filter->filter_startdate)?$this->pageInfo->filter->filter_startdate:''),'%Y-%m-%d %H:%M'), 'filter_startdate','start_date','%Y-%m-%d %H:%M',array('size' => '20'));
		?></dd>
		<dt class="hikamarket_order_export_enddate"><label><?php echo JText::_('END_DATE'); ?></label></dt>
		<dd class="hikamarket_order_export_enddate"><?php
			echo JHTML::_('calendar', hikamarket::getDate((!empty($this->pageInfo->filter->filter_enddate)?$this->pageInfo->filter->filter_enddate:''),'%Y-%m-%d %H:%M'), 'filter_enddate','end_date','%Y-%m-%d %H:%M',array('size' => '20'));
		?></dd>
		<dt class="hikamarket_order_export_format"><label><?php echo JText::_('EXPORT_FORMAT'); ?></label></dt>
		<dd class="hikamarket_order_export_format"><?php
			$values = array(
				JHTML::_('select.option', 'csv', JText::_('HIKAM_EXPORT_CSV')),
				JHTML::_('select.option', 'xls', JText::_('HIKAM_EXPORT_XLS'))
			);
			echo JHTML::_('hikaselect.radiolist', $values, 'data[export][format]', '', 'value', 'text', 'csv');
		?></dd>
	</dl>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="export" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
window.hikashop.ready(function(){ window.hikamarket.dlTitle('hikamarket_order_export_form'); });
</script>
