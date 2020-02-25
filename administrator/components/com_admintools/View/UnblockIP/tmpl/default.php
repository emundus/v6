<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var    $this   Akeeba\AdminTools\Admin\View\UnblockIP\Html */

// Protect from unauthorized access
defined('_JEXEC') or die;

?>
<form action="index.php" name="adminForm" id="adminForm" method="post" class="akeeba-form--horizontal">
    <p class="akeeba-block--info">
        <?php echo JText::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_INFO')?>
    </p>
    <div class="akeeba-container--50-50">
        <div>
            <div class="akeeba-form-group">
                <label><?php echo JText::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_CHOOSE_IP')?></label>
                <input type="text" value="" name="ip" />
            </div>

            <div class="akeeba-form-group">
                <input type="submit" class="akeeba-btn--primary--big" value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_UNBLOCKIP_IP'); ?>"/>
            </div>
        </div>
    </div>

    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="UnblockIP"/>
    <input type="hidden" name="task" value="unblock"/>
    <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
</form>
