<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>	<div class="toolbar" id="toolbar" style="float: right;">
		<a href="#" class="btn btn-success"  onclick="submitbutton('addcharacteristic'); return false;">
			<i class="fa fa-save"></i> <?php echo JText::_('OK'); ?>
		</a>
	</div>
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=characteristic" method="post"  name="adminForm" id="adminForm">
	<?php
		$this->setLayout('form_item');
		echo $this->loadTemplate();
	?>
	<input type="hidden" name="data[characteristic][characteristic_parent_id]" value="<?php echo hikaInput::get()->getInt('characteristic_parent_id',-1); ?>" />
	<input type="hidden" name="id" value="<?php echo hikaInput::get()->getInt('id'); ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->cid; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="<?php echo hikaInput::get()->getCmd('task'); ?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="tmpl" value="component" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
