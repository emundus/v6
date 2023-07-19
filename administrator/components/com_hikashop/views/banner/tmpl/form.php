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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=banner" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<?php
		$this->banner_title_input = "data[banner][banner_title]";
		$this->banner_url_input = "data[banner][banner_url]";
		$this->banner_image_url_input = "data[banner][banner_image_url]";
		$this->banner_comment_input = "data[banner][banner_comment]";
		if($this->translation){
			$this->setLayout('translation');
		}else{
			$this->setLayout('normal');
		}
		echo $this->loadTemplate();
	?>
	<table class="admintable table" width="100%">
		<tr>
			<td class="key">
				<label for="data[banner][banner_published]">
					<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('hikaselect.booleanlist', "data[banner][banner_published]" , '',@$this->element->banner_published); ?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->banner_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="banner" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
