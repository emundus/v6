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


<style>
    .em-message-notification {
        width: 100%;
        height: 50px;
        background-color: #e7f6fc;
        border-radius: 15px;
    }

    .em-message-notification p {
        text-align: center;
        padding-top: 13px;
        color: #0B61A4;
    }
</style>



<div class='em-message-notification'>
    <?php if ($messages == "1") :?>
        <p><a href="/index.php?option=com_emundus&view=messages"> <?php echo JText::_("YOU_HAVE") . $messages . JText::_("ONE_MESSAGE") ;?></a></p>
    <? else:?>
        <p><a href="/index.php?option=com_emundus&view=messages"> <?php echo JText::_("YOU_HAVE") . $messages . JText::_("MORE_MESSAGES");?></a></p>
    <?php endif;?>
</div>

