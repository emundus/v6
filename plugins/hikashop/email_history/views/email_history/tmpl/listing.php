<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('email_history'); ?>" method="post" name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-md-5 hika_j4_search"><?php
		echo $this->loadHkLayout('search', array());
	?></div>
	<div id="hikashop_listing_filters_id" class="hkc-md-7 hikashop_listing_filters" style="text-align: right;">
		<?php echo $this->filter_type->display('filter_type',$this->pageInfo->filter->filter_type) ?>
	</div>
</div>
<?php
	echo $this->loadHkLayout('columns', array());
?>
	<table id="hikashop_email_history_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('TO_ADDRESS'), 'a.email_log_recipient_email', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('REPLYTO_ADDRESS'), 'a.email_log_reply_email', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('EMAIL_SUBJECT'), 'a.email_log_subject', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'a.email_log_date', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'a.email_log_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('ID'), 'a.email_log_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$k = 0;
	$i = 0;
	foreach($this->rows as $row){
?>
			<tr class="row<?php echo $k; ?>">
				<td class="hk_center">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td class="hk_center">
					<?php echo JHTML::_('grid.id', $i, $row->email_log_id ); ?>
				</td>
				<td>
					<?php echo $row->email_log_recipient_email; ?>
				</td>
				<td>
					<?php echo $row->email_log_reply_email; ?>
				</td>
				<td>
<?php if($this->manage){ ?>
					<a href="<?php echo hikashop_completeLink('email_history&task=edit&cid[]='.(int)$row->email_log_id); ?>">
<?php } ?>
					<?php
						if(!empty($row->email_log_subject))
							echo $row->email_log_subject;
						else
							echo '<em>'.JText::_('HIKA_NONE').'</em>';
					?>
<?php if($this->manage){ ?>
					</a>
<?php } ?>
				</td>
				<td>
					<?php echo hikashop_getDate($row->email_log_date); ?>
				</td>
				<td>
					<?php echo str_replace('%s','',JText::_(strip_tags($row->email_log_name))); ?>
				</td>
				<td width="1%" class="hk_center">
					<?php echo (int)$row->email_log_id; ?>
				</td>
			</tr>
<?php
		$k = 1-$k;
		$i++;
	}
?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
