<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_order_listing">
<?php
	echo $this->toolbarHelper->process($this->toolbar, $this->title);
?>
<form action="<?php echo hikashop_completeLink('order'); ?>" method="post" name="adminForm" id="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-md-6">
		<div class="hikashop_search_block <?php echo HK_GROUP_CLASS; ?>">
			<input type="text" name="search" id="hikashop_search" value="<?php echo $this->escape($this->pageInfo->search);?>" placeholder="<?php echo JText::_('HIKA_SEARCH'); ?>" class="<?php echo HK_FORM_CONTROL_CLASS; ?>" onchange="this.form.submit();" />
			<button class="<?php echo HK_CSS_BUTTON; ?> <?php echo HK_CSS_BUTTON_PRIMARY; ?>" onclick="this.form.submit();"><?php echo JText::_('GO'); ?></button>
<?php
	foreach($this->leftFilters as $name => $filterObj) {
		if(is_string($filterObj))
			echo $filterObj;
		else
			echo $filterObj->displayFilter($name, $this->pageInfo->filter);
	}
?>		</div>
	</div>
	<div class="hkc-md-6">
		<div class="hikashop_order_sort"><?php
	foreach($this->rightFilters as $name => $filterObj) {
		if(is_string($filterObj))
			echo $filterObj;
		else
			echo $filterObj->displayFilter($name, $this->pageInfo->filter);
	}
?>
		</div>
	</div>
</div>

<div class="hikashop_order_listing">
	<div class="hikashop_orders_content">
<?php

	if(empty($this->rows)) {
?>
		<div class="hk-well hika_no_orders">
			<p><?php echo JText::_('HIKA_CPANEL_NO_ORDERS'); ?></p>
		</div>
<?php
	} else {
		$url_itemid = (!empty($this->Itemid) ? '&Itemid=' . $this->Itemid : '');
		$cancel_orders = false;
		$print_invoice = false;
		$cancel_url = '&cancel_url='.base64_encode(hikashop_currentURL());

		$i = 0;
		$k = 0;
		foreach($this->rows as &$row) {
			$order_link = hikashop_completeLink('order&task=show&cid='.$row->order_id.$url_itemid.$cancel_url);
?>
		<div class="hk-card hk-card-default hk-card-order" data-order-container="<?php echo (int)$row->order_id; ?>">
			<div class="hk-card-header">
				<a class="hk-row-fluid" href="<?php echo $order_link; ?>">

					<div class="hkc-sm-6 hika_cpanel_date">
<!-- ORDER DATE -->
						<i class="fa fa-clock"></i>
						<?php echo hikashop_getDate((int)$row->order_created, '%d %B %Y %H:%M'); ?>
<!-- EO ORDER DATE -->
					</div>
					<div class="hkc-sm-6 hika_cpanel_price">
<!-- ORDER TOTAL -->
						<i class="fa fa-credit-card"></i>
						<?php echo $this->currencyClass->format($row->order_full_price, $row->order_currency_id); ?>
<!-- EO ORDER TOTAL -->
					</div>
				</a>
			</div>
<!-- END GRID -->
			<div class="hk-card-body">
				<div class="hk-row-fluid">
					<div class="hkc-sm-4 hika_order_left_div">
<!-- TOP LEFT EXTRA DATA -->
<?php if(!empty($row->extraData->topLeft)) { echo implode("\r\n", $row->extraData->topLeft); } ?>
<!-- EO TOP LEFT EXTRA DATA -->
<!-- ORDER NUMBER -->
						<a class="hika_order_number" href="<?php echo $order_link; ?>">
							<span class="hika_order_number_title"><?php echo  JText::_('ORDER_NUMBER'); ?> : </span>
							<span class="hika_order_number_value"><?php echo $row->order_number; ?></span>
<?php if(!empty($row->order_invoice_number)) { ?>
							<br class="hika_order_number_invoice_separator"/>
							<span class="hika_invoice_number_title"><?php echo JText::_('INVOICE_NUMBER'); ?> : </span>
							<span class="hika_invoice_number_value"><?php echo $row->order_invoice_number; ?></span>
<?php } ?>
						</a>
<!-- EO ORDER NUMBER -->
<!-- BOTTOM LEFT EXTRA DATA -->
<?php if(!empty($row->extraData->bottomLeft)) { echo implode("\r\n", $row->extraData->bottomLeft); } ?>
<!-- EO BOTTOM LEFT EXTRA DATA -->
					</div>
					<div class="hkc-sm-3 hika_order_info">
<!-- BEFORE INFO EXTRA DATA -->
<?php if(!empty($row->extraData->beforeInfo)) { echo implode("\r\n", $row->extraData->beforeInfo); } ?>
<!-- EO BEFORE INFO EXTRA DATA -->
<!-- SHIPPING ADDRESS -->
<?php if(!empty($row->order_shipping_address_id) && !empty($this->address_data[(int)$row->order_shipping_address_id])) { ?>
						<div class="hika_order_shipping_address" data-toggle="hk-tooltip" data-title="<?php echo $this->escape($this->address_html[(int)$row->order_shipping_address_id]); ?>">
							<div class="hika_order_shipping_address_title"><?php echo JText::_('HIKA_LISTING_ORDER_SHIP'); ?></div>
							<span class="hika_order_shipping_address_value">
								<i class="fas fa-map-marker-alt"></i>
								<?php echo $this->address_data[(int)$row->order_shipping_address_id]->address_firstname . ' ' . $this->address_data[(int)$row->order_shipping_address_id]->address_lastname; ?>
							</span>
						</div>
<?php } ?>
<!-- EO SHIPPING ADDRESS -->
<!-- AFTER INFO EXTRA DATA -->
<?php if(!empty($row->extraData->afterInfo)) { echo implode("\r\n", $row->extraData->afterInfo); } ?>
<!-- EO AFTER INFO EXTRA DATA -->
					</div>
					<div class="hkc-sm-2 hika_order_status">
<!-- TOP MIDDLE EXTRA DATA -->
<?php if(!empty($row->extraData->topMiddle)) { echo implode("\r\n", $row->extraData->topMiddle); } ?>
<!-- EO TOP MIDDLE EXTRA DATA -->
<!-- ORDER STATUS -->
						<span class="order-label order-label-<?php echo preg_replace('#[^a-z_0-9]#i', '_', str_replace(' ','_', $row->order_status)); ?>"><?php
							echo hikashop_orderStatus($row->order_status);
						?></span>
<!-- EO ORDER STATUS -->
<!-- BOTTOM MIDDLE EXTRA DATA -->
<?php if(!empty($row->extraData->bottomMiddle)) { echo implode("\r\n", $row->extraData->bottomMiddle); } ?>
<!-- EO BOTTOM MIDDLE EXTRA DATA -->
					</div>
					<div class="hkc-sm-2 hika_order_action">
<!-- TOP RIGHT EXTRA DATA -->
<?php if(!empty($row->extraData->topRight)) { echo implode("\r\n", $row->extraData->topRight); } ?>
<!-- EO TOP RIGHT EXTRA DATA -->
<!-- ACTIONS BUTTON -->
<?php
			$dropData = array();
			$dropData[] = array(
				'name' => '<i class="fas fa-search-plus"></i>'.JText::_('HIKA_DETAILS'),
				'link' => $order_link
			);

			if(!empty($row->show_print_button)) {
				$print_invoice = true;
				$dropData[] = array(
					'name' => '<i class="fas fa-print"></i> '. JText::_('PRINT_INVOICE'),
					'link' => '#print_invoice',
					'click' => 'return window.localPage.printInvoice('.(int)$row->order_id.');',
				);
			}
			if(!empty($row->show_cancel_button)) {
				$cancel_orders = true;
				$dropData[] = array(
					'name' => '<i class="fas fa-ban"></i> '. JText::_('CANCEL_ORDER'),
					'link' => '#cancel_order',
					'click' => 'return window.localPage.cancelOrder('.(int)$row->order_id.',\''.$row->order_number.'\');',
				);
			}
			if(!empty($row->show_payment_button) && bccomp(sprintf('%F',$row->order_full_price), 0, 5) > 0) {
				$url_param = ($this->payment_change) ? '&select_payment=1' : '';
				$url = hikashop_completeLink('order&task=pay&order_id='.$row->order_id.$url_param.$url_itemid);
				if($this->config->get('force_ssl',0) && strpos('https://',$url) === false)
					$url = str_replace('http://','https://', $url);
				$dropData[] = array(
					'name' => '<i class="fas fa-money-bill-alt"></i> '. JText::_('PAY_NOW'),
					'link' => $url
				);
			}
			if($this->config->get('allow_reorder', 0)) {
				$url = hikashop_completeLink('order&task=reorder&order_id='.$row->order_id.$url_itemid);
				if($this->config->get('force_ssl',0) && strpos('https://',$url) === false)
					$url = str_replace('http://','https://', $url);
				$dropData[] = array(
					'name' => '<i class="fas fa-redo-alt"></i> '. JText::_('REORDER'),
					'link' => $url
				);
			}
			if(!empty($row->show_contact_button)) {
				$url = hikashop_completeLink('order&task=contact&order_id='.$row->order_id.$url_itemid);
				$dropData[] = array(
					'name' => '<i class="far fa-envelope"></i> '. JText::_('CONTACT_US_ABOUT_YOUR_ORDER'),
					'link' => $url
				);
			}

			if(!empty($row->actions)) {
				$dropData = array_merge($dropData, $row->actions);
			}

			if(!empty($dropData)) {
				echo $this->dropdownHelper->display(
					JText::_('HIKASHOP_ACTIONS'),
					$dropData,
					array('type' => 'btn',  'right' => true, 'up' => false)
				);
			}
?>
<!-- EO ACTIONS BUTTON -->
<!-- BOTTOM RIGHT EXTRA DATA -->
<?php if(!empty($row->extraData->bottomRight)) { echo implode("\r\n", $row->extraData->bottomRight); } ?>
<!-- EO BOTTOM RIGHT EXTRA DATA -->
					</div>
					<div class="hkc-sm-1 hika_order_more">
<!-- PRODUCTS LISTING BUTTON -->
<?php if($row->order_id == $this->row->order_id) { ?>
						<a class="hikabtn hikabtn-default " data-toggle="hk-tooltip" data-title="<?php echo $this->escape(JText::_('HIDE_PRODUCTS')); ?>" href="#" onclick="return window.localPage.handleDetails(this, <?php echo (int)$row->order_id; ?>);"><i class="fas fa-angle-up"></i></a>
<?php } else { ?>
						<a class="hikabtn hikabtn-default" data-toggle="hk-tooltip" data-title="<?php echo $this->escape(JText::_('DISPLAY_PRODUCTS')); ?>" href="#" onclick="return window.localPage.handleDetails(this, <?php echo (int)$row->order_id; ?>);"><i class="fas fa-angle-down"></i></a>
<?php } ?>
<!-- EO PRODUCTS LISTING BUTTON -->
					</div>
				</div>
			</div>
<!-- END GRID -->
<?php
			if($row->order_id == $this->row->order_id) {
				$this->setLayout('order_products');
				echo $this->loadTemplate();
			}
?>
		</div>
<?php
			$i++;
			$k = 1 - $k;
		}
		unset($row);
?>
<!-- PAGINATION -->
		<div class="hikashop_orders_footer">
			<div class="pagination">
				<?php $this->pagination->form = '_bottom'; echo $this->pagination->getListFooter(); ?>
				<?php echo '<span class="hikashop_results_counter">'.$this->pagination->getResultsCounter().'</span>'; ?>
			</div>
		</div>
<!-- EO PAGINATION -->
<?php } ?>
	</div>

	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="listing" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</div>
</form>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.handleDetails = function(btn, id) {
	var d = document, details = d.getElementById('hika_order_'+id+'_details');

	if(details) {
		details.style.display = (details.style.display == 'none' ? '' : 'none');
		if(details.style.display) {
			btn.innerHTML = '<i class="fas fa-angle-down"></i>';
			btn.setAttribute('data-original-title','<?php echo $this->escape(JText::_('DISPLAY_PRODUCTS')); ?>');
		} else{
			btn.innerHTML = '<i class="fas fa-angle-up"></i>';
			btn.setAttribute('data-original-title','<?php echo $this->escape(JText::_('HIDE_PRODUCTS')); ?>');
		}
		return false;
	}

	return window.localPage.loadOrderDetails(btn, id);
};
window.localPage.loadOrderDetails = function(btn, id) {
	var d = document, o = window.Oby, el = d.querySelector('[data-order-container="'+id+'"]');
	if(!el) return false;
	btn.classList.add('hikadisabled');
	btn.disabled = true;
	btn.blur();
	btn.innerHTML = '<i class="fas fa-spinner fa-pulse"></i>';
	var c = d.createElement('div');
	o.xRequest("<?php echo hikashop_completeLink('order&task=order_products', 'ajax', false, true); ?>", {mode:'POST',data:'cid='+id},function(xhr){
		if(!xhr.responseText || xhr.status != 200) {
			btn.innerHTML = '<i class="fas fa-angle-down"></i>';
			return;
		}
		btn.classList.remove('hikadisabled');
		btn.disabled = false;
		var resp = o.trim(xhr.responseText);
		c.innerHTML = resp;
		el.appendChild(c.querySelector('#hika_order_'+id+'_details'));
		btn.innerHTML = '<i class="fas fa-angle-up"></i>';
		btn.setAttribute('data-original-title','<?php echo $this->escape(JText::_('HIDE_PRODUCTS')); ?>');
	});
	return false;
};
</script>
<?php

if(!empty($this->rows) && ($print_invoice || $cancel_orders)) {
	echo $this->popupHelper->display(
		'',
		'INVOICE',
		hikashop_completeLink('order&task=invoice'.$url_itemid,true),
		'hikashop_print_popup',
		760, 480, '', '', 'link'
	);
?>
<script>
if(!window.localPage) window.localPage = {};
window.localPage.cancelOrder = function(id, number) {
	var d = document, form = d.getElementById('hikashop_cancel_order_form');
	if(!form || !form.elements['order_id']) {
		console.log('Error: Form not found, cannot cancel the order');
		return false;
	}
	if(!confirm('<?php echo JText::_('HIKA_CONFIRM_CANCEL_ORDER', true); ?>'.replace(/ORDER_NUMBER/, number)))
		return false;
	form.elements['order_id'].value = id;
	form.submit();
	return false;
};
window.localPage.printInvoice = function(id) {
	hikashop.openBox('hikashop_print_popup','<?php
		$u = hikashop_completeLink('order&task=invoice'.$url_itemid,true);
		echo $u;
		echo (strpos($u, '?') === false) ? '?' : '&';
	?>order_id='+id);
	return false;
};
</script>
<form action="<?php echo hikashop_completeLink('order&task=cancel_order&email=1'); ?>" name="hikashop_cancel_order_form" id="hikashop_cancel_order_form" method="POST">
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="cancel_order" />
	<input type="hidden" name="email" value="1" />
	<input type="hidden" name="order_id" value="" />
	<input type="hidden" name="ctrl" value="order" />
	<input type="hidden" name="redirect_url" value="<?php echo hikashop_currentURL(); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php
}
?>
</div>
