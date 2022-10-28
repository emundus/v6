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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

EventbookingHelper::normalizeNullDateTimeData($this->item, ['publish_up', 'publish_down', 'cancel_before_date', 'registrant_edit_close_date']);
?>
<fieldset class="form-horizontal options-form">
	<legend class="adminform"><?php echo Text::_('EB_MISC'); ?></legend>
    <?php
    if ($this->config->get('bes_show_event_password', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
                <span class="editlinktip hasTip" title="<?php echo Text::_('EB_EVENT_PASSWORD'); ?>::<?php echo Text::_('EB_EVENT_PASSWORD_EXPLAIN'); ?>"><?php echo Text::_('EB_EVENT_PASSWORD'); ?></span>
            </div>
            <div class="controls">
                <input type="text" name="event_password" id="event_password" class="input-small form-control" size="10" value="<?php echo $this->item->event_password; ?>"/>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_access', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
                <span class="editlinktip hasTip" title="<?php echo Text::_('EB_ACCESS'); ?>::<?php echo Text::_('EB_ACCESS_EXPLAIN'); ?>"><?php echo Text::_('EB_ACCESS'); ?></span>
            </div>
            <div class="controls">
			    <?php echo $this->lists['access']; ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_registration_access', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
                <span class="editlinktip hasTip" title="<?php echo Text::_('EB_REGISTRATION_ACCESS'); ?>::<?php echo Text::_('EB_REGISTRATION_ACCESS_EXPLAIN'); ?>"><?php echo Text::_('EB_REGISTRATION_ACCESS'); ?></span>
            </div>
            <div class="controls">
			    <?php echo $this->lists['registration_access']; ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_featured', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_FEATURED'); ?>
            </div>
            <div class="controls">
			    <?php echo EventbookingHelperHtml::getBooleanInput('featured', $this->item->featured); ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_hidden', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo EventbookingHelperHtml::getFieldLabel('hidden', Text::_('EB_HIDDEN'), Text::_('EB_HIDDEN_EXPLAIN')); ?>
            </div>
            <div class="controls">
			    <?php echo EventbookingHelperHtml::getBooleanInput('hidden', $this->item->hidden); ?>
            </div>
        </div>
    <?php
    }

	if (Multilanguage::isEnabled())
	{
	?>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('EB_LANGUAGE'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['language']; ?>
			</div>
		</div>
	<?php
	}

    if ($this->config->get('bes_show_published', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_PUBLISHED'); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['published']; ?>
            </div>
        </div>
    <?php
    }
	?>

	<div class="control-group">
		<div class="control-label"><?php echo Text::_('EB_CREATED_BY'); ?></div>
		<div class="controls">
			<?php echo EventbookingHelper::getUserInput($this->item->created_by, 'created_by', 1); ?>
		</div>
	</div>
    <?php
    if ($this->config->get('bes_show_min_group_number', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
                <span class="editlinktip hasTip" title="<?php echo Text::_('EB_MIN_NUMBER_REGISTRANTS'); ?>::<?php echo Text::_('EB_MIN_NUMBER_REGISTRANTS_EXPLAIN'); ?>"><?php echo Text::_('EB_MIN_NUMBER_REGISTRANTS'); ?></span>
            </div>
            <div class="controls">
                <input type="number" name="min_group_number" id="min_group_number" class="input-mini form-control" size="10" value="<?php echo $this->item->min_group_number; ?>"/>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_max_group_number', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
                <span class="editlinktip hasTip" title="<?php echo Text::_('EB_MAX_NUMBER_REGISTRANTS'); ?>::<?php echo Text::_('EB_MAX_NUMBER_REGISTRANTS_EXPLAIN'); ?>"><?php echo Text::_('EB_MAX_NUMBER_REGISTRANT_GROUP'); ?></span>
            </div>
            <div class="controls">
                <input type="number" name="max_group_number" id="max_group_number" class="input-mini form-control" size="10" value="<?php echo $this->item->max_group_number; ?>"/>
            </div>
        </div>
    <?php
    }

    if (!$this->config->multiple_booking)
    {
	    if ($this->config->get('bes_show_free_event_registration_status', 1))
        {
        ?>
            <div class="control-group">
                <div class="control-label">
			        <?php echo EventbookingHelperHtml::getFieldLabel('free_event_registration_status', Text::_('EB_FREE_EVENT_REGISTRATION_STATUS'), Text::_('EB_FREE_EVENT_REGISTRATION_STATUS_EXPLAIN')); ?>
                </div>
                <div class="controls">
			        <?php echo $this->lists['free_event_registration_status']; ?>
                </div>
            </div>
        <?php
        }

	    if ($this->config->get('bes_show_members_discount_apply_for', 1))
        {
        ?>
            <div class="control-group">
                <div class="control-label">
			        <?php echo Text::_('EB_MEMBERS_DISCOUNT_APPLY_FOR'); ?>
                </div>
                <div class="controls">
			        <?php echo $this->lists['members_discount_apply_for']; ?>
                </div>
            </div>
        <?php
        }
    }

    if ($this->config->get('bes_show_enable_coupon', 1))
    {
	?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_ENABLE_COUPON'); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['enable_coupon']; ?>
            </div>
        </div>
	<?php
    }

    if ($this->config->get('bes_show_activate_waiting_list', 1))
    {
	?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_ENABLE_WAITING_LIST'); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['activate_waiting_list']; ?>
            </div>
        </div>
	<?php
    }

    if ($this->config->get('bes_show_collect_member_information', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_COLLECT_MEMBER_INFORMATION'); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['collect_member_information']; ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_prevent_duplicate_registration', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_PREVENT_DUPLICATE'); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['prevent_duplicate_registration']; ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_send_emails', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_SEND_NOTIFICATION_EMAILS'); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['send_emails']; ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->activate_deposit_feature)
    {
	?>
        <div class="control-group">
            <div class="control-label">
                <span class="editlinktip hasTip" title="<?php echo Text::_('EB_DEPOSIT_AMOUNT'); ?>::<?php echo Text::_('EB_DEPOSIT_AMOUNT_EXPLAIN'); ?>"><?php echo Text::_('EB_DEPOSIT_AMOUNT'); ?></span>
            </div>
            <div class="controls">
                <input type="number" name="deposit_amount" id="deposit_amount" class="input-mini form-control" size="5" value="<?php echo $this->item->deposit_amount; ?>"/>&nbsp;&nbsp;<?php echo $this->lists['deposit_type']; ?>
            </div>
        </div>
	<?php
    }

    if ($this->config->get('bes_show_enable_cancel_registration', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_ENABLE_CANCEL'); ?>
            </div>
            <div class="controls">
			    <?php echo EventbookingHelperHtml::getBooleanInput('enable_cancel_registration', $this->item->enable_cancel_registration); ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_cancel_before_date', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_CANCEL_BEFORE_DATE'); ?>
            </div>
            <div class="controls">
			    <?php echo HTMLHelper::_('calendar', $this->item->cancel_before_date, 'cancel_before_date', 'cancel_before_date', $this->datePickerFormat . ' %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_publish_up', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_PUBLISH_UP'); ?>
            </div>
            <div class="controls">
			    <?php echo HTMLHelper::_('calendar', $this->item->publish_up, 'publish_up', 'publish_up', $this->datePickerFormat . ' %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_publish_down', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_PUBLISH_DOWN'); ?>
            </div>
            <div class="controls">
			    <?php echo HTMLHelper::_('calendar', $this->item->publish_down, 'publish_down', 'publish_down', $this->datePickerFormat . ' %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_registrant_edit_close_date', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo EventbookingHelperHtml::getFieldLabel('registrant_edit_close_date', Text::_('EB_REGISTRANT_EDIT_CLOSE_DATE'), Text::_('EB_REGISTRANT_EDIT_CLOSE_DATE_EXPLAIN')); ?>
            </div>
            <div class="controls">
			    <?php echo HTMLHelper::_('calendar', $this->item->registrant_edit_close_date, 'registrant_edit_close_date', 'registrant_edit_close_date', $this->datePickerFormat . ' %H:%M', ['class' => 'input-medium', 'showTime' => true]); ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_registration_complete_url', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo  Text::_('EB_REGISTRATION_COMPLETE_URL'); ?>
            </div>
            <div class="controls">
                <input type="url" class="input-large form-control" name="registration_complete_url" value="<?php echo $this->item->registration_complete_url; ?>" size="50" />
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_offline_payment_registration_complete_url', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo  Text::_('EB_OFFLINE_PAYMENT_REGISTRATION_COMPLETE_URL'); ?>
            </div>
            <div class="controls">
                <input type="url" class="input-large form-control" name="offline_payment_registration_complete_url" value="<?php echo $this->item->offline_payment_registration_complete_url; ?>" size="50" />
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_enable_terms_and_conditions', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_ENABLE_TERMS_CONDITIONS'); ?>
            </div>
            <div class="controls">
			    <?php echo $this->lists['enable_terms_and_conditions']; ?>
            </div>
        </div>
    <?php
    }

    if ($this->config->get('bes_show_article_id', 1))
    {
    ?>
        <div class="control-group">
            <div class="control-label">
			    <?php echo Text::_('EB_TERMS_CONDITIONS'); ?>
            </div>
            <div class="controls">
			    <?php echo EventbookingHelper::getArticleInput($this->item->article_id); ?>
            </div>
        </div>
    <?php
    }
	?>
</fieldset>
