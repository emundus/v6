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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip');

if (EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('script', 'system/showon.js', ['version' => 'auto', 'relative' => true]);
}
else
{
	HTMLHelper::_('script', 'jui/cms.js', ['version' => 'auto', 'relative' => true]);
}

if (EventbookingHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';

	EventbookingHelperJquery::colorbox('a.modal');
}
else
{
	$tabApiPrefix = 'bootstrap.';

	HTMLHelper::_('behavior.modal');
	HTMLHelper::_('behavior.tabstate');
}

HTMLHelper::_('jquery.framework');

if (version_compare(JVERSION, '4.0.0-dev', '>='))
{
	HTMLHelper::_('script', 'system/showon.js', array('version' => 'auto', 'relative' => true));
}
else
{
	HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
}

$editor          = Editor::getInstance(Factory::getApplication()->get('editor', 'none'));
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$btnPrimary      = $bootstrapHelper->getClassMapping('btn btn-primary');
$formHorizontal  = $bootstrapHelper->getClassMapping('form form-horizontal');

EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/js/site-event-form.min.js');

Factory::getDocument()->addScriptOptions('activateRecurringEvent', (bool) $this->config->activate_recurring_event);

$languageItems = [
    'EB_PLEASE_ENTER_TITLE',
    'EB_ENTER_EVENT_DATE',
    'EB_CHOOSE_CATEGORY',
    'EB_ENTER_RECURRING_INTERVAL',
    'EB_CHOOSE_ONE_DAY',
    'EB_ENTER_DAY_IN_MONTH',
    'EB_ENTER_RECURRING_ENDING_SETTINGS',
    'EB_NO_ROW_TO_DELETE',
];

EventbookingHelperHtml::addJSStrings($languageItems);

$showRecurringSettingsTab      = $this->config->activate_recurring_event && (!$this->item->id || $this->item->event_type == 1);
$showGroupRegistrationRatesTab = $this->config->get('fes_show_group_registration_rates_tab', 1);
$showMiscTab                   = $this->config->get('fes_show_misc_tab', 1);
$showDiscountSettingTab        = $this->config->get('fes_show_discount_setting_tab', 1);
$showExtraInformationTab       = $this->config->get('fes_show_extra_information_tab', 1) && $this->config->event_custom_field;

$hasTab = $showGroupRegistrationRatesTab || $showMiscTab
    || $showDiscountSettingTab || $showExtraInformationTab
    || $showRecurringSettingsTab
    || $this->isMultilingual || count($this->plugins);
?>
<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_ADD_EDIT_EVENT')); ?></h1>
<div id="eb-add-edit-event-page" class="eb-container">
    <div class="btn-toolbar" id="btn-toolbar">
		<?php echo JToolbar::getInstance('toolbar')->render(); ?>
    </div>
    <form action="<?php echo Route::_('index.php?option=com_eventbooking&view=events&Itemid='.$this->Itemid); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="<?php echo $formHorizontal; ?>">
    <div class="<?php echo $rowFluidClass; ?> eb-container">
        <?php
            if ($hasTab)
            {
	            echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'event', ['active' => 'basic-information-page']);
	            echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'basic-information-page', Text::_('EB_BASIC_INFORMATION', true));
            }

            echo $this->loadTemplate('general', array('editor' => $editor));

            if ($hasTab)
            {
	            echo HTMLHelper::_($tabApiPrefix . 'endTab');
            }

            if ($showRecurringSettingsTab)
            {
                echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'recurring-settings-page', Text::_('EB_RECURRING_SETTINGS', true));
                echo $this->loadTemplate('recurring_settings');
                echo HTMLHelper::_($tabApiPrefix . 'endTab');
            }

            if ($showGroupRegistrationRatesTab)
            {
	            echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'group-registration-rates-page', Text::_('EB_GROUP_REGISTRATION_RATES', true));
	            echo $this->loadTemplate('group_rates');
	            echo HTMLHelper::_($tabApiPrefix . 'endTab');
            }

            if ($showMiscTab)
            {
	            echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'misc-page', Text::_('EB_MISC', true));
	            echo $this->loadTemplate('misc');
	            echo HTMLHelper::_($tabApiPrefix . 'endTab');
            }

            if ($showDiscountSettingTab)
            {
	            echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'discount-page', Text::_('EB_DISCOUNT_SETTING', true));
	            echo $this->loadTemplate('discount_settings');
	            echo HTMLHelper::_($tabApiPrefix . 'endTab');
            }

            if ($showExtraInformationTab)
            {
                echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'fields-page', Text::_('EB_EXTRA_INFORMATION', true));
                echo $this->loadTemplate('fields');
                echo HTMLHelper::_($tabApiPrefix . 'endTab');
            }

            if ($this->isMultilingual)
            {
                echo $this->loadTemplate('translation', ['editor' => $editor]);
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

            if ($hasTab)
            {
	            echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
            }
        ?>
    </div>
        <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="return" value="<?php echo $this->return; ?>" />
        <input type="hidden" name="activate_tickets_pdf" value="<?php echo $this->item->activate_tickets_pdf; ?>"/>
        <input type="hidden" name="send_tickets_via_email" value="<?php echo $this->item->send_tickets_via_email; ?>"/>
        <input type="hidden" name="form_layout" value="form" />
        <?php echo HTMLHelper::_( 'form.token' ); ?>
    </form>
</div>