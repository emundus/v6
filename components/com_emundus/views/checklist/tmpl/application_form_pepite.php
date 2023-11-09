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

$user   = $session->get('emundusUser');
$itemid = $app->input->get('Itemid', null, 'GET', 'none', 0);

$forms = EmundusHelperMenu::buildMenuQuery($user->profile);
?>
    <ul>
		<?php
		$query = $_db->getQuery(true);
		foreach ($forms as $form) {

			if ($form->db_table_name == "jos_emundus_pepite_projet1") {
				$column = explode(' - ', $form->label);
				$column = strtolower(trim($column[1]));
				$query->clear()->select('count(*)')->from($form->db_table_name)->where('user = ' . $user->id)->where('fnum like ' . $_db->quote($user->fnum))->where($column . ' IS NOT NULL');
			}
			else {
				$query->clear()->select('count(*)')->from($form->db_table_name)->where('user = ' . $user->id)->where('fnum like ' . $_db->quote($user->fnum));
			}
			$_db->setQuery($query);
			$form->nb = $_db->loadResult();

			$link    = '<a href="' . $form->link . '">';
			$active  = $form->id == $itemid ? ' active' : '';
			$need    = $form->nb == 0 ? 'need_missing' : 'need_ok';
			$class   = $need . $active;
			$endlink = '</a>';
			?>
            <li class="em_module <?php echo $class; ?>">
                <div class="em_form"><?php echo $link . $form->title . $endlink; ?></div>
            </li>
		<?php } ?>
    </ul>
<?php
unset($link);
unset($endlink);
?>