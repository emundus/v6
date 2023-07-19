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
<form action="<?php echo hikashop_completeLink('orderstatus'); ?>" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">

<div class="hk-container-fluid hikashop_backend_tile_edition">
	<div class="hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('MAIN_OPTIONS');
		?></div>

		<dl class="hika_options">
			<dt class="hikashop_orderstatus_name"><label for="data_orderstatus__orderstatus_name"><?php echo JText::_('HIKA_NAME'); ?></label></dt>
			<dd class="hikashop_orderstatus_name"><input type="text" id="data_orderstatus__orderstatus_name" name="data[orderstatus][orderstatus_name]" value="<?php echo $this->escape(@$this->element->orderstatus_name); ?>"/></dd>

<?php if(empty($this->element->orderstatus_id)) { ?>
			<dt class="hikashop_orderstatus_namekey"><label for="data_orderstatus__orderstatus_namekey"><?php echo JText::_('HIKA_NAMEKEY'); ?></label></dt>
			<dd class="hikashop_orderstatus_namekey"><input type="text" id="data_orderstatus__orderstatus_namekey" name="data[orderstatus][orderstatus_namekey]" value="<?php echo $this->escape(@$this->element->orderstatus_namekey); ?>"/></dd>
<?php } else { ?>
			<dt class="hikashop_orderstatus_namekey"><label><?php echo JText::_('HIKA_NAMEKEY'); ?></label></dt>
			<dd class="hikashop_orderstatus_namekey"><?php echo @$this->element->orderstatus_namekey; ?></dd>
<?php } ?>
			<dt class="hikashop_orderstatus_color"><label><?php echo JText::_('BOX_COLOR'); ?></label></dt>
			<dd class="hikashop_orderstatus_color"><?php echo $this->colorType->displayAll('','data[orderstatus][orderstatus_color]',@$this->element->orderstatus_color); ?></dd>

			<dt class="hikashop_orderstatus_published"><label><?php echo JText::_('HIKA_PUBLISHED'); ?></label></dt>
			<dd class="hikashop_orderstatus_published"><?php echo JHTML::_('hikaselect.booleanlist', "data[orderstatus][orderstatus_published]" , '', @$this->element->orderstatus_published); ?></dd>

		</dl>
	</div></div>

	<div class="hkc-lg-6 hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php
			echo JText::_('HIKA_DESCRIPTION');
		?></div>
		<?php echo $this->editor->display(); ?>
		<div style="clear:both"></div>
	</div></div>
</div>

	<div style="clear:both" class="clr"></div>
	<input type="hidden" name="cid" value="<?php echo @$this->element->orderstatus_id; ?>"/>
	<input type="hidden" name="data[orderstatus][orderstatus_id]" value="<?php echo @$this->element->orderstatus_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="ctrl" value="orderstatus"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
