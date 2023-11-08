<?php

defined('_JEXEC') or die('Restricted Access');

JText::script('COM_EMUNDUS_MESSENGER_TITLE');
JText::script('COM_EMUNDUS_MESSENGER_SEND_DOCUMENT');
JText::script('COM_EMUNDUS_MESSENGER_ASK_DOCUMENT');
JText::script('COM_EMUNDUS_MESSENGER_DROP_HERE');
JText::script('COM_EMUNDUS_MESSENGER_SEND');
JText::script('COM_EMUNDUS_MESSENGER_WRITE_MESSAGE');
JText::script('COM_EMUNDUS_MESSENGER_TYPE_ATTACHMENT');
JText::script('COM_EMUNDUS_PLEASE_SELECT');

$user = JFactory::getSession()->get('emundusUser')->id;

$jinput = JFactory::getApplication()->input;
$fnum   = $jinput->getString('rowid', null);
?>

<div id="em-notifications" user="<?= $user ?>" fnum="<?= $fnum ?>"></div>

<script src="media/mod_emundus_messenger_notifications/app.js"></script>
