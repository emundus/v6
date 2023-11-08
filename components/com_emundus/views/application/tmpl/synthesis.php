<?php

if (!empty((array) $this->synthesis)) {
	$anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
	if ($anonymize_data) :?>

        <div class="em-hidden-synthesis"><?= JText::_('COM_EMUNDUS_CANNOT_SEE_GROUP'); ?></div>

	<?php else : ?>

		<?php echo $this->synthesis->block; ?>
        <input type="hidden" id="application_fnum" value="<?= $this->synthesis->fnum; ?>">

	<?php endif;
}

