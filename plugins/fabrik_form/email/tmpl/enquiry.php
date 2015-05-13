<?php
/**
 * This is a sample email template. It will just print out all of the request data:
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.email
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$colors = json_decode('[{"0,0,0":"Black","98,56,142":"Purple","227,6,19":"Red","235,237,0":"Yellow","0,150,64":"Green","14,114,186":"Blue","49,39,131":"Navy Blue","230,0,126":"Hot Pink","255,255,255":"White","208,168,229":"Lavender","233,78,27":"Orange","255,244,140":"Lemon","125,194,149":"Light Green","131,182,214":"Light Blue","183,182,182":"Sliver","239,138,177":"Light Pink"}]');
$colors = $colors[0];

$color1Key = $this->data['enquiry___colour_1'];
$color1 = $colors->$color1Key;

$color2Key = $this->data['enquiry___colour_2'];
$color2 = $colors->$color2Key;

$color3Key = $this->data['enquiry___colour_3'];
$color3 = $colors->$color3Key;
?>
<table border="0">
	<tr><td>Company</td><td><?php echo $this->data['enquiry___company']?></td></tr>
	<tr><td>Contact</td><td><?php echo $this->data['enquiry___contact']?></td></tr>
	<tr><td>Phone</td><td><?php echo $this->data['enquiry___phone']?></td></tr>
	<tr><td>Fax</td><td><?php echo $this->data['enquiry___fax']?></td></tr>
	<tr><td>Email</td><td><?php echo $this->data['enquiry___email']?></td></tr>
	<tr><td>Delivery details</td><td><?php echo $this->data['enquiry___delivery_details']?></td></tr>
	<tr><td>Fax</td><td><?php echo $this->data['enquiry___fax']?></td></tr>

	<tr><td>Quantity</td><td><?php echo $this->data['enquiry___quantity']?></td></tr>
	<tr><td>Colour</td><td><?php echo $this->data['enquiry___colour']?></td></tr>
	<tr><td>Number of colours</td><td><?php echo $this->data['enquiry___number_of_colours']?></td></tr>
	<tr><td>Colour 1</td><td><?php echo $color1?></td></tr>
	<tr><td>Colour 2</td><td><?php echo $color2?></td></tr>
	<tr><td>Colour 3</td><td><?php echo $color3?></td></tr>

	<tr><td>Layout</td><td><?php echo $this->data['enquiry___Balloon_Layout']?></td></tr>
	<tr><td>Direction</td><td><?php echo $this->data['enquiry___artwork']?></td></tr>
	<tr><td>Date required</td><td><?php echo $this->data['enquiry___date_required']?></td></tr>
	<tr><td>Accessories</td><td><?php echo $this->data['enquiry___accesories']?></td></tr>
	<tr><td>ref</td><td><?php echo $this->data['enquiry___id']?></td></tr>


</table>
