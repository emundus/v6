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

?>
<?php if (!empty($this->file)) : ?>
    <div class="dropfiles-content dropfiles-file dropfiles-content-default">
        <div class="file">
            <div class="ext <?php echo $this->file->ext; ?>"><span class="txt">
                <?php echo $this->file->ext; ?>
            </div>
            <div class="filecontent">
                <?php if ((int) DropfilesBase::loadValue($this->params, 'showdownload', 1) === 1) : ?>
                    <a class="downloadlink"
                       href="index.php?option=com_dropfiles&task=frontfile.download&id=<?php echo $this->file->id; ?>">
                        <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?>
                    </a>
                <?php endif; ?>
                <?php if ((int) DropfilesBase::loadValue($this->params, 'showtitle', 1) === 1) : ?>
                    <h3>
                        <a href="index.php?option=com_dropfiles&task=frontfile.download&id=
                        <?php echo $this->file->id; ?>"><?php echo $this->file->title; ?>
                        </a>
                    </h3>
                <?php endif; ?>
                <div><?php echo $this->file->description; ?></div>
                <?php if ((int) DropfilesBase::loadValue($this->params, 'showversion', 1) === 1 ||
                    ((int) DropfilesBase::loadValue($this->params, 'showsize', 1) === 1 &&
                        trim($this->file->version !== ''))) : ?>
                    <div>
                            <?php if ((int) DropfilesBase::loadValue($this->params, 'showversion', 1) === 1 &&
                            trim($this->file->version !== '')) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_VERSION'); ?>
                                    : </span> <?php echo $this->file->version; ?>&nbsp;
                            </div>
                            <?php endif; ?>
                            <?php if ((int) DropfilesBase::loadValue($this->params, 'showsize', 1) === 1) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?>
                                    : </span> <?php echo DropfilesFilesHelper::bytesToSize($this->file->size); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ((int) DropfilesBase::loadValue($this->params, 'showhits', 1) === 1) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_HITS'); ?>
                                    : </span> <?php echo $this->file->hits; ?></div>
                            <?php endif; ?>
                            <?php if ((int) DropfilesBase::loadValue($this->params, 'showdateadd', 1) === 1) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SHOWDATEADD'); ?>
                                    : </span> <?php echo $this->file->created_time; ?></div>
                            <?php endif; ?>
                            <?php if ((int) DropfilesBase::loadValue($this->params, 'showdatemodified', 1) === 1) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DATEMODIFIED'); ?>
                                    : </span> <?php echo $this->file->modified_time; ?></div>
                            <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
