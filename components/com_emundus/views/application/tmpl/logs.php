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

    <!-- set fnum into hidden input -->
    <input type="hidden" id="fnum_hidden" value="<?php echo $this->fnum ?>">

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

            <br class="panel-body em-container-comment-body">
            <?php if (count($this->fileLogs) > 0) { ?>
                <div id="filters-logs">
                    <!-- add CRUD filters (multi-chosen) -->
                    <div id="actions">
                        <label for="crud-logs-label" id="crud-logs-hint"><?= JText::_('COM_EMUNDUS_CRUD_FILTER_LABEL'); ?></label>
                        <select name="crud-logs-select" id="crud-logs" class="chzn-select" multiple data-placeholder="<?= JText::_('COM_EMUNDUS_CRUD_FILTER_PLACEHOLDER'); ?>">
                            <option value="r"><?= JText::_('COM_EMUNDUS_LOG_READ_TYPE'); ?></option>
                            <option value="c"><?= JText::_('COM_EMUNDUS_LOG_CREATE_TYPE'); ?></option>
                            <option value="u"><?= JText::_('COM_EMUNDUS_LOG_UPDATE_TYPE'); ?></option>
                            <option value="d"><?= JText::_('COM_EMUNDUS_LOG_DELETE_TYPE'); ?></option>
                        </select>
                    </div>
                    <!-- -->
                    <div id="types">
                        <br>
                            <label for="actions-logs-label" id="actions-logs-hint"><?= JText::_('COM_EMUNDUS_TYPE_FILTER_LABEL'); ?></label>
                            <select name="type-logs-select" id="type-logs" class="chzn-select" multiple data-placeholder="<?= JText::_('COM_EMUNDUS_TYPE_FILTER_PLACEHOLDER'); ?>"></select>
                        </br>
                    </div>

                    <!-- -->
                    <div id="actors">
                        <br>
                            <label for="actors-logs-label" id="actors-logs-hint"><?= JText::_('COM_EMUNDUS_ACTORS_FILTER_LABEL'); ?></label>
                            <select name="actor-logs-select" id="actors-logs" class="chzn-select" multiple data-placeholder="<?= JText::_('COM_EMUNDUS_ACTOR_FILTER_PLACEHOLDER'); ?>"></select>
                        </br>
                    </div>

                </div>

                <div id="export-logs" class="em-flex-row">
                    <button id="log-filter-btn" class="em-w-max-content em-primary-button em-mt-8 em-mb-8 em-ml-8 em-mr-8">
                        <?= JText::_('COM_EMUNDUS_LOGS_FILTER') ?>
                    </button>

                    <button id="log-export-btn" style="background: #16afe1" class="em-w-max-content em-secondary-button em-mt-8 em-mb-8 em-ml-8 em-mr-8" onclick="exportLogs(<?=  "'" . $this->fnum . "'" ?>)">
                        <?= JText::_('COM_EMUNDUS_LOGS_EXPORT') ?>
                    </button>
                </div>

                <table class="table table-hover logs_table">
                    <caption class="hidden"><?= JText::_('COM_EMUNDUS_LOGS_CAPTION'); ?></caption>
                    <thead>
                        <tr>
                            <th id="date"><?= JText::_('DATE'); ?></th>
                            <th id="ip">IP</th>
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
                            <td><?= $log->ip_from; ?></td>
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
			</br>
        </div>
    </div>
</div>

<script type="text/javascript">
    var offset = 100;

    $('#crud-logs').chosen({width:'45%'});
    $('#type-logs').chosen({width:'45%'});
    $('#actors-logs').chosen({width:'45%'});

    /* get all logs when loading page */
    jQuery(document).ready(function() {
        /* get all logs type */
        jQuery.ajax({
            method: "post",
            url: "index.php?option=com_emundus&controller=files&task=getalllogs",
            dataType: 'json',
            success: function(results) {
                if(results.status) {
                    var logs = results.data;
                    logs.forEach(log => {
                        $('#type-logs').append('<option value="' + log.id + '">' + Joomla.JText._(log.label) + '</option>');           /// append data
                        $('#type-logs').trigger("chosen:updated");
                    })
                } else {
                    jQuery('#filters-logs').remove();
                    jQuery('#log-filter-btn').remove();
                    jQuery('.em-container-comment-heading').after('<b style="color:red">' + Joomla.JText._("COM_EMUNDUS_NO_ACTION_FOUND") + '</b>');
                }
            }, error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText, textStatus, errorThrown);
            }
        });

        /* show hint */
        $('#crud-logs-hint').on('hover', function(e){
            $(this).css('cursor','pointer').attr('title', Joomla.JText._("COM_EMUNDUS_CRUD_LOG_FILTER_HINT"));
        });

        $('#actions-logs-hint').on('hover', function(e){
            $(this).css('cursor','pointer').attr('title', Joomla.JText._("COM_EMUNDUS_TYPES_LOG_FILTER_HINT"));
        });

        $('#actors-logs-hint').on('hover', function(e){
            $(this).css('cursor','pointer').attr('title', Joomla.JText._("COM_EMUNDUS_ACTOR_LOG_FILTER_HINT"));
        });

        /* get fnum from input hidden ==> jQuery('#fnum_hidden').attr('value') */
        /* get all affected user(s) by current fnum */
        jQuery.ajax({
            type: 'post',
            url: 'index.php?option=com_emundus&controller=files&task=getuserslogbyfnum',
            data: ({
                fnum: jQuery('#fnum_hidden').attr('value'),
            }),
            dataType: 'json',
            success: function(results) {
                if(results.status) {
                    var users = results.data;
                    users.forEach(user => {
                        $('#actors-logs').append('<option value="' + user.uid + '">' + user.name + '</option>');           /// append data
                        $('#actors-logs').trigger("chosen:updated");
                    })
                } else {
                    $('#actors').remove();
                    $('#types').after('<br><p style="color:red">' + Joomla.JText._("COM_EMUNDUS_NO_LOG_USERS_FOUND") + '</p></br>');
                }

            }, error: function(xhr, status, error) {
                console.log(xhr.responseText, status, error);
            }
        });

        jQuery('#log-filter-btn').on('click', function(e) {
            // get actions (CRUD)
            var crud = jQuery('#crud-logs').val();

            // get type(s)
            var types = jQuery('#type-logs').val();

            // get person(s)
            var persons = jQuery('#actors-logs').val();

            jQuery.ajax({
                type: 'post',
                url: 'index.php?option=com_emundus&controller=files&task=getactionsonfnum',
                data: ({
                    fnum: jQuery('#fnum_hidden').attr('value'),
                    crud: crud,
                    types: types,
                    persons: persons,
                }),
                dataType: 'json',
                success: function(results) {
                    $('#log-count-results').remove();

                    // add loading icon
                    $('#logs_list').empty();
                    $('#logs_list').before('<div id="loading"><img src="'+loading+'" alt="loading"/></div>');

                    // remove the error-message (if any)
                    if($('#error-message').length > 0) {
                        $('#error-message').remove();
                    }

                    if(results.status) {
                        $('.logs_table').show();
                        $('#log-export-btn').show();
                        $('#export-logs').after('<p id="log-count-results" style="font-weight: bold" class="em-main-500-color">' + results.res.length + Joomla.JText._("COM_EMUNDUS_LOGS_FILTERS_FOUND_RESULTS") + '</p>');

                        // re-render the view (clear the logs-list)
                        $('#loading').remove();

                        var tr = ''
                        if (results.res.length < 100) {
                            $('.show-more').hide();
                        }
                        for (let i = 0; i < results.res.length; i++) {
                            tr = '<tr>' +
                                '<td>'+ results.res[i].date + '</td>' +
                                '<td>'+ results.res[i].ip_from + '</td>' +
                                '<td>'+ results.res[i].firstname + ' ' + results.res[i].lastname + '</td>' +
                                '<td>'+ results.details[i].action_category + '</td>' +
                                '<td>'+ results.details[i].action_name + '</td>' +
                                '<td>'+ results.details[i].action_details + '</td>' +
                                '</tr>'
                            $('#logs_list').append(tr);
                        }
                    } else {
                        $('#export-logs').after('<p id="log-count-results" style="font-weight: bold;" class="em-red-500-color">' + Joomla.JText._("COM_EMUNDUS_NO_LOGS_FILTERS_FOUND_RESULTS") + '</p>');
                        $('.show-more').hide();
                        $('#loading').remove();
                        $('#logs_list').append('<div id="error-message">' + Joomla.JText._("COM_EMUNDUS_NO_LOGS_FILTER_FOUND") + '</div>');
                        $('#log-export-btn').hide();
                        $('.logs_table').hide();
                    }
                }, error: function(xhr,status,error) {
                    console.log(xhr, status, error);
                }
            })
        })
    })

    $(document).on('click', '#show-more', function(e) {
        if(e.handle === true) {
            e.handle = false;
            var fnum = "<?php echo $this->fnum; ?>";

            // get actions (CRUD)
            var crud = jQuery('#crud-logs').val();

            // get type(s)
            var types = jQuery('#type-logs').val();

            // get person(s)
            var persons = jQuery('#actors-logs').val();

            url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=getactionsonfnum';
            $.ajax({
                type:'POST',
                url:url,
                dataType:'json',
                data:({fnum: fnum,
                    offset: offset,
                    crud: crud,
                    types: types,
                    persons: persons
                }),
                success: function(result) {
                    if (result.status) {
                        var tr = ''
                        if (result.res.length < 100) {
                            $('.show-more').hide();
                        }
                        for (let i = 0; i < result.res.length; i++) {
                            tr = '<tr>' +
                                '<td>'+ result.res[i].date + '</td>' +
                                '<td>'+ result.res[i].ip_from + '</td>' +
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

    function exportLogs(fnum)
    {
        xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?option=com_emundus&controller=files&task=exportLogs', true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    const response = JSON.parse(xhr.response);

                    if (response) {
                        let file_link = document.createElement('a');
                        file_link.id = 'file-link';
                        file_link.href = response;
                        file_link.download = fnum + '_logs.csv';
                        file_link.innerText = Joomla.JText._('COM_EMUNDUS_LOGS_DOWNLOAD');
                        file_link.click();
                    } else {
                        $('#log-export-btn').hide();
                        Swal.fire({
                            title: Joomla.JText._('COM_EMUNDUS_LOGS_DOWNLOAD_ERROR'),
                            type: 'error',
                            confirmButtonText: Joomla.JText._('OK')
                        });
                    }
                } else {
                    alert('Error: ' + xhr.status);
                }
            }
        };

        let body = new FormData();

        // get actions (CRUD)
        var crud = jQuery('#crud-logs').val();

        // get type(s)
        var types = jQuery('#type-logs').val();

        // get person(s)
        var persons = jQuery('#actors-logs').val();


        body.append('fnum', String(fnum));
        body.append('crud', JSON.stringify(crud));
        body.append('types', JSON.stringify(types));
        body.append('persons', JSON.stringify(persons));

        xhr.send(body);
    }
</script>

<style>
    .search-choice {
        font-size: small;
    }

    .search-field input{
        font-size: small !important;
        font-style: italic;
    }
</style>
