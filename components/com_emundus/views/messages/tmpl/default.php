<?php
/**
 * @package    Joomla
 * @subpackage emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Hugo Moracchini
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$lastId = $this->message_contacts[0]->message_id;
$id = JFactory::getApplication()->input->get->get('chatid',null);
if(empty($id)) {
    if ($this->message_contacts[0]->user_id_to == $this->user_id)
        $id = $this->message_contacts[0]->user_id_from;
    else
        $id = $this->message_contacts[0]->user_id_to;
}

?>

<div class="showContent" id="em-contacts">


    <?php if (empty($this->message_contacts)) :?>
        <div class="no-messages"><?php echo JText::_('NO_MESSAGES'); ?></div>
    <?php else :?>
    <ul id="em-message-list">
        <?php foreach ($this->message_contacts as $message_contact) :?>

            <?php if ($message_contact->user_id_to == $this->user_id) :?>
                <li  class="em-list-item" id="em-contact-<?php echo $message_contact->user_id_from ; ?>">
                        <div class="contact-photo contact-photo-<?php echo str_replace(' ', '-', $message_contact->profile_from) ?>"></div>
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
                </li>
            <hr>
            <?php endif; ?>

            <?php if ($message_contact->user_id_from == $this->user_id) :?>
                <li class="em-list-item" id="em-contact-<?php echo $message_contact->user_id_to ; ?>">
                    <?php if ($message_contact->photo_to == null) :?>
                        <div class="contact-photo contact-photo-<?php echo str_replace(' ', '-', $message_contact->profile_to) ?>"></div>
                    <?php endif; ?>
                    <div class="em-contact" id="em-contact-<?php echo $message_contact->user_id_to ; ?>">
                            <p class="read-contact" id="contact-<?php echo $message_contact->user_id_from ; ?>-name"><?php echo $message_contact->name_to ." : "; ?></p>
                            <p class="read-contact" id="contact-<?php echo $message_contact->user_id_from ; ?>-date"> <?php echo date("d/m/Y", strtotime($message_contact->date_time)) ;?></p>
                            <p class="read-contact contact-message" id="contact-<?php echo $message_contact->user_id_from ; ?>-message"><?php echo strip_tags($message_contact->message) ;?></p>
                    </div>
                </li>
            <hr>
            <?php endif; ?>

        <?php endforeach ; ?>
    </ul>
    <?php endif; ?>
</div>

<div id="em-chat" class="em-chat-<?php echo $id; ?>"><img src="media\com_emundus\images\icones\loader-line.gif" id="em-loader"/></div>


<script type="text/javascript">

    var lastId = '<?php echo $lastId; ?>';

    function updateMessages() {

        var chatClass = document.getElementById("em-chat").className.match(/\d+/)[0];

        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=messages&task=updatemessages',
            data : {
                id : lastId
            },
            success: function (result) {
                result = JSON.parse(result);
                if(result.status == 'true') {
                    lastId = result.messages[0].message_id;
                    for( key in result.messages) {
                        var user_from = result.messages[key].user_id_from;
                        $('#contact-'+user_from+'-name').removeClass('read-contact');
                        $('#contact-'+user_from+'-name').addClass('unread-contact');

                        $('#contact-'+user_from+'-date').removeClass('read-contact');
                        $('#contact-'+user_from+'-date').addClass('unread-contact');
                        $('#contact-'+user_from+'-date').text(result.messages[key].date_time);

                        $('#contact-'+user_from+'-message').removeClass('read-contact');
                        $('#contact-'+user_from+'-message').addClass('unread-contact');
                        $('#contact-'+user_from+'-message').text(result.messages[key].message);

                        $('#em-contact-'+user_from).prependTo('#em-message-list');

                        if(user_from = chatClass) {
                            var messageList = $('.message-list');
                            var contactMessage = document.getElementById('contact-message');
                            tinyMCE.activeEditor.setContent('');

                            messageList.append('<li><div class="em-message-bubble em-contact-left"><p style="margin-top: 15px; margin-bottom: 15px !important;">'+ result.messages[key].message + '</p></div></li><hr id="separator">');

                            $('#em-messagerie').scrollTop($('#em-messagerie')[0].scrollHeight);
                        }
                    }
                }
            },
            error: function () {
                // handle error
                $("#em-contacts").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
            }
        });

    }
    $(document).ready(function() {
        var chat = document.getElementById("chat");
        var chat2 = document.getElementById("em-chat");
        jQuery(chat).toggleClass('hideChat');
        jQuery(chat2).toggleClass('hideChat');

        setInterval(updateMessages, 10000);
        var id = '<?php echo $id; ?>';
        if(id != null && id != '') {
            $.ajax({
                type: 'POST',
                url: 'index.php?option=com_emundus&view=messages&format=raw&layout=chat',
                data : {
                    id : id
                },
                success: function (result) {

                    $('#em-chat').html(result);
                    var active = $('#em-contact-<?php echo $id; ?>');
                    var icon = document.getElementById('unread-icon');
                    var boldName = document.getElementById('contact-'+id+'-name');
                    var boldDate = document.getElementById('contact-'+id+'-date');
                    var boldMessage = document.getElementById('contact-'+id+'-message');
                    active.addClass('active');
                    if(icon && boldName && boldDate && boldMessage) {
                        icon.parentNode.removeChild(icon);
                        $(boldName).removeClass('unread-contact').addClass('read-contact');
                        $(boldDate).removeClass('unread-contact').addClass('read-contact');
                        $(boldMessage).removeClass('unread-contact').addClass('read-contact');
                    }
                },
                error: function () {
                    // handle error
                    $("#em-messages").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
                }
            });
        }
        else {
            var message_icon = '<i class="comments outline icon" id ="em-chat-no-message"></i>';
            $('#em-loader').hide();
            $('#em-chat').css({'backgroundColor' : '#f0f0f0', 'height': '750px'});
            $('#em-chat').append(message_icon);
        }


    });

    $('.em-list-item').on("click", function() {
        var active = $(this);
        var contactList = document.getElementById("em-contacts");
        var chat = document.getElementById("chat");
        var chat2 = document.getElementById("em-chat");


        if(contactList.className === 'hideContent'){
            //  document.getElementById("em-loader").style.display = "none";
            jQuery(contactList).toggleClass('showContent');
            jQuery(contactList).toggleClass('hideContent');
            jQuery(chat).toggleClass('showChat');
            jQuery(chat).toggleClass('hideChat');
            jQuery(chat2).toggleClass('showChat');
            jQuery(chat2).toggleClass('hideChat');
        } else {
            jQuery(contactList).toggleClass('showContent');
            jQuery(contactList).toggleClass('hideContent');
            jQuery(chat).toggleClass('showChat');
            jQuery(chat).toggleClass('hideChat');
            jQuery(chat2).toggleClass('showChat');
            jQuery(chat2).toggleClass('hideChat');
        }

        $('#em-chat').html("<img src='media/com_emundus/images/icones/loader-line.gif' class='hideLoader' id='em-loader'/>");
        var chatClass = document.getElementById("em-chat").className;
        var id = $(this)[0].id;
        id = id.match(/\d+/)[0];

        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&view=messages&format=raw&layout=chat',
            data : {
                id : id
            },
            success: function (result) {

                $('#em-chat').html(result);

                if(!active.hasClass('active')){
                    $('.em-list-item').removeClass('active');
                    active.addClass('active');
                }
                $('#em-chat').toggleClass(chatClass);
                $('#em-chat').toggleClass('em-chat-'+id);

                jQuery(chat2).toggleClass('showChat');
                //var icon = document.getElementById('unread-icon');
                var boldName = document.getElementById('contact-'+id+'-name');
                var boldDate = document.getElementById('contact-'+id+'-date');
                var boldMessage = document.getElementById('contact-'+id+'-message');
                if( boldName && boldDate && boldMessage) {
                    //icon.parentNode.removeChild(icon);
                    $(boldName).removeClass('unread-contact').addClass('read-contact');
                    $(boldDate).removeClass('unread-contact').addClass('read-contact');
                    $(boldMessage).removeClass('unread-contact').addClass('read-contact');
                }
            },
            error: function () {
                // handle error
                $("#em-messages").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
            }
        });
    });


</script>
