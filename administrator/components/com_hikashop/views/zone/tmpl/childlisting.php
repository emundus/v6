<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div style="float:right">
	<?php
		echo $this->popup->display(
			'<i class="fa fa-plus"></i> '.JText::_('ADD'),
			'ADD',
			hikashop_completeLink("zone&task=selectchildlisting&main_id=".$this->main_id."&main_namekey=".$this->main_namekey,true ),
			'subzones_add_button',
			760, 480, 'class="btn btn-primary"', '', 'link'
		);
	?>
</div>
<table id="hikashop_zone_child_listing" class="adminlist table table-striped table-hover" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="title">
				<?php echo JText::_('ZONE_NAME_ENGLISH'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('HIKA_NAME'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ZONE_CODE_2'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ZONE_CODE_3'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ZONE_TYPE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('HIKA_EDIT'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo JText::_('HIKA_DELETE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody id="list_0_data">
		<?php
			$this->k = 0;
			$this->setLayout('child');
			for($i = 0,$a = count($this->list);$i<$a;$i++){
				$this->row =& $this->list[$i];
				echo $this->loadTemplate();
				$this->k = 1-$this->k;
			}
		?>
	</tbody>
</table>

