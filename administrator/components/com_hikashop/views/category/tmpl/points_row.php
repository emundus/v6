<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><tr id="category_<?php echo $this->id;?>">
	<td>
		<?php echo $this->namebox->display(
				'category['.$this->id.']',
				0,
				hikashopNameboxType::NAMEBOX_SINGLE,
				'category',
				array(
					'delete' => false,
					'root' => 0,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				)
		); ?>
	</td>
	<td class="hk_center">
		<input type="text" name="category_points[<?php echo $this->id;?>]" id="category_points_<?php echo $this->id;?>" value="<?php echo (int)@$row->category_points; ?>" />
	</td>
	<td width="1%" class="hk_center">
		-
	</td>
</tr>
<?php exit; ?>
