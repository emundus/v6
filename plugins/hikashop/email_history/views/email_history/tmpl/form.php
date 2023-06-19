<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('email_history'); ?>" method="post" name="adminForm" id="adminForm" >
<div id="page-email_log" class="hk-row-fluid hikashop_backend_tile_edition">
	<div class="hkc-md-6">
				<div class="hikashop_tile_block">
					<div>
						<div class="hikashop_tile_title"><?php
							echo JText::_('MAIN_INFORMATION');
						?></div>
					<table class="admintable table">
						<tr>
							<td class="key">
								<?php echo JText::_( 'FROM_NAME' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->element->email_log_sender_name); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'FROM_ADDRESS' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->element->email_log_sender_email); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'TO_NAME' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->element->email_log_recipient_name); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'TO_ADDRESS' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->element->email_log_recipient_email); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'REPLYTO_NAME' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->element->email_log_reply_name); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'REPLYTO_ADDRESS' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->element->email_log_reply_email); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'BCC' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->element->email_log_bcc_email); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'CC' ); ?>
							</td>
							<td>
								<?php echo $this->escape(@$this->element->email_log_cc_email); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'EMAIL_SUBJECT' ); ?>
							</td>
							<td>
								<?php echo $this->escape($this->element->email_log_subject); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'DATE' ); ?>
							</td>
							<td>
								<?php echo hikashop_getDate($this->element->email_log_date);?>
							</td>
						</tr>
					</table>
				</div></div>
				<div class="hikashop_tile_block">
					<div>
						<div class="hikashop_tile_title"><?php
							echo JText::_('ORDER_ADD_INFO');
						?></div>
					<table class="admintable table">
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_EMAIL' ); ?>
							</td>
							<td>
								<?php echo JText::_(@$this->element->email_log_name); ?>
							</td>
						</tr>
<?php if(!empty($this->element->email_log_ref_id)){ ?>
						<tr>
							<td class="key">
							<?php
								if(in_array($this->element->email_log_name,$this->email_order_id) )
									 echo JText::_( 'ORDER_NUMBER' );

								if(in_array($this->element->email_log_name,$this->email_product_id) )
									 echo JText::_( 'PRODUCT_NAME' );

								if(in_array($this->element->email_log_name,$this->email_user_id) )
									 echo JText::_( 'CLIENT' );
							?>
							</td>
							<td>
								<?php if(in_array($this->element->email_log_name,$this->email_order_id) ){  ?>
									<a href="<?php echo hikashop_completeLink('order&task=edit&cid[]='.$this->element->email_log_ref_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('email_log&task=edit&cid[]='.$this->element->email_log_id)))); ?>"><?php echo @$this->email_order_number; ?></a>
								<?php } ?>
								<?php if(in_array($this->element->email_log_name,$this->email_product_id) ){  ?>
									<a href="<?php echo hikashop_completeLink('product&task=edit&cid[]='.$this->element->email_log_ref_id.'&cancel_redirect='.urlencode(base64_encode(hikashop_completeLink('email_log&task=edit&cid[]='.$this->element->email_log_id)))); ?>"><?php echo @$this->email_product_name; ?></a>
								<?php } ?>
								<?php if(in_array($this->element->email_log_name,$this->email_user_id) ){  ?>
									<a href="<?php echo hikashop_completeLink('user&task=edit&cid[]='.$this->element->email_log_ref_id.'&cancel_redirect='.urlencode(hikashop_completeLink('email_log&task=edit&cid[]='.$this->element->email_log_id))); ?>"><?php echo $this->escape(@$this->email_user_name); ?></a>
								<?php } ?>
							</td>
						</tr>
<?php } ?>
					</table>
				</div></div>
<?php if(!empty($data->email_log_params['attachments'])) { ?>
				<div class="hikashop_tile_block">
					<div>
						<div class="hikashop_tile_title"><?php
							echo JText::_('ATTACHMENTS');
						?></div>
					<table class="adminlist table table-striped table-hover">
						<thead>
							<tr>
								<th class="title titlenum">
									<?php echo JText::_( 'HIKA_NUM' );?>
								</th>
								<th class="title">
									<?php echo JText::_( 'HIKA_NAME' );?>
								</th>
								<th class="title">
									<?php echo JText::_( 'HIKA_PATH' );?>
								</th>
							</tr>
						</thead>
						<tbody>
<?php
							$i = 1;
							$k = 0;
							foreach($data->email_log_params['attachments'] as $attach) {
?>
							<tr class="row<?php echo $k; ?>">
								<td class="hk_center">
									<?php echo $i; ?>
								</td>
								<td>
									<a href="<?php echo $attach->url; ?>"><?php echo $attach->name; ?></a>
								</td>
								<td>
									<?php echo $attach->filename; ?>
								</td>
							</tr>
<?php
								$k = 1-$k;
								$i++;
							}
?>
						</tbody>
					</table>
				</div></div>
<?php } ?>
	</div>
	<div class="hkc-md-6">
		<?php echo $this->loadTemplate('param'); ?>
	</div>
</div>

	<div style="clear:both" class="clr"></div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="cid" value="<?php echo $this->element->email_log_id; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="email_history" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
