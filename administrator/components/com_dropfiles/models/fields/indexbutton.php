<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barr?re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

jimport('joomla.form.formfield');


/**
 * Class JFormFieldUpdaterstatus
 */
class JFormFieldIndexButton extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'indexbutton';

    /**
     * Method to get the field input updaterstatus
     *
     * @return string
     */
    protected function getInput()
    {
        // Make pid for this session
        $pid = sha1(time() . uniqid());
        // Build the script
        $script = array();
        $script[] = "var df_pid = '" . $pid . "';";
        $script[] = 'var df_pingtimeout = 5000;';
        $script[] = 'jQuery(document).ready(function($){';

        // Show hide index button
        $script[] = "if($('#jform_plain_text_search').is(':checked')) {
                        $('#search_indexer').closest('.control-group ').hide();
                    }
                    $('input[id^=jform_plain_text_search]').change(function() {
                        if (this.value == '1') {
                            $('#search_indexer').closest('.control-group ').show();
                        }
                        else if (this.value == '0') {
                            $('#search_indexer').closest('.control-group ').hide();
                        }
                    });";

        // Load Index Status
        $script[] = "getStatus = function() 
                    { 
                        $.ajax({
                            url: \"index.php?option=com_dropfiles&task=searchindexer.getstatus\",
                            type: \"GET\",
                            dataType : 'json'
                        }).done(function(res){
                            if(res.response) {
                                var index = res.datas.index;
                                var total = res.datas.total;
                                $('#indexstatus').html('&nbsp;Index status: ' + index + '/' + total);
                                if(index < total) {
                                    // Get Index data
                                    $.ajax({
                                        url: \"index.php?option=com_dropfiles&task=searchindexer.index\",
                                        type: \"GET\",
                                        dataType : 'json'
                                    }).done(function(res){
                                        getStatus();
                                    });
                                }
                            }
                        });
                    }";
        //$script[] = "getStatus();";

        //todo: Click Index Button
        $script[] = "$(document.body).on('click', 'button#search_indexer', function (e) {
                        e.preventDefault();
                        var confirmText = $(this).data('confirm-text');
                        var isAllow = false;
                        if ((confirmText) && (confirmText.length > 0)) {
                            if (confirm(confirmText)) {
                                isAllow = true;
                            }
                        } else {
                            isAllow = true;
                        }
                        if (isAllow) {
                            ftsAction('fts.submitrebuild', {pid: df_pid}, function (response) {
                                //
                            });
                        }
                        return false;
                    });";
        $script[] = "var df_pingprocessor = function (response) {
                        if (('code' in response) && (response['code'] === 0)) {
                            indexerBuildStatus(response['status']);
                            var result = response['result'];
                            switch (result) {
                                case 5:
                                    // Start indexing of part
                                    ftsAction('fts.rebuildstep', {'pid': df_pid}, df_pingprocessor);
                                    break;
                                case 10:
                                    // Indexing in progress (other process)
                                    setTimeout(pingtimer, df_pingtimeout);
                                    break;
                                case 0:
                                default:
                                    // Nothing to index
                                    setTimeout(pingtimer, df_pingtimeout);
                            }
                        }
                    };";
        // Ping system
        $script[] = "var pingtimer = function () {
                        ftsAction('fts.ajaxping', {'pid': df_pid}, df_pingprocessor);
                    };";
        // Build status bar
        $script[] = "var indexerBuildStatus = function (status) {
                        status = JSON.parse(status);
                        if (!status.message) {
                            if (status.n_inindex > 0 && status.n_inindex === status.n_actual + status.n_pending) {
                                var readyHtml = \"<span class=\\\"material-icons\\\" style=\\\"color: #fff; font-size: 20px; margin: 0 10px; vertical-align: text-bottom;\\\">done</span>\"
                                    + \"Index ready! On index: <b>\" + status.n_inindex + \"</b> files\";
                                $('#indexResult').html(readyHtml);
                            } else {
                                if (status.n_pending) {
                                    $('#indexResult .progress').removeClass(\"hide\");

                                    var total = status.n_inindex + status.n_pending;
                                    var processerStatus = status.n_actual + ' / ' + total + ' files';
                                    var percent = status.n_actual * 100 / (status.n_inindex + status.n_pending);
                                    if (status.n_inindex === 0) { 
                                        percent = 1; 
                                        processerStatus = '1' + ' / ' + total + ' files';
                                    }
                                    $(\"#indexResult .progress .bar\").width(percent + '%');
                                    $(\"#indexResult .progress .bar\").html(processerStatus);
                                } else if (status.n_pending === 0) {
                                    $(\"#indexResult .progress\").addClass(\"hide\");
                                    $(\"#indexResult .progress .bar\").width('0');
                                    $(\"#indexResult .progress .bar\").html(\"\");
                                } else {
                                    console.log(status);
                                }
                            }
                        } else {
                            var readyHtml = \"<span style=\\\"color: red; font-size: 15px;\\\">&#9679;</span>\"
                                + \"<b>\" + status.message + \"</b> files\";
                            $('#indexResult').html(readyHtml);
                        }
                    }";
        $script[] = 'if ($(\'#jform_plain_text_search\').is(\':checked\')) {
                        pingtimer();
                    }';
        $script[] = 'function ftsAction(action, data, callback) {
                        var url = "index.php?option=com_dropfiles&";
                
                        $.ajax({
                            url: url + "task=" + action,
                            method: \'POST\',
                            data: {\'__xr\': 1, \'z\': JSON.stringify(data)},
                            success: function (response) {
                                if(response === null || response === "") {
                                    window.location.reload();
                                }
                                var ret = true;
                                if ((typeof callback !== \'undefined\') && (callback)) {
                                    var vars = {};
                                    for (var i = 0; i < response.length; i++) {
                                        switch (response[i][0]) {
                                            case \'vr\':
                                                vars[response[i][1]] = response[i][2];
                                                break;
                                        }
                                    }
                                    ret = callback(vars);
                                }
                                if ((ret) || (typeof ret === \'undefined\')) {
                                    for (var i = 0; i < response.length; i++) {
                                        var data = response[i];
                                        switch (data[0]) {
                                            case \'cn\':
                                                break;
                                            case \'al\':
                                                alert(data[1]);
                                                break;
                                            case \'as\':
                                                if (jQuery(data[1]).length > 0) {
                                                    jQuery(data[1]).html(data[2]);
                                                }
                                                break;
                                            case \'js\':
                                                eval(data[1]);
                                                break;
                                            case \'rd\':
                                                document.location.href(data[1]);
                                                break;
                                            case \'rl\':
                                                window.location.reload();
                                                break;
                                        }
                                    }
                                }
                            },
                            error: function () {
                                //window.location.reload();
                            },
                            dataType: \'json\'
                        });
                    }';
        $script[] = '';
        $script[] = '';
        $script[] = '});';

        // Add to document head
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
        $html = array();

        // The reindex button
        $html[] = '<div class="button2-left">';
        $html[] = '  <div class="blank">';

        $html[] = '<button id="search_indexer" style="text-decoration:none;" class="btn button ju-btn ju-btn-connect"';
        $html[] = ' data-confirm-text="' . JText::_('COM_DROPFILES_REBUILD_SEARCH_ALERT') . '"';
        $html[] = ' title="Rebuild Search Index">Rebuild Search Index</button>';
        $html[] = '<div id="indexResult" style="display: inline;">';
        $html[] = '<div id="mybootstrap">';
        $html[] = '<div style="width: 300px;margin-top: 10px;" class="progress progress-striped active hide">';
        $html[] = '<div class="bar" style="width: 0%;margin:0;"></div>';
        $html[] = '</div></div></div>';


        $html[] = '  </div>';
        $html[] = '</div>';

        return implode("\n", $html);
    }
}
