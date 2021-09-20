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

?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('activate_invoice_feature', Text::_('EB_ACTIVATE_INVOICE_FEATURE'), Text::_('EB_ACTIVATE_INVOICE_FEATURE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_invoice_feature', $config->activate_invoice_feature); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('always_generate_invoice', Text::_('EB_ALWAYS_GENERATE_INVOICE'), Text::_('EB_ALWAYS_GENERATE_INVOICE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('always_generate_invoice', $config->always_generate_invoice); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('generated_invoice_for_paid_registration_only', Text::_('EB_GENERATE_INVOICE_FOR_PAID_REGISTRATION_ONLY'), Text::_('EB_GENERATE_INVOICE_FOR_PAID_REGISTRATION_ONLY_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('generated_invoice_for_paid_registration_only', $config->generated_invoice_for_paid_registration_only); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('send_invoice_to_customer', Text::_('EB_SEND_INVOICE_TO_SUBSCRIBERS'), Text::_('EB_SEND_INVOICE_TO_SUBSCRIBERS_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('send_invoice_to_customer', $config->send_invoice_to_customer); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('send_invoice_to_admin', Text::_('EB_SEND_INVOICE_TO_ADMIN'), Text::_('EB_SEND_INVOICE_TO_ADMIN_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('send_invoice_to_admin', $config->send_invoice_to_admin); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('invoice_start_number', Text::_('EB_INVOICE_START_NUMBER'), Text::_('EB_INVOICE_START_NUMBER_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="invoice_start_number" class="form-control" value="<?php echo $config->invoice_start_number ? $config->invoice_start_number : 1; ?>" size="10" />
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('reset_invoice_number', Text::_('EB_RESET_INVOICE_NUMBER_EVERY_YEAR'), Text::_('EB_RESET_INVOICE_NUMBER_EVERY_YEAR_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('reset_invoice_number', $config->reset_invoice_number); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('invoice_prefix', Text::_('EB_INVOICE_PREFIX'), Text::_('EB_INVOICE_PREFIX_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="invoice_prefix" class="form-control" value="<?php echo $config->get('invoice_prefix', 'IV'); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('invoice_number_length', Text::_('EB_INVOICE_NUMBER_LENGTH'), Text::_('EB_INVOICE_NUMBER_LENGTH_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="invoice_number_length" class="form-control" value="<?php echo $config->get('invoice_number_length', 5); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('invoice_format', Text::_('EB_INVOICE_FORMAT'), Text::_('EB_INVOICE_FORMAT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'invoice_format',  $config->invoice_format , '100%', '550', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('invoice_format_cart', Text::_('EB_INVOICE_FORMAT_CART'), Text::_('EB_INVOICE_FORMAT_CART_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'invoice_format_cart',  $config->invoice_format_cart , '100%', '550', '75', '8' ) ;?>
	</div>
</div>
