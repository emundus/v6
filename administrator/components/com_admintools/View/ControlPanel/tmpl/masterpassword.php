<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\View\ControlPanel\Html;
use Joomla\CMS\Language\Text;

/** @var  Html $this For type hinting in the IDE */

defined('_JEXEC') or die;

?>
<div class="akeeba-block--info">
	<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--inline">
		<input type="hidden" name="option" value="com_admintools" />
		<input type="hidden" name="view" value="ControlPanel" />
		<input type="hidden" name="task" value="login" />

		<h3><?php echo Text::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPWHEAD'); ?></h3>

		<p class="akeeba-help-text">
			<?php echo Text::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPWINTRO'); ?>
		</p>

		<div class="akeeba-form-group">
			<label for="userpw">
				<?php echo Text::_('COM_ADMINTOOLS_LBL_CONTROLPANEL_MASTERPW'); ?>
			</label>
			<input type="password" name="userpw" id="userpw" value="" />
		</div>

		<div class="akeeba-form-group--actions">
			<input type="submit" class="akeeba-btn--primary" />
		</div>
	</form>
</div>
