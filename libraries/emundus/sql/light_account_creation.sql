update jos_fabrik_elements set published = 0 where name like 'civility' and group_id = 640;
update jos_fabrik_elements set published = 0 where name like 'captcha' and group_id = 640;
update jos_fabrik_elements set published = 0 where name like 'confirm_email' and group_id = 640;
update jos_fabrik_elements set published = 0 where name like 'confirm_email' and group_id = 640;

INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('PASSWORD_TIP_TITLE', 'fr-FR', '', '', MD5(''), MD5(''), 'fr-FR.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);
INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('PASSWORD_TIP_TITLE', 'en-GB', '', '', MD5(''), MD5(''), 'en-GB.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);

INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('USER_PASSWORD_TIP', 'fr-FR', 'Le mot de passe doit comporter', 'Le mot de passe doit comporter', MD5('Le mot de passe doit comporter'), MD5('Le mot de passe doit comporter'), 'fr-FR.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);
INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('USER_PASSWORD_TIP', 'en-GB', 'The password must contain', 'The password must contain', MD5('The password must contain'), MD5('The password must contain'), 'en-GB.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);

INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('COM_USERS_LOGIN_NO_ACCOUNT', 'fr-FR', 'Pas encore de compte ?', 'Pas encore de compte ?', MD5('Pas encore de compte ?'), MD5('Pas encore de compte ?'), 'fr-FR.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);
INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('COM_USERS_LOGIN_NO_ACCOUNT', 'en-GB', 'No account yet?', 'No account yet?', MD5('No account yet?'), MD5('No account yet?'), 'en-GB.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);

INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('COM_USERS_SUBMIT_RESET', 'fr-FR', 'Réinitialiser mon mot de passe', 'Réinitialiser mon mot de passe', MD5('Réinitialiser mon mot de passe'), MD5('Réinitialiser mon mot de passe'), 'fr-FR.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);
INSERT INTO jos_emundus_setup_languages (tag, lang_code, override, original_text, original_md5, override_md5, location, type, reference_id, reference_table, reference_field, published, created_by, created_date, modified_by, modified_date)
VALUES ('COM_USERS_SUBMIT_RESET', 'en-GB', 'Reset my password', 'Reset my password', MD5('Reset my password'), MD5('Reset my password'), 'en-GB.override.ini', 'override', null, 'fabrik_elements', 'label', 1, 62, '2022-06-30 08:28:03', null, null);

INSERT INTO jos_fabrik_elements (name, group_id, plugin, label, checked_out, checked_out_time, created, created_by, created_by_alias, modified, modified_by, width, height, `default`, hidden, eval, ordering, show_in_list_summary, filter_type, filter_exact_match, published, link_to_detail, primary_key, auto_increment, access, use_in_page_title, parent_id, params)
VALUES ('password_tip', 640, 'calc', 'PASSWORD_TIP_TITLE', 0, '2022-07-13 11:47:27', '2022-07-13 11:47:27', 62, 'sysadmin', '2022-07-15 12:08:47', 62, 0, 0, '', 0, 0, 11, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"calc_calculation":"$params = JComponentHelper::getParams(''com_users'');\\r\\n$min_length = $params->get(''minimum_length'');\\r\\n$min_int = $params->get(''minimum_integers'');\\r\\n$min_sym = $params->get(''minimum_symbols'');\\r\\n$min_up = $params->get(''minimum_uppercase'');\\r\\n$min_low = $params->get(''minimum_lowercase'');\\r\\n\\r\\n$password = ''{jos_emundus_users___password_raw}'';\\r\\n\\r\\n$tip_text = ''<p>'' . JText::_(''USER_PASSWORD_TIP'') . ''<\\/p>'';\\r\\n\\r\\nif(strlen($password) >= (int)$min_length) {\\r\\n    $min_length_validation = ''<span class=\\"material-icons em-mr-8 em-main-500-color\\" style=\\"font-size: 16px\\">check_circle<\\/span>'';\\r\\n} else {\\r\\n    $min_length_validation = ''<span class=\\"material-icons em-mr-8 em-text-neutral-300\\" style=\\"font-size: 16px\\">circle<\\/span>'';\\r\\n}\\r\\n\\r\\n\\r\\n$tip_text .= ''<div class=\\"em-flex-row em-mt-8\\">''.$min_length_validation.JText::sprintf(''USER_PASSWORD_MIN_LENGTH'', $min_length).''<\\/div>'';\\r\\n\\r\\nif ((int)$min_int > 0) {\\r\\n    preg_match_all(''~[0-9]~'', $password, $matches);\\r\\n    if(sizeof($matches[0]) >= (int)$min_int) {\\r\\n        $min_int_validation = ''<span class=\\"material-icons em-mr-8 em-main-500-color\\" style=\\"font-size: 16px\\">check_circle<\\/span>'';\\r\\n    } else {\\r\\n        $min_int_validation = ''<span class=\\"material-icons em-mr-8 em-text-neutral-300\\" style=\\"font-size: 16px\\">circle<\\/span>'';\\r\\n    }\\r\\n\\r\\n    $tip_text .= ''<div class=\\"em-flex-row em-mt-8\\">''.$min_int_validation.JText::sprintf(''USER_PASSWORD_MIN_INT'', $min_int).''<\\/div>'';\\r\\n}\\r\\nif ((int)$min_sym > 0) {\\r\\n    preg_match_all(''~[?,*,+,=,\\u20ac,\\u00a3,-,_,),(,\\u00a7,&,@,%,:]~'', $password, $matches_sym);\\r\\n    if(sizeof($matches_sym[0]) >= (int)$min_sym) {\\r\\n        $min_sym_validation = ''<span class=\\"material-icons em-mr-8 em-main-500-color\\" style=\\"font-size: 16px\\">check_circle<\\/span>'';\\r\\n    } else {\\r\\n        $min_sym_validation = ''<span class=\\"material-icons em-mr-8 em-text-neutral-300\\" style=\\"font-size: 16px\\">circle<\\/span>'';\\r\\n    }\\r\\n    \\r\\n    $tip_text .= ''<div class=\\"em-flex-row em-mt-8\\">''.$min_sym_validation.JText::sprintf(''USER_PASSWORD_MIN_SYM'', $min_sym).''<\\/div>'';\\r\\n}\\r\\nif ((int)$min_up > 0) {\\r\\n    preg_match_all(''~[A-Z]~'', $password, $matches_up);\\r\\n    if(sizeof($matches_up[0]) >= (int)$min_up) {\\r\\n        $min_up_validation = ''<span class=\\"material-icons em-mr-8 em-main-500-color\\" style=\\"font-size: 16px\\">check_circle<\\/span>'';\\r\\n    } else {\\r\\n        $min_up_validation = ''<span class=\\"material-icons em-mr-8 em-text-neutral-300\\" style=\\"font-size: 16px\\">circle<\\/span>'';\\r\\n    }\\r\\n    \\r\\n    $tip_text .= ''<div class=\\"em-flex-row em-mt-8\\">''.$min_up_validation.JText::sprintf(''USER_PASSWORD_MIN_UPPER'', $min_up).''<\\/div>'';\\r\\n}\\r\\nif ((int)$min_low > 0) {\\r\\n    $tip_text .= ''<div class=\\"em-flex-row em-mt-8\\"><span class=\\"material-icons em-mr-8 em-text-neutral-300\\" style=\\"font-size: 16px\\">circle<\\/span>''.JText::sprintf(''USER_PASSWORD_MIN_LOWER'', $min_low).''<\\/div>'';\\r\\n}\\r\\n\\r\\nreturn $tip_text;","calc_format_string":"","calc_on_save_only":"0","calc_ajax":"1","calc_ajax_observe_all":"0","calc_ajax_observe":"","calc_on_load":"1","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}');
alter table jos_emundus_users add password_tip VARCHAR(255) default null;

select @password_elt := id from jos_fabrik_elements where name = 'password' and group_id = 640;
select @campaign_id_elt := id from jos_fabrik_elements where name = 'campaign_id' and group_id = 640;
INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@password_elt, 'keyup', 'var password_check = document.getElementById(&#039;jos_emundus_users___password_check&#039;);
var value = this.get(&#039;value&#039;);
password_check.value = value;', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group640","js_e_condition":"","js_e_value":"","js_published":"1"}');
UPDATE jos_fabrik_jsactions set code = 'var regex = /[#${};&lt;&gt;]/;

var password_value = this.form.formElements.get(&#039;jos_emundus_users___password&#039;).get(&#039;value&#039;);

var password = this.form.formElements.get(&#039;jos_emundus_users___password&#039;);
console.log(password_value.match(regex));
if(password_value.match(regex) != null){
Swal.fire(
  &#039;Info&#039;,
  &#039;The character #${};&lt;&gt; are forbidden&#039;
);
password.set(&#039;&#039;);
}
' WHERE element_id = @password_elt AND action LIKE 'change';

update jos_fabrik_forms set params = JSON_REPLACE(params,'$.ajax_validations', '0') WHERE id = 307;

update jos_fabrik_elements
set `default` = '$jinput = JFactory::getApplication()->input;
if(empty($jinput->get->getInt(''cid''))){
  $campaign = JFactory::getSession()->get(''cid'');
  JFactory::getSession()->clear(''cid'');

  return $campaign;
} else {
  return $jinput->get->getInt(''cid'');
}
',
params = JSON_REPLACE(params,'$.validations', '')
WHERE name like 'campaign_id' and group_id = 640;
INSERT INTO jos_fabrik_jsactions (element_id, action, code, params) VALUES (@campaign_id_elt, 'load', 'var value = this.get(&#039;value&#039;);

if(value != &#039;&#039;){
  document.getElementsByClassName(&#039;fb_el_jos_emundus_users___campaign_id&#039;)[0].style.display = &#039;block&#039;;
}', '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group640","js_e_condition":"","js_e_value":"","js_published":"1"}');

update jos_fabrik_elements
set params = JSON_REPLACE(params,'$.rollover', '')
WHERE name like 'password' and group_id = 640;

update jos_fabrik_elements
set params = JSON_REPLACE(params,'$.tipseval', '0')
WHERE name like 'password' and group_id = 640;

UPDATE jos_modules
SET params = JSON_REPLACE(params, '$.mod_em_campaign_link', 'connexion')
where module like 'mod_emundus_campaign';
UPDATE jos_falang_content
SET value = JSON_REPLACE(value, '$.mod_em_campaign_link', 'login')
where language_id = 1 AND reference_table like 'modules' and reference_field like 'params' and reference_id IN (SELECT id from jos_modules where module like 'mod_emundus_campaign');
UPDATE jos_falang_content
SET value = JSON_REPLACE(value, '$.mod_em_campaign_link', 'connexion')
where language_id = 2 AND reference_table like 'modules' and reference_field like 'params' and reference_id IN (SELECT id from jos_modules where module like 'mod_emundus_campaign');

UPDATE jos_modules
SET published = -1
where module like 'mod_login';
