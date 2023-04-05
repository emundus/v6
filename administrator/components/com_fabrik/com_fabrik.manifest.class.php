<?php
/**
 * Fabrik: Installer Manifest Class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @author      Henk
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Language\Text;

class Com_FabrikInstallerScript
{
	/**
	 * Run before installation or upgrade run
	 *
	 * @param   string $type   discover_install (Install unregistered extensions that have been discovered.)
	 *                         or install (standard install)
	 *                         or update (update)
	 * @param   object $parent installer object
	 *
	 * @return  void
	 */
	public function preflight($type, $parent)
	{ 

	}

	/**
	 * Run when the component is installed
	 *
	 * @param   object $parent installer object
	 *
	 * @return bool
	 */
	public function install($parent)
	{
		$parent->getParent()->setRedirectURL('index.php?option=com_fabrik');

		return true;
	}

	/**
	 * Run when the component is updated
	 *
	 * @param   object $parent installer object
	 *
	 * @return  bool
	 */
	public function update($parent)
	{
		// Needs revision. Deprecated plugins already uninstalled in 3.10 or earlier. Do we have other deprecated ?
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$app   = Factory::getApplication();
		$msg = array();

		// Uninstalled plugins.
		$plugins = array(
			'fabrik_element' => array('fbactivityfeed', 'fblikebox', 'fbrecommendations'),
			'fabrik_form' => array('vbforum')
		);

		// Deprecated - 'timestamp', 'exif'
		$query->select('*')->from('#__extensions');

		foreach ($plugins as $folder => $plugs)
		{
			$query->where('(folder = ' . $db->q($folder) . ' AND element IN (' . implode(', ', $db->q($plugs)) . '))', 'OR');

			foreach ($plugs as $plug)
			{
				$path = JPATH_PLUGINS . '/' . $folder . '/' . $plug;

				if (Folder::exists($path))
				{
					Folder::delete($path);
				}
			}
		}

		$deprecatedPlugins = $db->setQuery($query)->loadObjectList();

		if (!empty($deprecatedPlugins))
		{
			$ids = ArrayHelper::getColumn($deprecatedPlugins, 'extension_id');
			$ids = ArrayHelper::toInteger($ids);

			$query->clear()->delete('#__extensions')->where('extension_id IN ( ' . implode(',', $ids) . ')');
			$db->setQuery($query)->execute();

			// Un-publish elements
			$query->clear()->select('id, name, label')->from('#__fabrik_elements')
				->where('plugin IN (' . implode(', ', $db->q($plugins['fabrik_element'])) . ')')
				->where('published = 1');
			$db->setQuery($query);
			$unpublishedElements = $db->loadObjectList();
			$unpublishedIds      = ArrayHelper::getColumn($unpublishedElements, 'id');

			if (!empty($unpublishedIds))
			{
				$msg[] = 'The following elements have been unpublished as their plug-ins have been uninstalled. : ' . implode(', ', $unpublishedIds);
				$query->clear()
					->update('#__fabrik_elements')->set('published = 0')->where('id IN (' . implode(',', $db->q($unpublishedIds)) . ')');
				$db->setQuery($query)->execute();
			}
		}

		// Un-publish form plug-ins. Maybe do this for more plugins ?
		$query->clear()->select('id, params')->from('#__fabrik_forms');
		$forms = $db->setQuery($query)->loadObjectList();
		foreach ($forms as $form)
		{
			$params = json_decode($form->params);
			$found = false;

			if (isset($params->plugins))
			{
				for ($i = 0; $i < count($params->plugins); $i++)
				{
					if (in_array($params->plugins[$i], $plugins['fabrik_form']))
					{
						$msg[]                    = 'Form ' . $form->id . '\'s plugin \'' . $params->plugins[$i] .
							'\' has been unpublished';
						$params->plugin_state[$i] = 0;
						$found = true;
					}
				}

				if ($found)
				{
					$query->clear()->update('#__fabrik_forms')->set('params = ' . $db->q(json_encode($params)))
						->where('id = ' . (int) $form->id);

					$db->setQuery($query)->execute();
				}
			}
		}

		if (!empty($msg))
		{
			$app->enqueueMessage(implode('<br>', $msg), 'warning');
		}

		return true;
	}

	/**
	 * Run when the component is uninstalled.
	 *
	 * @param   object $parent installer object
	 *
	 * @return  void
	 */
	public function uninstall($parent)
	{
	}

	/**
	 * Run after installation or upgrade run
	 *
	 * @param   string $type   discover_install (Install unregistered extensions that have been discovered.)
	 *                         or install (standard install)
	 *                         or update (update)
	 * @param   object $parent installer object
	 *
	 * @return  bool
	 */
	public function postflight($type, $parent)
	{

		if ($type !== 'uninstall')
		{

			$this->fixMenuComponentId();

			if ($this->templateOverride() === false) return false;
		}

		if ($type !== 'update' && $type !== 'uninstall')
		{
			if (!$this->setConnection())
			{
				echo "<p style=\"color:red\">Didn't set connection. Aborting installation</p>";
				exit;

				return false;
			}
			echo "<p style=\"color:green\">Default connection created</p>";
		}

		if ($type !== 'update')
		{
			if (!$this->setDefaultProperties())
			{
				echo "<p>couldnt set default properties</p>";
				exit;

				return false;
			}
		}

		if ($type == 'uninstall') {
			
			// Remove empty folders if exist
			$path = JPATH_ROOT.'/media/com_fabrik';		
			if(Folder::exists($path)) Folder::delete($path);
			$pluginFolders =['comunnity/fabrik', 'content/fabrik', 'fabrik_cron', 'fabrik_element', 'fabrik_form', 'fabrik_list', 'fabrik_validationrule', 'fabrik_visualization', 'search/fabrik', 'system/fabrik', 'system/fabrik_cron', 'system/fabrikj2store'];
			foreach ($pluginFolders as $pluginFolder) {
				$path = JPATH_ROOT.'/plugins/'.$pluginFolder;	
				if (Folder::exists($path) && empty(Folder::files($path))) {
					Folder::delete($path);
				}
			}
			
			// Remove our admin template override
			$this->templateOverride(false);
			/* Remove plugin files */
			$pluginTables = [
				"#__fabrik_comments",
				"#__fabrik_change_log_fields",
				"#__fabrik_change_log",
				"#__fabrik_notification_event",
				"#__fabrik_notification_event_sent",
				"#__fabrik_notification",
				"#__fabrik_privacy",
				"#__fabrik_ratings",
				"#__fabrik_sequences",
				"#__fabrik_subs_users",
				"#__fabrik_subs_subscriptions",
				"#__fabrik_subs_plan_billing_cycle",
				"#__fabrik_subs_payment_gateways",
				"#__fabrik_subs_cron_emails",
				"#__fabrik_subs_plans",
				"#__fabrik_subs_invoices",
				"#__fabrik_thumbs",
			];
			foreach ($pluginTables as $pluginTable) {
				$db->setQuery("DROP TABLE IF EXISTS $pluginTable")->execute();
			}

		}

		if ($type !== 'uninstall') {
			// Remove old J!3 files & folders if exist
			$oldAdminFiles =['controllers/package.php', 'controllers/packages.php', 'controllers/package.raw.php', 'controllers/upgrade.php', 'models/package.php', 'models/packages.php', 
				'models/upgrade.php', 'models/forms/package.php', 'models/forms/packagelist.php', 'models/fields/packagelist.php', 'models/fields/twittersignin.php', 'tables/package.php'];
			foreach ($oldAdminFiles as $oldAdminFile) {
				$path = JPATH_ADMINISTRATOR.'/components/com_fabrik/'.$oldAdminFile;	
				if (File::exists($path)) File::delete($path);
			}
			$oldAdminFolders =['com_fabrik_skeleton', 'views/package', 'views/packages', 'update'];
			foreach ($oldAdminFolders as $oldAdminFolder) {
				$path = JPATH_ADMINISTRATOR.'/components/com_fabrik/'.$oldAdminFolder;	
				if (Folder::exists($path)) Folder::delete($path);
			}
			$oldSiteFiles =['controllers/package.php', 'models/package.php'];
			foreach ($oldSiteFiles as $oldSiteFile) {
				$path = JPATH_ROOT.'/components/com_fabrik/'.$oldSiteFile;	
				if (File::exists($path)) File::delete($path);
			}
			$oldSiteFolders =['dbdriver', 'driver', 'Document', 'fabrik', 'jhelpers', 'sef_ext', 'views/details/tmpl25', 'views/form/tmpl25', 'views/list/tmpl25', 'views/package'];
			foreach ($oldSiteFolders as $oldSiteFolder) {
				$path = JPATH_ROOT.'/components/com_fabrik/'.$oldSiteFolder;	
				if (Folder::exists($path)) Folder::delete($path);
			}
			$oldLibFiles =['PdfDocument.php', 'PartialDocument.php'];
			foreach ($oldLibFiles as $oldLibFile) {
				$path = JPATH_ROOT.'/libraries/src/Document/'.$oldLibFile;	
				if (File::exists($path)) File::delete($path);
			}
			$oldLibFolders =['Pdf', 'Partial'];
			foreach ($oldLibFolders as $oldLibFolder) {
				$path = JPATH_ROOT.'/libraries/src/Document/Renderer/'.$oldLibFolder;	
				if (Folder::exists($path)) Folder::delete($path);
			}
		}
	}

	/**
	 * Check if there is a connection already installed if not create one
	 * by copying over the site's default connection
	 *
	 * @return  bool
	 */
	protected function setConnection()
	{
		$db               = Factory::getContainer()->get('DatabaseDriver');
		$app              = Factory::getApplication();
		$row              = new stdClass;
		$row->host        = $app->get('host');
		$row->user        = $app->get('user');
		$row->password    = $app->get('password');
		$row->database    = $app->get('db');
		$row->description = 'site database';
		$row->params      = '';
		$row->checked_out = 0;
		$row->published   = 1;
		$row->default     = 1;
		$res              = $db->insertObject('#__fabrik_connections', $row, 'id');

		return $res;
	}

	/**
	 * Test to ensure that the main component params have a default setup
	 *
	 * @return  bool
	 */
	protected function setDefaultProperties()
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('extension_id, params')->from('#__extensions')
			->where('name = ' . $db->q('fabrik'))
			->where('type = ' . $db->q('component'));
		$db->setQuery($query);
		$row                                 = $db->loadObject();
		$opts                                = new stdClass;
		$opts->fbConf_wysiwyg_label          = 0;
		$opts->fbConf_alter_existing_db_cols = 0;
		$opts->spoofcheck_on_formsubmission  = 0;

		if ($row && ($row->params == '{}' || $row->params == ''))
		{
			$json  = $row->params;
			$query = $db->getQuery(true);
			$query->update('#__extensions')->set('params = ' . $db->quote($json))
				->where('extension_id = ' . (int) $row->extension_id);
			$db->setQuery($query);

			if (!$db->execute())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Does this still apply in 4.0 ?
	 * God knows why but install component, uninstall component and install
	 * again and component_id is set to 0 for the menu items
	 *
	 * @return  bool
	 */
	protected function fixMenuComponentId()
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('extension_id')->from('#__extensions')->where('element = ' . $db->q('com_fabrik'));
		$db->setQuery($query);
		$id = (int) $db->loadResult();
		$query->clear();
		$query->update('#__menu')->set('component_id = ' . $id)->where('path LIKE ' . $db->q('fabrik%'));

		return $db->setQuery($query)->execute();
	}

	/* Copy our admin template overrides to the site admin template */
	protected function templateOverride($install = true)
	{
		/* Get the current admin template, probably atum for J4 */
		$templateName = Factory::getApplication()->getTemplate();
		/* We will do some validation before we blindly overwrite anything */
		$overrides = [
			'params.php' => [ "loc" => JPATH_ADMINISTRATOR.'/components/com_fabrik/overrides/joomla/edit/',
								"pathParts" => ['html', 'layouts', 'joomla', 'edit'],
								"tag" => "FABRIK_JOOMLA_EDIT_LAYOUT_OVERRIDE"
							],
			'list.php' => [ "loc" => JPATH_ADMINISTRATOR.'/components/com_fabrik/overrides/joomla/form/field/',
								"pathParts" => ['html', 'layouts', 'joomla', 'form', 'field'],
								"tag" => "FABRIK_JOOMLA_LISTFIELD_LAYOUT_OVERRIDE"
							],
		];
		foreach ($overrides as $filename => $data) {
			$loc = $data['loc'];
			$pathParts = $data['pathParts'];
			$tag = $data['tag'];

			/* Check if there is already an override in place, creating any new directories as we go along */
			do {
				$path = JPATH_ADMINISTRATOR.'/templates/'.$templateName;
				foreach ($pathParts as $pathPart) {
					$path .= '/'.$pathPart;
					if (Folder::exists($path) === false && Folder::create($path) === false) {
						throw new RuntimeException('An error occurred creating path: $path. Please check your permissions.');
						return false;
					}
				}
				/* Check if an override file of our name exists */
				$file = $path."/$filename";
				if (File::exists($file) === false) break;
				/* It does exist, is it ours? */
				$buffer = file_get_contents($file);
				if (strpos($buffer, $tag) === false) {
					/* There is already an override for this layout and it is not ours */
					throw new RuntimeException("An $filename layout override that is not ours is already installed. Please contact Fabrik support for assistance.");
					return false;
				}
				/* The override exists and it is ours, delete and replace it in case it has been updated */
				if (File::delete($file) === false) {
					throw new RuntimeException("Layout override ($file) delete failed.  Please check your permissions.");
					return false;
				};
			} while(0);
			/* We are good to go */
			switch ($install) {
				case true:
					if (File::copy($loc."/".$filename, $file) === false) {
						throw new RuntimeException("Layout override ($file) copy failed.  Please check your permissions.");
						return false;
					}
					break;
				case false:
					/* The file itself will already be deleted */
					/* Remove any empty folders in the tree */

					$dir = $file;
					foreach (array_reverse($pathParts) as $path) {
						$dir = dirname($dir);	
						if (Folder::exists($dir) === false) continue;
						if (empty(Folder::files($dir)) && empty(Folder::folders($dir))) {
							if (Folder::delete($dir) === false) {
								throw new RuntimeException("Failed to delete empty folder $dir.  Please check your permissions.");
							}
						}
					}

					break;
			}
		}
	}
}
