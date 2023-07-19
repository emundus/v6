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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=characteristic" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
<div id="page-characteristic" class="hk-row-fluid hikashop_backend_tile_edition">
	<div class="hkc-md-4 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('MAIN_INFORMATION'); ?></div>
				<?php echo $this->loadTemplate('item');?>
				<table width="100%" class="admintable table">
					<tbody>
					<?php if(!empty($this->extraData->leftTop)) { echo implode("\r\n",$this->extraData->leftTop); } ?>
						<tr>
							<td class="key">
									<?php echo JText::_( 'HIKA_ALIAS' ); ?>
							</td>
							<td>
								<input type="text" id="characteristic_alias" name="data[characteristic][characteristic_alias]" value="<?php echo $this->escape(@$this->element->characteristic_alias); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
									<?php echo JText::_( 'DISPLAY_AVAILABLE_VALUES_ON_LISTINGS' ); ?>
							</td>
							<td>
							<?php echo JHTML::_('hikaselect.booleanlist', "data[characteristic][characteristic_values_on_listing]" , '',@$this->element->characteristic_values_on_listing	); ?>
							</td>
						</tr>
						<?php $config = hikashop_config();
						if(in_array($config->get('characteristic_display'),array('dropdown','radio'))){ ?>
							<tr>
								<td class="key">
										<?php echo JText::_( 'CHARACTERISTICS_DISPLAY' ); ?>
								</td>
								<td>
									<?php
										$characteristicdisplayType = hikashop_get('type.characteristicdisplay');
										echo $characteristicdisplayType->display('data[characteristic][characteristic_display_method]',@$this->element->characteristic_display_method,'characteristic');
									?>
								</td>
							</tr>
						<?php } ?>
						<?php if(!empty($this->extraData->leftBottom)) { echo implode("\r\n",$this->extraData->leftBottom); } ?>
					</tbody>
				</table>
		</div>
	</div>
	<div class="hkc-md-8 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('VALUES'); ?></div>
			<?php if(!empty($this->extraData->rightTop)) { echo implode("\r\n",$this->extraData->rightTop); } ?>
			<?php
				$this->setLayout('form_value');
				echo $this->loadTemplate();
			?>
			<?php if(!empty($this->extraData->rightBottom)) { echo implode("\r\n",$this->extraData->rightBottom); } ?>
		</div>
	</div>
</div>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->characteristic_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="characteristic" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
