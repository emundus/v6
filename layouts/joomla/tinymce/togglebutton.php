<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$name = $displayData;

?>
<div class="toggle-editor btn-toolbar pull-right clearfix">
	<div class="btn-group em-flex-row">
		<a class="btn em-flex-row em-flex-gap-8" href="#"
			onclick="tinyMCE.execCommand('mceToggleEditor', false, '<?php echo $name; ?>');return false;"
			title="<?php echo JText::_('PLG_TINY_BUTTON_TOGGLE_EDITOR'); ?>"
		>
			<span class="material-icons-outlined">code</span> <?php echo JText::_('PLG_TINY_BUTTON_TOGGLE_EDITOR'); ?>
		</a>
	</div>
</div>