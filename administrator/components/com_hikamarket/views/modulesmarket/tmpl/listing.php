<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikamarket::completeLink('modules'); ?>" method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_('FILTER'); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area"/>
				<button class="btn" onclick="this.form.submit();"><?php echo JText::_('GO'); ?></button>
				<button class="btn" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_('RESET'); ?></button>
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum"><?php
					echo JText::_( 'HIKA_NUM' );
				?></th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title"><?php
					echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'title', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
				<th class="title titletoggle"><?php
					echo JText::_('HIKA_ENABLED'); ?></th>
				<th class="title titleid"><?php
					echo JHTML::_('grid.sort', JText::_( 'ID' ), 'id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value );
				?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
$k = 0;
foreach($this->rows as $i => $row) {
	$publishedid = 'published-' . $row->id;
?>
			<tr class="row<?php echo $k; ?>">
				<td align="center"><?php
					echo $i + 1;
				?></td>
				<td align="center"><?php
					echo JHTML::_('grid.id', $i, $row->id);
				?></td>
				<td><?php
					if($this->manage){
						?><a href="<?php echo hikamarket::completeLink('modules&task=edit&cid[]=' . $row->id);?>"><?php
					}
					echo $row->title;
					if($this->manage){
						?></a><?php
					}
				?></td>
				<td align="center"><?php
					if($this->manage){
						?><span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid, $row->published, 'modules'); ?></span><?php
					}else{
						echo $this->toggleClass->display('activate', $row->published);
					}
				?></td>
				<td align="center"><?php
					echo $row->id;
				?></td>
			</tr>
<?php
	$k = 1-$k;
}
?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
