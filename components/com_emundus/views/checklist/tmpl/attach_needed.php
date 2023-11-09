<?php
defined('_JEXEC') or die('Restricted access');
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

$query = $_db->getQuery(true);

$query->select('id,link')
	->from('#__menu')
	->where($_db->quoteName('alias') . ' LIKE ' . $_db->quote('checklist%')
			->where($_db->quoteName('menutype') . ' LIKE ' . $_db->quote('%' . $user->menutype . '%')));
$_db->setQuery($query);
$itemid = $_db->loadAssoc();

if (!empty($user->campaign_id)) {
	$query->clear()
		->select('esa.value, esap.id as _id, esa.id as id')
		->from($_db->quoteName('#__emundus_setup_attachment_profiles', 'esap'))
		->leftJoin($_db->quoteName('#__emundus_setup_attachments', 'esa') . ' ON ' . $_db->quoteName('esa.id') . ' = ' . $_db->quoteName('esap.attachment_id'))
		->where($_db->quoteName('esap.displayed') . ' = 1')
		->where($_db->quoteName('esap.mandatory') . ' = 1')
		->where('(' . $_db->quoteName('esap.campaign_id') . ' = ' . $_db->quote($user->campaign_id) . ' OR ' . $_db->quoteName('esap.profile_id') . ' = ' . $_db->quote($user->profile) . ')')
		->order($_db->quoteName('esap.ordering'));
	$_db->setQuery($query);
	$forms = $_db->loadObjectList();
}

if (empty($forms)) {
	$query->clear()
		->select('esa.value, esap.id as _id, esa.id as id')
		->from($_db->quoteName('#__emundus_setup_attachment_profiles', 'esap'))
		->leftJoin($_db->quoteName('#__emundus_setup_attachments', 'esa') . ' ON ' . $_db->quoteName('esa.id') . ' = ' . $_db->quoteName('esap.attachment_id'))
		->where($_db->quoteName('esap.displayed') . ' = 1')
		->where($_db->quoteName('esap.mandatory') . ' = 1')
		->where('(' . $_db->quoteName('esap.profile') . ' = ' . $_db->quote($user->profile))
		->where($_db->quoteName('esap.campaign_id') . ' IS NULL)')
		->order($_db->quoteName('esap.ordering'));
	$_db->setQuery($query);
	try {
		$forms = $_db->loadObjectList();
	}
	catch (Exception $e) {
		JLog::add('Error in views/tmpl/attach_needed at query : ' . $query, JLog::ERROR, 'com_emundus');
	}
}
?>
<ul>
	<?php
	if (count($forms) > 0) {
		foreach ($forms as $form) {
			$query->clear()
				->select('count(id)')
				->from($_db->quoteName('#__emundus_uploads'))
				->where($_db->quoteName('user_id') . ' = ' . $_db->quote($user->id))
				->where($_db->quoteName('attachment_id') . ' = ' . $_db->quote($form->id))
				->where($_db->quoteName('fnum') . ' LIKE ' . $_db->quote($user->fnum));
			$_db->setQuery($query);
			$cpt = $_db->loadResult();

			$link = '<a id="' . $form->id . '" class="document" href="' . $itemid['link'] . '&Itemid=' . $itemid['id'] . '#a' . $form->id . '">';
			if ($cpt == 0) {
				$class = 'need_missing';
			}
			else {
				$class = 'need_ok';
			}
			$endlink = '</a>';
			?>
            <li class="em_module <?php echo $class; ?>">
                <div class="em_form em-checklist"><?php echo $link . $form->value . $endlink; ?></div>
            </li>
			<?php
		}
	}
	?>
</ul>
<?php
unset($link);
unset($endlink);
?>
