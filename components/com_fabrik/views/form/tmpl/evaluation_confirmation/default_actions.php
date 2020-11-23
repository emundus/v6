<?php
/**
 * Bootstrap Form Template - Actions
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$form = $this->form;
if ($this->hasActions) : ?>
<div class="fabrikActions form-actions">
	<div class="row-fluid">
		<?php if ($form->submitButton || $form->applyButton || $form->copyButton): ?>
			<div class="<?= FabrikHelperHTML::getGridSpan(4); ?>">
				<div class="btn-group">
					<?php
					echo $form->submitButton . ' ';
					echo $form->applyButton . ' ';
					echo $form->copyButton;
					?>
				</div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary button save_continue" name="Confirm" id="confirm_eval" onclick="confirmEvaluation();"><?= JText::_('COM_EMUNDUS_CONFIRM_EVALUATION'); ?></button>
                </div>
			</div>
		<?php endif; ?>

		<?php if ($form->customButtons): ?>
			<div class="fabrikCustomButtons <?= FabrikHelperHTML::getGridSpan(2); ?>">
				<div class="btn-group">
					<?= $form->customButtons; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($form->prevButton || $form->nextButton): ?>
			<div class="offset1 <?= FabrikHelperHTML::getGridSpan(2); ?>">
				<div class="btn-group">
					<?= $form->prevButton . ' ' . $form->nextButton; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ($form->gobackButton || $form->resetButton || $form->deleteButton || $form->clearMultipageSessionButton): ?>
			<div class="offset1 <?= FabrikHelperHTML::getGridSpan(4); ?>">
				<div class="pull-right btn-group">
					<?php
					echo $form->gobackButton;
					echo $form->resetButton;
					echo $form->deleteButton;
					echo $form->clearMultipageSessionButton;
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<script>
    function confirmEvaluation() {
        window.parent.ScrollToTop();
        Swal.fire({
            position: 'top',
            type: 'warning',
            title: '<?= JText::_('CONFIRM_ARE_YOU_SURE'); ?>',
            text: '<?= JText::_('CONFIRM_EXPLANATION'); ?>',
            width: 1000,
            showCancelButton: true
        }).then(confirm => {
            if (confirm.value) {
                document.getElementById('jos_emundus_evaluations___confirm').value = 1;
                document.querySelector('[id^="form_<?= $form->id; ?>"]').submit();
            }
        });
    }
</script>

<?php
endif;
