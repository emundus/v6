<?php
/**
 * Created by PhpStorm.
 * User: emundus
 * Date: 21/08/2018
 * Time: 11:52
 * @author Hugo Moracchini
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$lastId = 0;

JFactory::getDocument()->addStyleSheet("media/com_emundus/css/emundus_chat.css")
?>

<div class="content">
    <div class="w-container">

        <h1 class="heading-2 no-dash">Message</h1>
        <p class="paragraph-infos2">
            <span class="text-span-2"><?= $this->offers->titre; ?></span><br>
        </p>
        <div class="sujet-message">
            <a href="consultez-les-offres/details/299/<?= $this->offers->search_engine_page; ?>"><?= JText::_('CONSULT_OFFER'); ?></a>
        </div>
        <div class="underline"></div>

        <div id="chat" class="wrapper-chat">
            <div id="em-messagerie" class="card-chat">
                <div class="message-list">
                    <?php if (empty($this->messages)) :?>
                        <div class="w-col w-col-6">
                            <p class="name-message"><?= JText::_('COM_EMUNDUS_CHATROOM_NO_MESSAGES_WITH'); ?></p>
                        </div>
                    <?php else:?>
                        <?php foreach ($this->messages as $message) :?>
                            <div class="columns-4 w-row">
                                <?php if ($message->user_id_from != $this->user_id) :?>
                                    <div class="w-col w-col-6">
                                        <p class="name-message">
                                            <strong><?= JFactory::getUser($message->user_id_from)->name; ?></strong> <?= date('D j M à H:i', strtotime($message->date_time)); ?>
                                        </p>
                                        <div class="div-block-8">
                                            <p class="chat-message"><?= $message->message; ?></p>
                                        </div>
                                    </div>
                                    <div class="w-col w-col-6"></div>
                                <?php elseif ($message->user_id_from == $this->user_id) :?>
                                    <div class="w-col w-col-6"></div>
                                    <div class="w-col w-col-6">
                                        <p class="name-message"><strong>Moi</strong> <?= date('D j M à H:i', strtotime($message->date_time)); ?></p>
                                        <div class="me">
                                            <p class="chat-message"><?= $message->message; ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php
                            if (!empty($message->message_id)) {
	                            $lastId = $message->message_id;
                            }
                        ?>
                    <?php endif;?>
                </div>
            </div>

            <div id="em-message">
                <input type="text" class="chat-field w-input" maxlength="256" name="Tapez-votre-message-ici" placeholder="Tapez votre message ici" id="Tapez-votre-message-ici">
                <button type="button" class="submit-button w-button" id="sendMessage" onclick="sendMessage()"><?= JText::_('SEND'); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    document.getElementsByTagName('body')[0].classList.add('espace-perso');

    let lastId = '<?= $lastId; ?>';

    function updateMessages() {

        const chatroom = "<?= $this->chatroom_id; ?>";

        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=messages&task=updatemessages',
            data : {
                id : lastId,
                chatroom : chatroom
            },
            success: function (result) {
                result = JSON.parse(result);
                if (result.status == 'true') {
                    lastId = result.messages[0].message_id;
                    for (let key in result.messages) {
                        let messageList = $('.message-list');
                        messageList.append('<div class="columns-4 w-row">' +
                            '<div class="w-col w-col-6">\n'+
'                              <p class="name-message"><strong>'+result.messages[key].user_from+'</strong> <?= JText::_('NOW'); ?></p>\n'+
'                              <div class="me">\n'+
'                                  <p class="chat-message">'+ result.messages[key].message + '</p>\n'+
'                              </div>\n'+
'                           </div>' +
                            '<div class="w-col w-col-6"></div></div>');

                        $('#em-messagerie').scrollTop($('#em-messagerie')[0].scrollHeight);
                    }
                }
            },
            error: function () {
                // handle error
                $("#em-contacts").append('<span class="alert"> <?= JText::_('ERROR'); ?> </span>')
            }
        });
    }

    $(document).ready(function() {
        setInterval(updateMessages, 10000);
    });

    function strip(html) {
        let tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText;
    }

    // Editor loads disabled by default, we apply must toggle it active on page load.
    $(document).ready(function() {
        $('#em-messagerie').scrollTop($('#em-messagerie')[0].scrollHeight);
    });

    function sendMessage() {
        let message = document.getElementById('Tapez-votre-message-ici').value;
        const chatroom = '<?= $this->chatroom_id; ?>';

        if (message.length !== 0  && strip(message).replace(/\s/g, '').length !== 0) {
            // remove white spaces
            message = message.replace(/ &nbsp;/g,'').replace(/&nbsp;/g,'').replace(/&nbsp; /g,'');
            $.ajax({
                type: 'POST',
                url: 'index.php?option=com_emundus&controller=messages&task=sendChatroomMessage',
                data : {
                    message: message,
                    chatroom: chatroom
                },
                success: function (result) {
                    let messageList = $('.message-list');
                    let contactMessage = document.getElementById('contact-message');

                    messageList.append('<div class="columns-4 w-row"></div>' +
                                             '<div class="w-col w-col-6"></div>\n'+
                        '                         <div class="w-col w-col-6">\n'+
            '                                        <p class="name-message"><strong>Moi</strong> <?= JText::_('NOW'); ?></p>\n'+
            '                                        <div class="me">\n'+
            '                                            <p class="chat-message">' + message + '</p>\n'+
            '                                        </div>\n'+
            '                                    </div>');

                    $('#em-messagerie').scrollTop($('#em-messagerie')[0].scrollHeight);

                    if (contactMessage) {
                        contactMessage.innerHTML = strip(message);
                    }
                    document.getElementById('Tapez-votre-message-ici').value = '';
                },
                error: function () {
                    // handle error
                    $("#em-messages").append('<span class="alert"> <?= JText::_('ERROR'); ?> </span>')
                }
            });
        }
    }

    document.getElementById("Tapez-votre-message-ici").addEventListener("keyup", function(e) {
        e.preventDefault();
        if (e.keyCode === 13) {
            sendMessage();
        }
    });
</script>

