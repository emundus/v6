INSERT INTO jos_emundus_setup_tags (tag, request, description, published)
VALUES ('MESSENGER_NOTIFY', 'php|require_once (JPATH_SITE.DS.''components''.DS.''com_emundus_messenger''.DS.''models''.DS.''messages.php'');
$m_messages = new EmundusmessengerModelmessages();
$fnum = ''[FNUM]'';
if (!empty($fnum)) {
    return ''<p class="messenger__notifications_counter">'' . $m_messages->getNotificationsByFnum($fnum) . ''</p>'';
}
', '', 0);
