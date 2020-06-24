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
.calendarbutton, .timeButton {
    height: 41px;
}

.applicant-form main#g-main-mainbody {
    padding-right: 5%;
}

.applicant-form .fabrikForm .fabrikActions.form-actions {
    padding: 0;
}

.applicant-form .fabrikGroup {
    background: #fcfcfc;
    margin-bottom: 20px;
    border-radius: 0;
    padding: 20px;
}

.applicant-form .fabrikSubGroup > div[data-role="group-repeat-intro"] {
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

.applicant-form .ui.attached.segment {
    background: #3e8ac5;
    border: #3e8ac5;
    color: #ffffff;
}

.applicant-form .ui.attached.segment > p:first-child {
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
    margin-top: 0 !important;
    overflow: visible;
}

.icon-plus {
    margin-right: 0 !important;
}

.applicant-form #drawer .attached p {
    margin: 0 !important;
}

.applicant-form textarea {
    margin: 10px 0 !important;
}

label {
    line-height: normal;
    margin-bottom: 0;
}

#system-message {
    margin: 0;
    padding: 0;
}

label.fabrikLabel.fabrikTip, span.fabrikTip {
    display: inline-flex !important;
    align-items: center;
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

.applicant-form fieldset, .view-checklist fieldset, body:not(.g-back-office-emundus-tableau) .ui.attached.warning.message, body:not(.g-back-office-emundus-tableau) .ui.warning.message {
    box-shadow: 2px 2px 10px 0px #c0c0c0;
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

.fabrikGroup .fabrikElementReadOnly {
    margin-top: 0;
    display: flex;
    flex-direction: column;
    padding-top: 5px;
}

.fabrikGroup .fabrikElementReadOnly:first-child {
    padding-top: 0;
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

.applicant-form .toggle-editor.btn-toolbar {
    display: none !important;
}

/* Fabrik application form details */
.applicant-form .nav.nav-tabs {
    display: none;
}

/* Fabrik application form buttons left-right */
.applicant-form .fabrikActions.form-actions .span4 {
    float: right;
    text-align: right;
}

.applicant-form .fabrikActions.form-actions .offset1.span4 {
    float: left;
    margin-left: 0;
}

.applicant-form .fabrikActions.form-actions .offset1 .pull-right {
    float: left;
}

.customsend-application-file a {
    color: #ffffff !important;
}
/* Fabrik application WYSIWYG height */
.applicant-form iframe {
    height: 200px !important;
}

.applicant-form .mce-tinymce .mce-statusbar .mce-path > .mce-path-item {
    display: none !important;
}

.applicant-form .sauvegarder,
.view-checkout .sauvegarder, #fabrikSubmit_321 {
    margin-left: 20px !important;
}
/* ------ Rapport d'erreur -------- */
#form_293 {
    padding: 20px !important;
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

form#member-profile .controls {
    display: flex;
    justify-content: center;
    flex-direction: row-reverse;
}

form#member-profile .form-horizontal .control-label {
    width: 180px;
}

/*Dossiers Perso Complet*/
.applicant-form .row-striped > .span12 {
    width: 100%;
}

.applicant-form .row-striped .span12 > * {
    width: 50% !important;
}

.applicant-form .row-striped .span12 > * {
    width: 50% !important;
}

.applicant-form .row-striped .span12 .fabrikLabel, .applicant-form .row-striped .span12 .fabrikElement {
    width: 100% !important;
}

#info_checklist a.appsent {
    background-size: 30px;
    background-position: left center;
    padding: 0 0 0 40px;
}

/* ---- VALIDATION STARS ----- */
/* --- hiding icon atm because i'm not sure what they want yet... --- */
/*.applicant-form .icon-star.small {
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
.fabrikForm .fabrikLabel[opts*="Validation"] {
    flex-direction: row-reverse;
    align-items: center !important;
    justify-content: flex-end;
}
.icon-star.small {
    margin-top: -10px;
    padding-top: 0px;
    position: relative;
    padding-left: 2px;
    color: #c30505;
    font-size: 5px;
}
.icon-.small {
    display: none;
}

/* -- BUDGET FORM --- */
.fb_el_jos_emundus_funding___total_ressource > label::after, .fb_el_jos_emundus_funding___total_depense > label::after {
    content: "";
}

.applicant-form form[name="form_324"] input::placeholder, .applicant-form input[placeholder="€"]::placeholder {
    color: red !important;
    opacity: 1;
}

#group712 .groupintro, #group739 .groupintro {
    display: flex;
    flex-direction: row-reverse;
}

#jos_emundus_funding___total_ressource, #jos_emundus_funding___total_depense, .applicant-form form[name="form_324"] input[readonly="readonly"] {
    border: none;
    box-shadow: none;
    background-color: transparent;
    text-align: end;
}

.applicant-form form[name="form_324"] .legend {
    font-size: 1.2rem;
}

.applicant-form form[name="form_324"] #group711 tr td:first-child,
.applicant-form form[name="form_324"] #group714 tr td:first-child,
.applicant-form form[name="form_324"] #group720 tr td:first-child,
.applicant-form form[name="form_324"] #group721 tr td:first-child {
    width: 24%;
}

.applicant-form form[name="form_324"] #group711 tr td:last-child,
.applicant-form form[name="form_324"] #group714 tr td:last-child,
.applicant-form form[name="form_324"] #group720 tr td:last-child,
.applicant-form form[name="form_324"] #group721 tr td:last-child {
    width: 5%;
}

.applicant-form #group735 select.input.input-medium {
    width: 210px;
}

/* ---- ALLOW * TO BE AT THE END OF TEXT ----- */
.applicant-form form.fabrikForm .row-fluid label.fabrikLabel.control-label.fabrikTip {
    display: inline;
}
/* Inscription */
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

.registration form {
    background: #4e5b6d;
    border: #4e5b6d;
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

.registration fieldset {
    background: #4e5b6F;
}

.registration legend {
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
    width: auto !important;
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

.fabrikGroupRepeater .pull-right {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.fabrikGroupRepeater .pull-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.fabrikGroupRepeater .pull-right .icon-plus {
    margin-right: 5px !important;
}

.form-actions {
    padding: 20px 0 0;
    margin-bottom: 0;
}

.span12 .page-header {
    border: none;
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
    color: #3e8ac5 !important;
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

body:not(.g-back-office-emundus-tableau) .fabrikForm .nav {
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


.applicant-form .fabrikGroupRepeater .btn-success {
    margin-right: 10px !important;
}

.applicant-form .fabrikGroupRepeater .addGroup.btn-success {
    color: #3e8ac5 !important;
    background-color: transparent !important;
    border: 1px solid transparent !important;
    font-size: 1rem;
}

.applicant-form .fabrikGroupRepeater .deleteGroup.btn-danger {
    color: #E03C35 !important;
    background-color: transparent !important;
    border: 1px solid transparent !important;
    font-size: 1rem;
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
    display: flex;
    text-align: left;
    color: #000;
}

.btn {
	border-radius: 0 !important;
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

button.goback-btn, .btn-danger, .pull-right .btn:not(.dropdown-toggle), .below-content a, #member-profile .controls a, .toggle-editor .btn,
.xclsform .panel-body button#delfilter, button#back, .btn.advanced-search-clearall, .emails-setup-page .form-actions button:not(.save),
.documents-type-candidacy-page .fabrikDetails .btn, .fabrikUploadDelete .btn, #em_select_filter #del-filter, .btn-toolbar .btn-group:nth-child(2) .btn {
  background: #4e5b6d;
  border: 1px solid #4e5b6d;
  color: white;
  text-shadow: none;
  text-transform: none;
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
    border-radius: 0 !important;
    padding: 10px 12px !important;
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
    border-radius: 0 !important;
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

.fabrikForm .fabrikSubGroup{
    position: relative;
}
.fabrikForm .fabrikSubGroup .fabrikGroupRepeater {
    position: absolute;
    right: 10px;
}

.radio input[type="radio"], .checkbox input[type="checkbox"] {
    margin-left: 0px !important;
}

.header-right a.btn.btn-danger.connexion, .header-right a.btn.btn-danger.inscription  {
    line-height: inherit;
    letter-spacing: inherit;
    font-weight: 300;
}

h1::after {
    content: '';
    position: absolute;
    margin-top: 45px;
    width: 2.7rem;
    height: 0.2rem;
    background: #de6339;
    left: 0;
}

ol {
    width: 50%;
    text-align: justify;
    margin-left: 0px !important;
}

ol li {
   margin-bottom: 10px;
}

#form_307 .button.btn.btn-primary.save-btn.sauvegarder.button.register {
    height: 34px;
    text-transform: capitalize;
}

#form_307 form.fabrikForm fieldset.fabrikGroup.form-horizontal .row-fluid .controls input {
   border-radius : 0px; 
}

#form_307 form.fabrikForm fieldset.fabrikGroup.form-horizontal .row-fluid:nth-child(4) .controls label.radio {
  padding-left: 0px;
}

#form_307.fabrikForm .controls select {
    border-radius : 0px; 
    height: 41px !important;
}

#form_307.fabrikForm {
    width:50%;
}
#form_307 .fabrikActions.form-actions{
    padding: 0 !important;
}
#form_307 .span4{
    width:100% !important;
}
#form_307 .btn-group{
    width:100%;
}
#form_307 #fabrikSubmit_307{
    width:100% !important;
    font-weight: 300; 
}

#form_307 .row-fluid .controls input {
    border-radius : 0px;
}

body.em-formRegistrationCenter  {
    overflow-x: hidden;
}


.applicant-form input[type='text'], .applicant-form input[type='tel'], .applicant-form input[type='number'], .applicant-form textarea , .applicant-form input[type='email'], .applicant-form select {
        border-radius: 0px;
}

.applicant-form .fabrikElement select {
    height: 41px !important;
}

/* AJOUT APRES MAJ */
.applicant-form input[type='radio']  { 
    border: 1px solid #e0e0e5 !important;
}

.applicant-form input[type='radio']:checked  { 
    background: #de6339;
}

.applicant-form .fabrikActions.form-actions .row-fluid button {
    font-weight: 300; 
    height: 41px;
}
​
.applicant-form .sidebar-a a#print {
    height: 41px;
}

.applicant-form p em strong i.icon-star.small.obligatoire  {
    margin-top: -2px;
}

.fabrikMainError.alert.alert-error.fabrikError {
    border-color: #b94a48;
    color: #b94a48;
}
.fabrikMainError.alert.alert-error.fabrikError .close{
    color: #b94a48 !important;
}

@media all and (max-width: 1239px) {
    #form_307.fabrikForm {
        width:60%;
    }

    ol {
        width: 60%;
        text-align: justify;
        margin-left: 0px !important;
    }
}

@media all and (max-width: 767px) {
    ol {
        width: 100%;
        text-align: justify;
        margin-left: 0px !important;
    }

    #form_307.fabrikForm {
        width:100%;
    }
    
    /* APRES MAJ */
    .applicant-form .fabrikForm fieldset > .row-fluid {
        padding: 0px !important;
    }

    .plg-birthday .fabrikSubElementContainer {
        display: flex;
        flex-direction: column;
    }

    .applicant-form .fabrikForm fieldset > .row-fluid, .applicant-form .fabrikForm fieldset .fabrikSubGroupElements > .row-fluid {
        padding: 0px !important;
    }

    .fabrikForm .fabrikSubGroup .fabrikGroupRepeater {
        top: 0px!important;
    }
}

@media all and (min-width: 768px) and (max-width: 1023px) {
    #form_307 .control-group.fabrikElementContainer.plg-radiobutton.fb_el_jos_emundus_users___civility.fabrikDataEmpty.span12 div.fabrikgrid_radio.span2 {
        width:35%;
    }
}

/* END - Your CSS styling ends here */

EOT;