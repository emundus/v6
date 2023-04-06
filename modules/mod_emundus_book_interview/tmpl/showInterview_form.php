<?php
defined('_JEXEC') or die;

?>

<div class="alert alert-info">
    <p> <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_NEXT_INTERVIEW_JURY"); ?> <strong><?php echo $next_interview->title; ?> </strong><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_NEXT_INTERVIEW")?> <strong><?php echo $interview_date; ?> </strong>  <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_AT"); ?> <strong><?php echo $interview_time." ".$offset; ?></strong> <p>
        <button type="button" class="btn btn-danger" id="btnBook" onclick="cancelInterview()"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_CANCEL"); ?></button>
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
            success: function(result) {
                result = JSON.parse(result);
                if (result.status) {
                    location.reload(true);
                } else {
                    bookBtn.style.backgroundColor = '#96281B';
                    bookBtn.style.innerText = 'Error!';
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                bookBtn.style.backgroundColor = '#96281B';
                bookBtn.style.innerText = 'Error!';
            }
        });
    }

</script>
