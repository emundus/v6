<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
?>
<div class="media_option real">
	<h4><?php echo JText::_('WF_MEDIAMANAGER_REAL_OPTIONS'); ?></h4>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="real_autostart" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_autostart" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_AUTOSTART'); ?></label>
		<label for="real_loop" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_loop" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_LOOP'); ?></label>

		<label for="real_autogotourl" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_autogotourl" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_AUTOGOTOURL'); ?></label>
		<label for="real_center" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_center" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_CENTER'); ?></label>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="real_autostart" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_autostart" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_AUTOSTART'); ?></label>
		<label for="real_loop" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_loop" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_LOOP'); ?></label>

		<label for="real_imagestatus" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_imagestatus" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_IMAGESTATUS'); ?></label>
		<label for="real_maintainaspect" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_maintainaspect" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_MAINTAINASPECT'); ?></label>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="real_autostart" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_autostart" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_AUTOSTART'); ?></label>
		<label for="real_loop" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_loop" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_LOOP'); ?></label>

		<label for="real_nojava" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_nojava" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_NOJAVA'); ?></label>
		<label for="real_prefetch" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_prefetch" checked="checked" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_PREFETCH'); ?></label>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="real_shuffle" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="real_shuffle" /> <?php echo JText::_('WF_MEDIAMANAGER_REAL_SHUFFLE'); ?></label>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="real_console" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_REAL_CONSOLE'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="real_console" />
			</div>
			<label for="real_controls" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_REAL_CONTROLS'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="real_controls" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="real_numloop" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_REAL_NUMLOOP'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="real_numloop" />
			</div>
			<label for="real_scriptcallbacks" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_REAL_SCRIPTCALLBACKS'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="real_scriptcallbacks" />
			</div>
		</div>
	</div>

	<h6 class="notice">RealNetworks, Real, the Real logo, RealPlayer, and the RealPlayer logo are trademarks or registered trademarks of RealNetworks, Inc.</h6>
</div>