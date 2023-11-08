<?php
/**
 * @package        Joomla.Site
 * @subpackage     mod_menu
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Note. It is important to remove spaces between elements.
if ($user != null) {
	?>

    <div class="user-menu-phone">
        <div class="content">
            <ul>
				<?php if (!empty($list)) : ?>
					<?php foreach ($list as $i => $item) : ?>
                        <li class="<?= ($item->id == $active_id) ? 'active' : ''; ?>"><a
                                    href="<?= $item->flink ?>" <?= ($item->browserNav == 1) ? 'target="_blank"' : ''; ?>><?= $item->title; ?></a>
                        </li>
					<?php endforeach; ?>

				<?php endif; ?>
				<?php if ($show_logout == '1') : ?>
                    <li><a href="index.php?option=com_users&task=user.logout&<?= JSession::getFormToken(); ?>=1"
                           class="logout-phone-btn"
                           title="<?= JText::_('COM_EMUNDUS_USER_MENU_LOGOUT_TITLE'); ?>"><?= JText::_('LOGOUT'); ?></a>
                    </li>
				<?php endif; ?>
            </ul>
        </div>
    </div>
<?php } else { ?>
    <div class="user-list-menu">
        <div class="content">
            <ul>
                <li><a href="<?= $link_login; ?>"><?= JText::_('CONNEXION_LABEL'); ?></a></li>
				<?php if ($show_registration) { ?>
                    <li><a href="<?= $link_register; ?>"><?= JText::_('CREATE_ACCOUNT_LABEL'); ?></a></li>
				<?php } ?>
                <li><a href="<?= $link_forgotten_password; ?>"><?= JText::_('FORGOTTEN_PASSWORD_LABEL'); ?></a></li>
            </ul>
        </div>
    </div>
<?php } ?>
