<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 13/11/14
 * Time: 11:24
 */
JFactory::getSession()->set('application_layout', 'logs');

?>

<style type="text/css">
	.widget .panel-body { padding:0px; }
	.widget .list-group { margin-bottom: 0; }
	.widget .panel-title { display:inline }
    .widget .log-info { margin: 1.5rem;}
</style>

<div class="logs">
    <div class="row">
        <div class="panel panel-default widget em-container-comment">
            <div class="panel-heading em-container-comment-heading">

                <h3 class="panel-title">
                	<span class="glyphicon glyphicon-list"></span>
                	<?php echo JText::_('COM_EMUNDUS_ACCESS_LOGS'); ?>
                </h3>

                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><i class="small arrow left icon"></i></button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><i class="small arrow right icon"></i></button>
                </div>

            </div>
            <div class="panel-body em-container-comment-body">
                <?php
                if (count($this->fileLogs) > 0) { ?>
                <table class="table table-hover logs_table">
                    <caption class="hidden"><?= JText::_('COM_EMUNDUS_LOGS_CAPTION'); ?></caption>
                    <thead>
                        <tr>
                            <th id="date"><?= JText::_('DATE'); ?></th>
                            <th id="user"><?= JText::_('USER'); ?></th>
                            <th id="action_category"><?= JText::_('COM_EMUNDUS_LOGS_VIEW_ACTION_CATEGORY'); ?></th>
                            <th id="action_name"><?= JText::_('COM_EMUNDUS_LOGS_VIEW_ACTION'); ?></th>
                            <th id="action_details"><?= JText::_('COM_EMUNDUS_LOGS_VIEW_ACTION_DETAILS'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="logs_list">
                        <?php
                        foreach ($this->fileLogs as $log) { ?>
                        <tr>
                            <td><?= $log->date; ?></td>
                            <td><?= $log->firstname . ' ' . $log->lastname; ?></td>
                            <td><?= $log->details['action_category']; ?></td>
                            <td><?= $log->details['action_name']; ?></td>
                            <td><?= $log->details['action_details']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php
                if (count($this->fileLogs) >= 100) { ?>
                <div class="log-info show-more"><button type="button" class="btn btn-info btn-xs" id="show-more">Afficher plus</button></div>
                <?php } ?>
                <?php } else { ?>
                <div class="log-info"><?= JText::_('NO_LOGS'); ?></div>
                <?php } ?>
			</div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var offset = 100;
$(document).on('click', '#show-more', function(e)
    {
        if(e.handle === true) {
            e.handle = false;
            var fnum = "<?php echo $this->fnum; ?>";

            url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getactionsonfnum';
            $.ajax(
                {
                    type:'POST',
                    url:url,
                    dataType:'json',
                    data:({fnum: fnum, offset: offset}),
                    success: function(result) {
                        if (result.status) {
                            var tr = ''
                            if (result.res.length < 100) {
                                $('.show-more').hide();
                            }
                            for (let i = 0; i < result.res.length; i++) {
                                tr = '<tr>' +
                                    '<td>'+ result.res[i].date + '</td>' +
                                    '<td>'+ result.res[i].firstname + ' ' + result.res[i].lastname + '</td>' +
                                    '<td>'+ result.details[i].action_category + '</td>' +
                                    '<td>'+ result.details[i].action_name + '</td>' +
                                    '<td>'+ result.details[i].action_details + '</td>' +
                                '</tr>'
                                $('#logs_list').append(tr);
                            }
                            offset += 100;
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                    }
                });
            }
    });
