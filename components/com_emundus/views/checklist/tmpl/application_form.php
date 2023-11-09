<?php
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'menu.php');

JHTML::stylesheet('media/com_emundus/css/emundus.css');

use Joomla\CMS\Factory;

$app = Factory::getApplication();
if (version_compare(JVERSION, '4.0', '>')) {
	$document = $app->getDocument();
	$wa       = $document->getWebAssetManager();
	$wa->registerAndUseStyle('com_emundus.checklist', 'com_emundus/css/emundus_checklist.css', [], ['version' => 'auto', 'relative' => true]);
	$session = $app->getSession();
	$_db     = Factory::getContainer()->get('DatabaseDriver');
}
else {
	$document = Factory::getDocument();
	$document->addStyleSheet("media/com_emundus/css/emundus_checklist.css");
	$session = Factory::getSession();
	$_db     = Factory::getDBO();
}

$user = $session->get('emundusUser');

$itemid = $app->input->get('Itemid', null, 'GET', 'none', 0);

$h_menu = new EmundusHelperMenu();
$forms  = $h_menu->getUserApplicationMenu($user->profile);
?>
    <ul>
		<?php
		$query = $_db->getQuery(true);

		foreach ($forms as $form) {
			$query->clear()->select('count(*)')->from($form->db_table_name)->where('user = ' . $user->id)->where('fnum like ' . $_db->quote($user->fnum));
			$_db->setQuery($query);
			$form->nb = $_db->loadResult();

			$link     = '<a href="' . $form->link . '">';
			$active   = $form->id == $itemid ? ' active' : '';
			$need     = $form->nb == 0 ? 'need_missing' : 'need_ok';
			$class    = $need . $active;
			$endlink  = '</a>';
			$title    = preg_replace('/^([^-]+ - )/', '', $form->label);
			$linkForm = $link . JText::_(trim($title)) . $endlink;
			?>
            <li class="em_module <?php echo $class; ?>">
                <div class="em_form em-checklist"><?php echo $linkForm; ?></div>
            </li>
		<?php } ?>
    </ul>
<?php
unset($link);
unset($endlink);
?>