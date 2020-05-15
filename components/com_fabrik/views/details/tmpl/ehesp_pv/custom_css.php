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
h1,h2,h3{
    font-family: 'Signika', sans-serif !important;
    color: #395c9b !important;
}

body {
    margin-top:110px !important;
    margin-bottom:1.5em;
}
#headerdompdf {
    display:block;
    position: fixed;
    top: 0px;
    width:100%;
    height:110px;
    border-bottom: 1px solid black;
}

.em-headerdompdf img {
    float: left;
}

.em-headerdompdf-title {
    width:320px;
    margin-left:175px;
    text-align: center !important;
    line-height: 5
}

.em-headerdompdf-title h1 {
    font-size: 1.5rem;
    color: #395c9b; 
    margin: 0px !important;
    padding: 0px !important;
}

.em-headerdompdf-title span {
    margin: 0px !important;
    padding: 0px !important;
    width:320px !important;
    line-height: 15px !important;
    
}
.second-span {
    float: none !important;
    width: 150px !important;
    display: inline-block !important;
}

.commission-date, .commission-date > div{
    display: inline !important;
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

table {
    min-width: 100% !important;
}

td {
    padding: 1px !important;
}
.table-data {
    font-size: 10px !important;
    padding: 0px !important;
}

.fb_el_jos_emundus_pv___campaign_id_ro{
    display:none !important;
}

h3 {
    font-size: 1.2rem !important;
    text-decoration: underline;
    margin-bottom: 0px !important;
    padding-bottom: 0px !important;
}

#group734 {page-break-after: always;}


.repeatGroupTable .fabrikElementReadOnly {
    margin-top: -1px !important;
    padding: 15px 0 !important; 
}
/* END - Your CSS styling ends here */

EOT;

