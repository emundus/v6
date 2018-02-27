<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var  \Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

defined('_JEXEC') or die;

$uriBase = rtrim(JUri::base(), '/');

?>
<div class="akeeba-panel--default">
    <header class="akeeba-block-header">
        <h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_TOOLS'); ?></h3>
    </header>

    <div class="akeeba-grid--small">
        <?php if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'): ?>
            <a href="index.php?option=com_admintools&view=ConfigureFixPermissions" class="akeeba-action--teal">
                <span class="akion-ios-gear"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG'); ?>
            </a>

            <?php if ($this->enable_fixperms): ?>
                <a id="fixperms" href="index.php?option=com_admintools&view=FixPermissions&tmpl=component" class="akeeba-action--teal">
                    <span class="akion-wand"></span>
                    <?php echo \JText::_('COM_ADMINTOOLS_TITLE_FIXPERMS'); ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <a href="index.php?option=com_admintools&view=SEOAndLinkTools" class="akeeba-action--teal">
            <span class="akion-link"></span>
            <?php echo \JText::_('COM_ADMINTOOLS_TITLE_SEOANDLINK'); ?>
        </a>

        <?php if ($this->enable_cleantmp): ?>
            <a id="cleantmp" href="index.php?option=com_admintools&view=CleanTempDirectory&tmpl=component" class="akeeba-action--teal">
                <span class="akion-trash-a"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_TITLE_CLEANTMP'); ?>
            </a>
        <?php endif; ?>

        <?php if ($this->enable_tmplogcheck): ?>
            <a id="tmplogcheck" href="index.php?option=com_admintools&view=CheckTempAndLogDirectories&tmpl=component" class="akeeba-action--teal">
                <span class="akion-trash-a"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_TITLE_TMPLOGCHECK'); ?>
            </a>
        <?php endif; ?>

        <?php if ($this->enable_dbchcol && $this->isMySQL): ?>
            <a href="index.php?option=com_admintools&view=ChangeDBCollation" class="akeeba-action--teal">
                <span class="akion-ios-redo"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_CHANGEDBCOLLATION'); ?>
            </a>
        <?php endif; ?>

        <?php if ($this->enable_dbtools && $this->isMySQL): ?>
            <a id="optimizedb" href="index.php?option=com_admintools&view=DatabaseTools&task=optimize&tmpl=component" class="akeeba-action--teal">
                <span class="akion-wand"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB'); ?>
            </a>
        <?php endif; ?>

        <?php if ($this->enable_cleantmp && $this->isMySQL): ?>
            <a href="index.php?option=com_admintools&view=DatabaseTools&task=purgesessions" id="optimize" class="akeeba-action--teal">
                <span class="akion-nuclear"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_LBL_DATABASETOOLS_PURGESESSIONS'); ?>
            </a>
        <?php endif; ?>

        <a href="index.php?option=com_admintools&view=Redirections" class="akeeba-action--teal">
            <span class="akion-shuffle"></span>
            <?php echo \JText::_('COM_ADMINTOOLS_TITLE_REDIRS'); ?>
        </a>

        <?php if ($this->isPro): ?>
            <a href="index.php?option=com_plugins&task=plugin.edit&extension_id=<?php echo (int) $this->pluginid; ?>" target="_blank" class="akeeba-action--teal">
                <span class="akion-calendar"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_SCHEDULING'); ?>
            </a>

            <a href="index.php?option=com_admintools&view=ImportAndExport&task=export" class="akeeba-action--teal">
                <span class="akion-share"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS'); ?>
            </a>

            <a href="index.php?option=com_admintools&view=ImportAndExport&task=import" class="akeeba-action--teal">
                <span class="akion-archive"></span>
                <?php echo \JText::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS'); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
