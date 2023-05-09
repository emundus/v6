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

<h3><?= JText::_('COM_EMUNDUS_GROUPS_SHOW_RIGHTS'); ?></h3>
<?php foreach($this->groups as $k => $g) :?>
	<fieldset id="<?= $k; ?>" class="em-showgroupright">
		<h5>
			<?= $g['label']; ?>
		</h5>
        <?php if (!empty($g['progs'])) :?>
            <ul class="em-showgroupright-program">
                <strong><?= JText::_('COM_EMUNDUS_GROUPS_PROGRAM'); ?></strong>
                <?php foreach ($g['progs'] as $p) :?>
                    <li><?= $p['label']; ?></li>
                <?php endforeach;?>
            </ul>
        <?php endif; ?>
		<?php if (!empty($g['acl'])) :?>
			<table id="em-modal-action-table" class="table table-hover em-showgroupright-table" style="color:black !important;">
				<thead>
				<tr>
					<th></th>
					<th>
						<label for="c-check-all"><?= JText::_('COM_EMUNDUS_ACCESS_CREATE'); ?></label>
					</th>
					<th>
						<label for="r-check-all"><?= JText::_('COM_EMUNDUS_ACCESS_RETRIEVE'); ?></label>
					</th>
					<th>
						<label for="u-check-all"><?= JText::_('COM_EMUNDUS_ACCESS_UPDATE'); ?></label>
					</th>
					<th>
						<label for="d-check-all"><?= JText::_('COM_EMUNDUS_ACTIONS_DELETE'); ?></label>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ($g['acl'] as $l => $action) :?>

					<tr class="em-actions-table-line" id="<?= $action['id']; ?>">
						<td  id="<?= $action['id']; ?>"><?= JText::_(strtoupper($action['label'])); ?></td>
						<?php if ($action['is_c'] == 1) :?>
							<td action="c" class="action">
								<?php if ($action['c'] == 1) :?>
									<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
								<?php else :?>
									<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
								<?php endif; ?>
							</td>
						<?php else :?>
                            <td></td>
                        <?php endif; ?>

						<?php if ($action['is_r'] == 1) :?>
							<td action="r" class="action">
								<?php if ($action['r'] == 1) :?>
									<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
								<?php else :?>
									<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
								<?php endif; ?>
							</td>
						<?php else :?>
                            <td></td>
                        <?php endif; ?>

						<?php if ($action['is_u'] == 1) :?>
							<td action="u" class="action">
								<?php if ($action['u'] == 1) :?>
									<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
								<?php else :?>
									<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
								<?php endif; ?>
							</td>
						<?php else: ?>
                            <td></td>
                        <?php endif; ?>

						<?php if ($action['is_d'] == 1) :?>
							<td action="d" class="action">
								<?php if ($action['d'] == 1) :?>
									<span class="glyphicon glyphicon-ok" style="color: #00c500"></span>
								<?php else :?>
									<span class="glyphicon glyphicon-ban-circle" style="color: #ff0000"></span>
								<?php endif; ?>
							</td>
						<?php else :?>
                            <td></td>
                        <?php endif; ?>

					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php if (!empty($this->users)) :?>
			<hr>
			<ul>
			<strong><?= JText::_('COM_EMUNDUS_USERS_GROUP'); ?></strong>
			<?php foreach ($this->users as $user) :?>
				<li><a href="#"><?= ucwords($user['firstname']).' '.strtoupper($user['lastname']); ?></a></li>
			<?php endforeach;?>
			</ul>
		<?php endif;?>
        <div class="modal-footer">
            <button type="button" class="btn btn-success" onclick="history.go(-1)"><?php echo JText::_('COM_EMUNDUS_OK');?></button>
        </div>
	</fieldset>
<?php endforeach;?>

<script type="text/javascript">
var itemId = <?= $this->itemId; ?>;
$(document).ready(function () {
    $('.action').click(function() {
        var id = $(this).parent('tr').attr('id');
        var action = $(this).attr('action');
        var mclass='glyphicon-ok';
        var value=0;

        if ($('#'+id+' td[action="'+action+'"] img').is(':visible')) {return false;}
        if ($('#'+id+' td[action="'+action+'"] span').hasClass('glyphicon-ok')) {
            mclass='glyphicon-ban-circle';
            value=0;
        } else value=1;
        $('#'+id+' td[action="'+action+'"]').html('<img src="media/com_emundus/images/icones/loading.gif"></img>');

        $.ajax({
            type:'post',
            url:'<?php echo JRoute::_('index.php?option=com_emundus&controller=users&task=setgrouprights&format=raw', true); ?>',
            dataType:'json',
            data: {
                id:$(this).parent('tr').attr('id'),
                action:$(this).attr('action'),
                value:value
            },
            success: function(result) {
             if(result.status)
                $('#'+id+' td[action="'+action+'"]').html('<span class="glyphicon '+mclass+'" style="color: #01ADE3"></span>')
            },
            error: function() {
                $('#'+id+' td[action="'+action+'"]').html('')
            }
        })
    })
});
</script>
