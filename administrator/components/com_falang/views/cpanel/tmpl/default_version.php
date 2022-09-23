<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

?>
<div style="padding: 5px;">

    <table class="adminlist">
        <tr>
            <th width="120" align="left"><?php echo JText::_('COM_FALANG_CPANEL_VERSION_TYPE'); ?>:</th>
            <td><?php echo $this->versionType; ?></td>
        </tr>
        <tr>
        <th width="120" align="left"><?php echo JText::_('COM_FALANG_CPANEL_CURRENT_VERSION'); ?>:</th>
            <td><?php echo $this->currentVersion; ?></td>
        </tr>
        <tr>
            <th width="120" align="left"><?php echo JText::_('COM_FALANG_CPANEL_LATEST_VERSION'); ?>:</th>
                <td><div id="falang-last-version"><?php
                    if (version_compare($this->latestVersion,$this->currentVersion,'>' )) {
                        ?><span class="update-msg-new"><?php
                            echo $this->latestVersion;
                            ?></span><?php
                    } else {
                        echo $this->currentVersion;
                    }
                    ?>
                <?php if ($this->updateInfo->hasUpdate) {?>
                        <span class="update-msg-new"><?php echo JText::_('COM_FALANG_CPANEL_OLD_VERSION'); ?>
                            <a href="index.php?option=com_installer&view=update&filter[search]=falang&filter[type]=package"><?php echo JText::_('COM_FALANG_CPANEL_UPDATE_LINK'); ?></a>
                        </span>
                     <?php } else { ?>
                            <span class="update-msg-info"><?php echo JText::_('COM_FALANG_CPANEL_LATEST_VERSION'); ?></span>
                     <?php } ?>
                </div>
                </td>
        </tr>
        <tr>
            <th>
            </th>
            <td>
                <!-- display check button only if no update is reported -->
                <?php if (!$this->updateInfo->hasUpdate) {?>
                    <input type="button" value="<?php echo JText::_('COM_FALANG_CPANEL_CHECK_UPDATES'); ?>" onclick="checkUpdates();">
                    <span id="falang-update-progress"></span>
                <?php } ?>
            </td>
        </tr>
    </table>
    <div id="updatescontent" class="updates"></div>
</div>