<?php
/**
 * Default Form Template: Custom CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

/**
 * If you need to make small adjustments or additions to the CSS for a Fabrik
 * template, you can create a custom_css.php file, which will be loaded after
 * the main template_css.php for the template.
 *
 * This file will be invoked as a PHP file, so the view type, form ID and row ID
 * can be used in order to narrow the scope of any style changes.  A new form will
 * have an ID of "form_X" (where X is the form's numeric ID), while edit forms (for existing
 * rows) will have an ID of "form_X_Y" (where Y is the rowid).  Detail views will always
 * be of the format "details_X_Y".
 *
 * So to apply styles for (say) form ID 123, you would use ...
 *
 * #form_123, #form_123_$rowid { ... }
 *
 * Or to style for any form / row, it would just be ...
 *
 * #$form { ... }
 *
 * See examples below, which you should remove if you copy this file.
 *
 * Don't edit anything outside of the BEGIN and END comments.
 *
 * For more on custom CSS, see the Wiki at:
 *
 * http://www.fabrikar.com/forums/index.php?wiki/form-and-details-templates/#the-custom-css-file
 *
 * NOTE - for backward compatibility with Fabrik 2.1, and in case you
 * just prefer a simpler CSS file, without the added PHP parsing that
 * allows you to be be more specific in your selectors, we will also include
 * a custom.css we find in the same location as this file.
 *
 */

header('Content-type: text/css');
$c = (int) $_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
$rowid = isset($_REQUEST['rowid']) ? $_REQUEST['rowid'] : '';
$form = $view . '_' . $c;
if ($rowid !== '')
{
    $form .= '_' . $rowid;
}
echo <<<EOT

/* BEGIN - Your CSS styling starts here */
/* Add a padding on list program-year-page for example */

.program-year-page #g-main-mainbody, .campaign-page #g-main-mainbody, .users-profile-setup-page #g-main-mainbody, .emails-setup-page #g-main-mainbody,
.emails-declancheur-setup-page #g-main-mainbody, .courriers-setup-page #g-main-mainbody, .documents-type-candidacy-page #g-main-mainbody,
.tags-setup-page #g-main-mainbody, .groups-setup-page #g-main-mainbody, .files-status-page #g-main-mainbody,
.layout-showgrouprights #g-main-mainbody{
    padding: 0 20px;
}

.add-program-year-page #g-container-main, .add-campaign-page #g-main-mainbody, .add-new-program-page #g-main-mainbody, .import-csv-page #g-main-mainbody{
    padding: 0;
    width:75rem;
    margin:auto;
}


/* FORMULAIRE */

.view-form #g-sidebar{
  position: sticky;
  top: 0;
}

.view-form #g-page-surround{
  overflow: visible;
}

.controls .fabrikElement .radio.btn-radio.btn-group label.btn-default.btn:not(.active) {
    background-color: #c6c6c6;
        margin-left: 0px;
}

.modal {
    position: relative !important;
    background-color: #fff !important;
    margin: 0 !important;
    width: 100% !important;
    left: 0 !important;
    top: 0 !important;
    border: none !important;
    box-shadow: none !important;
}

.view-registration main#g-main-mainbody {
    padding-right: 0;
}


.view-form .fabrikForm .fabrikActions.form-actions, .view-details .fabrikForm .fabrikActions.form-actions{
    padding: 16px 0 0 0;
    margin-bottom: 0;
    border-top: solid 1px #EDEDED;
    padding-top: 16px;
}


.view-form.view-registration .fabrikGroup, 
.view-form.em-formRegistrationCenter .fabrikGroup {
    padding: 0px !important;
}

.view-form p.select-program + form .fabrikGroup {
    background: #fff;
}

.view-form.error-report-page .fabrikGroup {
    background: #fff;
}

.view-form:not(.em-formRegistrationCenter) .size-100 .size-100 .fabrikGroup {
    background: #fff;
}

.view-form.view-registration .size-100 .size-100 .fabrikGroup {
    background: #fff;
    margin-bottom: 32px;
}

.view-checklist #attachment_list_mand .fieldset, .view-checklist #attachment_list_opt .fieldset {
    background: #fafafa;
    box-shadow: none; 
}

.view-form .fabrikSubGroup > div[data-role="group-repeat-intro"] {
    font-size: 1.5rem;
}

#form_308_5 .form-horizontal {
    padding: 0;
    margin-top: 20px;
}

.moduletable.send-application-file {
    background: none !important;
    padding: 0 !important;
}

.active .em_form {
    background-attachment: scroll;
    background-clip: border-box;
    background-color: transparent;
    background-image: none;
    background-origin: padding-box;
    background-position: 0 0;
    background-repeat: repeat;
    background-size: auto auto;
    color: #4E5B6D;
}

.em_form {
    border-bottom: none;
    padding: 8px 0;
}

.view-details .ui.attached.segment, .view-form .ui.attached.segment, .view-checklist .ui.attached.segment  {
    background: #3e8ac5;
    border: #3e8ac5;
    color: #ffffff;
}

.view-details .ui.attached.segment > p:first-child, .view-form .ui.attached.segment > p:first-child, .view-checklist .ui.attached.segment > p:first-child {
    margin-bottom: 0;
    font-size: 18px;
}

#group643 {
    margin-top: 20px;
}

form .optional {
    display: none;
}

.delete .icon-remove {
    margin-right: 0;
}

.fabrikForm {
    overflow: visible;
}


.view-form #drawer .attached p, .view-checklist #drawer .attached p, .view-details #drawer .attached p {
    margin: 0 !important;
}

label {
    line-height: normal;
    margin-bottom: 0;
}

#system-message {
    margin: 0;
    padding: 0;
}

.referents-sollicitation-page .fb_el_jos_emundus_files_request___student_id .controls .input-append {
    display: flex !important;
    flex-direction:row;
}

#g-navigation .g-main-nav .g-dropdown > .g-dropdown-column {
    border: none;
}

fieldset {
    border-radius: 0 !important;
}

.view-registration fieldset {
    box-shadow: none !important;
}

.ui.yellow.segment:not(.inverted) {
    border-top: 2px solid #e5283b !important;
}

#group175 .controls {
    margin-top: 10px;
}

#group175 .controls .fabrikElement {
    display: flex;
}

.view-form #form_287_20 .nav, .view-form #form_308_5 .nav, #form_309_5 .nav {
    display: none;
}

.view-form .form-horizontal .controls {
    height: auto;
}


form legend a, form legend a:hover, form legend a:focus {
    background: #fcfcfc;
    padding-left: 2rem;
    padding-top: 1rem;
    border-radius: 0;
    color: #000;
    display: contents;
    font-size: 1.5rem;
    line-height: 1.5;
}

.itemid-2823 .popover, .itemid-2772 .popover {
    width: 100% !important;
    max-width: 800px !important;
}

#form_319_1 .control-label, #form_316 .control-label, #form_319 .control-label {
    margin: 10px 0;
}

.fabrikUploadDelete button {
    margin-bottom: 10px !important;
}

.p-intro {
    margin-bottom: 10px !important;
}

#form_324 .icon-minus, #form_324 .icon-plus {
    margin: 0 !important;
}

#form_324 .addGroup, #form_324 .deleteGroup {
    padding: 0 !important;
}

.fabrikgrid_radio {
    display: flex !important;
}

.groupintro h2 {
    font-weight: 300;
    font-size: 2em;
    border-bottom: 4px solid #ddd;
    display: inline-flex;
}

/* PERSONAL_DETAILS */
.list-striped, .row-striped {
    border: 0;
}

.row-striped .row-fluid {
    width: 100%;
}

.row-striped .row-fluid [class*="span"] {
    display: flex;
    align-items: center;
}

.fb_el_jos_emundus_personal_detail___nationality .fabrikElement {
    display: flex;
}

#form_258_63 .control-group .control-label {
    display: none !important;
}

#form_258_63 .control-group .controls {
    width: 100% !important;
}

.view-form .toggle-editor.btn-toolbar {
    display: none !important;
}

.view-form a.btn.btn-info.toggle-addoption {
    padding: 13px 12px !important; 
}

/* Fabrik application form details */
.view-details .nav.nav-tabs {
    display: none;
}

/* Fabrik application form buttons left-right */
.view-form .fabrikActions.form-actions .span4 {
    float: right;
    text-align: right;
}

.view-form .fabrikActions.form-actions .offset1.span4 {
    float: left;
    margin-left: 0;
}

.view-form .fabrikActions.form-actions .offset1 .pull-right {
    float: left;
}

.customsend-application-file a {
    color: #ffffff !important;
}

.view-form .mce-tinymce .mce-statusbar .mce-path > .mce-path-item {
    display: none !important;
}

.view-form .sauvegarder,
.view-checkout .sauvegarder, #fabrikSubmit_321 {
    margin-left: 20px !important;
}
/* ------ Rapport d'erreur -------- */
#form_293 {
    padding: 20px 0px 0px 0px !important;
}

#form_293 .nav {
    display: none;
}

#form_293 legend {
    margin: 0;
}
/* -------- Modifier votre profil ------- */
form#member-profile {
    padding: 20px !important;
}

#member-profile .controls {
  text-align: center;
}

form#member-profile .controls {
    display: flex;
    justify-content: center;
    flex-direction: row-reverse;
}

form#member-profile .form-horizontal .control-label {
    width: 180px;
}

/*Dossiers Perso Complet*/
.view-details .row-striped > .span12 {
    width: 100%;
}

.view-details .row-striped .span12 > * {
    width: 50% !important;
}

.view-details .row-striped .span12 > * {
    width: 50% !important;
}

.view-details .row-striped .span12 .fabrikLabel, .view-details .row-striped .span12 .fabrikElement {
    width: 100% !important;
}

#info_checklist a.appsent {
    background-size: 30px;
    background-position: left center;
    padding: 0 0 0 40px;
}

/* ---- VALIDATION STARS ----- */
/* --- hiding icon atm because i'm not sure what they want yet... --- */
/*.view-form .icon-star.small {
    display: none;
}

.fabrikLabel[opts*="Validation"]::after, body.view-login label#password-lbl::after, body.view-login label#username-lbl::after, body.view-reset label#jform_email-lbl::after, body.view-reset label#jform_username-lbl::after {
    content: "∗";
    color: red;
    padding-left: 5px;
    top: -5px;
    position: relative;
}

.fabrikLabel[opts*="Validation"] > .icon-star{
    display: none !important;
}*/


.view-registration .icon-star.small,
.view-form.em-formRegistrationCenter .icon-star.small {
    margin-top: 0px;
    padding-top: 0px;
    position: relative;
    padding-left: 2px;
    color: #e5283B;
    font-size: 5px;
    order: 2;
    top: -7px;
}

.icon-.small {
    display: none;
}

/* -- BUDGET FORM --- */
.fb_el_jos_emundus_funding___total_ressource > label::after, .fb_el_jos_emundus_funding___total_depense > label::after {
    content: "";
}

.view-form form[name="form_324"] input::placeholder, .view-form input[placeholder="€"]::placeholder {
    color: red !important;
    opacity: 1;
}

#group712 .groupintro, #group739 .groupintro {
    display: flex;
    flex-direction: row-reverse;
}

#jos_emundus_funding___total_ressource, #jos_emundus_funding___total_depense, .view-form form[name="form_324"] input[readonly="readonly"] {
    border: none;
    box-shadow: none;
    background-color: transparent;
    text-align: end;
}

.view-form form[name="form_324"] .legend {
    font-size: 1.2rem;
}

.view-form form[name="form_324"] #group711 tr td:first-child,
.view-form  form[name="form_324"] #group714 tr td:first-child,
.view-form  form[name="form_324"] #group720 tr td:first-child,
.view-form form[name="form_324"] #group721 tr td:first-child {
    width: 24%;
}

.view-form  form[name="form_324"] #group711 tr td:last-child,
.view-form form[name="form_324"] #group714 tr td:last-child,
.view-form  form[name="form_324"] #group720 tr td:last-child,
.view-form form[name="form_324"] #group721 tr td:last-child {
    width: 5%;
}

.view-form  #group735 select.input.input-medium {
    width: 210px;
}

/* ---- ALLOW * TO BE AT THE END OF TEXT ----- */

/* Inscription */

.view-registration .login.em-formRegistrationCenter .em-heading-registration h1, 
.view-form.em-formRegistrationCenter .em-formRegistrationCenter:not(.componentheading) .em-heading-registration h1 {
    margin-top: 0px !important; 
    font-size: 32px;
    font-style: normal;
    font-weight: 600;
    line-height: 39px;
    letter-spacing: 0;
}

.view-form.view-registration .span12. .login.em-formRegistrationCenter  p:first-of-type, 
.view-form.em-formRegistrationCenter  .span12 .em-formRegistrationCenter:not(.componentheading) p:first-of-type {
    margin-top: 0 !important; 
}

.view-registration .login.em-formRegistrationCenter {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    background: #fff;
    width: 50%;
    margin-left: auto;
    margin-right: auto;
    margin-top: 0;
    margin-bottom: 64px;
    border-radius: 16px;
}

 .view-form.em-formRegistrationCenter:not(.view-registration) .em-formRegistrationCenter:not(.componentheading) {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    background: #fff;
    width: 50%;
    margin-left: auto;
    margin-right: auto;
    margin-top: 32px;
    border-radius: 16px;
}


.view-registration .login.em-formRegistrationCenter .row-fluid {
    background: #fff;
}

.view-form.view-registration #system-message {
     top: 95px;
}

 .view-form.em-formRegistrationCenter form.fabrikForm .fabrikActions.form-actions .span4 .btn-group {
        width: 100% !important; 
}

.view-form.em-formRegistrationCenter form.fabrikForm .fabrikActions.form-actions .row-fluid .span4  {
        width: 100% !important; 
}

.view-form.em-formRegistrationCenter form.fabrikForm .fabrikActions.form-actions .row-fluid .span4 button {
        width: 100% !important;
        height: 48px !important;
        margin-bottom: 0px;
}



  .view-registration form .row-fluid .plg-password span, .view-form.em-formRegistrationCenter form .row-fluid .plg-password span {
    color: #000; 
    margin-top: 5px;
}

  /* ICON  TITRE INSCRIPTION */

  .view-registration .login div.em-heading-registration,  .view-form .em-formRegistrationCenter div.em-heading-registration  {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    flex-direction: row;
    width: 100%;
    padding: 80px 64px 32px 64px;
}

form.fabrikForm label {
  width: 100% !important;
}

button.register {
  height: 100% !important;
}

.view-registration form.fabrikForm , .view-form .em-formRegistrationCenter form.fabrikForm {
    border: none;
    border-radius: 0;
    background: #fff;
    box-shadow: none;
    padding: 0px 64px 16px 64px;
    margin-top: 0px !important;
    border-radius: 0 0 16px 16px;
    width: 100%;
  }

.view-registration fieldset {
    background: #4e5b6F;
  }

.view-registration button {
    display: inline-block;
    font-weight: 600;
    font-size: 12px;
    line-height: 12px;
    letter-spacing: 0.1rem;
    border-radius: 0;
    padding: 8px 12px;
    vertical-align: middle;
    text-shadow: none;
    -webkit-transition: all 0.2s;
    -moz-transition: all 0.2s;
    transition: all 0.2s;
    margin-right: 20px;
  }

.view-registration button:hover {
  color: #ffffff;
  background: none;
}

.view-registration .em-register-warning {
  border-radius: 0;
  padding: 20px;
  margin: 0 0 30px 0;
}


.view-registration table, .view-registration td {
    border: none;
}

.view-reset .btn {
    padding: 8px 12px !important;
}

.view-registration tr {
    display: inline-flex;
    align-items: center;
}

.view-registration tr {
    display: inline-flex;
    align-items: center;
    width: 100%;
}

.view-registration .em-label, .view-registration .em-checkBox-label {
    width: 20%;
}

.view-registration .em-input {
    width: 80%;
}

.view-registration form.fabrikForm {
    background: #fafafa;
    border: #fff;
    margin-top: 40px;
    margin-bottom: 0;
    border-radius: 0;
    color: #ffffff;
}

.view-registration .em-register-warning {
    border: 1px solid #e5283b;
    border-radius: 0;
    padding: 20px;
    margin: 0 0 30px 0;
}

.view-registration .em-register-warning h2 {
    margin-left: 10px;
}

.view-registration .em-register-warning p {
    margin: 20px 0 0 0;
}

.em-register-table .em-label, .em-register-table .em-checkBox-label {
    float: left;
    padding-top: 5px;
}

.em-register-table .em-input input,
.em-register-table .em-input select {
    background-color: #ffffff !important;
}

.em-register-table .em-checkBox-input {
    margin-top: 15px;
    margin-right: 12px;
    float: right;
}

.em-register-table .em-checkBox-label button {
    margin-left: 0 !important;
}

.view-registration fieldset {
    background: #fff;
}

.view-registration legend {
    display: none;
}

.view-registration .box_content {
    margin-top: 4rem;
}

.view-registration #jform_emundus_profile_cgu-lbl button.btn.btn-link:hover,
.view-registration #jform_emundus_profile_cgu-lbl button.btn.btn-link:active,
.view-registration #jform_emundus_profile_cgu-lbl button.btn.btn-link:focus {
    background: none;
}

#jform_emundus_profile_cgu-lbl button.btn.btn-link {
    padding: 0;
    background-color: transparent !important;
}

.view-registration tr.em-checkBox-tr td.em-checkBox-input {
    margin-top: 0;
    padding-top: 4px;
}

.view-login .nav-tabs > li {
    padding-bottom: 12px;
}

.statut-choice-container {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
}
/* --------- Créez votre espace -------------- */
.fabrikActions.form-actions .row-fluid .span4 {
    width: 100% !important;
    margin-bottom: 0 !important;
}

#fabrikSubmit_308,
#fabrikSubmit_305,
#fabrikSubmit_293,
form#member-profile .controls button,
#fabrikSubmit_306 {
    margin-left: 20px !important;
}

.form-actions {
    margin-top: 0;
}
.form-actions .row-fluid{
    display: flex;
    flex-direction: row;
    justify-content: center;
}

#form_308 .row-fluid.nav {
    display: none !important;
}

.fabrikActions.form-actions .row-fluid .span4:first-child {
    margin-bottom: 20px;
}

.pull-right,
.view-registration .offset1.span4 {
    float: none;
}

.form-actions {
    padding: 20px 0 0;
    margin-bottom: 0;
}

#form_102 .nav {
    display: none;
}

.ui.steps:not(.vertical) .step > .icon {
    width: 40px;
    height: 40px;
}

.step.completed i.icon.file.text.outline::before,
.step.completed i.icon.attach::before,
.step.completed i.icon.add.to.cart::before,
.step.completed i.icon.time::before {
    color: #3e8ac5;
    background: none !important;
}

.ui.segments:last-child {
    margin-bottom: 30px;
    margin-top: 20px;
}

.view-registration form label {
    color: #000;
}

.view-registration .fabrikgrid_checkbox {
    width: 100%;
}

#jos_emundus_users___password_check {
    margin-top: 10px;
}

body:not(.g-back-office-emundus-tableau) .fabrikForm div.nav.row-fluid {
    display: none;
}

#group673 .fabrikGroupRepeater .addGroup {
    margin-right: 10px !important;
}

#group673 .control-label, #group674 .control-label, #group675 .control-label {
    padding: 10px 0;
}


#form_323 .fabrikGroupRepeater {
    margin-bottom: 10px !important;
}


.view-form .fabrikGroupRepeater .btn-success {
    margin-right: 10px !important;
}

/* Déclarer un nouveau programme */
.add-new-program-page .btn-success{
    margin-right: 5px;
}

.add-new-program-page .form-actions .row-fluid {
    justify-content: space-between;
}

.wf-editor-container {
    max-width: 100% !important;
}

#jos_emundus_setup_programmes___synthesis_toolbargroup, #jos_emundus_setup_programmes___notes_toolbargroup, #jos_emundus_setup_programmes___tmpl_trombinoscope_toolbargroup, #jos_emundus_setup_programmes___tmpl_badge_toolbargroup{
    display: inline-flex;
}

/* Ajouter une année pour un programme */
.add-program-year-page textarea.fabrikinput {
    width: 100%;
}

.add-program-year-page .btn-success{
    margin-right: 5px;
}

.add-program-year-page .form-actions .row-fluid {
    justify-content: space-between;
}
/* date campaign */
#jos_emundus_setup_campaigns___start_date .input-append:last-of-type,
#jos_emundus_setup_campaigns___end_date .input-append:last-of-type{
  margin-left:40px;
}

#jos_emundus_setup_programmes___programmes > div > div {
  height:auto!important;
}

#jos_emundus_setup_programmes___programmes_ddLabel{
  margin:10px 0;
  width:99%;
}
/* edit period campaign */
.fbDateTime {
    width: auto !important;
    height: auto !important;
    max-width: 490px;
    transform: translate(-50%, -50%);
    left: 50% !important;
}

.fbDateTime .modal-header {
    height: 40px !important;
    min-height: 40px;
    background: #fff;
}
.fbDateTime .close-time{
    float: right !important;
}
.fbDateTime .btn-group a {
    width: auto !important;
    font-size: 16px !important;
    margin: 0 5px 5px 0 !important;
    padding: 0 3px !important;
    background: #ddd;
    border: 1px solid #ddd;
}
.fbDateTime .btn-group {
    width:100% !important;
}
/* tiny mce wysiwyg */
.mce-container-body.mce-abs-layout {
    overflow: visible !important;
}

input:not([type]).mce-textbox {
   width: inherit;
   height:auto!important;
}

/* --- add attachment --- */
.form-horizontal .control-group .control-label {
    text-align: left;
    color: #000;
}

.btn {
	border-radius: 4px !important;
}

.form-horizontal .control-group .btn-group label.btn-default {
    color: white;
    border-radius: 0 !important;
    padding: 6px 8px;
    text-shadow: none !important;
    line-height: normal !important;
    background-image: none !important;
    display: inline-flex !important;
    align-items: center !important;
    text-transform: initial !important;
    font-weight: 400;
    letter-spacing: normal !important;
}
.form-horizontal .control-group .btn-group label.btn-default:hover{
    background: #ababab;
}

.fabrik-boards .form-horizontal .control-group .btn-group label.btn-default:hover{
    background: #ababab;
}

.fabrik-boards .form-horizontal .control-group .btn-group label.btn-default{
    background: #d2d2d2;
}

button.goback-btn, .btn-danger, .pull-right .btn:not(.dropdown-toggle), .below-content a, #member-profile .controls a, .toggle-editor .btn,
.xclsform .panel-body button#delfilter, button#back, .btn.advanced-search-clearall, .emails-setup-page .form-actions button:not(.save),
.documents-type-candidacy-page .fabrikDetails .btn, .fabrikUploadDelete .btn, #em_select_filter #del-filter, .btn-toolbar .btn-group:nth-child(2) .btn {
  background: #4e5b6d;
  border: 1px solid #4e5b6d;
  color: white;
  text-shadow: none;
  text-transform: none;
}

.view-form form .btn.goback-btn.button[name="Goback"] {
    width:auto !important;
}

button.goback-btn:hover, button.goback-btn:active, button.goback-btn:focus,
.btn-danger:hover, .btn-danger:active, .btn-danger:focus,
.pull-right .btn:not(.dropdown-toggle):hover, .pull-right .btn:not(.dropdown-toggle):focus, .pull-right .btn:not(.dropdown-toggle):active,
.below-content a:hover, .below-content a:active, .below-content a:focus,
#member-profile .controls a:hover, #member-profile .controls a:active, #member-profile .controls a:focus,
.toggle-editor .btn:hover, .toggle-editor .btn:active, .toggle-editor .btn:focus,
.xclsform .panel-body button#delfilter:hover, .xclsform .panel-body button#delfilter:active, .xclsform .panel-body button#delfilter:focus,
button#back:hover, button#back:active, button#back:focus,
.btn.advanced-search-clearall:hover, .btn.advanced-search-clearall:active, .btn.advanced-search-clearall:focus,
.emails-setup-page .form-actions button:not(.save):hover, .emails-setup-page .form-actions button:not(.save):active, .emails-setup-page .form-actions button:not(.save):focus,
.documents-type-candidacy-page .fabrikDetails .btn:hover, .documents-type-candidacy-page .fabrikDetails .btn:active, .documents-type-candidacy-page .fabrikDetails .btn:focus,
.fabrikUploadDelete .btn:hover, .fabrikUploadDelete .btn:active, .fabrikUploadDelete .btn:focus,
#em_select_filter #del-filter:hover, #em_select_filter #del-filter:active, #em_select_filter #del-filter:focus,
.btn-toolbar .btn-group:nth-child(2) .btn:hover, .btn-toolbar .btn-group:nth-child(2) .btn:active, .btn-toolbar .btn-group:nth-child(2) .btn:focus {
	  background-color: transparent;
	  color: #4e5b6d;
	  outline: none;
}
button.save-btn, .btn-success, button.save, button.save_continue, button.send, a.btn-attach, #trombi_preview, #trombi_generate,
.indicateurs-page .btn, .fabrik_filter_submit, button.importation, a.btn-warning, a.btn-info, .header-right .btn-danger, button.btn-primary,
.em-generated-docs .em-doc-zip, .xclsform .panel-body button#savefilter, .modal-dialog #chargement a.btn, a#em-doc-zip,
.btn.advanced-search-apply, .toggle-addoption.btn, .candidacy-files-list a#send, .choice-statut-btn {
	  background-color: #4e5b6d;
	  background: #4e5b6d;
	  border: 1px solid #4e5b6d;
	  color:white;
	  text-shadow: none;
	  text-transform: none;
}

button.save-btn:hover, button.save-btn:active, button.save-btn:focus,
.btn-success:hover, .btn-success:focus, .btn-success:active,
button.save:hover, button.save:active, button.save:focus,
button.save_continue:hover, button.save_continue:active, button.save_continue:focus,
button.send:hover, button.send:active, button.send:focus,
a.btn-attach:hover, a.btn-attach:active, a.btn-attach:focus,
#trombi_preview:hover, #trombi_preview:active, #trombi_preview:focus,
#trombi_generate:hover, #trombi_generate:active, #trombi_generate:focus,
.indicateurs-page .btn:hover, .indicateurs-page .btn:active, .indicateurs-page .btn:focus,
.fabrik_filter_submit:hover, .fabrik_filter_submit:active, .fabrik_filter_submit:focus,
button.importation:hover, button.importation:active, button.importation:focus,
a.btn-warning:hover, a.btn-warning:active, a.btn-warning:focus,
a.btn-info:hover, a.btn-info:active, a.btn-info:focus,
.header-right .btn-danger:hover, .header-right .btn-danger:focus, .header-right .btn-danger:active,
button.btn-primary:hover, button.btn-primary:active, button.btn-primary:focus,
.em-generated-docs .em-doc-zip:hover, .em-generated-docs .em-doc-zip:active, .em-generated-docs .em-doc-zip:focus,
.xclsform .panel-body button#savefilter:hover, .xclsform .panel-body button#savefilter:active, .xclsform .panel-body button#savefilter:focus,
.modal-dialog #chargement a.btn:hover, .modal-dialog #chargement a.btn:active, .modal-dialog #chargement a.btn:focus,
a#em-doc-zip:hover, a#em-doc-zip:active, a#em-doc-zip:focus,
.btn.advanced-search-apply:hover, .btn.advanced-search-apply:active, .btn.advanced-search-apply:focus,
.toggle-addoption.btn:hover, .toggle-addoption.btn:active, .toggle-addoption.btn:focus,
.candidacy-files-list a#send:hover, .candidacy-files-list a#send:active, .candidacy-files-list a#send:focus,
.choice-statut-btn:hover, .choice-statut-btn:active, .choice-statut-btn:focus {
  background-color: transparent;
  color: #4e5b6d;
  outline: none;
}
.span4 > .btn-group > button.save-btn, .span4 > .btn-group > button.save {
    background: #4e5b6d;
    background-color: #4e5b6d;
    border: 1px solid #4e5b6d;
    border-radius: 4px !important;
    padding: 10px 12px !important;
   font-family: 'Inter', sans-serif;
    font-weight: 500;
}
.span4 > .btn-group > button.save-btn:hover, .span4 > .btn-group > button.save:hover {
    background-color: transparent;
    color: #4e5b6d;
    outline: none;
}
.span4.offset1 > .btn-group > button.button {
    background: #4e5b6d;
    background-color: #4e5b6d;
    border: 1px solid #4e5b6d;
    border-radius: 4px;
    padding: 8px 12px !important;
}
.span4.offset1 > .btn-group > button.button:hover{
    background-color: transparent;
    color: #4e5b6d;
    outline: none;
}
footer#g-footer {
	padding: 20px;
}
footer#g-footer {
	background: transparent;
	border-top: 1px solid #bbb;
}

/*Position du bouton aligné avec le titre*/


.header-right a.btn.btn-danger.connexion, .header-right a.btn.btn-danger.inscription  {
    line-height: inherit;
    letter-spacing: inherit;
    font-weight: 300;
}


ol {
   width: 59%;
    text-align: justify;
    margin-left: 0px !important;
}

.view-registration .login.em-formRegistrationCenter ol,
.view-form.em-formRegistrationCenter .em-formRegistrationCenter:not(.componentheading) ol {
    padding: 0px 64px 0px 83px !important;
    text-align: justify;
    margin-left: 0px !important;
    margin-bottom: 0px !important;
    width: 100%;
    margin-top: 0px;
}

ol li {
   margin-bottom: 10px;
}

.view-registration form .button.btn.btn-primary, .view-form .em-formRegistrationCenter #login-form #form-login-submit button {
    height: 50px !important;
    text-transform: initial;
    width:100%;
    padding: 8px 12px; 
}

.view-registration form.fabrikForm fieldset.fabrikGroup.form-horizontal .row-fluid .controls input:not([type="radio"]) {
   border-radius : 4px; 
}

.view-registration form.fabrikForm fieldset.fabrikGroup.form-horizontal .row-fluid .controls input[type="radio"] {
   margin-top: 5px;
      border-radius : 20px; 
}

.view-registration form.fabrikForm fieldset.fabrikGroup.form-horizontal .row-fluid:nth-child(4) .controls label.radio {
  padding-left: 0px;
}

.view-registration form .fabrikActions.form-actions {
    padding: 0 !important;
}
.view-registration form .span4{
    width:100% !important;
}
.view-registration form .btn-group{
    width:100%;
}
.view-registration form button.register {
    width:100% !important;
    font-weight: 300; 
}

.view-registration form .row-fluid .controls input {
    border-radius : 0px;
}

body.em-formRegistrationCenter  {
    overflow-x: hidden;
}

.view-form .fabrikElement div a.chzn-single span {
   display: inline; 
}

/* IMPORT CSV */ 

.import-csv-page h1, .import-csv-page legend { 
    color: #000 !important; 
}

/* RAPPRT ERREUR */ 

.error-report-page .page-header h1, .error-report-page form fieldset legend { 
    color: #000; 
}

.error-report-page  button.submit {
    padding: 5px 30px !important;
    font-weight: 500 !important;
    border-radius: 25px !important;
    border: 2px solid #16afe1; 
    background: #16afe1; 
    color: #fff; 
}

.error-report-page  button.submit:hover, 
.error-report-page  button.submit:focus, 
.error-report-page  button.submit:active, 
 {
    border-radius: 25px; 
    padding: 5px 30px; 
    border: 2px solid #16afe 1!important; 
    background: transparent; 
    color: #16afe1 !important; 
}

form#form_102 select#jos_emundus_campaign_candidature___campaign_id {
        height: 41px !important; 
        border-radius: 4px; 
}

/* EDITER LE PROFIL DU CANDIDAT - ESPACEMENT SOUS LE TITRE - */ 
.view-profile #member-profile fieldset:nth-child(2) legend {
          margin-bottom: 0px !important;
}

.view-form .fabrikActions.form-actions .row-fluid button, .view-details .fabrikActions.form-actions .row-fluid button {
    font-weight: 300; 
    height: 41px !important; 
}
​
.view-form .sidebar-a a#print, .view-checklist .sidebar-a a#print, .view-details .sidebar-a a#print  {
    height: 41px !important;
}

.view-form p em strong i.icon-star.small.obligatoire  {
    margin-top: -2px;
}



/*** HIDE VALIDATION TITLE ***/
.popover-title {
  display: none !important;
}

/****** Add border style to applicant forms ****/
.view-form  input[type='radio'] {
    margin-top: 0;
    -webkit-appearance: none;
    border-radius: 50%;
    width: 10px;
    height: 10px;
}

/* FORMULAIRE D'INSCRIPTION */

.view-form .em-formRegistrationCenter h1::after {
    display: none !important; 
}

.fb_el_jos_emundus_users___terms_and_conditions > label:after {
  content: '';
}

.view-login .form-horizontal,
.view-registration form .row-fluid,
.form-validate .control-group {
  display: flex;
  flex-direction: column;
}


.view-registration form #jos_emundus_users___civility .row-fluid, .view-form .em-formRegistrationCenter form #jos_emundus_users___civility .row-fluid {
     display: flex;
    justify-content: flex-start;
    flex-direction: row;
}

.view-registration form #jos_emundus_users___civility .row-fluid .fabrikgrid_radio:nth-child(1), .view-form .em-formRegistrationCenter form #jos_emundus_users___civility .row-fluid .fabrikgrid_radio:nth-child(1) { 
    margin-right: 26px;
}

.em-formRegistrationCenter {
  display: flex !important;
  flex-direction: column;
  align-items: center;
}

.view-login,
.view-registration
.form-horizontal .controls {
  width: 100%;
}

.form-horizontal .control-group {
  justify-content: space-around;
}
.form-horizontal .control-group .control-label {
      text-align: left;
}
.form-horizontal .control-group .controls {
    margin: 0;
    width: 100%;
    display: flex;
    align-items: flex-start;
    flex-direction: column;
}

/* Formulaire Fabrik */


.view-details .fabrikForm fieldset > .row-fluid,
.view-checklist .fabrikForm fieldset > .row-fluid, 
.view-details .fabrikForm fieldset .fabrikSubGroupElements > .row-fluid 
.view-checklist .fabrikForm fieldset .fabrikSubGroupElements > .row-fluid {
  padding: 5px 30px;
}

.view-form.view-registration .fabrikForm fieldset > .row-fluid, .view-form.em-formRegistrationCenter .fabrikForm fieldset > .row-fluid {
  padding: 0px;
}

.view-form.em-formRegistrationCenter main#g-main-mainbody {
    padding-right: 0%;
}

.form-actions .row-fluid {
  display: flex;
  flex-direction: row-reverse;
  justify-content: center;
}

.form-actions .row-fluid .span4 {
  text-align: center;
}

.form-actions .row-fluid .span4 .btn-group .register {
  background-color: transparent;
  outline: none;
}

.form-actions .row-fluid .span4 .btn-group .register:hover {
    text-shadow: none;
    color: white; 
}

.form-horizontal .controls {
  margin-left: 0 !important;
}

.view-checklist .form-horizontal .controls .em-deleteFile, .view-checklist  .row-fluid .plg-emundus_fileupload .em-deleteFile {
  background-image: none;
}

.view-checklist .form-horizontal .controls .em-deleteFile, .view-checklist .row-fluid .plg-emundus_fileupload .em-deleteFile:hover {
    color: white !important;
}

.view-form .form-horizontal .control-label {
  width: auto !important;
}

.form-validate .control-label label {
  color: #565656 !important;
}

.view-login .form-horizontal .control-label i.icon-star,
.popover i.icon-star {
  display: none;
}

.fb_el_jos_emundus_users___terms_and_conditions .fabrikLabel::after {
  content: "";
}

.view-registration form.row-fluid .span3 {
  margin-right: 30px;
}

.view-registration form .fabrikgrid_radio {
  margin-top: 10px;
}

.view-registration form .fabrikgrid_checkbox {
  width: 106%;
}

.view-registration form .row-fluid .control-group {
  margin-bottom: 0 !important;
}

.view-registration form .row-fluid .control-group, .form-validate .control-group {
  display: flex;
  flex-direction: column;
}

.view-registration form .row-fluid .control-group .controls, .form-validate .control-group .controls {
  margin: 0 !important;
  width: 100%;
}

.em-terms::after,
.popover div li::after {
  content: "∗";
  color: red;
  padding-left: 5px;
  top: -5px;
  position: relative;
}

.fabrikForm .row-fluid .fabrikElement .fabrik_characters_left {
  margin-bottom: 20px;
}

.fabrikgrid_checkbox span {
  padding-left: 5px;
}

/* Registration button */
form#member-registration div a {
  color: #ffffff;
  margin-left: 20px;
  font-weight: 600;
  font-size: 12px;
  line-height: 12px;
  letter-spacing: 0.1rem;
  border-radius: 0;
  padding: 8px 12px;
  background-color: #7ab956;
}

form#member-registration div a:hover {
    background-color: #6ea74e;
}

form.fabrikForm .row-fluid .control-group.plg-textarea label.fabrikTip {
  display: flex !important;
  align-items: flex-start !important;
}


.form-actions .row-fluid {
    text-align: center;
    display: flex;
    flex-direction: row-reverse;
    justify-content: space-between;
    padding: 0 32px;
}

.form-actions .row-fluid .span4 {
    margin: 0 !important;
}

/* marge au dessus input mdp formulaire inscription */
#jos_emundus_users___password {
  margin-bottom: 10px;
}

.groupintro h2 {
  font-weight: 300;
  font-size: 2em;
  border-bottom: 4px solid #ddd;
  display: inline-flex;
}

.mceToolbar button,
.timeButton,
.calendarbutton,
.btn-mini,
.fbdateTime-hour {
  border-radius: initial !important;
  color: initial;
  text-shadow: initial !important;
  line-height: initial !important;
  background-image: initial !important;
  width: auto !important;
}

a.btn-attach:hover,
a.btn-attach:focus,
a.btn-attach:active {
  text-decoration: none;
}

.choice-statut-btn {
  text-transform: uppercase !important;
  display: inline-flex !important;
  justify-content: center;
}

.nav-tabs > li > a,
.nav-pills > li > a {
  margin-right: 0;
}

.em-register-table .em-input input,
.em-register-table .em-input select {
    background-color: #ffffff !important;
}

.btn-radio label:hover {
  cursor: pointer;
}


/*** CONNEXION - CANDIDATER ***/

.view-form.em-formRegistrationCenter .moduletable.em-formRegistrationCenter h1.g-title {
    margin-bottom: 0px; 
}


.view-form.em-formRegistrationCenter .moduletable.em-formRegistrationCenter {
    background: #fff;
    width: 50%;
    margin-left: auto !important;
    margin-right: auto !important;
    padding-bottom: 80px !important;
    margin-top: 137px !important;

}

.view-form.em-formRegistrationCenter .moduletable.em-formRegistrationCenter form#login-form {
    border: none;
    margin-bottom: 0 !important;
    border-radius: 0;
    background: #fff;
    border-radius: 0;
    padding: 32px 64px 0 64px;
    box-shadow: none;
    width: 100%;
    margin-left: auto;
    margin-right: auto;
}

.view-form.em-formRegistrationCenter .moduletable.em-formRegistrationCenter form#login-form  .controls{
    display: flex;
    justify-content: center;
    flex-direction: column;
    margin-bottom: 16px;
}

.view-form.em-formRegistrationCenter .moduletable.em-formRegistrationCenter form#login-form  .controls input {
    border-radius: 4px;
    width: 100%;
    height: 48px;
    border-radius: 4px;
    padding: 0 12px 0 12px;
    border: 1px solid #E3E3E3;
    box-shadow: none;
    margin-top: 12px;
}

.view-form.em-formRegistrationCenter .moduletable.em-formRegistrationCenter form#login-form .controls label {
    font-family: 'Inter', sans-serif;
    font-size: 16px;
    font-style: normal;
    font-weight: 500;
    line-height: 19px;
    letter-spacing: 0.0015em;
    text-align: left;
    margin-bottom: 0px;
    color: #2B2B2B;
}


/*** MEDIA QUERY SECTION ***/

@media screen and (min-width: 960px) and (max-width: 1239px) {
  .view-form .fabrikActions.form-actions .span4 {
    width: 27%;
  }
}

@media screen and (min-width:480px) and (max-width: 959px) {

  .view-form {
    .sauvegarder {
      margin-left: 0 !important;
  }
  
  .view-registration form #jos_emundus_users___civility .row-fluid, .view-form .em-formRegistrationCenter form #jos_emundus_users___civility .row-fluid {
    justify-content: space-around;
  }
  
}

@media all and (max-width: 1239px) {
   .view-registration form.fabrikForm {
        width:60%;
    }
    
    ol {
        width: 60%;
        text-align: justify;
        margin-left: 0px !important;
    }
    
 .add-program-year-page #g-container-main, .add-campaign-page #g-main-mainbody, .add-new-program-page #g-main-mainbody, .indicateurs-page #g-main-mainbody, .import-csv-page #g-main-mainbody, .boxed-width #g-container-main {
        padding: 0 20px;
        width: 100%;
  }
  
   .itemid-2823 .popover, .itemid-2772 .popover {
    width: 100% !important;
    max-width: 280px !important;
  }
  
    .view-registration .em-label,  .view-registration .em-checkBox-label {
      width: 100%;
    }
    
    .view-registration tr {
      flex-direction: column;
    }

 .view-registration .em-input {
      width: 100%;
      margin: 10px 0;
    } 
}

@media all and (max-width: 479px) {

  .view-form.em-formRegistrationCenter .moduletable.em-formRegistrationCenter {
    padding: 20px;
   }
    
    .view-form .btn.send_the_request_for_individual_assessment {
       margin-left: 0px !important; 
    }
    
    .view-form .fabrikActions.form-actions .row-fluid {
       align-items: end;
    }
    
    .view-registration .login div.em-heading-registration,
     .view-form .em-formRegistrationCenter div.em-heading-registration {
         padding: 80px 24px 32px 24px;
         width: 100%;
    }
        
}

@media all and (min-width 480px) and (max-width: 767px) {

    .fabrikActions.form-actions .row-fluid .span4 {
        width: 98% !important;
    }
        
    .view-form .btn.btn-primary.save-btn.sauvegarder.save_continue {
        margin-left: 0px !important;
        margin-bottom: 10px; 
    }

    .add-new-program-page .save, .add-program-year-page .save {
        margin-bottom: 10px;
    }
    
    #jform_emundus_profile_cgu-lbl button.btn.btn-link {
        padding: 0 0 0 5px;
        margin-bottom: 0;
    }
    
    #fabrikSubmit_102, #fabrikSubmit_307, #fabrikSubmit_321 {
        margin-left: 0px !important;
        margin-bottom: 10px !important;
    }
    
    .em-register-table .em-input input, .em-register-table .em-input select {
      width: -webkit-fill-available;
    }
     
    .customsend-application-file a {
        width: 100%;
    }

    .view-registration tr.em-checkBox-tr td.em-checkBox-input {
          width: auto !important;
    }
    
    .view-registration .box_content {
          margin-top: 0;
    }

     .statut-choice-container {
        flex-direction: column;
      }
    
      .choice-statut-btn {
        width: 100%;
      }
    
    #group643 .table-striped th, #group643 .table-striped td {
        font-size: 0.7em !important;
    }
    
    #jos_emundus_projet___project_discipline_other .row-fluid > .span3 {
        width: 50%;
        float: left;
    }
      
    .view-form  main#g-main-mainbody, .view-details  main#g-main-mainbody, .view-checklist  main#g-main-mainbody {
          padding-right: 0;
    }

    .view-form .fabrikGroup .row-fluid, .view-details .fabrikGroup .row-fluid, .view-checklist .fabrikGroup .row-fluid{
        padding-right: 0;
        padding-left: 0;
    }
    
    .view-form .fabrikGroup legend, .view-checklist .fabrikGroup legend,  .view-details .fabrikGroup legend {
        padding-left: 0;
    }


    .view-form .fabrikActions.form-actions .row-fluid .offset1.span4,  .view-checklist .fabrikActions.form-actions .row-fluid .offset1.span4,  .view-details .fabrikActions.form-actions .row-fluid .offset1.span4 {
          float: left;
    }
    
    #fabrikSubmit_308 {
        margin-bottom: 20px !important;
        margin-left: 0 !important;
    }


    .form-horizontal .controls {
          width: auto;
          display: flex !important;
          justify-content: center !important;
    }
         
    .form-horizontal .fabrikElement {
            width: 100% !important;
    }

    .form-horizontal .control-label {
          padding-right: 10px !important;
          min-width: 100% !important;
    }

    .form-actions .row-fluid {
          flex-direction: column !important;
          align-items: center;
    }

    .form-actions .span4:first-child {
            margin-bottom: 10px !important;
    }

    .view-registration form {
          padding-left: 1rem;
          padding-right: 1rem;
    }
    
    .view-form.em-formRegistrationCenter form {
          width:100%;
          padding: 20px;
    }
    
    .view-form.em-formRegistrationCenter .fabrikForm fieldset > .row-fluid {
          padding: 0px;
    }

    .view-registration .em-register-table {
          border: none;
          text-align: left;
          width: 100% !important;
          display: inline-table;
          margin-bottom: 2rem;
    }
    
    .view-registration .em-register-table td {
          border: none;
          text-align: left;
          width: 100% !important;
          display: inline-table;
    }
        
    /* APRES MAJ */
    .view-form .fabrikForm fieldset > .row-fluid, .view-details .fabrikForm fieldset > .row-fluid,  .view-checklist .fabrikForm fieldset > .row-fluid {
        padding: 0px !important;
    }

    .plg-birthday .fabrikSubElementContainer {
        display: flex;
        flex-direction: column;
    }

     .view-form .fabrikForm fieldset > .row-fluid,
      .view-checklist .fabrikForm fieldset > .row-fluid,
      .view-details .fabrikForm fieldset > .row-fluid,
     .view-form .fabrikForm fieldset .fabrikSubGroupElements > .row-fluid, 
      .view-checklist .fabrikForm fieldset .fabrikSubGroupElements > .row-fluid, 
      .view-details .fabrikForm fieldset .fabrikSubGroupElements > .row-fluid{
        padding: 0px !important;
    }
}

@media all and (min-width: 768px) and (max-width: 1023px) {
    .view-registration form .control-group.fabrikElementContainer.plg-radiobutton.fb_el_jos_emundus_users___civility.fabrikDataEmpty.span12 div.fabrikgrid_radio.span2 {
        width:35%;
    }
    
    /* INSCRIPTION */ 
    .view-form .em-formRegistrationCenter {
        width: 65% !important;   
    }
    
    .view-registration form.fabrikForm  {
        width: 100% !important;
    }
    
     .view-registration .em-formRegistrationCenter .em-heading-registration {
        width: 100% !important;   
    }
    
    .view-registration ol {
        width: 75%;   
    }
    
}

@media all and (max-width: 959px) {

    .view-form form.fabrikForm label.radio  { 
        display: inline-block !important; 
    }
    .form-horizontal .control-group {
          flex-direction: column;
    }
    
    .form-horizontal .controls {
          width: 100%;
    }
    
    .form-horizontal .control-label {
          width: 100%;
          margin-bottom: 5px;
    }
      
 .add-program-year-page .control-label {
    text-align: left !important;
    margin-bottom: 10px;
  }
}

@media screen and (min-width: 768px) and (max-width: 959px) {
  .view-form .fabrikActions.form-actions .span4 {
    width: 34%;
  }

  .error-report-page .fabrikForm {
    padding: 1rem 9rem 1rem 4rem;
  }
  
 .view-registration form {
    padding-right: 8rem;
  }
  
      .view-form #g-sidebar .moduletable  {
        padding: 0px !important;
    }
}

/* END - Your CSS styling ends here */

EOT;
