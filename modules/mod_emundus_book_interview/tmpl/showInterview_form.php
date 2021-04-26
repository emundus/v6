<?php
defined('_JEXEC') or die;

?>

<div class="alert alert-info">
    <p> <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_NEXT_INTERVIEW"); ?> <strong><?php echo $interview_date; ?></strong> <?php echo JText::_("MOD_EM_BOOK_INTERVIEW_AT"); ?> <strong><?php echo $interview_time." ".$offset; ?></strong> <p>
    <button type="button" class="btn btn-danger" id="btnBook" onclick="cancelInterview()"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_CANCEL"); ?></button>
</div>

<script>

    function cancelInterview() {

        var eventId = <?php echo $next_interview->id; ?>;

        $$("#btnBook").setStyle('background-color','#4183D7');
        $$("#btnBook").set('text','Loading... ');
        $$("#btnBook").removeProperty("onclick");

        var ajax = new Request({
            url: 'index.php?option=com_emundus&controller=calendar&task=cancelinterview&format=raw',
            method: 'POST',
            data: {
                eventId: eventId
            },
            onSuccess: function(result) {
                result = JSON.parse(result);
                if (result.status) {
                    location.reload(true);
                } else {
                    $$('#btnBook').setStyle('background-color','#96281B');
                    $$('#btnBook').set('text','Error!');
                }
            },
            onFailure: function(jqXHR, textStatus, errorThrown) {
                $$('#btnBook').setStyle('background-color','#96281B');
                $$('#btnBook').set('text','Error!');
            }
        });

        ajax.send();

    }

</script>
