<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikamarket_discount_listing">
<form action="<?php echo hikamarket::completeLink('discount&task=listing'); ?>" method="post" id="adminForm" name="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-md-12">
<?php
	echo $this->loadHkLayout('search', array(
		'id' => 'hikamarket_discount_listing_search',
	));
?>
		<div class="hikam_sort_zone"><?php
			if(!empty($this->ordering_values))
				echo JHTML::_('select.genericlist', $this->ordering_values, 'filter_fullorder', 'onchange="this.form.submit();"', 'value', 'text', $this->full_ordering);
		?></div>
	</div>
</div>
<div class="hk-row-fluid">
	<div class="hkc-md-12">
		<div class="expand-filters" style="width:auto;">
<?php
	$discount_types = array(
		'' => JText::_('HIKA_ALL_DISCOUNTS'),
		'discount' => JText::_('DISCOUNTS'),
		'coupon' => JText::_('COUPONS'),
	);
	echo JHTML::_('select.genericlist', $discount_types, 'filter_type', 'data-search-reset="" onchange="this.form.submit();"', 'value', 'text', $this->pageInfo->filter->type);

	$status_types = array(
		-1 => JText::_('HIKA_ALL_STATUSES'),
		1 => JText::_('HIKA_PUBLISHED'),
		0 => JText::_('HIKA_UNPUBLISHED'),
	);
	echo JHTML::_('select.genericlist', $status_types, 'filter_published', 'data-search-reset="-1" onchange="this.form.submit();"', 'value', 'text', $this->pageInfo->filter->published);

	if(!empty($this->vendorType))
		echo $this->vendorType->display('filter_vendors', @$this->pageInfo->filter->vendors);
?>
		</div>
		<div style="clear:both"></div>
	</div>
</div>

<div id="hikam_discount_main_listing">
<?php
hikamarket::loadJslib('tooltip');
$this->loadRef(array('dropdownHelper' => 'shop.helper.dropdown'));

$now = time();
$publish_content = '<i class="fas fa-check"></i> ' . JText::_('HIKA_PUBLISHED');
$unpublish_content = '<i class="fas fa-times"></i> ' . JText::_('HIKA_UNPUBLISHED');

foreach($this->discounts as $discount) {
	$url = ($this->manage) ? hikamarket::completeLink('discount&task=show&cid='.$discount->discount_id) : null;
	$extra_classes = '';

	if(!empty($discount->discount_code)) {
		$discount_name = $discount->discount_code;
	} else {
		$discount_name = '<em>' . JText::_('HIKA_NONE') . '</em>';
		if(isset($discount->discount_flat_amount) && $discount->discount_flat_amount > 0) {
			$discount_name = '<em>' . $this->currencyClass->displayPrices(array($discount),'discount_flat_amount','discount_currency_id') . '</em>';
		} elseif(isset($discount->discount_percent_amount) && $discount->discount_percent_amount > 0) {
			$discount_name = '<em>' .  $discount->discount_percent_amount. '%</em>';
		}
	}
?>
	<div class="hk-card hk-card-default hk-card-discount<?php echo $extra_classes; ?>" data-hkm-discount="<?php echo (int)$discount->discount_id; ?>">
		<div class="hk-card-header">
			<a class="hk-row-fluid" href="<?php echo $url; ?>">
				<div class="hkc-sm-6 hkm_discount_name"><?php
	if($discount->discount_type == 'coupon') {
		echo '<i class="fas fa-receipt"></i> ';
	} else {
		echo '<i class="fas fa-percent"></i> ';
	}
	echo $discount_name;
				?></div>
				<div class="hkc-sm-6 hkm_discount_value" style="text-align:right"><?php
	if(isset($discount->discount_flat_amount) && $discount->discount_flat_amount > 0) {
		echo $this->currencyClass->displayPrices(array($discount),'discount_flat_amount','discount_currency_id');
	} elseif(isset($discount->discount_percent_amount) && $discount->discount_percent_amount > 0) {
		echo $discount->discount_percent_amount. '%';
	} else {
		echo JText::_('NO_DISCOUNT');
	}

				?></div>
			</a>
		</div>
		<div class="hk-card-body">
			<div class="hk-row-fluid">
				<div class="hkc-sm-7 hkm_discount_restrictions">
					<div class="hkm_discount_quota">
<?php
	if(empty($discount->discount_quota))
		echo '<i class="fas fa-infinity hk-icon-green"></i> <strong>' . JText::_('DISCOUNT_QUOTA').'</strong> '.JText::_('UNLIMITED');
	else
		echo '<span class="hk-label">'.$discount->discount_used_times.' / '.$discount->discount_quota. '</span> <strong>' . JText::_('DISCOUNT_QUOTA').'</strong> '.JText::sprintf('X_LEFT', $discount->discount_quota - $discount->discount_used_times);
?>
					</div>
<?php
	if(!empty($discount->discount_start)) {
		if(!empty($discount->discount_end) && ((int)$discount->discount_start <= $now) && ((int)$discount->discount_start < $now))
			$discount_enabled = 'far fa-calendar-alt';
		else
			$discount_enabled = ((int)$discount->discount_start <= $now) ? 'far fa-calendar-check hk-icon-green' : 'far fa-calendar-times hk-icon-orange';
?>
					<div class="hkm_discount_start">
						<i class="<?php echo $discount_enabled; ?>"></i> <strong><?php echo JText::_('DISCOUNT_START_DATE'); ?></strong>
						<span><?php echo hikamarket::getDate($discount->discount_start, '%Y-%m-%d %H:%M'); ?></span>
					</div>
<?php
	}
?>
<?php
	if(!empty($discount->discount_end)) {
		if(!empty($discount->discount_start) && ((int)$discount->discount_start > $now))
			$discount_enabled = 'far fa-calendar-alt';
		else
			$discount_enabled = ((int)$discount->discount_end > $now) ? 'far fa-calendar-check hk-icon-green' : 'far fa-calendar-times hk-icon-red';
?>
					<div class="hkm_discount_start">
						<i class="<?php echo $discount_enabled; ?>"></i> <strong><?php echo JText::_('DISCOUNT_END_DATE'); ?></strong>
						<span><?php echo hikamarket::getDate($discount->discount_end, '%Y-%m-%d %H:%M'); ?></span>
					</div>
<?php
	}
?>
<?php
	if(hikashop_level(1)) {
		if(!empty($discount->discount_minimum_order) && hikamarket::toFloat($discount->discount_minimum_order) != 0)
			echo '<div class="hkm_discount_minorder"><i class="far fa-money-bill-alt hk-icon-blue"></i> <strong>'.JText::_('MINIMUM_ORDER_VALUE') . '</strong> <span>' . $this->currencyClass->displayPrices(array($discount), 'discount_minimum_order', 'discount_currency_id').'</span></div>';
		if(!empty($discount->product_name))
			echo '<div class="hkm_discount_onproduct"><i class="fas fa-cubes hk-icon-blue"></i> <strong>'.JText::_('PRODUCT') . '</strong> <span>' . $discount->product_name . '<span></div>';
		if(!empty($discount->category_name)) {
			echo '<div class="hkm_discount_oncategory"><i class="fas fa-folder hk-icon-blue"></i> <strong>'.JText::_('CATEGORY') . '</strong> <span>' .
				$discount->category_name .
				(($discount->discount_category_childs) ? (' <em>' . JText::_('INCLUDING_SUB_CATEGORIES') . '</em>') : ''). '</span></div>';
		}
		if(!empty($discount->discount_user_id) && hikashop_level(2))
			echo '<div class="hkm_discount_onuser"><i class="fas fa-user hk-icon-blue"></i> <span>'.JText::_('HIKA_COUPON_TARGET_USERS').'</span></div>'; // 'For specific users'
		if(!empty($discount->zone_name_english))
			echo '<div class="hkm_discount_onzone"><i class="fas fa-map-marker-alt hk-icon-blue"></i> <strong>'.JText::_('ZONE') . '</strong> <span>' . $discount->zone_name_english . '</span></div>';

		if($discount->discount_type == 'coupon') {
			if(!empty($discount->discount_coupon_product_only))
				 echo '<div class="hkm_discount_percentproduct"><i class="far fa-money-bill-alt hk-icon-blue"></i> <span>'.JText::_('HIKA_COUPON_PRODUCT_ONLY').'</span></div>'; // 'Percentage for product only'
		}
	}
?>
				</div>
				<div class="hkc-sm-3">
<?php
	if($this->discount_action_publish) {
?>
					<a class="hikabtn hikabtn-<?php echo ($discount->discount_published) ? 'success' : 'danger'; ?> hkm_publish_button" data-toggle-state="<?php echo $discount->discount_published ? 1 : 0; ?>" data-toggle-id="<?php echo $discount->discount_id; ?>" onclick="return window.localPage.toggleDiscount(this);"><?php
						echo ($discount->discount_published) ? $publish_content : $unpublish_content;
					?></a>
<?php
	} else {
?>
					<span class="hkm_publish_state hk-label hk-label-<?php echo ($discount->discount_published) ? 'green' : 'red'; ?>"><?php echo ($discount->discount_published) ? $publish_content : $unpublish_content; ?></span>
<?php
	}
?>
				</div>
				<div class="hkc-sm-2">
<?php
	$data = array(
		'details' => array(
			'name' => '<i class="fas fa-search"></i> ' . JText::_('HIKA_DETAILS', true),
			'link' => $url
		)
	);
	if($this->discount_action_delete) {
		$data['delete'] = array(
			'name' => '<i class="fas fa-trash"></i> ' . JText::_('HIKA_DELETE', true),
			'link' => '#delete',
			'click' => 'return window.localPage.deleteDiscount('.(int)$discount->discount_id.', \''.urlencode(strip_tags($discount_name)).'\');'
		);
	}
	if(!empty($data)) {
		echo $this->dropdownHelper->display(
			JText::_('HIKA_ACTIONS'),
			$data,
			array('type' => '', 'class' => 'hikabtn-primary', 'right' => true, 'up' => false)
		);
	}
?>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>
	<div class="hikashop_discounts_footer">
		<div class="hikamarket_pagination">
			<?php echo $this->pagination->getListFooter(); ?>
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
	</div>
</div>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="listing" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
<?php if($this->discount_action_publish) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.toggleDiscount = function(el) {
	var w=window, d=document, o=w.Oby,
		state = el.getAttribute('data-toggle-state'),
		id = el.getAttribute('data-toggle-id');
	if(!id) return false;
	var url="<?php echo hikamarket::completeLink('toggle','ajax',true); ?>",
		v = (state == 0) ? 1 : 0,
		data=o.encodeFormData({"task":"discount_published-"+id,"value":v,"table":"discount","<?php echo hikamarket::getFormToken(); ?>":1});
	el.disabled = true;
	if(state == 1) el.innerHTML = "<i class=\"fas fa-spinner fa-pulse\"></i> <?php echo JText::_('HIKA_UNPUBLISHING', true); ?>";
	else el.innerHTML = "<i class=\"fas fa-spinner fa-pulse\"></i> <?php echo JText::_('HIKA_PUBLISHING', true); ?>";
	el.classList.remove("hikabtn-success", "hikabtn-danger");
	o.xRequest(url,{mode:"POST",data:data},function(x,p){
		if(x.responseText && x.responseText == '1')
			state = v;
		el.disabled = false;
		el.setAttribute('data-toggle-state', v);
		if(state == 1) el.innerHTML = "<i class=\"fas fa-check\"></i> <?php echo JText::_('HIKA_PUBLISHED', true); ?>";
		else el.innerHTML = "<i class=\"fas fa-times\"></i> <?php echo JText::_('HIKA_UNPUBLISHED', true); ?>";
		el.classList.add( state ? "hikabtn-success" : "hikabtn-danger" );
	});
};
</script>
<?php } ?>
<?php if($this->discount_action_delete) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.deleteDiscount = function(id, name) {
	var confirmMsg = "<?php echo JText::_('CONFIRM_DELETE_DISCOUNT_X'); ?>";
	if(!confirm(confirmMsg.replace('{DISCOUNT}', decodeURI(name))))
		return false;
	var f = document.forms['hikamarket_delete_discount_form'];
	if(!f) return false;
	f.discount_id.value = id;
	f.submit();
	return false;
};
</script>
<form action="<?php echo hikamarket::completeLink('discount&task=delete'); ?>" method="post" name="hikamarket_delete_discount_form" id="hikamarket_delete_discount_form">
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="delete" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="discount_id" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php }
