<?php
defined('_JEXEC') or die;

?>

<div class="alert alert-info">
    <p> <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_NEXT_INTERVIEW"); ?>
        <strong><?php echo $interview_date; ?></strong> <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_AT"); ?>
        <strong><?php echo $interview_time; ?></strong>
    <p>
        <button type="button" class="btn btn-danger" data-toggle="modal"
                data-target="#em-modal-form"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_CANCEL"); ?></button>
</div>


<div class="modal fade" id="em-modal-form" tabindex="-1" style="z-index:99999" role="dialog"
     aria-labelledby="em-modal-actions-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="em-modal-actions-title">
					<?php echo JText::_("MOD_EM_BOOK_INTERVIEW_CANCEL"); ?>
                </h4>
            </div>
            <div class="modal-body">
                <p><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_ARE_YOU_SURE"); ?></p>
                <p id="bookHelp"
                   class="form-text text-muted"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_DELETE_HELP"); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnBook" onclick="cancelInterview()"
                        class="btn btn-danger"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_CONFIRM_DELETE") ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>

    function cancelInterview() {

        var eventId = <?php echo $next_interview->id; ?>;

        var bookBtn = document.getElementById('btnBook');
        bookBtn.style.backgroundColor = '#4183D7';
        bookBtn.style.innerText = 'Loading...';
        bookBtn.removeAttribute("onclick");

        jQuery.ajax({
            url: 'index.php?option=com_emundus&controller=calendar&task=cancelinterview&format=raw',
            method: 'POST',
            data: {
                eventId: eventId
            },
            success: function (result) {
                result = JSON.parse(result);
                if (result.status) {
                    location.reload(true);
                } else {
                    bookBtn.style.backgroundColor = '#96281B';
                    bookBtn.style.innerText = 'Error!';
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                bookBtn.style.backgroundColor = '#96281B';
                bookBtn.style.innerText = 'Error!';
            }
        });
    }

</script>
