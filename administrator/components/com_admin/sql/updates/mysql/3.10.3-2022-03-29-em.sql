UPDATE jos_fabrik_forms
SET params = '{"outro":"","copy_button":"0","copy_button_label":"SAVE as a copy","copy_button_class":"","copy_icon":"","copy_icon_location":"before","reset_button":"0","reset_button_label":"RESET","reset_button_class":"btn-warning","reset_icon":"","reset_icon_location":"before","apply_button":"0","apply_button_label":"APPLY","apply_button_class":"","apply_icon":"","apply_icon_location":"before","goback_button":"0","goback_button_label":"GO_BACK","goback_button_class":"","goback_icon":"","goback_icon_location":"before","submit_button":"1","submit_button_label":"SEND","save_button_class":"btn-primary","save_icon":"","save_icon_location":"before","submit_on_enter":"0","delete_button":"0","delete_button_label":"Delete","delete_button_class":"btn-danger","delete_icon":"","delete_icon_location":"before","ajax_validations":"0","ajax_validations_toggle_submit":"0","submit-success-msg":"","suppress_msgs":"0","show_loader_on_submit":"0","spoof_check":"1","multipage_save":"1","note":"","labels_above":"0","labels_above_details":"0","pdf_template":"","pdf_orientation":"portrait","pdf_size":"letter","pdf_include_bootstrap":"1","admin_form_template":"","admin_details_template":"","show-title":"0","print":"0","email":"0","pdf":"0","show-referring-table-releated-data":"0","tiplocation":"tip","process-jplugins":"2","plugin_state":["1","1","1"],"only_process_curl":["onLoad","onBeforeCalculations","onAfterProcess"],"form_php_file":["-1","emundus-attachment.php","-1"],"form_php_require_once":["0","0","0"],"curl_code":["$student_id=JRequest::getVar(''student_id'', null,''get'');$student=JUser::getInstance($student_id);echo ''<h1>''.$student->name.''<\\/h1>'';\\r\\nJHTML::stylesheet( JURI::Base().''media\\/com_fabrik\\/css\\/fabrik.css'' );\\r\\necho ''<script src=\\"''.JURI::Base().''media\\/com_fabrik\\/js\\/lib\\/head\\/head.min.js\\" type=\\"text\\/javascript\\"><\\/script>'';","","echo \\"<script>\\r\\n  window.setTimeout(function() {\\r\\n\\t  window.parent.postMessage(''addFileToFnum'', ''*'');\\r\\n\\r\\n\\t\\tparent.$(''#em-modal-actions'').modal(''hide'');\\r\\n\\t}, 1500);\\r\\n<\\/script>\\";\\r\\n\\tdie(''<h1><img src=\\"''.JURI::Base().''\\/media\\/com_emundus\\/images\\/icones\\/admin_val.png\\" width=\\"80\\" height=\\"80\\" align=\\"middle\\" \\/> ''.JText::_(\\"SAVED\\").''<\\/h1>'');"],"plugins":["php","php","php"],"plugin_locations":["front","front","both"],"plugin_events":["both","both","both"],"plugin_description":["header","attachment","saved"]}'
WHERE id = 67 AND label = 'SETUP_UPLOAD_FILE_FOR_APPLICANT'