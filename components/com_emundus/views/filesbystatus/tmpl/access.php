<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 22/11/14
 * Time: 17:36
 */ ?>


<div>
    <div>
		<?php foreach ($this->users as $user): ?>
            <h4 title="<?php echo $user['fnum'] ?>"><?php echo $user['name'] ?> (<?php echo $user['label'] ?>
                - <?php echo $user['year'] ?>)</h4>
			<?php if (!empty($this->groupFnum[$user['fnum']])): ?>
                <strong><?php echo JText::_('COM_EMUNDUS_GROUPS') ?></strong>
                <ul>
					<?php
					if (!is_array($this->groupFnum[$user['fnum']])) {
						$groups = explode(',', $this->groupFnum[$user['fnum']]);
						foreach ($groups as $g):?>
                            <li>
								<?php echo $g ?>
                            </li>
						<?php endforeach; ?>
					<?php } ?>
                </ul>
			<?php endif; ?>
			<?php if (!empty($this->evalFnum[$user['fnum']])): ?>
                <strong><?php echo JText::_('COM_EMUNDUS_ASSESSORS') ?></strong>
                <ul>
					<?php
					if (!is_array($this->evalFnum[$user['fnum']])) {
						$assessors = explode(',', $this->evalFnum[$user['fnum']]);
						foreach ($assessors as $a):?>
                            <li>
								<?php echo $a ?>
                            </li>
						<?php endforeach; ?>
					<?php } ?>
                </ul>
			<?php endif; ?>
		<?php endforeach; ?>
    </div>

    <div class="panel panel-info em-access-filesbystatus">
        <div class="panel-heading em-access-filesbystatus-heading">
			<?php echo JText::_('COM_EMUNDUS_ACCESS_CHECK_ACL') ?>
        </div>
        <div class="form-group em-access-filesbystatus-form" style="color:black !important">
            <label class="col-lg-2 control-label"><?php echo JText::_('COM_EMUNDUS_GROUPS') ?></label>
            <select class="col-lg-7 modal-chzn-select" multiple="true"
                    data-placeholder="<?php echo JText::_('COM_EMUNDUS_GROUPS_PLEASE_SELECT_GROUP') ?>"
                    name="em-access-groups-eval" id="em-access-groups-eval" value="">
				<?php foreach ($this->groups as $group): ?>
                    <option value="<?php echo $group['id'] ?>"><?php echo $group['label'] ?></option>
				<?php endforeach; ?>
            </select>

            <label class="col-lg-2 control-label"><?php echo JText::_('COM_EMUNDUS_EVALUATION_EVALUATORS') ?></label><select
                    class="col-lg-7 modal-chzn-select" multiple="true"
                    data-placeholder="<?php echo JText::_('COM_EMUNDUS_GROUPS_PLEASE_SELECT_ASSESSOR') ?>"
                    name="em-access-evals" id="em-access-evals" value="">
				<?php foreach ($this->evals as $eval): ?>
                    <option value="<?php echo $eval['user_id'] ?>"><?php echo $eval['name'] ?></option>
				<?php endforeach; ?>
            </select>
        </div>
        <div class="panel-body em-access-filesbystatus-body">
            <table id="em-modal-action-table" class="table table-hover em-access-filesbystatus-body-table"
                   style="color:black !important;">
                <thead>
                <tr>
                    <th></th>
                    <th>
                        <input type="checkbox" class="em-modal-check em-check-all" name="c-check-all" id="c-check-all"
                               checked style="width: 20px !important"/>
                        <label for="c-check-all"><?php echo JText::_('COM_EMUNDUS_ACCESS_CREATE') ?></label>
                    </th>
                    <th>
                        <input type="checkbox" class="em-modal-check em-check-all" name="r-check-all" id="r-check-all"
                               checked style="width: 20px !important"/>
                        <label for="r-check-all"><?php echo JText::_('COM_EMUNDUS_ACCESS_RETRIEVE') ?></label>
                    </th>
                    <th>
                        <input type="checkbox" class="em-modal-check em-check-all" name="u-check-all" id="u-check-all"
                               checked style="width: 20px !important"/>
                        <label for="u-check-all"><?php echo JText::_('COM_EMUNDUS_ACCESS_UPDATE') ?></label>
                    </th>
                    <th>
                        <input type="checkbox" class="em-modal-check em-check-all" name="d-check-all" id="d-check-all"
                               checked style="width: 20px !important"/>
                        <label for="d-check-all"><?php echo JText::_('COM_EMUNDUS_ACTIONS_DELETE') ?></label>
                    </th>
                </tr>
                </thead>
                <tbody size="<?php echo count($this->actions) ?>">
				<?php foreach ($this->actions as $l => $action): ?>
                    <tr class="em-actions-table-line">
                        <td id="<?php echo $action['id'] ?>"><?php echo JText::_(strtoupper($action['label'])) ?></td>
						<?php if ($action['c'] == 1): ?>
                            <td id="c-check-<?php echo $action['id'] ?>" class="em-has-checkbox">
                                <input type="checkbox" class="em-modal-check c-check"
                                       name="c-check-<?php echo $action['id'] ?>"
                                       id="c-check-<?php echo $action['id'] ?>" <?php echo (@$this->actions_evaluators->{@$action['id']}->c == 1) ? 'checked' : ''; ?> />
                            </td>
						<?php else: ?>
                            <td class="em-no no-action-c"></td>
						<?php endif ?>
						<?php if ($action['r'] == 1): ?>
                            <td id="r-check-<?php echo $action['id'] ?>" class="em-has-checkbox"><input type="checkbox"
                                                                                                        class="em-modal-check r-check"
                                                                                                        name="r-check-<?php echo $action['id'] ?>"
                                                                                                        id="r-check-<?php echo $action['id'] ?>" <?php echo (@$this->actions_evaluators->{@$action['id']}->r == 1) ? 'checked' : ''; ?> />
                            </td>
						<?php else: ?>
                            <td class="em-no no-action-r"></td>
						<?php endif ?>
						<?php if ($action['u'] == 1): ?>
                            <td id="u-check-<?php echo $action['id'] ?>" class="em-has-checkbox"><input type="checkbox"
                                                                                                        class="em-modal-check u-check"
                                                                                                        name="u-check-<?php echo $action['id'] ?>"
                                                                                                        id="u-check-<?php echo $action['id'] ?>" <?php echo (@$this->actions_evaluators->{@$action['id']}->u == 1) ? 'checked' : ''; ?>/>
                            </td>
						<?php else: ?>
                            <td class="em-no no-action-u"></td>
						<?php endif ?>
						<?php if ($action['d'] == 1): ?>
                            <td id="d-check-<?php echo $action['id'] ?>" class="em-has-checkbox"><input type="checkbox"
                                                                                                        class="em-modal-check d-check"
                                                                                                        name="d-check-<?php echo $action['id'] ?>"
                                                                                                        id="d-check-<?php echo $action['id'] ?>" <?php echo (@$this->actions_evaluators->{@$action['id']}->d == 1) ? 'checked' : ''; ?>/>
                            </td>
						<?php else: ?>
                            <td class="em-no no-action-d"></td>
						<?php endif ?>
                    </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
