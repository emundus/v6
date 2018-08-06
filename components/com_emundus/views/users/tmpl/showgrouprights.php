<?php
/**
 * User: brivalland
 * Date: 24/09/14
 * Time: 17:14
 */
?>
<style type="text/css">
span:hover {
    cursor: pointer;
}
</style>

<h3><?php echo JText::_('SHOW_RIGTH'); ?></h3>
<?php foreach($this->groups as $k => $g):?>
	<fieldset id="<?php echo $k?>">
		<h5>
			<?php echo $g['label']?>
		</h5>
		<ul>
			<strong><?php echo JText::_('COM_EMUNDUS_GROUP_PROGRAM')?></strong>
			<?php foreach($g['progs'] as $p):?>
				<li><?php echo $p['label']?></li>
			<?php endforeach;?>
		</ul>
		<?php if(!empty($g['acl'])):?>
			<table id="em-modal-action-table" class="table table-hover" style="color:black !important;">
				<thead>
				<tr>
					<th></th>
					<th>
						<label for="c-check-all"><?php echo JText::_('CREATE')?></label>
					</th>
					<th>
						<label for="r-check-all"><?php echo JText::_('RETRIEVE')?></label>
					</th>
					<th>
						<label for="u-check-all"><?php echo JText::_('UPDATE')?></label>
					</th>
					<th>
						<label for="d-check-all"><?php echo JText::_('DELETE')?></label>
					</th>
				</tr>
				</thead>
				<tbody size="<?php echo count(@$this->actions)?>">
				<?php 
				//var_dump($g['acl']);
				foreach($g['acl'] as $l => $action):?>

					<tr class="em-actions-table-line" id="<?php echo $action['id']; ?>">
						<td  id="<?php echo $action['id']?>"><?php echo JText::_(strtoupper($action['label']))?></td>
						<?php if($action['is_c'] == 1): ?>
							<td action="c" class="action">
								<?php if($action['c'] == 1): ?>
									<span class="glyphicon glyphicon-ok" style="color: #00c500"</span>
								<?php else:?>
									<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
								<?php endif?>
							</td>
						<?php else: ?><td></td><?php endif?>
							
						<?php if($action['is_r'] == 1): ?>
							<td action="r" class="action">
								<?php if($action['r'] == 1): ?>
									<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
								<?php else:?>
									<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
								<?php endif?>
							</td>
						<?php else: ?><td></td><?php endif?>

						<?php if($action['is_u'] == 1): ?>
							<td action="u" class="action">
								<?php if($action['u'] == 1): ?>
									<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
								<?php else:?>
									<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
								<?php endif?>
							</td>
						<?php else: ?><td></td><?php endif?>
						
						<?php if($action['is_d'] == 1): ?>
							<td action="d" class="action">
								<?php if($action['d'] == 1): ?>
									<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
								<?php else:?>
									<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
								<?php endif?>
							</td>
						<?php else: ?><td></td><?php endif?>


					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		<?php endif;?>
		<?php if(!empty($this->users)):?>
			<hr>
			<ul>
			<strong><?php echo JText::_('COM_EMUNDUS_USERS_GROUP')?></strong>
			<?php foreach($this->users as $user):?>
				<li><a href="#"><?php echo ucwords($user['firstname']).' '.strtoupper($user['lastname']);?></a></li>
			<?php endforeach;?>
			</ul>
		<?php endif;?>
	</fieldset>
<?php endforeach;?>

<script type="text/javascript">
var itemId = <?php echo $this->itemId; ?>;
	$(document).ready(
		function ()
		{
			$('.action').click(function(){
				var id = $(this).parent('tr').attr('id');
				var action = $(this).attr('action');
				var mclass='glyphicon-ok';
				var value=0;
				
				if ($('#'+id+' td[action="'+action+'"] img').is(':visible')) {return false;};
				if ($('#'+id+' td[action="'+action+'"] span').hasClass('glyphicon-ok')) {
					mclass='glyphicon-ban-circle';
					value=0;
				} else value=1;
				$('#'+id+' td[action="'+action+'"]').html('<img src="media/com_emundus/images/icones/loading.gif"></img>');
				
				$.ajax(
					{
						type:'post', 
						url:'<?php echo JRoute::_('index.php?option=com_emundus&controller=users&task=setgrouprights&format=raw', true); ?>', 
						dataType:'json', 
						data:{
							id:$(this).parent('tr').attr('id'), 
							action:$(this).attr('action'),
							value:value
						},
						success:function(result){ 
						 if(result.status)
							$('#'+id+' td[action="'+action+'"]').html('<span class="glyphicon '+mclass+'" style="color: #01ADE3"></span>')
						},
						error:function(jqXHR, textStatus, errorThrown){
							$('#'+id+' td[action="'+action+'"]').html('')
						}
					}
				)
			})
		}
	);
</script>
