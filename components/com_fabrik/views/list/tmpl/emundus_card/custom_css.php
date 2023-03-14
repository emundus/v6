<?php
/**
 * Fabrik List Template: Default Custom CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
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
echo "

#listform_$c .fabrikForm {
	margin-top: 25px !important;
}

/*radius search responsive*/

@media only screen and (max-device-width: 499px)
{
.fabrikWindow.modal {
	max-width:100% !important;
	left:0 !important;
}
 
.fabrikWindow.modal .contentWrapper {
	max-width:100% !important;
} 

.fabrikWindow .radius_search table.radius_table {
	table-layout:fixed;
	max-width:100%;
} 

.fabrikWindow .radius_search table.radius_table td:first-child {
	width:70px;
}

.radius_search_geocode_map {
	width:300px;
	height:275px;
	margin-top:15px;
}
}

@media only screen and (min-device-width: 500px)
{
.radius_search_geocode_map {
	width:400px;
	height:275px;
	margin-top:15px;
}
}

.us_cities___city {
	font-size: 24px;
}

.us_cities___state_code {
	font-size: 16px;
}

.fab_main_test___yes_no {
	width: 150px;
}

a.advanced-search-link-NOT {
background-color: #4CAF50; /* Green */
border: none;
color: white;
padding: 15px 32px;
text-align: center;
text-decoration: none;
display: inline-block;
font-size: 16px;
}

.view-list form.form-search {
   overflow: auto;  
}

.fabrikImageBackground{
	border-top-left-radius: 8px;
	border-top-right-radius: 8px;
	background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    height: 170px;
}

.list-footer{
	margin-bottom: 16px
}

.view-list .fabrikNav .list-footer span.add-on{
	background: transparent;
	color: #A4A4A4;
}

.fabrikFilterContainer{
    grid-column: span 1;
}

.fabrikFiltersBlock{
    padding: 24px;
    background: #FFFFFF;
	border: 1px solid #EDEDED;
	box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07), 0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);
	border-radius: 16px;
}

.fabrikDataContainer{
    grid-column: span 4;
}

.fabrikDataContainer .page-header{
    margin-top: 0 !important;
}

.view-list .filtertable input.fabrik_filter.search-query.input-medium{
	background-color: #FFFFFF !important;	
    border: 1px solid #E3E3E3 !important;
    border-radius: 8px !important;
}

.view-list .filtertable select.inputbox.fabrik_filter{
	width: 100%;
	height: 38px;
}

";?>