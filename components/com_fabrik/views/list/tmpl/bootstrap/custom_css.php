<?php
/**
 * Fabrik List Template: Default Custom CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

/**
* If you need to make small adjustments or additions to the CSS for a Fabrik
* list template, you can create a custom_css.php file, which will be loaded after
* the main template_css.php for the template.
*
* This file will be invoked as a PHP file, so the list ID
* can be used in order to narrow the scope of any style changes.  You do
* this by prepending #listform_$c to any selectors you use.  This will become
* (say) #listform_12, owhich will be the HTML ID of your list on the page.
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
$c = $_REQUEST['c'];
echo <<<EOT
/* BEGIN - Your CSS styling starts here */

/* Add a padding on list program-year-page for example */
.program-year-page #g-main-mainbody, .campaign-page #g-main-mainbody, .users-profile-setup-page #g-main-mainbody, .emails-setup-page #g-main-mainbody,
.emails-declancheur-setup-page #g-main-mainbody, .courriers-setup-page #g-main-mainbody, .documents-type-candidacy-page #g-main-mainbody,
.tags-setup-page #g-main-mainbody, .groups-setup-page #g-main-mainbody, .files-status-page #g-main-mainbody,
.layout-showgrouprights #g-main-mainbody{
    padding: 0 20px;
}
/* Add margin to list footer */
.campaign-page #g-footer, .files-status-page #g-footer, .emails-setup-page #g-footer, .documents-type-candidacy-page #g-footer,
.tags-setup-page #g-footer{
  margin-top:20px!important;
}

.fabrikForm {
	margin-top: 25px !important;
}
/* Tags */
.ui.horizontal.label, .ui.horizontal.labels .label, .em-cell .label-default {
    margin: 4px !important;
    display: inline-flex !important;
}

.label-lightpurple {
    background-color: #DCC6E0 !important;
    text-shadow: none;
}

.label-purple {
    background-color: #947CB0 !important;
    text-shadow: none;
}

.label-darkpurple {
    background-color: #663399 !important;
    text-shadow: none;
}

.label-lightblue {
    background-color: #6bb9F0 !important;
    text-shadow: none;
}

.label-blue {
    background-color: #19B5FE !important;
    text-shadow: none;
}

.label-darkblue {
    background-color: #013243 !important;
    text-shadow: none;
}

.label-lightgreen {
    background-color: #7befb2 !important; 
    text-shadow: none;
}

.label-green {
    background-color: #3FC380 !important;
    text-shadow: none;
}

.label-darkgreen {
    background-color: #1E824C !important;
    text-shadow: none;
}

.label-lightyellow {
    background-color: #FFFD7E !important;
    text-shadow: none;
}

.label-yellow {
    background-color: #FFFD54 !important;
    text-shadow: none;
}

.label-darkyellow {
    background-color: #F7CA18 !important;
    text-shadow: none;
}

.label-lightorange {
    background-color: #FABE58 !important;
    text-shadow: none;
}

.label-orange {
    background-color: #E87E04 !important;
    text-shadow: none;
}

.label-darkorange {
    background-color: #D35400 !important;
    text-shadow: none;
}

.label-lightred {
    background-color: #EC644B !important;
    text-shadow: none;
}

.label-red {
    background-color: #CF000F !important;
    text-shadow: none;
}

.label-darkred {
    background-color: #e5283b !important;
    text-shadow: none;
}

/*Programms*/
.programmes-page #g-container-main{
    padding: 0 20px;
}

.programmes-page .fabrik_view{
    margin: 0 5px!important;
}

.programmes-page .fabrik__rowlink{
    padding: 3px 5px;
}
.g-back-office-emundus-tableau table{
    background-color:#ddd;
}
.g-back-office-emundus-tableau h6 {
    padding: 10px 0 0 0;   
}
.g-back-office-emundus-tableau #g-main-mainbody .platform-content .container-fluid{
    padding:0;
}
.g-back-office-emundus-tableau .header-c .open > .dropdown-menu {
    right: 0!important;
}
/* ---------------- Tableau programme ------------------- */
.fabrik-boards tr.fabrik_groupheading.info td {
    background: #4E5B6D !important;
    color: #ffffff;
    border-top: 2px solid;
    border-bottom: 2px solid;
}

.fabrik-boards tr.fabrik_groupheading.info i {
    color: #ffffff;
}

.fabrik-boards .fabrikDataContainer {
    font-size: 14px;
}

.fabrik-boards .fabrikDataContainer tbody.fabrik_groupdata .btn-group a {
    text-shadow: none;
    color: inherit;
}

.fabrik-boards .fabrikDataContainer tbody.fabrik_groupdata .btn-group a:hover,
.fabrik-boards .fabrikDataContainer tbody.fabrik_groupdata .btn-group a:active,
.fabrik-boards .fabrikDataContainer tbody.fabrik_groupdata .btn-group a:focus,
.fabrik-boards a.btn.fabrik_edit.fabrik__rowlink.btn-default:hover,
.fabrik-boards a.btn.fabrik_view.fabrik__rowlink.btn-default:hover,
.fabrik-boards a.btn.btn-default.delete:hover,
.fabrik-boards a.btn.copy-0.listplugin.btn-default:hover {
    color: #e5283b;
}

.fabrik-boards tr.fabrik___heading a {
    color: #2a363b;
}

.fabrik-boards tr.fabrik___heading a:hover {
    color: #4E5B6D;
}

.fabrik-boards .nav .dropdown-toggle .caret {
    margin-top: 6px;
    border-top-color: #e5283b;
    border-bottom-color: #e5283b;
}

.fabrik-boards table.filtertable.table.table-striped tr.fabrik___heading {
    background: #4E5B6D;
    color: #ffffff;
}

.fabrik-boards table.filtertable.table.table-striped tr.fabrik___heading a {
    color: #ffffff;
}

.fabrik-boards .fabrikForm.form-search .table.table-striped.table-hover tr.fabrik_groupheading.info a {
    color: #ffffff;
    font-weight: 600;
}
/*Liste des programmes par années*/
.program-year-page .row-fluid .span6 {
    width: 100%;
    *width: 100%;
}

.program-year-page a i{
    margin-right:0;
}

.program-year-page table .btn{
    margin: 0 5px;
}
.fabrikDataContainer .listplugin {
    -webkit-appearance: initial;
}

.fabrikDataContainer .listplugin .icon-copy {
    font-weight: bold;
}
/* email */
#com-content-formContent label, .emails-declancheur-setup-page .control-label, .set-up-mail-declencheur .control-label{
  padding:10px 0;
}

.emails-setup-page .fabrikElementContainer .controls{
  display:flex;
  flex-direction:column;
}

.emails-setup-page .fabrikElementContainer .controls > span{
  padding-top:10px;
  width:100%;
}
.table-striped tbody > tr > td{
    vertical-align: middle;
}

/*EMAIL HISTORY*/
#advanced-filter .button{
    line-height:normal;
}

#advanced-filter .addbutton{
    border-radius:5px 5px 0 0!important;
}

#advanced-filter .btn-danger{
    padding: 5px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
}

#advanced-filter .icon-minus {
    margin-right: 0;
}

.email-history-indicators-page > h1:first-child{
    display:none;
}
.email-history-indicators-page .pull-left  a,
 .email-history-indicators-page .fabrikorder,
 .email-history-indicators-page .fabrikorder-desc {
    color: #e03c32;
}

.email-history-indicators-page .pull-left  a:hover,.email-history-indicators-page .fabrikorder:hover,
 .email-history-indicators-page .fabrikorder-desc:hover {
    color: #b83229;
}

.email-history-indicators-page #g-main-mainbody{
    padding:0 20px;
}
.fabrik_filter_submit i{
    display: inline!important;
    margin: 5px !important;
}
.fabrikFilterContainer .fabrik_actions .fabrik_filter_submit{
    background: #29d4ff;
    background-color: #29d4ff;
    border: 1px solid #29d4ff;
    border-radius: 0 !important;
    padding: 8px 12px !important;
    color:white;
}
.fabrikFilterContainer .fabrik_actions .fabrik_filter_submit:hover{
    background-color: transparent !important;
    color: #29d4ff;
    outline: none;
}
.btn-info, .btn-group .fabrik__rowlink, a.delete, a.copy-0 {
    background-color: #aaa;
}
.toggle-addoption, .delete, .listplugin, .fabrik__rowlink, button#showhide, .btn-sm {
    border-radius: 0 !important;
    padding: 5px 10px !important;
    color: initial;
    text-shadow: initial !important;
    line-height: initial !important;
    background-image: initial !important;
    border: none !important;
}
.btn, a.btn-attach, .xclsform .panel-body button, button.close {
    color: #fff !important;
}
.table .fabrik_groupdata center {
    background: #eceff3;
}
.email-container table{
    border: none;
}

.email-container table td{
    border-top: none;
}
.email-container table tbody tr:hover > td{
    background: #eceff3;
}
footer#g-footer {
	padding: 20px !important;
}
footer#g-footer {
	background: transparent;
	border-top: 1px solid #bbb;
}

.list-footer .input-prepend.input-append > span, .list-footer .input-prepend.input-append > select {
    height: 41px;
}
/* END - Your CSS styling ends here */
EOT;
