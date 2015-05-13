<?php
/**
* @version   $Id: iconlist.php 26100 2015-01-27 14:16:12Z james $
* @author    RocketTheme http://www.rockettheme.com
* @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldIconList extends JFormFieldList
{

	public $type = 'IconList';

	// icons
	protected $icons = array('icon-adjust','icon-align-center','icon-align-justify','icon-align-left','icon-align-right','icon-arrow-down','icon-arrow-left','icon-arrow-right','icon-arrow-up','icon-asterisk','icon-backward','icon-ban-circle','icon-bar-chart','icon-barcode','icon-beaker','icon-bell','icon-bold','icon-bolt','icon-book','icon-bookmark','icon-bookmark-empty','icon-briefcase','icon-bullhorn','icon-calendar','icon-camera','icon-camera-retro','icon-caret-down','icon-caret-left','icon-caret-right','icon-caret-up','icon-certificate','icon-check','icon-check-empty','icon-chevron-down','icon-chevron-left','icon-chevron-right','icon-chevron-up','icon-circle-arrow-down','icon-circle-arrow-left','icon-circle-arrow-right','icon-circle-arrow-up','icon-cloud','icon-cog','icon-cogs','icon-columns','icon-comment','icon-comment-alt','icon-comments','icon-comments-alt','icon-copy','icon-credit-card','icon-cut','icon-dashboard','icon-download','icon-download-alt','icon-edit','icon-eject','icon-envelope','icon-envelope-alt','icon-exclamation-sign','icon-external-link','icon-eye-close','icon-eye-open','icon-facebook','icon-facebook-sign','icon-facetime-video','icon-fast-backward','icon-fast-forward','icon-file','icon-film','icon-filter','icon-fire','icon-flag','icon-folder-close','icon-folder-open','icon-font','icon-forward','icon-fullscreen','icon-gift','icon-github','icon-github-sign','icon-glass','icon-globe','icon-google-plus','icon-google-plus-sign','icon-group','icon-hand-down','icon-hand-left','icon-hand-right','icon-hand-up','icon-hdd','icon-headphones','icon-heart','icon-heart-empty','icon-home','icon-inbox','icon-indent-left','icon-indent-right','icon-info-sign','icon-italic','icon-key','icon-leaf','icon-legal','icon-lemon','icon-link','icon-linkedin','icon-linkedin-sign','icon-list','icon-list-alt','icon-list-ol','icon-list-ul','icon-lock','icon-magic','icon-magnet','icon-map-marker','icon-minus','icon-minus-sign','icon-money','icon-move','icon-music','icon-off','icon-ok','icon-ok-circle','icon-ok-sign','icon-paper-clip','icon-paste','icon-pause','icon-pencil','icon-phone','icon-phone-sign','icon-picture','icon-pinterest','icon-pinterest-sign','icon-plane','icon-play','icon-play-circle','icon-plus','icon-plus-sign','icon-print','icon-pushpin','icon-qrcode','icon-question-sign','icon-random','icon-refresh','icon-remove','icon-remove-circle','icon-remove-sign','icon-reorder','icon-repeat','icon-resize-full','icon-resize-horizontal','icon-resize-small','icon-resize-vertical','icon-retweet','icon-road','icon-rss','icon-save','icon-screenshot','icon-search','icon-share','icon-share-alt','icon-shopping-cart','icon-sign-blank','icon-signal','icon-signin','icon-signout','icon-sitemap','icon-sort','icon-sort-down','icon-sort-up','icon-star','icon-star-empty','icon-star-half','icon-step-backward','icon-step-forward','icon-stop','icon-strikethrough','icon-table','icon-tag','icon-tags','icon-tasks','icon-text-height','icon-text-width','icon-th','icon-th-large','icon-th-list','icon-thumbs-down','icon-thumbs-up','icon-time','icon-tint','icon-trash','icon-trophy','icon-truck','icon-twitter','icon-twitter-sign','icon-umbrella','icon-underline','icon-undo','icon-unlock','icon-upload','icon-upload-alt','icon-user','icon-user-md','icon-volume-down','icon-volume-off','icon-volume-up','icon-warning-sign','icon-wrench','icon-zoom-in','icon-zoom-out');


	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		$options[] = JHtml::_('select.option', '-1', '- None Selected -', 'value', 'text');

		foreach ($this->icons as $icon)
		{
			$options[] = JHtml::_('select.option', $icon, $icon, 'value', 'text');
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;

	}
}
