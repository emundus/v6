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
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="align" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_CAPTION_ALIGN_DESC'); ?>">
        <?php echo JText::_('WF_LABEL_ALIGN'); ?>
    </label>
    <div class="uk-form-controls uk-width-2-5">
        <select id="align">
            <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
            <option value="left"><?php echo JText::_('WF_OPTION_ALIGN_LEFT'); ?></option>
            <option value="center"><?php echo JText::_('WF_OPTION_ALIGN_CENTER'); ?></option>
            <option value="right"><?php echo JText::_('WF_OPTION_ALIGN_RIGHT'); ?></option>
            <option value="justified"><?php echo JText::_('WF_OPTION_ALIGN_JUSTIFIED'); ?></option>
        </select>
    </div>

    <label for="bgcolor" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_CAPTION_BGCOLOR_DESC'); ?>">
        <?php echo JText::_('WF_CAPTION_BGCOLOR'); ?>
    </label>
    <div class="uk-form-controls uk-width-1-5">
        <input id="bgcolor" class="color" type="text" value="" />
    </div>
</div>

<div class="uk-form-row uk-hidden-mini uk-grid uk-grid-small">
    <label class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_CAPTION_PADDING_DESC'); ?>">
        <?php echo JText::_('WF_CAPTION_PADDING'); ?>
    </label>
    <div class="uk-form-controls uk-width-4-5 uk-grid uk-grid-small uk-form-equalize">

        <label for="padding_top" class="uk-form-label">
            <?php echo JText::_('WF_OPTION_TOP'); ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" id="padding_top" value="" />
        </div>

        <label for="padding_right" class="uk-form-label">
            <?php echo JText::_('WF_OPTION_RIGHT'); ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" id="padding_right" value="" />
        </div>

        <label for="padding_bottom" class="uk-form-label">
            <?php echo JText::_('WF_OPTION_BOTTOM'); ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" id="padding_bottom" value="" />
        </div>

        <label for="padding_left" class="uk-form-label">
            <?php echo JText::_('WF_OPTION_LEFT'); ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" id="padding_left" value="" />
        </div>
        <label class="uk-form-label">
            <input type="checkbox" class="uk-equalize-checkbox" checked />
            <?php echo JText::_('WF_LABEL_EQUAL'); ?>
        </label>
    </div>
</div>

<div class="uk-form-row uk-hidden-mini uk-grid uk-grid-small">
    <label class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_CAPTION_MARGIN_DESC'); ?>">
        <?php echo JText::_('WF_CAPTION_MARGIN'); ?>
    </label>
    <div class="uk-form-controls uk-width-4-5 uk-grid uk-grid-small uk-form-equalize">

        <label for="margin_top" class="uk-form-label">
            <?php echo JText::_('WF_OPTION_TOP'); ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" id="margin_top" value="" />
        </div>

        <label for="margin_right" class="uk-form-label">
            <?php echo JText::_('WF_OPTION_RIGHT'); ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" id="margin_right" value="" />
        </div>

        <label for="margin_bottom" class="uk-form-label">
            <?php echo JText::_('WF_OPTION_BOTTOM'); ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" id="margin_bottom" value="" />
        </div>

        <label for="margin_left" class="uk-form-label">
            <?php echo JText::_('WF_OPTION_LEFT'); ?>
        </label>
        <div class="uk-form-controls">
            <input type="text" id="margin_left" value="" />
        </div>
        <label class="uk-form-label">
            <input type="checkbox" class="uk-equalize-checkbox" checked />
            <?php echo JText::_('WF_LABEL_EQUAL'); ?>
        </label>
    </div>
</div>

<div class="uk-form-row uk-grid uk-grid-small">
    <label for="border" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_BORDER_DESC'); ?>">
        <?php echo JText::_('WF_LABEL_BORDER'); ?>
    </label>

    <div class="uk-form-controls uk-width-4-5">
        <div class="uk-form-controls uk-width-0-3 uk-margin-small-top">
            <input type="checkbox" id="border" />
        </div>

        <label for="border_width" class="hastip uk-form-label uk-width-1-10 uk-margin-small-left" title="<?php echo JText::_('WF_LABEL_BORDER_WIDTH_DESC'); ?>"><?php echo JText::_('WF_LABEL_WIDTH'); ?></label>
        <div class="uk-form-controls uk-width-1-6">
            <input pattern="[0-9]+" id="border_width" class="uk-datalist" list="border_width_datalist" />
            <datalist id="border_width_datalist">
                <option value="inherit">--</option>
                <option value="0">0</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="thin"><?php echo JText::_('WF_OPTION_BORDER_THIN'); ?></option>
                <option value="medium"><?php echo JText::_('WF_OPTION_BORDER_MEDIUM'); ?></option>
                <option value="thick"><?php echo JText::_('WF_OPTION_BORDER_THICK'); ?></option>
            </datalist>
        </div>

        <label for="border_style" class="hastip uk-form-label uk-width-1-10 uk-margin-small-left" title="<?php echo JText::_('WF_LABEL_BORDER_STYLE_DESC'); ?>"><?php echo JText::_('WF_LABEL_STYLE'); ?></label>
        <div class="uk-form-controls uk-width-2-10">
            <select id="border_style">
                <option value="inherit">--</option>
                <option value="none"><?php echo JText::_('WF_OPTION_BORDER_NONE'); ?></option>
                <option value="solid"><?php echo JText::_('WF_OPTION_BORDER_SOLID'); ?></option>
                <option value="dashed"><?php echo JText::_('WF_OPTION_BORDER_DASHED'); ?></option>
                <option value="dotted"><?php echo JText::_('WF_OPTION_BORDER_DOTTED'); ?></option>
                <option value="double"><?php echo JText::_('WF_OPTION_BORDER_DOUBLE'); ?></option>
                <option value="groove"><?php echo JText::_('WF_OPTION_BORDER_GROOVE'); ?></option>
                <option value="inset"><?php echo JText::_('WF_OPTION_BORDER_INSET'); ?></option>
                <option value="outset"><?php echo JText::_('WF_OPTION_BORDER_OUTSET'); ?></option>
                <option value="ridge"><?php echo JText::_('WF_OPTION_BORDER_RIDGE'); ?></option>
            </select>
        </div>

        <label for="border_color" class="hastip uk-form-label uk-width-1-10 uk-margin-small-left" title="<?php echo JText::_('WF_LABEL_BORDER_COLOR_DESC'); ?>"><?php echo JText::_('WF_LABEL_COLOR'); ?></label>
        <div class="uk-form-controls uk-width-1-4">
            <input id="border_color" class="color" type="text" value="#000000" />
        </div>
    </div>
</div>

<div class="uk-form-row uk-form-row  uk-grid uk-grid-small">
    <label for="classes" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_CLASSES_DESC'); ?>">
        <?php echo JText::_('WF_LABEL_CLASSES'); ?>
    </label>
    <div class="uk-form-controls uk-width-4-5">
        <input type="text" id="classes" class="uk-datalist" list="classes_datalist" multiple />
        <datalist id="classes_datalist"></datalist>
    </div>
</div>