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

if (!empty($this->files) || !empty($this->categories)) : ?>
    <div class="dropfiles-content dropfiles-content-multi dropfiles-files dropfiles-content-default"
         data-category="<?php echo $this->category->id; ?>">
        <?php if ((int) DropfilesBase::loadValue($this->params, 'showcategorytitle', 1) === 1) : ?>
            <h2><?php echo $this->category->title; ?></h2>
        <?php endif; ?>

        <?php if (is_array($this->categories) && count($this->categories) && (int) DropfilesBase::loadValue($this->params, 'showsubcategories', 1) === 1) : ?>
            <?php foreach ($this->categories as $category) : ?>
                <a class="dropfilescategory catlink" href="#"
                   data-idcat="<?php echo $category->id; ?>"><?php echo $category->title; ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (is_array($this->files) && count($this->files)) : ?>
            <?php foreach ($this->files as $file) : ?>
                <div class="file">
                    <div class="ext <?php echo $file->ext; ?>"><span class="txt"><?php echo $file->ext; ?></div>
                    <div class="filecontent">
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showdownload', 1) === 1) : ?>
                            <a class="downloadlink"
                               href="index.php?option=com_dropfiles&task=frontfile.download&id=<?php echo $file->id; ?>
                                ">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?>
                            </a>
                        <?php endif; ?>
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showtitle', 1) === 1) : ?>
                            <h3>
                                <a href="index.php?option=com_dropfiles&task=frontfile.download&id=
                                <?php echo $file->id; ?>"><?php echo $file->title; ?></a>
                            </h3>
                        <?php endif; ?>
                        <div><?php echo $file->description; ?></div>
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showversion', 1) === 1 &&
                            trim($file->version !== '')) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_VERSION'); ?>
                                    : </span> <?php echo $file->version; ?>&nbsp;
                            </div>
                        <?php endif; ?>
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showsize', 1) === 1) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?>
                                    : </span> <?php echo DropfilesFilesHelper::bytesToSize($file->size); ?></div>
                        <?php endif; ?>
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showhits', 1) === 1) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_HITS'); ?>
                                    : </span> <?php echo $file->hits; ?></div>
                        <?php endif; ?>
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showdateadd', 1) === 1) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SHOWDATEADD'); ?>
                                    : </span> <?php echo $file->created_time; ?></div>
                        <?php endif; ?>
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showdatemodified', 0) === 1) : ?>
                            <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DATEMODIFIED'); ?>
                                    : </span> <?php echo $file->modified_time; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
