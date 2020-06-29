<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('order'); ?>" method="post"  name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-md-3"><?php
		echo $this->loadHkLayout('search', array());
	?></div>
	<div class="hkc-md-9 hikashop_listing_filters"><?php
	foreach($this->extrafilters as $name => $filterObj) {
		echo $filterObj->displayFilter($name, $this->pageInfo->filter);
	}
	if(!is_numeric($this->pageInfo->filter->filter_start) && !empty($this->pageInfo->filter->filter_start)) $this->pageInfo->filter->filter_start = strtotime($this->pageInfo->filter->filter_start);
	if(!is_numeric($this->pageInfo->filter->filter_end) && !empty($this->pageInfo->filter->filter_end)) $this->pageInfo->filter->filter_end = strtotime($this->pageInfo->filter->filter_end);
	echo JText::_('FROM').' ';
	echo JHTML::_('calendar', hikashop_getDate((@$this->pageInfo->filter->filter_start?@$this->pageInfo->filter->filter_start:''),'%Y-%m-%d'), 'filter_start','period_start',hikashop_getDateFormat('%d %B %Y'),array('size'=>'10','onchange'=>'document.adminForm.submit();', 'onChange'=>'document.adminForm.submit();'));
	echo ' '.JText::_('TO').' ';
	echo JHTML::_('calendar', hikashop_getDate((@$this->pageInfo->filter->filter_end?@$this->pageInfo->filter->filter_end:''),'%Y-%m-%d'), 'filter_end','period_end',hikashop_getDateFormat('%d %B %Y'),array('size'=>'10','onchange'=>'document.adminForm.submit();', 'onChange'=>'document.adminForm.submit();'));
	echo $this->payment->display("filter_payment",$this->pageInfo->filter->filter_payment,false);
	$this->category->multiple = true;
	echo $this->category->display("filter_status",$this->pageInfo->filter->filter_status,false);
	$this->category->multiple = false;
?></div>
</div>
<?php
	$classes = 'adminlist table';
	if(empty($this->colors)) {
		$classes .= ' table-striped table-hover';
	}
?>
	<table id="hikashop_order_listing" class="<?php echo $classes; ?>" cellpadding="1">
		<thead>
			<tr>
				<th class="hikashop_order_num_title title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="hikashop_order_select_title title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="hikashop_order_number_title title">
					<?php echo JHTML::_('grid.sort', JText::_('ORDER_NUMBER'), 'b.order_number', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					<br/>
					<?php echo JHTML::_('grid.sort', JText::_('INVOICE_NUMBER'), 'b.order_invoice_number', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_customer_title title">
					<?php echo JHTML::_('grid.sort', JText::_('CUSTOMER'), 'c.name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_payment_title title">
					<?php echo JHTML::_('grid.sort', JText::_('PAYMENT_METHOD'), 'b.order_payment_method', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_date_title title">
					<?php echo JHTML::_('grid.sort', JText::_('DATE'), 'b.order_created', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_modified_title title">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_LAST_MODIFIED'), 'b.order_modified', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_status_title title">
					<?php echo JHTML::_('grid.sort',   JText::_('ORDER_STATUS'), 'b.order_status', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="hikashop_order_total_title title">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKASHOP_TOTAL'), 'b.order_full_price', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<?php $count_fields=0;
				if(hikashop_level(2) && !empty($this->fields)){
					foreach($this->fields as $field){
						$count_fields++;
						echo '<th class="hikashop_order_'.$field->field_namekey.'_title title">'.JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'b.'.$field->field_namekey, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ).'</th>';
					}
				}
				$count_extrafields = 0;
				if(!empty($this->extrafields)) {
					foreach($this->extrafields as $namekey => $extrafield) {
						echo '<th class="hikashop_order_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
					}
					$count_extrafields = count($this->extrafields);
				}?>
				<th class="hikashop_order_id_title title">
					<?php echo JHTML::_('grid.sort', JText::_( 'ID' ), 'b.order_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo 10 + $count_fields + $count_extrafields; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$target = '';
				if($this->popup)
					$target = '" target="_top';
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$attributes = '';
					if(!empty($this->orderStatuses[$row->order_status]->orderstatus_color))
						$attributes .= ' style="background-color:'.$this->orderStatuses[$row->order_status]->orderstatus_color.';"';
			?>
				<tr class="row<?php echo $k; ?>"<?php echo $attributes; ?>>
					<td class="hikashop_order_num_value">
					<?php echo $this->pagination->getRowOffset($i);
					?>
					</td>
					<td class="hikashop_order_select_value">
						<?php echo JHTML::_('grid.id', $i, $row->order_id ); ?>
					</td>
					<td class="hikashop_order_number_value">
						<?php if($this->manage){ ?>
							<a title="<?php echo JText::_('ORDER_NUMBER'); ?>" href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$row->order_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('order')))).$target; ?>">
						<?php } ?>
								<?php echo $row->order_number; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
						<?php if(!empty($row->order_invoice_number)) {
								if($this->manage){ ?>
							<a title="<?php echo JText::_('INVOICE_NUMBER'); ?>" href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$row->order_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('order')))).$target; ?>">
							<?php } ?>
								<?php echo '<br/>'.$row->order_invoice_number; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php }
							} ?>
					</td>
					<td class="hikashop_order_customer_value">
						<?php
						if(empty($row->address_firstname) && empty($row->address_lastname)){
							echo $row->name;
						}else{
							echo $row->address_firstname.' '.$row->address_middle_name.' '.$row->address_lastname;
						}
						if(!empty($row->username)){
							echo ' ( '.$row->username.' )';
						}
						echo '<br/>';
						if(!empty($row->user_id)){
							$url = hikashop_completeLink('user&task=edit&cid[]='.$row->user_id);
							$config =& hikashop_config();
							if(hikashop_isAllowed($config->get('acl_user_manage','all'))) echo $row->user_email.' <a href="'.$url.$target.'"><i class="fa fa-chevron-right"></i></a>';
						}elseif(!empty($row->user_email)){
							echo $row->user_email;
						}
						?>
					</td>
					<td class="hikashop_order_payment_value">
						<?php if(!empty($row->order_payment_method)){
							if(!empty($this->payments[$row->order_payment_id])){
								echo $this->payments[$row->order_payment_id]->payment_name;
							}elseif(!empty($this->payments[$row->order_payment_method])){
								echo $this->payments[$row->order_payment_method]->payment_name;
							}else{
								echo $row->order_payment_method;
							}
						} ?>
					</td>
					<td class="hikashop_order_date_value">
						<?php echo hikashop_getDate($row->order_created,'%d %B %Y %H:%M');?>
					</td>
					<td class="hikashop_order_modified_value">
						<?php echo hikashop_getDate($row->order_modified,'%d %B %Y %H:%M');?>
					</td>
					<td class="hikashop_order_status_value">
						<?php
						if($this->manage && !$this->popup){
							$doc = JFactory::getDocument();
							$doc->addScriptDeclaration(' var '."default_filter_status_".$row->order_id.'=\''.$row->order_status.'\'; ');
							echo $this->category->display("filter_status_".$row->order_id,$row->order_status,'onchange="if(this.value==default_filter_status_'.$row->order_id.'){return;} hikashop.openBox(\'status_change_link\',\''.hikashop_completeLink('order&task=changestatus&order_id='.$row->order_id,true).'&status=\'+this.value);this.value=default_filter_status_'.$row->order_id.';if(typeof(jQuery)!=\'undefined\'){jQuery(this).trigger(\'liszt:updated\');}"');
						} else {
							echo $row->order_status;
						}
						?>
					</td>
					<td class="hikashop_order_total_value">
						<?php echo $this->currencyHelper->format($row->order_full_price,$row->order_currency_id);?>
					</td>
<?php
					if(hikashop_level(2) && !empty($this->fields)){
						foreach($this->fields as $field){
							$namekey = $field->field_namekey;
							echo '<td class="hikashop_order_'.$namekey.'_value">';
							if(!empty($row->$namekey)) echo $this->fieldsClass->show($field,$row->$namekey);
							echo '</td>';
						}
					}
					if(!empty($this->extrafields)) {
						foreach($this->extrafields as $namekey => $extrafield) {
							$value = '';
							if(!empty($extrafield->value)) {
								$n = $extrafield->value;
								$value = $row->$n;
							} else if(!empty($extrafield->obj)) {
								$n = $extrafield->obj;
								$value = $n->showfield($this, $namekey, $row);
							}
							echo '<td class="hikashop_order_'.$namekey.'_value">'.$value.'</td>';
						}
					}
?>
					<td class="hikashop_order_id_value">
						<?php echo $row->order_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<?php if($this->manage && !$this->popup){
		echo $this->popupHelper->display(
			JText::_('ORDER_STATUS'),
			'ORDER_STATUS',
			'/',
			'status_change_link',
			760, 480, 'style="display:none;"', '', 'link'
		);
	}
	?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_product" value="<?php echo $this->pageInfo->filter->filter_product; ?>"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
