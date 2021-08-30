<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (EventbookingHelper::isJoomla4())
{
	EventbookingHelperJquery::colorbox('a.eb-modal');
	$popup = '';
}
else
{
	HTMLHelper::_('behavior.modal', 'a.eb-modal');
	$popup = 'class="eb-modal" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"';
}

if ($this->config->use_https)
{
	$url = Route::_('index.php?option=com_eventbooking&Itemid='.$this->Itemid, false, 1);
}
else
{
	$url = Route::_('index.php?option=com_eventbooking&Itemid='.$this->Itemid, false);
}
$btnClass = $this->bootstrapHelper->getClassMapping('btn');
$span12  = $this->bootstrapHelper->getClassMapping('span12');
?>
<div id="eb-cart-page" class="eb-container eb-cart-content">
<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_ADDED_EVENTS')); ?></h1>
<?php
if (count($this->items))
{
?>
	<form method="post" name="adminForm" id="adminForm" action="<?php echo $url; ?>">
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
			$item = $this->items[$i];

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

			if ($this->config->show_discounted_price)
			{
				$item->rate = $item->discounted_rate;
			}

			$total += $item->quantity*$item->rate ;
			$url = Route::_('index.php?option=com_eventbooking&view=event&id='.$item->id.'&tmpl=component&Itemid='.$this->Itemid);
		?>
		<div class="well clearfix">
			<div class="row-fluid">
				<div class="<?php echo $span12; ?> eb-mobile-event-title">
					<a href="<?php echo $url; ?>" <?php echo $popup; ?>><?php echo $item->title; ?></a>
				</div>
				<?php
					if ($this->config->show_event_date)
					{
					?>
						<div class="<?php echo $span12; ?> eb-mobile-event-date">
							<strong><?php echo Text::_('EB_EVENT_DATE'); ?>: </strong>
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
						</div>
					<?php
					}
				?>
				<div class="<?php echo $span12; ?> eb-mobile-event-price">
					<strong><?php echo Text::_('EB_PRICE'); ?> :</strong>
					<?php echo EventbookingHelper::formatCurrency($item->rate, $this->config); ?>
				</div>
				<div class="<?php echo $span12; ?> eb-mobile-quantity">
					<strong><?php echo Text::_('EB_QUANTITY'); ?> :</strong>
					<div class="btn-wrapper input-append <?php echo $inputGroupClass; ?>">
						<input type="number"<?php if ($item->max_group_number > 0) echo ' max="' . $item->max_group_number . '"'; ?> class="form-control input-mini quantity_box" size="3" value="<?php echo $item->quantity ; ?>" name="quantity[]" <?php echo $readOnly ; ?> onchange="updateCart();" />
						<button onclick="javascript:removeItem(<?php echo $item->id; ?>);" id="update_cart" class="<?php echo $btnClass; ?> btn-default" type="button">
							<i class="fa fa-times-circle"></i>
						</button>
						<input type="hidden" name="event_id[]" value="<?php echo $item->id; ?>" />
					</div>
				</div>
				<div class="<?php echo $span12; ?> eb-mobile-sub-total">
					<strong><?php echo Text::_('EB_SUB_TOTAL'); ?> :</strong>
					<?php echo EventbookingHelper::formatCurrency($item->rate*$item->quantity, $this->config); ?>
				</div>
			</div>
		</div>
		<?php
		}
		?>
		<div style="text-align: center" class="totals clearfix">
			<div>
				<?php echo Text::_('EB_TOTAL') .' '. EventbookingHelper::formatCurrency($total, $this->config); ?>
			</div>
		</div>
		<div style="text-align: center;" class="bottom control-group">
			<div>
				<button onclick="continueShopping('<?php echo $this->continueUrl; ?>');" id="add_more_item" class="<?php echo $btnClass; ?> btn-success" type="button">
					<i class="icon-new"></i> <?php echo Text::_('EB_ADD_MORE_EVENTS'); ?>
				</button>
				<button onclick="javascript:checkOut();" id="check_out" class="<?php echo $btnClass; ?> btn-primary" type="button">
					<i class="fa fa-mail-forward"></i> <?php echo Text::_('EB_CHECKOUT'); ?>
				</button>
			</div>
		</div>
		<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
		<input type="hidden" name="category_id" value="<?php echo $this->categoryId; ?>" />
		<input type="hidden" name="option" value="com_eventbooking" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" value="" />
	</form>
<?php
	Factory::getDocument()->addScriptDeclaration($this->jsString)
		->addScriptDeclaration('var EB_INVALID_QUANTITY = "'.Text::_('EB_INVALID_QUANTITY', true).')";')
		->addScriptDeclaration('var EB_REMOVE_CONFIRM = "'.Text::_('EB_REMOVE_CONFIRM', true).')";');
}
else
{
?>
	<p class="eb-message"><?php echo Text::_('EB_NO_EVENTS_IN_CART'); ?></p>
<?php
}
?>
</div>