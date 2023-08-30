<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 22/11/14
 * Time: 17:36
 */
$current_user = JFactory::getUser();
?>


<div>
	<div>
		<?php
		foreach ($this->users as $user) : ?>

			<h4 title="<?= $user['fnum']; ?>"><?= $user['name']; ?> (<?= $user['label']; ?> - <?= $user['year']; ?>)</h4>

			<?php if (!empty($this->groupFnum[$user['fnum']])) : ?>
				<strong><?= JText::_('COM_EMUNDUS_GROUPS'); ?></strong>
				<ul>
					<?php
					if (!is_array($this->groupFnum[$user['fnum']])) {
						$groups = explode(',', $this->groupFnum[$user['fnum']]);
						foreach ($groups as $g) : ?>
							<li>
								<?= $g; ?>
							</li>
						<?php endforeach; ?>
					<?php } ?>
				</ul>
			<?php endif; ?>

			<?php if (!empty($this->evalFnum[$user['fnum']])) : ?>
				<strong><?= JText::_('COM_EMUNDUS_ASSESSORS'); ?></strong>
				<ul>
					<?php
					if (!is_array($this->evalFnum[$user['fnum']])) {
						$assessors = explode(',', $this->evalFnum[$user['fnum']]);
						foreach ($assessors as $a) : ?>
							<li>
								<?= $a; ?>
							</li>
						<?php endforeach; ?>
					<?php } ?>
				</ul>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<div class="em-access">
		<div class="em-access-form">
            <?php if (EmundusHelperAccess::asAccessAction(11,'u', $current_user->id)) { ?>
            <div>
                <label><?= JText::_('COM_EMUNDUS_GROUPS')?></label>
                <select class="modal-chzn-select" multiple="true" data-placeholder="<?= JText::_('COM_EMUNDUS_GROUPS_PLEASE_SELECT_GROUP'); ?>" name="em-access-groups-eval" id="em-access-groups-eval" value="">
                    <?php foreach ($this->groups as $group) : ?>
                        <option value="<?= $group['id']; ?>"><?= $group['label']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php } ?>
            <div class="em-mt-12">
                <label><?= JText::_('COM_EMUNDUS_EVALUATION_EVALUATORS'); ?></label>
                <select class="modal-chzn-select" multiple="true" data-placeholder="<?= JText::_('COM_EMUNDUS_GROUPS_PLEASE_SELECT_ASSESSOR'); ?>" name="em-access-evals" id="em-access-evals" value="">
                    <?php foreach ($this->evals as $eval) : ?>
                        <option value="<?= $eval['user_id']; ?>"><?= $eval['name']; ?> (<?= $eval['email']; ?>) :: <?= $eval['label']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="em-flex-row em-mt-12">
                <input type="checkbox" id="evaluator-email"><label for="evaluator-email" class="em-mb-0-important"><?= JText::_('COM_EMUNDUS_GROUPS_NOTIFY_EVALUATORS'); ?></label>
            </div>
		</div>

		<div class="<?= $this->hide_actions == 1 ? 'hidden ' : ''; ?>panel-body em-access em-access-body hidden">
			<table id="em-modal-action-table em-access" class="table table-hover em-access-body-table">
				<thead>
					<tr>
						<th></th>
						<th>
							<input type="checkbox" class="em-modal-check em-check-all" name="c-check-all" id="c-check-all" />
						    <label for="c-check-all"><?= JText::_('COM_EMUNDUS_ACCESS_CREATE'); ?></label>
						</th>
						<th>
							<input type="checkbox" class="em-modal-check em-check-all" name="r-check-all" id="r-check-all" />
						    <label for="r-check-all"><?= JText::_('COM_EMUNDUS_ACCESS_RETRIEVE'); ?></label>
						</th>
						<th>
							<input type="checkbox" class="em-modal-check em-check-all" name="u-check-all" id="u-check-all" />
						    <label for="u-check-all"><?= JText::_('COM_EMUNDUS_ACCESS_UPDATE'); ?></label>
						</th>
						<th>
							<input type="checkbox" class="em-modal-check em-check-all" name="d-check-all" id="d-check-all" />
						    <label for="d-check-all"><?= JText::_('COM_EMUNDUS_ACTIONS_DELETE'); ?></label>
						</th>
					</tr>
				</thead>
				<tbody size="<?= count($this->actions); ?>">
					<?php foreach ($this->actions as $l => $action) : ?>
						<tr class="em-actions-table-line">
							<td id="<?= $action['id']; ?>"><?= JText::_(strtoupper($action['label'])); ?></td>
							<?php if ($action['c'] == 1) : ?>
								<td id="c-check-<?= $action['id']; ?>" class="em-has-checkbox">
									<input type="checkbox" class="em-modal-check c-check" name="c-check-<?= $action['id']; ?>" id="c-check-<?= $action['id']; ?>" <?= (@$this->actions_evaluators->{@$action['id']}->c == 1) ? 'checked' : ''; ?> />
								</td>
							<?php else : ?>
								<td class="em-no no-action-c"></td>
							<?php endif; ?>
							<?php if ($action['r'] == 1) : ?>
								<td id="r-check-<?= $action['id']; ?>" class="em-has-checkbox">
									<input type="checkbox" class="em-modal-check r-check" name="r-check-<?= $action['id']; ?>" id="r-check-<?= $action['id']; ?>" <?= (@$this->actions_evaluators->{@$action['id']}->r == 1) ? 'checked' : ''; ?> />
								</td>
							<?php else : ?>
								<td class="em-no no-action-r"></td>
							<?php endif; ?>
							<?php if ($action['u'] == 1) : ?>
								<td id="u-check-<?= $action['id']; ?>" class="em-has-checkbox">
									<input type="checkbox" class="em-modal-check u-check" name="u-check-<?= $action['id']; ?>" id="u-check-<?= $action['id']; ?>" <?= (@$this->actions_evaluators->{@$action['id']}->u == 1) ? 'checked' : ''; ?> />
								</td>
							<?php else : ?>
								<td class="em-no no-action-u"></td>
							<?php endif ?>
							<?php if ($action['d'] == 1) : ?>
								<td id="d-check-<?= $action['id']; ?>" class="em-has-checkbox">
									<input type="checkbox" class="em-modal-check d-check" name="d-check-<?= $action['id']; ?>" id="d-check-<?= $action['id']; ?>" <?= (@$this->actions_evaluators->{@$action['id']}->d == 1) ? 'checked' : ''; ?> />
								</td>
							<?php else : ?>
								<td class="em-no no-action-d"></td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

	</div>
</div>
