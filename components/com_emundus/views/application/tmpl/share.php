<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 20/01/15
 * Time: 17:51
 */?>

<?php if(!empty($this->access['groups'])):?>
	<div class="row">
        <div class="panel panel-default widget em-container-share">
            <div class="panel-heading em-container-share-heading">
                <h3 class="panel-title">
                	<span class="material-icons">visibility</span>
                	<?= JText::_('COM_EMUNDUS_ACCESS_CHECK_ACL'); ?>
                </h3>
                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_back</span></button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_forward</span></button>
                </div>
            </div>
            <div class="panel-body em-container-share-body">
                <div class="active content em-container-share-table em-flex-row">
                    <div class="table-left em-container-share-table-left">
                        <table id="groups-table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th></th>
                            </tr>
                            <tr>
                                <th><?= JText::_('COM_EMUNDUS_ACCESS_GROUPS')?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($this->access['groups'] as $gid => $groups) :?>
                                <tr>
                                    <td class="em-flex-row em-flex-space-between">
                                        <span><?= $groups['gname']?></span>
                                        <?php if ($groups['isAssoc'] && EmundusHelperAccess::asAccessAction(11, 'd', $this->_user->id, $this->fnum)) :?>
                                            <?php if($groups['isACL']):?>
                                                <a class="em-flex-row em-del-access" href="index.php?option=com_emundus&controller=application&task=deleteaccess&fnum=<?= $this->fnum ?>&id=<?= $gid ?>&type=groups">
                                                    <span class="material-icons-outlined">autorenew</span>
                                                </a>
                                            <?php else :?>
                                                <a class="em-flex-row em-del-access" href="index.php?option=com_emundus&controller=application&task=deleteaccess&fnum=<?= $this->fnum ?>&id=<?= $gid ?>&type=groups">
                                                    <span class="material-icons-outlined">close</span>
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="access-table table-right em-container-share-table-right">
                        <table class="table table-bordered" id="groups-access-table">
                            <thead>
                            <tr>
                                <?php foreach ($this->access['groups'] as $gid => $groups) :?>
                                    <?php foreach ($groups['actions'] as $aid => $action) :?>
                                        <th colspan="4" id="<?= $aid?>">
                                            <?= JText::_($action['aname'])?>
                                        </th>
                                    <?php endforeach;?>
                                    <?php break;
                                    endforeach;?>
                            </tr>
                            <tr>
                                <?php foreach($this->access['groups'] as $gid => $groups) :?>
                                    <?php foreach($groups['actions'] as $actions) :?>
                                        <th><?= JText::_('COM_EMUNDUS_ACCESS_CREATE')?></th>
                                        <th><?= JText::_('COM_EMUNDUS_ACCESS_RETRIEVE')?></th>
                                        <th><?= JText::_('COM_EMUNDUS_ACCESS_UPDATE')?></th>
                                        <th><?= JText::_('COM_EMUNDUS_ACTIONS_DELETE')?></th>
                                    <?php endforeach;?>
                                    <?php break; endforeach;?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($this->access['groups'] as $gid => $groups):?>
                                <tr>
                                    <?php
                                        $cruds = ['c', 'r', 'u', 'd'];
                                        foreach($this->defaultActions as $def_action_id => $default_action) {
                                            if ($default_action['status'] == 1) {
                                                foreach($cruds as $crud) {
                                                    $td = '';
                                                    if ($default_action[$crud] == 1) {
                                                        if ($this->canUpdate) {
                                                            $td .= '<td class="can-update" id="' . $gid . '-' . $def_action_id . '-' . $crud . '" state="' . $groups['actions'][$def_action_id][$crud] . '">';
                                                        } else {
                                                            $td .= '<td id="' . $gid . '-' . $def_action_id . '-' . $crud . '" state="' . $groups['actions'][$def_action_id][$crud] . '">';
                                                        }

                                                        if ($groups['actions'][$def_action_id][$crud] > 0) {
                                                            $td .= '<span class="material-icons-outlined em-green-500-color" title="' . JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') . '">check_box</span>';
                                                        } else if ($groups['actions'][$def_action_id][$crud] < 0) {
                                                            $td .= '<span class="material-icons-outlined em-red-500-color" title="' . JText::_('BLOCKED') . '">block</span>';
                                                        } else {
                                                            $td .= '<span class="material-icons-outlined" title="' . JText::_('UNDEFINED') . '">check_box_outline_blank</span>';
                                                        }
                                                        $td .= '</td>';

                                                    } else {
                                                        $td = '<td id="' . $gid . '-' . $def_action_id . '-' . $crud . '"></td>';
                                                    }

                                                    echo $td;
                                                }

                                            }
                                        }
                                    ?>
                                </tr>
                            <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
	</div>
<?php endif;?>
<?php if(!empty($this->access['users'])):?>
	<div class="row em-w-100 em-p-16" style="display: flex">
		<div class="table-left em-container-share-table-left">
			<table class="table table-bordered" id="users-table">
				<thead>
				<tr>
					<th></th>
				</tr>
				<tr>
					<th><?= JText::_('COM_EMUNDUS_ACCESS_USER')?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach($this->access['users'] as $gid => $groups):?>
					<tr>
						<td class="em-flex-row em-flex-space-between">
							<span><?= ucfirst($groups['uname']) ?></span>
							<?php if(EmundusHelperAccess::asAccessAction(11, 'd', $this->_user->id, $this->fnum)):?>
								<a class="em-flex-row em-del-access" href = "/index.php?option=com_emundus&controller=application&task=deleteaccess&fnum=<?= $this->fnum ?>&id=<?= $gid ?>&type=users">
									<span class="material-icons-outlined">close</span>
								</a>
							<?php endif;?>
						</td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</div>
		<div class="access-table table-right em-container-share-table-right">
			<table class="table table-bordered" id="users-access-table" >
				<thead>
				<tr>
					<?php foreach($this->access['users'] as $gid => $groups):?>
						<?php foreach($groups['actions'] as $aid => $action):?>
							<th colspan="4" id="<?= $aid?>">
								<?= JText::_($action['aname'])?>
							</th>
						<?php endforeach;?>
						<?php break; endforeach;?>
				</tr>
				<tr>
					<?php foreach($this->access['users'] as $gid => $groups):?>
						<?php foreach($groups['actions'] as $actions):?>
							<th><?= JText::_('COM_EMUNDUS_ACCESS_CREATE')?></th>
							<th><?= JText::_('COM_EMUNDUS_ACCESS_RETRIEVE')?></th>
							<th><?= JText::_('COM_EMUNDUS_ACCESS_UPDATE')?></th>
							<th><?= JText::_('COM_EMUNDUS_ACTIONS_DELETE')?></th>
						<?php endforeach;?>

                        <?php break; endforeach;?>
				</tr>
				</thead>
				<tbody>
				<?php foreach($this->access['users'] as $gid => $groups):?>
					<tr>
                        <?php
                        $cruds = ['c', 'r', 'u', 'd'];
                        foreach($this->defaultActions as $def_action_id => $default_action) {
                            foreach($cruds as $crud) {
                                $td = '';
                                if ($default_action['status'] == 1) {
                                    if ($default_action[$crud] == 1) {
                                        if ($this->canUpdate) {
                                            $td .= '<td class="can-update" id="' . $gid . '-' . $def_action_id . '-' . $crud . '" state="' . $groups['actions'][$def_action_id][$crud] . '">';
                                        } else {
                                            $td .= '<td id="' . $gid . '-' . $def_action_id . '-' . $crud . '" state="' . $groups['actions'][$def_action_id][$crud] . '">';
                                        }

                                        if ($groups['actions'][$def_action_id][$crud] > 0) {
                                            $td .= '<span class="material-icons-outlined em-green-500-color" title="' . JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') . '">check_box</span>';
                                        } else if ($groups['actions'][$def_action_id][$crud] < 0) {
                                            $td .= '<span class="material-icons-outlined em-red-500-color" title="' . JText::_('BLOCKED') . '">block</span>';
                                        } else {
                                            $td .= '<span class="material-icons-outlined" title="' . JText::_('UNDEFINED') . '">check_box_outline_blank</span>';
                                        }
                                        $td .= '</td>';

                                    } else {
                                        $td = '<td id="' . $gid . '-' . $def_action_id . '-' . $crud . '"></td>';
                                    }
                                }

                                echo $td;
                            }
                        }
                        ?>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</div>
	</div>
<?php endif;?>

<script type="text/javascript">
	const fnum = "<?= $this->fnum?>";
	const iconArray = [
        {icon: "block", class: "em-red-500-color"},
        {icon: "check_box_outline_blank", class: ""},
        {icon: "check_box", class: "em-green-500-color"},
    ];
	$(document).off('click', '.table-right td.can-update');
	$(document).on('click', '.table-right td.can-update', function(e)
	{
		if(e.handle !== true)
		{
			e.handle = true;
			var state = parseInt($(this).attr('state'));
			var index = state + 1;
			if(state < 0)
			{
				state = 0;
				index = 1;

			}
			else
			{
				state++;
				index++;
			}
			if(state > 1)
			{
				state = -2;
				index = 0;
			}
            // set inner text  of span icon refresh
			$(this).children('span').text('refresh');
			let type = $(this).parents('table').attr('id').split('-');
			let accessId = $(this).attr('id');
            $.ajax({
                type:'post',
                url:'/index.php?option=com_emundus&controller=application&task=updateaccess',
                dataType:'json',
                data:{access_id: $(this).attr('id'), fnum:fnum, state: state, type: type[0]},
                success: function(result)
                {
                    const element = document.getElementById(accessId)
                    const span = element.querySelector('span');

                    // remove all classes that are not material-icons-outlined
                    span.classList.forEach((className) => {
                        if (className !== 'material-icons-outlined') {
                            span.classList.remove(className);
                        }
                    });

                    if(result.status)
                    {
                        span.innerText = iconArray[index].icon;
                        if (iconArray[index].class !== '') {
                            span.classList.add(iconArray[index].class);
                        }
                        element.setAttribute("state", state);
                    }
                    else
                    {
                        state--;
                        index--;
                        if(state < 0)
                        {
                            state = -2;
                            index = 0;
                        }
                        if(state < -2)
                        {
                            state = 1;
                            index = 2;
                        }
                        span.innerText = iconArray[index].icon;
                        if (iconArray[index].class !== '') {
                            span.classList.add(iconArray[index].class);
                        }
                        element.setAttribute("state", state);
                        alert(result.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    console.log(jqXHR.responseText);
                }
            })
		}
	});

	$(document).off('click', '.em-del-access');
	$(document).on('click', '.em-del-access', function(e)
	{
		e.preventDefault();
		if(e.handle !== true)
		{
			e.handle = true;
			let r = confirm("<?= JText::_("COM_EMUNDUS_ACCESS_ARE_YOU_SURE_YOU_WANT_TO_REMOVE_THIS_ACCESS")?>");

            if(r) {
                $.ajax({
                    type: 'post',
                    url: $(this).attr('href'),
                    dataType:'json',
                    success: function(result)
                    {
                        if(result.status) {
                            const url = "index.php?option=com_emundus&view=application&format=raw&layout=share&fnum=<?= $this->fnum; ?>";

                            $.ajax({
                                type: "get",
                                url: url,
                                dataType: 'html',
                                success: function(result)
                                {
                                    $('#em-appli-block').empty();
                                    $('#em-appli-block').append(result);
                                },
                                error: function (jqXHR, textStatus, errorThrown)
                                {
                                    console.log(jqXHR.responseText);
                                }
                            });

                        } else {
                            alert(result.msg);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        console.log(jqXHR.responseText);
                    }
                });
			}
		}
	});
</script>

<style>
    #groups-access-table, #users-access-table {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .table-left table {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-left: 1px solid #dddddd;
    }

    #groups-access-table td, #groups-access-table th, #users-access-table td, #users-access-table th {
        text-align: center;
        vertical-align: middle;
    }

    #groups-access-table thead tr:nth-child(1) th:not(:last-child), #users-access-table thead tr:nth-child(1) th:not(:last-child) {
        border-right: 1px solid #dddddd;
    }

    #groups-access-table thead tr:nth-child(2) th:nth-child(4n):not(:last-child), #users-access-table thead tr:nth-child(2) th:nth-child(4n):not(:last-child) {
        border-right: 1px solid #dddddd;
    }

    #groups-access-table tbody tr td:nth-child(4n):not(:last-child), #users-access-table tbody tr td:nth-child(4n):not(:last-child) {
        border-right: 1px solid #dddddd;
    }
</style>
