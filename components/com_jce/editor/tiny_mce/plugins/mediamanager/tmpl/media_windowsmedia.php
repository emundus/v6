<?php

/**
 * @copyright 	Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
?>
<div class="media_option windowsmedia">
	<h4><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_OPTIONS'); ?></h4>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_autostart" class="uk-form-label uk-width-1-5"><input type="checkbox" id="windowsmedia_autostart" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_AUTOSTART'); ?></label>
		<label for="windowsmedia_enabled" class="uk-form-label uk-width-1-5"><input type="checkbox" id="windowsmedia_enabled" /> <?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_ENABLED'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_enablecontextmenu" class="uk-form-label uk-width-1-5"><input type="checkbox" id="windowsmedia_enablecontextmenu" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_MENU'); ?></label>
		<label for="windowsmedia_fullscreen" class="uk-form-label uk-width-1-5"><input type="checkbox" id="windowsmedia_fullscreen" /> <?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_FULLSCREEN'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_invokeurls" class="uk-form-label uk-width-1-5"><input type="checkbox" id="windowsmedia_invokeurls" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_INVOKEURLS'); ?></label>
		<label for="windowsmedia_mute" class="uk-form-label uk-width-1-5"><input type="checkbox" id="windowsmedia_mute" /> <?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_MUTE'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_stretchtofit" class="uk-form-label uk-width-1-5"><input type="checkbox" id="windowsmedia_stretchtofit" /> <?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_STRETCHTOFIT'); ?></label>
		<label for="windowsmedia_windowlessvideo" class="uk-form-label uk-width-1-5"><input type="checkbox" id="windowsmedia_windowlessvideo" /> <?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_WINDOWLESSVIDEO'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_balance" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_BALANCE'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_balance" />
			</div>
			<label for="windowsmedia_baseurl" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_BASEURL'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_baseurl" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_captioningid" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_CAPTIONINGID'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_captioningid" />
			</div>
			<label for="windowsmedia_currentmarker" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_CURRENTMARKER'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_currentmarker" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_currentposition" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_CURRENTPOSITION'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_currentposition" />
			</div>
			<label for="windowsmedia_defaultframe" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_DEFAULTFRAME'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_defaultframe" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_playcount" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_PLAYCOUNT'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_playcount" />
			</div>
			<label for="windowsmedia_rate" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_RATE'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_rate" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="windowsmedia_uimode" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_UIMODE'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_uimode" />
			</div>
			<label for="windowsmedia_volume" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_WINDOWSMEDIA_VOLUME'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="windowsmedia_volume" />
			</div>
		</div>
	</div>

	<h6 class="notice">Windows Media is either a registered trademark or trademark of Microsoft Corporation in the United States and/or other countries.</h6>
</div>