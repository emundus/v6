<?php
/**
 * Fabrik List Template: Div CSS (including CSS for format=pdf)
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2023  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

header('Content-type: text/css');
$c = $_REQUEST['c'];

$pdf_format = array_key_exists("format",$_REQUEST)? $_REQUEST['format']=='pdf' : false;
echo "
.fabrikButtonsContainer .hasFilters, .fabrikFilterContainer .hasFilters {color:red;}
ul.fabrikRepeatData {
	list-style: none;
	list-style-position:inside;
	margin: 0;
	padding-left: 0;
}
.fabrikDataContainer .pagination a{
	color: inherit;
	text-decoration: inherit;
}
/** Hide the checkbox in each record*/

#listform_$c .fabrikList .fabrik_select {
	display: none;
}

#listform_$c .row	{margin-bottom:1em;}
#listform_$c .fabrik_divrow {position: relative;height: 100%;}
#listform_$c .table-bordered .fabrik_divrow {    border: solid 1px;padding: 5px;}

/*action buttons as dropown*/
#listform_$c .fabrik_action {
	position: absolute;
	top: 10px;
	right: 10px;
}

";
//For (dom)pdf set flex to inline-block
if ($pdf_format) echo "
#listform_$c .row {position:relative;display:block;    page-break-inside: avoid}
#listform_$c .fabrik_row {display:inline-block;position:relative;vertical-align:top;}
#listform_$c .fabrik_divrow {height:auto;}
#listform_$c .col-sm-2 {width:16%}
#listform_$c .col-sm-3 {width:24%}
#listform_$c .col-sm-4 {width:32%}
#listform_$c .col-sm-6 {width:48%}
#listform_$c .col-sm-12 {width:96%}
";?>
