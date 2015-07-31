<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$user = JFactory::getUser();

$doc->addStyleSheet(JUri::base() . 'components/com_emundus/assets/css/item.css');
$doc->addStyleSheet(JUri::base() .'components/com_emundus/assets/css/list.css');
JHtml::stylesheet('bootstrap.min.css', JURI::base() . 'media/com_emundus/lib/bootstrap-emundus/css/');

$canEdit = $user->authorise('core.edit', 'com_emundus.' . $this->item->id);
if (!$canEdit && $user->authorise('core.edit.own', 'com_emundus' . $this->item->id)) {
    $canEdit = $user->id == $this->item->created_by;
}

?>
<?php if ($user->guest): ?>
    <div class="alert alert-warning">
        <b><?php echo JText::_('WARNING'); ?> : </b> <?php echo JText::_('COM_EMUNDUS_THESIS_PLEASE_CONNECT_OR_LOGIN_TO_APPLY'); ?>
    </div>
<?php endif; ?>
<?php if ($this->item) : ?>
    <h1><?php echo $this->item->titre; ?></h1>
    <div class="item_fields">
        <table class="table">    
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_DOMAIN'); ?></th>
                <td><?php echo $this->item->domain; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_KEY_WORDS'); ?></th>
                <td><?php echo $this->item->key_words; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_SUPERVISION'); ?></th>
                <td><?php echo $this->item->supervision; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_JOIN_PHD'); ?></th>
                <td><?php echo $this->item->join_phd; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_THESIS_SUPERVISOR'); ?></th>
                <td><?php echo $this->item->thesis_supervisor; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_CO_SUPERVISOR'); ?></th>
                <td><?php echo $this->item->co_supervisor; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_INSTITUTION'); ?></th>
                <td><?php echo $this->item->institution; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_MEMBERS'); ?></th>
                <td><?php echo $this->item->members; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_DOCTORAL_SCHOOL'); ?></th>
                <td><?php echo $this->item->doctoral_school; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_RESEARCH_LABORATORY'); ?></th>
                <td><?php echo $this->item->research_laboratory; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_ADDRESS'); ?></th>
                <td><?php echo $this->item->address; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_WEBSITE'); ?></th>
                <td><a href="<?php echo $this->item->website; ?>" target="_blank"><?php echo $this->item->website; ?></a></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_LABORATORY_DIRECTOR'); ?></th>
                <td><?php echo $this->item->laboratory_director; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_EMAIL_LABORATORY_DIRECTOR'); ?></th>
                <td><?php echo $this->item->email_laboratory_director; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_SUBJECT_DESCRIPTION'); ?></th>
                <td><?php echo $this->item->subject_description; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_THESIS_EXPECTED_PROFILE_CANDIDAT'); ?></th>
                <td><?php echo $this->item->expected_profile_candidat; ?></td>
            </tr>
        </table>
    </div>
    <?php if($canEdit): ?>
        <button type="button" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_emundus&task=thesis.edit&id='.$this->item->id); ?>';"><?php echo JText::_("COM_EMUNDUS_EDIT_ITEM"); ?></button>
    <?php endif; ?>
    <?php if($user->authorise('core.delete','com_emundus.thesis.'.$this->item->id)):?>
        <button type="button" onclick="window.location.href='<?php echo JRoute::_('index.php?option=com_emundus&task=thesis.remove&id=' . $this->item->id, false, 2); ?>';"><?php echo JText::_("COM_EMUNDUS_DELETE_ITEM"); ?></button>
    <?php endif; ?>
<?php
else:
    echo JText::_('COM_EMUNDUS_ITEM_NOT_LOADED');
endif;
?>
<script type="text/javascript">
    $('rt-top-surround').remove();
    $('rt-header').remove();
    $('rt-footer').remove();
    $('footer').remove();
    $('gf-menu-toggle').remove();
</script>