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
$c           = $_REQUEST['c'];
$buttonCount = (int) $_REQUEST['buttoncount'];
$buttonTotal = $buttonCount === 0 ? '100%' : 30 * $buttonCount . "px";
echo "

.fabrikDataContainer {
	clear:both;
	/*
		dont use this as it stops dropdowns from showing correctly
		overflow: auto;*/
}

.fabrikDataContainer .pagination a{
	float: left;
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
	display: inline-block;
}

.mod_emundus_campaign__list_content {
	display: flex;
    flex-direction: column;
}

.all-actions-container {
	position: relative;
}

.modal-actions {
	position: absolute;
	height: fit-content !important;
	right: -135px;
	top: 75px;
	z-index: 9;
	box-shadow: 0 12px 17px rgba(5, 47, 55, 0.07), 0 5px 22px rgba(5, 47, 55, 0.06), 0 7px 8px rgba(5, 47, 55, 0.1);
    border-radius: calc(var(--em-applicant-br-cards) / 2);
    padding-top: var(--p-8);
    padding-bottom: var(--p-8);
    position: absolute;
    background: var(--neutral-0);
    width: max-content;
}

.catalogue_tag {
	background: var(--neutral-400);
	color: var(--neutral-700);
	border-radius: 25px;
	padding: 4px;
	width: 150px;
	text-align: center;
	font-size: 14px;
	margin: 12px 0
}
"; ?>
