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
 * This file will be invoked as a PHP file, so the view type and form ID
 * can be used in order to narrow the scope of any style changes.  You do
 * this by prepending #{$view}_$c to any selectors you use.  This will become
 * (say) #form_12, or #details_11, which will be the HTML ID of your form
 * on the page.
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
h1,h2{
    font-family: 'euclidflex-reg' !important;
    color: #002e5d !important;
    font-size: 16px !important;
}

body {
    margin-top:110px !important;
    margin-bottom:1.5em;
}
.legend {
    padding:10px;
    background:#ddd;
    color:black;
    text-align:center;
    width:100%;
}
#headerdompdf {
    display:block;
    position: fixed;
    top: 0px;
    width:100%;
    height:150px;
}

.em-headerdompdf img {
    float: left;
}

.em-headerdompdf-title {
    margin-left:175px;
    margin-bottom: 15px;
}
.right{
    text-align: right !important;
}
.center{
    text-align: center !important;
}

.em-headerdompdf-title h1 {
    font-size: 14px;
    color: #002e5d; 
    margin: 0px !important;
    padding: 0px !important;
}

.em-headerdompdf-title span, .em-headerdompdf-title p {
    margin: 0px !important;
    padding: 0px !important;
    line-height: 15px !important;
    text-align:center;
    font-size:15px;
    
}
.schoolyear, .schoolyear > div{
display:inline-block !important;
text-align:center;
}
.second-span {
    float: none !important;
    width: 150px !important;
    display: inline-block !important;
}

.em-headerdompdf-title br {
    display: none !important;
}

#footdompdf {
    display:block;
    position: fixed;
    bottom: 0px;
    width:100%;
    height: 1.1em;
    border-top: 1px solid black;
}

#footdompdf .pagenum { 
    position:absolute;
    right: 20px;
}
#footdompdf .pagenum:after {
    content:  counter(page);
}

#footdompdf .footleft {
    color:red;
}

table, table.repeatGroupTable, table.repeatGroupTable th, table.repeatGroupTable td{
    min-width: 100% !important;
    border: 2px solid black;
    padding:0;
    border-spacing : 0;
border-collapse : collapse;
}

td {
    padding: 1px !important;
}
.table-data {
    font-size: 10px !important;
    padding: 0px !important;
}

h3 {
    font-size: 1.2rem !important;
    margin-bottom: 0px !important;
}

#group744{
    display:none;
}

.repeatGroupTable .fabrikElementReadOnly {
    margin-top: -1px !important;
    padding: 15px 0 !important; 
}
/* END - Your CSS styling ends here */

EOT;

