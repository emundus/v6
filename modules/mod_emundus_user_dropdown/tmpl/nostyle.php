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
if($user != null) {
?>

<?= $intro; ?>

<!-- Button which opens up the dropdown menu. -->
<div class='dropdown' id="userDropdown">
    <div class="em-user-dropdown-button" id="userDropdownLabel" aria-haspopup="true" aria-expanded="false">
        <i class="<?= $icon;?>" id="userDropdownIcon"></i>
    </div>
    <ul class="dropdown-menu dropdown-menu-right" id="userDropdownMenu" aria-labelledby="userDropdownLabel">
        <li class="dropdown-header"><?= $user->name; ?></li>
        <li class="dropdown-header"><?= $user->email; ?></li>
        <?php if (!empty($list)) :?>
            <li role="separator" class="divider"></li>
            <?php foreach ($list as $i => $item) :?>
                <li class="<?= ($item->id == $active_id)?'active':''; ?>"><a href="<?= $item->flink; ?>" <?= ($item->browserNav == 1)?'target="_blank"':''; ?>><?= $item->title; ?></a></li>
            <?php endforeach; ?>
        <?php endif; ?>
	    <?php if ($show_logout == '1') :?>
            <li role="separator" class="divider"></li>
            <?= '<li><a href="index.php?option=com_users&task=user.logout&'.JSession::getFormToken().'=1">'.JText::_('LOGOUT').'</a></li>'; ?>
        <?php endif; ?>
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
<?php } else { ?>
<div class="header-right" style="text-align: right;">
	<a class="btn btn-danger" href="<?= $link_login; ?>" data-toggle="sc-modal"><?= JText::_('CONNEXION_LABEL'); ?></a>
	<?php if ($show_registration) { ?>
		<a class="btn btn-danger btn-creer-compte" href="<?= $link_register; ?>" data-toggle="sc-modal"><?= JText::_('CREATE_ACCOUNT_LABEL'); ?></a>
	<?php } ?>
	<br />
	<a href="<?= $link_forgotten_password; ?>"><?= JText::_('FORGOTTEN_PASSWORD_LABEL'); ?></a>
</div>
<?php } ?>
