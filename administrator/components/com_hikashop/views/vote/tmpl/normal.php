<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
	$row =& $this->rows[0];
	if($row == null) $row = new stdClass();
	$item =& $this->item;
	$newItem = $item->newItem;
	if($newItem == true){
		$row->vote_ip = '';
		$row->vote_type = 'product';
		$this->item->enabled = '3';
		$row->vote_rating = '';
		$row->vote_comment = ' ';
		$row->vote_date = time();
	}
?>
	<tr>
		<td class="key">
			<label for="data[vote][vote_ref_id]">
				<?php echo JText::_( 'HIKASHOP_ITEM' ); ?>
			</label>
		</td>
		<td>
			<?php
			echo $this->nameboxType->display(
				'data[vote][vote_ref_id]',
				@$row->vote_ref_id,
				hikashopNameboxType::NAMEBOX_SINGLE,
				'product',
				array(
					'delete' => false,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
				)
			);
			?>
		</td>
	</tr>
	<?php if($newItem == true){ ?>
	<tr>
		<td class="key">
			<label for="data[vote][vote_type]">
				<?php echo JText::_( 'HIKA_TYPE' ); ?>
			</label>
		</td>
		<td>
		<?php
			echo $this->voteType->display('data[vote][vote_type]',$row->vote_type);
		?>
		</td>
	</tr>
	<?php } ?>
	<?php if($this->item->enabled == 1 || $this->item->enabled == 3){ ?>
	<tr>
		<td class="key">
			<label for="data[vote][vote_rating]">
				<?php echo JText::_( 'VOTE' ); ?>
			</label>
		</td>
		<td>
			<input type="text" size="100" name="data[vote][vote_rating]"
			value="<?php echo $this->escape(@$row->vote_rating);?>" />

		</td>
	</tr>
	<?php }
		if($this->item->enabled == 2 || $this->item->enabled == 3){
	?>
	<tr>
		<td class="key">
			<label for="data[vote][vote_comment]">
				<?php echo JText::_( 'COMMENT' ); ?>
			</label>
		</td>
		<td>
			<textarea cols="71" name="data[vote][vote_comment]" ><?php echo $this->escape(@$row->vote_comment); ?></textarea>

		</td>
	</tr>
	<?php } ?>
	<tr>
		<td class="key">
			<label for="data[vote][vote_date]">
				<?php echo JText::_( 'DATE' ); ?>
			</label>
		</td>
		<td>
			<?php if(!isset($row->vote_date)){$row->vote_date = time();} ?>
			<input type="text" size="100" name="data[vote][vote_date]" value="<?php echo date('d/m/Y h:m:s', @$row->vote_date); ?>" disabled="disabled"/>
		</td>
	</tr>
