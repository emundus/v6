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
            <li><a href="/component/users/profile?layout=edit" class="profile-btn-phone" title="<?php echo JText::_('COM_EMUNDUS_USER_MENU_MY_ACCOUNT_TITLE'); ?>"><?php echo JText::_('COM_EMUNDUS_USER_MENU_MY_ACCOUNT'); ?></a></li>
            <li><a href="/index.php?option=com_users&task=user.logout&<?php echo JSession::getFormToken(); ?>=1" class="logout-phone-btn" title="<?php echo JText::_('COM_EMUNDUS_USER_MENU_LOGOUT_TITLE'); ?>"><?php echo JText::_('LOGOUT'); ?></a></li>
        </ul>
    </div>
</div>
