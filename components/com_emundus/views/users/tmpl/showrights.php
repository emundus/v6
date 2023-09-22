<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 19/09/14
 * Time: 17:14
 */
?>
<style>
    form {
        margin: 0;
    }
</style>
<form action = "index.php?option=com_emundus&controller=users&task=addgroup" id="em-add-group" class="em-add-group" role="form" method="post">
	<?php
	if(!empty($this->groups)) {
		echo JText::_('COM_EMUNDUS_GROUPS_NO_GROUP');
	} else {
	?>
		<?php foreach($this->groups as $k => $g):?>
			<fieldset id="<?php echo $k?>" class="em-add-group-right">
				<h3>
					<?php echo $g['label']?>
				</h3>

                <?php if (!empty($g['progs'])) : ?>
                    <div class="em-add-group-program mt-3">
                        <h4 class="mb-2"><?php echo JText::_('COM_EMUNDUS_GROUPS_PROGRAM')?></h4>
                        <?php foreach($g['progs'] as $p):?>
                            <p><?php echo $p['label']?></p>
                        <?php endforeach;?>
                    </div>
                <?php endif; ?>

				<?php if(!empty($g['acl'])):?>
					<table id="em-modal-action-table" class="table table-hover em-add-group-right-table" style="color:black !important;">
						<thead>
						<tr>
							<th></th>
							<th>
								<label for="c-check-all"><?php echo JText::_('COM_EMUNDUS_ACCESS_CREATE')?></label>
							</th>
							<th>
								<label for="r-check-all"><?php echo JText::_('COM_EMUNDUS_ACCESS_RETRIEVE')?></label>
							</th>
							<th>
								<label for="u-check-all"><?php echo JText::_('COM_EMUNDUS_ACCESS_UPDATE')?></label>
							</th>
							<th>
								<label for="d-check-all"><?php echo JText::_('COM_EMUNDUS_ACTIONS_DELETE')?></label>
							</th>
						</tr>
						</thead>
						<tbody size="<?php echo count($this->actions)?>">
						<?php foreach($g['acl'] as $l => $action):?>

							<tr class="em-actions-table-line">
								<td  id="<?php echo $action['id']?>"><?php echo JText::_(strtoupper($action['label']))?></td>
								<td>
									<?php if($action['c'] == 1): ?>
										<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
									<?php else:?>
										<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
									<?php endif?>
								</td>
								<td>
									<?php if($action['r'] == 1): ?>
										<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
									<?php else:?>
										<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
									<?php endif?>
								</td>
								<td>
									<?php if($action['u'] == 1): ?>
										<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
									<?php else:?>
										<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
									<?php endif?>
								</td>
								<td>
									<?php if($action['d'] == 1): ?>
										<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
									<?php else:?>
										<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
									<?php endif?>
								</td>

							</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				<?php endif;?>
			</fieldset>
		<?php endforeach;?>
	<?php };?>

    <?php
echo '<script type="text/javascript">
	$(document).ready(function() {
	    $("#can-val").hide();
	});
	
	$(document).on("click", ".close", function() {
	    $("#can-val").show();
	});
</script>'
    ?>

</form>

