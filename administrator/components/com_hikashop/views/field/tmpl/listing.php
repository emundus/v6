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
<form action="<?php echo hikashop_completeLink('field'); ?>" method="post" name="adminForm" id="adminForm">
<?php if(hikashop_level(1)) { ?>
<div class="hk-row-fluid">
	<div class="hkc-md-4">
<?php
?>
	</div>
	<div class="hkc-md-8 hikashop_listing_filters">
		<?php echo $this->tabletype->display('filter_table', $this->selectedType); ?>
	</div>
</div>
<?php } 
		echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_field_listing" class="adminlist table table-striped" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum hk-lg-show"><?php
					echo JText::_('HIKA_NUM');
				?></th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
<?php if(hikashop_level(1) || isset($this->tabletype)) { ?>
				<th id="hikashop_field_table_title" class="title hk-ts-show"><?php
					echo JText::_('FIELD_TABLE');
				?></th>
<?php } ?>
				<th class="title"><?php
					echo JText::_('FIELD_COLUMN');
				?></th>
				<th class="title hk-xs-show"><?php
					echo JText::_('FIELD_LABEL');
				?></th>
				<th class="title hk-sm-show"><?php
					echo JText::_('FIELD_TYPE');
				?></th>
				<th class="title titletoggle hk-md-show"><?php
					echo JText::_('REQUIRED');
				?></th>
				<th class="title titleorder"><?php
					$keys = array_keys($this->rows);  
					$rows_nb = end($keys);
					$href = "javascript:saveorder(".$rows_nb.", 'saveorder')";
					?><a href="<?php echo $href; ?>" rel="tooltip" class="saveorder btn btn-sm btn-secondary float-end" title="Save Order">
						<button class="button-apply btn btn-success" type="button">
<!--						<span class="icon-apply" aria-hidden="true"></span> -->
							<i class="fas fa-save"></i>
						</button>
					</a><?php
					echo JText::_('HIKA_ORDER');
				?></th>
				<th class="title titletoggle hk-md-show"><?php
					echo JText::_('DISPLAY_FRONTCOMP');
				?></th>
				<th class="title titletoggle hk-md-show"><?php
					echo JText::_('DISPLAY_BACKEND_FORM');
				?></th>
				<th class="title titletoggle hk-md-show"><?php
					echo JText::_('DISPLAY_BACKEND_LISTING');
				?></th>
				<th class="title titletoggle hk-xts-show"><?php
					echo JText::_('HIKA_PUBLISHED');
				?></th>
				<th class="title titletoggle hk-lg-show"><?php
					echo JText::_('CORE');
				?></th>
				<th class="title titleid hk-lg-show"><?php
					echo JText::_('ID');
				?></th>
			</tr>
		</thead>
		<tbody>
<?php
	$k = 0;
	$i = 0;
	$a = count($this->rows);
	foreach($this->rows as &$row) {
		$publishedid = 'field_published-'.$row->field_id;
		$requiredid = 'field_required-'.$row->field_id;
		$backendid = 'field_backend-'.$row->field_id;
		$backendlistingid = 'field_backend_listing-'.$row->field_id;
		$frontcompid = 'field_frontcomp-'.$row->field_id;
?>
			<tr class="row<?php echo $k; ?>">
				<td class="hk-lg-show hk_center">
					<?php echo $i + 1; ?>
				</td>
				<td class="hk_center">
					<?php echo JHTML::_('grid.id', $i, $row->field_id ); ?>
				</td>
<?php if(hikashop_level(1) || isset($this->tabletype)) { ?>
				<td class="hikashop_field_table_value hk-ts-show"><?php

					$table_name = $row->field_table;
					if(substr($table_name, 0, 4) == 'plg.' && isset($this->tabletype) && isset($this->tabletype->values[$table_name]))
						$table_name = $this->tabletype->values[$table_name]->text;

					echo $table_name;
				?></td>
<?php } ?>
				<td class="hk-xs-show">
					<?php if($this->manage){ ?>
						<a href="<?php echo hikashop_completeLink('field&task=edit&cid[]='.$row->field_id); ?>">
					<?php } ?>
							<?php echo $row->field_namekey; ?>
					<?php if($this->manage){ ?>
						</a>
					<?php } ?>
				</td>
				<td>
					<?php echo $this->fieldsClass->trans($row->field_realname); ?>
				</td>
				<td class="hk-sm-show">
					<?php
						if(isset($this->fieldtype->allValues[$row->field_type]))
							echo $this->fieldtype->allValues[$row->field_type]['name'];
						else
							echo $row->field_type;
					?>
				</td>
				<td style="text-align:center;" class="hk-md-show">
					<?php if($this->manage){ ?>
						<span id="<?php echo $requiredid ?>" class="loading"><?php echo $this->toggleClass->toggle($requiredid,(int) $row->field_required,'field') ?></span>
					<?php }else{ echo $this->toggleClass->display('activate',$row->field_required); } ?>
				</td>
				<td class="order">
					<?php if($this->manage){ ?>
						<span><?php echo $this->pagination->orderUpIcon( $i, $row->field_ordering >= @$this->rows[$i-1]->field_ordering ,'orderup', 'Move Up',true ); ?></span>
						<span><?php echo $this->pagination->orderDownIcon( $i, $a, $row->field_ordering <= @$this->rows[$i+1]->field_ordering , 'orderdown', 'Move Down' ,true); ?></span>
						<input type="text" name="order[]" size="5" value="<?php echo $row->field_ordering; ?>" class="text_area" style="text-align: center" />
					<?php }else{ $row->field_ordering; } ?>
				</td>
				<td style="text-align:center;" class="hk-md-show">
<?php
	if(in_array($row->field_table, array(null))) {
		echo '--';
	} else {
?>
					<?php if($this->manage){ ?>
						<span id="<?php echo $frontcompid ?>" class="loading"><?php echo $this->toggleClass->toggle($frontcompid,(int) $row->field_frontcomp,'field') ?></span>
					<?php }else{ echo $this->toggleClass->display('activate',$row->field_frontcomp); } ?>
<?php } ?>
				</td>
				<td style="text-align:center;" class="hk-md-show">
<?php if(in_array($row->field_table, array(null))) {
		echo '--';
	} else {
?>
					<?php if($this->manage){ ?>
						<span id="<?php echo $backendid ?>" class="loading"><?php echo $this->toggleClass->toggle($backendid,(int) $row->field_backend,'field') ?></span>
					<?php }else{ echo $this->toggleClass->display('activate',$row->field_backend); } ?>
<?php } ?>
				</td>
				<td style="text-align:center;" class="hk-md-show">
<?php if(in_array($row->field_table, array('address'))) {
		echo '--';
	} else {
?>
						<?php if($this->manage){ ?>
							<span id="<?php echo $backendlistingid ?>" class="loading"><?php echo $this->toggleClass->toggle($backendlistingid,(int) $row->field_backend_listing,'field') ?></span>
						<?php }else{ echo $this->toggleClass->display('activate',$row->field_backend_listing); } ?>
					<?php } ?>
				</td>
				<td style="text-align:center;" class="hk-xts-show">
					<?php if($this->manage){ ?>
						<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->field_published,'field') ?></span>
					<?php }else{ echo $this->toggleClass->display('activate',$row->field_published); } ?>
				</td>
				<td style="text-align:center;" class="hk-lg-show">
					<?php echo $this->toggleClass->display('activate', $row->field_core); ?>
				</td>
				<td width="1%" style="text-align:center;" class="hk-lg-show">
					<?php echo $row->field_id; ?>
				</td>
			</tr>
<?php
		$i++;
		$k = 1 - $k;
	}
	unset($row);
?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="field" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
