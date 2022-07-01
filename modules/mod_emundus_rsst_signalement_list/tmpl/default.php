<?php

defined('_JEXEC') or die('Restricted Access');

JText::script('MOD_EMUNDUS_RSST_SIGNALEMENT_LIST');

$user = JFactory::getSession()->get('emundusUser');

echo '<div id="em-rsst-signalement-list-vue" user="'. $user->id . '"></div>';
?>

<script src="media/mod_emundus_rsst_signalement_list/app.js"></script>