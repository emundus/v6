<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<?php
	if(isset($_COOKIE['product_exploreWidth_cookie']))
		$cookie_value = $_COOKIE['product_exploreWidth_cookie'];
	else
		$cookie_value = 'explorer_open';
?>
<?php if($this->config->get('category_explorer')){ ?>
<div id="page-product" class="hk-row-fluid">
	<div id ="hikashop_category_explorer_container" class="hkc-md-2 <?php echo $cookie_value; ?>"><?php
		echo hikashop_setExplorer('product&task=listing',$this->pageInfo->filter->filter_id,false,'product');
	?></div>
	<div class="hkc-md-10">
<?php } ?>
			<form action="<?php echo hikashop_completeLink('product'); ?>" method="POST" name="adminForm" id="adminForm">
				<div class="hk-row-fluid">
					<div class="hkc-md-4 hika_j4_search">
<?php
	if(!empty( $this->extrafilters)) {
		foreach($this->extrafilters as $name => $filterObj) {
			if(is_string($filterObj)) {
				echo $filterObj;
			} elseif( isset($filterObj->objSearch) && method_exists($filterObj->objSearch, 'displayFilter')) {
				echo $filterObj->objSearch->displayFilter($name, $this->pageInfo->filter);
			} elseif( isset($filterObj->filter_html_search)) {
				echo $filterObj->filter_html_search;
			}
		}
	}
?>
<?php if(!$this->config->get('category_explorer')){ ?>
							<a href="<?php echo hikashop_completeLink('product&task=listing&filter_id=0'); ?>"><?php echo JText::_( 'ROOT' ); ?>/</a>
							<?php echo $this->breadCrumb; ?><br/>
<?php }
	echo $this->loadHkLayout('search', array());
?>
				</div>
				<div id="hikashop_listing_filters_id" class="hkc-md-7 hikashop_listing_filters <?php echo $this->openfeatures_class; ?>">
<?php
	if ( !empty( $this->extrafilters)) {
		foreach($this->extrafilters as $name => $filterObj) {
			if(is_string($filterObj)){
				echo $filterObj;
			}elseif(isset( $filterObj->objDropdown) && method_exists($filterObj->objDropdown,'displayFilter')){
				echo $filterObj->objDropdown->displayFilter($name, $this->pageInfo->filter);
			}else if ( isset( $filterObj->filter_html_dropdown)){
				echo $filterObj->filter_html_dropdown;
			}
		}
	}
?>
							<?php echo $this->productWeightType->display('filter_product_weight',$this->pageInfo->filter->filter_product_weight); ?>
							<?php echo $this->manufacturerDisplay; ?>
							<?php echo $this->publishDisplay; ?>
							<?php echo $this->productType->display('filter_product_type',$this->pageInfo->filter->filter_product_type); ?>
							<?php echo $this->childDisplay; ?>
						</div>
						<div style="clear:both"></div>
				</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
				<table id="hikashop_product_listing" class="adminlist table table-striped table-hover" cellpadding="1">
					<thead>
						<tr>
							<th class="title titlenum"><?php
								echo JText::_( 'HIKA_NUM' );
							?></th>
							<th class="title titlebox">
								<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
							</th>
							<th class="title"><?php
								echo JText::_( 'HIKA_IMAGE' );
							?></th>
							<th class="title"><?php
								echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'b.product_name', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?> / <?php echo JHTML::_('grid.sort', JText::_('PRODUCT_CODE'), 'b.product_code', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
							?></th>
							<th class="title"><?php
								echo JHTML::_('grid.sort', JText::_('PRODUCT_PRICE'), 'b.product_sort_price', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
							?></th>
							<th class="title"><?php
								echo JHTML::_('grid.sort', JText::_('PRODUCT_QUANTITY'), 'b.product_quantity', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
							?></th>
							<th class="title default"  data-alias="length"><?php
								echo JHTML::_('grid.sort', JText::_('PRODUCT_LENGTH'), 'b.product_length', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
							?></th>
								<th class="title default"  data-alias="width"><?php
								echo JHTML::_('grid.sort', JText::_('PRODUCT_WIDTH'), 'b.product_width', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
							?></th>
							<th class="title default" data-alias="height"><?php
								echo JHTML::_('grid.sort', JText::_('PRODUCT_HEIGHT'), 'b.product_height', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
							?></th>
							<th class="title default" data-alias="weight"><?php
								echo JHTML::_('grid.sort', JText::_('PRODUCT_WEIGHT'), 'b.product_weight', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
							?></th>
<?php
	$count_extrafields = 9;
	if(!empty($this->fields)){
		foreach($this->fields as $field){
			if($field->field_type == 'customtext') continue;
			echo '<th class="title custom_field" data-alias="'.$field->field_realname.'">'.
				JHTML::_('grid.sort', $this->fieldsClass->trans($field->field_realname), 'b.'.$field->field_namekey, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ).
			'</th>';
		}
		$count_extrafields += count($this->fields);
	}

	if(!empty($this->extrafields)) {
		foreach($this->extrafields as $namekey => $extrafield) {
			echo '<th class="hikashop_product_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
		}
		$count_extrafields += count($this->extrafields);
	}
?>
							<th class="title titleorder"><?php
								if($this->doOrdering) {
									if ($this->order->ordering) {
										$keys = array_keys($this->rows);
										$rows_nb = end($keys);
										$href = "javascript:saveorder(".$rows_nb.", 'saveorder')";
										?><a href="<?php echo $href; ?>" rel="tooltip" class="saveorder btn btn-sm btn-secondary float-end" title="Save Order">
											<button class="button-apply btn btn-success" type="button">
<!--											<span class="icon-apply" aria-hidden="true"></span> -->
												<i class="fas fa-save"></i>
											</button>
										</a><?php
									}
									echo JHTML::_('grid.sort', JText::_( 'HIKA_ORDER' ), 'a.ordering',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
								} else {
									?><a href="#" title="<?php echo $this->noOrderingMessage; ?>"><?php echo JText::_( 'HIKA_ORDER' ); ?></a><?php
								}
							?></th>
							<th class="title titletoggle"><?php
								echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'b.product_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
							?></th>
							<th class="title"><?php
								echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'b.product_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value );
							?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="<?php echo $count_extrafields; ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
<?php
	$k = 0;
	for($i = 0,$a = count($this->rows);$i<$a;$i++){
		$row =& $this->rows[$i];
		$publishedid = 'product_published-'.$row->product_id;
?>
						<tr class="row<?php echo $k; ?>">
							<td class="hk_center"><?php
								echo $this->pagination->getRowOffset($i);
							?></td>
							<td class="hk_center"><?php
								echo JHTML::_('grid.id', $i, $row->product_id );
							?></td>
							<td><?php
								$image_options = array('default' => true,'forcesize'=>$this->config->get('image_force_size',true),'scale'=>$this->config->get('image_scale_mode','inside'));
								$img = $this->image->getThumbnail(@$row->file_path, array('width' => 50, 'height' => 50), $image_options);
								if($img->success) {
									$attributes = '';
									if($img->external)
										$attributes = ' width="'.$img->req_width.'" height="'.$img->req_height.'"';
									echo '<img class="hikashop_product_image" title="'.$this->escape(@$row->file_description).'" alt="'.$this->escape(@$row->file_name).'" src="'.$img->url.'"'.$attributes.'/>';
								}
							?></td>
							<td>
								<?php if($this->manage){ ?>
									<a href="<?php echo hikashop_completeLink('product&task=edit&cid[]='.$row->product_id); ?>">
								<?php } ?>
										<?php echo $row->product_name; ?><br/><?php echo $row->product_code; ?>
								<?php if($this->manage){ ?>
									</a>
								<?php } ?>
							</td>
							<td><?php
								$field = 'price_value';
								if($this->config->get('floating_tax_prices')){
									$field = 'price_value_with_tax';
								}
								echo $this->currencyHelper->displayPrices(@$row->prices, $field);
							?></td>
							<td><?php
								echo ($row->product_quantity==-1?JText::_('UNLIMITED'):$row->product_quantity);
							?></td>
							<td><?php
								echo rtrim(rtrim($row->product_length,'0'),',.').' '.JText::_($row->product_dimension_unit);
							?></td>
							<td><?php
								echo rtrim(rtrim($row->product_width,'0'),',.').' '.JText::_($row->product_dimension_unit);
							?></td>
							<td><?php
								echo rtrim(rtrim($row->product_height,'0'),',.').' '.JText::_($row->product_dimension_unit);
							?></td>
							<td><?php
								echo rtrim(rtrim($row->product_weight,'0'),',.').' '. JText::_($row->product_weight_unit);
							?></td>
<?php
		if(!empty($this->fields)){
			foreach($this->fields as $field){
				if($field->field_type == 'customtext') continue;
				$namekey = $field->field_namekey;
				echo '<td>'.$this->fieldsClass->show($field,$row->$namekey).'</td>';
			}
		}

		if(!empty($this->extrafields)) {
			foreach($this->extrafields as $namekey => $extrafield) {
				$value = '';
				if( isset($extrafield->value)) {
					$n = $extrafield->value;
					$value = $row->$n;
				} else if(!empty($extrafield->obj)) {
					$n = $extrafield->obj;
					$value = $n->showfield($this, $namekey, $row);
				} else if( isset( $row->$namekey)) {
					$value = $row->$namekey;
				}
				echo '<td class="hikashop_product_'.$namekey.'_value">'.$value.'</td>';
			}
		}
?>
							<td class="order"><?php
								if($this->doOrdering){
									if($this->manage){ ?>
										<span><?php echo $this->pagination->orderUpIcon( $i, $this->order->reverse XOR ( $row->ordering >= @$this->rows[$i-1]->ordering ), $this->order->orderUp, 'Move Up',$this->order->ordering ); ?></span>
										<span><?php echo $this->pagination->orderDownIcon( $i, $a, $this->order->reverse XOR ( $row->ordering <= @$this->rows[$i+1]->ordering ), $this->order->orderDown, 'Move Down' ,$this->order->ordering); ?></span>
										<input type="text" name="order[]" size="5" <?php if(!$this->order->ordering) echo 'disabled="disabled"'?> value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
									<?php }else{ echo $row->ordering; }
								} else {
									?><a href="#" title="<?php echo $this->noOrderingMessage; ?>"><i class="fa fa-times-circle"></i></a><?php
								}
							?></td>
							<td class="hk_center"><?php
								if($this->manage) {
									?><span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->product_published,'product') ?></span><?php
								} else {
									echo $this->toggleClass->display('activate',$row->product_published);
								}
							?></td>
							<td width="1%" class="hk_center"><?php
								echo $row->product_id;
							?></td>
						</tr>
<?php
		$k = 1-$k;
	}
?>
					</tbody>
				</table>
				<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" id="filter_id" name="filter_id" value="<?php echo $this->pageInfo->filter->filter_id; ?>" />
				<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
<?php if($this->config->get('category_explorer')) { ?>
	</div>
</div>
<?php } ?>
