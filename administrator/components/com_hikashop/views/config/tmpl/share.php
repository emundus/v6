<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.0.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="index.php?tmpl=component&amp;option=<?php echo HIKASHOP_COMPONENT ?>" method="post"  name="adminForm" id="adminForm" >
	<div style="min-height: 50px;">
		<div class="hikashop_header" style="float: left;"><?php echo JText::_('SHARE').' : '.$this->file->name; ?></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<button class="btn btn-primary" type="button" onclick="javascript:submitbutton('send')"><i class="fa fa-share-alt"></i> <?php echo JText::_('SHARE'); ?></button>
		</div>
	</div>
	<div>
		<?php hikashop_display(JText::_('SHARE_HIKASHOP_CONFIRMATION_1').'<br/>'.JText::_('SHARE_HIKASHOP_CONFIRMATION_2').'<br/>'.JText::_('SHARE_CONFIRMATION_3'),'info'); ?><br/>
		<textarea cols="80" rows="8" name="mailbody">Hi Hikari Software team,
Here is a new version of the language file, I translated few more strings...</textarea>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="code" value="<?php echo $this->file->name; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
