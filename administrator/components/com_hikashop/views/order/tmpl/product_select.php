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
	$cart_type = hikaInput::get()->getCmd('cart_type', 'cart');
	if($this->element->order_id == 0){
		$cart_id = hikaInput::get()->getInt($cart_type.'_id', 0);
		$parameters = '&cart_type='.$cart_type;
		$parameters .= '&cart_id='.$cart_id;
	}else{
		$parameters = '&order_id='.@$this->element->order_id;
	}
?>
<fieldset>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="btn" type="button" onclick="if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_( 'PLEASE_SELECT_SOMETHING',true ); ?>');}else{submitbutton('product_add');}"><img style="vertical-align:middle;" src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('OK'); ?></button>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<?php if($this->config->get('category_explorer')){?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
<div id="page-product">
	<table style="width:100%">
		<tr>
			<td style="vertical-align:top;border:1px solid #CCC;background-color: #F3F3F3" width="150px">
				<?php echo hikashop_setExplorer('order&task=product_select'.$parameters,$this->pageInfo->filter->filter_id,true,'product'); ?>
			</td>
			<td style="vertical-align:top;">
<?php } else { ?>
<div id="page-product" class="row-fluid">
	<div class="span4">
		<?php echo hikashop_setExplorer('order&task=product_select'.$parameters,$this->pageInfo->filter->filter_id,true,'product'); ?>
	</div>
	<div class="span8">
<?php } ?>
<?php } ?>
			<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=order" method="post"  name="adminForm" id="adminForm">
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
				<table style="width:100%">
					<tr>
						<td>
<?php } else {?>
				<div class="row-fluid">
					<div class="span6">
<?php } ?>
							<a href="<?php echo hikashop_completeLink('order&task=product_select&filter_id=0'.$parameters,true); ?>"><?php echo JText::_( 'ROOT' ); ?>/</a>
							<?php echo $this->breadCrumb.'<br/>'.JText::_( 'FILTER' ); ?>:
							<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
							<button class="btn" onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
							<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
						</td>
						<td>
<?php } else {?>
					</div>
					<div class="span6">
						<div class="expand-filters" style="width:auto;float:right">
<?php } ?>
							<?php echo $this->productType->display('filter_product_type',$this->pageInfo->filter->filter_product_type); ?>
							<?php echo $this->childDisplay; ?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
						</td>
					</tr>
				</table>
<?php } else {?>
						</div>
						<div style="clear:both"></div>
					</div>
				</div>
<?php } ?>
				<table class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'HIKA_NUM' );?>
							</th>
							<th class="title titlebox">
								<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'b.product_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort', JText::_('PRODUCT_CODE'), 'b.product_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
							</th>
							<th class="title">
								<?php echo JText::_('PRODUCT_PRICE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('PRODUCT_QUANTITY'); ?>
							</th>
							<th class="title">
								<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'b.product_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="7">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<?php
							$k = 0;
							for($i = 0,$a = count($this->rows);$i<$a;$i++){
								$row =& $this->rows[$i];
							?>
							<tr class="<?php echo "row$k"; ?>">
								<td class="hk_center">
								<?php echo $this->pagination->getRowOffset($i); ?>
								</td>
								<td class="hk_center">
									<?php echo JHTML::_('grid.id', $i, $row->product_id ); ?>
								</td>
								<td>
									<?php echo $row->product_name; ?>
								</td>
								<td>
									<?php echo $row->product_code; ?>
								</td>
								<td>
									<?php echo $this->currencyHelper->displayPrices(@$row->prices); ?>
								</td>
								<td class="order">
									<input name="quantity[<?php echo $row->product_id ?>]" type="text" size="4" value="1"/>
								</td>
								<td width="1%" class="hk_center">
									<?php echo $row->product_id; ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
						<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
				<input type="hidden" name="task" value="<?php echo hikaInput::get()->getCmd('task'); ?>" />
				<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="tmpl" value="component" />
				<input type="hidden" name="order_id" value="<?php echo @$this->element->order_id;?>" />
				<input type="hidden" id="filter_id" name="filter_id" value="<?php echo $this->pageInfo->filter->filter_id; ?>" />
				<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
				<input type="hidden" name="data[order][order_id]" value="<?php echo @$this->element->order_id;?>" />

				<input type="hidden" name="cart_type" value="<?php echo $this->escape($cart_type); ?>" />
				<input type="hidden" name="<?php echo $cart_type.'_id';?>" value="<?php echo (int)@$cart_id; ?>" />

				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
<?php if($this->config->get('category_explorer')){?>
<?php if(!HIKASHOP_BACK_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
<?php } ?>
