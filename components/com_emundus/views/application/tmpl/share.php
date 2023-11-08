<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 20/01/15
 * Time: 17:51
 */ ?>

<?php if (!empty($this->access['groups'])): ?>
    <div class="row">
        <div class="panel panel-default widget em-container-share">
            <div class="panel-heading em-container-share-heading">
                <h3 class="panel-title">
                    <span class="material-icons">visibility</span>
					<?= JText::_('COM_EMUNDUS_ACCESS_CHECK_ACL'); ?>
                </h3>
                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><span
                                class="material-icons">arrow_back</span></button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><span
                                class="material-icons">arrow_forward</span></button>
                </div>
            </div>
            <div class="panel-body em-container-share-body">
                <div class="active content em-container-share-table">
                    <div class="col-md-2 table-left em-container-share-table-left">
                        <table class="table table-bordered" id="groups-table">
                            <thead>
                            <tr>
                                <th></th>
                            </tr>
                            <tr>
                                <th><?= JText::_('COM_EMUNDUS_ACCESS_GROUPS') ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php foreach ($this->access['groups'] as $gid => $groups) : ?>
                                <tr>
                                    <td>
										<?= $groups['gname'] ?>
										<?php if ($groups['isAssoc'] && EmundusHelperAccess::asAccessAction(11, 'd', $this->_user->id, $this->fnum)) : ?>
											<?php if ($groups['isACL']): ?>
                                                <a class="btn btn-info btn-xs pull-right em-del-access"
                                                   href="index.php?option=com_emundus&controller=application&task=deleteaccess&fnum=<?= $this->fnum ?>&id=<?= $gid ?>&type=groups">
                                                    <span class="glyphicon glyphicon-retweet"></span>
                                                </a>
											<?php else : ?>
                                                <a class="btn btn-danger btn-xs pull-right em-del-access"
                                                   href="index.php?option=com_emundus&controller=application&task=deleteaccess&fnum=<?= $this->fnum ?>&id=<?= $gid ?>&type=groups">
                                                    <span class="glyphicon glyphicon-remove"></span>
                                                </a>
											<?php endif; ?>
										<?php endif; ?>
                                    </td>
                                </tr>
							<?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="access-table col-md-10 table-right em-container-share-table-right">
                        <table class="table table-bordered" id="groups-access-table">
                            <thead>
                            <tr>
								<?php foreach ($this->access['groups'] as $gid => $groups) : ?>
									<?php foreach ($groups['actions'] as $aid => $action) : ?>
                                        <th colspan="4" id="<?= $aid ?>">
											<?= JText::_($action['aname']) ?>
                                        </th>
									<?php endforeach; ?>
									<?php break;
								endforeach; ?>
                            </tr>
                            <tr>
								<?php foreach ($this->access['groups'] as $gid => $groups) : ?>
									<?php foreach ($groups['actions'] as $actions) : ?>
                                        <th><?= JText::_('COM_EMUNDUS_ACCESS_CREATE') ?></th>
                                        <th><?= JText::_('COM_EMUNDUS_ACCESS_RETRIEVE') ?></th>
                                        <th><?= JText::_('COM_EMUNDUS_ACCESS_UPDATE') ?></th>
                                        <th><?= JText::_('COM_EMUNDUS_ACTIONS_DELETE') ?></th>
									<?php endforeach; ?>
									<?php break; endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
							<?php foreach ($this->access['groups'] as $gid => $groups): ?>
                                <tr>
									<?php foreach ($groups['actions'] as $aid => $actions): ?>
										<?php if ($this->defaultActions[$aid]['c'] == 1): ?>
                                            <td class="<?php if ($this->canUpdate) {
												echo "can-update";
											} ?>" id="<?= $gid . '-' . $aid . '-c' ?>" state="<?= $actions['c'] ?>">
												<?php if ($actions['c'] > 0): ?>
                                                    <span class="glyphicon glyphicon-ok green"
                                                          title="<?= JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') ?>"></span>
												<?php elseif ($actions['c'] < 0): ?>
                                                    <span class="glyphicon glyphicon-ban-circle red "
                                                          title="<?= JText::_('BLOCKED') ?>"></span>
												<?php else: ?>
                                                    <span class="glyphicon glyphicon-unchecked"
                                                          title="<?= JText::_('UNDEFINED') ?>"></span>
												<?php endif ?>
                                            </td>
										<?php else: ?>
                                            <td></td>
										<?php endif; ?>
										<?php if ($this->defaultActions[$aid]['r'] == 1): ?>
                                            <td class="<?php if ($this->canUpdate) {
												echo "can-update";
											} ?>" id="<?= $gid . '-' . $aid . '-r' ?>" state="<?= $actions['r'] ?>">
												<?php if ($actions['r'] > 0): ?>
                                                    <span class="glyphicon glyphicon-ok green"
                                                          title="<?= JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') ?>"></span>
												<?php elseif ($actions['r'] < 0): ?>
                                                    <span class="glyphicon glyphicon-ban-circle red "
                                                          title="<?= JText::_('BLOCKED') ?>"></span>
												<?php else: ?>
                                                    <span class="glyphicon glyphicon-unchecked"
                                                          title="<?= JText::_('UNDEFINED') ?>"></span>
												<?php endif ?>
                                            </td>
										<?php else: ?>
                                            <td></td>
										<?php endif; ?>
										<?php if ($this->defaultActions[$aid]['u'] == 1): ?>
                                            <td class="<?php if ($this->canUpdate) {
												echo "can-update";
											} ?>" id="<?= $gid . '-' . $aid . '-u' ?>" state="<?= $actions['u'] ?>">
												<?php if ($actions['u'] > 0): ?>
                                                    <span class="glyphicon glyphicon-ok green"
                                                          title="<?= JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') ?>"></span>
												<?php elseif ($actions['u'] < 0): ?>
                                                    <span class="glyphicon glyphicon-ban-circle red "
                                                          title="<?= JText::_('BLOCKED') ?>"></span>
												<?php else: ?>
                                                    <span class="glyphicon glyphicon-unchecked"
                                                          title="<?= JText::_('UNDEFINED') ?>"></span>
												<?php endif ?>
                                            </td>
										<?php else: ?>
                                            <td></td>
										<?php endif; ?>
										<?php if ($this->defaultActions[$aid]['d'] == 1): ?>
                                            <td class="<?php if ($this->canUpdate) {
												echo "can-update";
											} ?>" id="<?= $gid . '-' . $aid . '-d' ?>" state="<?= $actions['d'] ?>">
												<?php if ($actions['d'] > 0): ?>
                                                    <span class="glyphicon glyphicon-ok green"
                                                          title="<?= JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') ?>"></span>
												<?php elseif ($actions['d'] < 0): ?>
                                                    <span class="glyphicon glyphicon-ban-circle red "
                                                          title="<?= JText::_('BLOCKED') ?>"></span>
												<?php else: ?>
                                                    <span class="glyphicon glyphicon-unchecked"
                                                          title="<?= JText::_('UNDEFINED') ?>"></span>
												<?php endif ?>
                                            </td>
										<?php else: ?>
                                            <td></td>
										<?php endif; ?>
									<?php endforeach; ?>
                                </tr>
							<?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (!empty($this->access['users'])): ?>
    <div class="row" style="display: flex;">
        <div class="table-left em-container-share-table-left">
            <table class="table table-bordered" id="users-table">
                <thead>
                <tr>
                    <th></th>
                </tr>
                <tr>
                    <th><?= JText::_('COM_EMUNDUS_ACCESS_USER') ?></th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ($this->access['users'] as $gid => $groups): ?>
                    <tr>
                        <td>
							<?= ucfirst($groups['uname']) ?>
							<?php if (EmundusHelperAccess::asAccessAction(11, 'd', $this->_user->id, $this->fnum)): ?>
                                <a class="btn btn-danger btn-xs pull-right em-del-access"
                                   href="/index.php?option=com_emundus&controller=application&task=deleteaccess&fnum=<?= $this->fnum ?>&id=<?= $gid ?>&type=users">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </a>
							<?php endif; ?>
                        </td>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="access-table col-md-10 table-right em-container-share-table-right">
            <table class="table table-bordered" id="users-access-table">
                <thead>
                <tr>
					<?php foreach ($this->access['users'] as $gid => $groups): ?>
						<?php foreach ($groups['actions'] as $aid => $action): ?>
                            <th colspan="4" id="<?= $aid ?>">
								<?= JText::_($action['aname']) ?>
                            </th>
						<?php endforeach; ?>
						<?php break; endforeach; ?>
                </tr>
                <tr>
					<?php foreach ($this->access['users'] as $gid => $groups): ?>
						<?php foreach ($groups['actions'] as $actions): ?>
                            <th><?= JText::_('COM_EMUNDUS_ACCESS_CREATE') ?></th>
                            <th><?= JText::_('COM_EMUNDUS_ACCESS_RETRIEVE') ?></th>
                            <th><?= JText::_('COM_EMUNDUS_ACCESS_UPDATE') ?></th>
                            <th><?= JText::_('COM_EMUNDUS_ACTIONS_DELETE') ?></th>
						<?php endforeach; ?>
						<?php break; endforeach; ?>
                </tr>
                </thead>
                <tbody>
				<?php foreach ($this->access['users'] as $gid => $groups): ?>
                    <tr>
						<?php foreach ($groups['actions'] as $aid => $actions): ?>
							<?php if ($this->defaultActions[$aid]['c'] == 1): ?>
                                <td class="<?php if ($this->canUpdate) {
									echo "can-update";
								} ?>" id="<?= $gid . '-' . $aid . '-c' ?>" state="<?= $actions['c'] ?>">
									<?php if ($actions['c'] > 0): ?>
                                        <span class="glyphicon glyphicon-ok green"
                                              title="<?= JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') ?>"></span>
									<?php elseif ($actions['c'] < 0): ?>
                                        <span class="glyphicon glyphicon-ban-circle red "
                                              title="<?= JText::_('BLOCKED') ?>"></span>
									<?php else: ?>
                                        <span class="glyphicon glyphicon-unchecked"
                                              title="<?= JText::_('UNDEFINED') ?>"></span>
									<?php endif ?>
                                </td>
							<?php else: ?>
                                <td></td>
							<?php endif; ?>
							<?php if ($this->defaultActions[$aid]['r'] == 1): ?>
                                <td class="<?php if ($this->canUpdate) {
									echo "can-update";
								} ?>" id="<?= $gid . '-' . $aid . '-r' ?>" state="<?= $actions['r'] ?>">

									<?php if ($actions['r'] > 0): ?>
                                        <span class="glyphicon glyphicon-ok green"
                                              title="<?= JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') ?>"></span>
									<?php elseif ($actions['r'] < 0): ?>
                                        <span class="glyphicon glyphicon-ban-circle red "
                                              title="<?= JText::_('BLOCKED') ?>"></span>
									<?php else: ?>
                                        <span class="glyphicon glyphicon-unchecked"
                                              title="<?= JText::_('UNDEFINED') ?>"></span>
									<?php endif ?>
                                </td>
							<?php else: ?>
                                <td></td>
							<?php endif; ?>
							<?php if ($this->defaultActions[$aid]['u'] == 1): ?>
                                <td class="<?php if ($this->canUpdate) {
									echo "can-update";
								} ?>" id="<?= $gid . '-' . $aid . '-u' ?>" state="<?= $actions['u'] ?>">

									<?php if ($actions['u'] > 0): ?>
                                        <span class="glyphicon glyphicon-ok green"
                                              title="<?= JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') ?>"></span>
									<?php elseif ($actions['u'] < 0): ?>
                                        <span class="glyphicon glyphicon-ban-circle red "
                                              title="<?= JText::_('BLOCKED') ?>"></span>
									<?php else: ?>
                                        <span class="glyphicon glyphicon-unchecked"
                                              title="<?= JText::_('UNDEFINED') ?>"></span>
									<?php endif ?>
                                </td>
							<?php else: ?>
                                <td></td>
							<?php endif; ?>
							<?php if ($this->defaultActions[$aid]['d'] == 1): ?>
                                <td class="<?php if ($this->canUpdate) {
									echo "can-update";
								} ?>" id="<?= $gid . '-' . $aid . '-d' ?>" state="<?= $actions['d'] ?>">
									<?php if ($actions['d'] > 0): ?>
                                        <span class="glyphicon glyphicon-ok green"
                                              title="<?= JText::_('COM_EMUNDUS_ACTIONS_ACTIVE') ?>"></span>
									<?php elseif ($actions['d'] < 0): ?>
                                        <span class="glyphicon glyphicon-ban-circle red "
                                              title="<?= JText::_('BLOCKED') ?>"></span>
									<?php else: ?>
                                        <span class="glyphicon glyphicon-unchecked"
                                              title="<?= JText::_('UNDEFINED') ?>"></span>
									<?php endif ?>
                                </td>
							<?php else: ?>
                                <td></td>
							<?php endif; ?>

						<?php endforeach; ?>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    var fnum = "<?= $this->fnum?>";
    glyphArray = ["glyphicon glyphicon-ban-circle red", "glyphicon glyphicon-unchecked", "glyphicon glyphicon-ok green"];
    $(document).off('click', '.table-right td.can-update');
    $(document).on('click', '.table-right td.can-update', function (e) {
        if (e.handle !== true) {
            e.handle = true;
            var state = parseInt($(this).attr('state'));
            var index = state + 1;
            if (state < 0) {
                state = 0;
                index = 1;

            } else {
                state++;
                index++;
            }
            if (state > 1) {
                state = -2;
                index = 0;
            }
            $(this).children('span').removeClass();
            $(this).children('span').addClass("glyphicon glyphicon-refresh");
            var type = $(this).parents('table').attr('id').split('-');
            var accessId = $(this).attr('id');
            $.ajax({
                type: 'post',
                url: '/index.php?option=com_emundus&controller=application&task=updateaccess',
                dataType: 'json',
                data: {access_id: $(this).attr('id'), fnum: fnum, state: state, type: type[0]},
                success: function (result) {
                    if (result.status) {
                        $("#" + accessId).children('span').removeClass();
                        $("#" + accessId).children('span').addClass(glyphArray[index]);
                        $("#" + accessId).attr("state", state);
                    } else {
                        $("#" + accessId).children('span').removeClass();
                        state--;
                        index--;
                        if (state < 0) {
                            state = -2;
                            index = 0;
                        }
                        if (state < -2) {
                            state = 1;
                            index = 2;
                        }
                        $("#" + accessId).children('span').addClass(glyphArray[index]);
                        $("#" + accessId).attr("state", state);
                        alert(result.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR.responseText);
                }
            })
        }
    });

    $(document).off('click', '.em-del-access');
    $(document).on('click', '.em-del-access', function (e) {
        e.preventDefault();
        if (e.handle !== true) {
            e.handle = true;
            var r = confirm("<?= JText::_("COM_EMUNDUS_ACCESS_ARE_YOU_SURE_YOU_WANT_TO_REMOVE_THIS_ACCESS")?>");
            if (r) {
                var url = $(this).attr('href');
                $.ajax({
                    type: 'post',
                    url: url,
                    dataType: 'json',
                    success: function (result) {
                        if (result.status) {
                            var url = "index.php?option=com_emundus&view=application&format=raw&layout=share&fnum=<?= $this->fnum; ?>";

                            $.ajax({
                                type: "get",
                                url: url,
                                dataType: 'html',
                                success: function (result) {
                                    $('#em-appli-block').empty();
                                    $('#em-appli-block').append(result);
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    console.log(jqXHR.responseText);
                                }
                            });

                            //$('#em-appli-menu .list-group-item#1410').trigger('click');
                        } else {
                            alert(result.msg);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                    }
                });
            }
        }
    });
</script>
