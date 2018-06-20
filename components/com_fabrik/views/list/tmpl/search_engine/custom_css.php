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
.fabrikForm {
	margin-top: 25px !important;
}

.label-lightblue {
    background-color: #6bb9F0 !important;
}
.label-blue {
    background-color: #19B5FE !important;
}
.label-darkblue {
    background-color: #013243 !important;
}.label-lightgreen {
    background-color: #00E640 !important;
}
.label-green {
    background-color: #3FC380 !important;
}
.label-darkgreen {
    background-color: #1E824C !important;
}
.label-lightyellow {
    background-color: #FFFD7E !important;
}
.label-yellow {
    background-color: #FFFD54 !important;
}
.label-darkyellow {
    background-color: #F7CA18 !important;
}
.label-lightorange {
    background-color: #FABE58 !important;
}
.label-orange {
    background-color: #E87E04 !important;
}
.label-darkorange {
    background-color: #D35400 !important;
}
.label-lightred {
    background-color: #EC644B !important;
}
.label-red {
    background-color: #CF000F !important;
}
.label-darkred {
    background-color: #96281B !important;
}
.label-lightpurple {
    background-color: #DCC6E0 !important;
}
.label-purple {
    background-color: #947CB0 !important;
}
.label-darkpurple {
    background-color: #663399 !important;
}


table {
	font-family: Arial, sans-serif;
	width: 100%;
	float: right;
	margin-bottom:50px;
}
td, th {
	text-align: left;
	padding: 10px;
}
p {
	font-size: 16px;
	color:black;
}
.em-search-engine-div-data {
	width:95%; 
	height:auto; 
	text-align:justify;
	border: 1px solid;
	border-radius:5px;
	padding: 10px;
	box-shadow: 5px 10px #a22727;
}
.em-search-engine-filters {
	width:750px;
	float:left;
	position:absolute;
    height: -webkit-fill-available;
}
.em-search-engine-data {
	margin-left:38%;
	min-width:400px;
}
.fabrikDataContainer{
	border-radius:5px;
	display: grid;
	grid-gap: 5px;
}
.filtertable{
	border: 1px solid #b9b9b9f5; 
	border-radius: 5px;
	border-style: hidden; 
	box-shadow: 0 0 10px 0 #a22727;
	float:left
}
.fabrikButtonsContainer{
	background-color:#d6d6d6;
	border-radius:3px;
	height:53px
}
.form{
	margin-right: 15%;
}

.main{
	display:inline-flex;
}
/* END - Your CSS styling ends here */
EOT;
