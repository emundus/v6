-- insert new tag --
INSERT INTO jos_emundus_setup_tags (tag, request, description, published) VALUES ('QUICK MESSAGE', 'php|require_once (JPATH_SITE.DS.''components''.DS.''com_emundus''.DS.''models''.DS.''messages.php'');
$m_messages = new EmundusModelMessages();
$fnum = ''[FNUM]'';
if (!empty($fnum)) {
    $_messages = $m_messages->getActionByFnum($fnum);
    if($_messages == true) {
        return "<button type=''button'' style=''background:unset'' class=''send-emails-btn''id=candidat_". $fnum . ''>'' . "<i class=''em-send-email fa fa-envelope-open-text fa-2x'' id=" . $fnum . "></i></button>";
    } else {
    }
}', 'Send results to candidat', 1);