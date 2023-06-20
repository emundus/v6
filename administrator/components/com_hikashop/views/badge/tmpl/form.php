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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=badge" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<?php
		$this->badge_name = "data[badge][badge_name]";
		$this->badge_position = "data[badge][badge_position]";

	?>
<div id="page-badge" class="hk-row-fluid hikashop_backend_tile_edition">
	<div class="hkc-md-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
				<table class="admintable table" style="margin:auto">
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKA_NAME' ); ?>
						</td>
						<td>
							<input type="text" size="40" name="data[badge][badge_name]" value="<?php echo $this->escape(@$this->element->badge_name); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[badge][badge_published]" , '',@$this->element->badge_published);?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'START_DATE' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('calendar', (@$this->element->badge_start?hikashop_getDate(@$this->element->badge_start,'%Y-%m-%d %H:%M'):''), 'data[badge][badge_start]','badge_start',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size'=>'20')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'END_DATE' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('calendar', (@$this->element->badge_end?hikashop_getDate(@$this->element->badge_end,'%Y-%m-%d %H:%M'):''), 'data[badge][badge_end]','badge_end',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size'=>'20')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'MAXIMUM_PRODUCT_QUANTITY' ); ?>
						</td>
						<td>
							<input type="text" name="data[badge][badge_quantity]" value="<?php echo @$this->element->badge_quantity; ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'NEW_PRODUCT_PERIOD' ); ?>
						</td>
						<td>
							<?php
								$delayType = hikashop_get('type.delay');
								echo $delayType->display('data[badge][badge_new_period]', @$this->element->badge_new_period, 3);
							?>
						</td>
					</tr>

					<tr>
						<td class="key">
							<?php echo JText::_( 'PRODUCT' ); ?>
						</td>
						<td>
						<?php
							echo $this->nameboxType->display(
								'data[badge][badge_product_id]',
								explode(',',trim(@$this->element->badge_product_id,',')),
								hikashopNameboxType::NAMEBOX_MULTIPLE,
								'product',
								array(
									'delete' => true,
									'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'CATEGORY' ); ?>
						</td>
						<td>
						<?php
							echo $this->nameboxType->display(
								'data[badge][badge_category_id]',
								explode(',',trim(@$this->element->badge_category_id, ',')),
								hikashopNameboxType::NAMEBOX_MULTIPLE,
								'category',
								array(
									'delete' => true,
									'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'INCLUDING_SUB_CATEGORIES' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[badge][badge_category_childs]" , '',@$this->element->badge_category_childs	); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'DISCOUNT' ); ?>
						</td>
						<td>
						<?php
							echo $this->nameboxType->display(
								'data[badge][badge_discount_id]',
								explode(',',@$this->element->badge_discount_id),
								hikashopNameboxType::NAMEBOX_MULTIPLE,
								'discount',
								array(
									'type' => 'discount',
									'delete' => true,
									'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
								)
							);
						?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'URL' ); ?>
						</td>
						<td>
							<input type="text" name="data[badge][badge_url]" value="<?php echo @$this->element->badge_url; ?>" />
						</td>
					</tr>
				</table>
		</div>
	</div>
	<div class="hkc-md-6">
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('HIKA_IMAGE'); ?></div>
				<table class="admintable table" margin="auto">
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_IMAGES' ); ?>
							</td>
							<td>
								<?php
									$image_options = array('default' => true,'forcesize'=>true,'scale'=>$this->config->get('image_scale_mode','inside'));
									$img = $this->image->getThumbnail(@$this->element->badge_image, array('width' => 100, 'height' => 100), $image_options);
									if($img->success) {
										echo '<img class="hikashop_category_listing_image" title="'.$this->escape(@$this->element->badge_name).'" alt="'.$this->escape(@$this->element->badge_name).'" src="'.$img->url.'"/>';
									}
								?>
								<input type="file" name="files" size="30" />
								<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'KEEP_SIZE' ); ?>
							</td>
							<td>
									<?php echo JHTML::_('hikaselect.booleanlist', "data[badge][badge_keep_size]" , 'onchange="hikashopSizeUpdate(this.value);"',@$this->element->badge_keep_size);?>
							</td>
						</tr>
						<tr id="field_size">
							<td class="key">
									<?php echo JText::_( 'FIELD_SIZE' ); ?>
							</td>
							<td>
								<?php if(!isset($this->element->badge_size))$this->element->badge_size=30;?>
								<input type="text" size="2" name="data[badge][badge_size]" value="<?php echo $this->escape($this->element->badge_size);?>" />
							<?php echo JText::_( '%' );?>

							</td>
						</tr>
						<tr>
						<td class="key">
								<?php echo JText::_( 'POSITION' );?>
						</td>
						<td>
								<?php echo $this->badge->display("data[badge][badge_position]",@$this->element->badge_position);?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'VERTICAL_DISTANCE' );?>
						</td>
						<td>
								<?php if(!isset($this->element->badge_vertical_distance))$this->element->badge_vertical_distance=0;?>
								<input type="text" size="2" name="data[badge][badge_vertical_distance]" value="<?php echo $this->escape($this->element->badge_vertical_distance);?>" />
							<?php echo JText::_( 'px' );?>

							</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'HORIZONTAL_DISTANCE' );?>
						</td>
						<td>
								<?php if(!isset($this->element->badge_horizontal_distance))$this->element->badge_horizontal_distance=0;?>
								<input type="text" size="2" name="data[badge][badge_horizontal_distance]" value="<?php echo $this->escape($this->element->badge_horizontal_distance);?>" />
							<?php echo JText::_( 'px' );?>

							</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('ACCESS_LEVEL'); ?></div>
<?php
	if(hikashop_level(2)) {
		$acltype = hikashop_get('type.acl');
		echo $acltype->display('badge_access',@$this->element->badge_access,'badge');
	} else {
		echo '<small style="color:red">'.JText::_('ONLY_FROM_HIKASHOP_BUSINESS').'</small>';
	}
?>
			</div>
		</div>
	</div>
</div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->badge_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="badge" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
