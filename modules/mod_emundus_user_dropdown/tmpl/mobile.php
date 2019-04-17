<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
?>

<div class="user-menu-phone">
    <div class="content">
        <ul>
            <?php if (!empty($list)) :?>
                <?php foreach ($list as $i => $item) :?>
                    <li class="<?php echo ($item->id == $active_id)?'active':''; ?>"><a href="<?php echo $item->flink ?>" <?php echo ($item->browserNav == 1)?'target="_blank"':''; ?>><?php echo $item->title; ?></a></li>
                <?php endforeach; ?>
                <li role="separator" class="divider"></li>
            <?php endif; ?>
            <li><a href="index.php?option=com_users&task=user.logout&<?php echo JSession::getFormToken(); ?>=1" class="logout-phone-btn" title="<?php echo JText::_('COM_EMUNDUS_USER_MENU_LOGOUT_TITLE'); ?>"><?php echo JText::_('LOGOUT'); ?></a></li>
        </ul>
    </div>
</div>
