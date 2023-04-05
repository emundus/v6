<?php
/**
 * Bootstrap Form Template - Actions
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$form = $this->form;
if ($this->hasActions) : ?>
<div class="fabrikActions form-actions mt-3">
	<div class="btn-toolbar justify-content-between" role="toolbar" aria-label="Fabrik Actions">
		<?php if ( $form->submitButton || $form->applyButton || $form->copyButton ): ?>
			<div class="btn-group" role="group" airia-label="Submit-Apply-Copy">
				<?php
				echo $form->submitButton . ' ';
				echo $form->applyButton . ' ';
				echo $form->copyButton;
				?>
			</div>
		<?php endif; ?>
		<?php if ($form->customButtons): ?>
			<div class="btn-group" role="group" airia-label="Fabrik Custom Buttons">
				<?php echo $form->customButtons; ?>
			</div>
		<?php endif; ?>
		<?php if ( $form->prevButton || $form->nextButton ): ?>
			<div class="btn-group" role="group" airia-label="Previous-Next Buttons">
				<?php echo $form->prevButton . ' ' . $form->nextButton; ?>
			</div>
		<?php endif; ?>
		<?php if ( $form->gobackButton || $form->resetButton || $form->deleteButton || $form->clearMultipageSessionButton): ?>
			<div class="btn-group" role="group" airia-label="Back-Reset-Delete-MultiSession Buttons">
				<?php
				echo $form->gobackButton;
				echo $form->resetButton;
				echo $form->deleteButton;
				echo $form->clearMultipageSessionButton;
				?>
			</div>
		<?php

		endif;
		 ?>
	</div>
</div>
<?php
endif;
