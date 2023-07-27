<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_affiliate_main">
<?php
if(!empty($this->user->user_id)){
	$js ='
	function changeCurrency() {
		alert(\''.JText::_('PARTNER_CHANGE_CURRENCY_WARNING').'\');
	}';
	$doc = JFactory::getDocument();
	$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");

	echo $this->toolbarHelper->process($this->toolbar, $this->title);
 } ?>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('affiliate'.$this->url_itemid); ?>" method="post"  name="adminForm" id="adminForm">
	<?php if(!empty($this->user->user_partner_activated)){?>
<?php if(!HIKASHOP_RESPONSIVE) { ?>
<div id="page-affiliate">
	<table style="width:100%">
		<tr>
			<td valign="top" width="50%">
<?php } else { ?>
<div id="page-affiliate" class="row-fluid">
	<div class="span6">
<?php } ?>
				<fieldset class="adminform">
					<legend><?php echo JText::_('MAIN_INFORMATION'); ?></legend>
					<table class="hikashop_affiliate_table table">
						<tr>
							<td class="key">
								<label for="data[user][user_partner_activated]">
									<?php echo JText::_( 'AFFILIATE_ACCOUNT_ACTIVE' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[user][user_partner_activated]" , '',1,JText::_('HIKASHOP_YES'),	JText::_('HIKASHOP_NO')	); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'ID' ); ?>
							</td>
							<td>
								<?php echo $this->escape((string)@$this->user->user_id); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="data[user][user_partner_email]">
									<?php echo JText::_( 'PAYMENT_EMAIL_ADDRESS' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[user][user_partner_email]" class="<?php echo HK_FORM_CONTROL_CLASS; ?>" value="<?php echo $this->escape((string)@$this->user->user_partner_email); ?>" />
							</td>
						</tr>
						<?php if($this->allow_currency_selection){?>
						<tr>
							<td class="key">
								<label for="data[user][user_currency_id]">
									<?php echo JText::_( 'PARTNER_CURRENCY' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->currencyType->display("data[user][user_currency_id]",@$this->user->user_currency_id,'onChange="changeCurrency();" class="'.HK_FORM_SELECT_CLASS.'"');?>
							</td>
						</tr>
						<?php }?>
					</table>
				</fieldset>
<?php if(!HIKASHOP_RESPONSIVE) { ?>
			</td>
			<td valign="top" width="50%">
<?php } else { ?>
	</div>
	<div class="span6">
<?php } ?>
				<fieldset class="adminform">
					<legend><?php echo JText::_('STATS'); ?></legend>

					<?php if($this->advanced_stats){ ?>
							<span id="affiliate_all_clicks">
								<?php
								echo $this->popup->display(
										JText::_('CLICKS_ALL').' <i class="fa fa-chevron-right"></i>',
										'CLICKS_ALL',
										hikashop_completeLink('affiliate&task=clicks&unpaid=0&user_id='.$this->user->user_id.$this->url_itemid,true),
										'hikashop_affiliate_clicks_popup',
										760, 480, 'title="'.JText::_('CLICKS_ALL').'"', '', 'link'
									);
								?>
							</span>
						<?php
						}
						$config =& hikashop_config();
						$affiliate_payment_delay = $config->get('affiliate_payment_delay');
						?>
						<table class="hikashop_affiliate_stats_table table">
							<thead>
								<tr>
									<th></th>
									<?php if(!empty($affiliate_payment_delay)){ ?><th><?php  $delayType = hikashop_get('type.delay'); echo hikashop_tooltip(JText::sprintf('AMOUNT_DELAY',$delayType->displayDelay($config->get('affiliate_payment_delay'))),JText::_('PAYABLE'),'',JText::_('PAYABLE'))?></th><?php } ?>
									<th><?php echo JText::_('HIKASHOP_TOTAL'); ?></th>
								</tr>
							</thead>
							<tbody>
						<?php if(bccomp(sprintf('%F',$this->user->user_params->user_partner_click_fee),0,5)){ ?>
						<tr id="affiliate_unpaid_clicks">
							<td class="key">
								<?php echo JText::_( 'CLICKS_UNPAID_AMOUNT' );
								if($this->advanced_stats){
									echo $this->popup->display(
										'<i class="fa fa-chevron-right"></i>',
										'CLICKS',
										hikashop_completeLink('affiliate&task=clicks&unpaid=1&user_id='.$this->user->user_id.$this->url_itemid,true),
										'hikashop_affiliate_clicks_popup',
										760, 480, '', '', 'link'
									);
								}?>
							</td>
							<?php if(!empty($affiliate_payment_delay)){ ?><td class="hk_center">
								<?php echo $this->currencyHelper->format(@$this->user->accumulated['currentclicks'],@$this->user->user_currency_id); ?>
							</td><?php } ?>
							<td class="hk_center">
								<?php echo $this->currencyHelper->format(@$this->user->accumulated['clicks'],@$this->user->user_currency_id); ?>
							</td>
						</tr>
						<?php
						}
						if(bccomp(sprintf('%F',$this->user->user_params->user_partner_lead_fee),0,5)){
						?>
						<tr id="affiliate_unpaid_leads">
							<td class="key">
								<?php echo JText::_( 'LEADS_UNPAID_AMOUNT' );
								if($this->advanced_stats){
									echo $this->popup->display(
										'<i class="fa fa-chevron-right"></i>',
										'LEADS',
										hikashop_completeLink('affiliate&task=leads&user_id='.$this->user->user_id.$this->url_itemid,true),
										'hikashop_affiliate_leads_popup',
										760, 480, '', '', 'link'
									);
								}?>
							</td>
							<?php if(!empty($affiliate_payment_delay)){ ?><td class="hk_center">
								<?php echo $this->currencyHelper->format(@$this->user->accumulated['currentleads'],@$this->user->user_currency_id); ?>
							</td><?php } ?>
							<td class="hk_center">
								<?php echo $this->currencyHelper->format(@$this->user->accumulated['leads'],@$this->user->user_currency_id); ?>
							</td>
						</tr>
						<?php
						}
						if(bccomp(sprintf('%F',$this->user->user_params->user_partner_percent_fee),0,5) || bccomp(sprintf('%F',$this->user->user_params->user_partner_flat_fee),0,5)){ ?>
						<tr id="affiliate_unpaid_sales">
							<td class="key">
								<?php echo JText::_( 'SALES_UNPAID_AMOUNT' ); if($this->advanced_stats){
									echo $this->popup->display(
										'<i class="fa fa-chevron-right"></i>',
										'SALES',
										hikashop_completeLink('affiliate&task=sales&user_id='.$this->user->user_id.$this->url_itemid,true),
										'hikashop_affiliate_sales_popup',
										760, 480, '', '', 'link'
									);
								}?>
							</td>
							<?php if(!empty($affiliate_payment_delay)){ ?><td class="hk_center">
								<?php echo $this->currencyHelper->format(@$this->user->accumulated['currentsales'],@$this->user->user_currency_id); ?>
							</td><?php } ?>
							<td class="hk_center">
								<?php echo $this->currencyHelper->format(@$this->user->accumulated['sales'],@$this->user->user_currency_id); ?>
							</td>
						</tr>
						<?php
						}?>
						<tr id="affiliate_unpaid_amount">
							<td class="key">
								<?php echo JText::_( 'TOTAL_UNPAID_AMOUNT' ); ?>
							</td>
							<?php if(!empty($affiliate_payment_delay)){ ?><td class="hk_center">
								<?php echo $this->currencyHelper->format(@$this->user->accumulated['currenttotal'],@$this->user->user_currency_id);   ?>
							</td><?php } ?>
							<td class="hk_center">
								<?php echo $this->currencyHelper->format(@$this->user->accumulated['total'],@$this->user->user_currency_id);   ?>
							</td>
						</tr>
						</tbody>
					</table>
				</fieldset>
<?php if(!HIKASHOP_RESPONSIVE) { ?>
			</td>
		</tr>
	</table>
</div>
<?php } else { ?>
	</div>
</div>
<?php } ?>
	<?php }elseif(!empty($this->user->user_id)){?>
	<fieldset class="adminform">
		<legend><?php echo JText::_('MAIN_INFORMATION'); ?></legend>
		<table class="hikashop_affiliate_table table" cellspacing="1" width="100%">
			<tr>
				<td class="key">
					<label for="data[user][user_partner_activated]">
						<?php echo JText::_( 'AFFILIATE_ACCOUNT_ACTIVE' ); ?>
					</label>
				</td>
				<td>
					<?php echo JHTML::_('hikaselect.booleanlist', "data[user][user_partner_activated]" , '',0,JText::_('HIKASHOP_YES'),	JText::_('HIKASHOP_NO')); ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="data[user][user_partner_email]">
						<?php echo JText::_( 'PARTNER_EMAIL' ); ?>
					</label>
				</td>
				<td>
					<input type="text" name="data[user][user_partner_email]" class="<?php echo HK_FORM_CONTROL_CLASS; ?>" value="<?php echo $this->escape((string)@$this->user->user_partner_email); ?>" />
				</td>
			</tr>
		</table>
	</fieldset>
	<br/>
	<?php
	}
	if(!empty($this->affiliate_terms)){?>
		<span class="hikashop_affiliate_terms" id="hikashop_affiliate_terms">
			<a href="<?php echo JRoute::_('index.php?option=com_content&view=article&id='.$this->affiliate_terms); ?>"><?php echo JText::_('READ_AFFILIATE_TERMS'); ?></a>
		</span>
		<br/><?php
	}
	if(!empty($this->banners)){
		$this->setLayout('banners');
		echo $this->loadTemplate();
	}
	global $Itemid;
	?>
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>"/>
	<input type="hidden" name="cid[]" value="<?php echo @$this->user->user_id; ?>" />
	<input type="hidden" name="option" value="com_hikashop" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="affiliate" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
<div class="clear_both"></div>
