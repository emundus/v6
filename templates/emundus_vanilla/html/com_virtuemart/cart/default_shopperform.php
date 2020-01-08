<?php
defined('_JEXEC') or die('Restricted access');
$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
$saveoption = $ini_array['saveoption'];
?>


<h3 class="ttr_page_title"><?php echo vmText::_ ('COM_VIRTUEMART_CART_CHANGE_SHOPPER'); ?></h3>

<form action="<?php echo JRoute::_ ('index.php'); ?>" method="post" class="inline">
	<table cellspacing="0" cellpadding="0" border="0" style="border:0px !important;">
		<tr style="border:0px;">
			<td  style="border:0px;">
				<input type="text" name="usersearch" size="30" maxlength="50" style="width:auto">
				<input type="submit" name="searchShopper" title="<?php echo vmText::_('COM_VIRTUEMART_SEARCH'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_SEARCH'); ?>" class="button <?php echo $saveoption; ?>"  style="margin-left: 10px;"/>
			</td>
			<td style="border:0px; width: 5%;"></td>
			<td style="border:0px;">
				<?php 
				if (!class_exists ('VirtueMartModelUser')) {
					require(VMPATH_ADMIN . DS . 'models' . DS . 'user.php');
				}

				$currentUser = $this->cart->user->virtuemart_user_id;
				echo JHtml::_('Select.genericlist', $this->userList, 'userID', 'class="vm-chzn-select" style="width: 200px"', 'id', 'displayedName', $currentUser,'userIDcart');
				?>
			</td>
			<td style="border:0px;">
				<input type="submit" name="changeShopper" title="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" value="<?php echo vmText::_('COM_VIRTUEMART_SAVE'); ?>" class="button  <?php echo $saveoption; ?>"  style="margin:0 10px 0;"/>
				<input type="hidden" name="view" value="cart"/>
				<input type="hidden" name="task" value="changeShopper"/>
			</td>
		</tr>
		<tr style="border:0px;">
			<td colspan="2" style="border:0px;"></td>
			<td colspan="2" style="border:0px;">
				<?php if($this->adminID && $currentUser != $this->adminID) { ?>
					<b><?php echo vmText::_('COM_VIRTUEMART_CART_ACTIVE_ADMIN') .' '.JFactory::getUser($this->adminID)->name; ?></b>
				<?php } ?>
				<?php echo JHtml::_( 'form.token' ); ?>
			</td>
		</tr>
	</table>
</form>
<br />