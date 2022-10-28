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

HTMLHelper::_('behavior.core');

EventbookingHelperJquery::validateForm();

$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$btnPrimary        = $bootstrapHelper->getClassMapping('btn btn-primary');
$btnClass          = $bootstrapHelper->getClassMapping('btn');

Factory::getDocument()->addScriptOptions('eventUrl', $this->eventUrl);
?>
<div id="eb-event-password-page" class="eb-container">
<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_PRIVATE_EVENT_VALIDATION')); ?></h1>
<form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_eventbooking&task=register.check_event_password&Itemid='.$this->Itemid); ?>" autocomplete="off" class="form form-horizontal">
	<p><?php echo Text::_('EB_PRIVATE_EVENT_VALIDATION_MSG'); ?></p>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>" for="password">
			<?php echo  Text::_('EB_PASSWORD') ?><span class="required">*</span>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<input type="text" id="password" name="password" class="input-large validate[required]" value="" />
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlsClass; ?>">
			<input type="button" id="btn-cancel" value="<?php echo Text::_('EB_CANCEL'); ?>" class="<?php echo $btnClass; ?>" onclick="window.history.go(-1);" />
			<input type="submit" value="<?php echo Text::_('EB_CONTINUE'); ?>" class="<?php echo $btnPrimary; ?>" />
		</div>
	</div>
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="event_id" value="<?php echo $this->eventId; ?>" />
</form>
</div>