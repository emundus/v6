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
defined('_JEXEC') || die;
if (!isset($params)) {
    $params = null;
}
if (!isset($result)) {
    $result = null;
}
$dropfileConfig = JComponentHelper::getParams('com_dropfiles');

$display_title        = (int) $params->get('display_title', 1);
$display_size         = (int) $params->get('display_size', 1);
$display_version      = (int) $params->get('display_version', 0);
$display_hits         = (int) $params->get('display_hits', 0);
$display_date_added   = (int) $params->get('display_date_added', 0);
$display_date_updated = (int) $params->get('display_date_updated', 0);
$usegoogleviewer      = ((int) $dropfileConfig->get('usegoogleviewer', 1) === 1) ? 'dropfileslightbox' : '';
?>
<div class="mod_dropfiles_latest">
    <div class="mod_dropfiles_list">
        <?php if (!empty($result) && is_array($result)) : ?>
            <?php foreach ($result as $file) : ?>
                <?php if ($file->ext !== null) : ?>
                    <div class="mod_file">
                        <div class="mod_filecontent">
                            <div class="mod_filecontent_head">
                                <?php if ($display_title === 1) : ?>
                                    <h3><a class="mod_dropfiles_downloadlink" title="<?php echo $file->title; ?>"
                                           href="<?php echo $file->link; ?>"><?php echo $file->title; ?></a></h3>
                                <?php endif; ?>
                                <div class="mod_file-right">
                                    <?php if ($params) : ?>
                                        <a class="mod_downloadlink" href="<?php echo $file->link; ?>"><i
                                                    class="zmdi zmdi-cloud-download mod_dropfiles-download"></i></a>
                                    <?php endif; ?>
                                    <?php if (isset($file->openpdflink)) { ?>
                                        <a href="<?php echo $file->openpdflink; ?>" class="mod_openlink"
                                           target="_blank">
                                            <i class="zmdi zmdi-filter-center-focus mod_dropfiles-preview"></i></a>
                                    <?php } else { ?>
                                        <?php if (isset($file->viewerlink)) : ?>
                                            <a data-id="<?php echo $file->id; ?>"
                                               data-catid="<?php echo $file->catid; ?>"
                                               data-file-type="<?php echo $file->ext; ?>"
                                               class="mod_openlink <?php echo $usegoogleviewer; ?>"
                                               href="<?php echo $file->viewerlink; ?>">
                                                <i class="zmdi zmdi-filter-center-focus mod_dropfiles-preview"></i></a>
                                        <?php endif; ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="mod_file-xinfo">
                                <div class="mod_file-desc"><?php echo $file->description; ?></div>
                                <?php if ($display_version === 1) : ?>
                                    <div class="mod_file-version">
                                    <span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_VERSION'); ?>
                                        : </span> <?php echo $file->versionNumber; ?>&nbsp;
                                    </div>
                                <?php endif; ?>
                                <?php if ($display_size === 1) : ?>
                                    <div class="mod_file-size">
                                        <span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?>: </span>
                                        <?php echo DropfilesFilesHelper::bytesToSize($file->size); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($display_hits === 1) : ?>
                                    <div class="mod_file-hits"><span>
                                            <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_HITS'); ?>
                                            : </span> <?php echo $file->hits; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($display_date_added === 1) : ?>
                                    <div class="mod_file-dated"><span>
                                        <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SHOWDATEADD'); ?>: </span>
                                        <?php echo date(
                                            $dropfileConfig->get('date_format', 'Y-m-d'),
                                            strtotime($file->created_time)
                                        ); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($display_date_updated === 1) : ?>
                                    <div class="mod_file-modified">
                                        <span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DATEMODIFIED'); ?>
                                            : </span>
                                        <?php echo date(
                                            $dropfileConfig->get('date_format', 'Y-m-d'),
                                            strtotime($file->modified_time)
                                        ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style type="text/css">
    <?php echo $mode_style ?>
</style>
