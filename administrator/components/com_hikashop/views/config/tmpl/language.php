<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>" method="post"  name="adminForm" id="adminForm" >
	<input type="hidden" name="code" value="<?php echo $this->file->name; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<div class="clearfix"></div>

	<?php echo hikashop_display(JText::_( 'OVERRIDE_WITH_EXPLANATION_TRANSLATION'),'info'); ?>
	<div class="hikashop_backend_tile_edition">
		<div class="hk-container-fluid">
			<div class="hkc-lg-6 hikashop_tile_block hikashop_language_edit_main">
				<div>
					<div class="hikashop_tile_title">
						<?php echo JText::_( 'HIKA_FILE').' : '.$this->file->name; ?>
					</div>
					<textarea style="width:98%;" rows="32" name="content" id="translation" ><?php echo str_replace('</textarea>', '&lt;/textarea&gt;', @$this->file->content);?></textarea>
				</div>
			</div>
			<div class="hkc-lg-6 hikashop_tile_block hikashop_language_edit_override">
				<div>
					<div class="hikashop_tile_title">
						<?php echo JText::_( 'OVERRIDE').' : '; ?>
					</div>
					<textarea style="width:98%;" rows="32" name="content_override" id="translation_override" ><?php echo str_replace('</textarea>', '&lt;/textarea&gt;', $this->override_content);?></textarea>
				</div>
			</div>
		</div>
	</div>
	<div class="clr"></div>
</form>
