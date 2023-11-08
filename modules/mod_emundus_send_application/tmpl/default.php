<?php

/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$uri          =& JFactory::getURI();
$url          = explode('&', $uri->toString());
$details_view = array_search('view=details', $url);
?>
<div class="em-send-print-button">
	<?php if ($print) : ?>
        <div class="col-md-12 em-print-button">
            <a id="print" class="btn btn-info btn-xs"
               href="index.php?option=com_emundus&task=pdf&fnum=<?php echo $user->fnum; ?>" target="_blank"
               title="<?php echo JText::_('MOD_EMUNDUS_SEND_APPLICATION_PRINT_APPLICATION_FILE'); ?>"><i
                        class="icon-print"></i> <?php echo JText::_('MOD_EMUNDUS_SEND_APPLICATION_PRINT_APPLICATION_FILE'); ?>
            </a>
        </div>
	<?php endif; ?>
	<?php if ($send && $details_view === false && $is_confirm_url === false) : ?>
        <div class="col-md-6 em-send-button">
            <a class="btn btn-success btn-xs"
				<?php if (((int) ($attachments) >= 100 && (int) ($forms) >= 100 && in_array($application->status, $status_for_send) && (!$is_dead_line_passed || ($is_dead_line_passed && $can_edit_after_deadline))) || in_array($user->id, $applicants)) : ?>
                    href="<?php echo $confirm_form_url; ?>" style="opacity: 1"
				<?php else: ?>
                    style="opacity: 0.6; cursor: not-allowed"
				<?php endif; ?>
				<?php if ($application_fee && !$paid) : ?>
                    title="<?php echo JText::_('MOD_EMUNDUS_SEND_APPLICATION_PROCESS_TO_PAYMENT'); ?>"
				<?php else : ?>
                    title="<?php echo JText::_('MOD_EMUNDUS_SEND_APPLICATION_SEND_APPLICATION_FILE'); ?>"
				<?php endif ?>>
                <i class="icon-envelope"></i>
				<?php if ($application_fee && !$paid) : ?>
					<?php echo JText::_('MOD_EMUNDUS_SEND_APPLICATION_PROCESS_TO_PAYMENT'); ?>
				<?php else : ?>
					<?php echo JText::_('MOD_EMUNDUS_SEND_APPLICATION_SEND_APPLICATION_FILE'); ?>
				<?php endif ?>
            </a>
        </div>
	<?php endif; ?>
</div>
