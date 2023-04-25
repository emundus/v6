<?php 
//JHTML::_('behavior.modal'); 
JHTML::stylesheet('media/com_emundus/css/emundus.css' );

$document = JFactory::getDocument();
$document->addStyleSheet("media/com_emundus/css/emundus_checklist.css" );

defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'menu.php');

$user = JFactory::getSession()->get('emundusUser');
$_db = JFactory::getDBO();
$itemid = JFactory::getApplication()->input->get('Itemid', null, 'GET', 'none', 0);

$h_menu = new EmundusHelperMenu();
$forms = $h_menu->getUserApplicationMenu($user->profile);
?>
<ul>
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
    // TODO: implement this way of writing title everywhere (instead of the explode)
    $title = preg_replace('/^([^-]+ - )/', '', $form->label);
    $linkForm = $link . JText::_(trim($title)) . $endlink;
?>
	<li class="em_module <?php echo $class; ?>"><div class="em_form em-checklist"><?php echo $linkForm; ?></div></li>
<?php } ?>
</ul>
<?php
unset($link);
unset($endlink);
?>