<?php
defined('_JEXEC') or die;

?>

<div class="col-md-2">
    <button type="button" data-toggle="modal" data-target="#em-modal-book"
            class="btn btn-success"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW"); ?></button>
</div>

<div class="modal fade" id="em-modal-book" tabindex="-1" style="z-index:99999" role="dialog"
     aria-labelledby="em-modal-actions-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="em-modal-actions-title">
					<?php echo JText::_("MOD_EM_BOOK_INTERVIEW"); ?>
                </h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="em-book-interview"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_SELECT"); ?></label>
                        <select class="form-control" name="em-book-interview" id="em-book-interview"
                                aria-describedby="bookHelp">
                            <option value=""><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_PICK_A_DATE"); ?></option>
							<?php foreach ($available_events as $event) : ?>
                                <option value="<?php echo $event->id ?>"><?php echo $event->title . " <strong>" . $event->start_date . "</strong> " . $event->description ?></option>
							<?php endforeach; ?>
                        </select>
                        <small id="bookHelp"
                               class="form-text text-muted"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_SELECT_HELP"); ?></small>
                    </div>

					<?php if (sizeof($contact_info) > 0) : ?>

                        <h4 class="modal-title"><?php echo JText::_('MOD_EM_BOOK_INTERVIEW_VIDEO_CALLING'); ?></h4>

						<?php foreach ($contact_info as $type => $text) : ?>
                            <div class="form-group">
                                <label for="<?php echo $type . '-input'; ?>"><?php echo $text; ?></label>
                                <input type="text" class="form-control" name="<?php echo $type . '-input'; ?>"
                                       id="<?php echo $type . '-input'; ?>">
                            </div>
						<?php endforeach; ?>

					<?php endif; ?>

                    <button type="button" onclick="bookInterview()" class="btn btn-primary" id="btnBook"
                            name="btnBookInterview"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_SUBMIT"); ?></button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>

    function bookInterview() {

        var eventId = document.getElementById('em-book-interview').value,
            userId = <?php echo $user->id; ?>,
            fnum = <?php echo $user->fnum; ?>;

        var bookBtn = document.getElementById('btnBook');

        var contactInfo = new Object();

		<?php foreach ($contact_info as $type => $text) :?>
        contactInfo.<?php echo $type; ?> = document.getElementById('<?php echo $type . '-input'; ?>').value;
		<?php endforeach; ?>

        jQuery.ajax({
            url: 'index.php?option=com_emundus&controller=calendar&task=bookinterview&format=raw',
            method: 'POST',
            data: {
                eventId: eventId,
                userId: userId,
                fnum: fnum,
                contactInfo: contactInfo,
            },
            success: function (result) {
                result = JSON.parse(result);
                if (result.status) {
                    location.reload(true);
                } else {
                    bookBtn.style.backgroundColor = '#96281B';

                    if (typeof result.message != 'undefined') {
                        bookBtn.innerText = result.message;
                    } else {
                        bookBtn.innerText = 'Error!';
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                bookBtn.style.backgroundColor = '#96281B';
                bookBtn.innerText = 'Error!';
            }
        });

    }

</script>
