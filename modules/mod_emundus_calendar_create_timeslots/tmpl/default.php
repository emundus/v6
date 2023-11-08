<?php
defined('_JEXEC') or die;

?>

<div class="col-md-2">
    <button type="button" data-toggle="modal" data-target="#em-modal-timeslots"
            class="btn btn-success"><?php echo JText::_("MOD_EM_TIMESLOTS_ADD"); ?></button>
</div>

<div class="modal fade" id="em-modal-timeslots" tabindex="-1" style="z-index:99999" role="dialog"
     aria-labelledby="em-modal-actions-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="em-modal-actions-title">
					<?php echo JText::_("MOD_EM_TIMESLOTS_ADD"); ?>
                </h4>
            </div>
            <div class="modal-body">
                <form>

                    <!-- Calendar to add timeslots to -->
                    <div class="form-group">
                        <label for="em-timeslots-calendar"><?php echo JText::_("MOD_EM_CALENDAR_TIMESLOTS_TITLE"); ?></label>
                        <select class="form-control" id="em-timeslots-calendar">
                            <option value=""><?php echo JText::_("MOD_EM_CALENDAR_PICK_A_CALENDAR"); ?></option>
							<?php foreach ($calendars as $calendar) : ?>
                                <option value="<?php echo $calendar->id; ?>"><?php echo $calendar->title; ?></option>
							<?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Start and end dates -->
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"
                                  for="em-timeslots-start-date"><?php echo JText::_("MOD_EM_TIMESLOTS_START_DATE"); ?></span>
                            <input id="em-timeslots-start-date" type="date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon"
                                  for="em-timeslots-end-date"><?php echo JText::_("MOD_EM_TIMESLOTS_END_DATE"); ?></span>
                            <input id="em-timeslots-end-date" type="date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <!-- Start and end times -->
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"
                                  for="em-timeslots-start-time"><?php echo JText::_("MOD_EM_TIMESLOTS_START_TIME"); ?></span>
                            <input id="em-timeslots-start-time" type="time">
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon"
                                  for="em-timeslots-end-time"><?php echo JText::_("MOD_EM_TIMESLOTS_END_TIME"); ?></span>
                            <input id="em-timeslots-end-time" type="time">
                        </div>
                    </div>

                    <!-- Meeting and pause length -->
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon"
                                  for="em-timeslot-length"><?php echo JText::_("MOD_EM_TIMESLOTS_LENGTH"); ?></span>
                            <input id="em-timeslot-length" type="number" value="50">
                        </div>
                        <div class="input-group">
                            <span class="input-group-addon"
                                  for="em-pause-length"><?php echo JText::_("MOD_EM_PAUSE_LENGTH"); ?></span>
                            <input id="em-pause-length" type="number" value="10">
                        </div>
                    </div>


                    <button type="button" onclick="postTimeslots()" class="btn btn-primary" id="btnTimeslots"
                            name="btnAddtimeslots"><?php echo JText::_("MOD_EM_TIMESLOTS_SUBMIT"); ?></button>
                </form>

            </div>
        </div>
    </div>
</div>

<script>

    function postTimeslots() {

        var calId = $$("#em-timeslots-calendar").get('value'),
            sDate = $$("#em-timeslots-start-date").get('value'),
            eDate = $$("#em-timeslots-end-date").get('value'),
            sTime = $$("#em-timeslots-start-time").get('value'),
            eTime = $$("#em-timeslots-end-time").get('value'),
            tsLength = $$("#em-timeslot-length").get('value'),
            pLength = $$("#em-pause-length").get('value');

        let ajax = new Request({
            url: 'index.php?option=com_emundus&controller=calendar&task=createtimeslots&format=raw',
            method: 'post',
            data: {
                calId: calId,
                sDate: sDate,
                eDate: eDate,
                sTime: sTime,
                eTime: eTime,
                tsLength: tsLength,
                pLength: pLength
            },
            onRequest: function () {
                $$('#btnTimeslots').setStyle('background-color', '#4183D7');
                $$('#btnTimeslots').set('text', 'Loading... ');
            },
            onSuccess: function (result) {
                result = JSON.parse(result);
                if (result.status) {
                    location.reload(true);
                } else {
                    $$('#btnTimeslots').setStyle('background-color', '#96281B');
                    $$('#btnTimeslots').set('text', result.error);
                }
            },
            onFailure: function () {
                $$('#btnTimeslots').setStyle('background-color', '#96281B');
                $$('#btnTimeslots').set('text', 'Error!');
            }
        });

        ajax.send();
    }

</script>
