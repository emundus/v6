<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_group_billing_records', Text::_('EB_EXPORT_GROUP_BILLING_RECORDS')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_group_billing_records', $config->get('export_group_billing_records', $config->get('include_group_billing_in_registrants', 1))); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_group_member_records', Text::_('EB_EXPORT_GROUP_MEMBERS_RECORDS')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_group_member_records', $config->get('export_group_member_records', $config->get('include_group_members_in_registrants', 0))); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_event_date', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_EVENT_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_event_date', $config->get('export_event_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_event_end_date', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_EVENT_END_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_event_end_date', $config->get('export_event_end_date', 0)); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_category', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_CATEGORY'))); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_category', $config->get('export_category', 0)); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_user_id', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_USER_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_user_id', $config->get('export_user_id', 1)); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_username', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_USERNAME'))); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_username', $config->get('export_username', 0)); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_number_registrants', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_NUMBER_REGISTRANTS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_number_registrants', $config->get('export_number_registrants', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_amount', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_amount', $config->get('export_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_discount_amount', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_DISCOUNT_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_discount_amount', $config->get('export_discount_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_late_fee', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_LATE_FEE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_late_fee', $config->get('export_late_fee', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_tax_amount', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_TAX'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_tax_amount', $config->get('export_tax_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_gross_amount', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_GROSS_AMOUNT'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_gross_amount', $config->get('export_gross_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
        <?php echo EventbookingHelperHtml::getFieldLabel('export_registration_date', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_REGISTRATION_DATE'))); ?>
    </div>
    <div class="controls">
        <?php echo EventbookingHelperHtml::getBooleanInput('export_registration_date', $config->get('export_registration_date', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_payment_method', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_PAYMENT_METHOD'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_payment_method', $config->get('export_payment_method', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_transaction_id', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_TRANSACTION_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_transaction_id', $config->get('export_transaction_id', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_payment_status', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_PAYMENT_STATUS'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_payment_status', $config->get('export_payment_status', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_payment_date', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_PAYMENT_DATE'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_payment_date', $config->get('export_payment_date')); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_deposit_amount', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_DEPOSIT_AMOUNT'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_deposit_amount', $config->get('export_deposit_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_due_amount', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_DUE_AMOUNT'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_due_amount', $config->get('export_due_amount', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_deposit_payment_transaction_id', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_DEPOSIT_PAYMENT_TRANSACTION_ID'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_deposit_payment_transaction_id', $config->get('export_deposit_payment_transaction_id', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_checked_in', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_CHECKED_IN'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_checked_in', $config->get('export_checked_in', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_checked_in_at', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_CHECKED_IN_TIME'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_checked_in_at', $config->get('export_checked_in_at', 1)); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('export_checked_out_at', Text::sprintf('EB_EXPORT_FIELD', Text::_('EB_CHECKED_OUT_TIME'))); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('export_checked_out_at', $config->get('export_checked_out_at', 1)); ?>
    </div>
</div>