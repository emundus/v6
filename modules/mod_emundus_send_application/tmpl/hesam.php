<?php

/**
 * @package   Joomla.Site
 * @subpackage  eMundus
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$uri = JUri::getInstance();
?>
<div class="em-send-print-button">

	<?php if ((!empty($attachments) && (int)($attachments) >= 100 && !empty($forms) && (int)($forms) >= 100 && in_array($application->status, $status_for_send) && !$is_dead_line_passed) || in_array($user->id, $applicants)) :?>
        <?php if ($print) :?>
            <div class="col-md-12 em-print-button">
                <a id="print" class="btn btn-info btn-xs" href="index.php?option=com_emundus&task=pdf&fnum=<?= $user->fnum; ?>" title="Print" target="_blank" title="<?= JText::_('PRINT_APPLICATION_FILE'); ?>"><i class="icon-print"></i> <?= JText::_('PRINT_APPLICATION_FILE'); ?></a>
            </div>
        <?php endif; ?>
        <div class="suivant w-inline-block">
            <a class="entrer-en-contact" href="<?= JRoute::_(JURI::base().$confirm_form_url); ?>" title="<?= JText::_('SEND_APPLICATION_FILE'); ?>"><?= JText::_('SEND_APPLICATION_FILE'); ?></a>
        </div>
    <?php elseif (modemundusSendApplicationHelper::getSearchEngineId($user->fnum) && $print)  :?>
        <div class="col-md-12 em-print-button">
            <a id="print" class="btn btn-info btn-xs" href="<?= JRoute::_(JURI::base().'consultez-les-offres/details/299/'. modemundusSendApplicationHelper::getSearchEngineId($application->fnum)).'?format=pdf'; ?>" title="Print" target="_blank" title="<?= JText::_('PRINT_APPLICATION_FILE'); ?>"><i class="icon-print"></i> <?= JText::_('PRINT_APPLICATION_FILE'); ?></a>
        </div>
	<?php endif; ?>
    <a class="brouillon w-inline-block" onclick="draft()">
        <h3 class="enregistrer-brouillon">Enregistrer en brouillon</h3>
    </a>
</div>

<script>
    function draft() {

        Swal.fire({
            position: 'center',
            type: 'info',
            title: 'Retour à votre éspace personnel',
            html: '<p class="paragraph-infos"><strong>Uniquement les formulaires identifies par une case à cocher verte seront sauvegardées.</span></p>' +
                '<div class="w-col w-col-6"><img src="https://1000docs.emundus.io/images/custom/formulaires-popup.png" width="340" height="220" /></div>',
            width: 1000,
            showCancelButton: true,
            confirmButtonText: 'Continuer',
            cancelButtonText: 'Annuler'
        }).then(function (confirm) {
            if (confirm.value) {
                window.location = 'espace-personnel';
            }
        });
    }
</script>
