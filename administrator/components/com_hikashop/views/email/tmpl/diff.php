<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->element->{$this->override_name})) {
	hikashop_display(array('FILE_NOT_FOUND'), 'error');
	return;
}
$diff = HikashopDiffInc::compareFiles($this->element->{$this->path_name}, $this->element->{$this->override_path_name});
array_unshift($diff, array(JText::sprintf('ORIGINAL_FILE', ''), 3, JText::sprintf('OVERRIDE_FILE', '')));
echo HikashopDiffInc::toTable($diff);
?>
<form action="<?php echo hikashop_completeLink('view');?>" method="post"  name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getString('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
