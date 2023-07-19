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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=zone" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<div id="hikashop_zone_form" class="hk-row-fluid hikashop_backend_tile_edition">
		<div class="hkc-md-4">
			<div class="hikashop_tile_block">
				<div>
					<div class="hikashop_tile_title"><?php
						echo JText::_('ZONE_INFORMATION');
					?></div>
						<?php
						$this->setLayout('information');
						echo $this->loadTemplate();
						?>
				</div>
			</div>
		</div>
		<div class="hkc-md-8">
			<div class="hikashop_tile_block">
				<div>
					<div class="hikashop_tile_title"><?php
						echo JText::_('SUBZONES');
					?></div>
						<?php if(empty($this->element->zone_namekey)){
							echo JText::_( 'SUBZONES_CHOOSER_DISABLED' );
						}else{
							$this->setLayout('childlisting');
							echo $this->loadTemplate();
						} ?>
				</div>
			</div>
		</div>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->zone_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="zone" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
