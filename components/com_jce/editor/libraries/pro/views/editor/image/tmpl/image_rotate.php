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
<h3 id="transform-rotate" data-action="rotate">
	<a href="#">
		<?php echo JText::_('WF_MANAGER_TRANSFORM_ROTATE', 'Rotate'); ?>
	</a>
</h3>
<div class="uk-form">
	<div class="uk-grid uk-grid-mini">
		<label for="rotate-angle" class="uk-width-2-10"><?php echo JText::_('WF_MANAGER_TRANSFORM_ROTATE', 'Rotate'); ?></label>
		<div class="uk-width-4-10">
			<button type="button" class="uk-button uk-width-1-1 uk-flex uk-flex-middle uk-flex-space-around" id="rotate-angle-clockwise"><i class="uk-icon uk-icon-redo"></i><span class="uk-text"><?php echo JText::_('WF_MANAGER_TRANSFORM_ROTATE_RIGHT', 'Right'); ?></span></button>
		</div>
		<div class="uk-width-4-10">
			<button type="button" class="uk-button uk-width-1-1 uk-flex uk-flex-middle uk-flex-space-around" id="rotate-angle-anticlockwise"><i class="uk-icon uk-icon-undo"></i><span class="uk-text"><?php echo JText::_('WF_MANAGER_TRANSFORM_ROTATE_LEFT', 'Left'); ?></span></button>
		</div>
	</div>
	<div class="uk-grid uk-grid-mini">
		<label for="rotate-flip" class="uk-width-2-10"><?php echo JText::_('WF_MANAGER_TRANSFORM_FLIP', 'Flip'); ?></label>
		<div class="uk-width-4-10">
			<button type="button" class="uk-button uk-width-1-1 uk-flex uk-flex-middle uk-flex-space-around" id="rotate-flip-vertical"><i class="uk-icon uk-icon-flipv"></i><span class="uk-text"><?php echo JText::_('WF_MANAGER_TRANSFORM_FLIP_VERTICAL', 'Vertical'); ?></span></button>
		</div>
		<div class="uk-width-4-10">
			<button type="button" class="uk-button uk-width-1-1 uk-flex uk-flex-middle uk-flex-space-around" id="rotate-flip-horizontal"><i class="uk-icon uk-icon-fliph"></i><span class="uk-text"><?php echo JText::_('WF_MANAGER_TRANSFORM_FLIP_HORIZONTAL', 'Horizontal'); ?></span></button>
		</div>
	</div>
</div>