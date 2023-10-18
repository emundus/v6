<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hkc-lg-6 hikashop_tile_block hikashop_discount_edit_attributes"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('HIKA_ADVANCED_RESTRICTIONS'); ?></div>
	<dl class="hika_options large">

		<dt data-discount-display="coupon">
			<label for="discount_minimum_order"><?php
				echo JText::_('HIKA_MIN_COUPON');
			?></label>
		</dt>
		<dd class="hikashop_discount_value_order" data-discount-display="coupon">
			<input type="text" id="discount_minimum_order" name="data[discount][discount_minimum_order]" value="<?php echo (int)@$this->element->discount_minimum_order; ?>" />
			<label for="discount_maximum_order" style="font-weight:bold"><?php echo JText::_('HIKA_QTY_RANGE_TO'); ?></label>
			<div class="input-append">
				<input type="text" id="discount_maximum_order" name="data[discount][discount_maximum_order]"  value="<?php echo (int)@$this->element->discount_maximum_order; ?>" />
			</div>
		</dd>
		<dt data-discount-display="coupon">
			<label for="discount_minimum_products"><?php
				echo JText::_('HIKA_MAX_COUPON');
			?></label>
		</dt>
		<dd class="hikashop_discount_quantity_products" data-discount-display="coupon">
			<input type="text" id="discount_minimum_products" name="data[discount][discount_minimum_products]" value="<?php echo (int)@$this->element->discount_minimum_products; ?>" />
			<label for="discount_maximum_products" style="font-weight:bold"><?php echo JText::_('HIKA_QTY_RANGE_TO'); ?></label>
			<div class="input-append">
				<input type="text" id="discount_maximum_products" name="data[discount][discount_maximum_products]" value="<?php echo (int)@$this->element->discount_maximum_products; ?>" />
			</div>
		</dd>

		<dt><label><?php
			echo JText::_('PRODUCT');
		?></label></dt>
		<dd><?php
	echo $this->nameboxType->display(
		'data[discount][discount_product_id]',
		explode(',', trim((string)@$this->element->discount_product_id, ',')),
		hikashopNameboxType::NAMEBOX_MULTIPLE,
		'product',
		array(
			'delete' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			'variants' => 2,
			'url_params' => array('VARIANTS' => 2)
		)
	);
		?></dd>

		<dt><label><?php
			echo JText::_('CATEGORY');
		?></label></dt>
		<dd><?php
	echo $this->nameboxType->display(
		'data[discount][discount_category_id]',
		explode(',', trim((string)@$this->element->discount_category_id, ',')),
		hikashopNameboxType::NAMEBOX_MULTIPLE,
		'category',
		array(
			'delete' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		)
	);
		?></dd>
		<dt><label><?php
			echo JText::_('INCLUDING_SUB_CATEGORIES');
		?></label></dt>
		<dd><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[discount][discount_category_childs]', '', @$this->element->discount_category_childs);
		?></dd>

		<dt><label><?php
			echo JText::_('ZONE');
		?></label></dt>
		<dd><?php
	echo $this->nameboxType->display(
		'data[discount][discount_zone_id]',
		explode(',', trim((string)@$this->element->discount_zone_id, ',')),
		hikashopNameboxType::NAMEBOX_MULTIPLE,
		'zone',
		array(
			'delete' => true,
			'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
		)
	);
		?></dd>

<?php
	if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')) {
		$db = JFactory::getDBO();
		$db->setQuery('SHOW CREATE table ' . $db->quoteName( hikashop_table('discount')));
		$discount_descr = $db->loadObject();
		if( !empty( $discount_descr->View)) {
			if ( empty( $this->element->discount_site_id) || $this->element->discount_site_id == '[unselected]') {
				$this->element->discount_site_id = defined( 'MULTISITES_ID') ? MULTISITES_ID : null;
?>
						<tr style="display:none">
								<td colspan="2">
									<input type="hidden" name="data[discount][discount_site_id]" value="<?php echo @$this->element->discount_site_id; ?>" />
								</td>
						</tr>
<?php
				}
		}
		else {
			include_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');
			if ( class_exists( 'MultisitesHelperUtils') && method_exists( 'MultisitesHelperUtils', 'getComboSiteIDs')) {
				$comboSiteIDs = MultisitesHelperUtils::getComboSiteIDs( @$this->element->discount_site_id, 'data[discount][discount_site_id]', JText::_( 'SELECT_A_SITE'));
				if( !empty( $comboSiteIDs)){
?>
						<tr>
							<td class="key">
								 <?php echo JText::_( 'SITE_ID' ); ?>
							</td>
							<td>
								<?php echo $comboSiteIDs; ?>
							</td>
						</tr>
<?php
				}
			}
		}
	}
?>
		<dt><label><?php
			echo JText::_('USERS');
		?></label></dt>
		<dd><?php
			if(hikashop_level(2)){
				echo $this->nameboxType->display(
					'data[discount][discount_user_id]',
					explode(',', trim((string)@$this->element->discount_user_id, ',')),
					hikashopNameboxType::NAMEBOX_MULTIPLE,
					'user',
					array(
						'delete' => true,
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					)
				);
			}else{
				echo hikashop_getUpgradeLink('business');
			}

		?></dd>
		<dt><label><?php
			echo JText::_('ACCESS_LEVEL');
		?></label></dt>
		<dd><?php
			if(hikashop_level(2)){
				$acltype = hikashop_get('type.acl');
				echo $acltype->display('discount_access', @$this->element->discount_access);
			}else{
				echo hikashop_getUpgradeLink('business');
			}
		?></dd>

	</dl>
</div></div>

<div class="hkc-lg-6 hikashop_tile_block hikashop_discount_edit_attributes"><div>
	<div class="hikashop_tile_title"><?php echo JText::_('HIKA_DISCOUNT_FEATURES'); ?></div>
	<dl class="hika_options large">

		<dt data-discount-display="coupon"><label><?php
			echo JText::_('COUPON_AUTO_LOAD');
		?></label></dt>
		<dd data-discount-display="coupon"><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[discount][discount_auto_load]', '', @$this->element->discount_auto_load);
		?></dd>

		<dt data-discount-display="coupon"><label><?php
			echo JText::_('COUPON_APPLIES_TO_PRODUCT_ONLY');
		?></label></dt>
		<dd data-discount-display="coupon"><?php
			echo JHTML::_('hikaselect.booleanlist', 'data[discount][discount_coupon_product_only]', '', @$this->element->discount_coupon_product_only);
		?></dd>

		<dt data-discount-display="coupon"><label><?php
			echo JText::_('COUPON_HANDLING_OF_DISCOUNTED_PRODUCTS');
		?></label></dt>
		<dd data-discount-display="coupon"><?php
			$values = array(
				JHTML::_('select.option', 0, JText::_('STANDARD_BEHAVIOR')),
				JHTML::_('select.option', 1, JText::_('IGNORE_DISCOUNTED_PRODUCTS')),
				JHTML::_('select.option', 2, JText::_('OVERRIDE_DISCOUNTED_PRODUCTS')),
			);
			echo JHTML::_('hikaselect.genericlist', $values, 'data[discount][discount_coupon_nodoubling]', 'class="custom-select"', 'value', 'text', @$this->element->discount_coupon_nodoubling);
		?></dd>

		<dt><label for="discount_quota"><?php
			echo JText::_('DISCOUNT_QUOTA');
		?></label></dt>
		<dd>
			<input type="text" name="data[discount][discount_quota]" id="discount_quota" class="inputbox" value="<?php echo (int)@$this->element->discount_quota; ?>" />
		</dd>

		<dt data-discount-display="coupon"><label for="discount_quota_per_user"><?php
			echo JText::_('DISCOUNT_QUOTA_PER_USER');
		?></label></dt>
		<dd data-discount-display="coupon">
			<input type="text" name="data[discount][discount_quota_per_user]" id="discount_quota_per_user" class="inputbox" value="<?php echo (int)@$this->element->discount_quota_per_user; ?>" />
		</dd>

	</dl>
</div></div>
