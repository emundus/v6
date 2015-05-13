<?php
/**
 * Default Form Template: Custom CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */
 ?>
<?php

header('Content-type: text/css');
$c = (int) $_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
echo "

/* BEGIN - Your CSS styling starts here */

.floating-tip {
	font-size: 18px;
}

#{$view}_$c .fabrikGroupRepeater{
	left:10px;
	padding-top:5px;
}

/* END - Your CSS styling ends here */

";
?>