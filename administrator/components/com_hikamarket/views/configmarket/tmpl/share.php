<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="index.php?tmpl=component&amp;option=<?php echo HIKAMARKET_COMPONENT ?>" method="post"  name="adminForm" id="adminForm" >
	<fieldset>
		<div class="header" style="float: left;"><?php echo JText::_('SHARE').' : '.$this->file->name; ?></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<button class="btn" type="button" onclick="window.hikashop.submitform('send','adminForm')"><?php echo JText::_('SHARE'); ?></button>
		</div>
	</fieldset>
	<fieldset class="adminform">
		<?php
echo hikamarket::display(
	JText::_('SHARE_HIKAMARKET_CONFIRMATION_1').'<br/>'.
	JText::_('SHARE_HIKAMARKET_CONFIRMATION_2').'<br/>'.
	JText::_('SHARE_CONFIRMATION_3'), 'info');
		?><br/>
		<textarea cols="100" rows="8" name="mailbody">Hi Hikari Software team,
Here is a new version of the language file for HikaMarket, I translated few more strings...</textarea>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="code" value="<?php echo $this->file->name; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_('form.token'); ?>
</form>
