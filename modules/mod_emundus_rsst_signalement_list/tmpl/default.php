<?php

defined('_JEXEC') or die('Restricted Access');

JText::script('MOD_EMUNDUS_RSST_SIGNALEMENT_LIST');

$user = JFactory::getSession()->get('emundusUser');

echo '<div id="em-rsst-signalement-list-vue" user="'. $user->id . '" listId="'. $params->get('fabrik_list_id') . '" listActionColumn="'. $params->get('fabrik_list_particular_action_column') .'" listParticularConditionalColumn="'.$params->get('fabrik_list_particular_conditional_column').'" listParticularConditionalColumnValues="'.$params->get('fabrik_list_particular_conditional_column_values').'">
</div>';


?>

<script src="media/mod_emundus_rsst_signalement_list/app.js"></script>
