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

if($this->message_contacts[0]->user_id_to == $this->user_id)
    $id = $this->message_contacts[0]->user_id_from;
else
    $id = $this->message_contacts[0]->user_id_to;
?>


<div id="em-contacts">
    <?php if(empty($this->message_contacts)):?>
        <div class="no-messages"><?php echo JText::_('NO_MESSAGES'); ?></div>
    <?php else:?>
    <ul id="list">
        <?php foreach ($this->message_contacts as $message_contact) :?>
        <li>
            <?php if($message_contact->user_id_to == $this->user_id):?>
                <div class="em-contact" id="em-contact-<?php echo $message_contact->user_id_from ; ?>">
                    <?php if($message_contact->state == 1) :?>
                        <p id="unread-contact-<?php echo $message_contact->user_id_from ; ?>" class='unread-contact' ><i class="fas fa-circle" id="unread-icon"></i><?php  echo $message_contact->name_from ." : " . date("d-m-Y", strtotime($message_contact->date_time)) ; ?></p>
                        <?php echo "<p id='contact-message'>" . strip_tags($message_contact->message) ."</p>" ;?>
                    <?php else:?>
                        <p class="read-contact"><?php  echo $message_contact->name_from ." : " ; ?></p>
                        <?php echo date("d-m-Y", strtotime($message_contact->date_time)) ; ?>
                        <?php echo "<p id='contact-message'>" . strip_tags($message_contact->message) ."</p>" ;?>
                    <?php endif;?>
                </div>

            <?php endif; ?>

            <?php if($message_contact->user_id_from == $this->user_id):?>
                <div class="em-contact" id="em-contact-<?php echo $message_contact->user_id_to ; ?>">
                        <?php echo $message_contact->name_to ." : " . date("d-m-Y", strtotime($message_contact->date_time)); ?>
                        <?php echo "<p id='contact-message'>" . strip_tags($message_contact->message) ."</p>" ;?>
                </div>
            <?php endif; ?>
            <hr>
        </li>
        <?php endforeach ; ?>
    </ul>
    <?php endif;?>
</div>

<div id="em-chat"></div>


<script type="text/javascript">
    $(document).ready(function() {
        var id = '<?php echo $id; ?>';
        console.log('#unread-contact-'+id);
        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&view=messages&format=raw&layout=chat',
            data : {
                id : id
            },
            success: function (result) {

                $('#em-chat').html(result);
                var icon = document.getElementById('unread-icon');
                var bold = document.getElementById('unread-contact-'+id);
                if(icon && bold) {
                    icon.parentNode.removeChild(icon);
                    $(bold).removeClass('unread-contact').addClass('read-contact');
                }
            },
            error: function () {
                // handle error
                $("#em-messages").append('<span class="alert"> <?php echo JText::_('ERROR'); ?> </span>')
            }
        });
    });

    $('.em-contact').on("click", function() {
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
                var bold = document.getElementById('unread-contact-'+id);
                if(icon && bold) {
                    icon.parentNode.removeChild(icon);
                    $(bold).removeClass('unread-contact').addClass('read-contact');
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
        background-color: #EEEEEE;
        overflow-y: scroll;
    }

    #contact-message {
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

    #em-message {
        float: right;
        height: 150px;
    }

    .unread-contact {font-weight: bold;}
    .read-contact span {font-weight: normal;}


</style>
