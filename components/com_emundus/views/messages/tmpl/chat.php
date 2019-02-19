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
if ($this->getMessages[0]->user_id_to == $this->user_id) {
	$receiver = $this->getMessages[0]->user_id_from;
} else {
	$receiver = $this->getMessages[0]->user_id_to;
}
?>

<!-- WYSIWYG Editor -->
<link rel="stylesheet" href="/components/com_jce/editor/libraries/css/editor.min.css" type="text/css">
<script data-cfasync="false" type="text/javascript" src="/media/editors/tinymce/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript" src="/media/editors/tinymce/js/tinymce.min.js"></script>
<script data-cfasync="false" type="text/javascript">tinyMCE.init({ selector: "textarea.tinymce", theme: "modern", mobile: { theme: 'mobile' }, menubar:false, statusbar: false,
        toolbar: 'undo redo | styleselect | bold italic', plugins: [
            "advlist autolink autosave link lists charmap print preview hr anchor pagebreak spellchecker",
            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
            "contextmenu directionality emoticons template textcolor paste"
        ]
    })
</script>

<div id="chat" class="">
    <button class="navbar-toggler toggler-example" id="burger" type="button" onclick="burgerClick()"   aria-controls="navbarSupportedContent1"
            aria-expanded="false" aria-label="Toggle navigation"><span class="dark-blue-text"><i class="angle left icon"></i></span></button>
    <div id="em-messagerie">

        <?php if (empty($this->getMessages)) :?>
            <div class="no-messages"><?php echo JText::_('NO_MESSAGES_WITH'); ?></div>
        <?php else:?>
            <ul class="message-list">
                <?php foreach ($this->getMessages as $getMessage) :?>
                    <li>
                        <?php if ($getMessage->user_id_to == $this->user_id) :?>
                            <?php if ($getMessage->folder_id == 3) :?>
                                <span class="em-system-chat-message alert alert-info">
                                    <?php echo $getMessage->message; ?>
                                </span>
                            <?php else :?>
                                <div class="em-message-bubble em-contact-left">
			                        <?php echo "<p>" . $getMessage->message . "</p>"; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($getMessage->user_id_from == $this->user_id) :?>
                            <div class="em-message-bubble em-contact-right">
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
        <button type="button" class="btn" id="sendMessage" onclick="sendMessage()"><?php echo JText::_('SEND'); ?></button>
    </div>
</div>


<script type="text/javascript">
    function burgerClick () {

        var contactList = document.getElementById("em-contacts");
        var chat = document.getElementById("chat");
        var chat2 = document.getElementById("em-chat");
        if (contactList.className === 'hideContent') {

          //  document.getElementById("em-loader").style.display = "none";
            jQuery(contactList).toggleClass('showContent');
            jQuery(contactList).toggleClass('hideContent');
            jQuery(chat2).toggleClass('hideChat');
            jQuery(chat).toggleClass('hideChat');
            jQuery(chat).toggleClass('showChat');
            jQuery(chat2).toggleClass('showChat');

        } else {

            jQuery(contactList).toggleClass('showContent');
            jQuery(contactList).toggleClass('hideContent');
            jQuery(chat).toggleClass('hideChat');
            jQuery(chat).toggleClass('showChat');
            jQuery(chat2).toggleClass('hideChat');
            jQuery(chat2).toggleClass('showChat');

        }
    }

    function strip(html) {
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

        if (message.length != 0  && strip(message).replace(/\s/g, '').length != 0) {
            // remove white spaces
            message = message.replace(/ &nbsp;/g,'').replace(/&nbsp;/g,'').replace(/&nbsp; /g,'');
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
                    tinyMCE.activeEditor.setContent('');

                    messageList.append('<li><div class="em-message-bubble em-contact-right"><p>'+ message + '</p></div></li><hr id="separator">');

                    $('#em-messagerie').scrollTop($('#em-messagerie')[0].scrollHeight);

                    if (contactMessage)
                        contactMessage.innerHTML = strip(message);
                },
                error: function () {
                    // handle error
                    $("#em-messages").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
                }
            });
        }
    }

    function reply(id) {

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=cifre&task=replybyid',
            data: { id : id },
            beforeSend: () => {
                jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-default" disabled> ... </button>');
            },
            success: result => {
                if (result.status) {
                    // When we successfully change the status, we simply dynamically change the button.
                    jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-primary" onclick="breakUp(\'breakup\','+id+')"> <?php echo JText::_('COM_EMUNDUS_CIFRE_CUT_CONTACT'); ?> </button>');
                } else {
                    var actionText = document.getElementById('em-action-text-'+id);
                    actionText.classList.remove('hidden');
                    actionText.innerHTML = result.msg;
                }
            },
            error: jqXHR => {
                console.log(jqXHR.responseText);
            }
        });
    }

    function breakUp(action, id) {

        jQuery.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&controller=cifre&task=breakupbyid&action='+action,
            data: { id : id },
            beforeSend: () => {
                jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-default" disabled> ... </button>');
            },
            success: result => {
                if (result.status) {
                    // Dynamically change the button back to the state of not having a link.
                    jQuery('#em-buttons-'+id).html('<button type="button" class="btn btn-default" disabled><?php echo JText::_('COM_EMUNDUS_CIFRE_CANCELLED'); ?></button>');
                } else {
                    var actionText = document.getElementById('em-action-text-'.id);
                    actionText.classList.remove('hidden');
                    actionText.innerHTML = result.msg;
                }
            },
            error: jqXHR => {
                console.log(jqXHR.responseText);
            }
        });
    }

</script>

