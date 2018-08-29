<?php
/**
 * Created by PhpStorm.
 * User: emundus
 * Date: 29/08/2018
 * Time: 12:12
 */


?>

<style>

    #em-contacts {
        width: 340px;
        overflow-y: scroll;
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
        margin-top: -8px;
        display: inline-block;
    }

    .contact-message {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .em-contact {
        height: 90px;
        margin-top: 10px;
        margin-left: 15px;
        float: left;
        width: 60%;
        border-bottom: 1px solid #e5e5e5;
    }

    #em-contacts li {
        margin-bottom: -30px;
        height: 110px;
        display: block;
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

<div id="em-contacts">
    <?php if (empty($message_contacts)) :?>
        <div class="no-messages"><?php echo JText::_('NO_MESSAGES'); ?></div>
    <?php else :?>
        <ul id="em-message-list">
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
        </ul>
    <?php endif; ?>
</div>

<script type="text/javascript">

</script>
