<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');

if (!EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

Factory::getDocument()->addScript(Uri::root(true) . '/media/com_eventbooking/js/admin-massmail-default.min.js');

ToolbarHelper::title( Text::_( 'EB_MASS_MAIL' ), 'massemail.png' );
ToolbarHelper::custom('send','envelope','envelope', Text::_('EB_SEND_MAILS'), false);
ToolbarHelper::cancel('cancel');

$editor = Editor::getInstance(Factory::getApplication()->get('editor', 'none'));

Text::script('EB_CHOOSE_EVENT', true);
?>
<form action="index.php?option=com_eventbooking&view=massmail" method="post" name="adminForm" id="adminForm" class="form form-horizontal" enctype="multipart/form-data">
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_EVENT'); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['event_id']) ; ?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_REGISTRANT_STATUS'); ?>
        </div>
        <div class="controls">
			<?php echo $this->lists['published'] ; ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_SEND_TO_GROUP_BILLING'); ?>
        </div>
        <div class="controls">
	        <?php echo EventbookingHelperHtml::getBooleanInput('send_to_group_billing', $this->input->getInt('send_to_group_billing', 1)); ?>
        </div>
    </div>

    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_SEND_TO_GROUP_MEMBERS'); ?>
        </div>
        <div class="controls">
	        <?php echo EventbookingHelperHtml::getBooleanInput('send_to_group_members', $this->input->getInt('send_to_group_members', 1)); ?>
        </div>
    </div>

    <?php
        if ($this->config->activate_checkin_registrants)
        {
        ?>
            <div class="control-group">
                <div class="control-label">
			        <?php echo Text::_('EB_ONLY_SEND_TO_CHECKED_IN_REGISTRANTS'); ?>
                </div>
                <div class="controls">
			        <?php echo EventbookingHelperHtml::getBooleanInput('only_send_to_checked_in_registrants', $this->input->getInt('only_send_to_checked_in_registrants', 0)); ?>
                </div>
            </div>
        <?php
        }
    ?>

    <div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_ATTACHMENT'); ?>
		</div>
		<div class="controls">
			<input type="file" name="attachment" value="" size="70" class="form-control input-xxlarge" />
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo Text::_('EB_BCC_EMAIL'); ?>
        </div>
        <div class="controls">
            <input type="text" name="bcc_email" value="" size="70" class="form-control input-xxlarge" />
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_EMAIL_SUBJECT'); ?>
		</div>
		<div class="controls">
			<input type="text" name="subject" value="" size="70" class="form-control input-xxlarge" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_EMAIL_MESSAGE'); ?>
			<p class="eb-available-tags">
				<?php echo Text::_('EB_AVAILABLE_TAGS'); ?>: <strong>[FIRST_NAME], [LAST_NAME], [EVENT_TITLE], [EVENT_DATE], [SHORT_DESCRIPTION], [LOCATION_NAME], [LOCATION_NAME_ADDRESS]</strong>
			</p>
		</div>
		<div class="controls">
			<?php echo $editor->display('description', $this->message->mass_mail_template, '100%', '250', '75', '10'); ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="task" value="" />
</form>