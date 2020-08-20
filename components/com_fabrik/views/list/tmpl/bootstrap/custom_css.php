<?php
/**
 * Fabrik List Template: Default Custom CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
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
echo <<<EOT
/* BEGIN - Your CSS styling starts here */

.g-back-office-emundus-tableau.view-list #g-container-main .page-header, 
.g-back-office-emundus-tableau.view-form #g-container-main .page-header, 
g-back-office-emundus-tableau.view-details #g-container-main .page-header {
    padding-bottom: 30px;
}

/* PAGINATION */

.list-footer .limit .input-prepend.input-append span.add-on {
	padding: 9px !important;
    height: 45px;
}

.list-footer .limit .input-prepend.input-append select.inputbox.input-mini {
    padding: 10px !important;
    width: 80px;
    height: 45px;
}

.list-footer .pagination ul.pagination-list {
    box-shadow: none;
}

ul.pagination.pagination-sm a {
  color: #4E5B6D;
}

ul.pagination.pagination-sm a:hover, ul.pagination.pagination-sm a:focus, ul.pagination.pagination-sm a:active {
    color: #404B5A;
}

.pagination > li.active a {
  font-weight: bold !important;
}

table {
  border-radius: 0 !important;
}

@media all and (max-width: 767px) {
  #listform_84_com_fabrik_84 {
    padding: 0 !important;
  }
  
  .view-list form {
    overflow: auto;
  }
}

@media all and (max-width: 959px) {
  .email-history-indicators-page form {
    padding: 0 !important;f
  }
}

@media screen and (min-width: 768px) and (max-width: 959px) {
  .fabrikForm.form-search {
    overflow: auto;
  }
}

@media all and (max-width: 1239px) {
  .fabrikDataContainer {
    overflow: auto;
  }
}

/* END - Your CSS styling ends here */
EOT;
