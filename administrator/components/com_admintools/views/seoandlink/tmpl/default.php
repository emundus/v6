<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

$this->loadHelper('select');

$lang = JFactory::getLanguage();
?>
<form name="adminForm" id="adminForm" action="index.php" method="post"
	  class="form form-horizontal form-horizontal-wide">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="seoandlink"/>
	<input type="hidden" name="task" value="save"/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<fieldset>
		<legend><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPTGROUP_MIGRATION') ?></legend>

		<div class="control-group">
			<label for="linkmigration"
				   class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_LINKMIGRATION'); ?></label>

			<div class="controls">
				<?php echo AdmintoolsHelperSelect::booleanlist('linkmigration', array(), $this->salconfig['linkmigration']) ?>
			</div>
		</div>
		<div class="control-group">
			<label for="migratelist" class="control-label"
				   title="<?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_LINKMIGRATIONLIST_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_LINKMIGRATIONLIST'); ?></label>

			<div class="controls">
				<textarea rows="5" cols="55" name="migratelist"
						  id="migratelist"><?php echo $this->salconfig['migratelist'] ?></textarea>
			</div>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPTGROUP_TOOLS') ?></legend>

		<div class="control-group">
			<label for="httpsizer"
				   class="control-label"><?php echo JText::_('ATOOLS_LBL_SEOANDLINK_OPT_HTTPSIZER'); ?></label>

			<div class="controls">
				<?php echo AdmintoolsHelperSelect::booleanlist('httpsizer', array(), $this->salconfig['httpsizer']) ?>
			</div>
		</div>
	</fieldset>
</form>