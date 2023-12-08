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
   /*overflow: auto;  */
}

.view-list .fabrik_action.dropdown-toggle.btn.btn-mini{
	margin: 0;
	height: min-content;
}

";?>