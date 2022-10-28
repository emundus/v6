<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Language\Text;

$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$inputPrependClass = $bootstrapHelper->getClassMapping('input-prepend');
$inputAppendClass  = $bootstrapHelper->getClassMapping('input-append');
$addOnClass        = $bootstrapHelper->getClassMapping('add-on');
?>
<h3 class="eb-heading"><?php echo $this->escape(Text::_('EB_PAYMENT_INFORMATION')); ?></h3>
<div class="<?php echo $controlGroupClass;  ?>">
	<label class="<?php echo $controlLabelClass; ?>">
		<?php echo Text::_('EB_AMOUNT'); ?>
	</label>
	<div class="<?php echo $controlsClass; ?>">
		<?php
		if ($this->config->currency_position == 0)
		{
		?>
			<div class="<?php echo $inputPrependClass; ?> inline-display">
				<span class="<?php echo $addOnClass; ?>"><?php echo $this->currencySymol;?></span>
				<input id="amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->fees['amount'], $this->config); ?>" />
			</div>
		<?php
		}
		else
		{
		?>
			<div class="<?php echo $inputAppendClass; ?> inline-display">
				<input id="amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->fees['amount'], $this->config); ?>" />
				<span class="<?php echo $addOnClass; ?>"><?php echo $this->currencySymol;?></span>
			</div>
		<?php
		}
		?>
	</div>
</div>
<?php
	if ($this->showPaymentFee)
	{
	?>
	<div class="<?php echo $controlGroupClass;  ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_PAYMENT_FEE'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php
			if ($this->config->currency_position == 0)
			{
			?>
				<div class="<?php echo $inputPrependClass; ?>">
					<span class="<?php echo $addOnClass; ?>"><?php echo $this->currencySymol;?></span>
					<input id="payment_processing_fee" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->fees['payment_processing_fee'], $this->config); ?>" />
				</div>
			<?php
			}
			else
			{
			?>
				<div class="<?php echo $inputAppendClass; ?>">
					<input id="payment_processing_fee" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->fees['payment_processing_fee'], $this->config); ?>" />
					<span class="<?php echo $addOnClass; ?>"><?php echo $this->currencySymol;?></span>
				</div>
			<?php
			}
			?>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass;  ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_GROSS_AMOUNT'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php
			if ($this->config->currency_position == 0)
			{
			?>
				<div class="<?php echo $inputPrependClass; ?> inline-display">
					<span class="<?php echo $addOnClass; ?>"><?php echo $this->currencySymol;?></span>
					<input id="gross_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->fees['gross_amount'], $this->config); ?>" />
				</div>
			<?php
			}
			else
			{
			?>
				<div class="<?php echo $inputAppendClass; ?> inline-display">
					<input id="gross_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->fees['gross_amount'], $this->config); ?>" />
					<span class="<?php echo $addOnClass; ?>"><?php echo $this->currencySymol;?></span>
				</div>
			<?php
			}
			?>
		</div>
	</div>
<?php
}
