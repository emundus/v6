INSERT INTO jos_extensions (package_id, name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data, system_data, checked_out, checked_out_time, ordering, state)
VALUES (0, 'MOD_EMUNDUS_VERSION_SYS_XML', 'module', 'mod_emundus_version', '', 0, 1, 1, 0, '{"name":"MOD_EMUNDUS_VERSION_SYS_XML","type":"module","creationDate":"April 2022","author":"Brice HUBINET","copyright":"Copyright (C) 2022 eMundus. All rights reserved.","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.30.0","description":"MOD_EMUNDUS_VERSION_XML_DESCRIPTION","group":"","filename":"mod_emundus_version"}', '{}', '', '', 0, '2022-02-22 16:28:57', 0, 0);

INSERT INTO jos_modules (asset_id, title, note, content, ordering, position, checked_out, checked_out_time, publish_up, publish_down, published, module, access, showtitle, params, client_id, language)
VALUES (0, 'Release notes', '', null, 1, 'content-top-a', 0, '2022-02-22 16:28:57', '2022-02-22 16:28:57', '2099-02-22 16:28:57', 1, 'mod_emundus_version', 7, 0, '{"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, '*');

INSERT INTO jos_modules_menu (moduleid, menuid) VALUES (LAST_INSERT_ID(), 0);

create table jos_emundus_setup_status_repeat_tags
(
    id        int auto_increment
        primary key,
    parent_id int  null,
    tags      int  null,
    params    text null
);

create index fb_parent_fk_parent_id_INDEX
    on jos_emundus_setup_status_repeat_tags (parent_id);

create index fb_repeat_el_tags_INDEX
    on jos_emundus_setup_status_repeat_tags (tags);

update jos_menu set published = 0 where link LIKE 'https://www.emundus.fr/ressources/centre-aide';

update jos_content set title = 'Indicateurs' where alias = 'tableau-de-bord';

update jos_content set introtext = '' where alias = 'tableau-de-bord';

SELECT @menu_id:=id
FROM jos_menu
WHERE link LIKE 'index.php?option=com_fabrik&view=form&formid=150&rowid=&jos_emundus_campaign_candidature___applicant_id={applicant_id}&jos_emundus_campaign_candidature___copied=1&jos_emundus_campaign_candidature___fnum={fnum}&jos_emundus_campaign_candidature___status=2&tmpl=component&iframe=1';

update jos_menu set title = 'Copier/Déplacer le dossier' WHERE id = @menu_id;
update jos_falang_content set value = 'Copy/Move the file' WHERE reference_field LIKE 'title' and reference_id = @menu_id;

UPDATE jos_fabrik_forms
SET params = '{"outro":"","copy_button":"0","copy_button_label":"SAVE as a copy","copy_button_class":"","copy_icon":"","copy_icon_location":"before","reset_button":"0","reset_button_label":"RESET","reset_button_class":"btn-warning","reset_icon":"","reset_icon_location":"before","apply_button":"0","apply_button_label":"APPLY","apply_button_class":"","apply_icon":"","apply_icon_location":"before","goback_button":"0","goback_button_label":"GO_BACK","goback_button_class":"","goback_icon":"","goback_icon_location":"before","submit_button":"1","submit_button_label":"SEND","save_button_class":"btn-primary","save_icon":"","save_icon_location":"before","submit_on_enter":"0","delete_button":"0","delete_button_label":"Delete","delete_button_class":"btn-danger","delete_icon":"","delete_icon_location":"before","ajax_validations":"0","ajax_validations_toggle_submit":"0","submit-success-msg":"","suppress_msgs":"0","show_loader_on_submit":"0","spoof_check":"1","multipage_save":"1","note":"","labels_above":"0","labels_above_details":"0","pdf_template":"","pdf_orientation":"portrait","pdf_size":"letter","pdf_include_bootstrap":"1","admin_form_template":"","admin_details_template":"","show-title":"0","print":"0","email":"0","pdf":"0","show-referring-table-releated-data":"0","tiplocation":"tip","process-jplugins":"2","plugin_state":["1","1","1"],"only_process_curl":["onLoad","onBeforeCalculations","onAfterProcess"],"form_php_file":["-1","emundus-attachment.php","-1"],"form_php_require_once":["0","0","0"],"curl_code":["$student_id=JRequest::getVar(''student_id'', null,''get'');$student=JUser::getInstance($student_id);echo ''<h1>''.$student->name.''<\\/h1>'';\\r\\nJHTML::stylesheet( JURI::Base().''media\\/com_fabrik\\/css\\/fabrik.css'' );\\r\\necho ''<script src=\\"''.JURI::Base().''media\\/com_fabrik\\/js\\/lib\\/head\\/head.min.js\\" type=\\"text\\/javascript\\"><\\/script>'';","","echo \\"<script>\\r\\n  window.setTimeout(function() {\\r\\n    window.parent.postMessage(''addFileToFnum'', ''*'');\\r\\n\\r\\n\\t\\tparent.$(''#em-modal-actions'').modal(''hide'');\\r\\n\\t}, 1500);\\r\\n<\\/script>\\";\\r\\n\\tdie(''<div style=\\"text-align: center\\"><img src=\\"''.JURI::base().''images\\/emundus\\/animations\\/checked.gif\\" width=\\"200\\" height=\\"200\\" align=\\"middle\\" \\/><\\/div>'');"],"plugins":["php","php","php"],"plugin_locations":["front","front","both"],"plugin_events":["both","both","both"],"plugin_description":["header","attachment","saved"]}'
WHERE id = 67;

DELETE FROM jos_extensions WHERE element IN ('com_emundus_onboard','com_emundus_messenger') and type LIKE 'component';
DELETE FROM jos_menu WHERE path LIKE 'tchooz' and menutype LIKE 'main';
