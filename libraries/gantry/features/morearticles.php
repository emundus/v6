<?php
/**
 * @version   $Id: morearticles.php 23362 2014-10-05 14:28:04Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

defined('JPATH_BASE') or die();

gantry_import('core.gantryfeature');

/**
 * @package     gantry
 * @subpackage  features
 */
class GantryFeatureMoreArticles extends GantryFeature
{
	var $_feature_name = 'morearticles';

	function init()
	{
		/** @var $gantry Gantry */
		global $gantry;

		if ($this->get('enabled')) {

			$gantry->addScript('gantry-morearticles.js');
			$queryUrl = JROUTE::_($gantry->addQueryStringParams($gantry->getCurrentUrl($gantry->_setbyurl), array(
			                                                                                                     'tmpl' => 'component',
			                                                                                                     'type' => 'raw'
			                                                                                                )));
			$gantry->addInlineScript("window.addEvent('domready', function() { new GantryMoreArticles({'leadings': " . $this->_getCurrentLeadingArticles() . ", 'moreText': '" . addslashes($this->get('text')) . "', 'url': '" . $queryUrl . "'}); });");

			if ($gantry->get('morearticles-pagination')) {
				$gantry->addInlineStyle('.rt-pagination {display: none;}');
			}
		}
	}

	function isOrderable()
	{
		return false;
	}

	function _getCurrentLeadingArticles()
	{
		$num_leading_articles = false;
		$app                  = JFactory::getApplication();
		$menus                = $app->getMenu();
		$menu                 = $menus->getActive();
		if (null != $menu) {
			$params               = new GantryRegistry($menu->params->toObject());
			$num_leading_articles = $params->get('num_leading_articles', 0) + $params->get('num_intro_articles', 0);
		}
		return ($num_leading_articles !== false ? $num_leading_articles : 0);
	}
}