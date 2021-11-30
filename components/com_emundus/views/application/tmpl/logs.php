<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 13/11/14
 * Time: 11:24
 */
JFactory::getSession()->set('application_layout', 'logs');

$offset = JFactory::getApplication()->get('offset', 'UTC');
$dateTime = new DateTime(gmdate('Y-m-d H:i:s'), new DateTimeZone($offset));
$now = $dateTime->format(JText::_('DATE_FORMAT_LC2'));

?>

<style type="text/css">
	.widget .panel-body { padding:0px; }
	.widget .list-group { margin-bottom: 0; }
	.widget .panel-title { display:inline }
	.widget .label-info { float: right; }
	.widget li.list-group-item {border-radius: 0;border: 0;border-top: 1px solid #ddd;}
	.widget li.list-group-item:hover { background-color: rgba(86,61,124,.1); }
	.widget .mic-info { color: #666666;font-size: 11px; }
	.widget .action { margin-top:5px; }
    .widget .log-message { font-size: 18px; font-weight:600;}
    .widget .log-user { font-size:16px;}
    .widget .log-info { margin: 1.5rem;}
	.widget .btn-block { border-top-left-radius:0px;border-top-right-radius:0px; }
</style>

<div class="logs">
    <div class="row">
        <div class="panel panel-default widget em-container-comment">
            <div class="panel-heading em-container-comment-heading">

                <h3 class="panel-title">
                	<span class="glyphicon glyphicon-list"></span>
                	<?php echo JText::_('LOGS'); ?>
                    <span class="label label-info"><?php echo count($this->fileLogs); ?></span>
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
                    <thead>
                        <tr>
                            <th><?= JText::_('DATE'); ?></th>
                            <th><?= JText::_('USER'); ?></th>
                            <th><?= JText::_('COM_EMUNDUS_LOGS_VIEW_ACTION_CATEGORY'); ?></th>
                            <th><?= JText::_('COM_EMUNDUS_LOGS_VIEW_ACTION'); ?></th>
                            <th><?= JText::_('COM_EMUNDUS_LOGS_VIEW_ACTION_DETAILS'); ?></th>
                        </tr>       
                    </thead>
                    <tbody id="logs_list">
                        <?php
                        foreach ($this->fileLogs as $log) { ?>
                        <tr>
                            <td><?= $log->timestamp; ?></td>
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
                            var timestamp = Date();
                            if (result.res.length < 100) {
                                $('.show-more').hide();
                            }
                            for (let i = 0; i < result.res.length; i++) {
                                timestamp = new Date(result.res[i].timestamp);
                                tr = '<tr>' +
                                    '<td>'+ timestamp.toLocaleString() + '</td>' +
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