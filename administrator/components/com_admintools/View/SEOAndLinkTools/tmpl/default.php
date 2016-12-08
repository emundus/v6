<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\SEOAndLinkTools\Html */

use Akeeba\AdminTools\Admin\Helper\Select;

defined('_JEXEC') or die;

$lang = JFactory::getLanguage();
?>
<form name="adminForm" id="adminForm" action="index.php" method="post"
	  class="form form-horizontal form-horizontal-wide">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="SEOAndLinkTools"/>
	<input type="hidden" name="task" value="save"/>
	<input type="hidden" name="<?php echo $this->escape(JFactory::getSession()->getFormToken()); ?>" value="1"/>

	<fieldset>
		<legend><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPTGROUP_MIGRATION'); ?></legend>

		<div class="control-group">
			<label for="linkmigration"
				   class="control-label"><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATION'); ?></label>

			<div class="controls">
				<?php echo Select::booleanlist('linkmigration', array(), $this->salconfig['linkmigration']); ?>

			</div>
		</div>
		<div class="control-group">
			<label for="migratelist" class="control-label"
				   title="<?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATIONLIST_TIP'); ?>"><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_LINKMIGRATIONLIST'); ?></label>

			<div class="controls">
				<textarea rows="5" cols="55" name="migratelist"
						  id="migratelist"><?php echo $this->escape($this->salconfig['migratelist']); ?></textarea>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPTGROUP_TOOLS'); ?></legend>

		<div class="control-group">
			<label for="httpsizer"
				   class="control-label"><?php echo \JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_HTTPSIZER'); ?></label>

			<div class="controls">
				<?php echo Select::booleanlist('httpsizer', array(), $this->salconfig['httpsizer']); ?>

			</div>
		</div>
	</fieldset>
</form>