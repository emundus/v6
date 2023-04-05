<?php
/**
 * Admin List Edit:plugins Tmpl
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
use Joomla\CMS\HTML\HTMLHelper;

?>
<?php echo HTMLHelper::_('tabs.panel',Text::_('COM_FABRIK_GROUP_LABEL_PLUGINS_DETAILS'), 'list-plugins-panel');?>

<fieldset class="adminform">
	<div id="plugins" class="pane-sliders"></div>
	<a href="#" id="addPlugin" class="addButton"><?php echo Text::_('COM_FABRIK_ADD'); ?></a>
</fieldset>