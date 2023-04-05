<?php
/**
 * Admin List Tmpl
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
<div class="tab-pane" id="access">
<legend><?php echo Text::_('COM_FABRIK_GROUP_LABEL_RULES_DETAILS'); ?></legend>
   <fieldset>
		<?php
		foreach ($this->form->getFieldset('access') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		foreach ($this->form->getFieldset('access2') as $this->field) :
			echo $this->loadTemplate('control_group');
		endforeach;
		?>
	</fieldset>
</div>
