<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('intro_text', Text::_('EB_INTRO_TEXT'), Text::_('EB_INTRO_TEXT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'intro_text',  $this->message->intro_text , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message', Text::_('EB_REGISTRATION_FORM_MESSAGE'), Text::_('EB_REGISTRATION_FORM_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_form_message',  $this->message->registration_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message_group', Text::_('EB_REGISTRATION_FORM_MESSAGE_GROUP'), Text::_('EB_REGISTRATION_FORM_MESSAGE_GROUP_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_form_message_group',  $this->message->registration_form_message_group , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('number_members_form_message', Text::_('EB_NUMBER_OF_MEMBERS_FORM_MESSAGE'), Text::_('EB_NUMBER_OF_MEMBERS_FORM_MESSAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'number_members_form_message',  $this->message->number_members_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('member_information_form_message', Text::_('EB_MEMBER_INFORMATION_FORM_MESSAGE'), Text::_('EB_MEMBER_INFORMATION_FORM_MESSAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'member_information_form_message',  $this->message->member_information_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('thanks_message', Text::_('EB_THANK_YOU_MESSAGE'), Text::_('EB_THANK_YOU_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'thanks_message',  $this->message->thanks_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('thanks_message_offline', Text::_('EB_THANK_YOU_MESSAGE_OFFLINE'), Text::_('EB_THANK_YOU_MESSAGE_OFFLINE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'thanks_message_offline',  $this->message->thanks_message_offline , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<?php
	foreach ($this->extraOfflinePlugins as $offlinePaymentPlugin)
	{
		$name   = $offlinePaymentPlugin->name;
		$title  = $offlinePaymentPlugin->title;
		$prefix = str_replace('os_offline', '', $name);
	?>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('Thank you message (' . $title . ')'); ?>
				<p>
					<strong>This message will be displayed on the thank you page after users complete an offline
						payment</strong>
				</p>
			</div>
			<div class="controls">
				<?php echo $editor->display('thanks_message_offline' . $prefix, $this->message->{'thanks_message_offline' . $prefix}, '100%', '250', '75', '8'); ?>
			</div>
		</div>
    <?php
    }
?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('cancel_message', Text::_('EB_CANCEL_MESSAGE'), Text::_('EB_CANCEL_MESSAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'cancel_message',  $this->message->cancel_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('mass_mail_template', Text::_('EB_MASS_MAIL_TEMPLATE'), Text::_('EB_MASS_MAIL_TEMPLATE_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo $editor->display( 'mass_mail_template',  $this->message->mass_mail_template , '100%', '250', '75', '8' ) ;?>
    </div>
</div>
