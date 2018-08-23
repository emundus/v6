<?php
/**
 * Created by PhpStorm.
 * User: emundus
 * Date: 21/08/2018
 * Time: 11:52
 * @author James Dean
 */


// no direct access
defined('_JEXEC') or die('Restricted access');

// Load the WYSIWYG editor used to edit the mail body.
$editor = JFactory::getEditor('tinymce');
$wysiwyg = $editor->display('message_body', '', '100%', '110', '20', '20', false, 'message_body', null, null, array('mode' => 'simple'));

$receiver = null;
if($this->getMessages[0]->user_id_to == $this->user_id)
    $receiver = $this->getMessages[0]->user_id_from;
else
    $receiver = $this->getMessages[0]->user_id_to;
?>

<!-- WYSIWYG Editor -->
<link rel="stylesheet" href="/components/com_jce/editor/libraries/css/editor.min.css" type="text/css">
<script data-cfasync="false" type="text/javascript" src="/media/editors/tinymce/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript" src="/media/editors/tinymce/js/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript">tinyMCE.init({selector: "textarea.tinymce", theme: "modern",menubar:false,statusbar: false,
        toolbar: 'undo redo | styleselect | bold italic | link image'})
</script>

<div id="em-messagerie">
    <?php if(empty($this->getMessages)):?>
            <div class="no-messages"><?php echo JText::_('NO_MESSAGES_WITH'); ?></div>
    <?php else:?>
    <ul class="message-list">
        <?php foreach ($this->getMessages as $getMessage) :?>
            <li>
                <?php if($getMessage->user_id_to == $this->user_id):?>
                    <div class="em-contact em-contact-left">
                        <?php echo "<p class='message'>" . $getMessage->message . "</p>"; ?>
                    </div>
                <?php endif; ?>

                <?php if($getMessage->user_id_from == $this->user_id):?>
                    <div class="em-contact em-contact-right">
                        <?php  echo "<p>" . $getMessage->message . "</p>"; ?>
                    </div>
                <?php endif; ?>
            </li>
        <hr id="separator">
        <?php endforeach ; ?>
    </ul>
    <?php endif;?>
</div>

<div id="em-message">
    <?php echo $wysiwyg; ?>
    <button type="button" class="btn" id="sendMessage" onclick="sendMessage()">Send</button>
</div>

<script type="text/javascript">

    function strip(html)
    {
        var tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText;
    }

    // Editor loads disabled by default, we apply must toggle it active on page load.
    $(document).ready(function() {
        tinymce.execCommand('mceToggleEditor', true, 'message_body');
        $('#em-messagerie').scrollTop($('#em-messagerie')[0].scrollHeight);
    });


    function sendMessage() {
        tinyMCE.triggerSave();
        var message = tinyMCE.activeEditor.getContent();
        var receiver = '<?php echo $receiver; ?>';


        //console.log(messageTrim);
        if(message.length != 0  && strip(message).replace(/\s/g, '').length != 0) {
            // remove white spaces
            message = message.replace(/ &nbsp;/g,'').replace(/&nbsp;/g,'').replace(/&nbsp; /g,'');
            console.log(message);
            $.ajax({
                type: 'POST',
                url: 'index.php?option=com_emundus&controller=messages&task=sendMessage',
                data : {
                    message : message,
                    receiver: receiver
                },
                success: function (result) {
                    var messageList = $('.message-list');
                    var contactMessage = document.getElementById('contact-message');

                    messageList.append('<li><div class="em-contact em-contact-right"><p>'+ message + '</p></div></li><hr id="separator">');

                    $('#em-messagerie').scrollTop($('#em-messagerie')[0].scrollHeight);

                    if(contactMessage) {
                        contactMessage.innerHTML = strip(message);
                    }
                },
                error: function () {
                    // handle error
                    $("#em-messages").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
                }
            });
        }

    };

</script>

<style>


    #em-messagerie {
        float: right;
        width: 100%;
        height: inherit;
        overflow-y: scroll;
        max-height: inherit;
    }

    .message-list{
        height: inherit;
    }

    #em-message {
        width: 100%;
    }

    .em-contact {
        border-radius: 25px;
        border-width: 1px;
        margin-left: 1%;
        max-width: 80%;
        list-style-position: inside;
        padding-left: 10px;
        padding-right: 10px;
    }

    .em-contact-right {
        float: right;
        display: inline-block;
        border-color: #00AF64;
        background-color: #00AF64;

    }
    .em-contact-left {
        float: left;
        display: inline-block;
        background-color: #0AA5DF;
    }

    .em-contact p {
        word-wrap: break-word;
        word-break: break-all;
    }

    #separator {
        display: inline-block;
        width: 100%;
        height: 0px;
        border: 0px;
    }


</style>
