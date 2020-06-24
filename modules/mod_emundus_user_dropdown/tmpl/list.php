<?php
/**
 * Created by PhpStorm.
 * User: imacemundus
 * Date: 2018-12-20
 * Time: 14:04
 */

?>

<?= $intro; ?>

<div class="user-list-menu">
    <div class="content">
        <ul>
            <?php if (!empty($list)) :?>
                <?php foreach ($list as $i => $item) :?>
                    <li class="<?php echo ($item->id)?'menu-item':''; echo ($item->id == $active_id)?' menu-item-active':''; ?>"><a href="<?php echo $item->flink ?>"><?php echo $item->title; ?></a></li>
                <?php endforeach; ?>
            <?php endif; ?>
	        <?php if ($show_logout == '1') :?>
                <?= '<li class="user-logout"><a href="index.php?option=com_users&task=user.logout&'.JSession::getFormToken().'=1">'.JText::_('LOGOUT').'</a></li>'; ?>
            <?php endif; ?>
        </ul>
    </div>
</div>