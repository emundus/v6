<?php
/**
 * Fabrik List Template: Bootstrap
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

header('Content-type: text/css');
$c = $_REQUEST['c'];
//buttonCount = (int) $_REQUEST['buttoncount'];
//$buttonTotal = $buttonCount === 0 ? '100%' : 30 * $buttonCount ."px";
echo "
.fabrikButtonsContainer li.dropdown-item {border-bottom:1px solid grey;padding: 0.5rem 1rem;}
.fabrikButtonsContainer li.dropdown-item a {text-decoration:none;}

.fabrikButtonsContainer .hasFilters, .fabrikFilterContainer .hasFilters {color:red;}

.fabrikDataContainer {
	clear:both;
	max-width:100%;}
	
.fabrikDataContainer .table thead th {
white-space: initial;}


.fabrikDataContainer .pagination a{
	/*float: left;*/
	color: inherit;
	text-decoration: inherit;
}

ul.fabrikRepeatData {
	list-style: none;
	list-style-position:inside;
	margin: 0;
	padding-left: 0;
}
.fabrikRepeatData > li {
	white-space: nowrap;
	max-width:350px;
	overflow:hidden;
	text-overflow: ellipsis;
}
td.repeat-merge div, td.repeat-reduce div,
td.repeat-merge i, td.repeat-reduce i {
padding: 5px !important;
}

.nav li {
list-style: none;
}

.filtertable_horiz {
	display: inline-block;
	vertical-align: top;
}

.fabrikListFilterCheckbox {
	text-align: left;
}

.fabrikDateListFilterRange {
	text-align: left;
}
/* added for J!4 */
.btn-group {margin:0}
";?>
