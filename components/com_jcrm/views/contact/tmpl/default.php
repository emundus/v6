<?php
/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

//Load admin language file
$lang = JFactory::getLanguage();
$lang->load('com_jcrm', JPATH_ADMINISTRATOR);
$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::base() . '/components/com_jcrm/assets/css/item.css');
$canEdit = JFactory::getUser()->authorise('core.edit', 'com_jcrm.' . $this->item->id);
if (!$canEdit && JFactory::getUser()->authorise('core.edit.own', 'com_jcrm' . $this->item->id)) {
	$canEdit = JFactory::getUser()->id == $this->item->created_by;
}
?>
<?php if ($this->item) : ?>

    <div class="item_fields">
        <table class="table">
            <tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_ID'); ?></th>
			<td><?php echo $this->item->id; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_STATE'); ?></th>
			<td>
			<?php echo ($this->item->state == 1) ? JText::_('JPUBLISH') : JText::_('JUNPUBLISH'); ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_CREATED_BY'); ?></th>
			<td><?php echo $this->item->created_by_name; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_LAST_NAME'); ?></th>
			<td><?php echo $this->item->last_name; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_FIRST_NAME'); ?></th>
			<td><?php echo $this->item->first_name; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_ORGANISATION'); ?></th>
			<td><?php echo $this->item->organisation; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_EMAIL'); ?></th>
			<td><?php echo $this->item->email; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_PHONE'); ?></th>
			<td><?php echo $this->item->phone; ?></td>
</tr>
<tr>
			<th><?php echo JText::_('COM_JCRM_FORM_LBL_CONTACT_JCARD'); ?></th>
			<td><?php echo $this->item->jcard; ?></td>
</tr>

        </table>
    </div>
    <?php if($canEdit && $this->item->checked_out == 0): ?>
		<button type="button" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_jcrm&task=contact.edit&id='.$this->item->id); ?>';"><?php echo JText::_("COM_JCRM_EDIT_ITEM"); ?></button>
	<?php endif; ?>
								<?php if(JFactory::getUser()->authorise('core.delete','com_jcrm.contact.'.$this->item->id)):?>
									<button type="button" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_jcrm&task=contact.remove&id=' . $this->item->id, false, 2); ?>';"><?php echo JText::_("COM_JCRM_DELETE_ITEM"); ?></button>
								<?php endif; ?>
    <?php
else:
    echo JText::_('COM_JCRM_ITEM_NOT_LOADED');
endif;
?>
