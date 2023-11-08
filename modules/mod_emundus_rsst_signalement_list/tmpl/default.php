<?php
defined('_JEXEC') or die('Restricted Access');

JText::script('MOD_EMUNDUS_RSST_SIGNALEMENT_LIST');
$user         = JFactory::getSession()->get('emundusUser');
$fabrikListId = $params->get('fabrik_list_id');
$ro_profiles  = $params->get('fabrik_list_readonly_profiles');
$read_only    = in_array($user->profile, $ro_profiles);

if (!empty($fabrikListId)) :
	?>
    <div
            id="em-rsst-signalement-list-vue"
            user="<?= $user->id ?>"
            listId="<?= $fabrikListId ?>"
            listActionColumn="<?= $params->get('fabrik_list_particular_action_column') ?>"
            listParticularConditionalColumn="<?= $params->get('fabrik_list_particular_conditional_column') ?>"
            listParticularConditionalColumnValues="<?= $params->get('fabrik_list_particular_conditional_column_values') ?>"
            listColumnShowingAsBadge="<?= $params->get('fabrik_list_column_to_be_shown_as_badge') ?>"
            listColumnToNotShowingWhenFilteredBy="<?= $params->get('fabrik_list_column_to_not_show_when_filtered_by') ?>">
        readOnly="<?= $read_only; ?>"
    </div>
    <script src="media/mod_emundus_rsst_signalement_list/app.js"></script>
<?php else: ?>
    <div>
        <p>Aucun identifiant de liste Fabrik saisi dans le module</p>
    </div>
<?php endif; ?>
