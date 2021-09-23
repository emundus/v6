<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

if ($this->config->use_https)
{
	$checkoutUrl = Route::_('index.php?option=com_eventbooking&task=view_checkout&Itemid=' . $this->Itemid . EventbookingHelper::addTimeToUrl(), false, 1);
}
else
{
	$checkoutUrl = Route::_('index.php?option=com_eventbooking&task=view_checkout&Itemid=' . $this->Itemid . EventbookingHelper::addTimeToUrl(), false, 0);
}

$btnClass = $this->bootstrapHelper->getClassMapping('btn');
?>
<script src="<?php echo Uri::root(true); ?>/media/com_eventbooking/assets/js/cartpopup.min.js"></script>
<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_ADDED_EVENTS')); ?></h1>
<div id="eb-mini-cart-page" class="eb-container eb-cart-content">
<?php
if (count($this->items))
{
?>
	<form method="post" name="adminForm" id="adminForm" action="index.php">
		<table class="<?php echo $this->bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-condensed">
			<thead>
				<tr>
					<th class="col_event">
						<?php echo Text::_('EB_EVENT'); ?>
					</th>
					<?php
						if ($this->config->show_event_date)
						{
						?>
							<th class="col_event_date text-center">
								<?php echo Text::_('EB_EVENT_DATE'); ?>
							</th>
						<?php
						}
					?>
					<th class="col_price text-right">
						<?php echo Text::_('EB_PRICE'); ?>
					</th>
					<th class="col_quantity text-center">
						<?php echo Text::_('EB_QUANTITY'); ?>
					</th>
					<th class="col_subtotal text-right">
						<?php echo Text::_('EB_SUB_TOTAL'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$total = 0 ;

				if (EventbookingHelper::isJoomla4())
                {
                    $inputGroupClass = ' input-group';
                }
                else
                {
                    $inputGroupClass = '';
                }

				for ($i = 0 , $n = count($this->items) ; $i < $n; $i++)
				{
					$item = $this->items[$i] ;

					if ($item->prevent_duplicate_registration === '')
					{
						$preventDuplicateRegistration = $this->config->prevent_duplicate_registration;
					}
					else
					{
						$preventDuplicateRegistration = $item->prevent_duplicate_registration;
					}

					if ($preventDuplicateRegistration)
					{
						$readOnly = ' readonly="readonly" ' ;
					}
					else
					{
						$readOnly = '' ;
					}

					$rate  = $this->config->show_discounted_price ? $item->discounted_rate : $item->rate;
					$total += $item->quantity * $rate;
					$url   = Route::_('index.php?option=com_eventbooking&view=event&id=' . $item->id . '&tmpl=component&Itemid=' . $this->Itemid);
				?>
					<tr>
						<td class="col_event">
							<a href="<?php echo $url; ?>"><?php echo $item->title; ?></a>
						</td>
						<?php
							if ($this->config->show_event_date) {
							?>
								<td class="col_event_date text-center">
									<?php
										if ($item->event_date == EB_TBC_DATE)
										{
											echo Text::_('EB_TBC');
										}
										else
										{
											echo HTMLHelper::_('date', $item->event_date, $this->config->event_date_format, null);
										}
									?>
								</td>
							<?php
							}
						?>
						<td class="col_price text-right">
							<?php echo EventbookingHelper::formatCurrency($rate, $this->config); ?>
						</td>
						<td class="col_quantity">
							<div class="btn-wrapper input-append<?php echo $inputGroupClass; ?>">
								<input id="quantity" type="number"<?php if ($item->max_group_number > 0) echo ' max="' . $item->max_group_number . '"'; ?> class="form-control input-mini quantity_box" size="3" value="<?php echo $item->quantity ; ?>" name="quantity[]" <?php echo $readOnly ; ?> onchange="updateCart(<?php echo (int) $this->Itemid; ?>);" />
								<button onclick="javascript:removeCart(<?php echo $item->id; ?>, <?php echo (int) $this->Itemid; ?>);" class="<?php echo $btnClass; ?> btn-default" type="button">
									<i class="fa fa-times-circle"></i>
								</button>
								<input type="hidden" name="event_id[]" value="<?php echo $item->id; ?>" />
							</div>
						</td>
						<td class="col_subtotal text-right">
							<?php echo EventbookingHelper::formatCurrency($rate*$item->quantity, $this->config); ?>
						</td>
					</tr>
				<?php
				}
				if ($this->config->show_event_date)
				{
					$cols = 5 ;
				}
				else
				{
					$cols = 4 ;
				}
				?>
				<tr>
					<td class="col_price" colspan="<?php echo $cols; ?>">
						<span class="total_amount"><?php echo Text::_('EB_TOTAL'); ?>:  </span>
						<?php echo EventBookingHelper::formatCurrency($total, $this->config); ?>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="option" value="com_eventbooking" />
		<input type="hidden" name="task" value="cart.update_cart" />
		<div style="text-align: center;" class="form-actions">
			<div class="controls">
				<button onclick="javascript:closeCartPopup();" id="add_more_item" class="<?php echo $btnClass; ?> btn-success" type="button">
					<i class="icon-new"></i> <?php echo Text::_('EB_ADD_MORE_EVENTS'); ?>
				</button>
				<button onclick="javascript:checkOut('<?php echo $checkoutUrl; ?>');" id="check_out" class="<?php echo $btnClass; ?> btn-primary" type="button">
					<i class="fa fa-mail-forward"></i> <?php echo Text::_('EB_CHECKOUT'); ?>
				</button>
			</div>
		</div>
	</form>
<?php
}
else
{
?>
	<p class="message"><?php echo Text::_('EB_NO_EVENTS_IN_CART'); ?></p>
<?php
}
?>
</div>
<script type="text/javascript">
	<?php echo $this->jsString ; ?>
    var EB_INVALID_QUANTITY = '<?php echo Text::_('EB_INVALID_QUANTITY', true); ?>';
</script>