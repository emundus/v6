<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$notif = 0;
// Note. It is important to remove spaces between elements.
?>


<!-- Button which opens up the dropdown menu. -->
<div class='dropdown' id="messageDropdown" style="float: right;">

    <div class="em-message-dropdown-button" id="messageDropdownLabel" aria-haspopup="true" aria-expanded="false">
        <i class="big circular envelope outline icon" id="messageDropdownIcon"></i>
        <?php if (!empty($message_contacts)) :?>

            <?php foreach ($message_contacts as $message_notif) :?>
                <?php if ($message_notif->state == '1')
                    $notif= $notif+1;
                ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($notif > 0) :?>
            <div id="new-message-notif"><?php echo $notif; ?></div>
        <?php endif; ?>
    </div>
    <ul class="dropdown-menu dropdown-menu-right" id="em-message-list">
        <?php if (empty($message_contacts)) :?>
            <li><p style="text-align: center"><?php echo JText::_("NO_MESSAGES");?></p></li>
        <?php else:?>
            <?php foreach ($message_contacts as $message_contact) :?>

            <?php if ($message_contact->user_id_to == $user->id) :?>
                <li  class="em-list-item" id="em-contact-<?php echo $message_contact->user_id_from ; ?>">
                    <a class="linkToMessage" href="/index.php?option=com_emundus&view=messages&chatid=<?php echo $message_contact->user_id_from ; ?>">
                    <?php if ($message_contact->photo_from == null) :?>
                        <div class="contact-photo contact-photo-<?php echo str_replace(' ', '-', $message_contact->profile_from) ?>"></div>
                    <?php endif; ?>
                    <div class="em-contact" >
                        <?php if ($message_contact->state == 1) :?>
                            <p class="unread-contact" id="contact-<?php echo $message_contact->user_id_from ; ?>-name"><i class="circle outline" id="unread-icon"></i><?php  echo $message_contact->name_from ." : " ; ?></p>
                            <p class='unread-contact' id="contact-<?php echo $message_contact->user_id_from ; ?>-date"><?php echo date("d/m/Y", strtotime($message_contact->date_time)) ;?></p>
                            <p class="unread-contact contact-message" id="contact-<?php echo $message_contact->user_id_from ; ?>-message"><?php echo strip_tags($message_contact->message)  ;?></p>
                        <?php else :?>
                            <p class="read-contact" id="contact-<?php echo $message_contact->user_id_from ; ?>-name"><?php  echo $message_contact->name_from ." : " ; ?></p>
                            <p class="read-contact" id="contact-<?php echo $message_contact->user_id_from ; ?>-date"><?php echo date("d/m/Y", strtotime($message_contact->date_time)) ; ?></p>
                            <p class='read-contact contact-message' id="contact-<?php echo $message_contact->user_id_from ; ?>-message"><?php echo strip_tags($message_contact->message)  ;?></p>
                        <?php endif; ?>
                    </div>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($message_contact->user_id_from == $user->id) :?>
                <li id="em-contact-<?php echo $message_contact->user_id_to ; ?>">
                    <a class="linkToMessage" href="/index.php?option=com_emundus&view=messages&chatid=<?php echo $message_contact->user_id_to ; ?>">
                    <?php if ($message_contact->photo_to == null) :?>
                        <div class="contact-photo contact-photo-<?php echo str_replace(' ', '-', $message_contact->profile_to) ?>"></div>
                    <?php endif; ?>
                    <div class="em-contact" id="em-contact-<?php echo $message_contact->user_id_to ; ?>">
                        <p class="read-contact" id="contact-<?php echo $message_contact->user_id_from ; ?>-name"><?php echo $message_contact->name_to ." : "; ?></p>
                        <p class="read-contact" id="contact-<?php echo $message_contact->user_id_from ; ?>-date"> <?php echo date("d/m/Y", strtotime($message_contact->date_time)) ;?></p>
                        <p class="read-contact contact-message" id="contact-<?php echo $message_contact->user_id_from ; ?>-message"><?php echo strip_tags($message_contact->message) ;?></p>
                    </div>
                    </a>
                </li>
            <?php endif; ?>

        <?php endforeach ; ?>
            <li class="em-list-item "><a class="em-link-messages" href="/index.php?option=com_emundus&view=messages"><?php echo JText::_('SHOW_ALL'); ?></a></li>
        <?php endif;?>

    </ul>
</div>


<script type="text/javascript">
    // This counters all of the issues linked to using BootstrapJS.
    document.getElementById('messageDropdownLabel').addEventListener('click', function (e) {
        e.stopPropagation();
        var dropdown = document.getElementById('messageDropdown');
        var icon = document.getElementById('messageDropdownIcon');

        // get user_dropdown module elements
        var userDropdown = document.getElementById('userDropdown');
        var userIcon = document.getElementById('userDropdownIcon');

        if (dropdown.classList.contains('open')) {
            dropdown.classList.remove('open');
            icon.classList.remove('active');
            icon.classList.remove('open');
        } else {
            // remove message classes if message module is on page
            if(userDropdown||userIcon) {
                userDropdown.classList.remove('open');
                userIcon.classList.remove('active');
                userIcon.classList.remove('open');
            }
            dropdown.classList.add('open');
            icon.classList.add('open');
        }
    });

    document.addEventListener('click', function (e) {
        e.stopPropagation();
        var dropdown = document.getElementById('messageDropdown');
        var icon = document.getElementById('messageDropdownIcon');

        if (dropdown.classList.contains('open')) {
            dropdown.classList.remove('open');
            icon.classList.remove('active');
            icon.classList.remove('open');
        }
    });



</script>

