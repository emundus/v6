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
 * This file will be invoked as a PHP file, so the view type, form ID and row ID
 * can be used in order to narrow the scope of any style changes.  A new form will
 * have an ID of "form_X" (where X is the form's numeric ID), while edit forms (for existing
 * rows) will have an ID of "form_X_Y" (where Y is the rowid).  Detail views will always
 * be of the format "details_X_Y".
 *
 * So to apply styles for (say) form ID 123, you would use ...
 *
 * #form_123, #form_123_$rowid { ... }
 *
 * Or to style for any form / row, it would just be ...
 *
 * #$form { ... }
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


/* INSCRIPTION */ 

.view-registration .em-heading-registration {
    display: flex;
    justify-content: flex-start;
    align-items: flex-end;
    flex-direction: row;
    width: 57%;
    margin-left: auto;
    margin-right: auto;
}

.view-registration .icon-title.registrationicon {
      background-image: url(/components/com_emundus_onboard/src/assets/images/register.svg);
      background-size: contain;
      background-repeat: no-repeat;
}

.view-registration .icon-title {
      margin-right: 10px;
      width: 40px;
      height: 40px;
      background-position: 0 0;
      background-size: contain;
      -webkit-filter: brightness(.5);
      filter: brightness(.5);
      fill: grey;
}



.view-login .em-page-header {
    display: flex;
    justify-content: flex-start;
    align-items: flex-end;
    flex-direction: row;
    width: 60%;
    margin-left: auto;
    margin-right: auto;
    margin-bottom: 30px;
}

.view-login .icon-title.icon-login {
    background-image: url(/components/com_emundus_onboard/src/assets/images/enter.svg);
    background-size: contain;
    background-repeat: no-repeat;
    margin-bottom: 5px;
}

.view-login .icon-title {
    margin-right: 10px;
    width: 35px;
    height: 35px;
    background-position: 0 0;
    background-size: contain;
    -webkit-filter: brightness(.5);
    filter: brightness(.5);
    fill: grey;
}

.view-login .em-title {
    margin-top: 50px;
}

.nav.nav-tabs.nav-stacked {
    background: #fff;
    border-radius: 0;
    display: flex;
    justify-content: space-around;
    width: 60%;
    margin-left: auto;
    margin-right: auto;
    margin-top: 0px;
    padding-bottom: 50px;
}

.view-registration .span12 > .view-registration  {
    margin-top: 50px;
    width: 60%;
    margin-left: auto;
    margin-right: auto;
    background: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.view-registration form {
    background: #fff;
    border: none;
    border-radius: 0;
    margin-left: auto;
    margin-right: auto;
    padding: 0;
    width: 60%;
}

.view-registration .control-group {
    display: flex;
    flex-direction: column;
    width: 100%;
 
}

.view-registration form .control-group label {
    text-align: left;
    font-weight: 700;
}

.view-registration form .controls {
    margin: 0;
}

.view-registration form .controls input {
    margin: 0;
    height: 50px;
    border-radius: 4px;
}

.view-registration ol {
    width: 60%;
    text-align: justify;
    margin-left: 0px !important;
}

.view-registration h1 {
    margin-top: 50px;
}

.view-registration .icon-star.small {
    color: #c30505;
    padding-left: 2px;
}


  
/* END - Your CSS styling ends here */

EOT;
