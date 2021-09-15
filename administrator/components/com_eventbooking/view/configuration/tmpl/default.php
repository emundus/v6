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
use Joomla\CMS\Language\Multilanguage;

HTMLHelper::_('bootstrap.tooltip');
$document = Factory::getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");
$translatable = Multilanguage::isEnabled() && count($this->languages);
$editor       = Editor::getInstance(Factory::getApplication()->get('editor'));
$config       = $this->config;

if (!EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('formbehavior.chosen', 'select.chosen');
	HTMLHelper::_('behavior.tabstate');
}

HTMLHelper::_('jquery.framework');

if (EventbookingHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';
	HTMLHelper::_('script', 'system/showon.js', ['version' => 'auto', 'relative' => true]);
}
else
{
	$tabApiPrefix = 'bootstrap.';
	HTMLHelper::_('script', 'jui/cms.js', ['version' => 'auto', 'relative' => true]);
}

/* @var EventbookingViewConfigurationHtml $this */
?>
<div class="row-fluid">
    <form action="index.php?option=com_eventbooking&view=configuration" method="post" name="adminForm" id="adminForm"
          class="form-horizontal eb-configuration">
		<?php
		echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'configuration', ['active' => 'general-page']);

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'general-page', Text::_('EB_GENERAL', true));
		echo $this->loadTemplate('general', ['config' => $config]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'theme-page', Text::_('EB_THEMES', true));
		echo $this->loadTemplate('themes', ['config' => $config]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'sef-setting-page', Text::_('EB_SEF_SETTING', true));
		echo $this->loadTemplate('sef', ['config' => $config]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'PDF_SETTINGS', Text::_('EB_PDF_SETTINGS', true));
		echo $this->loadTemplate('pdf_settings');
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'invoice-page', Text::_('EB_INVOICE_SETTINGS', true));
		echo $this->loadTemplate('invoice', ['config' => $config, 'editor' => $editor]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'tickets-page', Text::_('EB_TICKETS_SETTINGS', true));
		echo $this->loadTemplate('tickets', ['config' => $config, 'editor' => $editor]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'certificate-page', Text::_('EB_CERTIFICATE_SETTINGS', true));
		echo $this->loadTemplate('certificate', ['config' => $config, 'editor' => $editor]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'submit-event-fields-page', Text::_('EB_SUBMIT_EVENT_FIELDS', true));
		echo $this->loadTemplate('submit_event_fields', ['config' => $config]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'backend-submit-event-fields-page', Text::_('EB_BACKEND_SUBMIT_EVENT_FIELDS', true));
		echo $this->loadTemplate('backend_submit_event_fields', ['config' => $config]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'export-settings-page', Text::_('EB_EXPORT_REGISTRANTS_SETTINGS', true));
		echo $this->loadTemplate('export_fields', ['config' => $config]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		if ($translatable)
		{
			echo $this->loadTemplate('translation', ['config' => $config, 'editor' => $editor]);
		}

		if ($config->event_custom_field)
		{
			echo $this->loadTemplate('event_fields');
		}

		echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'eu-tax-rules-page', Text::_('EB_EU_TAX_RULES_SETTINGS', true));
		echo $this->loadTemplate('eu_tax_rules', ['config' => $config]);
		echo HTMLHelper::_($tabApiPrefix . 'endTab');

		echo $this->loadTemplate('custom_css');

		// Add support for custom settings layout
		if (file_exists(__DIR__ . '/default_custom_settings.php'))
		{
			echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'custom-settings-page', Text::_('EB_CUSTOM_SETTINGS', true));
			echo $this->loadTemplate('custom_settings', ['config' => $config, 'editor' => $editor]);
			echo HTMLHelper::_($tabApiPrefix . 'endTab');
		}

		echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
		?>
        <div class="clearfix"></div>
        <input type="hidden" name="task" value=""/>
    </form>
</div>