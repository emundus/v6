<?php
/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2016 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_WF_EXT') or die('RESTRICTED');
?>
<table border="0" cellpadding="3" cellspacing="0">
    <tr>
        <td><label for="widgetkit_lightbox_title" class="hastip" title="<?php echo WFText::_('WF_POPUPS_WIDGETKIT_BOXTITLE_DESC'); ?>"><?php echo WFText::_('WF_POPUPS_WIDGETKIT_BOXTITLE'); ?></label></td>
        <td colspan="3"><input id="widgetkit_lightbox_title" type="text" class="text" value="" /></td>
    </tr>
    <tr>
        <td><label for="widgetkit_lightbox_group" class="hastip" title="<?php echo WFText::_('WF_POPUPS_WIDGETKIT_GROUP_DESC'); ?>"><?php echo WFText::_('WF_POPUPS_WIDGETKIT_GROUP'); ?></label></td>
        <td colspan="3"><input id="widgetkit_lightbox_group" type="text" class="text" value="" /></td>
    </tr>
    <tr>
        <td><label for="widgetkit_lightbox_type" class="hastip" title="<?php echo WFText::_('WF_POPUPS_WIDGETKIT_TYPE_DESC'); ?>"><?php echo WFText::_('WF_POPUPS_WIDGETKIT_TYPE'); ?></label></td>
        <td>
            <select id="widgetkit_lightbox_type">
                <option value=""><?php echo WFText::_('WF_POPUPS_WIDGETKIT_DETECT'); ?></option>
                <option value="image"><?php echo WFText::_('WF_POPUPS_WIDGETKIT_IMAGE'); ?></option>
                <option value="video"><?php echo WFText::_('WF_POPUPS_WIDGETKIT_VIDEO'); ?></option>
                <option value="youtube"><?php echo WFText::_('WF_POPUPS_WIDGETKIT_YOUTUBE'); ?></option>
                <option value="vimeo"><?php echo WFText::_('WF_POPUPS_WIDGETKIT_VIMEO'); ?></option>
                <!--option value="iframe"><?php echo WFText::_('WF_POPUPS_WIDGETKIT_IFRAME'); ?></option-->
            </select>
        </td>
    </tr>
</table>