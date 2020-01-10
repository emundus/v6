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
  border-top-width: 1px!important;
  border-bottom-width: 1px!important;
  border-left-width: 1px!important;
  border-right-width: 1px!important;
  border-top-color: #ccc!important;
  border-bottom-color: #ccc!important;
  border-left-color: #ccc!important;
  border-right-color: #ccc!important;
  border-top-style: solid!important;
  border-bottom-style: solid!important;
  border-left-style: solid!important;
  border-right-style: solid!important;
  width: 12px!important;
  height: 12px!important;
  border-bottom-left-radius: 50%!important;
  border-bottom-right-radius: 50%!important;
  border-top-left-radius: 50%!important;
  border-top-right-radius: 50%!important;
}

.w-form-formradioinput--inputType-custom.w--redirected-focus {
  box-shadow: 0px 0px 3px 1px #3898ec!important;
}

.w-form-formradioinput--inputType-custom.w--redirected-checked {
  border-top-width: 4px!important;
  border-bottom-width: 4px!important;
  border-left-width: 4px!important;
  border-right-width: 4px!important;
  border-top-color: #3898ec!important;
  border-bottom-color: #3898ec!important;
  border-left-color: #3898ec!important;
  border-right-color: #3898ec!important;
}

.w-checkbox {
  display: block!important;
  margin-bottom: 5px!important;
  padding-left: 20px!important;
}

.w-checkbox::before {
  content: ' '!important;
  display: table!important;
  -ms-grid-column-span: 1!important;
  grid-column-end: 2!important;
  -ms-grid-column: 1!important;
  grid-column-start: 1!important;
  -ms-grid-row-span: 1!important;
  grid-row-end: 2!important;
  -ms-grid-row: 1!important;
  grid-row-start: 1!important;
}

.w-checkbox::after {
  content: ' '!important;
  display: table!important;
  -ms-grid-column-span: 1!important;
  grid-column-end: 2!important;
  -ms-grid-column: 1!important;
  grid-column-start: 1!important;
  -ms-grid-row-span: 1!important;
  grid-row-end: 2!important;
  -ms-grid-row: 1!important;
  grid-row-start: 1!important;
  clear: both!important;
}

.w-checkbox-input {
  float: left!important;
  margin-bottom: 0px!important;
  margin-left: -20px!important;
  margin-right: 0px!important;
  margin-top: 4px!important;
  line-height: normal!important;
}

.w-checkbox-input--inputType-custom {
  border-top-width: 1px!important;
  border-bottom-width: 1px!important;
  border-left-width: 1px!important;
  border-right-width: 1px!important;
  border-top-color: #ccc!important;
  border-bottom-color: #ccc!important;
  border-left-color: #ccc!important;
  border-right-color: #ccc!important;
  border-top-style: solid!important;
  border-bottom-style: solid!important;
  border-left-style: solid!important;
  border-right-style: solid!important;
  width: 12px!important;
  height: 12px!important;
  border-bottom-left-radius: 2px!important;
  border-bottom-right-radius: 2px!important;
  border-top-left-radius: 2px!important;
  border-top-right-radius: 2px!important;
}

.fabrikgrid_radio > .fabrikinput:checked {
  background-color: #3898ec!important;
  border-top-color: #3898ec!important;
  border-bottom-color: #3898ec!important;
  border-left-color: #3898ec!important;
  border-right-color: #3898ec!important;
  background-image: url('https://d3e54v103j8qbb.cloudfront.net/static/custom-checkbox-checkmark.589d534424.svg')!important;
  background-position: 50% 50%!important;
  background-size: cover!important;
  background-repeat: no-repeat!important;
}

.w-checkbox-input--inputType-custom.w--redirected-focus {
  box-shadow: 0px 0px 3px 1px #3898ec!important;
}

h1 {
  margin-top: 20px!important;
  margin-bottom: 10px!important;
  color: #482683!important;
  font-size: 45px!important;
  line-height: 45px!important;
  font-weight: 700!important;
}
a:hover{
text-decoration:none!important;
}
ol{
margin:0!important;
padding:0!important;
}
li {
  color: #636363!important;
  font-size: 18px!important;
  line-height: 24px!important;
}

.g-container-main{
	background-attachment: fixed!important;
}
.icon-question-sign{
width:0!important;
height:0!important;
}
.g-navigation {
  display: -webkit-box!important;
  display: -webkit-flex!important;
  display: -ms-flexbox!important;
  display: flex!important;
  width: auto!important;
  height: auto!important;
  min-height: 20vh!important;
  min-width: auto!important;
  margin: 0px!important;
  padding: 0px!important;
  -webkit-box-pack: center!important;
  -webkit-justify-content: center!important;
  -ms-flex-pack: center!important;
  justify-content: center!important;
  -webkit-box-align: center!important;
  -webkit-align-items: center!important;
  -ms-flex-align: center!important;
  align-items: center!important;
}

.g-menu-overlay {
  display: none!important;
  width: 0px!important;
  height: 0px!important;
  min-height: auto!important;
  min-width: auto!important;
  margin: 0px!important;
  padding: 0px!important;
  border: 0px none #000!important;
  background-color: transparent!important;
  text-align: left!important;
  text-transform: none!important;
}

.g-feature {
  position: relative!important;
  width: 100%!important;
  height: 0px!important;
}

.g-grid {
  width: 100%!important;
}

.custom {
  width: 150px!important;
  height: 100px!important;
  background-image: url('../images/5e0f713da622fb5e5032474a_5e0101728d0e1e94a13f80de_Groupe-VYV_Q.svg')!important;
  background-position: 50% 50%!important;
  background-size: contain!important;
  background-repeat: no-repeat!important;
}

.span12 {
  width: 450px!important;
  margin-right: auto!important;
  margin-left: auto!important;
}

.validate-password {
  height: 48px!important;
  background-color: #fff!important;
  color: #482683!important;
}

.validate-password::-webkit-input-placeholder {
  color: #482683!important;
}

.validate-password:-ms-input-placeholder {
  color: #482683!important;
}

.validate-password::-ms-input-placeholder {
  color: #482683!important;
}

.validate-password::placeholder {
  color: #482683!important;
}

.g-page-surround {
  display: block!important;
  width: auto!important;
  height: auto!important;
  min-height: auto!important;
  min-width: auto!important;
  margin: 0px!important;
  padding: 0px!important;
  border: 0px none transparent!important;
  border-radius: 0px!important;
  background-color: transparent!important;
  text-transform: none!important;
}

.text-span {
  color: #ee7937!important;
}

.text-span-2 {
  color: #ee7937!important;
}
.span4{
width:100%!important;
}
.form-actions{
padding:0!important;
margin:0!important;
border:none!important;
	background: transparent!important;
}
.nav{
	margin-bottom:0!important;
}
.btn {
  width: 100%!important;
  margin-top: 15px!important;
  margin-bottom: 12px!important;
  padding: 16px 30px!important;
  background-color: #482683!important;
  -webkit-transition: background-color 200ms ease, -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53)!important;
  transition: background-color 200ms ease, -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53)!important;
  transition: transform 200ms cubic-bezier(.55, .085, .68, .53), background-color 200ms ease!important;
  transition: transform 200ms cubic-bezier(.55, .085, .68, .53), background-color 200ms ease, -webkit-transform 200ms cubic-bezier(.55, .085, .68, .53)!important;
  background-image:none!important;
  border:none!important;
  box-shadow:none!important;
  border-bottom-right-radius:none!important;
  border-bottom-left-radius:none!important;
  border-top-right-radius:none!important;
  border-top-left-radius:none!important;
	margin-bottom: 80px!important;
}

.btn:hover {
  background-color: #ee7937!important;
  -webkit-transform: translate(0px, -3px)!important;
  -ms-transform: translate(0px, -3px)!important;
  transform: translate(0px, -3px)!important;
}
.btn-group{
display:block!important;
}
.gantry {
  background-color: #f5f5f5!important;
  font-family: Wigrum, sans-serif!important;
}

.alter {
  display: -webkit-box!important;
  display: -webkit-flex!important;
  display: -ms-flexbox!important;
  display: flex!important;
  width: 450px!important;
  margin-right: auto!important;
  margin-left: auto!important;
  padding: 15px 15px 5px!important;
  -webkit-box-pack: center!important;
  -webkit-justify-content: center!important;
  -ms-flex-pack: center!important;
  justify-content: center!important;
  -webkit-flex-wrap: wrap!important;
  -ms-flex-wrap: wrap!important;
  flex-wrap: wrap!important;
  -webkit-box-align: center!important;
  -webkit-align-items: center!important;
  -ms-flex-align: center!important;
  align-items: center!important;
  background-color: #ee7937!important;
  color: #fff!important;
  text-align: center!important;
  text-transform: uppercase!important;
}

.fabrikForm {
  padding-top: 5px!important;
  color: #482683!important;
}
.controls{
	margin-left:0!important;
}
.control-label{
	width: 100% !important;
	margin-bottom: 5px !important;
	text-align: left!important;
}
.form-horizontal .control-group{
	margin-bottom: 0!important;
}
.fabrikinput {
  height: 48px!important;
  margin-bottom: 20px!important;
  background-color: #fff!important;
  color: #2d1852!important;
}

.fabrikinput::-webkit-input-placeholder {
  background-color: #fff!important;
  color: #482683!important;
}

.fabrikinput:-ms-input-placeholder {
  background-color: #fff!important;
  color: #482683!important;
}

.fabrikinput::-ms-input-placeholder {
  background-color: #fff!important;
  color: #482683!important;
}

.fabrikinput::placeholder {
  background-color: #fff!important;
  color: #482683!important;
}

.input-medium {
  height: 48px!important;
  margin-bottom: 20px!important;
  background-color: #fff!important;
  color: #2d1852!important;
  width:100%!important;
}

.input-medium::-webkit-input-placeholder {
  background-color: #fff!important;
  color: #482683!important;
}

.input-medium:-ms-input-placeholder {
  background-color: #fff!important;
  color: #482683!important;
}

.input-medium::-ms-input-placeholder {
  background-color: #fff!important;
  color: #482683!important;
}

.input-medium::placeholder {
  background-color: #fff!important;
  color: #482683!important;
}

.fabrikgrid_checkbox.span12 {
  display: -webkit-box!important;
  display: -webkit-flex!important;
  display: -ms-flexbox!important;
  display: flex!important;
  margin-top: 25px!important;
  margin-bottom: 15px!important;
  -webkit-box-align: center!important;
  -webkit-align-items: center!important;
  -ms-flex-align: center!important;
  align-items: center!important;
}

.fabrikgrid_yes.checkbox {
  margin-top: 6px!important;
  margin-bottom: 0px!important;
  padding-left: 0px!important;
  font-size: 14px!important;
  line-height: 14px!important;
  font-weight: 700!important;
}

#jos_emundus_users___terms_and_conditions_0_input_0 {
  width: 30px!important;
  height: 30px!important;
  margin-right: 17px!important;
  border-style: solid!important;
  border-color: #b6b6b6!important;
  border-radius: 50%!important;
  background-color: #fff!important;
}

#jos_emundus_users___terms_and_conditions_0_input_0:checked {
  border-color: #482683!important;
  background-color: #482683!important;
  background-size: 50%!important;
}
.fabrikgrid_radio.span2{
	width: auto!important;
	margin-bottom: 15px!important;
}
.fabrikgrid_radio.span2 input{
	margin-bottom: 0!important;
	margin-left: 0!important;
	margin-right: 5px!important;
}
.fabrikgrid_radio.span2 .radio{
	display: flex!important;
	flex-direction: row!important;
	justify-content: center!important;
	align-items: center!important;
	padding-left: 0!important;
	margin-right: 20px!important;
	
}
.fabrikgrid_checkbox.span12 .checkbox{
	display: flex!important;
	flex-direction: row!important;
	justify-content: center!important;
	align-items: center!important;
}
.fabrikgrid_checkbox.span12 input{
	margin-bottom: 0!important;
	margin-left: 0!important;
}
.fabrikgrid_radio.span12 {
  display: -webkit-box!important;
  display: -webkit-flex!important;
  display: -ms-flexbox!important;
  display: flex!important;
  margin-top: 25px!important;
  margin-bottom: 15px!important;
  -webkit-box-align: center!important;
  -webkit-align-items: center!important;
  -ms-flex-align: center!important;
  align-items: center!important;
}

.fabrikgrid_radio .fabrikinput {
  width: 30px!important;
  height: 30px!important;
  border-color: #b6b6b6!important;
  background-color: #fff!important;
}

.fabrikgrid_radio .fabrikinput:checked, .fabrikgrid_checkbox .fabrikinput:checked {
  border-width: 1px!important;
  border-color: #482683!important;
  background-color: #482683!important;
padding-right: 5px!important;
margin-right: 5px!important;
	background-image: url('https://d3e54v103j8qbb.cloudfront.net/static/custom-checkbox-checkmark.589d534424.svg')!important;
	background-size: 50%;
background-repeat: no-repeat;
background-position: 50%;
}
/**** VALIDATION STARS ****/
.applicant-form, .em-formRegistrationCenter .form.fabrikForm .row-fluid label.fabrikLabel.control-label.fabrikTip {
    display: inline !important;
  }
  .icon-star.small {
    display: none!important;
  }

.view-reset .star{
    display:none!important;
  }

.fabrikLabel[opts*="Validation"]::after,
.control-label .required::after,
#jos_emundus_users___terms_and_conditions span::before {
    content: "∗"!important;
    color: red!important;
    padding-left: 5px!important;
    top: -5px!important;
    position: relative!important;
}
.control-group label.fabrikEmptyLabel{
	display: none!important;
}
.fabrikElement input{
	border-radius: 0!important;
}
input[type='radio'],input[type='checkbox'] {
    margin-top:0;
    -webkit-appearance: none;
    border: 2px solid $base-secondary-color;
    border-radius: 50%!important;
    width:10px;
    height: 10px;
    
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
		background-size: 60% auto!important;
	}
	.span12{
		float:none!important;
		width:90%!important;
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
/* END - Your CSS styling ends here */

EOT;
