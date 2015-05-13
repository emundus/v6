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


?>
<table border="0">
	<tr><td>Name</td><td><?php echo $this->data['custom_balloon_orders___full_name']?></td></tr>
	<tr><td>Phone</td><td><?php echo $this->data['custom_balloon_orders___phone']?></td></tr>
	<tr><td>Email</td><td><?php echo $this->data['custom_balloon_orders___email']?></td></tr>
	<tr><td>Shop</td><td><?php echo JArrayHelper::getValue($this->data['custom_balloon_orders___shop'], 0, '')?></td></tr>
	<tr><td>Custom Balloon Description</td>
	<td><?php echo JArrayHelper::getValue($this->data['custom_balloon_orders___custom_balloon_description'], 0, '')?></td></tr>
	<tr><td>ref</td><td><?php echo $this->data['custom_balloon_orders___id']?></td></tr>
</table>
