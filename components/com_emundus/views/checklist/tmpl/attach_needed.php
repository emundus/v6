<?php
defined('_JEXEC') or die('Restricted access');
JHTML::stylesheet( 'media/com_emundus/css/emundus.css' );

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/css/emundus_checklist.css" );

$user = JFactory::getSession()->get('emundusUser');
$_db = JFactory::getDBO();

$query='SELECT id, link FROM #__menu WHERE alias like "checklist%" AND menutype like "%'.$user->menutype.'"';
$_db->setQuery($query);
$itemid = $_db->loadAssoc();

if (!empty($user->campaign_id)) {

    $query='SELECT esa.value, esap.id as _id, esa.id as id
	FROM #__emundus_setup_attachment_profiles esap
	JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id
    WHERE esap.displayed = 1 AND esap.mandatory = 1 AND (esap.campaign_id ='.$user->campaign_id.' OR esap.profile_id ='.$user->profile.')
	ORDER BY esap.ordering';

    $_db->setQuery( $query );
    $forms = $_db->loadObjectList();
}

if (empty($forms)) {

    $query = 'SELECT esa.value, esap.id as _id, esa.id as id
	FROM #__emundus_setup_attachment_profiles esap
	JOIN #__emundus_setup_attachments esa ON esa.id = esap.attachment_id
	WHERE esap.displayed = 1 AND esap.mandatory = 1 AND esap.profile_id ='.$user->profile.' AND esap.campaign_id IS NULL  
	ORDER BY esa.ordering';

    $_db->setQuery($query);
    try {
	    $forms = $_db->loadObjectList();
    } catch (Exception $e) {
        JLog::add('Error in views/tmpl/attach_needed at query : '.$query, JLog::ERROR, 'com_emundus');
    }
}
?>
</ul>
<?php
if (count($forms) > 0) {
	foreach ($forms as $form) {
		$query = 'SELECT count(id) FROM #__emundus_uploads up
					WHERE up.user_id = '.$user->id.' AND up.attachment_id = '.$form->id.' AND fnum like '.$_db->Quote($user->fnum);
		$_db->setQuery($query);
		$cpt = $_db->loadResult();
		$link = '<a id="'.$form->id.'" class="document" href="'.$itemid['link'].'&Itemid='.$itemid['id'].'#a'.$form->id.'">';
		if ($cpt == 0) {
		    $class = 'need_missing';
		} else {
		    $class = 'need_ok';
		}
		$endlink= '</a>';
	?>
	    <li class="em_module <?php echo $class; ?>"><div class="em_form em-checklist"><?php echo $link.$form->value.$endlink; ?></div></li>
	<?php
	}
}
 ?>
</li>
<?php
unset($link);
unset($endlink);
?>
