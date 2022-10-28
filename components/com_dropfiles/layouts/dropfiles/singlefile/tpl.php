<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */


//-- No direct access
defined('_JEXEC') || die('=;)');
$showsize = (int) DropfilesBase::loadValue($displayData['params'], 'showsize', 1);
$usegoogleviewer = ((int) $displayData['componentParams']->get('usegoogleviewer', 1) === 1) ? 'dropfileslightbox' : '';
$target          = ((int) $displayData['componentParams']->get('usegoogleviewer', 1) === 2) ? 'target="_blank"' : '';
?>
<?php if (!empty($displayData['file'])) : ?>
    <div class="dropfiles-content dropfiles-file dropfiles-single-file" data-file="<?php echo $displayData['file']->id; ?>">
        <div class="dropfiles-file-link dropfiles_downloadlink">
            <a class="noLightbox"
               href="<?php echo $displayData['file']->link; ?>"
               data-id="<?php echo $displayData['file']->id; ?>"
               title="<?php echo $displayData['file']->title; ?>">
                <span class="droptitle"><?php echo $displayData['file']->title; ?></span><br/>
                <span class="dropinfos">
                                            <?php if ($showsize === 1) : ?>
                                                <b><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?>: </b>
                                                <?php echo DropfilesFilesHelper::bytesToSize($displayData['file']->size); ?>
                                            <?php endif; ?>
                    <b><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_FORMAT'); ?>: </b>
                    <?php echo strtoupper($displayData['file']->ext); ?>
                    </span>

            </a><br>
            <?php if (isset($displayData['file']->viewerlink)) { ?>
                <a data-id="<?php echo $displayData['file']->id; ?>" data-catid="<?php echo $displayData['category']->id; ?>"
                   data-file-type="<?php echo $displayData['file']->ext; ?>" class="noLightbox <?php echo $usegoogleviewer; ?>"
                   <?php echo $target; ?>
                   href="<?php echo $displayData['file']->viewerlink; ?>">
                    <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_PREVIEW'); ?>
                </a>
            <?php } ?>
        </div>
    </div>
<?php endif; ?>
