<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 13/11/14
 * Time: 11:24
 */

use Joomla\CMS\Factory;

if (version_compare(JVERSION, '4.0', '>')) {
	Factory::getApplication()->getSession()->set('application_layout', 'logs');
}
else {
	Factory::getSession()->set('application_layout', 'logs');
}

?>

<style>
    .widget .panel-body {
        padding: 0;
    }

    .widget .list-group {
        margin-bottom: 0;
    }

    .widget .panel-title {
        display: inline
    }

    .widget .log-info {
        margin: 1.5rem;
    }
</style>

<div class="logs">
    <input type="hidden" id="fnum_hidden" value="<?php echo $this->fnum ?>">

    <div class="row">
        <div class="panel panel-default widget em-container-comment">
            <div class="panel-heading em-container-comment-heading">

                <h3 class="panel-title">
                    <span class="glyphicon glyphicon-list"></span>
					<?php echo JText::_('COM_EMUNDUS_ACCESS_LOGS'); ?>
                </h3>

                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><i class="small arrow left icon"></i>
                    </button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><i class="small arrow right icon"></i>
                    </button>
                </div>

            </div>

            <br class="panel-body em-container-comment-body">
			<?php if (!empty($this->fileLogs)) { ?>
                <div id="filters-logs" class="em-flex-row">
                    <!-- add CRUD filters (multi-chosen) -->
                    <div id="actions" class="em-w-33 em-mr-16">
                        <label for="crud-logs-label"
                               id="crud-logs-hint"><?= JText::_('COM_EMUNDUS_CRUD_FILTER_LABEL'); ?></label>
                        <select name="crud-logs-select" id="crud-logs" class="chzn-select em-w-100" multiple
                                data-placeholder="<?= JText::_('COM_EMUNDUS_CRUD_FILTER_PLACEHOLDER'); ?>">
                            <option value="r"><?= JText::_('COM_EMUNDUS_LOG_READ_TYPE'); ?></option>
                            <option value="c"><?= JText::_('COM_EMUNDUS_LOG_CREATE_TYPE'); ?></option>
                            <option value="u"><?= JText::_('COM_EMUNDUS_LOG_UPDATE_TYPE'); ?></option>
                            <option value="d"><?= JText::_('COM_EMUNDUS_LOG_DELETE_TYPE'); ?></option>
                        </select>
                    </div>
                    <div id="types" class="em-w-33 em-mr-16">
                        <label for="actions-logs-label"
                               id="actions-logs-hint"><?= JText::_('COM_EMUNDUS_TYPE_FILTER_LABEL'); ?></label>
                        <select name="type-logs-select" id="type-logs" class="chzn-select em-w-100" multiple
                                data-placeholder="<?= JText::_('COM_EMUNDUS_TYPE_FILTER_PLACEHOLDER'); ?>"></select>
                    </div>
                    <div id="actors" class="em-w-33 em-mr-16">
                        <label for="actors-logs-label"
                               id="actors-logs-hint"><?= JText::_('COM_EMUNDUS_ACTORS_FILTER_LABEL'); ?></label>
                        <select name="actor-logs-select" id="actors-logs" class="chzn-select em-w-100" multiple
                                data-placeholder="<?= JText::_('COM_EMUNDUS_ACTOR_FILTER_PLACEHOLDER'); ?>"></select>
                    </div>
                </div>

                <div id="apply-filters" class="em-flex-row-justify-end">
                    <button id="log-reset-filter-btn"
                            class="em-w-auto em-secondary-button em-mt-8 em-mb-8 em-ml-8 em-mr-8">
						<?= JText::_('COM_EMUNDUS_LOGS_RESET_FILTER') ?>
                    </button>
                    <button id="log-filter-btn" class="em-w-auto em-primary-button em-mt-8 em-mb-8 em-ml-8 em-mr-16">
						<?= JText::_('COM_EMUNDUS_LOGS_FILTER') ?>
                    </button>
                </div>

                <div id="export-logs" class="em-flex-row-justify-end">
                    <button id="log-export-btn" class="em-w-auto em-secondary-button em-mt-8 em-mb-8 em-ml-8 em-mr-16"
                            onclick="exportLogs(<?= "'" . $this->fnum . "'" ?>)">
                        <span class="material-icons-outlined em-mr-8">file_upload</span>
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
                    <div class="log-info show-more">
                        <button type="button" class="btn btn-info btn-xs" id="show-more">Afficher plus</button>
                    </div>
				<?php } ?>
			<?php } else { ?>
                <div class="log-info"><?= JText::_('COM_EMUNDUS_LOGS_NO_LOGS'); ?></div>
			<?php } ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    var offset = 100;

    $('#crud-logs').chosen({width: '100%'});
    $('#type-logs').chosen({width: '100%'});
    $('#actors-logs').chosen({width: '100%'});

    /* get all logs when loading page */
    $(document).ready(function () {
        /* get all logs type */
        $.ajax({
            method: "post",
            url: "index.php?option=com_emundus&controller=files&task=getalllogactions",
            dataType: 'json',
            success: function (results) {
                if (results.status) {
                    const typeLogs = $('#type-logs');

                    results.data.forEach(log => {
                        typeLogs.append('<option value="' + log.id + '">' + Joomla.JText._(log.label) + '</option>');           /// append data
                        typeLogs.trigger("liszt:updated");
                    })
                } else {
                    $('#filters-logs').remove();
                    $('#log-filter-btn').remove();
                    $('.em-container-comment-heading').after('<b style="color:red">' + Joomla.JText._("COM_EMUNDUS_NO_ACTION_FOUND") + '</b>');
                }
            }, error: function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText, textStatus, errorThrown);
            }
        });

        /* show hint */
        $('#crud-logs-hint').on('hover', function () {
            $(this).css('cursor', 'pointer').attr('title', Joomla.JText._("COM_EMUNDUS_CRUD_LOG_FILTER_HINT"));
        });

        $('#actions-logs-hint').on('hover', function () {
            $(this).css('cursor', 'pointer').attr('title', Joomla.JText._("COM_EMUNDUS_TYPES_LOG_FILTER_HINT"));
        });

        $('#actors-logs-hint').on('hover', function () {
            $(this).css('cursor', 'pointer').attr('title', Joomla.JText._("COM_EMUNDUS_ACTOR_LOG_FILTER_HINT"));
        });

        $.ajax({
            type: 'post',
            url: 'index.php?option=com_emundus&controller=files&task=getuserslogbyfnum',
            data: ({
                fnum: $('#fnum_hidden').attr('value'),
            }),
            dataType: 'json',
            success: function (results) {
                if (results.status) {
                    const actorsLog = $('#actors-logs');

                    results.data.forEach((user) => {
                        actorsLog.append('<option value="' + user.uid + '">' + user.name + '</option>');           /// append data
                        actorsLog.trigger("liszt:updated");
                    });
                } else {
                    $('#actors').remove();
                    $('#types').after('<br><p style="color:red">' + Joomla.JText._("COM_EMUNDUS_NO_LOG_USERS_FOUND") + '</p></br>');
                }

            }, error: function (xhr, status, error) {
                console.log(xhr.responseText, status, error);
            }
        });

        $('#log-filter-btn').on('click', function () {
            let crud = $('#crud-logs').val();

            if (!crud) {
                crud = ['c', 'r', 'u', 'd'];
            }

            const types = $('#type-logs').val();
            const persons = $('#actors-logs').val();

            $.ajax({
                type: 'post',
                url: 'index.php?option=com_emundus&controller=files&task=getactionsonfnum',
                data: ({
                    fnum: $('#fnum_hidden').attr('value'),
                    crud: crud,
                    types: types,
                    persons: persons,
                }),
                dataType: 'json',
                success: function (results) {
                    $('#log-count-results').remove();

                    // add loading icon
                    const logList = $('#logs_list');
                    logList.empty();
                    logList.before('<div id="loading"><img src="' + loading + '" alt="loading"/></div>');

                    // remove the error-message (if any)
                    if ($('#error-message').length > 0) {
                        $('#error-message').remove();
                    }

                    if (results.status) {
                        $('.logs_table').show();
                        $('#log-export-btn').show();
                        $('#export-logs').after('<p id="log-count-results" style="font-weight: bold" class="em-main-500-color em-p-8-12 em-float-right">' + results.res.length + Joomla.JText._("COM_EMUNDUS_LOGS_FILTERS_FOUND_RESULTS") + '</p>');
                        $('#loading').remove();

                        let tr = '';
                        if (results.res.length < 100) {
                            $('.show-more').hide();
                        }
                        for (let i = 0; i < results.res.length; i++) {
                            tr = '<tr>' +
                                '<td>' + results.res[i].date + '</td>' +
                                '<td>' + results.res[i].ip_from + '</td>' +
                                '<td>' + results.res[i].firstname + ' ' + results.res[i].lastname + '</td>' +
                                '<td>' + results.details[i].action_category + '</td>' +
                                '<td>' + results.details[i].action_name + '</td>' +
                                '<td>' + results.details[i].action_details + '</td>' +
                                '</tr>'
                            logList.append(tr);
                        }
                    } else {
                        $('#export-logs').after('<p id="log-count-results" style="font-weight: bold;" class="em-red-500-color em-p-8-12">' + Joomla.JText._("COM_EMUNDUS_NO_LOGS_FILTERS_FOUND_RESULTS") + '</p>');
                        $('.show-more').hide();
                        $('#loading').remove();
                        logList.append('<div id="error-message">' + Joomla.JText._("COM_EMUNDUS_NO_LOGS_FILTER_FOUND") + '</div>');
                        $('#log-export-btn').hide();
                        $('.logs_table').hide();
                    }
                }, error: function (xhr, status, error) {
                    console.log(xhr, status, error);
                }
            })
        })
    });

    $(document).on('click', '#show-more', function (e) {
        if (e.handle === true) {
            e.handle = false;
            const fnum = "<?php echo $this->fnum; ?>";
            const crud = $('#crud-logs').val();
            const types = $('#type-logs').val();
            const persons = $('#actors-logs').val();

            $.ajax({
                type: 'POST',
                url: 'index.php?option=com_emundus&controller=' + $('#view').val() + '&task=getactionsonfnum',
                dataType: 'json',
                data: ({
                    fnum: fnum,
                    offset: offset,
                    crud: crud,
                    types: types,
                    persons: persons
                }),
                success: function (result) {
                    if (result.status) {
                        let tr = ''
                        if (result.res.length < 100) {
                            $('.show-more').hide();
                        }
                        for (let i = 0; i < result.res.length; i++) {
                            tr = '<tr>' +
                                '<td>' + result.res[i].date + '</td>' +
                                '<td>' + result.res[i].ip_from + '</td>' +
                                '<td>' + result.res[i].firstname + ' ' + result.res[i].lastname + '</td>' +
                                '<td>' + result.details[i].action_category + '</td>' +
                                '<td>' + result.details[i].action_name + '</td>' +
                                '<td>' + result.details[i].action_details + '</td>' +
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

    function exportLogs(fnum) {
        xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?option=com_emundus&controller=files&task=exportLogs', true);

        xhr.onreadystatechange = function () {
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

        const crud = $('#crud-logs').val();
        const types = $('#type-logs').val();
        const persons = $('#actors-logs').val();

        body.append('fnum', String(fnum));
        body.append('crud', JSON.stringify(crud));
        body.append('types', JSON.stringify(types));
        body.append('persons', JSON.stringify(persons));

        xhr.send(body);
    }

    document.querySelector('#log-reset-filter-btn').addEventListener('click', function () {
        resetFilters();
    });

    function resetFilters() {
        const log_link = document.querySelector('#em-appli-menu a[href*="layout=logs"]');
        if (log_link) {
            log_link.click();
        }
    }
</script>

<style>
    .search-field input {
        font-size: small !important;
        font-style: italic;
    }

    #filters-logs, #export-logs {
        padding-bottom: 8px;
    }

    #apply-filters {
        padding-bottom: 0;
    }
</style>
