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
<h3 id="transform-crop" data-action="crop">
	<a href="#">
		<?php echo JText::_('WF_MANAGER_TRANSFORM_CROP', 'Crop'); ?>
	</a>
</h3>
<div class="uk-form">
	<div class="uk-grid uk-grid-small">
		<label for="crop_width" class="uk-width-3-10">
			<?php echo JText::_('WF_LABEL_WIDTH'); ?>
		</label>
		<input type="text" id="crop_width" value="" class="uk-width-2-10 uk-text-center" />

		<label for="crop_x" class="uk-width-3-10 uk-text-center">
			<?php echo JText::_('WF_LABEL_CROP_X', 'X'); ?>
		</label>
		<input type="text" id="crop_x" value="" class="uk-width-2-10 uk-text-center" />
	</div>

	<div class="uk-grid uk-grid-small">
		<label for="crop_height" class="uk-width-3-10">
			<?php echo JText::_('WF_LABEL_HEIGHT'); ?>
		</label>
		<input type="text" id="crop_height" class="uk-width-2-10 uk-text-center" />

		<label for="crop_y" class="uk-width-3-10 uk-text-center">
			<?php echo JText::_('WF_LABEL_CROP_Y', 'Y'); ?>
		</label>
		<input type="text" id="crop_y" class="uk-width-2-10 uk-text-center" />
	</div>

	<div class="uk-grid uk-grid-small">
		<div class="uk-width-4-10">
			<input type="checkbox" id="crop_constrain" />
			<label for="crop_constrain">
				<?php echo JText::_('WF_LABEL_CONSTRAIN'); ?>
			</label>
		</div>

		<div class="uk-width-6-10 uk-padding-remove">
			<select id="crop_presets" class="uk-width-1-1">
				<option value=""><?php echo JText::_('WF_MANAGER_TRANSFORM_PRESET_ORIGINAL'); ?></option>
				<?php foreach ($this->lists['crop'] as $option):?>
				<option value="<?php echo $option; ?>"><?php echo $option; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="uk-form-row uk-align-right uk-margin-top uk-margin-bottom">
		<button type="button" id="crop_apply" class="uk-button uk-button-primary apply" data-function="crop">
			<?php echo JText::_('WF_LABEL_APPLY'); ?>
		</button>
		<button type="button" id="crop_reset" class="uk-button reset" data-function="crop">
			<?php echo JText::_('WF_LABEL_RESET'); ?>
		</button>
	</div>
</div>