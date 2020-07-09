<?php
defined('_JEXEC') or die('Restricted access');
$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
?>
<div class="billto-shipto">
	<div class="ttr_cart_content col-lg-6 col-md-6 col-sm-6 col-xs-12">
		<div style="margin:5px 0;" class="ttr_prochec_product_title">
			<span class="vmicon vm2-billto-icon"></span>
			<?php echo vmText::_ ('COM_VIRTUEMART_USER_FORM_BILLTO_LBL'); ?>			
		</div>
		<?php // Output Bill To Address ?>
		<div class="output-billto">
			<?php
			$cartfieldNames = array();
			foreach( $this->userFieldsCart['fields'] as $fields){
				$cartfieldNames[] = $fields['name'];
			}

			foreach ($this->cart->BTaddress['fields'] as $item) {
				if(in_array($item['name'],$cartfieldNames)) continue;
				if (!empty($item['value'])) {
					if ($item['name'] === 'agreed') {
						$item['value'] = ($item['value'] === 0) ? vmText::_ ('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_NO') : vmText::_ ('COM_VIRTUEMART_USER_FORM_BILLTO_TOS_YES');
					}
					?><!-- span class="titles"><?php echo $item['title'] ?></span -->
			<span class="values vm2<?php echo '-' . $item['name'] ?>" style="padding-top:10px;"><?php echo $item['value'] ?></span>
			<?php if ($item['name'] != 'title' and $item['name'] != 'first_name' and $item['name'] != 'middle_name' and $item['name'] != 'zip') { ?>
				<br class="clear"/>
			<?php
			}
			}
			} ?>
			<div class="clear"></div>
		</div>

		<?php
		if($this->pointAddress){
			$this->pointAddress = 'required invalid';
		}

		?>
		<a class="forgotpassword <?php echo $this->pointAddress ?>" href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=BT', $this->useXHTML, $this->useSSL) ?>" rel="nofollow">
			<?php echo vmText::_ ('COM_VIRTUEMART_USER_FORM_EDIT_BILLTO_LBL'); ?>
		</a>

		<input type="hidden" name="billto" value="<?php echo $this->cart->lists['billTo']; ?>"/>
	</div>

	<div class="ttr_cart_content col-lg-6 col-md-6 col-sm-6 col-xs-12" style="float: right;">

		<div style="padding: 5px;" class="ttr_prochec_product_title">
		<span class="vmicon vm2-shipto-icon"></span>
			<?php echo vmText::_ ('COM_VIRTUEMART_USER_FORM_SHIPTO_LBL'); ?>			
		</div>
		<?php // Output Bill To Address ?>
		<div class="output-shipto">
			<?php
			if (!class_exists ('VmHtml')) {
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');
			}
			if($this->cart->user->virtuemart_user_id==0){

				echo vmText::_ ('COM_VIRTUEMART_USER_FORM_ST_SAME_AS_BT');
				echo VmHtml::checkbox ('STsameAsBT', $this->cart->STsameAsBT,1,0,'id="STsameAsBTjs" data-dynamic-update=1') . '<br />';
			} else if(!empty($this->cart->lists['shipTo'])){
				echo $this->cart->lists['shipTo'];
			}

			if(empty($this->cart->STsameAsBT) and !empty($this->cart->ST) and !empty($this->cart->STaddress['fields'])){ ?>
				<div id="output-shipto-display" style="margin: 10px;">
					<?php
					foreach ($this->cart->STaddress['fields'] as $item) {
						if (!empty($item['value'])) {
							?>
							<!-- <span class="titles"><?php echo $item['title'] ?></span> -->
							<?php
							if ($item['name'] == 'first_name' || $item['name'] == 'middle_name' || $item['name'] == 'zip') {
								?>
								<span class="values<?php echo '-' . $item['name'] ?>"><?php echo $item['value'] ?></span>
							<?php } else { ?>
								<span class="values"><?php echo $item['value'] ?></span>
								<br class="clear"/>
							<?php
							}
						}
					}
					?>
				</div>
			<?php
			}
			?>
			<div class="clear"></div>
		</div>
		<?php if (!isset($this->cart->lists['current_id'])) {
			$this->cart->lists['current_id'] = 0;

		} ?>
		<a class="forgotpassword" href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=user&task=editaddresscart&addrtype=ST&virtuemart_user_id[]=' . $this->cart->lists['current_id'], $this->useXHTML, $this->useSSL) ?>" rel="nofollow" style="margin: 10px;">
			<?php echo vmText::_ ('COM_VIRTUEMART_USER_FORM_ADD_SHIPTO_LBL'); ?>
		</a>

	</div>

	<div class="clear"></div>
</div>