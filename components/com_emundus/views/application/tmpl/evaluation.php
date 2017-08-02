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

//$isCoordinator = EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)?true:false;

?>
<div class="row">
    <div class="panel panel-default widget">
        <div class="panel-heading">
            <h3 class="panel-title">
            <span class="glyphicon glyphicon-check"></span> 
                <?php echo JText::_('COM_EMUNDUS_ASSESSMENT'); ?>
                <?php if(EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum) && !empty($this->url_form)):?>
                        <a class="  clean" target="_blank" href="<?php echo JURI::Base(); ?>index.php?option=com_emundus&controller=evaluation&task=pdf&user=<?php echo $this->student->id; ?>&fnum=<?php echo $this->fnum; ?>">
                            <button class="btn btn-default" data-title="<?php echo JText::_('DOWNLOAD_PDF'); ?>"><span class="glyphicon glyphicon-file"></span></button>
                        </a>
                <?php endif;?>
            </h3>
            <?php if(!empty($this->url_form)):?>
                <a href="<?php echo $this->url_form; ?>" target="_blank" title="<?php echo JText::_('OPEN_EVALUATION_FORM_IN_NEW_TAB_DESC'); ?>"><span class="glyphicon glyphicon-pencil"></span> <?php echo JText::_('OPEN_EVALUATION_FORM_IN_NEW_TAB'); ?></a>
            <?php endif;?>
        </div>
        <div class="panel-body">
            <div class="content">
                <?php if (count($this->evaluation_select) > 0):?>
                    <label for="copy_evaltuations"><?php echo JText::_('PICK_EVAL_TO_COPY'); ?></label>
                    <select id="copy_evaluations">
                        <option value="0" selected><?php echo JText::_('PICK_EVAL_TO_COPY'); ?></option>
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
                    <?php if(!empty($this->url_form)):?>
                        <div class="holds-iframe"><?php echo JText::_('LOADING'); ?></div>
                        <iframe id="iframe" src="<?php echo $this->url_form; ?>" align="left" frameborder="0" height="600" width="100%" scrolling="no" marginheight="0" marginwidth="0" onload="resizeIframe(this)"></iframe>
                    <?php else:?>
                        <div class="em_no-form"><?php echo JText::_('NO_EVALUATION_FORM_SET'); ?></div>
                    <?php endif;?>
                </div>
                <div class="evaluations" id="evaluations"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $('iframe').load(function(){
        $(".holds-iframe").remove();
    }).show();

    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }

    window.ScrollToTop = function(){
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
            error: function(jqXHR, textStatus, errorThrown) {
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
               error: function(jqXHR, textStatus, errorThrown) {
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
            error: function(jqXHR, textStatus, errorThrown) {
                console.log("error");
            }
        })
    });

    /*

     var url_form = '<?php echo $this->url_form; ?>';

if (url_form != '') {
    $.ajax({
            type: "GET",
            url: url_form,
            dataType: 'text',
            success: function(data) {
                var form = data;
                form = form.replace('<script src="/media/system/js/calendar.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/system/js/calendar-setup.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/system/js/mootools-core.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/system/js/core.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/system/js/mootools-more.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/com_fabrik/js/lib/art.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/libraries/gantry/js/browser-engines.js" type="text/javascript"><\/script>',''); 
                $("#form").empty();
                $("#form").append(form);
                $("#form").empty();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
}
*/
</script>
