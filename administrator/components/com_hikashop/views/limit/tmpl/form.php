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
<div>
	<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=limit" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div id="hikashop_limit_form" class="hk-row-fluid hikashop_backend_tile_edition">
		<div class="hkc-md-6">
			<div class="hikashop_tile_block">
				<div>
					<div class="hikashop_tile_title"><?php
						echo JText::_('MAIN_INFORMATION');
					?></div>
					<table class="admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'PERIOD' ); ?>
							</td>
							<td>
								<?php
								$values = array();
								$values[] = JHTML::_('select.option', 'cart', JText::_('HIKASHOP_CART'));
								$values[] = JHTML::_('select.option', 'daily', JText::_('HIKASHOP_DAILY'));
								$values[] = JHTML::_('select.option', 'weekly', JText::_('HIKASHOP_WEEKLY'));
								$values[] = JHTML::_('select.option', 'monthly', JText::_('HIKASHOP_MONTHLY'));
								$values[] = JHTML::_('select.option', 'quarterly', JText::_('HIKASHOP_QUARTERLY'));
								$values[] = JHTML::_('select.option', 'yearly', JText::_('HIKASHOP_YEARLY'));
								$values[] = JHTML::_('select.option', 'forever', JText::_('HIKASHOP_FOREVER'));
								echo JHTML::_('select.genericlist', $values, "data[limit][limit_periodicity]" , 'class="custom-select" size="1"', 'value', 'text', @$this->element->limit_periodicity );
								?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_TYPE' ); ?>
							</td>
							<td>
								<?php $attr = '';
								if(!empty($this->element->limit_id))
									$attr = 'onchange="this.form.submit();"';
								echo $this->type->display('data[limit][limit_type]', @$this->element->limit_type, false, $attr); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'VALUE' ); ?>
							</td>
							<td>
								<input type="text" name="data[limit][limit_value]" value="<?php echo $this->escape(@$this->element->limit_value); ?>" />
							</td>
						</tr>
<?php
if(empty($this->element->limit_type) || $this->element->limit_type == 'weight') {
?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'WEIGHT_SYMBOLS' ); ?>
							</td>
							<td>
								<?php echo $this->unit->display('data[limit][limit_unit]',@$this->element->limit_unit,true); ?>
							</td>
						</tr>
<?php
}
if(empty($this->element->limit_type) || $this->element->limit_type == 'price') {
?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'CURRENCY' ); ?>
							</td>
							<td>
								<?php echo @$this->currency->display('data[limit][limit_currency_id]',@$this->element->limit_currency_id); ?>
							</td>
						</tr>
<?php
}
?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[limit][limit_published]" , '',@$this->element->limit_published	); ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="hkc-md-6">
			<div class="hikashop_tile_block">
				<div>
					<div class="hikashop_tile_title"><?php
						echo JText::_('RESTRICTIONS');
					?></div>
					<table class="admintable table">
						<tr>
							<td class="key">
									<?php echo JText::_( 'START_DATE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('calendar', (@$this->element->limit_start?hikashop_getDate(@$this->element->limit_start,'%Y-%m-%d %H:%M'):''), 'data[limit][limit_start]','limit_start',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size'=>'20')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'END_DATE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('calendar', (@$this->element->limit_end?hikashop_getDate(@$this->element->limit_end,'%Y-%m-%d %H:%M'):''), 'data[limit][limit_end]','limit_end',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size'=>'20')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'PRODUCT' ); ?>
							</td>
							<td><?php
		echo $this->nameboxType->display(
			'data[limit][limit_product_id]',
			@$this->element->limit_product_id,
			hikashopNameboxType::NAMEBOX_SINGLE,
			'product',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
							?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'CATEGORY' ); ?>
							</td>
							<td><?php
		echo $this->nameboxType->display(
			'data[limit][limit_category_id]',
			@$this->element->limit_category_id,
			hikashopNameboxType::NAMEBOX_MULTIPLE,
			'category',
			array(
				'delete' => true,
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
							?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_( 'ORDER_STATUS' );// only for orders ?>
							</td>
							<td>
								<?php echo $this->status->display('data[limit][limit_status][]',@$this->element->limit_status,' multiple="multiple" size="5"',false); ?>
							</td>
						</tr>
						<?php  ?>
						<tr>
							<td colspan="2">
								<fieldset class="adminform">
									<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
									<?php
									if(hikashop_level(2)){
										$acltype = hikashop_get('type.acl');
										echo $acltype->display('limit_access',@$this->element->limit_access,'limit');
									}else{
										echo hikashop_getUpgradeLink('business');
									} ?>
								</fieldset>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo @$this->element->limit_id; ?>" />
		<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
		<input type="hidden" name="task" value="apply" />
		<input type="hidden" name="ctrl" value="limit" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
