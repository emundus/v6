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
<form action="<?php echo hikashop_completeLink('category'); ?>" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
<div id="page-category" class="hk-row-fluid hikashop_backend_tile_edition">
	<div class="hkc-md-6">
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
					<?php
						$this->category_name_input = "data[category][category_name]";
						$this->category_meta_description_input = "data[category][category_meta_description]";
						$this->category_keywords_input = "data[category][category_keywords]";
						$this->category_page_title_input = "data[category][category_page_title]";
						$this->category_alias_input = "data[category][category_alias]";
						$this->category_canonical_input = "data[category][category_canonical]";
						if($this->translation && !empty($this->element->category_id)){
							echo '<div class="hikashop_multilang_buttons" id="hikashop_multilang_buttons">';
							$popupHelper = hikashop_get('helper.popup');
							foreach($this->element->translations as $language_id => $translation){
								echo $popupHelper->display(
									'<div class="hikashop_multilang_button hikashop_language_'.$language_id.'">'.$this->transHelper->getFlag($language_id).'</div>',
									$this->transHelper->getFlag($language_id),
									'\''."index.php?option=com_hikashop&ctrl=category&task=edit_translation&category_id=".@$this->element->category_id."&language_id=".$language_id.'&tmpl=component\'',
									'hikashop_edit_'.$language_id.'_translations',
									(int)$this->config->get('multi_language_edit_x', 760),(int)$this->config->get('multi_language_edit_y', 480), '', '', 'link',true
								);
							}
							echo '</div>';

						}
						$this->setLayout('normal');
						echo $this->loadTemplate();
					?>
			</div>
		</div>
	</div>
	<div class="hkc-md-6">
<?php if($this->category_image){ ?>
		<div class="hikashop_tile_block"><div style="min-height:auto;">
			<div class="hikashop_tile_title"><?php echo JText::_('HIKA_IMAGE'); ?></div>
<?php
			$this->setLayout('form');
			echo $this->loadTemplate('image');
?>
			</div>
		</div>
<?php } ?>
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('CATEGORY_ADDITIONAL_INFORMATION'); ?></div>
					<table class="admintable table" style="">
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('hikaselect.booleanlist', "data[category][category_published]" , '',@$this->element->category_published	); ?>
							</td>
						</tr>
<?php
	if((isset($this->element->category_type) && $this->element->category_type == 'status') || (isset($this->element->category_namekey) && in_array($this->element->category_namekey, array('root','product','tax','status','created','confirmed','cancelled','refunded','shipped','manufacturer')))) {
?>
						<tr style="display:none;">
							<td></td>
							<td><input type="hidden" name="data[category][category_parent_id]" value="<?php echo @$this->element->category_parent_id; ?>" /></td>
						</tr>
<?php
	} else {
		switch(@$this->element->category_type){
			case 'tax':
				$type = 'tax_category';
				break;
			case 'manufacturer':
				$type = 'category';
				break;
			case 'status':
				$type = 'order_status';
				break;
			default:
				$type = 'category';
				break;
		}
?>
						<tr id="category_parent">
							<td class="key"><?php
								echo JText::_('CATEGORY_PARENT');
							?></td>
							<td><?php
		echo $this->nameboxType->display(
			'data[category][category_parent_id]',
			@$this->element->category_parent_id,
			hikashopNameboxType::NAMEBOX_SINGLE,
			$type,
			array(
				'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
			)
		);
							?></td>
						</tr>
<?php
	}
?>
<?php
	if(empty($this->element->category_type) || $this->element->category_type=='product') {
?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'LAYOUT_ON_PRODUCT_PAGE' ); ?>
							</td>
							<td>
								<?php echo $this->productDisplayType->display('data[category][category_layout]' , @$this->element->category_layout); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'QUANTITY_LAYOUT_ON_PRODUCT_PAGE' ); ?>
							</td>
							<td>
								<?php echo $this->quantityDisplayType->display('data[category][category_quantity_layout]' , @$this->element->category_quantity_layout); ?>
							</td>
						</tr>
<?php
	}
	if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php')){
		include_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'utils.php');
		if ( class_exists( 'MultisitesHelperUtils') && method_exists( 'MultisitesHelperUtils', 'getComboSiteIDs')) {
			$comboSiteIDs = MultisitesHelperUtils::getComboSiteIDs( @$this->element->category_site_id, 'data[category][category_site_id]', JText::_( 'SELECT_A_SITE'));
			if( !empty( $comboSiteIDs) && (empty($this->element->category_parent_id) || $this->element->category_parent_id!=1) && (empty($this->element->category_type) || in_array($this->element->category_type,array('product','brand','manufacturer')))){
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
	$after = array();
	if(!empty($this->fields)) {
		foreach($this->fields as $fieldName => $oneExtraField) {
			if($oneExtraField->field_backend) {
				$onWhat='onchange';
				if($oneExtraField->field_type=='radio')
					$onWhat='onclick';
				$html = $this->fieldsClass->display($oneExtraField,$this->element->$fieldName,'data[category]['.$fieldName.']',false,' '.$onWhat.'="window.hikashop.toggleField(this.value,\''.$fieldName.'\',\'category\',0);"');
				if($oneExtraField->field_type=='hidden') {
					$after[] = $html;
					continue;
?>
								<tr><td><input type="hidden" name="data[category][<?php echo $fieldName; ?>]" value="<?php echo $this->element->$fieldName; ?>" /></td></tr>
				<?php }else{ ?>
								<tr id='hikashop_category_<?php echo $fieldName; ?>'>
									<td class="key">
										<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
									</td>
									<td>
										<?php echo $html; ?>
									</td>
								</tr>
<?php
				}
			}
		}
	}

	if(!empty($this->extra_blocks['category'])) {
		foreach($this->extra_blocks['category'] as $r) {
			if(is_string($r))
				echo $r;
			if(is_object($r)) $r = (array)$r;
			if(is_array($r)) {
				if(!isset($r['name']) && isset($r[0]))
					$r['name'] = $r[0];
				if(!isset($r['value']) && isset($r[1]))
					$r['value'] = $r[1];
?>
								<tr>
									<td class="key"><?php echo JText::_(@$r['name']); ?></td>
									<td><?php echo @$r['value']; ?></td>
								</tr>
<?php
			}
		}
	}
?>
					</table>
<?php
	if(count($after)) {
		echo implode("\r\n", $after);
	}
?>
			</div>
		</div>
		<div class="hikashop_tile_block"><div style="min-height:auto;">
			<div class="hikashop_tile_title"><?php echo JText::_('ACCESS_LEVEL'); ?></div>
<?php
	if(hikashop_level(2)) {
		$acltype = hikashop_get('type.acl');
		echo $acltype->display('category_access',@$this->element->category_access,'category');
	} else {
		echo '<small style="color:red">'.JText::_('ONLY_FROM_HIKASHOP_BUSINESS').'</small>';
	}
?>
			</div>
		</div>
	</div>
</div>
	<div style="clear:both" class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->category_id; ?>" />
	<input type="hidden" name="data[category][category_id]" value="<?php echo @$this->element->category_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="category" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
