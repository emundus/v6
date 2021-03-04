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

	<?php if (((int)($attachments) >= 100 && (int)($forms) >= 100 && in_array($application->status, $status_for_send) && (!$is_dead_line_passed || ($is_dead_line_passed && $can_edit_after_deadline))) || in_array($user->id, $applicants)) :?>
	    <?php if ($print) :?>
            <div class="col-md-6 em-print-button">
                <a id="print" class="btn btn-info btn-xs" href="index.php?option=com_emundus&task=pdf&fnum=<?php echo $user->fnum; ?>" target="_blank" title="<?php echo JText::_('PRINT_APPLICATION_FILE'); ?>"><i class="icon-print"></i> <?php echo JText::_('PRINT_APPLICATION_FILE'); ?></a>
            </div>
        <?php endif; ?>
        <div class="col-md-6 em-send-button">
            <a class="btn btn-success btn-xs" href="<?php echo $confirm_form_url; ?>" title="<?php echo JText::_('SEND_APPLICATION_FILE'); ?>"><i class="icon-envelope"></i> <?php echo JText::_('SEND_APPLICATION_FILE'); ?></a>
        </div>
    <?php elseif ($print) :?>
        <div class="col-md-12 em-print-button">
            <a id="print" class="btn btn-info btn-xs" href="index.php?option=com_emundus&task=pdf&fnum=<?php echo $user->fnum; ?>" target="_blank" title="<?php echo JText::_('PRINT_APPLICATION_FILE'); ?>"><i class="icon-print"></i> <?php echo JText::_('PRINT_APPLICATION_FILE'); ?></a>
        </div>
	<?php endif; ?>
</div>
