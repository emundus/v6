<?php
/**
 *
 * Enter address data for the cart, when anonymous users checkout
 *
 * @package    VirtueMart
 * @subpackage User
 * @author Oscar van Eijk, Max Milbers
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: edit_address.php 9164 2016-02-14 00:09:35Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined ('_JEXEC') or die('Restricted access');
global $btnclass,$saveoption;
$app = JFactory::getApplication();
	$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
	$ini_array = parse_ini_file($template_path);
	$btnclass = $ini_array['btnclass'];
	$saveoption = $ini_array['saveoption'];	
// Implement Joomla's form validation
JHtml::_ ('behavior.formvalidation');
JHtml::stylesheet ('vmpanels.css', JURI::root () . 'components/com_virtuemart/assets/css/');

if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
$this->cart = VirtueMartCart::getCart();
$url = 0;
if ($this->cart->_fromCart or $this->cart->getInCheckOut()) {
	$rview = 'cart';
}
else {
	$rview = 'user';
}

function renderControlButtons($view,$rview){	
	global $btnclass,$saveoption;
	?>
<div class="control-buttons">
	<?php
	if ($view->cart->getInCheckOut() || $view->address_type == 'ST') {
		$buttonclass = $saveoption ;
	}
	else {
		$buttonclass = 'button vm-button-correct '.$btnclass;
	}


	
	if (VmConfig::get ('oncheckout_show_register', 1) && $view->userDetails->JUser->id == 0 && $view->address_type == 'BT' and $rview == 'cart') {
		?>
		<div style="float: right;">
		<button name="register" class="<?php echo $buttonclass ?>" type="submit" onclick="javascript:return myValidator(userForm,true);"
				title="<?php echo vmText::_ ('COM_VIRTUEMART_REGISTER_AND_CHECKOUT'); ?>"><?php echo vmText::_ ('COM_VIRTUEMART_REGISTER_AND_CHECKOUT'); ?></button>
		<?php if (!VmConfig::get ('oncheckout_only_registered', 0)) { ?>
			<button name="save" class="<?php echo $buttonclass ?>" title="<?php echo vmText::_ ('COM_VIRTUEMART_CHECKOUT_AS_GUEST'); ?>" type="submit"
					onclick="javascript:return myValidator(userForm, false);"><?php echo vmText::_ ('COM_VIRTUEMART_CHECKOUT_AS_GUEST'); ?></button>
		<?php } ?>
		<button class="<?php echo $saveoption;?>" type="reset"
				onclick="window.location.href='<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=' . $rview.'&task=cancel'); ?>'"><?php echo vmText::_ ('COM_VIRTUEMART_CANCEL'); ?></button>
</div>
<div style="clear: both;"></div>
	<?php
	}
	else {
		?>
		<button class="<?php echo $btnclass ?>" type="submit"
				onclick="javascript:return myValidator(userForm,true);"><?php echo vmText::_ ('COM_VIRTUEMART_SAVE'); ?></button>
		<button class="<?php echo $saveoption;?>" type="reset"
				onclick="window.location.href='<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=' . $rview.'&task=cancel'); ?>'"><?php echo vmText::_ ('COM_VIRTUEMART_CANCEL'); ?></button>
	<?php }	
	
if (VmConfig::get ('oncheckout_show_register', 1) && $view->userDetails->JUser->id == 0 && !VmConfig::get ('oncheckout_only_registered', 0) && $view->address_type == 'BT' and $rview == 'cart') {
		echo '<div id="reg_text" class="ttr_cart_content">'.vmText::sprintf ('COM_VIRTUEMART_ONCHECKOUT_DEFAULT_TEXT_REGISTER', vmText::_ ('COM_VIRTUEMART_REGISTER_AND_CHECKOUT'), vmText::_ ('COM_VIRTUEMART_CHECKOUT_AS_GUEST')).'</div>';			}
	else {
		//echo vmText::_('COM_VIRTUEMART_REGISTER_ACCOUNT');
	}
?>
</div>
<div style="clear: both;"></div>
<?php
}

?>
<h1 class="ttr_page_title"><?php echo $this->page_title ?></h1>
<?php


$task = '';
if ($this->cart->getInCheckOut()){
	//$task = '&task=checkout';
}
$url = 'index.php?option=com_virtuemart&view='.$rview.$task;

echo shopFunctionsF::getLoginForm (TRUE, FALSE, $url);

?>

<form method="post" id="userForm" name="userForm" class="form-validate" action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=user',$this->useXHTML,$this->useSSL) ?>" >

	<h2 class="ttr_prochec_product_title"><?php
		if ($this->address_type == 'BT') {
			echo vmText::_ ('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL');
		}
		else {
			echo vmText::_ ('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL');
		}
		?>
	</h2>

	<!--<form method="post" id="userForm" name="userForm" action="<?php echo JRoute::_ ('index.php'); ?>" class="form-validate">-->
	<?php renderControlButtons($this,$rview); ?>
<div style="clear: both;"></div>
<?php // captcha addition
	if(VmConfig::get ('reg_captcha') && JFactory::getUser()->guest == 1){
		JHTML::_('behavior.framework');
		JPluginHelper::importPlugin('captcha');
		$captcha_visible = vRequest::getVar('captcha');
		$dispatcher = JDispatcher::getInstance(); $dispatcher->trigger('onInit','dynamic_recaptcha_1');
		$hide_captcha = (VmConfig::get ('oncheckout_only_registered') or $captcha_visible) ? '' : 'style="display: none;"';
		?>
		<fieldset id="recaptcha_wrapper" <?php echo $hide_captcha ?>>	
			<?php if(!VmConfig::get ('oncheckout_only_registered')) { ?>
				<span class="userfields_info"><?php echo vmText::_ ('COM_VIRTUEMART_USER_FORM_CAPTCHA'); ?></span>
			<?php } ?>
			<div id="dynamic_recaptcha_1"></div>
		</fieldset>	
		
<?php }?>

<fieldset class="floatleft edit_shipto_address">	
<?php
	// end of captcha addition

	if (!class_exists ('VirtueMartCart')) {
		require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
	}

	if (count ($this->userFields['functions']) > 0) {
		echo '<script language="javascript">' . "\n";
		echo join ("\n", $this->userFields['functions']);
		echo '</script>' . "\n";
	}

	echo $this->loadTemplate ('userfields');	
	if ($this->userDetails->JUser->get ('id')) {
		echo $this->loadTemplate ('addshipto');
	} ?>
	<div style="clear: both;"></div>
	<?php
	renderControlButtons($this,$rview);
	?>
	<input type="hidden" name="option" value="com_virtuemart"/>
	<input type="hidden" name="view" value="user"/>
	<input type="hidden" name="controller" value="user"/>
	<input type="hidden" name="task" value="saveUser"/>
	<input type="hidden" name="layout" value="<?php echo $this->getLayout (); ?>"/>
	<input type="hidden" name="address_type" value="<?php echo $this->address_type; ?>"/>
	<?php if (!empty($this->virtuemart_userinfo_id)) {
		echo '<input type="hidden" name="shipto_virtuemart_userinfo_id" value="' . (int)$this->virtuemart_userinfo_id . '" />';
	}
	echo JHtml::_ ('form.token');
	?>
	</fieldset>	
</form>