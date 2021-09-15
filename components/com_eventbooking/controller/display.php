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
use Joomla\CMS\Uri\Uri;

trait EventbookingControllerDisplay
{
	protected function loadAssets()
	{
		$document = Factory::getDocument();
		$rootUrl  = Uri::root(true);
		$config   = EventbookingHelper::getConfig();

		// Javascript
		HTMLHelper::_('jquery.framework');

		EventbookingHelperHtml::addOverridableScript('media/com_eventbooking/assets/js/eventbookingjq.min.js');

		// CSS
		if ($config->load_bootstrap_css_in_frontend)
		{
			$document->addStyleSheet($rootUrl . '/media/com_eventbooking/assets/bootstrap/css/bootstrap.min.css');
		}

		if ($config->get('load_font_awesome', '1'))
		{
			$document->addStyleSheet($rootUrl . '/media/com_eventbooking/assets/css/font-awesome.min.css');
		}

		$document->addStyleSheet($rootUrl . '/media/com_eventbooking/assets/css/style.min.css');

		if ($config->calendar_theme)
		{
			$theme = $config->calendar_theme;
		}
		else
		{
			$theme = 'default';
		}

		$document->addStyleSheet($rootUrl . '/media/com_eventbooking/assets/css/themes/' . $theme . '.css');

		$theme = EventbookingHelper::getDefaultTheme();

		// Call init script of theme to allow it to load it's own javascript + css files if needed
		if (file_exists(JPATH_ROOT . '/components/com_eventbooking/themes/' . $theme->name . '/init.php'))
		{
			require_once JPATH_ROOT . '/components/com_eventbooking/themes/' . $theme->name . '/init.php';
		}

		$customCssFile = JPATH_ROOT . '/media/com_eventbooking/assets/css/custom.css';

		if (file_exists($customCssFile) && filesize($customCssFile) > 0)
		{
			$document->addStyleSheet($rootUrl . '/media/com_eventbooking/assets/css/custom.css');
		}
	}
}