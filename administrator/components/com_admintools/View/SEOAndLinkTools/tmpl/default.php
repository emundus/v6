<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\SEOAndLinkTools\Html */

use Akeeba\AdminTools\Admin\Helper\Select;

defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--horizontal">
	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
            <h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPTGROUP_MIGRATION'); ?></h3>
        </header>

		<div class="akeeba-form-group">
			<label for="linkmigration"><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATION'); ?></label>

            <?php echo \JHtml::_('FEFHelper.select.booleanswitch', 'linkmigration', $this->salconfig['linkmigration']); ?>
		</div>
		<div class="akeeba-form-group">
			<label for="migratelist"
				   title="<?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATIONLIST_TIP'); ?>">
                <?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATIONLIST'); ?>
            </label>

            <textarea rows="5" cols="55" name="migratelist"
                      id="migratelist"><?php echo $this->escape($this->salconfig['migratelist']); ?></textarea>
		</div>
	</div>

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
            <h3><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPTGROUP_TOOLS'); ?></h3>
        </header>

		<div class="akeeba-form-group">
			<label for="httpsizer"><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_HTTPSIZER'); ?></label>

            <?php echo \JHtml::_('FEFHelper.select.booleanswitch', 'httpsizer', $this->salconfig['httpsizer']); ?>
		</div>
	</div>

    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="SEOAndLinkTools"/>
    <input type="hidden" name="task" value="save"/>
    <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
</form>
