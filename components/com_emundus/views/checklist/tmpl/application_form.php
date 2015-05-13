<?php 
//JHTML::_('behavior.modal'); 
JHTML::stylesheet(JURI::Base().'media/com_emundus/css/emundus.css' );
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

$user = JFactory::getUser();
$_db = JFactory::getDBO();
$itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

$forms = EmundusHelperMenu::buildMenuQuery($user->profile);
?>
</ul>
<?php 
foreach ($forms as $form) {
	$query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$user->id. ' AND fnum like '.$_db->Quote($user->fnum);
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