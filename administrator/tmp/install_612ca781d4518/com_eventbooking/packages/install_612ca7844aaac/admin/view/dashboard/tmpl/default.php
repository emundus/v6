<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

JToolBarHelper::title(Text::_('EB_DASHBOARD'), 'generic.png');

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
    <div id="cpanel" class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
		<?php
		$this->quickiconButton('index.php?option=com_eventbooking&view=configuration', 'icon-48-eventbooking-config.png', Text::_('EB_CONFIGURATION'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=categories', 'icon-48-eventbooking-categories.png', Text::_('EB_CATEGORIES'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=events', 'icon-48-eventbooking-events.png', Text::_('EB_EVENTS'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=registrants', 'icon-48-eventbooking-registrants.png', Text::_('EB_REGISTRANTS'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=fields', 'icon-48-eventbooking-fields.png', Text::_('EB_CUSTOM_FIELDS'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=locations', 'icon-48-eventbooking-locations.png', Text::_('EB_LOCATIONS'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=coupons', 'icon-48-eventbooking-coupons.png', Text::_('EB_COUPONS'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=plugins', 'icon-48-eventbooking-payments.png', Text::_('EB_PAYMENT_PLUGINS'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=language', 'icon-48-eventbooking-language.png', Text::_('EB_TRANSLATION'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=message', 'icon-48-mail.png', Text::_('EB_EMAIL_MESSAGES'));
		$this->quickiconButton('index.php?option=com_eventbooking&task=registrant.export', 'icon-48-eventbooking-export.png', Text::_('EB_EXPORT_REGISTRANTS'));

		//Permission settings
		$return = urlencode(base64_encode(Uri::getInstance()->toString()));

		$this->quickiconButton('index.php?option=com_config&amp;view=component&amp;component=com_eventbooking&amp;return=' . $return, 'icon-48-acl.png', Text::_('EB_PERMISSIONS'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=massmail', 'icon-48-eventbooking-massmail.png', Text::_('EB_MASS_MAIL'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=countries', 'icon-48-countries.png', Text::_('EB_COUNTRIES'));
		$this->quickiconButton('index.php?option=com_eventbooking&view=states', 'icon-48-states.png', Text::_('EB_STATES'));

		if ($this->config->check_new_version_in_dashboard !== '0')
		{
			$this->quickiconButton('index.php?option=com_eventbooking', 'icon-48-download.png', Text::_('EB_UPDATE_CHECKING'), 'update-check');
		}
		?>
    </div>
    <div class="<?php echo $bootstrapHelper->getClassMapping('span6'); ?>">
        <?php
            echo HTMLHelper::_('bootstrap.startAccordion', 'statistics_pane', array('active' => 'statistic'));
            echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('EB_STATISTICS'), 'statistic');
            echo $this->loadTemplate('statistics');
            echo HTMLHelper::_('bootstrap.endSlide');
            echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('EB_UPCOMING_EVENTS'), 'upcoming_events');
            echo $this->loadTemplate('upcoming_events');
            echo HTMLHelper::_('bootstrap.endSlide');
            echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('EB_LATEST_REGISTRANTS'), 'registrants');
            echo $this->loadTemplate('registrants');
            echo HTMLHelper::_('bootstrap.endSlide');
            echo HTMLHelper::_('bootstrap.addSlide', 'statistics_pane', Text::_('EB_USEFUL_LINKS'), 'links_panel');
            echo $this->loadTemplate('useful_links');
            echo HTMLHelper::_('bootstrap.endSlide');
            echo HTMLHelper::_('bootstrap.endAccordion');
        ?>
    </div>
</div>
<style>
	#statistics_pane
    {
		margin:0px !important
	}
</style>
<?php
if ($this->config->check_new_version_in_dashboard !== '0')
{
	HTMLHelper::_('behavior.core');

	$document = Factory::getDocument();
	$baseUri  = Uri::base(true);

	$document->addScript(Uri::root(true) . '/media/com_eventbooking/js/admin-dashboard-default.min.js');
	$document->addScriptOptions('upToDateImg', $baseUri . '/components/com_eventbooking/assets/icons/icon-48-jupdate-uptodate.png');
	$document->addScriptOptions('updateFoundImg', $baseUri . '/components/com_eventbooking/assets/icons/icon-48-jupdate-updatefound.png');
	$document->addScriptOptions('updateFoundImg', $baseUri . '/components/com_eventbooking/assets/icons/icon-48-deny.png');

	Text::script('EB_UPDATE_CHECKING_ERROR', true);
}