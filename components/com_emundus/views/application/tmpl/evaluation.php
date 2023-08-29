<?php
/**
 * User: brivalland
 * Date: 17/06/16
 * Time: 11:39
 * @package       Joomla
 * @subpackage    eMundus
 * @link          http://www.emundus.fr
 * @copyright     Copyright (C) 2016 eMundus. All rights reserved.
 * @license       GNU/GPL
 * @author        eMundus
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

JFactory::getSession()->set('application_layout', 'evaluation');
?>

<div class="row">
    <div class="panel panel-default widget em-container-evaluation">
        <div class="panel-heading em-container-evaluation-heading">
            <h3 class="panel-title">
            <span class="glyphicon glyphicon-check"></span>
                <?= JText::_('COM_EMUNDUS_ASSESSMENT'); ?>
                <?php if (EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum) && !empty($this->url_form)) :?>
                        <a class="  clean" target="_blank" href="<?= JURI::base(); ?>index.php?option=com_emundus&controller=evaluation&task=pdf&user=<?= $this->student->id; ?>&fnum=<?= $this->fnum; ?>">
                            <button class="btn btn-default" data-title="<?= JText::_('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?= JText::_('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF'); ?>"><span class="material-icons">file_download</span></button>
                        </a>
                <?php endif;?>
                <div class="em-flex-row">
                    <?php if (!empty($this->url_form)) :?>
                        <a href="<?= $this->url_form; ?>" target="_blank" class="em-flex-row" title="<?= JText::_('COM_EMUNDUS_EVALUATIONS_OPEN_EVALUATION_FORM_IN_NEW_TAB_DESC'); ?>"><span class="material-icons">open_in_new</span></a>
                    <?php endif;?>
                    <?php
                    if (EmundusHelperAccess::asAccessAction(5, 'd', $this->_user->id, $this->fnum)) :?>
                        <div><button class="btn btn-danger btn-xs btn-attach" title="<?= JText::_('COM_EMUNDUS_EVALUATIONS_DELETE_SELECTED_EVALUATIONS'); ?>" id="em_delete_evals" name="em_delete_evals" link="index.php?option=com_emundus&controller=evaluation&task=delevaluation&applicant=<?= $this->student->id; ?>&fnum=<?= $this->fnum; ?>">
                                <span class="material-icons">delete_outline</span></button></div>
                    <?php endif; ?>
                </div>
            </h3>
            <div class="btn-group pull-right">
                <button id="em-prev-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_back</span></button>
                <button id="em-next-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_forward</span></button>
            </div>
        </div>
        <div class="panel-body em-container-evaluation-body">
            <div class="content" style="display: flex; flex-direction: column;">
                <?php if (isset($this->evaluation_select) && count($this->evaluation_select) > 0) :?>
                    <label for="copy_evaltuations" class="em-container-evaluation-body-label"><?= JText::_('COM_EMUNDUS_EVALUATION_PICK_EVAL_TO_COPY'); ?></label>
                    <select id="copy_evaluations">
                        <option value="0" selected><?= JText::_('COM_EMUNDUS_EVALUATION_PICK_EVAL_TO_COPY'); ?></option>
                        <?php
                            foreach ($this->evaluation_select as $eval) {
                                foreach ($eval as $fnum => $evaluators) {
                                    foreach ($evaluators as $evaluator_id => $title) {
                                        echo "<option value='".$fnum."-".$evaluator_id."'>".$title."</option>";
                                    }
                                }
                            }
                        ?>
                    </select>
                <?php endif; ?>
                <a id="formCopyButton" href='#' style="display: none;">
                    <div class="btn button copyForm">Copy</div>
                </a>
                <div id="formCopy"></div>
                <div class="form" id="form">
                    <?php if (!empty($this->url_form)) :?>
                        <div class="holds-iframe"><?= JText::_('COM_EMUNDUS_LOADING'); ?></div>
                        <iframe id="iframe" src="<?= $this->url_form; ?>" align="left" frameborder="0" height="600" width="100%" scrolling="no" marginheight="0" marginwidth="0" onload="resizeIframe(this)"></iframe>
                    <?php else :?>
                        <div class="em_no-form"><?= JText::_($this->message); ?></div>
                    <?php endif; ?>
                </div>
                <div class="evaluations" id="evaluations"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $('iframe').load(function() {
        $(".holds-iframe").remove();
    }).show();

    $('#iframe').mouseleave(function() {
        resizeIframe(document.getElementById('iframe'));
    });

    $('#iframe').mouseover(function() {
        resizeIframe(document.getElementById('iframe'));
    });

    function resizeIframe(obj) {
        if(obj.contentWindow.document.body) {
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
        }
    }

    window.ScrollToTop = function() {
      $('html,body', window.document).animate({
        scrollTop: '0px'
      }, 'slow');
    };

    var url_evaluation = '<?php echo $this->url_evaluation; ?>';

    if (url_evaluation != '') {
        $.ajax({
            type: "GET",
            url: url_evaluation,
            dataType: 'html',
            success: function(data) {
                $("#evaluations").empty();
                $("#evaluations").append(data);
            },
            error: function(jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }

    $('#copy_evaluations').on('change', function() {
        if (this.value != 0) {

            var tmp = this.value.split('-');
            var fnum = tmp[0];
            var evaluator = tmp[1];

            $.ajax({
               type: 'GET',
               url: 'index.php?option=com_emundus&controller=evaluation&task=getevalcopy&format=raw&fnum='+fnum+'&evaluator='+evaluator,
               success: function(result) {
                   result = JSON.parse(result);

                    if (result.status) {

                        $('#formCopy').html(result.evaluation);
                        $('#formCopyButton').show();
                        $('div.copyForm').attr('id', result.formID);

                    }

               },
               error: function(jqXHR) {
                    console.log(jqXHR.responseText);
                }
            });
        } else {
            $('#formCopy').html(null);
            $('#formCopyButton').hide();
        }
    });

    $('#formCopyButton').on('click', function(e) {
        e.preventDefault();

        // ID of form we are copying from
        var fromID = $('div.copyForm').attr('id');
        // ID of form we are copying to
        var toID = $("#iframe").contents().find(".fabrikHiddenFields").find('[name="rowid"]').val(),
            fnum = $("#iframe").contents().find('#jos_emundus_evaluations___fnum').val(),
            student_id = parseInt(fnum.substr(-5),10);

        $.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=evaluation&task=copyeval',
            data: {
                from: fromID,
                to: toID,
                fnum: fnum,
                student: student_id
            },
            success: function(result) {
                result = JSON.parse(result);

                if (result.status)
                    $('div#formCopy').before('<p style="color: green">Success</p>');
                else
                    $('div#formCopy').before('<p style="color: red">Failed</p>');
            },
            error: function(jqXHR) {
                console.log("error");
            }
        })
    });

    function getEvalChecked() {
        var checkedInput = new Array();
        $('#evaluations input:checked').each(function() {
            checkedInput.push($(this).data('evalid'));
        });
        return checkedInput
    }

    $(document).on('click', '#em_delete_evals', function(e) {

        if (e.handle === true) {
            e.handle = false;
            var checked = getEvalChecked();

            if (checked.length > 0) {
                var res = confirm("<?php echo JText::_('COM_EMUNDUS_EVALUATIONS_CONFIRM_DELETE_SELETED_EVALUATIONS')?>");
                if (res) {
                    var url = $(this).attr('link');

                    $('#em-modal-actions .modal-body').empty();
                    $('#em-modal-actions .modal-body').append('<div><img src="' + loadingLine + '" alt="' +
                    Joomla.JText._('COM_EMUNDUS_LOADING') + '"/></div>');
                    $('#em-modal-actions .modal-footer').hide();
                    $('#em-modal-actions .modal-dialog').addClass('modal-lg');
                    $('#em-modal-actions .modal').show();
                    $('#em-modal-actions').modal({backdrop: false, keyboard: true}, 'toggle');

                    $.ajax({
                        type: 'post',
                        url: url,
                        dataType: 'json',
                        data: { ids: JSON.stringify(checked) },
                        success: function (result) {
                            $('#em-modal-actions').modal('hide');
                            var url = "index.php?option=com_emundus&view=application&format=raw&layout=evaluation&fnum=<?php echo $this->fnum; ?>";
                            $.ajax({
                                type:'get',
                                url:url,
                                dataType:'html',
                                success: function(result) {
                                    $('#em-appli-block').empty();
                                    $('#em-appli-block').append(result);
                                },
                                error: function(jqXHR) {
                                    console.log(jqXHR.responseText);
                                }
                            });
                        },
                        error: function(jqXHR) {
                            console.log(jqXHR.responseText);
                        }
                    });
                }
            } else {
                alert("<?php echo JText::_('COM_EMUNDUS_EVALUATIONS_YOU_MUST_SELECT_EVALUATIONS')?>");
            }
        }
    });
</script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
