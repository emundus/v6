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
<div class="media_option divx">
    <h4><?php echo JText::_('WF_MEDIAMANAGER_DIVX_OPTIONS'); ?></h4>


    <div class="uk-form-row uk-grid uk-grid-small">
        <label for="divx_loop" class="uk-form-label uk-width-1-4"><input type="checkbox" class="checkbox" id="divx_loop">
            <?php echo JText::_('WF_MEDIAMANAGER_DIVX_LOOP'); ?></label>
        <label for="divx_bannerenabled" class="uk-form-label uk-width-1-4"><input type="checkbox" class="checkbox" id="divx_bannerenabled" checked="checked"> <?php echo JText::_('WF_MEDIAMANAGER_DIVX_BANNERENABLED'); ?></label>
        <label for="divx_autoplay" class="uk-form-label uk-width-1-4"><input type="checkbox" class="checkbox" id="divx_autoplay" checked="checked">
            <?php echo JText::_('WF_MEDIAMANAGER_DIVX_AUTOPLAY'); ?></label>
        <label for="divx_allowcontextmenu" class="uk-form-label uk-width-1-4"><input type="checkbox" class="checkbox" id="divx_allowcontextmenu" checked="checked"> <?php echo JText::_('WF_MEDIAMANAGER_DIVX_ALLOWCONTEXTMENU'); ?></label>
    </div>

    <div class="uk-form-row uk-grid uk-grid-small">
        <label for="divx_mode" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_DIVX_MODE'); ?></label>
        <div class="uk-width-4-5 uk-grid uk-grid-small">
            <div class="uk-form-controls uk-width-2-5">
                <select id="divx_mode" onchange="MediaManagerDialog.setControllerHeight('divx');">
                    <option value="null">
                        <?php echo JText::_('WF_MEDIAMANAGER_DIVX_NULL'); ?>
                    </option>

                    <option value="zero">
                        <?php echo JText::_('WF_MEDIAMANAGER_DIVX_ZERO'); ?>
                    </option>

                    <option value="mini">
                        <?php echo JText::_('WF_MEDIAMANAGER_DIVX_MINI'); ?>
                    </option>

                    <option value="large">
                        <?php echo JText::_('WF_MEDIAMANAGER_DIVX_LARGE'); ?>
                    </option>

                    <option value="full">
                        <?php echo JText::_('WF_MEDIAMANAGER_DIVX_FULL'); ?>
                    </option>
                </select>
            </div>

            <label for="divx_bufferingmode" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_DIVX_BUFFERINGMODE'); ?></label>
            <div class="uk-form-controls uk-width-2-5">
                <select id="divx_bufferingmode">
                    <option value="null">
                        <?php echo JText::_('WF_MEDIAMANAGER_DIVX_NULL'); ?>
                    </option>

                    <option value="auto">
                        <?php echo JText::_('WF_MEDIAMANAGER_DIVX_AUTO'); ?>
                    </option>

                    <option value="full">
                        <?php echo JText::_('WF_MEDIAMANAGER_DIVX_FULL'); ?>
                    </option>
                </select>
            </div>
        </div>
    </div>

    <div class="uk-form-row uk-grid uk-grid-small">
        <label for="divx_previewmessage" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_DIVX_PREVIEWMESSAGE'); ?></label>
        <div class="uk-width-4-5 uk-grid uk-grid-small">
            <div class="uk-form-controls uk-width-2-5">
                <input type="text" id="divx_previewmessage">
            </div>
            <label for="divx_movietitle" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_DIVX_MOVIETITLE'); ?></label>
            <div class="uk-form-controls uk-width-2-5">
                <input type="text" id="divx_movietitle">
            </div>
        </div>
    </div>

    <div class="uk-form-row uk-grid uk-grid-small">
        <label for="divx_previewmessagefontsize" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_DIVX_PREVIEWMESSAGEFONTSIZE'); ?></label>
        <div class="uk-width-4-5 uk-grid uk-grid-small">
            <div class="uk-form-controls uk-width-2-5">
                <input type="text" id="divx_previewmessagefontsize">
            </div>
            <label for="divx_minversion" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_DIVX_MINVERSION'); ?></label>
            <div class="uk-form-controls uk-width-2-5">
                <input type="text" id="divx_minversion">
            </div>
        </div>
    </div>

    <div class="uk-form-row uk-grid uk-grid-small">
        <label for="divx_previewimage" class="uk-form-label uk-width-1-5"><?php echo JText::_('WF_MEDIAMANAGER_DIVX_PREVIEWIMAGE'); ?></label>
        <div class="uk-form-controls uk-width-4-5">
            <input type="text" id="divx_previewimage" class="browser image">
        </div>
    </div>

    <h6 class="notice">DivX and the associated DivX logos are trademarks of DivX, LLC, a subsidiary of Rovi Corporation</h6>
</div>