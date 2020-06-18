<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\View\FixPermissions\Html;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var $this Html */

defined('_JEXEC') or die;

if (version_compare(JVERSION, '3.999.999', 'lt'))
{
	HTMLHelper::_('behavior.modal');
}
?>
<?php if ($this->more): ?>
	<h1><?php echo Text::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_INPROGRESS'); ?></h1>
<?php else: ?>
	<h1><?php echo Text::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DONE'); ?></h1>
<?php endif; ?>

<div class="akeeba-progress">
	<div class="akeeba-progress-fill" style="width:<?php echo $this->percentage ?>%;"></div>
	<div class="akeeba-progress-status">
		<?php echo $this->percentage ?>%
	</div>
</div>

<form action="index.php" name="adminForm" id="adminForm" method="get">
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="FixPermissions" />
	<input type="hidden" name="task" value="run" />
	<input type="hidden" name="tmpl" value="component" />
</form>

<?php if (!$this->more): ?>
	<div class="akeeba-block--info" id="admintools-fixpermissions-autoclose">
		<p><?php echo Text::_('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S'); ?></p>
	</div>
<?php endif; ?>
