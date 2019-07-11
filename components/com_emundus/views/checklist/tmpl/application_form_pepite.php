<?php 
//JHTML::_('behavior.modal'); 
JHTML::stylesheet(  'media/com_emundus/css/emundus.css' );

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/css/emundus_checklist.css" );

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

$user = JFactory::getSession()->get('emundusUser');
$_db = JFactory::getDBO();
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

$forms = @EmundusHelperMenu::buildMenuQuery($user->profile);

/*$app = JFactory::getApplication();
$alias = $app->getMenu()->getActive()->alias;*/
?>
</ul>
<?php 
foreach ($forms as $form) {
	
	if ($form->db_table_name == "jos_emundus_pepite_projet1") {
		$column = explode(' - ', $form->label);
		$column = strtolower(trim($column[1]));
		$query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE `user` = '.$user->id. ' AND `fnum` like '.$_db->Quote($user->fnum).' AND (`'.$column.'` IS NOT NULL OR `'.$column.'` != "")';	
	} else {
		$query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE `user` = '.$user->id. ' AND `fnum` like '.$_db->Quote($user->fnum);
	}

	$_db->setQuery( $query );
	$form->nb = $_db->loadResult();
	$link 	= '<a href="'.$form->link.'">';
	$active = $form->id==$itemid?' active':'';
	$need = $form->nb==0?'need_missing':'need_ok';
	$class = $need.$active;
	$endlink= '</a>';
?>
	<li class="em_module <?php echo $class; ?>"><div class="em_form"><?php echo $link.$form->title.$endlink; ?></div></li>
<?php } ?>
</ul>
<?php
unset($link);
unset($endlink);
?>