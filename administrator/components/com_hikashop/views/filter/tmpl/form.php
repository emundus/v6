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
<form action="index.php?option=com_hikashop&amp;ctrl=filter" method="post"  name="adminForm" id="adminForm" >
<div id="page-filter" class="hk-row-fluid hikashop_backend_tile_edition">
	<div class="hkc-md-6">
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
				<table class="paramlist admintable table">
					<tr>
						<td class="key">
								<?php echo JText::_( 'HIKA_NAME' ); ?>
						</td>
						<td>
							<input type="text" name="data[filter][filter_name]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_name); ?>" />
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_( 'HIKA_TYPE' ); ?>
						</td>
						<td>
							<?php
								echo $this->filterType->display('data[filter][filter_type]',@$this->element->filter_type);
							?>
						</td>
					</tr>
					<tr>
						<td class="key"><?php
							echo hikashop_hktooltip(JText::_('HIKA_FILTER_CATEGORY_DESC'), '', JText::_('CATEGORY'), '', 0);
						?></td>
						<td>
						<?php
							$categories = explode(',', (string)@$this->element->filter_category_id);
							echo  $this->nameboxType->display(
								'data[filter][filter_category_id]',
								$categories,
								hikashopNameboxType::NAMEBOX_MULTIPLE,
								'category',
								array(
									'delete' => true,
									'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
								)
							);
						?>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'INCLUDING_SUB_CATEGORIES' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_category_childs]" , '',@$this->element->filter_category_childs	); ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('EXTRA_INFORMATION'); ?></div>
				<table class="paramlist admintable table">
					<tr>
						<td class="key">
								<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_published]" , '',@$this->element->filter_published); ?>
						</td>
					</tr>
					<tr id="applyOnClick">
						<td class="key">
								<?php echo JText::_( 'SUBMIT_ON_CLICK' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_direct_application]" , '',@$this->element->filter_direct_application); ?>
						</td>
					</tr>
					<tr id="filterHeight">
						<td class="key">
								<?php echo JText::_( 'PRODUCT_HEIGHT' ); ?>
						</td>
						<td>
							<input size="10" type="text" name="data[filter][filter_height]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->element->filter_height); ?>" /> px
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'DELETABLE_FILTER' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_deletable]" , '',@$this->element->filter_deletable); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
								<?php echo JText::_( 'DYNAMIC_DISPLAY' ); ?>
						</td>
						<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[filter][filter_dynamic]" , '',@$this->element->filter_dynamic); ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="hikashop_tile_block"><div style="min-height:auto;">
			<div class="hikashop_tile_title"><?php echo JText::_('ACCESS_LEVEL'); ?></div>
				<?php
				if(hikashop_level(2)){
					$acltype = hikashop_get('type.acl');
					echo $acltype->display('filter_access',@$this->element->filter_access,'filter');
				}else{
					echo hikashop_getUpgradeLink('business');
				} ?>
			</div>
		</div>
	</div>
	<div class="hkc-md-6">
		<div class="hikashop_tile_block"><div>
			<div class="hikashop_tile_title"><?php echo JText::_('OPTIONS'); ?></div>
				<?php if(1){
						echo $this->loadTemplate('options');
				}?>
			</div>
		</div>
	</div>
</div>
	<div class="clr"></div>

	<input type="hidden" name="cid[]" value="<?php echo @$this->element->filter_id; ?>" />
	<input type="hidden" name="option" value="com_hikashop" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="filter" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
