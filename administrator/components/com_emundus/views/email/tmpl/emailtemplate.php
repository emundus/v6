<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>" method="post"  name="adminForm" id="adminForm">
	<fieldset>
		<label><?php echo JText::_('HIKA_NAME'); ?></label>
		<input type="text" name="file" value="<?php echo $this->fileName; ?>" />
		<div class="toolbar" id="toolbar" style="float: right;">
			<button class="btn" type="button" onclick="javascript:submitbutton('saveemailtemplate'); return false;"><?php echo JText::_('HIKA_SAVE'); ?></button>
		</div>
	</fieldset>
	<?php echo $this->editor->displayCode('templatecontent',$this->content); ?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="saveemailtemplate" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="ctrl" value="email" />
	<input type="hidden" name="email_name" value="<?php echo $this->email_name; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
