<?php
/**
 * Admin Visualization Edit Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::stylesheet('administrator/components/com_fabrik/views/fabrikadmin.css');
HTMLHelper::_('bootstrap.tooltip');

FabrikHelperHTML::formvalidation();
HTMLHelper::_('behavior.keepalive');
?>

<form action="<?php Route::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="row">

		<div class="col-sm-6">
			<fieldset>
				<legend><?php echo Text::_('COM_FABRIK_DETAILS'); ?></legend>
				<?php foreach ($this->form->getFieldset('details') as $this->field) :
					echo $this->loadTemplate('control_group');
				endforeach;
				?>
			</fieldset>
		</div>

		<div class="col-sm-5">
			<div class="offset2">
				<fieldset>
						<legend>
							<?php echo Text::_('COM_FABRIK_GROUP_LABEL_PUBLISHING_DETAILS');?>
						</legend>
					<?php foreach ($this->form->getFieldset('publishing') as $this->field) :
						echo $this->loadTemplate('control_group');
					endforeach;
					?>
				</fieldset>

				<fieldset>
						<legend>
							<?php echo Text::_('COM_FABRIK_VISUALIZATION_LABEL_VISUALIZATION_DETAILS');?>
						</legend>
					<?php foreach ($this->form->getFieldset('more') as $this->field) :
						echo $this->loadTemplate('control_group');
					endforeach;
					?>
				</fieldset>
			</div>
		</div>
	</div>
	<div class="row">

		<div class="col-sm-12">
		<fieldset>
		    	<legend>
		    		<?php echo Text::_('COM_FABRIK_OPTIONS');?>
		    	</legend>
			</fieldset>
			<div id="plugin-container">
				<?php echo $this->pluginFields;?>
			</div>
		</div>

	</div>

	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
