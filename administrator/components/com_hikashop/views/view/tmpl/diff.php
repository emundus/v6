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
if(!file_exists($this->element->override)) {
	hikashop_display(array('FILE_NOT_FOUND'), 'error');
	return;
}
?>
<form action="<?php echo hikashop_completeLink('view');?>" method="post"  name="adminForm" id="adminForm">
<?php
if(!empty($this->element->possible_source_files)) {
	$values = array(JHTML::_('select.option', '', JText::_('PLEASE_SELECT')));
	foreach($this->element->possible_source_files as $file){
		$values[] = JHTML::_('select.option', $file, $file);
	}
	echo JText::sprintf('SOURCE_VIEW_FILE_FOR_THE_OVERRIDE_X',  $this->element->view .' / '. $this->element->filename). ' ';
	echo JHTML::_('hikaselect.genericlist',   $values, 'src', 'class="custom-select" size="1" onchange="this.form.submit();"', 'value', 'text', @$this->element->src);
} elseif(!JFile::exists($this->element->path)) {
	hikashop_display(array('NO_POSSIBLE_SOURCE_FILE_FOUND_FOR_THIS_OVERRIDE'), 'error');
	return;
}
if(!empty($this->element->src)) {
	$this->element->path = $this->element->folder.$this->element->src;
} else {
	$this->element->src = $this->element->filename;
}

if(JFile::exists($this->element->path)) {
	$diff = HikashopDiffInc::compareFiles($this->element->path, $this->element->override);
	array_unshift($diff, array(JText::sprintf('ORIGINAL_FILE', $this->element->view .' / '. $this->element->src), 3, JText::sprintf('OVERRIDE_FILE', $this->element->view .' / '. $this->element->filename)));
	echo HikashopDiffInc::toTable($diff);
}
?>

    <input type="hidden" name="id" value="<?php echo $this->element->id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
	<input type="hidden" name="task" value="diff" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getString('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
