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
        </table>
    </div>
<?php else:
    echo JText::_('COM_JCRM_ITEM_NOT_LOADED');
endif; ?>
