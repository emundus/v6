<?php
/**
 * Fabrik List Template: Default Custom CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
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

.span6 {
	width:100%!important;
	background-color:white;
	padding:1%;
	color:#de6339!important;
	box-shadow: 0px 0px 5px 0px lightgrey;
}


.tabData thead {
	background:white!important;
}

#g-container-main {
	padding-left:5%!important;
	padding-right:5%!important;
	background-color:#f8f8f8;
}

.g-content h1::after {
	background:#de6339;
}

.fabrikDataContainer .tabData tbody {
	border-left:2px solid #de6339;
	border-right:2px solid #de6339;
}

.fabrikDataContainer .tabData .fabrik_groupdata {
	border-bottom:2px solid #de6339;
}

.fabrikDataContainer .tabData thead {
	border-top:2px solid #de6339;
	border-left:2px solid #de6339;
	border-right:2px solid #de6339;
}

.pagination a {
	color:gray !important;
	background-color:#f5f5f5!important;
}

.pagination ul > .suivantPagination > a, .pagination ul > .active > a {
	background-color:white!important;
	color: var(--em-profile-color) !important;
}

.fabrikButtonsContainer a {
	color:#de6339;
}

.fabrikButtonsContainer a .caret {
	border-top-color: #de6339;
	border-bottom-color: #de6339;
}

.input-prepend.input-append select, .input-prepend.input-append .add-on {
	height:39.4px;
	border:none;
}

.filtertable {
	margin:1%;
	width:98%!important;
	color:#de6339 !important;
}

.filtertable a {
	color:#de6339;
}

.filtertable>thead>tr>th {
	border-bottom:none!important;
}

.filtertable td {
	border-top:none!important;
}

.filtertable>tbody>tr>td:first-child {
	padding-left:10%!important;
	width:20%!important;
	font-weight:600;
	font-size:12px;
}

.filtertable select[multiple] {
	height:50px!important;
	width:60%;
}

.filtertable select:not([multiple]) {
	height:30px!important;
	width:20%;
}

.modal-header {
	background:#de6339 !important;
	display:table;
	width:100%;
}

.modal-header h3 {
	display:table-cell;
	vertical-align:middle;
}

#csvmsg .alert-success {
	display: inline-block!important;
	text-align:center;
}

#csvmsg a:hover {
	display: inline-block!important;
	font-size:14px!important;
	float:none!important;
}

/* END - Your CSS styling ends here */
EOT;
