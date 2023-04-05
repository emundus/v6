<?php
/**
 * Admin Form Edit Tmpl
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

?>
<div class="tab-pane" id="tab-layout">

<div class="row">
	<div class="col-sm-12">
		<fieldset>
	    <legend>
			<?php echo Text::_('COM_FABRIK_FRONT_END_TEMPLATES'); ?>
		</legend>
		<?php foreach ($this->form->getFieldset('templates') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
		<?php foreach ($this->form->getFieldset('templates2') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
	</div>

	<div class="col-sm-12">

    <fieldset>
    	<legend>
			<?php echo Text::_('COM_FABRIK_ADMIN_TEMPLATES'); ?>
		</legend>
		<?php foreach ($this->form->getFieldset('admintemplates') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
	</div>
</div>



	<fieldset>
    	<legend>
			<?php echo Text::_('COM_FABRIK_LAYOUT'); ?>
		</legend>
		<?php foreach ($this->form->getFieldset('layout') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
</div>
