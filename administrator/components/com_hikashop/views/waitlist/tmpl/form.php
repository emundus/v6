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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=waitlist" method="post"  name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table class="admintable table" width="100%">
		<tr>
			<td class="key">
					<?php echo JText::_( 'HIKA_NAME' ); ?>
			</td>
			<td>
				<input type="text" size="40" name="data[waitlist][name]" value="<?php echo $this->escape(@$this->element->name); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'HIKA_EMAIL' ); ?>
			</td>
			<td>
				<input type="text" size="40" name="data[waitlist][email]" value="<?php echo $this->escape(@$this->element->email); ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'PRODUCT' ); ?>
			</td>
			<td>
				<?php
					echo  $this->nameboxType->display(
						'data[waitlist][product_id]',
						@$this->element->product_id,
						hikashopNameboxType::NAMEBOX_SINGLE,
						'product',
						array(
							'delete' => true,
							'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
							'variants' => true
						)
					);
				?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'MENU' ); ?>
			</td>
			<td>
				<?php $menuType = hikashop_get('type.menus');
					echo $menuType->display('data[waitlist][product_item_id]',@$this->element->product_item_id);?>
			</td>
		</tr>
		<tr>
			<td class="key">
					<?php echo JText::_( 'DATE' ); ?>
			</td>
			<td>
				<?php echo JHTML::_('calendar', (@$this->element->date?hikashop_getDate(@$this->element->date,'%Y-%m-%d %H:%M'):''), 'data[waitlist][date]','date',hikashop_getDateFormat('%d %B %Y %H:%M'),array('size'=>'20')); ?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->waitlist_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="waitlist" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
