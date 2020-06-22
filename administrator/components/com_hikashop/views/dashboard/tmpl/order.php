<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div>
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
				</th>
				<th class="title">
					<?php echo JText::_('CUSTOMER'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('ORDER_STATUS'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKASHOP_TOTAL'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				if(!empty($this->widget->elements)){
					for($i = 0,$a = count($this->widget->elements);$i<$a;$i++){
						$row =& $this->widget->elements[$i];
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="hk_center">
						<a href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$row->order_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('dashboard')))); ?>">
							<?php echo $row->order_number; ?>
						</a>
					</td>
					<td>
						<?php
						if(isset($row->user_cms_id)) {
							$customer = JFactory::getUser($row->user_cms_id);
							if(!empty($customer->username) && !empty($customer->name))
								echo $customer->name.' ( '.$customer->username.' )</a><br/>';
						}
						 $url = hikashop_completeLink('user&task=edit&cid[]='.$row->user_id);
						 echo $row->user_email.' <a href="'.$url.'"><i class="fas fa-pen"></i></a>';
						 ?>
					</td>
					<td class="hk_center">
						<?php echo hikashop_orderStatus($row->order_status); ?>
					</td>
					<td class="hk_center">
						<?php
							if(isset($this->widget->widget_params->orders_total_calculation) && $this->widget->widget_params->orders_total_calculation=='exclude_fees'){
								echo $this->currencyHelper->format(($row->order_full_price-$row->order_shipping_price),$row->order_currency_id);
							}else{
								echo $this->currencyHelper->format($row->order_full_price,$row->order_currency_id);
							}
						?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			}

			?>
		</tbody>
	</table>
</div>
