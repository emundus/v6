<?php
defined('_JEXEC') or die; 

?>

    <div class="col-md-2">
        <button type="button" data-toggle="modal" data-target="#em-modal-form" class="btn btn-success"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW"); ?></button>
    </div>

    <div class="modal fade" id="em-modal-form" tabindex="-1" style="z-index:99999" role="dialog" aria-labelledby="em-modal-actions-title">
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
                            <label for="em-calendar-title"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_SELECT"); ?></label>
                            <select class="form-control" name="em-book-interview" id="em-book-interview" aria-describedby="bookHelp">
                                <option value=""><?php echo JText::_("MOD_EM_CALENDAR_PICK_A_DATE"); ?></option>
                                <?php foreach($available_events as $event) :?>
                                    <option value="<?php echo $event->id ?>"><?php echo $event->title." <strong>".$event->start_date."</strong> ".$event->description ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small id="bookHelp" class="form-text text-muted"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_SELECT_HELP"); ?></small>
                        </div>

                        <button type="button" onclick="bookInterview()" class="btn btn-primary" id="btnBook" name="btnBookInterview"><?php echo JText::_("MOD_EM_BOOK_INTERVIEW_SUBMIT"); ?></button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    
    <script>

        function bookInterview() {

            var eventId = $$("#em-book-interview").get('value'),
                userId = <?php echo $user->id; ?>,
                fnum = <?php echo $user->fnum; ?>;

            var ajax = new Request({
                url: 'index.php?option=com_emundus&controller=calendar&task=bookinterview',
                method: 'post',
                data: {
                    eventId: eventId,
                    userId: userId,
                    fnum: fnum,
                },
                onRequest: function(){
                    $$('#btnCal').setStyle('background-color','#4183D7');
                    $$('#btnCal').set('text','Loading... ');
                },
                onSuccess: function(result){
                    if (result.status) {
                        $$('#btnCal').setStyle('background-color','#26A65B');
                        $$('#btnCal').set('text','Calendar added!');
                    } else {
                        $$('#btnCal').setStyle('background-color','#96281B');
                        $$('#btnCal').set('text','Error!');
                    }
                },
                onFailure: function(){
                    $$('#btnCal').setStyle('background-color','#96281B');
                    $$('#btnCal').set('text','Error!');
                }
            });

            ajax.send();
        }
    </script>