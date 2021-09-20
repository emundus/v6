<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('payment_methods', Text::_('EB_PAYMENT_METHODS'), Text::_('EB_PAYMENT_METHODS_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['payment_methods']); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fixed_group_price', Text::_('EB_FIXED_GROUP_PRICE'), Text::_('EB_FIXED_GROUP_PRICE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="number" class="form-control" min="0" step="0.01" name="fixed_group_price" id="fixed_group_price" class="form-control" size="10" value="<?php echo $this->item->fixed_group_price; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('currency_code', Text::_('EB_CURRENCY'), Text::_('EB_CURRENCY_CODE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['currency_code']); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('currency_symbol', Text::_('EB_CURRENCY_SYMBOL'), Text::_('EB_CURRENCY_SYMBOL_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="currency_symbol" size="5" class="form-control" value="<?php echo $this->item->currency_symbol; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('paypal_email', Text::_('EB_PAYPAL_EMAIL'), Text::_('EB_PAYPAL_EMAIL_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="email" name="paypal_email" class="form-control" size="50" value="<?php echo $this->item->paypal_email ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo Text::_('EB_API_LOGIN') ; ?></div>
	<div class="controls">
		<input type="text" name="api_login" value="<?php echo $this->item->api_login; ?>" class="form-control" size="30" />
	</div>
</div>
<div class="control-group">
	<div class="control-label"><?php echo Text::_('EB_TRANSACTION_KEY') ; ?></div>
	<div class="controls">
		<input type="text" name="transaction_key" value="<?php echo $this->item->transaction_key; ?>" class="form-control" size="30" />
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('from_name', Text::_('EB_FROM_NAME'), Text::_('EB_EVENT_FROM_NAME_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="from_name" class="form-control" size="70" value="<?php echo $this->item->from_name ; ?>" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('from_email', Text::_('EB_FROM_EMAIL'), Text::_('EB_EVENT_FROM_EMAIL_EXPLAIN')); ?>
    </div>
    <div class="controls">
        <input type="text" name="from_email" class="form-control" size="70" value="<?php echo $this->item->from_email; ?>" />
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('notification_emails', Text::_('EB_NOTIFICATION_EMAILS'), Text::_('EB_NOTIFICATION_EMAIL_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="notification_emails" class="form-control" size="70" value="<?php echo $this->item->notification_emails ; ?>" />
	</div>
</div>
<?php
if ($this->config->activate_invoice_feature)
{
?>
    <div class="control-group">
        <div class="control-label">
            <?php echo  Text::_('EB_INVOICE_FORMAT'); ?>
        </div>
        <div class="controls">
            <?php echo $editor->display( 'invoice_format',  $this->item->invoice_format , '100%', '180', '90', '6' );?>
        </div>
    </div>
<?php
}

