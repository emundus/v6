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
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="itemtype" class="uk-form-label uk-width-3-10"><?php echo JText::_('WF_LABEL_TYPE'); ?></label>

    <div class="uk-form-controls uk-width-7-10">
        <input type="text" id="itemtype" class="uk-datalist" list="itemtype_datalist" disabled />
        <datalist id="itemtype_datalist"></datalist>
    </div>
</div>

<div class="uk-form-row uk-grid uk-grid-small">
    <label for="itemprop" class="uk-form-label uk-width-3-10"><?php echo JText::_('WF_LABEL_PROPERTY'); ?></label>
    <div class="uk-form-controls uk-width-7-10">
        <input type="text" id="itemprop" class="uk-datalist" list="itemprop_datalist" disabled />
        <datalist id="itemprop_datalist"></datalist>
    </div>
</div>

<div class="uk-form-row uk-grid uk-grid-small">
    <label for="itemid" class="uk-form-label uk-width-3-10"><?php echo JText::_('WF_LABEL_ID'); ?></label>
    <div class="uk-form-controls uk-width-7-10">
        <input type="text" value="" id="itemid" />
    </div>
</div>

<div class="uk-form-row itemtype-options">
    <label class="uk-width-1-2"><input type="radio" id="itemtype-replace" checked="checked" name="itemtype-option" class="uk-margin-small-right" /><?php echo JText::_('WF_LABEL_REPLACE'); ?></label>
    <label class="uk-width-1-2"><input type="radio" id="itemtype-new" name="itemtype-option" class="uk-margin-small-right" /><?php echo JText::_('WF_LABEL_NEW'); ?></label>
</div>