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
<div class="uk-grid uk-grid-small">
    <div class="uk-width-4-5">
        <div class="uk-form-row uk-grid uk-grid-small">
            <label for="media_type" class="uk-form-label uk-width-1-5">
                <?php echo JText::_('WF_LABEL_MEDIA_TYPE'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5">
                <select id="media_type" onchange="MediaManagerDialog.changeType(this.value);">
                    <?php echo $this->plugin->getMediaOptions(); ?>
                </select>
            </div>
        </div>
        <div class="uk-form-row uk-grid uk-grid-small">
            <label for="src" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_URL_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_URL'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5">
                <input type="text" id="src" value="" class="filebrowser" data-filebrowser required />
            </div>
        </div>

        <div class="uk-form-row uk-grid uk-grid-small" id="attributes-dimensions">
            <label class="hastip uk-form-label uk-width-1-1 uk-width-small-1-5" title="<?php echo JText::_('WF_LABEL_DIMENSIONS_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_DIMENSIONS'); ?>
            </label>
            <div class="uk-form-control uk-width-1-1 uk-width-small-4-5 uk-form-constrain uk-flex">

                <div class="uk-form-controls">
                    <input type="text" id="width" value="" class="uk-text-muted" />
                </div>

                <div class="uk-form-controls">
                    <strong class="uk-form-label uk-text-center uk-vertical-align-middle">&times;</strong>
                </div>

                <div class="uk-form-controls">
                    <input type="text" id="height" value="" class="uk-text-muted" />
                </div>

                <label class="uk-form-label">
                    <input class="uk-constrain-checkbox" type="checkbox" checked />
                    <?php echo JText::_('WF_LABEL_PROPORTIONAL'); ?>
                </label>
            </div>
        </div>

        <div class="uk-hidden-mini uk-grid uk-grid-small uk-form-row" id="attributes-align">
            <label for="align" class="hastip uk-form-label uk-width-1-5"
                title="<?php echo JText::_('WF_LABEL_ALIGN_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_ALIGN'); ?>
            </label>
            <div class="uk-grid uk-grid-small uk-form-row uk-width-4-5">
                <div class="uk-width-1-2">
                    <div class="uk-form-controls uk-width-9-10">
                        <select id="align">
                            <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                            <optgroup label="------------">
                                <option value="left"><?php echo JText::_('WF_OPTION_ALIGN_LEFT'); ?></option>
                                <option value="center"><?php echo JText::_('WF_OPTION_ALIGN_CENTER'); ?></option>
                                <option value="right"><?php echo JText::_('WF_OPTION_ALIGN_RIGHT'); ?></option>
                            </optgroup>
                            <optgroup label="------------">
                                <option value="top"><?php echo JText::_('WF_OPTION_ALIGN_TOP'); ?></option>
                                <option value="middle"><?php echo JText::_('WF_OPTION_ALIGN_MIDDLE'); ?></option>
                                <option value="bottom"><?php echo JText::_('WF_OPTION_ALIGN_BOTTOM'); ?></option>
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="uk-width-1-2 uk-hidden-mini">
                    <label for="clear" class="hastip uk-form-label uk-width-3-10"
                        title="<?php echo JText::_('WF_LABEL_CLEAR_DESC'); ?>">
                        <?php echo JText::_('WF_LABEL_CLEAR'); ?>
                    </label>
                    <div class="uk-form-controls uk-width-7-10">
                        <select id="clear" disabled>
                            <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
                            <option value="none"><?php echo JText::_('WF_OPTION_CLEAR_NONE'); ?></option>
                            <option value="both"><?php echo JText::_('WF_OPTION_CLEAR_BOTH'); ?></option>
                            <option value="left"><?php echo JText::_('WF_OPTION_CLEAR_LEFT'); ?></option>
                            <option value="right"><?php echo JText::_('WF_OPTION_CLEAR_RIGHT'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-hidden-mini uk-grid uk-grid-small uk-form-row" id="attributes-margin">
            <label for="margin" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_MARGIN_DESC'); ?>">
                <?php echo JText::_('WF_LABEL_MARGIN'); ?>
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
                    <input type="checkbox" class="uk-equalize-checkbox" />
                    <?php echo JText::_('WF_LABEL_EQUAL'); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="uk-width-1-5 uk-hidden-small">
        <div class="preview">
            <img id="sample" src="<?php echo $this->plugin->image('sample.jpg', 'libraries'); ?>" alt="sample.jpg" />
            <?php echo JText::_('WF_LOREM_IPSUM'); ?>
        </div>
    </div>
</div>