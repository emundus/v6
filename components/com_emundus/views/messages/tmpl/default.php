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


<div id="em-contacts">
    <?php if (empty($this->message_contacts)) :?>
        <div class="no-messages"><?php echo JText::_('NO_MESSAGES'); ?></div>
    <?php else :?>
    <ul id="em-message-list">
        <?php foreach ($this->message_contacts as $message_contact) :?>

            <?php if ($message_contact->user_id_to == $this->user_id) :?>
                <li  class="em-list-item" id="em-contact-<?php echo $message_contact->user_id_from ; ?>">
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
                </li>
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
                    var icon = document.getElementById('unread-icon');
                    var boldName = document.getElementById('contact-'+id+'-name');
                    var boldDate = document.getElementById('contact-'+id+'-date');
                    var boldMessage = document.getElementById('contact-'+id+'-message');
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
        $('#em-chat').html("<img src='media/com_emundus/images/icones/loader-line.gif' id='em-loader'/>");
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
                $('#em-chat').removeClass(chatClass);
                $('#em-chat').addClass('em-chat-'+id);


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

<style>
    #em-contacts {
        float: left;
        width: 25%;
        height: 750px;
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
        margin-top: -10px;
        display: inline-block;
    }

    #unread-icon {
        font-size: 1em;
        color: #00AF64;
        margin-right: 5px;
    }

    #em-chat-no-message {
        font-size: 150px;
        display: block;
        margin-left: auto;
        margin-right: auto;
        margin-top: 200px;
        color: #909090;
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
        width: 60%;
        border-bottom: 1px solid #e5e5e5;
    }

    #em-contacts li {
        margin-bottom: -30px;
        height: 110px;
        cursor: pointer;
    }

    .em-contact p {
        margin-top: 7px;
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #em-loader{
        margin-left: auto;
        margin-right: auto;
        margin-top: 250px;
        display: block;
    }

    .unread-contact {font-weight: bold;}
    .read-contact {font-weight: normal;}

    .em-message-bubble {
        border-width: 1px;
        margin-left: 1%;
        max-width: 80%;
        list-style-position: inside;
        padding-left: 10px;
        padding-right: 10px;
    }

    .em-contact-left {
        float: left;
        display: inline-block;
        border-radius: 75px 75px 75px 0px;
        -moz-border-radius: 75px 75px 75px 0px;
        -webkit-border-radius: 75px 75px 75px 0px;
        border: 2px solid #17693d;
    }

    .em-contact-right {
        float: right;
        display: inline-block;
        border-radius: 75px 75px 0px 75px;
        -moz-border-radius: 75px 75px 0px 75px;
        -webkit-border-radius: 75px 75px 0px 75px;
        border: 2px solid #0b64b3;
        margin-right: 5px;

    }

    .em-message-bubble p {
        word-wrap: break-word;
        margin-left: 10px;
        margin-right: 10px;
    }

    .em-message-bubble img{
        border-radius: 60px;
    }

</style>
