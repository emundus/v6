<?php 
defined('_JEXEC') or die('Restricted access'); 

JHTML::_('behavior.tooltip'); 
JHTML::_('behavior.modal');
JHTML::stylesheet( 'emundus.css', JURI::Base().'media/com_emundus/css' );
JHTML::stylesheet( 'template.css', JURI::Base().'templates/emundus/css/' );

$action = JRequest::getVar('action', null, 'GET', 'none',0);
if ($action == 'DONE') {
  echo '<p><fieldset><legend><img src="'.JURI::Base().'media/com_emundus/images/icones/clean.png" alt="'.JText::_('ACTION_DONE').'"/>'.JText::_('ACTION_DONE').'</legend>';
  //echo '<input type="button" value="'.JText::_('CLOSE').'" onclick="window.close()" />';
  echo '</fieldset></p>';
} 

$user = JFactory::getUser();

echo '<h1>'.JText::_('COM_EMUNDUS_MIGRATION_V4_V5').'</h1>';
?>

<fieldset><legend><img src="<?php JURI::Base(); ?>/media/com_emundus/images/icones/documentary_properties_22x22.png" alt="<?php echo JText::_('COM_EMUNDUS_REPEATED_TABLES'); ?>"/> <?php echo JText::_('COM_EMUNDUS_REPEATED_TABLES'); ?></legend>
<?php
if (count($this->repeat_table_list) > 0) {
?>
<form id="adminForm" name="adminForm" onSubmit="return OnSubmitForm();" method="POST"/>
<input type="hidden" name="option" value="com_emundus"/>
<input type="hidden" name="view" value="check"/>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="itemid" value="<?php echo $itemid; ?>"/>
<input type="hidden" name="limitstart" value="<?php echo $limitstart; ?>"/>
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

<table id="userlist">
  <thead>
  <tr>
     <th><?php echo JHTML::_('grid.sort', JText::_('COM_EMUNDUS_TABLES_NAME'), 'TABLE_NAME', $this->lists['order_Dir'], $this->lists['order']); ?></th>
  </tr>
  </thead>
<?php foreach ($this->repeat_table_list as $table) { ?>
  <tr>
    <td>
      <?php echo $table->TABLE_NAME; ?>
      <a href="index.php?option=com_emundus&view=migration&controller=migration&task=check_table&t=<?php echo $table->TABLE_NAME; ?>"><?php echo JText::_( 'COM_EMUNDUS_CHECK_TABLE' ); ?></a>
    </td>
  </tr>

<?php } ?>
</table>
</form>
<?php
} else echo JText::_( 'COM_EMUNDUS_NO_REPEATED_TABLES' );
?>
</fieldset>