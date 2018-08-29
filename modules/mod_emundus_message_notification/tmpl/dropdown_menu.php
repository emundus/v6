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

    #messageDropdown {
        margin-top: -30px;
    }

    #messageDropdownIcon {
        background-color: #<?php echo $primary_color; ?>;
        color: #<?php echo $secondary_color; ?>;
        cursor: pointer;

    }

    #messageDropdownIcon:hover,
    #messageDropdownIcon.active {
        //border: 1px solid;
        box-shadow: inset 0 0 20px rgba(255, 255, 255, .5), 0 0 20px rgba(255, 255, 255, .2);
        outline-color: rgba(255, 255, 255, 0);
        outline-offset: 15px;
        background-color: #<?php echo $secondary_color; ?>;
        color: #<?php echo $primary_color; ?>;
    }

    .contact-photo {
        height: 80px;
        width: 80px;
        float: left;
        margin-top: 14px;
        background-color: #f0f0f0;
        border-radius: 60px;
    }


    .contact-photo:before {
        font-family: 'FontAwesome';
        color: #909090;
        content: "\f007";
        top: 0;
        font-size: 3rem;
        margin-left: 23px;
        margin-top: 27px;
        display: inline-block;
    }

    .contact-message {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #em-chat {
        float: right;
        width: 75%;
        height: 600px;
        scroll-behavior: auto;
    }

    .em-contact {
        height: 90px;
        margin-top: 10px;
        margin-left: 15px;
        float: left;
        width: 50%;
    }

    #em-contacts li {
        margin-bottom: -30px;
        height: 110px;
    }

    .em-contact p {
        margin-top: 7px;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 10px;
    }


    .unread-contact {font-weight: bold;}
    .read-contact {font-weight: normal;}

</style>

<!-- Button which opens up the dropdown menu. -->
<div class='dropdown' id="messageDropdown" style="float: right;">
    <div class="em-message-dropdown-button" id="messageDropdownLabel" aria-haspopup="true" aria-expanded="false">
        <i class="big circular envelope outline icon" id="messageDropdownIcon"></i>
    </div>
    <ul class="dropdown-menu dropdown-menu-right" id="em-message-list">
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
                    <a class="linkToMessage" href="/index.php?option=com_emundus&view=messages&chatid=<?php echo $message_contact->user_id_from ; ?>">
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

