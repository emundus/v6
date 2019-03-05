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

<!-- Button which opens up the dropdown menu. -->
<div class='dropdown' id="userDropdown">
    <div class="em-user-dropdown-button" id="userDropdownLabel" aria-haspopup="true" aria-expanded="false">
        <i class="big circular user outline icon" id="userDropdownIcon"></i>
    </div>
    <ul class="dropdown-menu dropdown-menu-right" id="userDropdownMenu" aria-labelledby="userDropdownLabel">
        <li class="dropdown-header"><?php echo $user->name; ?></li>
        <li class="dropdown-header"><?php echo $user->email; ?></li>
        <?php if (!empty($list)) :?>
            <li role="separator" class="divider"></li>
            <?php foreach ($list as $i => $item) :?>
                <li class="<?php echo ($item->id == $active_id)?'active':''; ?>"><a href="<?php echo $item->flink ?>" <?php echo ($item->browserNav == 1)?'target="_blank"':''; ?>><?php echo $item->title; ?></a></li>
            <?php endforeach; ?>
        <?php endif; ?>
        <li role="separator" class="divider"></li>
        <?php
            $userToken = JSession::getFormToken();
            echo '<li><a href="/index.php?option=com_users&task=user.logout&' . $userToken . '=1">'.JText::_('LOGOUT').'</a></li>';
        ?>
    </ul>
</div>

<script>
    // This counters all of the issues linked to using BootstrapJS.
    document.getElementById('userDropdownLabel').addEventListener('click', function (e) {
        e.stopPropagation();
        var dropdown = document.getElementById('userDropdown');
        var icon = document.getElementById('userDropdownIcon');

        // get message module elements
        var messageDropdown = document.getElementById('messageDropdown');
        var messageIcon = document.getElementById('messageDropdownIcon');

        if (dropdown.classList.contains('open')) {
            dropdown.classList.remove('open');
            icon.classList.remove('active');
        } else {
            // remove message classes if message module is on page
            if(messageDropdown||messageIcon) {
                messageDropdown.classList.remove('open');
                messageIcon.classList.remove('active');
                messageIcon.classList.remove('open');
            }
            dropdown.classList.add('open');
            icon.classList.add('open');
        }
    });

    document.addEventListener('click', function (e) {
        e.stopPropagation();
        var dropdown = document.getElementById('userDropdown');
        var icon = document.getElementById('userDropdownIcon');

        if (dropdown.classList.contains('open')) {
            dropdown.classList.remove('open');
            icon.classList.remove('active');
        }
    });
</script>

