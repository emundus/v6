<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2019. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$user = JFactory::getUser();

$doc->addStyleSheet('components/com_emundus/assets/css/item.css');
$doc->addStyleSheet('components/com_emundus/assets/css/list.css');

$offset = JFactory::getConfig()->get('offset');

$canEdit = $user->authorise('core.edit', 'com_emundus.' . $this->item->id);
if (!$canEdit && $user->authorise('core.edit.own', 'com_emundus' . $this->item->id)) {
    $canEdit = $user->id == $this->item->created_by;
}

?>
<?php if ($user->guest): ?>
    <div class="alert alert-error">
        <b><?php echo JText::_('COM_EMUNDUS_JOBS_PLEASE_CONNECT_OR_LOGIN_TO_APPLY'); ?>
    </div>
<?php endif; ?>
<?php if ($this->item) : ?>
    <h1><?php echo $this->item->intitule_poste; ?></h1>
    <div class="item_fields">
        <table class="table job">
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_ETABLISSEMENT'); ?></th>
                <td><?php echo $this->item->etablissement; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_SERVICE'); ?></th>
                <td><?php echo $this->item->service; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_DOMAINE'); ?></th>
                <td><?php echo $this->item->domaine; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_LOCALISATION'); ?></th>
                <td><?php echo $this->item->localisation; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_DESCRIPTION'); ?></th>
                <td><?php echo $this->item->description; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_NIVEAU'); ?></th>
                <td><?php echo $this->item->niveau; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_DOMAINE_ETUDES'); ?></th>
                <td><?php echo $this->item->domaine_etudes; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_COMPETENCES'); ?></th>
                <td><?php echo $this->item->competences; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_MISSION'); ?></th>
                <td><?php echo $this->item->mission; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_NB_HEURES'); ?></th>
                <td><?php echo $this->item->nb_heures; ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_DATE_DEBUT'); ?></th>
                <td><?php echo JFactory::getDate(new JDate($this->item->date_debut, $offset))->format('d/m/Y'); ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_DATE_FIN'); ?></th>
                <td><?php echo JFactory::getDate(new JDate($this->item->date_fin, $offset))->format('d/m/Y'); ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_DATE_LIMITE'); ?></th>
                <td><?php echo JFactory::getDate(new JDate($this->item->date_limite, $offset))->format('d/m/Y'); ?></td>
            </tr>
            <tr>
                <th><?php echo JText::_('COM_EMUNDUS_FORM_LBL_JOB_RESPONSABLE_EMAIL'); ?></th>
                <td><?php 
                    $value = $user->id > 0 ? $this->item->adresse_correspondance : JText::_('COM_EMUNDUS_HIDE_FOR_GUEST');
                    echo $value; ?>
                </td>
            </tr>


        </table>
    </div>

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