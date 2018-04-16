<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 17/09/14
 * Time: 10:30
 */?>

<form action = "index.php?option=com_emundus&controller=users&task=addgroup" id="em-add-group" role="form" method="post">
	<h3>
		<?php echo JText::_('ADD_GROUP'); ?>
</h3>
<fieldset>
	<div class="form-group">
		<label class="control-label" for="gname"><?php echo JText::_('GROUP_NAME'); ?></label>
		<input type="text" class="form-control" id="gname" name="gname" >
	</div>
	<div class="form-group">
		<label class="control-label" for="gdescription"><?php echo JText::_('GROUP_DESCRIPTION'); ?></label>
		<textarea class="form-control" name = "gdescription" id = "gdescription" cols = "30" rows = "3"></textarea>
	</div>
	<div class="form-group">
		<label class="control-label" for="gprog"><?php echo JText::_('COM_EMUNDUS_COURSES'); ?></label>
		<select name = "gprogs" id = "gprogs" data-placeholder="<?php echo JText::_("COM_EMUNDUS_CHOOSE_PROGRAMME")?>" multiple>
			<?php foreach($this->progs as $prog):?>
				<option value = "<?php echo $prog['code']?>"><?php echo trim($prog['label'])?></option>
			<?php endforeach?>
		</select>
	</div>
	
</fieldset>
<fieldset>
	<h4>
		<?php echo JText::_('DEFAULT_RIGHT');?>
	</h4>
	<table id="em-modal-action-table" class="table table-hover" style="color:black !important;">
		<thead>
			<tr>
				<th></th>
				<th>
					<input type="checkbox" class="em-modal-check em-check-all" name="c-check-all" id="c-check-all" checked style="width: 20px !important"/>
					<label for="c-check-all"><?php echo JText::_('CREATE')?></label>
				</th>
				<th>
					<input type="checkbox" class="em-modal-check em-check-all" name="r-check-all" id="r-check-all" checked style="width: 20px !important"/>
					<label for="r-check-all"><?php echo JText::_('RETRIEVE')?></label>
				</th>
				<th>
					<input type="checkbox" class="em-modal-check em-check-all" name="u-check-all" id="u-check-all" checked style="width: 20px !important"/>
					<label for="u-check-all"><?php echo JText::_('UPDATE')?></label>
				</th>
				<th>
					<input type="checkbox" class="em-modal-check em-check-all" name="d-check-all" id="d-check-all" checked style="width: 20px !important"/>
					<label for="d-check-all"><?php echo JText::_('DELETE')?></label>
				</th>
			</tr>
		</thead>
		<tbody size="<?php echo count($this->actions)?>">
		<?php 
//die(var_dump($this->actions));
		foreach($this->actions as $l => $action):?>
			<tr class="em-actions-table-line">
				<td  id="<?php echo $action['id']?>"><?php echo JText::_(strtoupper($action['label']))?></td>
				<?php if($action['c'] == 1): ?>
					<td  id="c-check-<?php echo $action['id']?>" class="em-has-checkbox" >
						<input type="checkbox" class="em-modal-check c-check" name="c-check-<?php echo $action['id']?>" id="c-check-<?php echo $action['id']?>" checked /></td>
				<?php else:?>
					<td class="em-no no-action-c"></td>
				<?php endif?>
				<?php if($action['r'] == 1): ?>
					<td  id="r-check-<?php echo $action['id']?>" class="em-has-checkbox" ><input type="checkbox" class="em-modal-check r-check" name="r-check-<?php echo $action['id']?>" id="r-check-<?php echo $action['id']?>" checked /></td>
				<?php else:?>
					<td class="em-no no-action-r"></td>
				<?php endif?>
				<?php if($action['u'] == 1): ?>
					<td  id="u-check-<?php echo $action['id']?>" class="em-has-checkbox" ><input type="checkbox" class="em-modal-check u-check" name="u-check-<?php echo $action['id']?>" id="u-check-<?php echo $action['id']?>" checked/></td>
				<?php else:?>
					<td class="em-no no-action-u"></td>
				<?php endif?>
				<?php if($action['d'] == 1): ?>
					<td  id="d-check-<?php echo $action['id']?>" class="em-has-checkbox" ><input type="checkbox" class="em-modal-check d-check" name="d-check-<?php echo $action['id']?>" id="d-check-<?php echo $action['id']?>" checked/></td>
				<?php else:?>
					<td class="em-no no-action-d"></td>
				<?php endif?>
				</tr>
		<?php endforeach;?>
		</tbody>
	</table>
</fieldset>
</form>


<script type="text/javascript">
$(document).ready(function()
{
	$('form').css({padding:"26px"})
	$('#gprogs').chosen({width:'100%'});
});
</script>

