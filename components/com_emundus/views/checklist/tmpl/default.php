<?php
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.modal');


$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/css/emundus_checklist.css" );
$document->addScript("https://cdn.jsdelivr.net/npm/sweetalert2@8");
$mainframe = JFactory::getApplication();

$chemin = EMUNDUS_PATH_REL;
$itemid = $mainframe->input->get('Itemid', null);

// check if it is possible to upload file
$eMConfig = JComponentHelper::getParams('com_emundus');
$copy_application_form = $eMConfig->get('copy_application_form', 0);
$can_edit_until_deadline = $eMConfig->get('can_edit_until_deadline', '0');
$can_edit_after_deadline = $eMConfig->get('can_edit_after_deadline', 0);
$status_for_send = explode(',', $eMConfig->get('status_for_send', 0));
$id_applicants = $eMConfig->get('id_applicants', '0');
$applicants = explode(',',$id_applicants);
//ADDPIPE
$addpipe_activation = $eMConfig->get('addpipe_activation', 0);
$addpipe_account_hash = $eMConfig->get('addpipe_account_hash', null);
$addpipe_eid = $eMConfig->get('addpipe_eid', null);
$addpipe_showmenu = $eMConfig->get('addpipe_showmenu', 1);
$addpipe_asv = $eMConfig->get('addpipe_asv', 0);
$addpipe_dup = $eMConfig->get('addpipe_dup', 1);
$addpipe_srec = $eMConfig->get('addpipe_srec', 0);
$addpipe_mrt = $eMConfig->get('addpipe_mrt', 60);
$addpipe_qualityurl = $eMConfig->get('addpipe_qualityurl', 'avq/480p.xml');
$addpipe_size = $eMConfig->get('addpipe_size', '{width:640,height:510}');

$offset = $mainframe->get('offset', 'UTC');
try {
    $dateTime = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone('UTC'));
    $dateTime = $dateTime->setTimezone(new DateTimeZone($offset));
    $now = $dateTime->format('Y-m-d H:i:s');
} catch (Exception $e) {
    echo $e->getMessage() . '<br />';
}

$this->is_dead_line_passed = !empty($this->is_admission) ? strtotime(date($now)) > strtotime(@$this->user->fnums[$this->user->fnum]->admission_end_date) : strtotime(date($now)) > strtotime(@$this->user->end_date);

$is_app_sent = !in_array($this->user->status, $status_for_send);

$block_upload = true;
if ($can_edit_after_deadline || (!$is_app_sent && (!$this->is_dead_line_passed || $this->isLimitObtained !== true)) || in_array($this->user->id, $applicants) || ($is_app_sent && !$this->is_dead_line_passed && $can_edit_until_deadline && $this->isLimitObtained !== true)) {
    $block_upload = false;
}


function return_bytes($val) {
	$val = trim($val);
	$last = strtolower($val[strlen($val)-1]);
	switch($last) {
		// Le modifieur 'G' est disponible depuis PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
	}

	return $val;
}


if (!empty($this->custom_title)) :?>
    <h1 class="em-checklist-title"><?= $this->custom_title; ?></h1>
<?php endif; ?>
<?php if ($this->show_info_panel) :?>
    <fieldset>
        <legend><?= $this->need<2?JText::_('COM_EMUNDUS_ATTACHMENTS_CHECKLIST'):JText::_('COM_EMUNDUS_ATTACHMENTS_RESULTS'); ?></legend>
        <div class = "<?= $this->need?'checklist'.$this->need:'checklist'.'0'; ?>" id="info_checklist">
            <h3><?= $this->title; ?></h3>
            <?php
                if ($this->sent && count($this->result) == 0) {
	                echo '<h3>' . JText::_('COM_EMUNDUS_ATTACHMENTS_APPLICATION_SENT') . '</h3>';
                } else {
	                echo $this->text;
                }

            if (!$this->need) { ?>
                    <h3><a href="<?= $this->sent?'index.php?option=com_emundus&task=pdf':$this->confirm_form_url; ?>" class="<?= $this->sent?'appsent':'sent'; ?>" target="<?= $this->sent?'_blank':''; ?>"><?= $this->sent?JText::_('COM_EMUNDUS_APPLICATION_PRINT_APPLICATION'):JText::_('COM_EMUNDUS_APPLICATION_SEND_APPLICATION'); ?></a></h3>
            <?php } ?>
        </div>
    </fieldset>

<?php
    if (!$this->sent) :?>
    <p class="em-instructions">
        <div id="instructions">
            <h3><?= $this->instructions->title; ?></h3>
            <?= $this->instructions->text; ?>
        </div>
    </p>
    <?php endif; ?>
<?php endif; ?>

<?php if (count($this->attachments) > 0) :?>

    <div id="attachment_list" class="em-attachmentList">
        <p><?= JText::_('COM_EMUNDUS_ATTACHMENTS_INFO_UPLOAD_MAX_FILESIZE') . ' = ' . ini_get("upload_max_filesize") . ' '. JText::_('COM_EMUNDUS_ATTACHMENTS_BYTES'); ?> </p>
    <?php if ($this->show_info_legend) :?>
        <div id="legend" class="em-attachmentList-legend">
            <div class="need_missing"><?= JText::_('COM_EMUNDUS_ATTACHMENTS_MISSING_DOC'); ?></div>,
            <div class="need_ok"><?= JText::_('COM_EMUNDUS_ATTACHMENTS_SENT_DOC'); ?></div>,
            <div class="need_missing_fac"><?= JText::_('COM_EMUNDUS_ATTACHMENTS_MISSING_DOC_FAC'); ?></div>
        </div>
    <?php endif; ?>
    <?php
        $file_upload = 1;
        $attachment_list_mand = "";
        $attachment_list_opt = "";
        foreach ($this->attachments as $attachment) {
            if ($attachment->nb == 0) {
                $class = $attachment->mandatory?'need_missing':'need_missing_fac';
            } else {
                $class = 'need_ok';
            }
            $div = '<fieldset id="a'.$attachment->id.'" class="em-fieldset-attachment">
                <legend id="l'.$attachment->id.'" class="'.$class.'">
                    <a class="'.$class.'">'.$attachment->value .'</a>
                </legend>
                <p class="description em-fieldset-attachment-description">'.$attachment->description .'</p>
                <div class="table-responsive em-fieldset-attachment-table-responsive">
                <table id="'.$attachment->id .'" class="table em-fieldset-attachment-table">';

            if ($attachment->nb > 0) {
                foreach ($attachment->liste as $item) {
                    $div .= '<tr class="em-added-files">
                    <td>';
                    if ($item->can_be_viewed == 1) {
                        $div .= '<a class="btn btn-success btn-xs" href="'.$chemin.$this->user->id .'/'.$item->filename .'" target="_blank"><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> '.JText::_('COM_EMUNDUS_ATTACHMENTS_VIEW').'</a>';
                    } else {
                        $div .= JText::_('COM_EMUNDUS_ATTACHMENTS_CANT_VIEW');
                    }
                    $div .= '&nbsp;-&nbsp;' ;
                    if (($item->can_be_deleted == 1 || $item->is_validated == "0") && !$block_upload) {
                        $div .= '<a class="btn btn-danger btn-xs" href="'.JRoute::_('index.php?option=com_emundus&task=delete&uid='.$item->id.'&aid='.$item->attachment_id.'&duplicate='.$attachment->duplicate.'&nb='.$attachment->nb.'&Itemid='.$itemid.'#a'.$attachment->id).'"><span class="material-icons">delete_outline</span> '.JText::_('COM_EMUNDUS_ACTIONS_DELETE').'</a>';
                    } else {
                        $div .= JText::_('COM_EMUNDUS_ATTACHMENTS_CANT_DELETE');
                    }
                    $div .= ' | ';
                    $div .= JString::ucfirst(JHTML::Date(strtotime($item->timedate), "DATE_FORMAT_LC2"));
                    $div .= ' | ';
                    if ($this->show_shortdesc_input) {
                        $div .= empty($item->description)?JText::_('COM_EMUNDUS_ATTACHMENTS_NO_DESC'):$item->description;
                    }
                    $div .= '</td></tr>';
                }
            }

            // Disable upload UI if
            if (!$block_upload) {

                if ($attachment->nb < $attachment->nbmax || $this->user->profile <= 4) {
                    $div .= '
                <tr>
                    <td>';
                ///Video
                if ($attachment->allowed_types == 'video' && $addpipe_activation == 1) {
                    $document->addStyleSheet("//cdn.addpipe.com/2.0/pipe.css" );
                    $document->addScript("//cdn.addpipe.com/2.0/pipe.js" );

                    $div .= '<div id="recorder-'.$attachment->id.'-'.$attachment->nb.'"></div>';
                    $div .= '<pre id="log"></pre>';

                    $div .= '<script type="text/javascript">
    
                    var pipeParams = {
                        size: '.$addpipe_size.',
                        qualityurl: "'.$addpipe_qualityurl.'", 
                        accountHash:"'.$addpipe_account_hash.'", 
                        payload:"{\"userId\":\"'.$this->user->id.'\",\"fnum\":\"'.$this->user->fnum.'\",\"aid\":\"'.$attachment->id.'\",\"lbl\":\"'.$attachment->lbl.'\",\"jobId\":\"'.$this->user->fnum.'|'.$attachment->id.'|'.date("Y-m-d_H:i:s").'\"}", 
                        eid:"'.$addpipe_eid.'", 
                        showMenu:'.$addpipe_showmenu.', 
                        mrt:'.(!empty($attachment->video_max_length) ? $attachment->video_max_length : $addpipe_mrt).',
                        sis:0,
                        asv:'.$addpipe_asv.', 
                        mv:0, 
                        st:1, 
                        ssb:1,
                        dup:'.$addpipe_dup.',
                        srec:'.$addpipe_srec.'
                    };

                    PipeSDK.insert("recorder-'.$attachment->id.'-'.$attachment->nb.'", pipeParams, function(recorderInserted){
     
                        //DESKTOP EVENTS API
                        recorderInserted.userHasCamMic = function(id,camNr, micNr){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_CAM_ACCESS').'");
                        }
            
                        recorderInserted.btRecordPressed = function(id){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("btRecordPressed("+args.join(\', \')+")");
                        }
            
                        recorderInserted.btStopRecordingPressed = function(id){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_STOP_RECORDING').'");
                        }
            
                        recorderInserted.btPlayPressed = function(id){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("btPlayPressed("+args.join(\', \')+")");
                        }
            
                        recorderInserted.btPausePressed = function(id){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("btPausePressed("+args.join(\', \')+")");
                        }
            
                        recorderInserted.onUploadDone = function(recorderId, streamName, streamDuration, audioCodec, videoCodec, fileType, audioOnly, location){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("onUploadDone("+args.join(\', \')+")");
                            recorderInserted.save();
                        }
            
                        recorderInserted.onCamAccess = function(id, allowed){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_CAM_ACCESS_READY').'");
                        }
            
                        recorderInserted.onPlaybackComplete = function(id){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("onPlaybackComplete("+args.join(\', \')+")");       
                        }
            
                        recorderInserted.onRecordingStarted = function(id){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_RECORDING').'");
                        }
            
                        recorderInserted.onConnectionClosed = function(id){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("onConnectionClosed("+args.join(\', \')+")");
                        }
            
                        recorderInserted.onConnectionStatus = function(id, status){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("onConnectionStatus("+args.join(\', \')+")");
                        }
            
                        recorderInserted.onMicActivityLevel = function(id, level){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("onMicActivityLevel("+args.join(\', \')+")");
                        }
            
                        recorderInserted.onFPSChange = function(id, fps){
                            //var args = Array.prototype.slice.call(arguments);
                            //__log("onFPSChange("+args.join(\', \')+")");
                        }
            
                        recorderInserted.onSaveOk = function(recorderId, streamName, streamDuration, cameraName, micName, audioCodec, videoCodec, filetype, videoId, audioOnly, location){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_RECORD_SAVED').'");
                
                            //reload page
                            recorderInserted.remove();
                            is_file_uploaded("'.$this->user->fnum.'","'.$attachment->id.'","'.$this->user->id.'");
                        }
            
                        //DESKTOP UPLOAD EVENTS API
                        recorderInserted.onFlashReady = function(id){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_CLICK_TO_RECORD').'");
                        }
            
                        recorderInserted.onDesktopVideoUploadStarted = function(recorderId, filename, filetype, audioOnly){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_UPLOADING').'");
                        }
            
                        recorderInserted.onDesktopVideoUploadSuccess = function(recorderId, filename, filetype, videoId, audioOnly, location){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_RECORD_SAVED').'");
                
                            //reload page
                            recorderInserted.remove();
                            is_file_uploaded('.$this->user->fnum.','.$attachment->id.','.$this->user->id.');
                        }
            
                        recorderInserted.onDesktopVideoUploadFailed = function(id, error){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_RECORD_FAILED').'");
                        }
            
                        //MOBILE EVENTS API
                        recorderInserted.onVideoUploadStarted = function(recorderId, filename, filetype, audioOnly){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_RECORD_SAVED').'");
                        }
    
                        recorderInserted.onVideoUploadSuccess = function(recorderId, filename, filetype, videoId, audioOnly, location){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_RECORD_SAVED').'");
                
                            //reload page
                            recorderInserted.remove();
                            is_file_uploaded("'.$this->user->fnum.'","'.$attachment->id.'","'.$this->user->id.'");
                        }
            
                        recorderInserted.onVideoUploadProgress = function(recorderId, percent){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_UPLOADING').'");
                        }
            
                        recorderInserted.onVideoUploadFailed = function(id, error){
                            //var args = Array.prototype.slice.call(arguments);
                            __log("'.JText::_('VIDEO_INSTR_RECORD_FAILED').'");
                        }
        
                    });
                    function __log(e, data) {
                        log.innerHTML += "\n" + e + " " + (data || "");
                    }
</script>';
            } else {
                $div .= '<form id="form-a'.$attachment->id.'" name="checklistForm" class="dropzone" action="'.JRoute::_('index.php?option=com_emundus&task=upload&duplicate='.$attachment->duplicate.'&Itemid='.$itemid).'" method="post" enctype="multipart/form-data">';
                $div .= '<input type="hidden" name="attachment" value="'.$attachment->id.'"/>
                <input type="hidden" name="duplicate" value="'.$attachment->duplicate.'"/>
                <input type="hidden" name="label" value="'.$attachment->lbl.'"/>
                <input type="hidden" name="required_desc" value="'.$this->required_desc.'"/>
                <div class="input-group em-fieldset-attachment-table-upload">';
                if ($this->show_shortdesc_input) {
                    $div .= '<div class="row"><div class="col-sm-12 em-description"><label><span>'.JText::_('COM_EMUNDUS_ATTACHMENTS_SHORT_DESC').'</span></label><input type="text" class="form-control" maxlength="80" name="description" placeholder="'.(($this->required_desc != 0)?JText::_('EMUNDUS_REQUIRED_FIELD'):'').'" /></div></div>';
                }
                if ($this->show_browse_button) {
                    $div .= '<div class="row" id="upload-files-'.$file_upload.'"><div class="col-sm-12"><label for="file" class="custom-file-upload"><input class="em-send-attachment" id="em-send-attachment-'.$file_upload.'" type="file" name="file" multiple onchange="processSelectedFiles(this)"/><span style="display: none;" >'.JText::_("COM_EMUNDUS_SELECT_UPLOAD_FILE").'</span></label>';
                }
                    $div .= '<input type="hidden" class="form-control" readonly="">';
                if ($this->show_browse_button) {
                    $div .= '<input class="btn btn-success em_send_uploaded_file" name="sendAttachment" type="submit" onclick="document.pressed=this.name" value="'.JText::_('COM_EMUNDUS_ATTACHMENTS_SEND_ATTACHMENT').'"/></div></div>';
                }
                $div .= '</div>';

                $div .= '<script>
                var maxFilesize = "'.ini_get("upload_max_filesize").'";

    Dropzone.options.formA'.$attachment->id.' =  {
        maxFiles: '.$attachment->nbmax .',
        maxFilesize: maxFilesize.substr(0, maxFilesize.length-1), // MB
        dictDefaultMessage: "'.JText::_('COM_EMUNDUS_ATTACHMENTS_UPLOAD_DROP_FILE_OR_CLICK').'",
        dictInvalidFileType: "'. JText::_('COM_EMUNDUS_WRONG_FORMAT').' '.$attachment->allowed_types.'",
        url: "index.php?option=com_emundus&task=upload&duplicate='.$attachment->duplicate.'&Itemid='.$itemid.'&format=raw",

        accept: function(file, done) {
            var sFileName = file.name;
            var sFileExtension = sFileName.split(".")[sFileName.split(".").length - 1].toLowerCase();

            if (sFileExtension == "php") {
              done("'.JText::_('COM_EMUNDUS_WRONG_FORMAT').' '.$attachment->allowed_types.'");
            } else {
                var allowedExtension = "'.$attachment->allowed_types.'";
                var n = allowedExtension.indexOf(sFileExtension);
                
                var required_desc =  document.querySelector("#form-a'.$attachment->id.' input[name=\'required_desc\']").value;
                if (document.querySelector("#form-a'.$attachment->id.' input[name=\'description\']") && required_desc == 1) {
                    var desc =  document.querySelector("#form-a'.$attachment->id.' input[name=\'description\']").value;
                }
                
                if (n >= 0) {
                    if (required_desc == 1 && desc.trim() === "") {
                        Swal.fire({
                            position: "top",
                            type: "warning",
                            title: "'.JText::_("COM_EMUNDUS_ERROR_DESCRIPTION_REQUIRED").'",
                            confirmButtonText: "'.JText::_("COM_EMUNDUS_SWAL_OK_BUTTON").'",
                            showCancelButton: false,
                            customClass: {
                              title: "em-swal-title",
                              confirmButton: "em-swal-confirm-button",
                              actions: "em-swal-single-action",
                            },
                        });
                        done("'.JText::_('COM_EMUNDUS_ERROR_DESCRIPTION_REQUIRED').'");
                        this.removeFile(file);
                    } else {
                        done();
                    }
                } else {
                    Swal.fire({
                            position: "top",
                            type: "warning",
                            title: "'. JText::_("COM_EMUNDUS_WRONG_FORMAT").' '.$attachment->allowed_types.'",
                            confirmButtonText: "'. JText::_("COM_EMUNDUS_SWAL_OK_BUTTON").'",
                            showCancelButton: false,
                            customClass: {
                              title: "em-swal-title",
                              confirmButton: "em-swal-confirm-button",
                              actions: "em-swal-single-action",
                            },
                        });
                    done("'. JText::_('COM_EMUNDUS_WRONG_FORMAT').' '.$attachment->allowed_types.'");
                    this.removeFile(file);
                }
            }
        },

        init: function() {

          this.on("maxfilesexceeded", function(file) {
            this.removeFile(file);
            alert("'. JText::_('COM_EMUNDUS_ATTACHMENTS_NO_MORE').' : '.$attachment->value .'. '.JText::_('COM_EMUNDUS_ATTACHMENTS_MAX_ALLOWED').' '.$attachment->nbmax .'");
          });

          this.on("success", function(file, responseText) {
            // Handle the responseText here. For example, add the text to the preview element:
            var response = JSON.parse(responseText);
            var id = response["id"];
            
            if (!response["status"]) {
                // Remove the file preview.
                this.removeFile(file);
                Swal.fire({
                    position: "top",
                    type: "warning",
                    title: response["message"],
                    confirmButtonText: "'.JText::_("COM_EMUNDUS_SWAL_OK_BUTTON").'",
                    showCancelButton: false,
                    customClass: {
                       title: "em-swal-title",
                       confirmButton: "em-swal-confirm-button",
                       actions: "em-swal-single-action",
                    },
                });
            } else {
                document.location.reload(true);
    
                // Change icon on fieldset
                document.getElementById("l'.$attachment->id.'").className = "need_ok";
                document.getElementById("'.$attachment->id.'").className = "need_ok";
    
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
                            document.getElementById("l'.$attachment->id.'").className = "";
                            document.getElementById("'.$attachment->id.'").className = "";
                            alert("'.JText::_('COM_EMUNDUS_ATTACHMENTS_DELETED').'");
                        }
    
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                    }
                  });
                });
                // Add the button to the file preview element.
                file.previewElement.appendChild(removeButton);
            }
          });
          this.on("error", function(file, responseText) {
              this.removeFile(file);
              Swal.fire({
                    position: "top",
                    type: "warning",
                    text: responseText,
                    confirmButtonText: "'.JText::_("COM_EMUNDUS_SWAL_OK_BUTTON").'",
                    showCancelButton: false,
                    customClass: {
                       title: "em-swal-title",
                       confirmButton: "em-swal-confirm-button",
                       actions: "em-swal-single-action",
                    },
                });
          });
        }
    }
    </script>';
                    $div .= '</form>';
                }
                    $div .= '</td>
                </tr>
                <tr class="em-allowed-files">
                    <td>
                    <p><em>'. JText::_('COM_EMUNDUS_ATTACHMENTS_PLEASE_ONLY').' '.$attachment->allowed_types.'</em></p><p><em>'.JText::_('COM_EMUNDUS_ATTACHMENTS_MAX_ALLOWED').' '.$attachment->nbmax .'</em></p>
                    </td>
                </tr>';
                } else {
                    $div .= '
                <tr class="em-no-more-files">
                    <td>
                    <p>'. JText::_('COM_EMUNDUS_ATTACHMENTS_NO_MORE').' '.$attachment->value .'<br />'.JText::_('COM_EMUNDUS_ATTACHMENTS_MAX_ALLOWED').' '.$attachment->nbmax .'</p>
                    </td>
                </tr>';

                $div .= '</tbody>';
                }
            } else {
                if ($this->isLimitObtained === true) {
                    $div .= JError::raiseNotice(401, JText::_('LIMIT_OBTAINED'));
                } else {
                    $div .= JError::raiseNotice(401, JText::sprintf('COM_EMUNDUS_PERIOD', strftime("%d/%m/%Y %H:%M", strtotime($this->user->start_date) ), strftime("%d/%m/%Y %H:%M", strtotime($this->user->end_date) )));
                }
            }
            $div .= '</table></div></fieldset>';
            if ($attachment->mandatory) {
	            $attachment_list_mand .= $div;
            } else {
	            $attachment_list_opt .= $div;
            }

            $file_upload++;
        }
    ?>


    <div class="row">
      <div class="col-md-<?= (int)(12/$this->show_nb_column); ?>">
    <?php
        if ($attachment_list_mand != '') {
           echo '<div id="attachment_list_mand" class="em-container-attachments"><h1 class="em-titleDocMand">'.JText::_('COM_EMUNDUS_ATTACHMENTS_MANDATORY_DOCUMENTS').'</h1>'.$attachment_list_mand.'</div>';
        }
    ?>
      </div>
    <?php
      if ($this->show_nb_column > 1) {
        echo '<div class="ui vertical divider"></div>';
      }
    ?>
      <div class="col-md-<?= (int)(12/$this->show_nb_column); ?>">
    <?php
        if ($attachment_list_opt != '') {
           echo '<div id="attachment_list_opt" class="em-container-attachmentsOpt"><h1 class="em-titleDocOpt">'.JText::_('COM_EMUNDUS_ATTACHMENTS_OPTIONAL_DOCUMENTS').'</h1>'.$attachment_list_opt.'</div>';
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

$(document).ready(function() {
    $('.em_send_uploaded_file').attr("disabled", "disabled");

    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {

        var input = $(this).parents('.input-group').find(':text'),
            log = numFiles > 1 ? numFiles + ' <?= JText::_("FILES_SELECTED"); ?>' : label;

        if( input.length ) {
            input.val(log);
        } else {
            if( log ) alert(log);
        }
    });
});

$(document).on('click', '.em_form .document', function(f) {
    var id = $(this).attr('id');
    $("fieldset").removeClass( "hover" );
    $("#a"+id).addClass( "hover" );
});

function toggleVisu(baliseId) {
    if (document.getElementById && document.getElementById(baliseId) != null) {
        if (document.getElementById(baliseId).style.visibility=='visible') {
            document.getElementById(baliseId).style.visibility='hidden';
            document.getElementById(baliseId).style.display='none';
        } else {
            document.getElementById(baliseId).style.visibility='visible';
            document.getElementById(baliseId).style.display='block';
        }
    }
}
/*
<?php foreach($this->attachments as $attachment) { ?>
  document.getElementById('<?= $attachment->id; ?>').style.visibility='<?= ($attachment->mandatory && $attachment->nb==0)?'visible':'hidden'; ?>';
  document.getElementById('<?= $attachment->id; ?>').style.display='<?= ($attachment->mandatory && $attachment->nb==0)?'block':'none'; ?>';
<?php } ?>

function OnSubmitForm() {
    var btn = document.getElementsByName(document.pressed);
    for(i=0 ; i<btn.length ; i++) {
        btn[i].disabled="disabled";
        btn[i].value="<?= JText::_('COM_EMUNDUS_ATTACHMENTS_SENDING_ATTACHMENT'); ?>";
    }
    switch(document.pressed) {
        case 'sendAttachment':
            document.checklistForm.action ="index.php?option=com_emundus&task=upload&Itemid=<?= $itemid; ?>";
        break;
        default: return false;
    }
    return true;
}
*/

function OnSubmitForm() {
    var btn = document.getElementsByName(document.pressed);

    for(i=0 ; i<btn.length ; i++) {
        btn[i].disabled="disabled";
        btn[i].value="<?= JText::_('COM_EMUNDUS_ATTACHMENTS_SENDING_ATTACHMENT'); ?>";
    }

    switch(document.pressed) {
        case 'sendAttachment':
            document.checklistForm.action ="<?= JURI::base();?>index.php?option=com_emundus&task=upload&Itemid=<?= $itemid; ?>";
            break;
        default: return false;
    }
    return true;
}



var hash = window.location.hash;
if (hash != '') {
    $(hash).addClass("ui warning message");
}

function processSelectedFiles(fileInput) {
    var files = fileInput.files;
    var max_post_size = <?= return_bytes(ini_get('post_max_size'));?>;

    var row = fileInput.parentNode.parentNode.parentNode.id;
    var rowId = document.getElementById(row);
    if (files[0].size < max_post_size) {
        if ($(rowId).find('.em-added-file').length > 0) {
            if (files.length > 0) {
                $(rowId).find('.em-added-file')[0].innerHTML = files[0].name;
            } else {
                $(rowId).find('.em-added-file')[0].innerHTML = "";
            }
        } else {
            var fileParagraphe = document.createElement("p");
            fileParagraphe.className = "em-added-file";
            if (files.length > 0) {
                fileParagraphe.innerHTML = files[0].name;
            } else {
                fileParagraphe.innerHTML = "";
            }
            rowId.append(fileParagraphe);
        }
        $(rowId).find( ".em_send_uploaded_file" ).removeAttr("disabled");
    } else {
        if ($(rowId).find('.em-added-file').length > 0) {
            $(rowId).find('.em-added-file')[0].innerHTML = "<?= JText::_('COM_EMUNDUS_ATTACHMENTS_ERROR_FILE_TOO_BIG')?>";
        } else {
            var fileParagraphe = document.createElement("p");
            fileParagraphe.className = "em-added-file em-added-file-error";
            fileParagraphe.innerHTML = "<?= JText::_('COM_EMUNDUS_ATTACHMENTS_ERROR_FILE_TOO_BIG')?>";
            rowId.append(fileParagraphe);
        }
        $(rowId).find( ".em_send_uploaded_file" ).attr("disabled","disabled");
    }
}

<?php if ($this->notify_complete_file == 1 && !$block_upload && $this->attachments_prog >= 100 && $this->forms_prog >= 100) :?>
    $(document).ready(() => {
        Swal.fire({
            position: 'top',
            type: 'success',
            title: '<?= JText::_('COM_EMUNDUS_CHECKLIST_FILE_COMPLETE'); ?>',
            confirmButtonText: '<?= JText::_('COM_EMUNDUS_CHECKLIST_SEND_FILE'); ?>',
            showCancelButton: true,
            cancelButtonText: '<?= JText::_('COM_EMUNDUS_ATTACHMENTS_EM_CONTINUE'); ?>'
        })
        .then(confirm => {
            if (confirm.value) {
                window.location.href = '<?= $this->confirm_form_url; ?>';
            }
        })
    });
<?php endif; ?>

//ADDPIPE check if video is uploaded. If yes, reaload page
function is_file_uploaded(fnum, aid, applicant_id) {
    setInterval(function(){

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'index.php?option=com_emundus&view=webhook&controller=webhook&task=is_file_uploaded&format=raw',
            data: ({
                fnum: fnum,
                aid: aid,
                applicant_id: applicant_id
            }),
            success: function(result) {
                //console.log(result.status + " :: " + result.fnum + " :: " + result.aid + " :: " + result.applicant_id + " :: " + result.user_id + " :: " + result.user_fnum + " :: " + result.query);
                if (result.status) {
                    clearInterval();
                    window.location.reload(true);
                }
            },
            error: function(jqXHR) {
                console.log("ERROR: "+jqXHR.responseText);
            }
        });
    }, 500);

}

</script>
