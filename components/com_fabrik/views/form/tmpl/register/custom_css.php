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
.w-form-formradioinput--inputType-custom {
  border-top-width: 1px;
  border-bottom-width: 1px;
  border-left-width: 1px;
  border-right-width: 1px;
  border-top-color: #ccc;
  border-bottom-color: #ccc;
  border-left-color: #ccc;
  border-right-color: #ccc;
  border-top-style: solid;
  border-bottom-style: solid;
  border-left-style: solid;
  border-right-style: solid;
  width: 12px;
  height: 12px;
  border-bottom-left-radius: 50%;
  border-bottom-right-radius: 50%;
  border-top-left-radius: 50%;
  border-top-right-radius: 50%;
}

.w-form-formradioinput--inputType-custom.w--redirected-focus {
  box-shadow: 0px 0px 3px 1px #3898ec;
}

.w-form-formradioinput--inputType-custom.w--redirected-checked {
  border-top-width: 4px;
  border-bottom-width: 4px;
  border-left-width: 4px;
  border-right-width: 4px;
  border-top-color: #3898ec;
  border-bottom-color: #3898ec;
  border-left-color: #3898ec;
  border-right-color: #3898ec;
}

.w-checkbox {
  display: block;
  margin-bottom: 5px;
  padding-left: 20px;
}

.w-checkbox::before {
  content: ' ';
  display: table;
  -ms-grid-column-span: 1;
  grid-column-end: 2;
  -ms-grid-column: 1;
  grid-column-start: 1;
  -ms-grid-row-span: 1;
  grid-row-end: 2;
  -ms-grid-row: 1;
  grid-row-start: 1;
}

.w-checkbox::after {
  content: ' ';
  display: table;
  -ms-grid-column-span: 1;
  grid-column-end: 2;
  -ms-grid-column: 1;
  grid-column-start: 1;
  -ms-grid-row-span: 1;
  grid-row-end: 2;
  -ms-grid-row: 1;
  grid-row-start: 1;
  clear: both;
}

.w-checkbox-input {
  float: left;
  margin-bottom: 0px;
  margin-left: -20px;
  margin-right: 0px;
  margin-top: 4px;
  line-height: normal;
}

.w-checkbox-input--inputType-custom {
  border-top-width: 1px;
  border-bottom-width: 1px;
  border-left-width: 1px;
  border-right-width: 1px;
  border-top-color: #ccc;
  border-bottom-color: #ccc;
  border-left-color: #ccc;
  border-right-color: #ccc;
  border-top-style: solid;
  border-bottom-style: solid;
  border-left-style: solid;
  border-right-style: solid;
  width: 12px;
  height: 12px;
  border-bottom-left-radius: 2px;
  border-bottom-right-radius: 2px;
  border-top-left-radius: 2px;
  border-top-right-radius: 2px;
}

.fabrikgrid_radio > .fabrikinput:checked {
  background-color: #3898ec;
  border-top-color: #3898ec;
  border-bottom-color: #3898ec;
  border-left-color: #3898ec;
  border-right-color: #3898ec;
  background-image: url('https://d3e54v103j8qbb.cloudfront.net/static/custom-checkbox-checkmark.589d534424.svg');
  background-position: 50% 50%;
  background-size: cover;
  background-repeat: no-repeat;
}

.w-checkbox-input--inputType-custom.w--redirected-focus {
  box-shadow: 0px 0px 3px 1px #3898ec;
}

h1 {
  margin-top: 20px;
  margin-bottom: 10px;
  color: #482683;
  font-size: 45px;
  line-height: 45px;
  font-weight: 700;
}
a:hover{
text-decoration:none;
}
ol{
margin:0;
padding:0;
}
li {
  color: #636363;
  font-size: 18px;
  line-height: 24px;
}

.g-container-main{
	background-attachment: fixed;
	padding-bottom: 60px;
}
.icon-question-sign{
width:0;
height:0;
}
.alert-error{
color:red;
font-weight: 500;
}
.alert-error .close{
display:none;
}
.g-navigation {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: auto;
  height: auto;
  min-height: 20vh;
  min-width: auto;
  margin: 0px;
  padding: 0px;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}

.g-menu-overlay {
  display: none;
  width: 0px;
  height: 0px;
  min-height: auto;
  min-width: auto;
  margin: 0px;
  padding: 0px;
  border: 0px none #000;
  background-color: transparent;
  text-align: left;
  text-transform: none;
}

.g-feature {
  position: relative;
  width: 100%;
  height: 0px;
}

.g-grid {
  width: 100%;
}

.custom {
  width: 150px;
  height: 100px;
  background-position: 50% 50%;
  background-size: contain;
  background-repeat: no-repeat;
}

.span12 {
  width: 450px;
  margin-right: auto;
  margin-left: auto;
}
.plg-radiobutton .row-fluid{
	display: flex;
}
.validate-password {
  height: 48px;
  background-color: #fff;
  color: #482683;
}

.validate-password::-webkit-input-placeholder {
  color: #482683;
}

.validate-password:-ms-input-placeholder {
  color: #482683;
}

.validate-password::-ms-input-placeholder {
  color: #482683;
}

.validate-password::placeholder {
  color: #482683;
}

.g-page-surround {
  display: block;
  width: auto;
  height: auto;
  min-height: auto;
  min-width: auto;
  margin: 0px;
  padding: 0px;
  border: 0px none transparent;
  border-radius: 0px;
  background-color: transparent;
  text-transform: none;
}

.text-span {
  color: #ee7937;
}

.text-span-2 {
  color: #ee7937;
}
.span4{
width:100%;
}
.form-actions{
padding:0;
margin:0;
border:none;
	background: transparent;
}
.nav{
	margin-bottom:0;
}
.btn {
  width: 100%;
  margin-top: 15px;
  margin-bottom: 12px;
  padding: 16px 30px!important;
  background-color: #482683!important;
  -webkit-transition: background-color 200ms ease, -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: background-color 200ms ease, -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  transition: transform 200ms cubic-bezier(.55, .085, .68, .53), background-color 200ms ease;
  transition: transform 200ms cubic-bezier(.55, .085, .68, .53), background-color 200ms ease, -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53);
  background-image:none;
  border:none;
  box-shadow:none;
  border-bottom-right-radius:none;
  border-bottom-left-radius:none;
  border-top-right-radius:none;
  border-top-left-radius:none;
	margin-bottom: 80px;
}

.btn:hover {
  background-color: #2CBFDC;
  -webkit-transform: translate(0px, -3px);
  -ms-transform: translate(0px, -3px);
  transform: translate(0px, -3px);
}
.btn-group{
display:block;
}
.gantry {
  background-color: #f5f5f5;
  font-family: Wigrum, sans-serif;
}

.alter {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  width: 450px;
  margin-right: auto;
  margin-left: auto;
  padding: 15px 15px 5px;
  -webkit-box-pack: center;
  -webkit-justify-content: center;
  -ms-flex-pack: center;
  justify-content: center;
  -webkit-flex-wrap: wrap;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
  background-color: #ee7937;
  color: #fff;
  text-align: center;
  text-transform: uppercase;
}

.fabrikForm {
  padding-top: 5px;
  color: #482683;
}
.fabrikForm fieldset{
	margin: 40px 0;
	padding: 40px 0;
	border-bottom: 1px solid #cccccc;
}
.fabrikGroup > legend{
	font-size: 30px;
	font-weight: 400;
	line-height: 33px;
	margin-bottom: 10px;
}
.controls{
	margin-left:0;
}
label.fabrikLabel.fabrikTip{
	width: 100% ;
	margin-bottom: 5px ;
	text-align: left;
	display: block!important;
}
.form-horizontal .control-group{
	margin-bottom: 0;
}
textarea.fabrikinput{
	max-width: 100%;
	min-width:100%;
	border:1px solid #cccccc;
	margin-bottom: 5px;
	padding:10px;
}
.fabrikElement .fabrik_characters_left.muted{
	margin-bottom: 15px;
}
.fabrikinput {
  height: 48px;
  margin-bottom: 20px;
  background-color: #fff;
  color: #2d1852;
}

.fabrikinput::-webkit-input-placeholder {
  background-color: #fff;
  color: #482683;
}

.fabrikinput:-ms-input-placeholder {
  background-color: #fff;
  color: #482683;
}

.fabrikinput::-ms-input-placeholder {
  background-color: #fff;
  color: #482683;
}

.fabrikinput::placeholder {
  background-color: #fff;
  color: #482683;
}

.input-medium {
  height: 48px;
  margin-bottom: 20px;
  background-color: #fff;
  color: #2d1852;
  width:100%;
}

.input-medium::-webkit-input-placeholder {
  background-color: #fff;
  color: #482683;
}

.input-medium:-ms-input-placeholder {
  background-color: #fff;
  color: #482683;
}

.input-medium::-ms-input-placeholder {
  background-color: #fff;
  color: #482683;
}

.input-medium::placeholder {
  background-color: #fff;
  color: #482683;
}

.fabrikgrid_checkbox.span12 {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  margin-top: 25px;
  margin-bottom: 15px;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}

.fabrikgrid_yes.checkbox {
  margin-top: 6px;
  margin-bottom: 0px;
  padding-left: 0px;
  font-size: 14px;
  line-height: 14px;
  font-weight: 700;
}

#jos_emundus_users___terms_and_conditions_0_input_0 {
  width: 30px;
  height: 30px;
  margin-right: 17px;
  border-style: solid;
  border-color: #b6b6b6;
  border-radius: 50%;
  background-color: #fff;
}

#jos_emundus_users___terms_and_conditions_0_input_0:checked {
  border-color: #482683;
  background-color: #482683;
  background-size: 50%;
}
.fabrikgrid_radio {
	margin-bottom: 15px;
}
.fabrikgrid_radio.span2{
	width: auto;
	margin-bottom: 15px;
}
.fabrikgrid_radio.span2 input{
	margin-bottom: 0;
	margin-left: 0;
	margin-right: 5px;
}
.fabrikgrid_radio .radio{
	display: flex;
	flex-direction: row;
	justify-content: center;
	align-items: center;
	padding-left: 0;
	margin-right: 20px;
	
}
.fabrikgrid_checkbox.span12 .checkbox{
	display: flex;
	flex-direction: row;
	justify-content: center;
	align-items: center;
}
.fabrikgrid_checkbox.span12 input{
	margin-bottom: 0;
	margin-left: 0;
}
.fabrikgrid_radio.span12 {
  display: -webkit-box;
  display: -webkit-flex;
  display: -ms-flexbox;
  display: flex;
  margin-top: 25px;
  margin-bottom: 15px;
  -webkit-box-align: center;
  -webkit-align-items: center;
  -ms-flex-align: center;
  align-items: center;
}
.fabrikForm .fabrikElement select {
    height: 48px!important;
	width: auto;
    background-position: 95% 50%!important;
    -webkit-appearance: none;
    border: 1px solid #cccccc !important;
	border-radius:none!important; 
    background: transparent url(../../../../../../images/custom/vyv/arrow-down.png) no-repeat;
    background-size: 10px;
    padding: 0 30px 0 10px;
	background-color: white;
  }
.fabrikgrid_radio .fabrikinput {
  width: 30px;
  height: 30px;
  border-color: #b6b6b6;
  background-color: #fff;
  margin-bottom: 0;
  margin-right: 15px;
}

.fabrikgrid_radio .fabrikinput:checked, .fabrikgrid_checkbox .fabrikinput:checked {
  border-width: 1px;
  border-color: #482683;
  background-color: #482683;
padding-right: 5px;
margin-right: 5px;
	background-image: url('https://d3e54v103j8qbb.cloudfront.net/static/custom-checkbox-checkmark.589d534424.svg');
	background-size: 50%;
background-repeat: no-repeat;
background-position: 50%;
}
.plg-calc .fabrikinput{
	background: transparent;
}
.plg-emundus_fileupload
/**** VALIDATION STARS ****/
.applicant-form, .em-formRegistrationCenter .form.fabrikForm .row-fluid label.fabrikLabel.control-label.fabrikTip {
    display: inline ;
  }
  .icon-star.small {
    display: none;
  }

.view-reset .star{
    display:none;
  }

.fabrikLabel[opts*="Validation"]::after,
.control-label .required::after,
#jos_emundus_users___terms_and_conditions span::before {
    content: "∗";
    color: red;
    padding-left: 5px;
    top: -5px;
    position: relative;
}
.control-group label.fabrikEmptyLabel{
	display: none!important;
}
.fabrikElement input{
	border-radius: 0;
}
input[type='radio'],input[type='checkbox'] {
    margin-top:0;
    -webkit-appearance: none;
    border: 2px solid var(--em-secondary-color);
    border-radius: 50%;
    width:10px;
    height: 10px;
    
  }
.em-fileAttachment-link {
	padding-top: 0px;
    height: 20px;
    padding-bottom: 30px;
}
.element.style {
    width: 20px!important;
    height: 20px;
    border-radius: 50%;
}
.em-deleteFile {
	width: 15px!important;
    border-radius: 50%!important;
    margin-top: 15px!important;
    margin-bottom: 12px!important;
    padding: 15px 15px!important;
    padding-top: 15px!important;
    padding-right: 15px!important;
    padding-bottom: 15px!important;
    padding-left: 15px!important;
    background-color: #482683!important;
    color: #fff!important;
    text-align: center!important;
    text-decoration: none!important;
    text-transform: uppercase!important;
	background: url('/images/custom/vyv/5e02227e8d72cc96258957b8_Plus.svg');
	background-size: 70%;
	background-repeat: no-repeat;
	background-position: 50%;
 }
.em-wrappermenufooter{
	display: flex;
	flex-direction: row;
	justify-content: center;
}
#jos_emundus_declaration___declare_0_input_0{
	width: 30px;
    height: 26px;
    padding: 0px!important;
    margin-right: 10px;
}
@media (max-width: 991px) {
  .g-navigation {
    min-height: auto;
    padding-top: 44px;
    padding-bottom: 44px;
  }
  .g-feature {
    height: auto;
  }
  .g-container-main {
    min-height: auto;
    padding-top: 80px;
    padding-bottom: 80px;
  }
  .g-footer {
    height: auto;
    padding-top: 35px;
    padding-bottom: 35px;
  }
  .em-menufooter {
    margin-right: 22px;
    font-size: 14px;
    line-height: 26px;
  }
}

@media (max-width: 767px) {
	.g-container-main{
		background-size: 60% auto;
	}
	.span12{
		float:none;
		width:90%;
	}
  .g-navigation {
    padding-bottom: 15px;
  }
  ._w-col {
    padding-top: 20px;
  }
  .em-wrappermenufooter {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -ms-flex-pack: center;
    justify-content: center;
  }
  .em-menufooter {
    margin-right: 9px;
    margin-left: 9px;
  }
  .em-rowfooter {
    -webkit-box-orient: vertical;
    -webkit-box-direction: reverse;
    -webkit-flex-direction: column-reverse;
    -ms-flex-direction: column-reverse;
    flex-direction: column-reverse;
  }
}

@media (max-width: 479px) {
  .g-container-main {
    display: block;
    padding-top: 56px;
    padding-bottom: 80px;
  }
  .g-grid {
    width: 100%;
  }
  .span12 {
    width: 90%;
  }
  .em-wrappermenufooter {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }
  .alter {
    width: 90%;
  }
  .em-rowfooter {
    width: 100%;
  }
}
/* popover */

.fade {
  opacity: 0;
  -webkit-transition: opacity 0.15s linear;
     -moz-transition: opacity 0.15s linear;
       -o-transition: opacity 0.15s linear;
          transition: opacity 0.15s linear;
}

.fade.in {
  opacity: 1;
}

.collapse {
  position: relative;
  height: 0;
  overflow: hidden;
  -webkit-transition: height 0.35s ease;
     -moz-transition: height 0.35s ease;
       -o-transition: height 0.35s ease;
          transition: height 0.35s ease;
}

.collapse.in {
  height: auto;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 1040;
  background-color: #000000;
}

.modal-backdrop.fade {
  opacity: 0;
}

.modal-backdrop,
.modal-backdrop.fade.in {
  opacity: 0.8;
  filter: alpha(opacity=80);
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

.modal.fade {
  top: -25%;
  -webkit-transition: opacity 0.3s linear, top 0.3s ease-out;
     -moz-transition: opacity 0.3s linear, top 0.3s ease-out;
       -o-transition: opacity 0.3s linear, top 0.3s ease-out;
          transition: opacity 0.3s linear, top 0.3s ease-out;
}

.modal.fade.in {
  top: 10%;
}

.modal-header {
  padding: 9px 15px;
  border-bottom: 1px solid #eee;
}

.modal-header .close {
  margin-top: 2px;
}

.modal-header h3 {
  margin: 0;
  line-height: 30px;
}

.modal-body {
  position: relative;
  max-height: 400px;
  padding: 15px;
  overflow-y: auto;
}

.modal-form {
  margin-bottom: 0;
}

.modal-footer {
  padding: 14px 15px 15px;
  margin-bottom: 0;
  text-align: right;
  background-color: #f5f5f5;
  border-top: 1px solid #ddd;
  -webkit-border-radius: 0 0 6px 6px;
     -moz-border-radius: 0 0 6px 6px;
          border-radius: 0 0 6px 6px;
  *zoom: 1;
  -webkit-box-shadow: inset 0 1px 0 #ffffff;
     -moz-box-shadow: inset 0 1px 0 #ffffff;
          box-shadow: inset 0 1px 0 #ffffff;
}

.modal-footer:before,
.modal-footer:after {
  display: table;
  line-height: 0;
  content: "";
}

.modal-footer:after {
  clear: both;
}

.modal-footer .btn + .btn {
  margin-bottom: 0;
  margin-left: 5px;
}

.modal-footer .btn-group .btn + .btn {
  margin-left: -1px;
}

.modal-footer .btn-block + .btn-block {
  margin-left: 0;
}

.tooltip {
  position: absolute;
  z-index: 1030;
  display: block;
  font-size: 11px;
  line-height: 1.4;
  opacity: 0;
  filter: alpha(opacity=0);
  visibility: visible;
}

.tooltip.in {
  opacity: 0.8;
  filter: alpha(opacity=80);
}

.tooltip.top {
  padding: 5px 0;
  margin-top: -3px;
}

.tooltip.right {
  padding: 0 5px;
  margin-left: 3px;
}

.tooltip.bottom {
  padding: 5px 0;
  margin-top: 3px;
}

.tooltip.left {
  padding: 0 5px;
  margin-left: -3px;
}

.tooltip-inner {
  max-width: 200px;
  padding: 8px;
  color: #ffffff;
  text-align: center;
  text-decoration: none;
  background-color: #000000;
  -webkit-border-radius: 4px;
     -moz-border-radius: 4px;
          border-radius: 4px;
}

.tooltip-arrow {
  position: absolute;
  width: 0;
  height: 0;
  border-color: transparent;
  border-style: solid;
}

.tooltip.top .tooltip-arrow {
  bottom: 0;
  left: 50%;
  margin-left: -5px;
  border-top-color: #000000;
  border-width: 5px 5px 0;
}

.tooltip.right .tooltip-arrow {
  top: 50%;
  left: 0;
  margin-top: -5px;
  border-right-color: #000000;
  border-width: 5px 5px 5px 0;
}

.tooltip.left .tooltip-arrow {
  top: 50%;
  right: 0;
  margin-top: -5px;
  border-left-color: #000000;
  border-width: 5px 0 5px 5px;
}

.tooltip.bottom .tooltip-arrow {
  top: 0;
  left: 50%;
  margin-left: -5px;
  border-bottom-color: #000000;
  border-width: 0 5px 5px;
}

.popover {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1010;
  display: none;
  max-width: 276px;
  padding: 1px;
  text-align: left;
  white-space: normal;
  background-color: #ffffff;
  border: 1px solid #ccc;
  border: 1px solid rgba(0, 0, 0, 0.2);
  -webkit-border-radius: 6px;
     -moz-border-radius: 6px;
          border-radius: 6px;
  -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
     -moz-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
          box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
  -webkit-background-clip: padding-box;
     -moz-background-clip: padding;
          background-clip: padding-box;
}

.popover.top {
  margin-top: -10px;
}

.popover.right {
  margin-left: 10px;
}

.popover.bottom {
  margin-top: 10px;
}

.popover.left {
  margin-left: -10px;
}

.popover-title {
  padding: 8px 14px;
  margin: 0;
  font-size: 14px;
  font-weight: normal;
  line-height: 18px;
  background-color: #f7f7f7;
  border-bottom: 1px solid #ebebeb;
  -webkit-border-radius: 5px 5px 0 0;
     -moz-border-radius: 5px 5px 0 0;
          border-radius: 5px 5px 0 0;
}

.popover-title:empty {
  display: none;
}

.popover-content {
  padding: 9px 14px;
}

.popover .arrow,
.popover .arrow:after {
  position: absolute;
  display: block;
  width: 0;
  height: 0;
  border-color: transparent;
  border-style: solid;
}

.popover .arrow {
  border-width: 11px;
}

.popover .arrow:after {
  border-width: 10px;
  content: "";
}

.popover.top .arrow {
  bottom: -11px;
  left: 50%;
  margin-left: -11px;
  border-top-color: #999;
  border-top-color: rgba(0, 0, 0, 0.25);
  border-bottom-width: 0;
}

.popover.top .arrow:after {
  bottom: 1px;
  margin-left: -10px;
  border-top-color: #ffffff;
  border-bottom-width: 0;
}

.popover.right .arrow {
  top: 50%;
  left: -11px;
  margin-top: -11px;
  border-right-color: #999;
  border-right-color: rgba(0, 0, 0, 0.25);
  border-left-width: 0;
}

.popover.right .arrow:after {
  bottom: -10px;
  left: 1px;
  border-right-color: #ffffff;
  border-left-width: 0;
}

.popover.bottom .arrow {
  top: -11px;
  left: 50%;
  margin-left: -11px;
  border-bottom-color: #999;
  border-bottom-color: rgba(0, 0, 0, 0.25);
  border-top-width: 0;
}

.popover.bottom .arrow:after {
  top: 1px;
  margin-left: -10px;
  border-bottom-color: #ffffff;
  border-top-width: 0;
}

.popover.left .arrow {
  top: 50%;
  right: -11px;
  margin-top: -11px;
  border-left-color: #999;
  border-left-color: rgba(0, 0, 0, 0.25);
  border-right-width: 0;
}

.popover.left .arrow:after {
  right: 1px;
  bottom: -10px;
  border-left-color: #ffffff;
  border-right-width: 0;
}



/* END - Your CSS styling ends here */

EOT;
