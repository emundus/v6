<?php
/**
 * Admin Form Edit:plugins Tmpl
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
<fieldset class="adminform">
	<ul class="adminformlist">
		<li>
			<div id="plugins" class="pane-sliders"></div>
			<a href="#" class="addButton" id="addPlugin"><?php echo Text::_('COM_FABRIK_ADD'); ?></a>
		</li>
	</ul>
</fieldset>