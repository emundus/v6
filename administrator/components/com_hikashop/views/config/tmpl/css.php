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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>" method="post"  name="adminForm" id="adminForm">
	<div class="title" style="float: left;">
		<h1><?php echo $this->type.'_'.$this->fileName.'.css'; ?></h1>
	</div>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="hikabtn hikabtn-success" type="button" onclick="javascript:submitbutton('savecss'); return false;"><i class="fas fa-save"></i> <?php echo JText::_('HIKA_SAVE'); ?></button>
	</div>
	<div class="clearfix"></div>
	<?php echo $this->editor->displayCode('csscontent',$this->content); ?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="savecss" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="config" />
	<input type="hidden" name="file" value="<?php echo $this->type.'_'.$this->fileName; ?>" />
	<input type="hidden" name="var" value="<?php echo hikaInput::get()->getCmd('var'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
