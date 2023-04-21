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
        max-height: 310px;
        min-height: 110px;
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

    .em-link-messages {
        display: block;
        margin-right: auto;
        margin-left: auto;
        padding-top: 50px;
        text-align: center;
        text-decoration: underline;
    }

    .unread-contact {font-weight: bold;}
    .read-contact {font-weight: normal;}

    .no-messages {
        background-color: #e5e5e5;
        padding: 10px;
        width: 310px;
    }
</style>
<?php if (empty($message_contacts) || empty($messages)) :?>
    <div class="no-messages"><?php echo JText::_('NO_NEW_MESSAGES'); ?></div>
<?php else :?>
    <div id="em-contacts">
        <ul id="em-message-list">
            <?php foreach ($message_contacts as $message_contact) :?>
                <?php if ($message_contact->state == 1) :?>
                    <?php if ($message_contact->user_id_to == $user->id) :?>
                        <li  class="em-list-item" id="em-contact-<?php echo $message_contact->user_id_from ; ?>">
                            <a class="linkToMessage" href="/index.php?option=com_emundus&view=messages&chatid=<?php echo $message_contact->user_id_from ; ?>">
                                <?php if ($message_contact->photo_from == null) :?>
                                    <div class="contact-photo contact-photo-<?php echo str_replace(' ', '-', $message_contact->profile_from) ?>"></div>
                                <?php endif; ?>
                                <div class="em-contact" >

                                        <p class="unread-contact" id="contact-<?php echo $message_contact->user_id_from ; ?>-name"><i class="circle outline" id="unread-icon"></i><?php  echo $message_contact->name_from ." : " ; ?></p>
                                        <p class='unread-contact' id="contact-<?php echo $message_contact->user_id_from ; ?>-date"><?php echo date("d/m/Y", strtotime($message_contact->date_time)) ;?></p>
                                        <p class="unread-contact contact-message" id="contact-<?php echo $message_contact->user_id_from ; ?>-message"><?php echo strip_tags($message_contact->message)  ;?></p>

                                </div>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach ; ?>
            <li class="em-list-item"><a class="em-link-messages" href="/index.php?option=com_emundus&view=messages"><?php echo JText::_('SHOW_ALL'); ?></a></li>
        </ul>
</div>
<?php endif; ?>
<script type="text/javascript">

</script>
