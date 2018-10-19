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
	<div class="title" style="float: left;">
		<h1><?php echo JText::_('HIKA_FILE').' : '.$this->file->name; ?></h1>
	</div>
	<div class="toolbar" id="toolbar" style="float: right;">
		<button class="hikabtn hikabtn-success" type="button" onclick="javascript:submitbutton('savelanguage'); return false;"><i class="fa fa-save"></i> <?php echo JText::_('HIKA_SAVE'); ?></button>
		<button class="hikabtn hikabtn-primary" type="button" onclick="javascript:submitbutton('share'); return false;"><i class="fa fa-share-alt"></i> <?php echo JText::_('SHARE'); ?></button>
	</div>
	<div class="clearfix"></div>

	<div>
		<h2>
			<?php echo JText::_( 'HIKA_FILE').' : '.$this->file->name; ?>
			<?php if(!empty($this->showLatest)){ ?><button class="btn btn-primary" style="text-align:right" type="button" onclick="javascript:submitbutton('latest')"><?php echo JText::_('LOAD_LATEST_LANGUAGE'); ?></button><?php } ?>
		</h2>
		<textarea style="width:100%;" rows="18" name="content" id="translation" ><?php echo @$this->file->content;?></textarea>
	</div>
	<hr/>
	<div>
		<h2>
			<?php echo JText::_( 'OVERRIDE').' : '; ?>
		</h2>
		<?php echo JText::_( 'OVERRIDE_WITH_EXPLANATION'); ?>
		<textarea style="width:100%;" rows="18" name="content_override" id="translation_override" ><?php echo $this->override_content;?></textarea>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="code" value="<?php echo $this->file->name; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
