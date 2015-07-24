<?php 
/*
*/
defined('_JEXEC') or die('Restricted access');
?>
<div style="padding: 5px;">
    <table class="adminlist">
            <th width="120"><?php echo JText::_('COM_FALANG_CPANEL_CURRENT_VERSION'); ?>:</th>
            <td><?php echo $this->currentVersion; ?></td>
        </tr>
        <tr>
            <th><?php echo JText::_('COM_FALANG_CPANEL_LATEST_VERSION'); ?>:</th>
                <td><div id="falang-last-version"><?php
                    if (version_compare($this->latestVersion,$this->currentVersion,'>' )) {
                        ?><span class="update-msg-new"><?php
                            echo $this->latestVersion;
                            ?></span><?php
                    } else {
                        echo $this->currentVersion;
                    }
                    ?>
                <?php if ($this->updateInfo->hasUpdates) {?>
                        <span class="update-msg-new"><?php echo JText::_('COM_FALANG_CPANEL_OLD_VERSION'); ?>
                            <a href="index.php?option=com_falang&view=liveupdate"/><?php echo JText::_('COM_FALANG_CPANEL_UPDATE_LINK'); ?></a>
                        </span>
                     <?php } else { ?>
                    <span class="update-msg-info"><?php echo JText::_('COM_FALANG_CPANEL_LATEST_VERSION'); ?></span>
                     <?php } ?>
                <div>
                </td>
        </tr>
        <tr>
            <th>
            </th>
            <td>
                <input type="button" value="<?php echo JText::_('COM_FALANG_CPANEL_CHECK_UPDATES'); ?>" onclick="checkUpdates();">
                <span id="falang-update-progress"></span>
            </td>
        </tr>
        <tr>
    </table>
    <div id="updatescontent" class="updates"></div>
</div>