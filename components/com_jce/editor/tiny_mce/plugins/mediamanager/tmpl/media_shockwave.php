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
<div class="media_option shockwave">
	<h4><?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_OPTIONS'); ?></h4>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="shockwave_swstretchstyle" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_SWSTRETCHSTYLE'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<select id="shockwave_swstretchstyle">
					<option value="none"><?php echo JText::_('JNONE'); ?></option>
					<option value="meet"><?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_MEET'); ?></option>
					<option value="fill"><?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_FILL'); ?></option>
					<option value="stage"><?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_STAGE'); ?></option>
				</select>
			</div>
			<label for="shockwave_swvolume" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_VOLUME'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="shockwave_swvolume" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="shockwave_swstretchhalign" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_SWSTRETCHHALIGN'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<select id="shockwave_swstretchhalign">
					<option value="none"><?php echo JText::_('JNONE'); ?></option>
					<option value="left"><?php echo JText::_('WF_OPTION_LEFT'); ?></option>
					<option value="center"><?php echo JText::_('WF_OPTION_CENTER'); ?></option>
					<option value="right"><?php echo JText::_('WF_OPTION_RIGHT'); ?></option>
				</select>
			</div>
			<label for="shockwave_swstretchvalign" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_SWSTRETCHVALIGN'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<select id="shockwave_swstretchvalign">
					<option value="none"><?php echo JText::_('JNONE'); ?></option>
					<option value="meet"><?php echo JText::_('WF_OPTION_TOP'); ?></option>
					<option value="fill"><?php echo JText::_('WF_OPTION_CENTER'); ?></option>
					<option value="stage"><?php echo JText::_('WF_OPTION_BOTTOM'); ?></option>
				</select>
			</div>
		</div>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="shockwave_autostart" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="shockwave_autostart" /> <?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_AUTOSTART'); ?></label>
		<label for="shockwave_sound" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="shockwave_sound" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_SOUND'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="shockwave_swliveconnect" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="shockwave_swliveconnect" /> <?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_LIVECONNECT'); ?></label>
		<label for="shockwave_progress" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="shockwave_progress" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_SHOCKWAVE_PROGRESS'); ?></label>
	</div>
</div>