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
<div class="media_option flash">
	<h4><?php echo JText::_('WF_MEDIAMANAGER_FLASH_OPTIONS'); ?></h4>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="flash_quality" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_QUALITY'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<select id="flash_quality">
					<option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
					<option value="high"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_HIGH'); ?></option>
					<option value="low"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_LOW'); ?></option>
					<option value="autolow"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_AUTOLOW'); ?></option>
					<option value="autohigh"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_AUTOHIGH'); ?></option>
					<option value="best"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_BEST'); ?></option>
				</select>
			</div>

			<label for="flash_scale" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_SCALE'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<select id="flash_scale">
					<option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
					<option value="showall"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_SHOWALL'); ?></option>
					<option value="noborder"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_NOBORDER'); ?></option>
					<option value="exactfit"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_EXACTFIT'); ?></option>
				</select>
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="flash_wmode" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_WMODE'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<select id="flash_wmode">
					<option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
					<option value="window"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_WINDOW'); ?></option>
					<option value="opaque"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_OPAQUE'); ?></option>
					<option value="transparent" selected="selected"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_TRANSPARENT'); ?></option>
				</select>
			</div>

			<label for="flash_salign" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_SALIGN'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<select id="flash_salign">
					<option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
					<option value="l"><?php echo JText::_('WF_OPTION_LEFT'); ?></option>
					<option value="t"><?php echo JText::_('WF_OPTION_TOP'); ?></option>
					<option value="r"><?php echo JText::_('WF_OPTION_RIGHT'); ?>t</option>
					<option value="b"><?php echo JText::_('WF_OPTION_BOTTOM'); ?></option>
					<option value="tl"><?php echo JText::_('WF_OPTION_TOP_LEFT'); ?></option>
					<option value="tr"><?php echo JText::_('WF_OPTION_TOP_RIGHT'); ?></option>
					<option value="bl"><?php echo JText::_('WF_OPTION_BOTTOM_LEFT'); ?></option>
					<option value="br"><?php echo JText::_('WF_OPTION_BOTTOM_RIGHT'); ?></option>
				</select>
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="flash_play" class="uk-form-label uk-width-1-5"><input type="checkbox" id="flash_play" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_FLASH_PLAY'); ?></label>
		<label for="flash_loop" class="uk-form-label uk-width-1-5"><input type="checkbox" id="flash_loop" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_LABEL_LOOP'); ?></label>
		<label for="flash_menu" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="flash_menu" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_FLASH_MENU'); ?></label>
		<label for="flash_swliveconnect" class="uk-form-label uk-width-1-5"><input type="checkbox" id="flash_swliveconnect" /> <?php echo JText::_('WF_MEDIAMANAGER_FLASH_LIVECONNECT'); ?></label>
		<label for="flash_allowfullscreen" class="uk-form-label uk-width-1-5"><input type="checkbox" id="flash_allowfullscreen" /> <?php echo JText::_('WF_MEDIAMANAGER_FLASH_ALLOWFULLSCREEN'); ?></label>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="flash_base" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_BASE'); ?></label>
		<div class="uk-form-controls uk-width-4-5">
			<input type="text" id="flash_base" />
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="flash_flashVars" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_FLASH_FLASHVARS'); ?></label>
		<div class="uk-form-controls uk-width-4-5">
			<textarea id="flash_flashvars" rows="3"></textarea>
		</div>
	</div>

	<p class="uk-text-small">Adobe and Flash are either registered trademarks or trademarks of Adobe Systems
		Incorporated in the United States and/or other countries.</p>
</div>