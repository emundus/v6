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
<div class="media_option quicktime">
	<h4><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_OPTIONS'); ?></h4>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_loop" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_loop" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_LOOP'); ?></label>
		<label for="quicktime_autoplay" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_autoplay" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_AUTOPLAY'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_cache" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_cache" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_CACHE'); ?></label>
		<label for="quicktime_controller" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_controller" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_CONTROLLER'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_correction" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_correction" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_CORRECTION'); ?></label>
		<label for="quicktime_enablejavascript" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_enablejavascript" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_ENABLEJAVASCRIPT'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_kioskmode" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_kioskmode" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_KIOSKMODE'); ?></label>
		<label for="quicktime_autohref" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_autohref" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_AUTOHREF'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_playeveryframe" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_playeveryframe" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_PLAYEVERYFRAME'); ?></label>
		<label for="quicktime_targetcache" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="quicktime_targetcache" /> <?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_TARGETCACHE'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_scale" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_SCALE'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<select id="quicktime_scale" class="mceEditableSelect">
					<option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
					<option value="tofit"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_TOFIT'); ?></option>
					<option value="aspect"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_ASPECT'); ?></option>
				</select>
			</div>
			<label for="quicktime_qtsrc" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_QTSRC'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="quicktime_qtsrc" />
			</div>
		</div>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_starttime" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_STARTTIME'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="quicktime_starttime" />
			</div>
			<label for="quicktime_endtime" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_ENDTIME'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="quicktime_endtime" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_target" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_TARGET'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="quicktime_target" />
			</div>
			<label for="quicktime_href" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_HREF'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="quicktime_href" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="quicktime_qtsrcchokespeed" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_QTSRCCHOKESPEED'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="quicktime_qtsrcchokespeed" />
			</div>
			<label for="quicktime_volume" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_QUICKTIME_VOLUME'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="quicktime_volume" />
			</div>
		</div>
	</div>

	<h6 class="notice">QuickTime® is a trademark of Apple Inc., registered in the U.S. and other countries.</h6>
</div>