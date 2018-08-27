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

if ($this->message_contacts[0]->user_id_to == $this->user_id)
    $id = $this->message_contacts[0]->user_id_from;
else
    $id = $this->message_contacts[0]->user_id_to;
?>


<div id="em-contacts">
    <?php if (empty($this->message_contacts)) :?>
        <div class="no-messages"><?php echo JText::_('NO_MESSAGES'); ?></div>
    <?php else :?>
    <ul>
        <?php foreach ($this->message_contacts as $message_contact) :?>
        <li>
            <?php if ($message_contact->user_id_to == $this->user_id) :?>
                <?php if ($message_contact->photo_from == null) :?>
                    <div class="contact-photo contact-photo-<?php echo str_replace(' ', '-', $message_contact->profile_from) ?>"></div>
                <?php endif; ?>
                <div class="em-contact" id="em-contact-<?php echo $message_contact->user_id_from ; ?>">
                    <?php if ($message_contact->state == 1) :?>
                        <p class='unread-contact' id="unread-contact-<?php echo $message_contact->user_id_from ; ?>"><i class="fas fa-circle" id="unread-icon"></i><?php  echo $message_contact->name_from ." : " ; ?></p>
                        <p class='unread-contact' id="unread-date-<?php echo $message_contact->user_id_from ; ?>"><?php echo date("N M Y", strtotime($message_contact->date_time)) ;?></p>
                        <p class='unread-contact contact-message' id="unread-message-<?php echo $message_contact->user_id_from ; ?>"><?php echo strip_tags($message_contact->message)  ;?></p>
                    <?php else :?>
                        <p class="read-contact"><?php  echo $message_contact->name_from ." : " ; ?></p>
                        <p class="read-contact"><?php echo date("N M Y", strtotime($message_contact->date_time)) ; ?></p>
                        <p class='read-contact contact-message'><?php echo strip_tags($message_contact->message)  ;?></p>
                    <?php endif; ?>
                </div>

            <?php endif; ?>

            <?php if ($message_contact->user_id_from == $this->user_id) :?>
                <?php if ($message_contact->photo_to == null) :?>
                    <div class="contact-photo contact-photo-<?php echo str_replace(' ', '-', $message_contact->profile_to) ?>"></div>
                <?php endif; ?>
                <div class="em-contact" id="em-contact-<?php echo $message_contact->user_id_to ; ?>">
                        <p class="read-contact"><?php echo $message_contact->name_to ." : "; ?></p>
                        <p class="read-contact"> <?php echo date("N M Y", strtotime($message_contact->date_time)) ;?></p>
                        <p class="read-contact contact-message" id=''><?php echo strip_tags($message_contact->message) ;?></p>
                </div>
            <?php endif; ?>
            </li>
        <hr>
        <?php endforeach ; ?>
    </ul>
    <?php endif; ?>
</div>

<div id="em-chat"><img src="media\com_emundus\images\icones\loader-line.gif" id="em-loader"/></div>


<script type="text/javascript">
    $(document).ready(function() {
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
                    var boldName = document.getElementById('unread-contact-'+id);
                    var boldDate = document.getElementById('unread-date-'+id);
                    var boldMessage = document.getElementById('unread-message-'+id);
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
            $('#em-loader').hide();
            $('#em-chat').css({'backgroundColor' : '#f0f0f0', 'height': '750px'});
        }

    });

    $('.em-contact').on("click", function() {
        $('#em-chat').html("<img src='media/com_emundus/images/icones/loader-line.gif' id='em-loader'/>");
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
                var icon = document.getElementById('unread-icon');
                var boldName = document.getElementById('unread-contact-'+id);
                var boldDate = document.getElementById('unread-date-'+id);
                var boldMessage = document.getElementById('unread-message-'+id);
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

    .contact-photo-Futur-doctorant:before {
        font-family: 'FontAwesome';
        color: #909090;
        content: "\f19d";
        top: 0;
        font-size: 3rem;
        margin-left: 11px;
        margin-top: -8px;
        display: inline-block;
    }

    .contact-photo-Chercheur:before {
        font-family: 'FontAwesome';
        color: #909090;
        content: "\f0c3";
        top: 0;
        font-size: 3rem;
        margin-left: 18px;
        margin-top: -10px;
        display: inline-block;
    }

    .contact-photo-Acteur-public-ou-associé:before {
        font-family: 'FontAwesome';
        color: #909090;
        content: "\f19c";
        top: 0;
        font-size: 3rem;
        margin-left: 15px;
        margin-top: -10px;
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
    }

    #em-loader{
        margin-left: auto;
        margin-right: auto;
        margin-top: 250px;
        display: block;
    }

    .unread-contact {font-weight: bold;}
    .read-contact {font-weight: normal;}


</style>
