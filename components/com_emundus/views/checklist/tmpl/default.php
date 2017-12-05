<?php
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.modal');

$user = JFactory::getSession()->get('emundusUser');
$chemin = EMUNDUS_PATH_REL;
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

if ($this->show_info_panel) :
    //if applicant not yet selected
    //if($this->isapplicant){ ?>
        <fieldset>
            <legend><?php echo $this->need<2?JText::_('CHECKLIST'):JText::_('RESULTS'); ?></legend>
            <div class = "<?php echo $this->need?'checklist'.$this->need:'checklist'.'0'; ?>" id="info_checklist">
                <h3><?php echo $this->title; ?></h3>
                <?php
                    if ($this->sent && count($this->result) == 0)
                        echo '<h3>'.JText::_('APPLICATION_SENT').'</h3>';
                    else
                        echo $this->text;

                if(!$this->need) {
                ?>
                        <h3><a href="<?php echo $this->sent?'index.php?option=com_emundus&task=pdf':$this->confirm_form_url; ?>" class="<?php echo $this->sent?'appsent':'sent'; ?>" target="<?php echo $this->sent?'_blank':''; ?>"><?php echo $this->sent?JText::_('PRINT_APPLICATION'):JText::_('SEND_APPLICATION'); ?></a>
                        </h3>
                    <?php } ?>
            </div>
        </fieldset>

    <?php
        if(!$this->sent) : ?>
        <p>
        <div id="instructions">
            <h3><?php echo $this->instructions->title; ?></h3>
            <?php echo $this->instructions->text; ?>
        </div>
        </p>
        <?php endif; ?>
    <?php //endif; ?>
<?php endif; ?>

<?php if (count($this->attachments) > 0) :?>

<!--<form id="checklistForm" name="checklistForm" onSubmit="return OnSubmitForm();"  method="post" enctype="multipart/form-data">-->
    <div id="attachment_list">
        <!-- <h3><?php echo JText::_('ATTACHMENTS'); ?></h3> -->
        <br>
        <p><?php echo JText::_('UPLOAD_MAX_FILESIZE') . ' = ' . ini_get("upload_max_filesize") . ' '. JText::_('BYTES'); ?> </p>
        <br>
    <?php if ($this->show_info_legend) :?>
            <div id="legend">
            <div class="need_missing"><?php echo JText::_('MISSING_DOC'); ?></div>,
            <div class="need_ok"><?php echo JText::_('SENT_DOC'); ?></div>,
            <div class="need_missing_fac"><?php echo JText::_('MISSING_DOC_FAC'); ?></div>
        </div>
    <?php endif; ?>
        <?php
        $attachment_list_mand = "";
        $attachment_list_opt = "";
        foreach($this->attachments as $attachment) {
            if ($attachment->nb==0) {
                $class= $attachment->mandatory?'need_missing':'need_missing_fac';
            } else {
                $class= 'need_ok';
            }
            $div = '<fieldset id="a'.$attachment->id.'">
                <legend id="l'.$attachment->id.'" class="'.$class.'">
                    <a href="javascript:toggleVisu(\''.$attachment->id .'\')">'.$attachment->value .' <i class="resize vertical icon"></i></a>
                </legend>
                <p class="description">'.$attachment->description .'</p>
                <div class="table-responsive">
                <table id="'.$attachment->id .'" class="table">';

            if ($attachment->nb>0)
                    foreach($attachment->liste as $item) {
                    $div .= '<tr>
                        <td>';
                        if($item->can_be_viewed==1) {
                        $div .= '<a class="btn btn-xs" href="'.$chemin.$user->id .'/'.$item->filename .'" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> '.JText::_('VIEW').'</a>';
                        }
                        else {
                        $div .= JText::_('CANT_VIEW') ;
                        }
                        $div .= '&nbsp;-&nbsp;' ;
                        if($item->can_be_deleted==1) {
                        $div .= '<a class="btn btn-danger btn-xs" href="'.JRoute::_('index.php?option=com_emundus&task=delete&uid='.$item->id.'&aid='.$item->attachment_id.'&duplicate='.$attachment->duplicate.'&nb='.$attachment->nb.'&Itemid='.$itemid.'#a'.$attachment->id).'"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span> '.JText::_('DELETE').'</a>';
                        }
                        else {
                        $div .= JText::_('CANT_DELETE');
                        }
                        $div .= ' | ';
                        $div .= ($item->timedate);
                        $div .= ' | ';
                        if ($this->show_shortdesc_input) {
                            $div .= empty($item->description)?JText::_('NO_DESC'):$item->description;
                        }
                        $div .= '</td></tr>';
                    }
            if ($attachment->nb<$attachment->nbmax || $user->profile<=4) {
                $div .= '
            <tr>
                <td>';
                $div .= '<form id="form-a'.$attachment->id.'" name="checklistForm" class="dropzone" action="'.JRoute::_('index.php?option=com_emundus&task=upload&duplicate='.$attachment->duplicate.'&Itemid='.$itemid).'" method="post" enctype="multipart/form-data">';
                $div .= '<input type="hidden" name="attachment" value="'.$attachment->id.'"/>
                <input type="hidden" name="duplicate" value="'.$attachment->duplicate.'"/>
                <input type="hidden" name="label" value="'.$attachment->lbl.'"/>
                <div class="input-group">
                    <span class="input-group-btn">';
                if ($this->show_browse_button) {
                    $div .= '<input type="file" name="file" style="opacity:1;font-size:18px;margin-left: 15px;" />';
                }
                    $div .= '<input type="hidden" class="form-control" readonly="">';
                if ($this->show_shortdesc_input) {
                    $div .= '<div class="col-sm-5"><input type="text" class="form-control" name="description" placeholder="'.JText::_('SHORT_DESC').'" /></div>';
                }
                if ($this->show_browse_button) {
                    $div .= '<input class="btn btn-success" name="sendAttachment" type="submit" onclick="document.pressed=this.name" value="'.JText::_('SEND_ATTACHMENT').'"/>';
                }
                $div .= '</span>
                </div>';

                $div .= '<script>
var maxFilesize = "'.ini_get("upload_max_filesize").'";

Dropzone.options.formA'.$attachment->id.' =  {
    maxFiles: '.$attachment->nbmax .',
    maxFilesize: maxFilesize.substr(0, maxFilesize.length-1), // MB
    dictDefaultMessage: "'.JText::_('COM_EMUNDUS_UPLOAD_DROP_FILE_OR_CLICK').'",
    dictInvalidFileType: "'. JText::_('PLEASE_ONLY').' '.$attachment->allowed_types.'",
    url: "index.php?option=com_emundus&task=upload&duplicate='.$attachment->duplicate.'&Itemid='.$itemid.'&format=raw",

    accept: function(file, done) {
        var sFileName = file.name;
        var sFileExtension = sFileName.split(".")[sFileName.split(".").length - 1].toLowerCase();

        if (sFileExtension == "php") {
          done("Naha, you do not.");
        }
        else {
            var allowedExtension = "'.$attachment->allowed_types.'";
            var n = allowedExtension.indexOf(sFileExtension);
            if (n >= 0)
                done();
            else {
                alert("'. JText::_('PLEASE_ONLY').' '.$attachment->allowed_types.'");
                done("'. JText::_('PLEASE_ONLY').' '.$attachment->allowed_types.'");
                this.removeFile(file);
            }
        }
    },

    init: function() {

      this.on("maxfilesexceeded", function(file) {
        this.removeFile(file);
        alert("'. JText::_('NO_MORE').' : '.$attachment->value .'. '.JText::_('MAX_ALLOWED').' '.$attachment->nbmax .'");
      });

      this.on("success", function(file, responseText) {
        // Handle the responseText here. For example, add the text to the preview element:
        var response = JSON.parse(responseText);
        var id = response["id"];
        file.previewTemplate.appendChild(document.createTextNode(response["message"]+" "));

        if (!response["status"]) {
            // Remove the file preview.
            this.removeFile(file);
        }

        // Change icon on fieldset
        document.getElementById("l"+'.$attachment->id.').className = "need_ok";
        document.getElementById("ml"+'.$attachment->id.').className = "need_ok";

        // Create the remove button
        var removeButton = Dropzone.createElement("<button>X</button>");

        // Capture the Dropzone instance as closure.
        var _this = this;

        // Listen to the click event
        removeButton.addEventListener("click", function(e) {
          // Make sure the button click does not submit the form:
          e.preventDefault();
          e.stopPropagation();

          // Remove the file preview.
          _this.removeFile(file);
          // If you want to the delete the file on the server as well,
          // you can do the AJAX request here.
          $.ajax({
            type: "GET",
            dataType: "json",
            url: "index.php?option=com_emundus&task=delete&uid="+id+"&aid='.$attachment->id.'&duplicate='.$attachment->duplicate.'&nb='.$attachment->nb.'&Itemid='.$itemid.'&format=raw",
            data: ({
                format: "raw"
            }),
            success: function(result) {
                if (result.status) {
                    // Change icon on fieldset
                    document.getElementById("l"+'.$attachment->id.').className = "";
                    document.getElementById("ml"+'.$attachment->id.').className = "";
                    alert("'. JText::_('ATTACHMENT_DELETED').'");
                }

            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
          });
        });
        // Add the button to the file preview element.
        file.previewElement.appendChild(removeButton);
      });

    }
};
</script>';
                $div .= '</form>';
                //$div .= '</div>';
                $div .= '</td>
            </tr>
            <tr>
                <td>
                <p><em>'. JText::_('PLEASE_ONLY').' '.$attachment->allowed_types.'</em></p><p><em>'.JText::_('MAX_ALLOWED').' '.$attachment->nbmax .'</em></p>
                </td>
            </tr>';
            } else {
                $div .= '
            <tr>
                <td>
                <p>'. JText::_('NO_MORE').' '.$attachment->value .'<br />'.JText::_('MAX_ALLOWED').' '.$attachment->nbmax .'</p>
                </td>
            </tr>
            </tbody>';
            }
            $div .='</table></div></fieldset>';
            if ($attachment->mandatory)
                $attachment_list_mand .= $div;
            else
                $attachment_list_opt .= $div;
        }
    ?>


    <div class="row">
      <div class="col-md-<?php echo (int)(12/$this->show_nb_column); ?>">
    <?php
        if ($attachment_list_mand!='') {
           echo '<div id="attachment_list_mand"><h1>'.JText::_('MANDATORY_DOCUMENTS').'</h1>'.$attachment_list_mand.'</div>';
        }
    ?>
      </div>
    <?php
      if ($this->show_nb_column > 1) {
        echo '<div class="ui vertical divider"></div>';
      }
    ?>
      <div class="col-md-<?php echo (int)(12/$this->show_nb_column); ?>">
    <?php
        if ($attachment_list_opt!='') {
           echo '<div id="attachment_list_opt"><h1>'.JText::_('OPTIONAL_DOCUMENTS').'</h1>'.$attachment_list_opt.'</div>';
        }
    ?>
      </div>
    </div>
    <?php endif; ?>
</div>

<script>
$(document).on('change', '.btn-file :file', function() {
  var input = $(this),
      numFiles = input.get(0).files ? input.get(0).files.length : 1,
      label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
  input.trigger('fileselect', [numFiles, label]);
});

$(document).ready( function() {
    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {

        var input = $(this).parents('.input-group').find(':text'),
            log = numFiles > 1 ? numFiles + ' <?php echo JText::_("FILES_SELECTED"); ?>' : label;

        if( input.length ) {
            input.val(log);
        } else {
            if( log ) alert(log);
        }

    });
});

$(document).on('click', '.em_form .document', function(f)
{
    var id = $(this).attr('id');
    $("fieldset").removeClass( "hover" );
    $("#a"+id).addClass( "hover" );
});

function toggleVisu(baliseId)
  {
  if (document.getElementById && document.getElementById(baliseId) != null)
    {
    if (document.getElementById(baliseId).style.visibility=='visible')
        {
        document.getElementById(baliseId).style.visibility='hidden';
        document.getElementById(baliseId).style.display='none';
        }
    else
        {
        document.getElementById(baliseId).style.visibility='visible';
        document.getElementById(baliseId).style.display='block';
        }
    }
  }
/*
<?php foreach($this->attachments as $attachment) { ?>
  document.getElementById('<?php echo $attachment->id; ?>').style.visibility='<?php echo ($attachment->mandatory && $attachment->nb==0)?'visible':'hidden'; ?>';
  document.getElementById('<?php echo $attachment->id; ?>').style.display='<?php echo ($attachment->mandatory && $attachment->nb==0)?'block':'none'; ?>';
<?php } ?>
*/
function OnSubmitForm() {
    var btn = document.getElementsByName(document.pressed);
    for(i=0 ; i<btn.length ; i++) {
        btn[i].disabled="disabled";
        btn[i].value="<?php echo JText::_('SENDING_ATTACHMENT'); ?>";
    }
    switch(document.pressed) {
        case 'sendAttachment':
            document.checklistForm.action ="index.php?option=com_emundus&task=upload&duplicate=<?php echo $duplicate; ?>&Itemid=<?php echo $itemid; ?>"
        break;
        default: return false;
    }
    return true;
}

var hash = window.location.hash;
if (hash != '') {
    $( hash ).addClass( "ui warning message" );
};

</script>