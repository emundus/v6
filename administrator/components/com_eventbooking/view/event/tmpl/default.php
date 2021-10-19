<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');
HTMLHelper::_('jquery.framework');
HTMLHelper::_('bootstrap.tooltip');


if(EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('script', 'system/showon.js', array('version' => 'auto', 'relative' => true));

	$tabApiPrefix = 'uitab.';
}
else
{
	HTMLHelper::_('formbehavior.chosen', '.advancedSelect', null, array('placeholder_text_multiple' => Text::_('EB_SELECT_CATEGORIES')));
	HTMLHelper::_('behavior.modal');
	HTMLHelper::_('behavior.tabstate');
	HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));

	$tabApiPrefix = 'bootstrap.';
}

Factory::getDocument()->addScript(Uri::root(true).'/media/com_eventbooking/js/admin-event-default.min.js');

$translatable    = Multilanguage::isEnabled() && count($this->languages);
$editor          = Editor::getInstance(Factory::getApplication()->get('editor'));
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$span8Class      = $bootstrapHelper->getClassMapping('span8');
$span4Class      = $bootstrapHelper->getClassMapping('span4');

$languageKeys = [
	'EB_PLEASE_ENTER_TITLE',
	'EB_CHOOSE_CATEGORY',
    'EB_ENTER_EVENT_DATE',
    'EB_ENTER_RECURRING_INTERVAL',
    'EB_CHOOSE_ONE_DAY',
    'EB_ENTER_DAY_IN_MONTH',
    'EB_ENTER_DAY_IN_MONTH',
    'EB_ENTER_RECURRING_ENDING_SETTINGS',
    'EB_NO_ROW_TO_DELETE',
];

EventbookingHelperHtml::addJSStrings($languageKeys);
?>
<form action="index.php?option=com_eventbooking&view=event" method="post" name="adminForm" id="adminForm"
      class="form form-horizontal" enctype="multipart/form-data">
	<?php echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'event', array('active' => 'basic-information-page')); ?>
	<?php echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'basic-information-page', Text::_('EB_BASIC_INFORMATION', true)); ?>
	<div class="<?php echo $rowFluidClass; ?>">
		<div class="<?php echo $span8Class; ?>">
			<?php echo $this->loadTemplate('general', array('editor' => $editor)); ?>
		</div>
		<div class="<?php echo $span4Class; ?>">
			<?php
            if ($this->config->get('bes_show_group_registration_rates', 1))
            {
	            echo $this->loadTemplate('group_rates');
            }

			echo $this->loadTemplate('misc');

			if ($this->config->activate_recurring_event && (!$this->item->id || $this->item->event_type == 1))
			{
				echo $this->loadTemplate('recurring_settings');
			}
			?>
			<fieldset class="form-horizontal options-form">
				<legend class="adminform"><?php echo Text::_('EB_META_DATA'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('EB_PAGE_TITLE'); ?>
					</div>
					<div class="controls">
						<input class="input-large form-control" type="text" name="page_title" id="page_title" size="" maxlength="250" value="<?php echo $this->item->page_title; ?>"/>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('EB_PAGE_HEADING'); ?>
					</div>
					<div class="controls">
						<input class="input-large form-control" type="text" name="page_heading" id="page_heading" size="" maxlength="250" value="<?php echo $this->item->page_heading; ?>"/>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('EB_META_KEYWORDS'); ?>
					</div>
					<div class="controls">
						<textarea rows="5" cols="30" class="input-large form-control"
						          name="meta_keywords"><?php echo $this->item->meta_keywords; ?></textarea>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('EB_META_DESCRIPTION'); ?>
					</div>
					<div class="controls">
						<textarea rows="5" cols="30" class="input-large form-control"
						          name="meta_description"><?php echo $this->item->meta_description; ?></textarea>
					</div>
				</div>
			</fieldset>
		</div>
	</div>
	<?php

	echo HTMLHelper::_($tabApiPrefix . 'endTab');

	if ($this->config->get('bes_show_tab_discount_settings', 1))
    {
	    echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'discount-page', Text::_('EB_DISCOUNT_SETTING', true));
	    echo $this->loadTemplate('discount_settings');
	    echo HTMLHelper::_($tabApiPrefix . 'endTab');
    }


	if ($this->config->event_custom_field)
	{
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'extra-information-page', Text::_('EB_EXTRA_INFORMATION', true));
	?>
		<table class="admintable" width="100%">
			<?php
				foreach ($this->form->getFieldset('basic') as $field)
				{
				?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php
				}
			?>
		</table>
	<?php
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}

	if ($this->config->activate_tickets_pdf)
	{
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'tickets-page', Text::_('EB_TICKETS_SETTINGS', true));
		echo $this->loadTemplate('tickets', array('editor' => $editor));
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}

	if ($this->config->activate_certificate_feature)
	{
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'certificate-page', Text::_('EB_CERTIFICATE_SETTINGS', true));
		echo $this->loadTemplate('certificate', array('editor' => $editor));
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}

	if ($this->config->get('bes_show_tab_advanced_settings', 1))
    {
	    echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'advance-settings-page', Text::_('EB_ADVANCED_SETTINGS', true));
	    echo $this->loadTemplate('advanced_settings', array('editor' => $editor));
	    echo HTMLHelper::_($tabApiPrefix . 'endTab');
    }

	if ($this->config->get('bes_show_tab_messages', 1))
    {
	    echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'messages-page', Text::_('EB_MESSAGES', true));
	    echo $this->loadTemplate('messages', array('editor' => $editor));
	    echo HTMLHelper::_($tabApiPrefix . 'endTab');
    }

	if ($translatable)
	{
		echo $this->loadTemplate('translation', array('editor' => $editor));
	}

	if (count($this->plugins))
	{
		$count = 0;

		foreach ($this->plugins as $plugin)
		{
			$count++;
			echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'tab_' . $count, Text::_($plugin['title'], true));
			echo $plugin['form'];
			echo HTMLHelper::_($tabApiPrefix . 'endTab');
		}
	}

	// Add support for custom settings layout
	if (file_exists(__DIR__ . '/default_custom_settings.php'))
	{
		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'custom-settings-page', Text::_('EB_EVENT_CUSTOM_SETTINGS', true));
		echo $this->loadTemplate('custom_settings', array('editor' => $editor));
		echo HTMLHelper::_($tabApiPrefix . 'endTab');
	}

	echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
	?>
	<input type="hidden" name="option" value="com_eventbooking"/>
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value=""/>
    <input type="hidden" id="activate_recurring_events" value="<?php echo (int) $this->config->activate_recurring_event; ?>">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>