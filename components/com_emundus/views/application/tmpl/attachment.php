<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 08/10/14
 * Time: 11:39
 * @package        Joomla
 * @subpackage    eMundus
 * @link          http://www.emundus.fr
 * @copyright    Copyright (C) 2008 - 2014 Décision Publique. All rights reserved.
 * @license        GNU/GPL
 * @author        Decision Publique - Yoan Durand
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

JFactory::getSession()->set('application_layout', 'attachment');

$isCoordinator = EmundusHelperAccess::asAccessAction(8,'c', $this->_user->id, $this->fnum)?true:false;

?>

<div class="title" id="em_application_attachments">
    <i class="dropdown icon"></i> <?php echo JText::_('ATTACHMENTS').' - '.$this->attachmentsProgress." % ".JText::_("SENT"); ?>
</div>

<div class="content">
    <div class="actions">

        <?php
        if ($isCoordinator):
            $url =  JURI::Base().'/index.php?option=com_fabrik&view=form&formid=67&rowid=&jos_emundus_uploads___user_id[value]='.$this->student_id.'&jos_emundus_uploads___fnum[value]='.$this->fnum.'&student_id='.$this->student_id.'&tmpl=component&iframe=1';
            ?>


        <?php endif;?>
    </div>
    <?php
    if(count($this->userAttachments) > 0)
    {
        if($isCoordinator)
        {
            echo '<button class="btn btn-default btn-xs btn-attach" id="em_export_pdf"  title="'.JText::_('PDF').'" link="/index.php?option=com_emundus&controller=application&task=exportpdf&fnum='.$this->fnum.'&student_id='.$this->student_id.'&ids={ids}">
			<span class="glyphicon glyphicon-file"></span>
		</button>';
        }

        if ($isCoordinator)
            $checkbox = '<input type="checkbox" name="em_application_attachments_all" id="em_application_attachments_all" />';

        echo '<table class="table table-hover">
			<thead>
                <tr>
                  <th>'.$checkbox.'#</th>
                  <th>'. JText::_('ATTACHMENT_FILENAME').'</th>
                  <th>'. JText::_('ATTACHMENT_DATE').'</th>
                  <th>'. JText::_('ATTACHMENT_DESCRIPTION').'</th>
                  <th>'. JText::_('CAMPAIGN').'</th>
                  <th>'. JText::_('ACADEMIC_YEAR').'</th>
                </tr>
              </thead><tbody>';
        $i = 1;

        foreach($this->userAttachments as $attachment)
        {
            /*if (EmundusHelperAccess::isExpert($this->_user->id) && $this->expert_document_id == $attachment->attachment_id && $this->_user->email != $attachment->description) {
                continue;
            }
            else {*/
            $path = $attachment->lbl == "_archive"?EMUNDUS_PATH_REL."archives/".$attachment->filename:EMUNDUS_PATH_REL.$this->student_id.'/'.$attachment->filename;
            $img_missing = (!file_exists($path))?'<img style="border:0;" src="media/com_emundus/images/icones/agt_update_critical.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> ':"";
            $img_dossier = (is_dir($path))?'<img style="border:0;" src="media/com_emundus/images/icones/dossier.png" width=20 height=20 title="'.JText::_( 'FILE_NOT_FOUND' ).'"/> ':"";
            $img_locked = (strpos($attachment->filename, "_locked") > 0)?'<img src="media/com_emundus/images/icones/encrypted.png" />':"";

            if ($isCoordinator)
                $checkbox = '<input type="checkbox" name="attachments[]" class="em_application_attachments" id="aid'.$attachment->aid.'" value="'.$attachment->aid.'" />';

            echo '<tr>
	                  <td>'.$checkbox.' '.$i.'</td>
	                  <td><a href="'.JURI::Base().$path.'" target="_blank">'.$img_dossier.' '. $img_locked.' '.$img_missing.' '.$attachment->value.'</a></td>
	                  <td>'.JHtml::_('date', $attachment->timedate, JText::_('DATE_FORMAT_LC2')).'</td>
	                  <td>'.$attachment->description.'</td>
	                  <td>'.$attachment->campaign_label.'</td>
	                  <td>'.$attachment->year.'</td>
	                </tr>';

            $i++;
            //}
        }
        echo '</tbody></table>';
        if(count($this->userAttachments) > 0) {
            if (EmundusHelperAccess::asAccessAction(4, 'd', $this->_user->id, $this->fnum)) {
                echo '<div style="width:40px;  margin-top: -15px; text-align: center"><span class="glyphicon glyphicon-chevron-down"></span><br /><button class="btn btn-danger btn-xs btn-attach" data-title="' . JText::_('DELETE_SELECTED_ATTACHMENTS') . '" id="em_delete_attachments" name="em_delete_attachments" link="/index.php?option=com_emundus&controller=application&task=deleteattachement&fnum=' . $this->fnum . '&student_id=' . $this->student_id . '">
                <span class="glyphicon glyphicon-trash"></span></button></div> ';
            }
        }
    } else echo JText::_('NO_ATTACHMENT');
    ?>
</div>

<div class="modal fade" id="em-modal-actions" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-actions" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="em-modal-actions-title"><?php echo JText::_('TITLE');?></h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('CANCEL')?></button>
                <button type="button" class="btn btn-success"><?php echo JText::_('OK');?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="em-modal-form" style="z-index:99999" tabindex="-1" role="dialog" aria-labelledby="em-modal-actions" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="em-modal-actions-title"><?php echo JText::_('LOADING');?></h4>
            </div>
            <div class="modal-body">
                <img src="<?php echo JURI::Base(); ?>media/com_emundus/images/icones/loader-line.gif">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('CANCEL')?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function getAttachementChecked()
    {
        var checkedInput = new Array();
        $('.em_application_attachments:checked').
            each(function()
            {
                checkedInput.push($(this).val());
            });
        return checkedInput;
    }

    $(document).on('click', '#em_application_attachments_all', function(e)
    {
        if($(this).is(':checked'))
        {
            $('.em_application_attachments').prop('checked', true);
        }
        else
        {
            $('.em_application_attachments').prop('checked', false);
        }
    });

    $(document).on('click', '#em_delete_attachments', function(e)
    {
        if(e.handle === true) {
            e.handle = false;
            var checkedInput = getAttachementChecked();
            if (checkedInput.length > 0) {
                var res = confirm("<?php echo JText::_('CONFIRM_DELETE_SELETED_ATTACHMENTS')?>");
                if (res) {
                    var url = $(this).attr('link');
                    $('#em-modal-actions .modal-body').empty();
                    $('#em-modal-actions .modal-body').append('<div><img src="' + loadingLine + '" alt="' +
                    Joomla.JText._('LOADING') + '"/></div>');
                    $('#em-modal-actions .modal-footer').hide();
                    $('#em-modal-actions .modal-dialog').addClass('modal-lg');
                    $('#em-modal-actions .modal').show();
                    $('#em-modal-actions').modal({backdrop: false, keyboard: true}, 'toggle');
                    $.ajax(
                        {
                            type: 'post',
                            url: url,
                            dataType: 'json',
                            data: {ids: JSON.stringify(checkedInput)},
                            success: function (result) {
                                $('#em-modal-actions').hide();

                                var url = "index.php?option=com_emundus&view=application&format=raw&layout=attachment&fnum=<?php echo $this->fnum; ?>";

                                $.ajax({
                                    type:"get",
                                    url:url,
                                    dataType:'html',
                                    success: function(result)
                                    {
                                        $('#em-appli-block').empty();
                                        $('#em-appli-block').append(result);
                                    },
                                    error: function (jqXHR, textStatus, errorThrown)
                                    {
                                        console.log(jqXHR.responseText);
                                    }
                                });


                                //$('.list-group-item#1318').click();
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                console.log(jqXHR.responseText);
                            }
                        });
                }
            }
            else {
                alert("<?php echo JText::_('YOU_MUST_SELECT_ATTACHMENT')?>");
            }
        }

    });

    $(document).on('click', '#em_export_pdf', function()
    {
        var checkInput = getAttachementChecked();
        String.prototype.fmt = function (hash) {
            var string = this, key;
            for (key in hash) string = string.replace(new RegExp('\\{' + key + '\\}', 'gm'), hash[key]); return string;
        }

        checkInput = checkInput.join(',');
        checkInput = encodeURIComponent(checkInput);
        var url = $(this).attr('link');
        url = url.fmt({ids: checkInput});
        if(checkInput.length > 0)
        {
            $('#em-modal-actions .modal-body').empty();
            $('#em-modal-actions .modal-body').append('<div class="em_attachs"><?php echo JText::_('ATTACHEMENTS_AGGREGATIONS')?> <input type="radio" name="aggr" id="aggr-yes" value="1"/>'  +
            '<label for="aggr-yes"><?php echo JText::_('JYES')?></label>'
            +
            '<input  type="radio" name="aggr" id="aggr-no" checked="checked" value="0"/> ' +
            '<label for="aggr-no"><?php echo JText::_('JNO')?></label>'+
            '<br/>' +
            '<a class="btn btn-default btn-attach" id="em_generate" href="'+url+'"><?php echo JText::_('GENERATE_PDF') ?></a><div id="attachement_res"></div></div>');
            $('#em-modal-actions .modal-footer').hide();
            $('#em-modal-actions .modal-dialog').addClass('modal-lg');
            $('#em-modal-actions .modal').show();
            $('#em-modal-actions').modal({backdrop:false, keyboard:true},'toggle');
        }
        else
        {
            alert("<?php echo JText::_('YOU_MUST_SELECT_ATTACHMENT')?>");
        }
    });
</script>
