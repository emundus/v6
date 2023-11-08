<?php
/**
 * Created by PhpStorm.
 * User: imacemundus
 * Date: 2018-12-20
 * Time: 14:04
 */

?>

<?= $intro; ?>

<?php
// No direct access.
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_user_dropdown/style/mod_emundus_user_dropdown.css");
// Note. It is important to remove spaces between elements.

if ($user != null) {
	?>
    <div class="user-list-menu">
        <div class="content">
            <ul>
				<?php if (!empty($list)) : ?>
					<?php foreach ($list as $i => $item) : ?>
                        <li class="<?= ($item->id) ? 'menu-item' : '';
						echo ($item->id == $active_id) ? ' menu-item-active' : ''; ?>"><a
                                    href="<?= $item->flink ?>"><?= $item->title; ?></a></li>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if ($show_logout == '1') : ?>
					<?= '<li class="user-logout"><a href="index.php?option=com_users&task=user.logout&' . JSession::getFormToken() . '=1">' . JText::_('LOGOUT') . '</a></li>'; ?>
				<?php endif; ?>
            </ul>
        </div>
    </div>
<?php } else { ?>
    <div class="user-list-menu">
        <div class="content">
            <ul>
                <li class="user-logout"><a href="<?= $link_login; ?>"><?= JText::_('CONNEXION_LABEL'); ?></a></li>
				<?php if ($show_registration) { ?>
                    <li class="user-logout"><a
                                href="<?= $link_register; ?>"><?= JText::_('CREATE_ACCOUNT_LABEL'); ?></a></li>
				<?php } ?>
                <li class="user-logout"><a
                            href="<?= $link_forgotten_password; ?>"><?= JText::_('FORGOTTEN_PASSWORD_LABEL'); ?></a>
                </li>
            </ul>
        </div>
    </div>
<?php } ?>