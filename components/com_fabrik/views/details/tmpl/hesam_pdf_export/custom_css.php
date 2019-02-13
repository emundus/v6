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

#$form .foobar {
	display: none;
}
.fabrikForm.fabrikDetails {
        display: block;
        width: 750px;
}
    .em-pdf-group {
    	right: 0;
    	top: 0;
        margin-bottom: 20px;
/*page-break-inside: avoid;*/
    }
   .breaker {
      page-break-before: always;
   }
    .em-pdf-title-div {
        background-color: #e9E9E9;
        border-top: 1px solid;
        border-bottom: 1px solid;
	margin-bottom: 0px;
    }
    .em-pdf-title-div h3 {
        margin: 0px 0px 0px 10px;
    }
    .em-pdf-element {
        font-size: 16px;
        border-bottom: 1px solid;
        display: block;
        width: 745px;
	    margin: 10px 0px;
    }
    .em-pdf-element-label {
        vertical-align: top;
        display: inline-block;
        width: 245px;
        font-weight: bold;
    }
    .em-pdf-element-label p {
        margin: 0px 0px 0px 10px;
    }
    .em-pdf-element-value {
        display: inline-block;
        width: 495px;
	margin-top: 5px;
    }
    .em-pdf-title-div a {
    font-size: 20px !important;
    color: #bb0E29 !important;
}
.fabrikForm.fabrikDetails {
    display: block !important;
    width: 850px !important;
}
.em-pdf-element {
    font-size: 16px !important;
    border-bottom: 1px solid !important;
    display: block !important;
    width: auto !important;
    margin: 10px 0px !important;
}
.em-pdf-element-label {
    vertical-align: top !important;
    display: inline-block !important;
    width: 29% !important;
    font-weight: bold !important;
}
.em-pdf-element-value {
    display: inline-block !important;
    width: 70% !important;
    margin-top: 0px !important;
}

@media only screen and (min-width: 769px) and (max-width:850px) {
    .fabrikForm.fabrikDetails {
        display: block !important;
        width: 750px !important;
    }
}

@media only screen and (max-width: 768px) {
    .fabrikForm.fabrikDetails {
        display: block !important;
        width: 100% !important;
    }
}
    
/* END - Your CSS styling ends here */

EOT;

