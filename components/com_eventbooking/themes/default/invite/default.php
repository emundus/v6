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

EventbookingHelperJquery::validateForm();
EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-invite-default.min.js');

$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$btnPrimary        = $bootstrapHelper->getClassMapping('btn btn-primary');

/* @var EventbookingViewInviteHtml $this*/
?>
<div id="eb-invite-friend-page" class="eb-container">
<h1 class="eb-page-heading"><?php echo Text::_('EB_REGISTRATION_INVITE'); ?></h1>
<div class="eb-message">
	<?php echo str_replace('[EVENT_TITLE]', $this->event->title, $this->inviteMessage) ; ?>
</div>
<div class="clearfix"></div>
<form name="adminForm" id="adminForm" method="post" action="<?php echo Route::_('index.php?Itemid=' . $this->Itemid . '&tmpl=component'); ?>" class="form form-horizontal">
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_NAME'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<input type="text" name="name" value="<?php echo $this->escape($this->name); ?>" class="validate[required]" size="50" />
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_FRIEND_NAMES'); ?>
			<br />
			<small><?php echo Text::_('EB_ONE_NAME_ONE_LINE'); ?></small>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<textarea rows="5" cols="50" name="friend_names" class="validate[required]"><?php echo $this->friendNames; ?></textarea>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_FRIEND_EMAILS'); ?>
			<br />
			<small><?php echo Text::_('EB_ONE_EMAIL_ONE_LINE'); ?></small>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<textarea rows="5" cols="50" name="friend_emails" class="validate[required]"><?php echo $this->friendEmails;?></textarea>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('EB_MESSAGE'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<textarea rows="10" cols="80" name="message" class="form-control"><?php echo $this->mesage; ?></textarea>
		</div>
	</div>
	<?php
	if ($this->showCaptcha)
	{
		if ($this->captchaPlugin == 'recaptcha_invisible')
		{
			$style = ' style="display:none;"';
		}
		else
		{
			$style = '';
		}
	?>
		<div class="<?php echo $controlGroupClass; ?>" <?php echo $style; ?>>
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('EB_CAPTCHA'); ?><span class="required">*</span>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->captcha; ?>
			</div>
		</div>
	<?php
	}
	?>
	<div class="form-actions">
		<input type="submit" value="<?php echo Text::_('EB_INVITE'); ?>" class="<?php echo $btnPrimary; ?>" />
	</div>
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="task" value="event.send_invite" />
	<input type="hidden" name="event_id" value="<?php echo $this->event->id; ?>" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>
</div>