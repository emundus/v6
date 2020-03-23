<?php
/**
 *
 * Modify user form view, User info
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: edit_shopper.php 8565 2014-11-12 18:26:14Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
$btnclass = $ini_array['btnclass'];
$saveoption = $ini_array['saveoption'];

if(!$this->userDetails->user_is_vendor){ ?>
<div class="buttonBar-right">
	<button class="<?php echo $btnclass; ?>" type="submit" onclick="javascript:return myValidator(userForm, true);" ><?php echo $this->button_lbl ?></button>
	&nbsp;
	<button class="<?php echo $saveoption; ?>" type="reset" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_virtuemart&view=user', FALSE); ?>'" ><?php echo vmText::_('COM_VIRTUEMART_CANCEL'); ?></button>
</div>
<div style="clear: both;"></div>
<?php if( $this->userDetails->virtuemart_user_id == 0) {?>
<fieldset class="floatleft">
<?php }}
if( $this->userDetails->virtuemart_user_id!=0) {
    echo $this->loadTemplate('vmshopper');
}

echo $this->loadTemplate('address_userfields');

if ($this->userDetails->JUser->get('id') ) {
  echo $this->loadTemplate('address_addshipto');
}

if(!empty($this->virtuemart_userinfo_id)){
	echo '<input type="hidden" name="virtuemart_userinfo_id" value="'.(int)$this->virtuemart_userinfo_id.'" />';
}
?>
<input type="hidden" name="task" value="saveUser" />
<input type="hidden" name="address_type" value="<?php echo $this->address_type; ?>"/>

