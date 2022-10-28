<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="<?php echo hikamarket::completeLink('config',true);?>" method="post" name="adminForm" id="adminForm">
	<div class="title" style="float: left;">
		<h1><?php echo JText::_('HIKA_FILE').' : '.$this->file->name; ?></h1>
	</div>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="hikabtn hikabtn-success" type="button" onclick="window.hikashop.submitform('savelanguage', 'adminForm'); return false;"><i class="fas fa-save"></i> <?php echo JText::_('HIKA_SAVE'); ?></button>
		<button class="hikabtn hikabtn-primary" type="button" onclick="window.hikashop.submitform('share', 'adminForm'); return false;"><i class="fas fa-share-alt"></i> <?php echo JText::_('SHARE'); ?></button>
	</div>
	<div class="clearfix"></div>

	<div >
		<h2><?php echo JText::_('HIKA_FILE').' : '.$this->file->name; ?></h2>
		<textarea style="width:100%;box-sizing: border-box;" rows="18" name="content" id="translation" ><?php echo @$this->file->content;?></textarea>
	</div>

	<hr/>

	<div>
		<h2><?php echo JText::_('HIKAMARKET_OVERRIDE').' : '; ?></h2>
		<?php echo JText::_('OVERRIDE_WITH_EXPLANATION'); ?>
		<textarea style="width:100%;box-sizing: border-box;" rows="18" name="content_override" id="translation_override" ><?php echo $this->override_content;?></textarea>
	</div>

	<div class="clr"></div>
	<input type="hidden" name="code" value="<?php echo $this->file->name; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_('form.token'); ?>
</form>
