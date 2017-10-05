<?php
defined('_JEXEC') or die; 

?>

<div class="alert alert-info">
    <p> <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_NEXT_INTERVIEW"); ?> <strong><?php echo $interview_date; ?></strong> <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_AT"); ?> <strong><?php echo $interview_time; ?></strong> <p>
    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#em-modal-form"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_CANCEL"); ?></button>
</div>


<div class="modal fade" id="em-modal-form" tabindex="-1" style="z-index:99999" role="dialog" aria-labelledby="em-modal-actions-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="em-modal-actions-title">
                    <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_CANCEL"); ?>
                </h4>
            </div>
            <div class="modal-body">
                <label for="em-calendar-title"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_ARE_YOU_SURE"); ?></label>
                <button type="button" onclick="cancelInterview()" class="btn btn-danger"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_CONFIRM_DELETE") ?></button>
                <p id="bookHelp" class="form-text text-muted"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_DELETE_HELP"); ?></p>
            </div>
        </div>
    </div>
</div>

<script>

    function cancelInterview() {

        var eventId = <?php echo $next_interview->id; ?>;

        $.ajax({
            url: 'index.php?option=com_emundus&controller=calendar&task=cancelinterview&format=raw',
            type: 'POST',
            dataType: 'json',
            data: ({
                eventId: eventId
            }),
            success: function(result){
                if (result.status) {
                    $('#btnCal').css('background-color','#26A65B');
                    $('#btnCal').text('Interview booked!');
                } else {
                    $('#btnCal').css('background-color','#96281B');
                    $('#btnCal').text('Error!');
                }
            },
            failure: function(jqXHR, textStatus, errorThrown){
                $('#btnCal').setStyle('background-color','#96281B');
                $('#btnCal').text('Error!');
            }
        });

        //location.reload(true);

    }

</script>
