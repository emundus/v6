<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_CONFIGURATION . '/configuration.php';


class com_emundusInstallerScript
{
	protected $manifest_cache;
	protected $schema_version;
	protected EmundusHelperUpdate $h_update;

	public function __construct()
	{
		// Get component manifest cache
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('manifest_cache')
			->from('#__extensions')
			->where("element = 'com_emundus'");
		$db->setQuery($query);
		$this->manifest_cache = json_decode($db->loadObject()->manifest_cache);

		$query->clear()
			->select('version_id')
			->from($db->quoteName('#__schemas'))
			->where($db->quoteName('extension_id') . ' = ' . $db->quote(700));
		$db->setQuery($query);
		$this->schema_version = $db->loadResult();

		require_once(JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');
		$this->h_update = new EmundusHelperUpdate();
	}


	/**
	 * @param $type
	 * @param $parent
	 *
	 *
	 * @since version 1.33
	 */
	public function install($type, $parent)
	{
	}


	/**
	 * Actions to run if we uninstall eMundus component
	 *
	 * @since version 1.33.0
	 */
	public function uninstall()
	{
	}


	/**
	 * @param $parent
	 *
	 *
	 * @since version 1.33.0
	 */
	public function update($parent)
	{
		$succeed = [];

		require_once(JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');
		$cache_version = $this->manifest_cache->version;

		# Check first run
		$firstrun = false;
		$regex    = '/^6\.[0-9]*/m';
		preg_match_all($regex, $cache_version, $matches, PREG_SET_ORDER, 0);
		if (!empty($matches))
		{
			$cache_version = (string) $parent->manifest->version;
			$firstrun      = true;
		}

		if ($this->manifest_cache)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			# First run condition
			if (version_compare($cache_version, '1.33.0', '<') || $firstrun)
			{
				# Delete emundus sql files in con_admin
				#$this->deleteOldSqlFiles();

				# Update SCP params
				EmundusHelperUpdate::updateSCPParams('pro_plugin', array('email_active', 'email_on_admin_login'), array('0', '0'));

				EmundusHelperUpdate::genericUpdateParams('#__modules', 'module', 'mod_emundusflow', array('show_programme'), array('0'));
				EmundusHelperUpdate::genericUpdateParams('#__fabrik_cron', 'plugin', 'emundusrecall', array('log', 'log_email', 'cron_rungate'), array('0', 'mail@emundus.fr', '1'));

				EmundusHelperUpdate::updateConfigurationFile('lifetime', '45');

				# Insert translations in override file
				EmundusHelperUpdate::insertTranslationsTag('CREATE_A_NEW_FILE', 'Créer un nouveau dossier pour un utilisateur existant', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('CREATE_A_NEW_FILE', 'Create a new folder for an existing user', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SEND_CREDENTIALS_BY_EMAIL', 'Envoyer un email d\'information lors de l\'importation', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SEND_CREDENTIALS_BY_EMAIL', 'Send an information email when importing', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_USERS_LOGIN_NO_ACCOUNT', 'Pas encore de compte ?', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COM_USERS_LOGIN_NO_ACCOUNT', 'No account yet?', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_USERS_SUBMIT_RESET', 'Réinitialiser mon mot de passe', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COM_USERS_SUBMIT_RESET', 'Reset my password', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('PROGRAMME_LOGO', 'Logo du programme', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('PROGRAMME_LOGO', 'Programme logo', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('PROGRAMME', 'Programme', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('PROGRAMME', 'Programme', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('IS_LIMITED', 'Limiter les candidatures', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('IS_LIMITED', 'Limiting applications', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_DOCUMENTS', 'Copier les documents', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_DOCUMENTS', 'Copy attachments', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_DELETE_FROM_FILE', "Supprimer le dossier d'origine après la copie", 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_DELETE_FROM_FILE', 'Delete the original file after copying', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_MOVE_HIKASHOP', 'Déplacer les commandes Hikashop', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_MOVE_HIKASHOP', 'Move Hikashop commands', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_TAG', 'Copier les étiquettes', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_TAG', 'Copy the tags', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('LIMIT', 'Limite', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('LIMIT', 'Limit', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('LIMIT_STATUS', 'Statut à limiter', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('LIMIT_STATUS', 'Status to limit', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('TIMEZONE', 'Timezone : ', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('TIMEZONE', 'Timezone : ', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('LOGOUT', 'Logout', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('FORMATION', 'Formation', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('FORMATION', 'Training', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('CREATE_NEW_MENU', 'Créer un nouveau menu', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('CREATE_NEW_MENU', 'Create new menu', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('VIDEO_MAX_LENGTH', 'Taille max de la vidéo', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('VIDEO_MAX_LENGTH', 'Video max length', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ATTACHMENTS_MIN_PDF', 'Nombre minimum de pages dans le pdf', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ATTACHMENTS_MIN_PDF', 'Minimum page number in pdf', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ATTACHMENTS_MAX_PDF', 'Nombre de page(s) maximum pour le pdf', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ATTACHMENTS_MAX_PDF', 'Maximum number of page(s) for the pdf', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('TABLE_SETUP_LETTERS_INTRO', 'Générer automatiquement des courriers personnalisés pour vos candidats. Vous pouvez y inclure des champs dynamiques appelés «balises», qui seront remplacés par les données renseignées par vos candidats.', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('TABLE_SETUP_LETTERS_INTRO', 'Automatically generate personalised mailings for your candidates. You can include dynamic fields called "tags", which will be replaced by the data filled in by your candidates.', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CAMPAIGNS', 'Pour quelle(s) campagnes(s) souhaitez-vous que ce courrier soit généré ?', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CAMPAIGNS', 'For which campaign(s) would you like this mail to be generated?', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_PROGRAMS', 'Pour quel(s) programme(s) souhaitez-vous que ce courrier soit généré ?', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_PROGRAMS', 'For which programme(s) do you want this mail to be generated?', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_STATUS', 'Pour quel(s) statut(s) souhaitez-vous que ce courrier soit généré ?', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_STATUS', 'For which status(es) do you want this mail to be generated?', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_INTRO', "<p style='text-align: right;'><a href='https://emundus.atlassian.net/wiki/external/1668448315/YmRiNGUyMjI3ODQ4NDZjZWIxNDRiMWQ4ZDIwODQwNGI?atlOrigin=eyJpIjoiMjZiMTk4ZWVhMDk3NDExN2JhOWNkYTk4YjFiZmQ2MzgiLCJwIjoiYyJ9' target='_blank' rel='noopener noreferrer'>Besoin d'aide ?</a></p>
                ", 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_INTRO', "<p style='text-align: right;'><a href='https://emundus.atlassian.net/wiki/external/1668448315/YmRiNGUyMjI3ODQ4NDZjZWIxNDRiMWQ4ZDIwODQwNGI?atlOrigin=eyJpIjoiMjZiMTk4ZWVhMDk3NDExN2JhOWNkYTk4YjFiZmQ2MzgiLCJwIjoiYyJ9' target='_blank' rel='noopener noreferrer'>Need help ?</a></p>
                ", 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_DOCUMENT_TYPE_HINT', "Votre modèle de courrier doit être rattaché à un type de document pour être visible dans vos dossiers. Si vous ne trouvez pas votre type de document, vous pouvez en <a href='administration-site/types-documents/form/34'>créer un nouveau</a>.", 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_DOCUMENT_TYPE_HINT', "Your mail template must be attached to a document type to be visible in your folders. If you cannot find your document type, you can <a href='administration-site/types-documents/form/34'>create a new one</a>.", 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CAMPAIGNS_HINT', 'Ce courrier ne sera disponible que lorsque le statut du candidat fera partie de ceux que vous avez sélectionné.', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CAMPAIGNS_HINT', "This mail will only be available when the applicant's status is one of those you have selected.", 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_PROGRAMS_HINT', 'Ce courrier ne sera disponible que pour les candidats inscrits dans le(s) programme(s) sélectionné(s).', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_PROGRAMS_HINT', 'This mail will only be available to applicants registered in the selected programme(s).', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_STATUS_HINT', 'Pour quel(s) statut(s) souhaitez-vous que ce courrier soit généré ?', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_STATUS_HINT', 'For which status(es) do you want this mail to be generated?', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CREATE_A_TEMPLATE_FROM_HINT', 'Créez votre modèle directement depuis votre plateforme ou depuis un logiciel externe.', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CREATE_A_TEMPLATE_FROM_HINT', 'Create your template directly from your platform or from external software.', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_179', 'Contenu du courrier', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_179', 'Content of the letter', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_179_INTRO', "<p>Pour rendre ce courrier dynamique, insérer des <a href='component/emundus/?view=export_select_columns&format=html&layout=all_programs' target='_blank' rel='noopener noreferrer'>balises</a> dans sa construction afin d’ajouter des informations personnalisées pour chaque candidat. Par exemple, la balise " . '<em>$APPLICANT_NAME</em>' . " sera remplacée par le nom de votre candidat. Bonjour " . '<em>$APPLICANT_NAME</em>' . ' deviendra Bonjour Julien.</p>', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_179_INTRO', "<p>To make this mail dynamic, insert <a href='component/emundus/?view=export_select_columns&format=html&layout=all_programs' target='_blank' rel='noopener noreferrer'>tags</a> in its construction in order to add personalised information for each candidate. For example, the tag " . '<em>$APPLICANT_NAME</em>' . " will be replaced by the name of your candidate. Hello " . '<em>$APPLICANT_NAME</em>' . " will become Hello Julien.</p>", 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('LETTER_TYPE', 'Type de lettre', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('LETTER_TYPE', 'Type of letter', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('LETTER_EXPORT_TO_PDF', 'Générer en pdf', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('LETTER_EXPORT_TO_PDF', 'Generate in pdf', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_185', 'Contenu du courrier', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_185', 'Content of the letter', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_185_INTRO', "<p>Pour rendre ce courrier dynamique, insérer des <a href='component/emundus/?view=export_select_columns&format=html&layout=all_programs' target='_blank' rel='noopener noreferrer'>balises</a> dans sa construction afin d’ajouter des informations personnalisées pour chaque candidat. Par exemple, la balise " . '<em>$APPLICANT_NAME</em>' . " sera remplacée par le nom de votre candidat. Bonjour " . '<em>$APPLICANT_NAME</em>' . ' deviendra Bonjour Julien.</p>', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_185_INTRO', "<p>To make this mail dynamic, insert <a href='component/emundus/?view=export_select_columns&format=html&layout=all_programs' target='_blank' rel='noopener noreferrer'>tags</a> in its construction in order to add personalised information for each candidate. For example, the tag " . '<em>$APPLICANT_NAME</em>' . " will be replaced by the name of your candidate. Hello " . '<em>$APPLICANT_NAME</em>' . " will become Hello Julien.</p>", 'override', null, 'fabrik_elements', 'label', 'en-GB');

				$succeed['campaign_workflow'] = EmundusHelperUpdate::updateCampaignWorkflowTable();
				$succeed['event_handlers']    = EmundusHelperUpdate::convertEventHandlers();

				EmundusHelperUpdate::addYamlVariable('location', '/media/com_emundus/js/fabrik.js', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'javascript', true, true);
				EmundusHelperUpdate::addYamlVariable('inline', '', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'javascript');
				EmundusHelperUpdate::addYamlVariable('in_footer', '0', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'javascript');
				EmundusHelperUpdate::addYamlVariable('extra', '{  }', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'javascript');
				EmundusHelperUpdate::addYamlVariable('priority', '0', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'javascript');
				EmundusHelperUpdate::addYamlVariable('name', 'Fabrik', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'javascript');

				EmundusHelperUpdate::addYamlVariable('location', 'https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css', true, true);
				EmundusHelperUpdate::addYamlVariable('inline', '', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('extra', '{  }', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('priority', '0', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('name', 'Material Icons', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');

				EmundusHelperUpdate::updateFont('family=Inter:300,400,500,600,700,800,900,400&subset=latin,vietnamese,latin-ext');

				$datas = [
					'menutype'     => 'usermenu',
					'title'        => 'Informations de compte',
					'alias'        => 'informations-de-compte',
					'path'         => 'informations-de-compte',
					'link'         => 'index.php?option=com_users&view=profile&layout=edit',
					'type'         => 'component',
					'component_id' => 25,
					'params'       => [
						'menu_show' => 0
					]
				];
				EmundusHelperUpdate::addJoomlaMenu($datas);
			}

			if ((version_compare($cache_version, '1.33.28', '<') || $firstrun))
			{
				EmundusHelperUpdate::installExtension('PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE', 'custom_event_handler', '{"name":"PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE","type":"plugin","creationDate":"18 August 2021","author":"James Dean","copyright":"(C) 2010-2019 EMUNDUS SOFTWARE. All rights reserved.","authorEmail":"james@emundus.fr","authorUrl":"https:\/\/www.emundus.fr","version":"1.22.1","description":"PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE_DESC","group":"","filename":"custom_event_handler"}', 'plugin', 1, 'emundus');
			}

			if ((version_compare($cache_version, '1.33.32', '<') || $firstrun))
			{
				EmundusHelperUpdate::disableEmundusPlugins('emundus_su');
			}

			if (version_compare($cache_version, '1.34.0', '<') || $firstrun)
			{
				EmundusHelperUpdate::addColumn('jos_emundus_setup_campaigns', 'pinned', 'TINYINT', 1);
				EmundusHelperUpdate::addColumn('jos_emundus_setup_campaigns', 'eval_start_date', 'DATETIME');
				EmundusHelperUpdate::addColumn('jos_emundus_setup_programmes', 'color', 'VARCHAR', 10);

				EmundusHelperUpdate::genericUpdateParams('#__modules', 'module', 'mod_falang', array('advanced_dropdown', 'full_name'), array('0', '0'));

				// Add back button to login, register and reset view
				$back_module = EmundusHelperUpdate::getModule(0, 'eMundus - Back button');
				if (!empty($back_module) && !empty($back_module['id']))
				{
					$moduleid = $back_module['id'];
				}
				else
				{
					$datas    = [
						'title'    => 'eMundus - Back button',
						'note'     => 'Back button available on login and register views',
						'content'  => '<p><a class="em-back-button em-pointer" href="/"><span class="material-icons em-mr-4">navigate_before</span>Retour à la page d\'accueil</a></p>',
						'position' => 'header-a',
						'module'   => 'mod_custom',
						'access'   => 9,
						'params'   => [
							'prepare_content' => 0,
							'backgroundimage' => '',
							'layout'          => '_:default',
							'moduleclass_sfx' => '',
							'cache'           => 1,
							'cache_time'      => 900,
							'cachemode'       => 'static',
						]
					];
					$moduleid = EmundusHelperUpdate::addJoomlaModule($datas);
				}

				if (!empty($moduleid))
				{
					$query->clear()
						->select('id')
						->from($db->quoteName('#__menu'))
						->where($db->quoteName('link') . ' IN (' . $db->quote('index.php?option=com_users&view=login') . ',' . $db->quote('index.php?option=com_fabrik&view=form&formid=307') . ',' . $db->quote('index.php?option=com_users&view=reset') . ')');
					$db->setQuery($query);
					$menus = $db->loadColumn();

					foreach ($menus as $menu)
					{
						$query->clear()
							->select('moduleid')
							->from($db->quoteName('#__modules_menu'))
							->where($db->quoteName('menuid') . ' = ' . $db->quote($menu))
							->andWhere($db->quoteName('moduleid') . ' = ' . $db->quote($moduleid['id']));
						$db->setQuery($query);
						$is_existing = $db->loadResult();

						if (!$is_existing)
						{
							$query->clear()
								->insert($db->quoteName('#__modules_menu'))
								->set($db->quoteName('moduleid') . ' = ' . $db->quote($moduleid['id']))
								->set($db->quoteName('menuid') . ' = ' . $db->quote($menu));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}
				//

				// Setup our new layouts
				$query->clear()
					->update($db->quoteName('#__fabrik_forms'))
					->set($db->quoteName('form_template') . ' = ' . $db->quote('_emundus'))
					->where($db->quoteName('form_template') . ' = ' . $db->quote('bootstrap'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__menu'))
					->set($db->quoteName('params') . ' = JSON_REPLACE(params,"$.fabriklayout","_emundus")')
					->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_fabrik&view=form&formid=307'));
				$db->setQuery($query);
				$db->execute();

				EmundusHelperUpdate::insertTranslationsTag('HIKA_BILLING_DESCRIPTION', 'Afin de poursuivre, vous devrez régler les frais de dossier liés à l’inscription');
				EmundusHelperUpdate::insertTranslationsTag('HIKA_BILLING_DESCRIPTION', 'In order to continue, you will need to pay the registration fee.', 'override', null, null, null, 'en-GB');
				EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_PAYMENT_METHOD_SENTENCE', 'Vous souhaitez payer par');
				EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_PAYMENT_METHOD_SENTENCE', 'You wish to pay by', 'override', null, null, null, 'en-GB');
				EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_NEW_BILLING_ADDRESS', 'Adresse de facturation');
				EmundusHelperUpdate::insertTranslationsTag('MAKE_THIS_ADDRESS_THE_DEFAULT_BILLING_ADDRESS', 'Enregistrer cette adresse');
				EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_CONFIRM_MY_ADDRESS', 'Valider mon adresse');
				EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_CONFIRM_MY_ADDRESS', 'Validate my address', 'override', null, null, null, 'en-GB');
				EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_COUPON_TITLE', 'Code de réduction');
				EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_COUPON_TITLE', 'Discount code', 'override', null, null, null, 'en-GB');
				EmundusHelperUpdate::insertTranslationsTag('CHECKOUT_BUTTON_FINISH', 'Procéder au paiement');
				EmundusHelperUpdate::insertTranslationsTag('CHECKOUT_BUTTON_FINISH', 'Process to payment', 'override', null, null, null, 'en-GB');
				EmundusHelperUpdate::insertTranslationsTag('HIKA_NEW', 'Ajouter une adresse');
				EmundusHelperUpdate::insertTranslationsTag('HIKA_NEW', 'Add an address', 'override', null, null, null, 'en-GB');
				//

				$succeed['campaign_workflow'] = EmundusHelperUpdate::addProgramToCampaignWorkflow();
				$query->clear()
					->select('jfe.id')
					->from($db->quoteName('jos_fabrik_elements', 'jfe'))
					->leftJoin($db->quoteName('jos_fabrik_formgroup', 'jffg') . ' ON jfe.group_id = jffg.group_id')
					->leftJoin($db->quoteName('jos_fabrik_lists', 'jfl') . ' ON jffg.form_id = jfl.form_id')
					->where('jfl.db_table_name = ' . $db->quote('jos_emundus_campaign_workflow'))
					->andWhere($db->quoteName('jfe.name') . ' IN (' . $db->quote('campaign') . ', ' . $db->quote('end_date') . ')');
				$db->setQuery($query);
				$campaign_elements = $db->loadColumn();

				if (!empty($campaign_elements))
				{
					foreach ($campaign_elements as $campaign_element)
					{
						EmundusHelperUpdate::genericUpdateParams('#__fabrik_elements', 'id', $campaign_element, ['validations'], [], null, true);
					}
				}

				// Install announcement module
				//TODO : Install a module or a plugin via folder (parse xml file and insert necessary datas)
				EmundusHelperUpdate::installExtension('MOD_EMUNDUS_ANNOUNCEMENTS_SYS_XML', 'mod_emundus_announcements', '{"name":"MOD_EMUNDUS_ANNOUNCEMENTS_SYS_XML","type":"module","creationDate":"September 2022","author":"eMundus","copyright":"Copyright (C) 2022 eMundus. All rights reserved.","authorEmail":"dev@emundus.fr","authorUrl":"www.emundus.fr","version":"1.0.0","description":"MOD_EMUNDUS_ANNOUNCEMENTS_XML_DESCRIPTION","group":"","filename":"mod_emundus_announcements"}', 'module');
				EmundusHelperUpdate::createModule('Announcement', 'top-b', 'mod_emundus_announcements', '{"announcement_content":"Cette plateforme de préproduction est une copie de la production datant du [DATE]. Les mails sont désactivés. Elle est isolée du web.","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 0, 1);

				$datas = [
					'title'    => 'Announcement',
					'note'     => 'Back button available on login and register views',
					'position' => 'top-b',
					'module'   => 'mod_emundus_announcements',
					'params'   => [
						'announcement_content' => 'Cette plateforme de préproduction est une copie de la production datant du [DATE]. Les mails sont désactivés. Elle est isolée du web.'
					]
				];
				EmundusHelperUpdate::addJoomlaModule($datas, 0, true);
				//

				// Install smart search menu
				$query->clear()
					->select('extension_id')
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('element') . ' LIKE ' . $db->quote('com_finder'));
				$db->setQuery($query);
				$ext_id = $db->loadResult();

				$datas = [
					'menutype'     => 'main',
					'title'        => 'COM_FINDER',
					'alias'        => 'com-finder',
					'path'         => 'com-finder',
					'link'         => 'index.php?option=com_finder',
					'type'         => 'component',
					'component_id' => $ext_id,
					'params'       => [],
					'client_id'    => 1,
					'img'          => 'class:finder'
				];
				EmundusHelperUpdate::addJoomlaMenu($datas);
				//

				EmundusHelperUpdate::installExtension('Smart Search - eMundus', 'emundus', '{"name":"Smart Search - eMundus","type":"plugin","creationDate":"November 2022","author":"HUBINET Brice","copyright":"Copyright (C) 2016 eMundus. All rights reserved.","authorEmail":"dev@emundus.fr","authorUrl":"www.emundus.fr","version":"","description":"This plugin indexes applications in eMundus extension.","group":"","filename":"emundus"}', 'plugin', 1, 'finder');
				$datas = [
					'title'    => 'Spotlight eMundus',
					'note'     => 'Advanced search based on Joomla indexing',
					'position' => 'header-c',
					'module'   => 'mod_finder',
					'access'   => 7,
					'params'   => [
						'searchfilter'     => '',
						'show_autosuggest' => 1,
						'show_advanced'    => 0,
						'field_size'       => 25,
						'show_label'       => 1,
						'label_pos'        => 'left',
						'alt_label'        => '',
						'show_button'      => 0,
						'button_pos'       => 'left',
						'opensearch'       => 1,
						'opensearch_title' => '',
						'set_itemid'       => 0,
						'layout'           => '_:tchooz',
					]
				];
				EmundusHelperUpdate::addJoomlaModule($datas, 1, true);

				$succeed['hikashop_events_added'] = EmundusHelperUpdate::addCustomEvents([['label' => 'onHikashopBeforeOrderCreate', 'category' => 'Hikashop'],
					['label' => 'onHikashopAfterOrderCreate', 'category' => 'Hikashop'],
					['label' => 'onHikashopBeforeOrderUpdate', 'category' => 'Hikashop'],
					['label' => 'onHikashopAfterOrderUpdate', 'category' => 'Hikashop'],
					['label' => 'onHikashopAfterOrderConfirm', 'category' => 'Hikashop'],
					['label' => 'onHikashopAfterOrderDelete', 'category' => 'Hikashop'],
					['label' => 'onHikashopCheckoutWorkflowLoad', 'category' => 'Hikashop'],
					['label' => 'onHikashopBeforeProductListingLoad', 'category' => 'Hikashop']
				]);

				EmundusHelperUpdate::disableEmundusPlugins('J2top');

				$succeed['generate_letter_events_added'] = EmundusHelperUpdate::addCustomEvents([
					['label' => 'onAfterGenerateLetters', 'category' => 'Files']
				]);
				$succeed['evaluation_events_added']      = EmundusHelperUpdate::addCustomEvents([
					['label' => 'onRenderEvaluation', 'category' => 'Evaluation'],
					['label' => 'onBeforeSubmitEvaluation', 'category' => 'Evaluation'],
					['label' => 'onAfterSubmitEvaluation', 'category' => 'Evaluation']
				]);

				EmundusHelperUpdate::addYamlVariable('location', 'gantry-assets://custom/scss/quill.scss', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css', true, true);
				EmundusHelperUpdate::addYamlVariable('inline', '', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('extra', '{  }', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('priority', '0', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('name', 'Quill', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');

				EmundusHelperUpdate::disableEmundusPlugins('emundus_profile');

				$query->clear()
					->update('#__modules')
					->set($db->quoteName('ordering') . ' = 1')
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_falang'))
					->andWhere($db->quoteName('position') . ' LIKE ' . $db->quote('header-c'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update('#__modules')
					->set($db->quoteName('ordering') . ' = 2')
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_messenger_notifications'))
					->andWhere($db->quoteName('position') . ' LIKE ' . $db->quote('header-c'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update('#__modules')
					->set($db->quoteName('ordering') . ' = 3')
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_finder'))
					->andWhere($db->quoteName('position') . ' LIKE ' . $db->quote('header-c'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update('#__modules')
					->set($db->quoteName('ordering') . ' = 4')
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_user_dropdown'))
					->andWhere($db->quoteName('position') . ' LIKE ' . $db->quote('header-c'));
				$db->setQuery($query);
				$db->execute();
			}

			if (version_compare($cache_version, '1.34.4', '<') || $firstrun)
			{
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_MISSING_MANDATORY_FILE_UPLOAD', 'Veuillez remplir le champ obligatoire %s du formulaire %s');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_MISSING_MANDATORY_FILE_UPLOAD', 'Please fill the mandatory field %s of form %s', 'override', null, null, null, 'en-GB');
			}

			if (version_compare($cache_version, '1.34.10', '<') || $firstrun)
			{
				EmundusHelperUpdate::insertTranslationsTag('APPLICATION_CREATION_DATE', 'Dossier crée le');
				EmundusHelperUpdate::insertTranslationsTag('APPLICATION_CREATION_DATE', 'File created on', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('CAMPAIGN_ID', 'Campagne');
				EmundusHelperUpdate::insertTranslationsTag('CAMPAIGN_ID', 'Campaign', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SEND_ON', 'Envoyé le');
				EmundusHelperUpdate::insertTranslationsTag('SEND_ON', 'Send on', 'override', null, null, null, 'en-GB');
			}

			if (version_compare($cache_version, '1.34.33', '<') || $firstrun)
			{
				EmundusHelperUpdate::addColumn('jos_emundus_uploads', 'local_filename', 'VARCHAR', 255);
			}

			if (version_compare($cache_version, '1.34.36', '<') || $firstrun)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('id,params')
					->from($db->quoteName('#__fabrik_forms'))
					->where("JSON_EXTRACT(params,'$.curl_code') LIKE '%media\/com_emundus\/lib\/chosen\/chosen.min.css%'");
				$db->setQuery($query);
				$forms_to_update = $db->loadObjectList();

				foreach ($forms_to_update as $form)
				{
					$params = json_decode($form->params);
					if (isset($params->curl_code))
					{
						foreach ($params->curl_code as $key => $code)
						{
							if (strpos($code, 'media/com_emundus/lib/chosen/chosen.min.css') !== false)
							{
								if (is_object($params->curl_code))
								{
									$params->curl_code->{$key} = str_replace('media/com_emundus/lib/chosen/chosen.min.css', 'media/jui/css/chosen.css', $params->curl_code->{$key});
								}
								elseif (is_array($params->curl_code))
								{
									$params->curl_code[$key] = str_replace('media/com_emundus/lib/chosen/chosen.min.css', 'media/jui/css/chosen.css', $params->curl_code[$key]);
								}
							}
							if (strpos($code, 'media/com_emundus/lib/chosen/chosen.jquery.min.js') !== false)
							{
								if (is_object($params->curl_code))
								{
									$params->curl_code->{$key} = str_replace('media/com_emundus/lib/chosen/chosen.jquery.min.js', 'media/jui/js/chosen.jquery.min.js', $params->curl_code->{$key});
								}
								elseif (is_array($params->curl_code))
								{
									$params->curl_code[$key] = str_replace('media/com_emundus/lib/chosen/chosen.jquery.min.js', 'media/jui/js/chosen.jquery.min.js', $params->curl_code[$key]);
								}
							}
						}

						$query->clear()
							->update($db->quoteName('#__fabrik_forms'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $db->quote($form->id));
						$db->setQuery($query);
						$db->execute();
					}

				}
			}

			if (version_compare($cache_version, '1.34.49', '<') || $firstrun)
			{
				EmundusHelperUpdate::addCustomEvents([
					['label' => 'onHikashopAfterCheckoutStep', 'category' => 'Hikashop', 'published' => 1],
					['label' => 'onHikashopAfterCartProductsLoad', 'category' => 'Hikashop', 'published' => 1],
					['label' => 'onBeforeRenderApplications', 'category' => 'Applicant', 'published' => 1]
				]);
			}

			if (version_compare($cache_version, '1.34.56', '<') || $firstrun)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('id')
					->from($db->quoteName('#__modules'))
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_version'))
					->where($db->quoteName('client_id') . ' = 0');
				$db->setQuery($query);
				$moduleid = $db->loadResult();

				if (!empty($moduleid))
				{
					$query->clear()
						->delete($db->quoteName('#__modules_menu'))
						->where($db->quoteName('moduleid') . ' = ' . $moduleid);
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->delete($db->quoteName('#__modules'))
						->where($db->quoteName('id') . ' = ' . $moduleid);
					$db->setQuery($query);
					$db->execute();
				}

				$query->clear()
					->delete($db->quoteName('#__extensions'))
					->where($db->quoteName('element') . ' LIKE ' . $db->quote('mod_emundus_version'))
					->where($db->quoteName('client_id') . ' = 0');
				$db->setQuery($query);
				$db->execute();
			}

			if (version_compare($cache_version, '1.34.64', '<') || $firstrun)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				// Add or update onchange js action for forbidden characters in password
				$password_jsaction = [
					'action' => 'change',
					'params' => '{"js_e_event":"","js_e_trigger":"fabrik_trigger_group_group640","js_e_condition":"","js_e_value":"","js_published":"1"}',
					'code'   => "var wrong_password_title = [&#039;Invalid password&#039;, &#039;Mot de passe invalide&#039;];
var wrong_password_description = [&#039;The characters #$\{\};&lt;&gt; are forbidden&#039;, &#039;Les caractères #$\{\};&lt;&gt; sont interdits&#039;];

var site_url = window.location.toString();
var site_url_lang_regexp = /\w+.\/en/d;

var index = 0;

if(site_url.match(site_url_lang_regexp) === null) { index = 1; }

var regex = /[#$\{\};&lt;&gt; ]/;
var password_value = this.form.formElements.get(&#039;jos_emundus_users___password&#039;).get(&#039;value&#039;);

var password = this.form.formElements.get(&#039;jos_emundus_users___password&#039;);
if (password_value.match(regex) != null) {
    Swal.fire({
    type: &#039;error&#039;,
    title: wrong_password_title[index],
    text: wrong_password_description[index],
    reverseButtons: true,
    customClass: {
        title: &#039;em-swal-title&#039;,
        confirmButton: &#039;em-swal-confirm-button&#039;,
        actions: &#039;em-swal-single-action&#039;,
    }
    });
    password.set(&#039;&#039;);
}"
				];

				$query->clear()
					->select($db->quoteName('id'))
					->from($db->quoteName('#__fabrik_elements'))
					->where($db->quoteName('plugin') . ' LIKE ' . $db->quote('password'))
					->andWhere($db->quoteName('name') . ' LIKE ' . $db->quote('password'));
				$db->setQuery($query);
				$password_inputs = $db->loadColumn();

				if (!empty($password_inputs))
				{
					foreach ($password_inputs as $password)
					{
						$password_jsaction['element_id'] = $password;
						$query->clear()
							->select($db->quoteName('id'))
							->from($db->quoteName('#__fabrik_jsactions'))
							->where($db->quoteName('element_id') . ' = ' . $db->quote($password_jsaction['element_id']))
							->andWhere($db->quoteName('action') . ' LIKE ' . $db->quote($password_jsaction['action']))
							->andWhere($db->quoteName('code') . ' LIKE ' . $db->quote('%Invalid password%'));
						$db->setQuery($query);
						$password_onchange = $db->loadResult();

						if (!empty($password_onchange))
						{
							$password_jsaction['action_id'] = $password_onchange;
							EmundusHelperUpdate::updateJsAction($password_jsaction);
						}
						else
						{
							EmundusHelperUpdate::addJsAction($password_jsaction);
						}
					}
				}
				//

				// Install send_file_archive and enable it, or just enable it if already installed
				EmundusHelperUpdate::installExtension('Emundus - Send ZIP file to user.', 'send_file_archive', '{"name":"Emundus - Send ZIP file to user.","type":"plugin","creationDate":"19 July 2019","author":"eMundus","copyright":"(C) 2010-2019 EMUNDUS SOFTWARE. All rights reserved.","authorEmail":"dev@emundus.fr","authorUrl":"https:\/\/www.emundus.fr","version":"6.9.10","description":"This plugin sends a ZIP of the file when it is changed to a certain status or when it is deleted.","group":"","filename":"send_file_archive"}', 'plugin', 1, 'emundus', '{"delete_email":"delete_file"}');
				EmundusHelperUpdate::enableEmundusPlugins('send_file_archive');
				//
			}

			if (version_compare($cache_version, '1.35.0', '<=') || $firstrun)
			{
				EmundusHelperUpdate::updateYamlVariable('offcanvas', '16rem', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'width');
				EmundusHelperUpdate::updateYamlVariable('breakpoints', '75rem', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'large-desktop-container');
				EmundusHelperUpdate::updateYamlVariable('breakpoints', '60rem', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'desktop-container');
				EmundusHelperUpdate::updateYamlVariable('breakpoints', '48rem', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tablet-container');
				EmundusHelperUpdate::updateYamlVariable('breakpoints', '30rem', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'large-mobile-container');
				EmundusHelperUpdate::updateYamlVariable('breakpoints', '48rem', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'mobile-menu-breakpoint');
				EmundusHelperUpdate::updateYamlVariable('menu', '11rem', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'col-width');
				EmundusHelperUpdate::updateYamlVariable('base', '#f8f8f8', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'background');

				EmundusHelperUpdate::addCustomEvents([
					['label' => 'onWebhookCallbackProcess', 'category' => 'Webhook', 'published' => 1]
				]);

				EmundusHelperUpdate::updateExtensionParam('gotenberg_url', 'https://gotenberg.microservices.tchooz.app', 'https://docs.emundus.app');

				// Install new flow module on old default layouts
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('id,params')
					->from($db->quoteName('#__modules'))
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundusflow'))
					->andWhere($db->quoteName('position') . ' LIKE ' . $db->quote('drawer'))
					->andWhere($db->quoteName('published') . ' = 1');
				$db->setQuery($query);
				$modules = $db->loadObjectList();

				foreach ($modules as $module)
				{
					$params = json_decode($module->params);

					if (isset($params->layout) && $params->layout == '_:default')
					{
						$params->layout = '_:tchooz';
						$query->clear()
							->update($db->quoteName('#__modules'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $module->id);
						$db->setQuery($query);
						$db->execute();

						$query->clear()
							->select('id,value')
							->from($db->quoteName('#__falang_content'))
							->where($db->quoteName('reference_table') . ' LIKE ' . $db->quote('modules'))
							->andWhere($db->quoteName('reference_field') . ' LIKE ' . $db->quote('params'))
							->andWhere($db->quoteName('reference_id') . ' = ' . $module->id);
						$db->setQuery($query);
						$module_translations = $db->loadObjectList();

						foreach ($module_translations as $module_translation)
						{
							$translation_params = json_decode($module_translation->value);

							if (isset($translation_params->layout) && $translation_params->layout == '_:default')
							{
								$translation_params->layout = '_:tchooz';

								$query->clear()
									->update($db->quoteName('#__falang_content'))
									->set($db->quoteName('value') . ' = ' . $db->quote(json_encode($translation_params)))
									->where($db->quoteName('id') . ' = ' . $module_translation->id);
								$db->setQuery($query);
								$db->execute();
							}
						}
					}
				}

				EmundusHelperUpdate::genericUpdateParams('#__fabrik_cron', 'plugin', 'emundusrecall', array('log_email'), array(''));

				EmundusHelperUpdate::updateConfigurationFile('caching', '1');
				EmundusHelperUpdate::updateModulesParams('mod_emundusmenu', 'cache', 0);

				$query->clear()
					->select('params')
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('element') . ' LIKE ' . $db->quote('com_fabrik'));
				$db->setQuery($query);
				$fabrik_extension = $db->loadResult();

				if (!empty($fabrik_extension))
				{
					$fabrik_params                    = json_decode($fabrik_extension, true);
					$fabrik_params['disable_caching'] = "1";

					$query->clear()
						->update($db->quoteName('#__extensions'))
						->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($fabrik_params)))
						->where($db->quoteName('element') . ' LIKE ' . $db->quote('com_fabrik'));
					$db->setQuery($query);
					$db->execute();
				}

				EmundusHelperUpdate::addYamlVariable('location', 'https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined', JPATH_ROOT . '/templates/g5_helium/custom/config/_error/page/assets.yaml', 'css', true, true);
				EmundusHelperUpdate::addYamlVariable('inline', '', JPATH_ROOT . '/templates/g5_helium/custom/config/_error/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('extra', '{  }', JPATH_ROOT . '/templates/g5_helium/custom/config/_error/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('priority', '0', JPATH_ROOT . '/templates/g5_helium/custom/config/_error/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('name', 'Material Icons', JPATH_ROOT . '/templates/g5_helium/custom/config/_error/page/assets.yaml', 'css');

				$old_values = [
					'fr-FR' => 'Créer votre compte',
				];
				$new_values = [
					'fr-FR' => 'Créez votre compte',
				];
				EmundusHelperUpdate::updateOverrideTag('FORM_REGISTRATION', $old_values, $new_values);

				$query->clear()
					->update($db->quoteName('#__fabrik_elements'))
					->set($db->quoteName('published') . ' = 0')
					->where($db->quoteName('name') . ' LIKE ' . $db->quote('confirm_email'))
					->where($db->quoteName('plugin') . ' LIKE ' . $db->quote('field'))
					->where($db->quoteName('group_id') . ' = 640');
				$db->setQuery($query);
				$db->execute();

				EmundusHelperUpdate::updateExtensionParam('export_application_pdf_title_color', '#000000', '#ee1c25');

				$old_values = [
					'fr-FR' => 'Table - Paramétrage des groupes',
					'en-GB' => 'Group settings',
				];
				$new_values = [
					'fr-FR' => 'Groupes',
					'en-GB' => 'Groups',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_GROUPS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Table - Paramétrage des profils',
					'en-GB' => 'Profile settings',
				];
				$new_values = [
					'fr-FR' => 'Profils',
					'en-GB' => 'Profiles',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_PROFILES', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Liste des programmes',
				];
				$new_values = [
					'fr-FR' => 'Programmes',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_PROGRAMS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Liste des années par programme',
					'en-GB' => 'Year settings',
				];
				$new_values = [
					'fr-FR' => 'Années',
					'en-GB' => 'Years',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_YEARS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Paramétrage - Périodes de dépôt de dossiers',
					'en-GB' => 'Registration period settings',
				];
				$new_values = [
					'fr-FR' => 'Campagnes',
					'en-GB' => 'Campaigns',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_PERIODE', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Table - Tags',
					'en-GB' => 'Tags',
				];
				$new_values = [
					'fr-FR' => 'Étiquettes',
					'en-GB' => 'Stickers',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_TAGS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Table - Invitation par email',
					'en-GB' => 'Invitation by email',
				];
				$new_values = [
					'fr-FR' => 'Sollicitation des référents',
					'en-GB' => 'Solicitation of referees',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_INVITATION_BY_EMAIL', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Table - Paramétrages des documents',
					'en-GB' => 'Document settings',
				];
				$new_values = [
					'fr-FR' => 'Types de documents',
					'en-GB' => 'Document types',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_DOCUMENTS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'ADMIN_SETUP_STATUS',
				];
				$new_values = [
					'fr-FR' => 'Statuts de dossiers',
				];
				EmundusHelperUpdate::updateOverrideTag('ADMIN_SETUP_STATUS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Table - Paramétrages des emails',
					'en-GB' => 'Email settings',
				];
				$new_values = [
					'fr-FR' => 'Emails',
					'en-GB' => 'Emails',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_EMAILS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Table- Paiements',
				];
				$new_values = [
					'fr-FR' => 'Paiements',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_PAYMENT', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Table - Paramétrage déclencheurs mails',
				];
				$new_values = [
					'fr-FR' => 'Déclencheurs d\'emails',
				];
				EmundusHelperUpdate::updateOverrideTag('TABLE_SETUP_EMAILS_TRIGGER', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Paramétrage de périodes de dépôt de dossiers',
					'en-GB' => 'Period for the submission of candidacies',
				];
				$new_values = [
					'fr-FR' => 'Paramétrage d\'une campagne',
					'en-GB' => 'Campaign\'s settings',
				];
				EmundusHelperUpdate::updateOverrideTag('SETUP_PERIODS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Paramétrage des tags',
					'en-GB' => 'Tag settings',
				];
				$new_values = [
					'fr-FR' => 'Paramétrage d\'une étiquette',
					'en-GB' => 'Stickers\'s settings',
				];
				EmundusHelperUpdate::updateOverrideTag('SETUP_TAGS', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Form - Invitation par email',
					'en-GB' => 'Invitation by email',
				];
				$new_values = [
					'fr-FR' => 'Sollicitation d\'un référent',
					'en-GB' => 'Solicitation of a referee',
				];
				EmundusHelperUpdate::updateOverrideTag('SETUP_INVITATION_BY_EMAIL', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'SETUP_STATUS',
				];
				$new_values = [
					'fr-FR' => 'Paramétrer un statut de dossier',
				];
				EmundusHelperUpdate::updateOverrideTag('SETUP_STATUS', $old_values, $new_values);

				$query->clear()
					->select('id,params')
					->from($db->quoteName('#__fabrik_lists'));
				$db->setQuery($query);
				$fabrik_lists = $db->loadObjectList();

				if (!empty($fabrik_lists))
				{
					foreach ($fabrik_lists as $list)
					{
						$params                    = json_decode($list->params, true);
						$params['advanced-filter'] = "0";

						if ($params['show-table-filters'] != "0")
						{
							$params['show-table-filters'] = "1";
						}

						$query->clear()
							->update($db->quoteName('#__fabrik_lists'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $list->id);
						$db->setQuery($query);
						$db->execute();
					}
				}

				EmundusHelperUpdate::installExtension('Emundus - Authentication.', 'emundus', '{"name":"Authentication - eMundus","type":"plugin","creationDate":"March 2023","author":"J\u00e9r\u00e9my LEGENDRE","copyright":"(C) 2023 eMundus All rights reserved.","authorEmail":"dev@emundus.fr","authorUrl":"emundus.fr","version":"1.0.0","description":"PLG_AUTHENTICATION_EMUNDUS_XML_DESCRIPTION","group":"","filename":"emundus"}', 'plugin', 1, 'authentication');
				EmundusHelperUpdate::enableEmundusPlugins('emundus', 'authentication');
			}

			if (version_compare($cache_version, '1.35.5', '<=') || $firstrun)
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('params')
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('element') . ' LIKE ' . $db->quote('emunduswaitingroom'));
				$db->setQuery($query);
				$waiting_room_params = $db->loadResult();

				if (!empty($waiting_room_params))
				{
					$strings_allowed_to_add = [
						'paybox_',
						'stripeconnect_',
						'notif_payment=monetico&ctrl=checkout&task=notify&option=com_hikashop&tmpl=component',
						'option=com_hikashop&ctrl=checkout&task=notify&notif_payment=payzen&tmpl=component',
					];
					$strings                = [];

					$params = json_decode($waiting_room_params, true);
					if (empty($params['strings_allowed']))
					{
						$params['strings_allowed'] = [];
					}

					// We get values from the database.
					foreach ($params['strings_allowed'] as $string)
					{
						$strings[] = $string['string_allowed_text'];
					}

					foreach ($strings_allowed_to_add as $string_allowed_to_add)
					{
						if (!in_array($string_allowed_to_add, $strings))
						{
							$strings[] = $string_allowed_to_add;
						}
					}

					$params['strings_allowed'] = [];
					foreach ($strings as $key => $string)
					{
						$params['strings_allowed']['strings_allowed' . $key] = [
							'string_allowed_text' => $string
						];
					}

					$query->clear()
						->update($db->quoteName('#__extensions'))
						->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
						->where($db->quoteName('element') . ' LIKE ' . $db->quote('emunduswaitingroom'));
					$db->setQuery($query);
					$db->execute();

				}
			}

			if (version_compare($cache_version, '1.35.9', '<=') || $firstrun)
			{
				EmundusHelperUpdate::addColumn('jos_messages', 'email_cc', 'TEXT');
				EmundusHelperUpdate::addColumn('jos_emundus_logs', 'timestamp', 'TIMESTAMP', null, 0);

				$dashboard_files_by_status_params = array(
					'eval' => 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

try {
    $query->select(\'*\')
        ->from($db->quoteName(\'jos_emundus_setup_status\'))
        ->order(\'ordering\');
    $db->setQuery($query);
    $status = $db->loadObjectList();

    $datas = [];

    foreach ($status as $statu) {
        $file = new stdClass;
        $file->label = $statu->value;

        $colors = array(
            \'lightpurple\' => \'#D444F1\',
            \'purple\' => \'#7959F8\',
            \'darkpurple\' => \'#663399\',
            \'lightblue\' => \'#0BA4EB\',
            \'blue\' => \'#2E90FA\',
            \'darkblue\' => \'#2970FE\',
            \'lightgreen\' => \'#15B79E\',
            \'green\' => \'#238C69\',
            \'darkgreen\' => \'#20835F\',
            \'lightyellow\' => \'#5D5B00\',
            \'yellow\' => \'#EAA907\',
            \'darkyellow\' => \'#F79009\',
            \'lightorange\' => \'#C87E00\',
            \'orange\' => \'#EF681F\',
            \'darkorange\' => \'#FF4305\',
            \'lightred\' => \'#EC644B\',
            \'red\' => \'#DB333E\',
            \'darkred\' => \'#DB333E\',
            \'lightpink\' => \'#B04748\',
            \'pink\' => \'#EE46BC\',
            \'darkpink\' => \'#F53D68\',
            \'default\' => \'#5E6580\'
        );

        $file->color = $colors[$statu->class];

        $query->clear()
            ->select(\'COUNT(ecc.id) as files\')
            ->from($db->quoteName(\'#__emundus_campaign_candidature\',\'ecc\'))
            ->leftJoin($db->quoteName(\'#__emundus_setup_campaigns\',\'esc\').\' ON \'.$db->quoteName(\'esc.id\').\' = \'.$db->quoteName(\'ecc.campaign_id\'))
            ->where($db->quoteName(\'ecc.status\') . \' = \' . $db->quote($statu->step))
            ->andWhere($db->quoteName(\'ecc.published\') . \' = \' . $db->quote(1));

        $db->setQuery($query);
        $file->value = $db->loadResult();
        $datas[] = $file;
    }

	$dataSource = new stdClass;
	$dataSource->chart = new stdClass;
	$dataSource->chart = array(
		\'caption\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_CAPTION"),
		\'xaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_STATUS"),
		\'yaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_NUMBER"),
		\'animation\' => 1,
		\'numberScaleValue\' => "1",
		\'numDivLines\' => 1,
		\'numbersuffix\'=> "",
		\'theme\'=> "fusion"
	);
	$dataSource->data = $datas;
	return $dataSource;
} catch (Exception $e) {
	return array(\'dataset\' => \'\');
}'
				);

				$dashboard_users_by_month_params = array(
					'eval' => 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);
$offset = JFactory::getApplication()->get(\'offset\', \'UTC\');
$now = new DateTime(gmdate("Y-m-d H:i:s"), new DateTimeZone(\'UTC\'));
$now = $now->setTimezone(new DateTimeZone($offset));

try {
    $users = array();
    $days = array();
    $users_by_day = array();

    $query->select(\'COUNT(id) as users\')
        ->from($db->quoteName(\'#__users\'));
    $db->setQuery($query);
    $totalUsers = $db->loadResult();

    $dateTime = $now;

    for ($d = 1;$d < 31;$d++){
        $user = new stdClass;
        $day = new stdClass;
        $query->clear()
            ->select(\'COUNT(id) as users\')
            ->from($db->quoteName(\'#__users\'))
            ->where($db->quoteName(\'id\') . \' != \' . $db->quote(62))
            ->andWhere(\'YEAR(registerDate) = \' . $db->quote($dateTime->format(\'Y\')))
            ->andWhere(\'MONTH(registerDate) = \' . $db->quote($dateTime->format(\'m\')))
            ->andWhere(\'DAY(registerDate) = \' . $db->quote($dateTime->format(\'j\')));

        $db->setQuery($query);
        $user = (int) $db->loadResult();
        $day = $dateTime->format(\'d\') . \'/\' . $dateTime->format(\'m\');
        $users[] = $user;
        $days[] = $day;
        $users_by_day[] = array(\'label\' => $day, \'value\' => $user);

        $dateTime->modify(\'-1 day\');
    }

    $dataSource = new stdClass;
    $dataSource->chart = new stdClass;
    $dataSource->chart = array(
        \'caption\'=> JText::_("COM_EMUNDUS_DASHBOARD_USERS_BY_MONTH_CAPTION"),
        \'subcaption\'=> JText::_("COM_EMUNDUS_DASHBOARD_USERS_TOTAL") . $totalUsers . JText::_("COM_EMUNDUS_DASHBOARD_USERS"),
        \'xaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_USERS_DAYS"),
        \'yaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_USERS_NUMBER"),
        \'animation\' => 1,
        \'yAxisMinValue\'=> 0,
        \'setAdaptiveYMin\'=> 0,
        \'adjustDiv\'=> 0,
        \'yAxisValuesStep\'=> 10,
        \'numbersuffix\'=> "",
        \'theme\'=> "fusion"
    );
    $dataSource->categories = [];
    $dataSource->categories[] = array(
        \'category\' => $days
    );
    $dataSource->data = array_reverse($users_by_day);
    return $dataSource;
} catch (Exception $e) {
	return array(\'users\' => \'\', \'days\' => \'\', \'total\' => 0);
}'
				);

				$dashboard_files_associated_by_status_params = array(
					'eval' => 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user_id = JFactory::getUser()->id;

try {
    $query->select(\'*\')
        ->from($db->quoteName(\'jos_emundus_setup_status\'))
        ->order(\'ordering\');
    $db->setQuery($query);
    $status = $db->loadObjectList();

    $datas = [];

    foreach ($status as $statu) {
        $file = new stdClass;
        $file->label = $statu->value;

        $colors = array(
            \'lightpurple\' => \'#D444F1\',
            \'purple\' => \'#7959F8\',
            \'darkpurple\' => \'#663399\',
            \'lightblue\' => \'#0BA4EB\',
            \'blue\' => \'#2E90FA\',
            \'darkblue\' => \'#2970FE\',
            \'lightgreen\' => \'#15B79E\',
            \'green\' => \'#238C69\',
            \'darkgreen\' => \'#20835F\',
            \'lightyellow\' => \'#5D5B00\',
            \'yellow\' => \'#EAA907\',
            \'darkyellow\' => \'#F79009\',
            \'lightorange\' => \'#C87E00\',
            \'orange\' => \'#EF681F\',
            \'darkorange\' => \'#FF4305\',
            \'lightred\' => \'#EC644B\',
            \'red\' => \'#DB333E\',
            \'darkred\' => \'#DB333E\',
            \'lightpink\' => \'#B04748\',
            \'pink\' => \'#EE46BC\',
            \'darkpink\' => \'#F53D68\',
            \'default\' => \'#5E6580\'
        );

        $file->color = $colors[$statu->class];

        $query->clear()
            ->select(\'distinct eua.fnum as files\')
            ->from($db->quoteName(\'#__emundus_users_assoc\',\'eua\'))
            ->leftJoin($db->quoteName(\'#__emundus_campaign_candidature\',\'cc\').\' ON \'.$db->quoteName(\'cc.fnum\').\' = \'.$db->quoteName(\'eua.fnum\'))
            ->where($db->quoteName(\'cc.status\').\' = \'.$db->quote($statu->step))
			->andWhere($db->quoteName(\'cc.published\').\' = \'.$db->quote(1))
            ->andWhere($db->quoteName(\'eua.user_id\').\' = \'.$db->quote($user_id));

        $db->setQuery($query);
        $files_user_assoc = $db->loadColumn();

        $query->clear()
            ->select(\'distinct ega.fnum as files\')
            ->from($db->quoteName(\'#__emundus_group_assoc\',\'ega\'))
            ->leftJoin($db->quoteName(\'#__emundus_campaign_candidature\',\'cc\').\' ON \'.$db->quoteName(\'cc.fnum\').\' = \'.$db->quoteName(\'ega.fnum\'))
            ->leftJoin($db->quoteName(\'#__emundus_groups\',\'eg\').\' ON \'.$db->quoteName(\'eg.group_id\').\' = \'.$db->quoteName(\'ega.group_id\'))
            ->where($db->quoteName(\'cc.status\').\' = \'.$db->quote($statu->step))
			->andWhere($db->quoteName(\'cc.published\').\' = \'.$db->quote(1))
            ->andWhere($db->quoteName(\'eg.user_id\').\' = \'.$db->quote($user_id));

        $db->setQuery($query);
        $files_group_assoc = $db->loadColumn();

        $query->clear()
            ->select(\'distinct cc.fnum as files\')
            ->from($db->quoteName(\'#__emundus_groups\',\'eg\'))
            ->leftJoin($db->quoteName(\'#__emundus_setup_groups_repeat_course\',\'esgrc\').\' ON \'.$db->quoteName(\'esgrc.parent_id\').\' = \'.$db->quoteName(\'eg.group_id\'))
            ->leftJoin($db->quoteName(\'#__emundus_setup_campaigns\', \'esc\').\' ON \'.$db->quoteName(\'esc.training\').\' = \'.$db->quoteName(\'esgrc.course\'))
            ->leftJoin($db->quoteName(\'#__emundus_campaign_candidature\',\'cc\').\' ON \'.$db->quoteName(\'cc.campaign_id\').\' = \'.$db->quoteName(\'esc.id\'))
            ->where($db->quoteName(\'cc.status\').\' = \'.$db->quote($statu->step))
			->andWhere($db->quoteName(\'cc.published\').\' = \'.$db->quote(1))
            ->andWhere($db->quoteName(\'eg.user_id\').\' = \'.$db->quote($user_id));

        $db->setQuery($query);
        $files_group_programs = $db->loadColumn();

        $file->value = sizeof(array_unique(array_merge($files_user_assoc,$files_group_assoc,$files_group_programs)));
        $datas[] = $file;
    }

	$dataSource = new stdClass;
	$dataSource->chart = new stdClass;
	$dataSource->chart = array(
		\'caption\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_ASSOCIATED_BY_STATUS_CAPTION"),
		\'xaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_STATUS"),
		\'yaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_NUMBER"),
		\'animation\' => 1,
		\'numberScaleValue\' => "1",
		\'numDivLines\' => 1,
		\'numbersuffix\'=> "",
		\'theme\'=> "fusion"
	);
	$dataSource->data = $datas;
	return $dataSource;
} catch (Exception $e) {
	return array(\'dataset\' => \'\');
}'
				);

				$dashboard_files_by_tag_params = array(
					'eval' => 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

try {
	$query->select(\'*\')
		->from($db->quoteName(\'jos_emundus_setup_action_tag\'));
	$db->setQuery($query);
	$tags = $db->loadObjectList();

	$datas = array();

	foreach ($tags as $tag) {
		$file = new stdClass;
		$file->label = $tag->label;

		$query->clear()
			->select(\'COUNT(distinct eta.fnum) as files\')
			->from($db->quoteName(\'jos_emundus_tag_assoc\',\'eta\'))
			->where($db->quoteName(\'eta.id_tag\').\' = \'.$db->quote($tag->id));

		$db->setQuery($query);
		$file->value = $db->loadResult();
		$datas[] = $file;
	}

	$dataSource = new stdClass;
	$dataSource->chart = new stdClass;
	$dataSource->chart = array(
		\'caption\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_TAG_CAPTION"),
		\'xaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_TAGS"),
		\'yaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_TAG_NUMBER"),
		\'animation\' => 1,
		\'numbersuffix\'=> "",
		\'theme\'=> "fusion"
	);
	$dataSource->data = $datas;
	return $dataSource;
} catch (Exception $e) {
	return array(\'dataset\' => \'\');
}'
				);

				EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS', $dashboard_files_by_status_params);
				EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_USERS_BY_MONTH', $dashboard_users_by_month_params);
				EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_FILES_ASSOCIATED_BY_STATUS', $dashboard_files_associated_by_status_params);
				EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_FILES_BY_TAG', $dashboard_files_by_tag_params);

				EmundusHelperUpdate::addColumnIndex('jos_messages', 'page');

				EmundusHelperUpdate::addCustomEvents([['label' => 'onAfterMoveApplication', 'category' => 'Campaign']]);
			}

			if (version_compare($cache_version, '1.36.0', '<=') || $firstrun)
			{
				EmundusHelperUpdate::addCustomEvents([
                    ['label' => 'onBeforeEmundusRedirectToHikashopCart', 'category' => 'Hikashop'],
                    ['label' => 'onBeforeApplicantEnterApplication', 'category' => 'Files'],
                    ['label' => 'onAccessDenied', 'category' => 'Access'],
					['label' => 'onBeforeEmundusRedirectToHikashopCart', 'category' => 'Hikashop'],
					['label' => 'onBeforeApplicantEnterApplication', 'category' => 'Files']
				]);

				// Campaign candidature tabs
				$columns      = [
					[
						'name'   => 'name',
						'type'   => 'VARCHAR',
						'length' => 255,
						'null'   => 0,
					],
					[
						'name' => 'applicant_id',
						'type' => 'INT',
						'null' => 0,
					],
					[
						'name'    => 'ordering',
						'type'    => 'INT',
						'default' => 1,
						'null'    => 1,
					]
				];
				$foreign_keys = [
					[
						'name'           => 'jos_emundus_users_fk_applicant_id',
						'from_column'    => 'applicant_id',
						'ref_table'      => 'jos_emundus_users',
						'ref_column'     => 'user_id',
						'update_cascade' => true,
						'delete_cascade' => true,
					]
				];
				EmundusHelperUpdate::createTable('jos_emundus_campaign_candidature_tabs', $columns, $foreign_keys, 'Storage tab for filing');

				$columns      = [
					[
						'name' => 'date_time',
						'type' => 'datetime',
						'null' => 1,
					],
					[
						'name'   => 'fnum_from',
						'type'   => 'VARCHAR',
						'length' => 255,
						'null'   => 0,
					],
					[
						'name'   => 'fnum_to',
						'type'   => 'VARCHAR',
						'length' => 255,
						'null'   => 0,
					],
					[
						'name'    => 'published',
						'type'    => 'TINYINT',
						'default' => 1,
						'null'    => 0,
					]
				];
				$foreign_keys = [];
				EmundusHelperUpdate::createTable('jos_emundus_campaign_candidature_links', $columns, $foreign_keys, 'Links between two fnums');

				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature', 'tab', 'INT', 10);
				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature', 'name', 'VARCHAR', 255);
				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature', 'updated', 'DATETIME');
				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature', 'updated_by', 'INT', 10);

				//////////////////////////////////////////////////////////////////////////////////////
				// Add campaign workflows documents;
				//////////////////////////////////////////////////////////////////////////////////////
				$query->clear()
					->select('jffg.group_id, jffg.form_id, jfl.id AS list_id')
					->from('#__fabrik_formgroup AS jffg')
					->leftJoin('#__fabrik_lists AS jfl ON jfl.form_id = jffg.form_id')
					->where('jfl.db_table_name = ' . $db->quote('jos_emundus_campaign_workflow'));

				$db->setQuery($query);
				$sql_result = $db->loadAssoc();
				$group_id   = $sql_result['group_id'];
				$form_id    = $sql_result['form_id'];
				$list_id    = $sql_result['list_id'];

				EmundusHelperUpdate::addColumn('jos_emundus_campaign_workflow', 'display_preliminary_documents', 'TINYINT', 1, 1, 0);
				$query->clear()
					->select('id')
					->from($db->quoteName('#__fabrik_elements'))
					->where('name = ' . $db->quote('display_preliminary_documents'))
					->andWhere('plugin = ' . $db->quote('yesno'));

				$db->setQuery($query);
				$element_id = $db->loadResult();

				if (empty($element_id))
				{
					if (!empty($group_id))
					{
						$values = ['display_preliminary_documents', $group_id, 'yesno', 'Afficher les documents à télécharger ?', '0', '0000-00-00 00:00:00', '2023-03-29 07:47:05', '62', 'sysadmin', '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '10', '0', "", '1', '1', '0', '0', '0', '1', '0', '0', '{"yesno_default":"0","yesno_icon_yes":"","yesno_icon_no":"","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}'];
						$query->clear()
							->insert($db->quoteName('#__fabrik_elements'))
							->columns(['name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', '`default`', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params'])
							->values(implode(',', $db->quote($values)));

						$db->setQuery($query);
						$inserted = $db->execute();

						if ($inserted)
						{
							$display_preliminary_documents_id = $db->insertid();
							EmundusHelperUpdate::addJsAction([
								'element_id' => $display_preliminary_documents_id,
								'action'     => 'load',
								'code'       => 'const value=this.get(&#039;value&#039;);const fab=this.form.elements;let {jos_emundus_campaign_workflow___specific_documents,jos_emundus_campaign_workflow_repeat_documents___id_0}=fab;if(value!=&#039;0&#039;){showFabrikElt(jos_emundus_campaign_workflow___specific_documents);}else{document.querySelector(&#039;#jos_emundus_campaign_workflow___specific_documents_input_0&#039;).click();hideFabrikElt(jos_emundus_campaign_workflow___specific_documents);  hideFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0, true);}'
							]);
							EmundusHelperUpdate::addJsAction([
								'element_id' => $display_preliminary_documents_id,
								'action'     => 'change',
								'code'       => 'const value=this.get(&#039;value&#039;);const fab=this.form.elements;let{jos_emundus_campaign_workflow___specific_documents,jos_emundus_campaign_workflow_repeat_documents___id_0}=fab;if(value!=&#039;0&#039;){showFabrikElt(jos_emundus_campaign_workflow___specific_documents)}else{document.querySelector(&#039;#jos_emundus_campaign_workflow___specific_documents_input_0&#039;).click();hideFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0);hideFabrikElt(jos_emundus_campaign_workflow___specific_documents,true)}'
							]);
						}
					}
				}

				EmundusHelperUpdate::addColumn('jos_emundus_campaign_workflow', 'specific_documents', 'TINYINT', 1, 1, 0);
				$query->clear()
					->select('id')
					->from($db->quoteName('#__fabrik_elements'))
					->where('name = ' . $db->quote('specific_documents'))
					->andWhere('plugin = ' . $db->quote('yesno'));

				$db->setQuery($query);
				$element_id = $db->loadResult();

				if (empty($element_id))
				{
					if (!empty($group_id))
					{
						$values = ['specific_documents', $group_id, 'yesno', 'Afficher des documents  spécifique ?', '0', '0000-00-00 00:00:00', '2023-03-29 07:47:05', '62', 'sysadmin', '0000-00-00 00:00:00', '0', '0', '0', '0', '0', '0', '10', '0', "", '1', '1', '0', '0', '0', '1', '0', '0', '{"yesno_default":"0","yesno_icon_yes":"","yesno_icon_no":"","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}'];
						$query->clear()
							->insert($db->quoteName('#__fabrik_elements'))
							->columns(['name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', '`default`', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params'])
							->values(implode(',', $db->quote($values)));

						$db->setQuery($query);
						$inserted = $db->execute();

						if ($inserted)
						{
							$specific_documents_id = $db->insertid();
							EmundusHelperUpdate::addJsAction([
								'element_id' => $specific_documents_id,
								'action'     => 'load',
								'code'       => 'const value=this.get(&#039;value&#039;);const fab=this.form.elements;let{jos_emundus_campaign_workflow_repeat_documents___id_0}=fab;if(value!= &#039;0&#039;){showFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0)}else{hideFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0, true)}'
							]);
							EmundusHelperUpdate::addJsAction([
								'element_id' => $specific_documents_id,
								'action'     => 'change',
								'code'       => 'const value=this.get(&#039;value&#039;);const fab=this.form.elements;let{jos_emundus_campaign_workflow_repeat_documents___id_0}=fab;if(value!= &#039;0&#039;){showFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0)}else{hideFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0, true)}'
							]);
						}
					}
				}

				$result = EmundusHelperUpdate::createTable('jos_emundus_campaign_workflow_repeat_documents', [
					['name' => 'parent_id', 'type' => 'int'],
					['name' => 'href', 'type' => 'text'],
					['name' => 'title', 'type' => 'VARCHAR', 'length' => 255]
				]);
				if ($result['status'])
				{
					$sql = "create index fb_parent_fk_parent_id_INDEX on jos_emundus_campaign_workflow_repeat_documents (parent_id)";
					$db->setQuery($sql);
					$db->execute();

					$values = ['Documents à télécharger', '', 'Documents à télécharger', 1, '2023-04-19 08:36:17', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":1,"repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_sortable":"0","repeat_order_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}'];
					$query->clear()
						->insert($db->quoteName('#__fabrik_groups'))
						->columns(['name', 'css', 'label', 'published', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'checked_out', 'checked_out_time', 'is_join', 'private', 'params'])
						->values(implode(',', $db->quote($values)));

					$db->setQuery($query);
					$inserted = $db->execute();

					if ($inserted)
					{
						$new_group_id = $db->insertid();

						$columns        = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
						$element_values = [
							['id', $new_group_id, 'internalid', 'id', 0, '0000-00-00 00:00:00', '2023-04-19 08:41:52', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 3, 0, '', 1, 0, 1, 0, '', '', 1, 1, 1, 1, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}'],
							['parent_id', $new_group_id, 'field', 'parent_id', 0, '0000-00-00 00:00:00', '2023-04-19 08:41:52', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, 0, '', 1, 0, 2, 0, '', '', 1, 1, 0, 0, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}'],
							['href', $new_group_id, 'fileupload', 'Document', 0, '0000-00-00 00:00:00', '2023-04-19 08:39:57', 62, 'sysadmin', '2023-04-19 09:11:13', 62, 0, 0, '', 0, 0, 3, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"ul_max_file_size":"10240","ul_device_capture":"0","ul_file_types":"pdf,jpg,png,jpeg,docx","ul_directory":"\/images\/emundus\/phases\/{jos_emundus_campaign_workflow___id}","ul_email_file":"0","random_filename":"0","length_random_filename":"","ul_file_increment":"1","upload_allow_folderselect":"0","upload_delete_image":"1","upload_use_wip":"0","allow_unsafe":"0","fu_clean_filename":"1","fu_rename_file_code":"","default_image":"","make_link":"1","fu_show_image_in_table":"1","fu_show_image":"0","fu_show_image_in_email":"1","image_library":"gd2","fu_main_max_width":"","fu_main_max_height":"","image_quality":"90","fu_title_element":"","fu_map_element":"","restrict_lightbox":"1","make_thumbnail":"0","fu_make_pdf_thumb":"0","thumb_dir":"images\/stories\/thumbs","thumb_prefix":"","thumb_suffix":"","thumb_max_width":"200","thumb_max_height":"100","fileupload_crop":"0","fileupload_crop_dir":"images\/stories\/crop","fileupload_crop_width":"200","fileupload_crop_height":"100","win_width":"400","win_height":"400","fileupload_storage_type":"filesystemstorage","fileupload_aws_accesskey":"","fileupload_aws_secretkey":"","fileupload_aws_location":"","fileupload_ssl":"0","fileupload_aws_encrypt":"0","fileupload_aws_bucketname":"","fileupload_s3_serverpath":"1","fileupload_amazon_acl":"2","fileupload_skip_check":"0","fileupload_amazon_auth_url":"60","ajax_upload":"0","ajax_show_widget":"1","ajax_runtime":"html5,html4","ajax_max":"4","ajax_dropbox_width":"400","ajax_dropbox_height":"200","ajax_chunk_size":"0","fu_use_download_script":"0","fu_open_in_browser":"0","fu_force_download_script":"0","fu_download_acl":"","fu_download_noaccess_image":"","fu_download_noaccess_url":"","fu_download_access_image":"","fu_download_hit_counter":"","fu_download_log":"0","fu_download_append":"0","ul_export_encode_csv":"relative","ul_export_encode_json":"relative","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}'],
							['title', $new_group_id, 'field', 'Nom du document', 0, '0000-00-00 00:00:00', '2023-04-19 09:11:42', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, 0, '', 0, 0, 12, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"11","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["0"],"must_validate":["0"],"show_icon":["1"]}}']
						];

						foreach ($element_values as $values)
						{
							$query->clear()
								->insert($db->quoteName('#__fabrik_elements'))
								->columns($db->quoteName($columns))
								->values(implode(',', $db->quote($values)));

							$db->setQuery($query);
							$db->execute();
						}

						// join new_group_id to form id of campaign workflows
						$query->clear()
							->insert($db->quoteName('#__fabrik_formgroup'))
							->columns($db->quoteName(['form_id', 'group_id', 'ordering']))
							->values(implode(',', $db->quote([$form_id, $new_group_id, 2])));
						$db->setQuery($query);
						$db->execute();

						// add fabrik joins on list id of campaign workflows
						if (!empty($list_id))
						{
							$query->clear()
								->insert($db->quoteName('#__fabrik_joins'))
								->columns($db->quoteName(['list_id', 'element_id', 'join_from_table', 'table_join', 'table_key', 'table_join_key', 'join_type', 'group_id', 'params']))
								->values(implode(',', $db->quote([$list_id, 0, 'jos_emundus_campaign_workflow', 'jos_emundus_campaign_workflow_repeat_documents', 'id', 'parent_id', 'left', $new_group_id, '{"type":"group","pk":"`jos_emundus_campaign_workflow_repeat_documents`.`id`"}'])));

							$db->setQuery($query);
							$db->execute();

							// update list params
							$query->clear()
								->update('#__fabrik_lists')
								->set('params = ' . $db->quote('{"show-table-filters":"1","advanced-filter":"0","advanced-filter-default-statement":"=","search-mode":"0","search-mode-advanced":"0","search-mode-advanced-default":"all","search_elements":"","list_search_elements":"null","search-all-label":"All","require-filter":"0","require-filter-msg":"","filter-dropdown-method":"0","toggle_cols":"0","list_filter_cols":"1","empty_data_msg":"","outro":"","list_ajax":"0","show-table-add":"1","show-table-nav":"1","show_displaynum":"1","showall-records":"0","show-total":"0","sef-slug":"","show-table-picker":"1","admin_template":"","show-title":"1","pdf":"","pdf_template":"","pdf_orientation":"portrait","pdf_size":"a4","pdf_include_bootstrap":"1","bootstrap_stripped_class":"1","bootstrap_bordered_class":"0","bootstrap_condensed_class":"0","bootstrap_hover_class":"1","responsive_elements":"","responsive_class":"","list_responsive_elements":"null","tabs_field":"","tabs_max":"10","tabs_all":"1","list_ajax_links":"0","actionMethod":"default","detailurl":"","detaillabel":"","list_detail_link_icon":"search","list_detail_link_target":"_self","editurl":"","editlabel":"","list_edit_link_icon":"edit","checkboxLocation":"end","hidecheckbox":"1","addurl":"","addlabel":"","list_add_icon":"plus","list_delete_icon":"delete","popup_width":"","popup_height":"","popup_offset_x":"","popup_offset_y":"","note":"","alter_existing_db_cols":"default","process-jplugins":"1","cloak_emails":"0","enable_single_sorting":"default","collation":"latin1_swedish_ci","force_collate":"","list_disable_caching":"0","distinct":"1","group_by_raw":"1","group_by_access":"1","group_by_order":"","group_by_template":"","group_by_template_extra":"","group_by_order_dir":"ASC","group_by_start_collapsed":"0","group_by_collapse_others":"0","group_by_show_count":"1","menu_module_prefilters_override":"1","prefilter_query":"","join_id":["1369"],"join_type":["left"],"join_from_table":["jos_emundus_campaign_workflow"],"table_join":["jos_emundus_campaign_workflow_repeat_documents"],"table_key":["id"],"table_join_key":["parent_id"],"join_repeat":[["1"]],"join-display":"merge","delete-joined-rows":"0","show_related_add":"0","show_related_info":"0","rss":"0","feed_title":"","feed_date":"","feed_image_src":"","rsslimit":"150","rsslimitmax":"2500","csv_import_frontend":"10","csv_export_frontend":"10","csvfullname":"0","csv_export_step":"100","newline_csv_export":"nl2br","csv_clean_html":"leave","csv_multi_join_split":",","csv_custom_qs":"","csv_frontend_selection":"0","incfilters":"0","csv_format":"0","csv_which_elements":"selected","show_in_csv":"","csv_elements":"null","csv_include_data":"1","csv_include_raw_data":"1","csv_include_calculations":"0","csv_filename":"","csv_encoding":"","csv_double_quote":"1","csv_local_delimiter":"","csv_end_of_line":"n","open_archive_active":"0","open_archive_set_spec":"","open_archive_timestamp":"","open_archive_license":"http:\/\/creativecommons.org\/licenses\/by-nd\/2.0\/rdf","dublin_core_element":"","dublin_core_type":"dc:description.abstract","raw":"0","open_archive_elements":"null","search_use":"0","search_title":"","search_description":"","search_date":"","search_link_type":"details","dashboard":"0","dashboard_icon":"","allow_view_details":"7","allow_edit_details":"7","allow_edit_details2":"","allow_add":"7","allow_delete":"7","allow_delete2":"","allow_drop":"10","menu_access_only":"0","isview":"0"}'))
								->where('id = ' . $db->quote($list_id));

							$db->setQuery($query);
							$db->execute();
						}
					}
				}

				//////////////////////////////////////////////////////////////////////////////////////
				// END add campaign workflows documents;
				//////////////////////////////////////////////////////////////////////////////////////

				EmundusHelperUpdate::addColumn('jos_emundus_setup_attachment_profiles', 'has_sample', 'TINYINT', 1);
				EmundusHelperUpdate::addColumn('jos_emundus_setup_attachment_profiles', 'sample_filepath', 'VARCHAR', 255);

				// check if table jos_emundus_setup_config exists
				$str_query = 'SHOW TABLES LIKE ' . $db->quote('jos_emundus_setup_config');
				$db->setQuery($str_query);
				$table_exists = $db->loadResult();

				if (!$table_exists)
				{
					// create it if it doesn't exist
					$str_query = 'create table jos_emundus_setup_config
					(
					    namekey   varchar(255) not null primary key,
					    value     text         null,
					    `default` text         null,
					    constraint jos_emundus_setup_config_namekey_uindex unique (namekey)
					);';

					$db->setQuery($str_query);
					$db->execute();
				}

				$query->clear()
					->select($db->quoteName('namekey'))
					->from($db->quoteName('#__emundus_setup_config'))
					->where($db->quoteName('namekey') . ' = ' . $db->quote('onboarding_lists'));
				$db->setQuery($query);
				$onboarding_lists = $db->loadResult();

				if(empty($onboarding_lists))
				{
					// insert default values
					$query->clear()
						->insert($db->quoteName('#__emundus_setup_config'))
						->columns($db->quoteName(['namekey', 'value', 'default']))
						->values($db->quote('onboarding_lists') . ', ' . $db->quote('{"forms":{"title":"COM_EMUNDUS_ONBOARD_FORMS","tabs":[{"title":"COM_EMUNDUS_FORM_MY_FORMS","key":"form","controller":"form","getter":"getallform","actions":[{"action":"duplicateform","label":"COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE","controller":"form","name":"duplicate"},{"action":"index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"},{"action":"createform","controller":"form","label":"COM_EMUNDUS_ONBOARD_ADD_FORM","name":"add"}],"filters":[]},{"title":"COM_EMUNDUS_FORM_MY_EVAL_FORMS","key":"form_evaluations","controller":"form","getter":"getallgrilleEval","actions":[{"action":"createformeval","label":"COM_EMUNDUS_ONBOARD_ADD_EVAL_FORM","controller":"form","name":"add"},{"action":"/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%&mode=eval","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"}],"filters":[]},{"title":"COM_EMUNDUS_FORM_PAGE_MODELS","key":"form_models","controller":"formbuilder","getter":"getallmodels","actions":[{"action":"deleteformmodelfromids","label":"COM_EMUNDUS_ACTIONS_DELETE","controller":"formbuilder","parameters":"&model_ids=%id%","name":"delete"},{"action":"/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%form_id%&mode=models","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"}],"filters":[]}]},"campaigns":{"title":"COM_EMUNDUS_ONBOARD_CAMPAIGNS","tabs":[{"title":"COM_EMUNDUS_ONBOARD_CAMPAIGNS","key":"campaign","controller":"campaign","getter":"getallcampaign","actions":[{"action":"index.php?option=com_emundus&view=campaigns&layout=add","label":"COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN","controller":"campaign","name":"add","type":"redirect"},{"action":"duplicatecampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE","controller":"campaign","name":"duplicate"},{"action":"index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"campaign","type":"redirect","name":"edit"},{"action":"deletecampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_DELETE","controller":"campaign","name":"delete","confirm":"COM_EMUNDUS_ONBOARD_CAMPDELETE","showon":{"key":"nb_files","operator":"<","value":"1"}},{"action":"unpublishcampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH","controller":"campaign","name":"unpublish","showon":{"key":"published","operator":"=","value":"1"}},{"action":"publishcampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_PUBLISH","controller":"campaign","name":"publish","showon":{"key":"published","operator":"=","value":"0"}},{"action":"pincampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_PIN_CAMPAIGN","controller":"campaign","name":"pin","icon":"push_pin","iconOutlined":true,"showon":{"key":"pinned","operator":"!=","value":"1"}},{"action":"unpincampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_UNPIN_CAMPAIGN","controller":"campaign","name":"unpin","icon":"push_pin","iconOutlined":false,"showon":{"key":"pinned","operator":"=","value":"1"}}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_FILTER_ALL","getter":"","controller":"campaigns","key":"filter","values":[{"label":"COM_EMUNDUS_ONBOARD_FILTER_ALL","value":"all"},{"label":"COM_EMUNDUS_CAMPAIGN_YET_TO_COME","value":"yettocome"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_OPEN","value":"ongoing"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_CLOSE","value":"Terminated"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_PUBLISH","value":"Publish"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH","value":"Unpublish"}],"default":"Publish"},{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAMS","getter":"getallprogramforfilter","controller":"programme","key":"program","values":null}]},{"title":"COM_EMUNDUS_ONBOARD_PROGRAMS","key":"programs","controller":"programme","getter":"getallprogram","actions":[{"action":"index.php?option=com_fabrik&view=form&formid=108","controller":"programme","label":"COM_EMUNDUS_ONBOARD_ADD_PROGRAM","name":"add","type":"redirect"},{"action":"index.php?option=com_fabrik&view=form&formid=108&rowid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"programme","type":"redirect","name":"edit"}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAM_CATEGORIES","getter":"getprogramcategories","controller":"programme","key":"recherche","values":null}]}]},"emails":{"title":"COM_EMUNDUS_ONBOARD_EMAILS","tabs":[{"controller":"email","getter":"getallemail","title":"COM_EMUNDUS_ONBOARD_EMAILS","key":"emails","actions":[{"action":"index.php?option=com_emundus&view=emails&layout=add","controller":"email","label":"COM_EMUNDUS_ONBOARD_ADD_EMAIL","name":"add","type":"redirect"},{"action":"index.php?option=com_emundus&view=emails&layout=add&eid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"email","type":"redirect","name":"edit"},{"action":"deleteemail","label":"COM_EMUNDUS_ACTIONS_DELETE","controller":"email","name":"delete","showon":{"key":"type","operator":"!=","value":"1"}},{"action":"preview","label":"COM_EMUNDUS_ONBOARD_VISUALIZE","controller":"email","name":"preview","icon":"preview","iconOutlined":true,"title":"subject","content":"message"}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAM_CATEGORIES","getter":"getemailcategories","controller":"email","key":"recherche","values":null}]}]}}') . ', ' . $db->quote(''));
					$db->setQuery($query);
					$db->execute();
				}

				/* Init new profile method */
				// First install the module
				EmundusHelperUpdate::installExtension('MOD_EMUNDUS_PROFILE','mod_emundus_profile','{"name":"MOD_EMUNDUS_PROFILE","type":"module","creationDate":"April 2023","author":"Brice Hubinet","copyright":"Copyright (C) 2023 eMundus. All rights reserved.","authorEmail":"brice.hubinet@emundus.fr","authorUrl":"www.emundus.fr","version":"1.36.0","description":"MOD_EMUNDUS_PROFILE_DESC","group":"","filename":"mod_emundus_profile"}','module',1,'','{"show_profile_picture":"1","update_profile_picture":"1","show_name":"1","show_account_edit_button":"1","intro":""}');

				$db = JFactory::getDbo();
				$query = $db->getQuery(true);

				// We get the profile Fabrik form id
				$query->select('form_id')
					->from($db->quoteName('#__emundus_setup_formlist'))
					->where($db->quoteName('type') . ' LIKE ' . $db->quote('profile'));
				$db->setQuery($query);
				$form_id = $db->loadResult();

				EmundusHelperUpdate::installExtension('plg_fabrik_element_emundusphonenumber', 'emundus_phonenumber', '{"name":"plg_fabrik_element_emundusphonenumber","type":"plugin","creationDate":"April 2023","author":"eMundus - Thibaud Grignon","copyright":"Copyright (C) 2005-2021 Media A-Team, Inc. - All rights reserved.","authorEmail":"rob@pollen-8.co.uk","authorUrl":"www.fabrikar.com","version":"3.10","description":"PLG_ELEMENT_FIELD_DESCRIPTION","group":"","filename":"emundus_phonenumber"}', 'plugin', 1, 'fabrik_element');
				EmundusHelperUpdate::addColumn('jos_emundus_users', 'token', 'VARCHAR', 50);
				EmundusHelperUpdate::addColumn('jos_emundus_users', 'anonym_user', 'TINYINT', 1);

				$country_table = EmundusHelperUpdate::createTable('data_country', [
					['name' => 'label_fr', 'type' => 'varchar', 'length' => 255],
					['name' => 'label_en', 'type' => 'varchar', 'length' => 255],
					['name' => 'iso2', 'type' => 'varchar', 'length' => 2],
					['name' => 'iso3', 'type' => 'varchar', 'length' => 4],
					['name' => 'country_nb', 'type' => 'varchar', 'length' => 4],
					['name' => 'continent', 'type' => 'varchar', 'length' => 2],
					['name' => 'continent_en', 'type' => 'varchar', 'length' => 255],
					['name' => 'member', 'type' => 'tinyint', 'length' => 1]
				]);

				if($country_table['status']){
					EmundusHelperUpdate::executeSQlFile('insert_data_country');
				}

				EmundusHelperUpdate::addColumn('data_country', 'flag', 'VARCHAR', 30);
				EmundusHelperUpdate::addColumn('data_country', 'flag_img', 'VARCHAR', 30);
				EmundusHelperUpdate::executeSQlFile('update_flags');
				EmundusHelperUpdate::executeSQlFile('update_acl_ordering');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_EMAILS_MESSAGE_SENT_TO', 'Email envoyé à');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_EMAILS_MESSAGE_SENT_TO', 'Email sent to', 'override', null, null, null, 'en-GB');

				$query->clear()
					->select('id,path')
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('path') . ' IN ("toutes-les-campagnes","mes-candidatures")')
					->andWhere($db->quoteName('menutype') . ' LIKE ' . $db->quote('applicantmenu'));
				$db->setQuery($query);
				$applicant_menus = $db->loadObjectList();

				foreach ($applicant_menus as $menu)
				{
					$text = 'All campaigns';
					if ($menu->path == 'mes-candidatures')
					{
						$text = 'My applications';
					}
					$query->clear()
						->select('id,value')
						->from($db->quoteName('#__falang_content'))
						->where($db->quoteName('reference_table') . ' LIKE ' . $db->quote('menu'))
						->andWhere($db->quoteName('reference_field') . ' LIKE ' . $db->quote('title'))
						->andWhere($db->quoteName('reference_id') . ' = ' . $menu->id);
					$db->setQuery($query);
					$translation = $db->loadObject();

					if (empty($translation))
					{
						$query->clear()
							->insert($db->quoteName('#__falang_content'))
							->columns($db->quoteName('reference_table') . ',' . $db->quoteName('reference_field') . ',' . $db->quoteName('reference_id') . ',' . $db->quoteName('value') . ',' . $db->quoteName('language_id') . ',' . $db->quoteName('published'))
							->values($db->quote('menu') . ',' . $db->quote('title') . ',' . $menu->id . ',' . $db->quote($text) . ',' . $db->quote(1) . ',' . $db->quote(1));
					}
					else
					{
						$query->clear()
							->update($db->quoteName('#__falang_content'))
							->set($db->quoteName('value') . ' = ' . $db->quote($text))
							->set($db->quoteName('published') . ' = ' . $db->quote(1))
							->where($db->quoteName('id') . ' = ' . $translation->id);
					}
					$db->setQuery($query);
					$db->execute();
				}

				$query->clear()
					->select($db->quoteName('params'))
					->from($db->quoteName('#__fabrik_forms'))
					->where($db->quoteName('id') . ' = ' . $db->quote(108));
				$db->setQuery($query);
				$program_form_params = $db->loadResult();

				if (!empty($program_form_params))
				{
					$program_form_params = json_decode($program_form_params);
					if (!in_array('onAfterProgramCreate', $program_form_params->plugin_description))
					{
						$program_form_params->plugin_state[]       = "1";
						$program_form_params->only_process_curl[]  = "onAfterProcess";
						$program_form_params->form_php_file[]      = "-1";
						$program_form_params->curl_code[]          = 'JPluginHelper::importPlugin(\'emundus\', \'custom_event_handler\');
\Joomla\CMS\Factory::getApplication()->triggerEvent(\'callEventHandler\', [\'onAfterProgramCreate\', [\'formModel\' => $this->getModel(), \'data\' => $this->getProcessData()]]);';
						$program_form_params->plugins[]            = "php";
						$program_form_params->plugin_locations[]   = "both";
						$program_form_params->plugin_events[]      = "new";
						$program_form_params->plugin_description[] = "onAfterProgramCreate";

						$query->clear()
							->update($db->quoteName('#__fabrik_forms'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($program_form_params)))
							->where($db->quoteName('id') . ' = ' . $db->quote(108));
						$db->setQuery($query);
						$db->execute();
					}
				}

				// update setup_program logo element code
				$query->clear()
					->select($db->quoteName('jfe.id'))
					->from($db->quoteName('#__fabrik_elements', 'jfe'))
					->innerJoin($db->quoteName('#__fabrik_formgroup', 'jffg') . ' ON ' . $db->quoteName('jfe.group_id') . ' = ' . $db->quoteName('jffg.group_id'))
					->innerJoin($db->quoteName('#__fabrik_lists', 'jfl') . ' ON ' . $db->quoteName('jfl.form_id') . ' = ' . $db->quoteName('jffg.form_id'))
					->where('jfe.plugin = ' . $db->quote('fileupload'))
					->andWhere('jfe.name = ' . $db->quote('logo'))
					->andWhere('jfl.db_table_name = ' . $db->quote('jos_emundus_setup_programmes'))
					->andWhere('jfl.label = ' . $db->quote('TABLE_SETUP_PROGRAMS'));

				$db->setQuery($query);
				$program_logo_element = $db->loadResult();

				if (!empty($program_logo_element))
				{
					EmundusHelperUpdate::genericUpdateParams('#__fabrik_elements', 'id', $program_logo_element, ['fu_rename_file_code'], ['error_clear_last();
					$new_name = $formModel->formData[\'jos_emundus_setup_programmes___code_raw\'];
					$new_name = preg_replace(\'/[^A-Za-z0-9_\\-]/\', \'\', $new_name);
					$new_name .= \'.\' . pathinfo($filename, PATHINFO_EXTENSION);
					return $new_name;'], null, true);
				}

				$content_index_offline = "name: _offline
timestamp: 1680776072
version: 7
preset:
  image: 'gantry-admin://images/layouts/body-only.png'
  name: _body_only
  timestamp: 1530009501
positions: {  }
sections:
  mainbar: Mainbar
particles:
  messages:
    system-messages-6659: 'System Messages'
  content:
    system-content-5845: 'Page Content'
inherit: {  }";

				$content_layout_offline = "version: 2
preset:
  image: 'gantry-admin://images/layouts/body-only.png'
  name: _body_only
  timestamp: 1530009501
layout:
  /mainbar/:
    -
      - system-messages-6659
    -
      - system-content-5845
structure:
  mainbar:
    type: section
    subtype: main
    attributes:
      boxed: ''
";

				EmundusHelperUpdate::updateYamlVariable('', '', JPATH_ROOT . '/templates/g5_helium/custom/config/_offline/index.yaml', '', $content_index_offline);
				EmundusHelperUpdate::updateYamlVariable('', '', JPATH_ROOT . '/templates/g5_helium/custom/config/_offline/layout.yaml', '', $content_layout_offline);
			}

			if (version_compare($cache_version, '1.36.2', '<=') || $firstrun){
				$tags_to_publish = [
					'APPLICANT_ID','USER_ID','APPLICANT_NAME','CURRENT_DATE','ID','NAME','EMAIL','USERNAME','SITE_URL','USER_NAME','USER_EMAIL','CAMPAIGN_LABEL','CAMPAIGN_YEAR','CAMPAIGN_START','CAMPAIGN_END','FNUM','PHOTO'
				];
				foreach ($tags_to_publish as $key => $tag)
				{
					$tags_to_publish[$key] = $db->quote($tag);
				}
				$query->clear()
					->update($db->quoteName('#__emundus_setup_tags'))
					->set($db->quoteName('published') . ' = ' . $db->quote(1))
					->where($db->quoteName('tag') . ' IN (' . implode(',',$tags_to_publish) . ')');
				$db->setQuery($query);
				$db->execute();
			}

            if (version_compare($cache_version, '1.36.3', '<=') || $firstrun){
                $query->clear()
                    ->select('DISTINCT '.$db->quoteName('form_id'))
                    ->from($db->quoteName('#__fabrik_lists'))
                    ->where($db->quoteName('db_table_name').' = '.$db->quote('jos_emundus_uploads'));
                $db->setQuery($query);
                $forms = $db->loadColumn();

                if (!empty($forms)) {
                    $query->clear()
                        ->select('DISTINCT '.$db->quoteName('group_id'))
                        ->from($db->quoteName('#__fabrik_formgroup'))
                        ->where($db->quoteName('form_id').' IN ('.implode(',',$forms).')');
                    $db->setQuery($query);
                    $groups = $db->loadColumn();

                    if (!empty($groups)) {
                        $params = array(
                            'bootstrap_class' => 'input-medium',
                            'date_showtime' => 1,
                            'date_which_time_picker' => 'wicked',
                            'date_show_seconds' => 1,
                            'date_24hour' => 1,
                            'bootstrap_time_class' => 'input-medium',
                            'placeholder' => '',
                            'date_store_as_local' => 0,
                            'date_table_format' => 'Y-m-d H:i:s',
                            'date_form_format' => 'Y-m-d H:i:s',
                            'date_defaulttotoday' => 1,
                            'date_alwaystoday' => 0,
                            'date_firstday' => 0,
                            'date_allow_typing_in_field' => 0,
                            'date_csv_offset_tz' => 0,
                            'date_advanced' => 0,
                            'date_allow_func' => '',
                            'date_allow_php_func' => '',
                            'date_observe' => ''
                        );
                        foreach($groups as $group_id) {
                            $datas = array(
                                'name' => 'timedate',
                                'group_id' => $group_id,
                                'plugin' => 'date',
                                'label' => 'Date d\'envoi du document',
                                'hidden' => 1
                            );
                            EmundusHelperUpdate::addFabrikElement($datas, $params);
                        }
                    }
                }

				EmundusHelperUpdate::updateExtensionParam('fbConf_alter_existing_db_cols','addonly', null, 'com_fabrik');

				if(file_exists(JPATH_ROOT . '/templates/g5_helium/custom/config/24/page/assets.yaml')){
					unlink(JPATH_ROOT . '/templates/g5_helium/custom/config/24/page/assets.yaml');
				}
            }

			if (version_compare($cache_version, '1.36.4', '<=') || $firstrun){
				EmundusHelperUpdate::addColumn('jos_emundus_uploads','size','INT',11);

                EmundusHelperUpdate::updateExtensionParam('gotenberg_url', 'https://gotenberg.microservices.tchooz.app', 'http://localhost:3000');
			}

            if (version_compare($cache_version, '1.36.6', '<=') || $firstrun){
                // Add missing columns from previous updates
                EmundusHelperUpdate::addColumn('jos_emundus_personal_detail','profile','INT',11);
                EmundusHelperUpdate::addColumn('jos_emundus_logs','ip_from','VARCHAR',26);
                EmundusHelperUpdate::addColumn('jos_messages','page','INT',11);
                EmundusHelperUpdate::alterColumn('jos_messages','page','INT',11);
                EmundusHelperUpdate::addColumnIndex('jos_messages','page');

                // Unpublish FAQ widget
                $faq_params = array(
                    'published' => 0,
                );
                EmundusHelperUpdate::updateWidget('FAQ', $faq_params);

                // Get FAQ widget id
                $query->clear()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__emundus_widgets'))
                    ->where($db->quoteName('name').' = '.$db->quote('FAQ'));
                $db->setQuery($query);
                $faq_widget_id = $db->loadResult();

                // Delete all usage of FAQ widget
                $query->clear()
                    ->delete($db->quoteName('#__emundus_setup_dashbord_repeat_widgets'))
                    ->where($db->quoteName('widget').' = '.$db->quote($faq_widget_id));
                $db->setQuery($query);
                $db->execute();

                // Update jos_emundus_uploads lists to change param alter_existing_db_cols to default (addonly)
                $query->clear()
                    ->select($db->quoteName(array('id','params')))
                    ->from($db->quoteName('#__fabrik_lists'))
                    ->where($db->quoteName('db_table_name').' = '.$db->quote('jos_emundus_uploads'));
                $db->setQuery($query);
                $lists = $db->loadObjectList();

                foreach ($lists as $list) {
                    $params = json_decode($list->params);
                    $params->alter_existing_db_cols = 0;
                    $params = json_encode($params);
                    $query->clear()
                        ->update($db->quoteName('#__fabrik_lists'))
                        ->set($db->quoteName('params').' = '.$db->quote($params))
                        ->where($db->quoteName('id').' = '.$db->quote($list->id));
                    $db->setQuery($query);
                    $db->execute();
                }

	            $query->clear()
		            ->select('id')
		            ->from($db->quoteName('#__menu'))
		            ->where($db->quoteName('title').' LIKE '.$db->quote('Evaluation'))
		            ->where($db->quoteName('menutype').' LIKE '.$db->quote('application'));
	            $db->setQuery($query);
	            $evaluation_application_menu = $db->loadResult();

				if(!empty($evaluation_application_menu)){
					$query->clear()
						->select('id')
						->from($db->quoteName('#__falang_content'))
						->where($db->quoteName('reference_table').' LIKE '.$db->quote('menu'))
						->where($db->quoteName('reference_field').' LIKE '.$db->quote('title'))
						->where($db->quoteName('language_id').' = 2')
						->where($db->quoteName('reference_id').' = '.$db->quote($evaluation_application_menu));
					$db->setQuery($query);
					$evaluation_application_menu_falang = $db->loadResult();

					if(!empty($evaluation_application_menu_falang)){
						$query->clear()
							->update($db->quoteName('#__falang_content'))
							->set($db->quoteName('value').' = '.$db->quote('Évaluation'))
							->where($db->quoteName('id').' = '.$db->quote($evaluation_application_menu_falang));
						$db->setQuery($query);
						$db->execute();
					} else {
						$query->clear()
							->insert($db->quoteName('#__falang_content'))
							->columns($db->quoteName(array('reference_id','reference_table','reference_field','language_id','value','original_text','published')))
							->values($db->quote($evaluation_application_menu).','.$db->quote('menu').','.$db->quote('title').',2,'.$db->quote('Évaluation').','.$db->quote('').',1');
						$db->setQuery($query);
						$db->execute();
					}
				}

				// Create redirection menu in Joomla administration
				$query->clear()
					->select('id')
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('link').' LIKE '.$db->quote('index.php?option=com_redirect'));
				$db->setQuery($query);
				$redirect_menu = $db->loadResult();

				if(empty($redirect_menu))
				{
					$query->clear()
						->insert($db->quoteName('#__menu'))
						->columns(array('menutype', 'title', 'alias', 'note', 'path', 'link', 'type', 'published', 'parent_id', 'level', 'component_id', 'checked_out', 'checked_out_time', 'browserNav', 'access', 'img', 'template_style_id', 'params', 'lft', 'rgt', 'home', 'language', 'client_id'))
						->values($db->quote('main') . ',' . $db->quote('Redirection') . ',' . $db->quote('com-redirect') . ',' . $db->quote('') . ',' . $db->quote('com-redirect') . ',' . $db->quote('index.php?option=com_redirect') . ',' . $db->quote('component') . ',1,1,1,24,0,' . $db->quote(date('Y-m-d H:i:s')) . ',0,1,' . $db->quote('class:redirect') . ',0,' . $db->quote('{}') . ',363,368,0,' . $db->quote('') . ',1');
					$db->setQuery($query);
					$db->execute();
				} else {
					$query->clear()
						->update($db->quoteName('#__menu'))
						->set($db->quoteName('menutype').' = '.$db->quote('main'))
						->where($db->quoteName('id').' = '.$db->quote($redirect_menu));
					$db->setQuery($query);
					$db->execute();
				}
				//
            }

            if (version_compare($cache_version, '1.36.7', '<=') || $firstrun){
                EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_COMMENTAIRE', 'Comments', 'override', null, null, null, 'en-GB');
                EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_COMMENTAIRE', 'Commentaires', 'override', null, null, null, 'fr-FR');
	            $data=array(
					'title'=>'Accessibilité',
		            'alias'=>'accessibilite',
		            'introtext'=>'<p><em>Déclaration RGAA version 4.1 (La version en vigueur du RGAA est la <strong>4.1</strong> et a été publiée le 18 février 2021)</em></p>
					<p> </p>
					<p> </p>
					<h1><strong>Accessibilité</strong></h1>
					<p> </p>
					<p>L\'article 47 de la Loi N°2005-102 du 11 février 2005 établit que tous les services publics de communication en ligne de l\'État, des autorités locales et des institutions publiques doivent être accessibles à tous les utilisateurs.</p>
					<p>De même, toutes les organisations publiques et privées entreprenant une activité d\'intérêt général sont tenues de se conformer au Référentiel Général d\'Accessibilité pour les Administrations (RGAA).</p>
					<p> </p>
					<p>Cette législation met en évidence l\'importance de rendre les services en ligne accessibles, indépendamment des capacités des utilisateurs. Les sites web, applications, etc. doivent respecter les principes d\'accessibilité, se conformer aux normes en vigueur et être compatibles avec les technologies d\'assistance utilisées par les personnes en situation de handicap.</p>
					<p> </p>
					<p><span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[NOM DE L’ENTITE]</span>, reconnait pleinement l\'impact de cette loi et s’engage à remplir ses obligations en matière d\'accessibilité numérique. <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[NOM DE L’ENTITE] </span>est déterminé(e) à se conformer au RGAA et à offrir des services en ligne accessibles à tous les utilisateurs.</p>
					<p> </p>
					<p>Dans cette déclaration d\'accessibilité, <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[NOM DE L’ENTITE]</span> souhaite présenter ses efforts continus pour rendre ses services en ligne accessibles à tous. <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[NOM DE L’ENTITE] </span>met en place des mesures spécifiques pour se conformer à l\'article 47 de la Loi N°2005-102 du 11 février 2005, ainsi qu\'au RGAA, afin de garantir une expérience équitable pour chacun de ses utilisateurs, quelles que soient leurs capacités.</p>
					<p> </p>
					<p> </p>
					<h2><strong>Schéma pluriannuel et plan annuel de </strong><span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[NOM DE L’ENTITE]</span></h2>
					<p> </p>
					<p><span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[NOM DE L’ENTITE]</span> s’engage dans un processus d’amélioration de l’accessibilité de l’ensemble de ses sites.</p>
					<p> </p>
					<p>Le schéma pluriannuel décrit les points importants sur lesquels <span class="fabric-text-color-mark" data-text-custom-color="#ff5630"><span style="color: #ff0000;">[NOM DE L’ENTITE]</span> </span>s’appuiera pour améliorer l’accessibilité numérique de l’ensemble de ses sites web et applications.</p>
					<p> </p>
					<ul>
					<li><u>Consulter le schéma pluriannuel d’accessibilité 2023 – 2025</u> <span style="color: #ff0000;"><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">(Les utilisateurs doivent avoir accès au schéma pluriannuel - [insérer un lien cliquable vers le schéma pluriannuel]) – Exemple de schéma pluriannuel : </span><a style="color: #ff0000;" href="https://www.numerique.gouv.fr/uploads/DINUM_SchemaPluriannuel_2020.pdf" target="_blank" rel="noopener noreferrer"><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">https://www.numerique.gouv.fr/uploads/DINUM_SchemaPluriannuel_2020.pdf</span></a></span></li>
					</ul>
					<p> </p>
					<p>Il s’accompagne d’un plan d’action annuel qui détaille les opérations programmées et mises en œuvre, ainsi que l’état de suivi de ces actions :</p>
					<p> </p>
					<ul>
					<li> <u>Consulter plan annuel d’accessibilité 2023</u>  <span style="color: #ff0000;"><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">(Les utilisateurs doivent avoir accès au plan annuel - [insérer un lien cliquable vers le plan annuel]) – Exemple de plan annuel : </span><a style="color: #ff0000;" href="https://www.numerique.gouv.fr/uploads/DINUM-plan-annuel-2021.pdf" target="_blank" rel="noopener noreferrer"><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">https://www.numerique.gouv.fr/uploads/DINUM-plan-annuel-2021.pdf</span></a></span></li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>État de conformité</strong></h2>
					<p> </p>
					<p>Le site<span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630"> [URL SITE] </span>est <span style="color: #ff0000;"><strong><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">non/partiellement/totalement</span></strong></span> conforme avec le référentiel général d’amélioration de l’accessibilité (RGAA), version 4.1.</p>
					<p> </p>
					<ul>
					<li><span style="color: #ff0000;"><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">[SI NON]</span> : <em><u>Exemple</u></em> <em>: Étant donné qu\'il n\'existe aucun résultat d\'audit permettant de mesurer l\'atteinte des critères, un audit de conformité sera planifié, et des travaux d\'amélioration seront entrepris à la suite d\'un premier diagnostic.</em></span></li>
					<li><span style="color: #ff0000;"><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">[SI PARTIELLEMENT]</span> : <em><u>Exemple</u></em> <em>: en raison des non-conformités et des dérogations énumérées ci-dessous.</em></span></li>
					<li><span style="color: #ff0000;"><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">[SI TOTALEMENT]</span><strong><span class="fabric-text-color-mark" data-text-custom-color="#ff5630"> </span></strong>: <em><u>Exemple</u></em> <em>: Voir section « Résultats des tests ».</em></span></li>
					</ul>
					<p> </p>
					<p><strong><span style="color: #ff0000;"><span class="fabric-text-color-mark" data-text-custom-color="#ff5630">Quel que soit votre état de conformité par suite d’un audit ou un diagnostic, vous êtes tenu de remplir la partie suivante en fonction de votre situation.</span></span></strong></p>
					<p> </p>
					<p> </p>
					<h2><strong>Résultats des tests</strong></h2>
					<p> </p>
					<p>L’audit de conformité finalisé le <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[DATE DE L’AUDIT] </span>par la société <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[NOM DE L’ENTITÉ QUI A RÉALISÉ L’AUDIT]</span> révèle que le site est conforme à<span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630"> [INDIQUER LE POURCENTAGE DE CONFORMITÉ]</span> au RGAA version 4.1.</p>
					<p> </p>
					<p> </p>
					<h2><strong>Contenus non accessibles</strong></h2>
					<p> </p>
					<p>Malgré le travail de mise en accessibilité effectué, certains contenus, listés ci-dessous, ne peuvent être rendus à 100% accessibles pour les raisons suivantes :</p>
					<p> </p>
					<p><em><u>Exemples</u> :</em></p>
					<p> </p>
					<ul>
					<li><em>Le bouton d’envoi du formulaire de déclaration contient un intitulé « Retour » au lieu de « Envoi ». Cette erreur sera corrigée avant le <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[DATE DE LA CORRECTION]</span></em></li>
					<li><em>La connexion au compte personnel contient une vérification que vous n’êtes pas un robot avec un captcha visuel. Il est possible d’effectuer sa démarche par téléphone ou au guichet pour les personnes empêchées d’accéder à leur compte…</em></li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>Dérogations pour charge disproportionnée</strong></h2>
					<p> </p>
					<p><em><u>Exemples</u> :</em></p>
					<p> </p>
					<ul>
					<li><em>Certains termes anglais ne peuvent pas être signalés comme tels (par exemple « meetup ») à certains endroits comme les titres, car le code html est alors visible dans le title de la page. La correction de ce point nécessiterait des travaux correctifs importants pour un impact utilisateur ici assez faible.</em></li>
					<li><em>Le CMS génère parfois automatiquement quelques balises paragraphes vides superflues. Après des essais infructueux, il a été conclu que corriger ce point ne pourrait être réalisé aisément pour un impact utilisateur très faible, le contenu restant accessible et compréhensible…</em></li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>Contenus non soumis à l’obligation d’accessibilité</strong></h2>
					<p> </p>
					<p><em><u>Exemples</u> :</em></p>
					<p> </p>
					<ul>
					<li><em>Le fil d’actualité Twitter sur la page d’Accueil</em></li>
					<li><em>Player vidéo (Youtube, Dailymotion)</em></li>
					<li><em>Reproduction du manuscrit du Moyen-Âge</em></li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>Établissement de cette déclaration d’accessibilité</strong></h2>
					<p> </p>
					<p>Cette déclaration a été établie le <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[DATE]</span>.</p>
					<p> </p>
					<p> </p>
					<h2><strong>Technologies utilisées pour la réalisation du site</strong></h2>
					<p> </p>
					<ul>
					<li>HTML5</li>
					<li>CSS</li>
					<li>JavaScript</li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>Environnement de test</strong></h2>
					<p> </p>
					<p>Les vérifications de restitution de contenus ont été réalisées sur la base de la combinaison fournie par la base de référence du RGAA, avec les versions suivantes :</p>
					<p> </p>
					<p><em><u>Exemples</u> :</em></p>
					<p> </p>
					<ul>
					<li><em>Firefox et NVDA</em></li>
					<li><em>Safari et VoiceOver</em></li>
					<li><em>Chrome…</em></li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>Outils pour évaluer l’accessibilité</strong></h2>
					<p> </p>
					<p><em><u>Exemples</u> :</em></p>
					<p> </p>
					<ul>
					<li><em>Color Contrast Analyzer</em></li>
					<li><em>WCAG Contrat checker (Firefox)</em></li>
					<li><em>Web Developer Toolbar pour Firefox</em></li>
					<li><em>Web Developer Toolbar pour Chrome…</em></li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>Pages du site ayant fait l’objet de la vérification de conformité</strong></h2>
					<p> </p>
					<p><em><u>Exemples</u> :</em></p>
					<p> </p>
					<ul>
					<li><em>page d’accueil <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[url]</span></em></li>
					<li><em>page contact <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[url]</span></em></li>
					<li><em>page mentions légales <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[url]</span></em></li>
					<li><em>page accessibilité <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[url]</span></em></li>
					<li><em>page plan du site<span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630"> [url]</span></em></li>
					<li><em>page d’aide <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[url]</span></em></li>
					<li><em>...</em></li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>Retour d’information et contact</strong></h2>
					<p> </p>
					<p>Si vous n’arrivez pas à accéder à un contenu ou à un service, vous pouvez contacter le responsable du site pour être orienté vers une alternative accessible ou obtenir le contenu sous une autre forme.</p>
					<p> </p>
					<ul>
					<li>Envoyer un message <span style="color: #ff0000;">[<span class="fabric-text-color-mark" data-text-custom-color="#ff5630">URL D’UN FORMULAIRE EN LIGNE OU ADRESSE E-MAIL DU SERVICE CONCERNÉ]</span></span></li>
					<li>Contacter <span class="fabric-text-color-mark" style="color: #ff0000;" data-text-custom-color="#ff5630">[NOM DE L’ENTITE OU DE LA PERSONNE RESPONSABLE DU SERVICE EN LIGNE ET COORDONNÉES]</span></li>
					</ul>
					<p> </p>
					<p> </p>
					<h2><strong>Voies de recours</strong></h2>
					<p> </p>
					<p>Si vous constatez un défaut d’accessibilité vous empêchant d’accéder à un contenu ou une fonctionnalité du site, que vous nous le signalez et que vous ne parvenez pas à obtenir une réponse de notre part, vous êtes en droit de faire parvenir vos doléances ou une demande de saisine au Défenseur des droits.</p>
					<p> </p>
					<p>Plusieurs moyens sont à votre disposition :</p>
					<p> </p>
					<ul>
					<li><a href="https://formulaire.defenseurdesdroits.fr/code/afficher.php?ETAPE=accueil_2016" target="_blank" rel="noopener noreferrer">Écrire un message au Défenseur des droits</a></li>
					<li><a href="https://www.defenseurdesdroits.fr/saisir/delegues" target="_blank" rel="noopener noreferrer">Contacter le délégué du Défenseur des droits dans votre région</a></li>
					<li>Envoyer un courrier par la poste (gratuit, ne pas mettre de timbre) Défenseur des droits Libre réponse 71120 75342 Paris CEDEX 07.</li>
					</ul>
					<p> </p>',
	                'fulltext'=>'',
	                'state'=>'1',
		            'attribs'=>'{"article_layout":"","show_title":"0","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}'
				);
	            $accessibility_article = EmundusHelperUpdate::createJoomlaArticle($data,'rgpd');

				if($accessibility_article['status']){
					$query->select('params')
						->from($db->quoteName('#__modules'))
						->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_footer'));
					$db->setQuery($query);
					$params = $db->loadResult();

					if (!empty($params))
					{
						$params = json_decode($params);
						$alias = $params->mod_emundus_footer_accessibility_alias ?: 'accessibilite';

						$query->clear()
							->select('id')
							->from($db->quoteName('#__menu'))
							->where($db->quoteName('alias').' LIKE '.$db->quote($alias));
						$db->setQuery($query);
						$accessibility_menu = $db->loadResult();

						if (!empty($accessibility_menu))
						{
							$query->clear()
								->update($db->quoteName('#__menu'))
								->set($db->quoteName('link').' = '.$db->quote('index.php?option=com_content&view=article&id='.$accessibility_article['id']))
								->set($db->quoteName('params').' = JSON_REPLACE(params, "$.show_title", 0)')
								->where($db->quoteName('id').' = '.$db->quote($accessibility_menu));
							$db->setQuery($query);
							$db->execute();
						} else {
							$datas = [
								'menutype'     => 'topmenu',
								'title'        => 'Accessibilité',
								'alias'        => 'accessibilite',
								'path'         => 'accessibilite',
								'link'         => 'index.php?option=com_content&view=article&id='.$accessibility_article['id'],
								'type'         => 'component',
								'component_id' => 22,
								'params'       => [
									'show_title' => 0
								]
							];
							EmundusHelperUpdate::addJoomlaMenu($datas);
						}

					}
				}
            }

			if (version_compare($cache_version, '1.37.0', '<=') || $firstrun){
				EmundusHelperUpdate::updateProfileMenu();

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ACCOUNT_PERSONAL_DETAILS', 'Informations personnelles', 'override', null, 'fabrik_groups', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ACCOUNT_PERSONAL_DETAILS', 'Personal details', 'override', null, 'fabrik_groups', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_USERS_DEFAULT_LANGAGE', 'Langue de préférence', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_USERS_DEFAULT_LANGAGE', 'Preferred langage', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_USERS_NATIONALITY', 'Nationalité', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_USERS_NATIONALITY', 'Nationality', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ACCOUNT_INFORMATIONS', 'Informations de compte', 'override', null, 'fabrik_groups', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ACCOUNT_INFORMATIONS', 'Account informations', 'override', null, 'fabrik_groups', 'label', 'en-GB');

				EmundusHelperUpdate::installExtension('eMundus - Filtres avancés [mod_emundus_filters]', 'mod_emundus_filters', '{"name":"eMundus - Filtres avancés [mod_emundus_filters]","type":"module","creationDate":"May 2022","author":"LEGENDRE J\u00e9r\u00e9my","copyright":"Copyright (C) 2022 eMundus. All rights reserved.","authorEmail":"jeremy.legendre@emundus.fr","authorUrl":"www.emundus.fr","version":"1.0.0","description":"","group":"","filename":"mod_emundus_filters"}', 'module', 1);
				EmundusHelperUpdate::enableEmundusPlugins('mod_emundus_filters');

				$xml_file = JPATH_SITE . '/templates/g5_helium/templateDetails.xml';
				$xml      = simplexml_load_file($xml_file);
				if($xml)
				{
					$positions = $xml->xpath('//extension/positions');
					// Check if position emundus_filters exist
					$exist = false;
					foreach ($positions[0]->children() as $position)
					{
						if ($position == 'emundus_filters')
						{
							$exist = true;
						}
					}
					if (!$exist)
					{
						$positions[0]->addChild('position', 'emundus_filters');
					}
					$xml->asXML($xml_file);
				}

				// Setup our new layouts
				$query->clear()
					->update($db->quoteName('#__fabrik_forms'))
					->set($db->quoteName('form_template') . ' = ' . $db->quote('emundus'))
					->where($db->quoteName('form_template') . ' = ' . $db->quote('_emundus'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__fabrik_lists'))
					->set($db->quoteName('template') . ' = ' . $db->quote('emundus'))
					->where($db->quoteName('template') . ' = ' . $db->quote('bootstrap'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__menu'))
					->set($db->quoteName('params') . ' = JSON_REPLACE(params,"$.fabriklayout","emundus")')
					->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_fabrik&view=form&formid=307'));
				$db->setQuery($query);
				$db->execute();

				EmundusHelperUpdate::addYamlVariable('location', 'gantry-assets://custom/scss/main.compiled.css', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css', true, true);
				EmundusHelperUpdate::addYamlVariable('inline', '', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('extra', '{  }', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('priority', '0', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');
				EmundusHelperUpdate::addYamlVariable('name', 'Main', JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml', 'css');

				$query->clear()
					->update($db->quoteName('#__fabrik_forms'))
					->set($db->quoteName('params') . ' = JSON_REPLACE(params,"$.labels_above","1")');
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__fabrik_forms'))
					->set($db->quoteName('params') . ' = JSON_REPLACE(params,"$.labels_above_details","1")');
				$db->setQuery($query);
				$db->execute();

				EmundusHelperUpdate::installExtension('plg_fabrik_element_panel', 'panel', '{"name":"plg_fabrik_element_panel","type":"plugin","creationDate":"July 2023","author":"eMundus","copyright":"Copyright (C) 2005-2023 Media A-Team, Inc. - All rights reserved.","authorEmail":"dev@emundus.io","authorUrl":"www.emundus.fr","version":"3.10","description":"PLG_ELEMENT_PANEL_DESCRIPTION","group":"","filename":"panel"}', 'plugin', 1, 'fabrik_element');

				EmundusHelperUpdate::installExtension('plg_fabrik_element_currency', 'currency', '{"name":"plg_fabrik_element_currency","type":"plugin","creationDate":"Mai 2023","author":"eMundus - Thibaud Grignon","copyright":"Copyright (C) 2005-2021 Media A-Team, Inc. - All rights reserved.","authorEmail":"dev@emundus.io","authorUrl":"www.emundus.fr","version":"3.10","description":"PLG_ELEMENT_FIELD_DESCRIPTION","group":"","filename":"currency"}', 'plugin', 1, 'fabrik_element');
				$columns      = [
					[
						'name'    => 'symbol',
						'type'    => 'varchar',
						'length'  => 255,
						'null'    => 0,
					],
					[
						'name'    => 'iso3',
						'type'    => 'varchar',
						'length'  => 3,
						'null'    => 0,
					],
					[
						'name'    => 'name',
						'type'    => 'varchar',
						'length'  => 255,
						'null'    => 0,
					],
					[
						'name'    => 'published',
						'type'    => 'tinyint',
						'length'  => 1,
						'default' => 1,
						'null'    => 0,
					]
				];
				$data_currency = EmundusHelperUpdate::createTable('data_currency', $columns);

				if($data_currency['status']){
					EmundusHelperUpdate::executeSQlFile('insert_data_currency');
				}

				/* UPDATE COLORS */
				EmundusHelperUpdate::updateNewColors();
				EmundusHelperUpdate::initNewVariables();

				EmundusHelperUpdate::insertTranslationsTag('COM_FABRIK_OPTIONNAL_FIELD', 'facultatif');
				EmundusHelperUpdate::insertTranslationsTag('COM_FABRIK_OPTIONNAL_FIELD', 'optional', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_FABRIK_REQUIRED_ICON_NOT_DISPLAYED', 'Tous les champs sont obligatoires sauf mention contraire');
				EmundusHelperUpdate::insertTranslationsTag('COM_FABRIK_REQUIRED_ICON_NOT_DISPLAYED', 'Tous les champs sont obligatoires sauf mention contraire', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_FABRIK_REPEAT_GROUP_MAX','Vous pouvez saisir jusqu\'à %s entrées');
				EmundusHelperUpdate::insertTranslationsTag('COM_FABRIK_REPEAT_GROUP_MAX','You can enter up to %s entries', 'override', null, null, null, 'en-GB');

				$dashboard_files_by_status_params = array(
					'eval' => 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

try {
    $query->select(\'*\')
        ->from($db->quoteName(\'jos_emundus_setup_status\'))
        ->order(\'ordering\');
    $db->setQuery($query);
    $status = $db->loadObjectList();

    $datas = [];

    foreach ($status as $statu) {
        $file = new stdClass;
        $file->label = $statu->value;

        $styles_files = JPATH_SITE . \'/templates/g5_helium/custom/config/default/styles.yaml\';
		$yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($styles_files));

		$file->color = $yaml[\'accent\'][$statu->class];

        $query->clear()
            ->select(\'COUNT(ecc.id) as files\')
            ->from($db->quoteName(\'#__emundus_campaign_candidature\',\'ecc\'))
            ->leftJoin($db->quoteName(\'#__emundus_setup_campaigns\',\'esc\').\' ON \'.$db->quoteName(\'esc.id\').\' = \'.$db->quoteName(\'ecc.campaign_id\'))
            ->where($db->quoteName(\'ecc.status\') . \' = \' . $db->quote($statu->step))
            ->andWhere($db->quoteName(\'ecc.published\') . \' = \' . $db->quote(1));

        $db->setQuery($query);
        $file->value = $db->loadResult();
        $datas[] = $file;
    }

	$dataSource = new stdClass;
	$dataSource->chart = new stdClass;
	$dataSource->chart = array(
		\'caption\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_CAPTION"),
		\'xaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_STATUS"),
		\'yaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_NUMBER"),
		\'animation\' => 1,
		\'numberScaleValue\' => "1",
		\'numDivLines\' => 1,
		\'numbersuffix\'=> "",
		\'theme\'=> "fusion"
	);
	$dataSource->data = $datas;
	return $dataSource;
} catch (Exception $e) {
	return array(\'dataset\' => \'\');
}'
				);
				EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS', $dashboard_files_by_status_params);

				EmundusHelperUpdate::insertTranslationsTag('JGLOBAL_AUTH_NO_USER','Cet utilisateur et/ou ce mot de passe est incorrect');
				EmundusHelperUpdate::insertTranslationsTag('JGLOBAL_AUTH_NO_USER','This user and/or password is incorrect', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('JGLOBAL_AUTH_INVALID_PASS','Cet utilisateur et/ou ce mot de passe est incorrect');
				EmundusHelperUpdate::insertTranslationsTag('JGLOBAL_AUTH_INVALID_PASS','This user and/or password is incorrect', 'override', null, null, null, 'en-GB');

				$old_values = [
					'fr-FR' => 'Cet utilisateur et/ou ce mot de passe est incorrecte'
				];
				$new_values = [
					'fr-FR' => 'Cet utilisateur et/ou ce mot de passe est incorrect'
				];
				EmundusHelperUpdate::updateOverrideTag('JGLOBAL_AUTH_INVALID_PASS', $old_values, $new_values);
				EmundusHelperUpdate::updateOverrideTag('JGLOBAL_AUTH_NO_USER', $old_values, $new_values);

				EmundusHelperUpdate::insertTranslationsTag('JGLOBAL_AUTH_NO_USER','Cet utilisateur et/ou ce mot de passe est incorrect');
				EmundusHelperUpdate::insertTranslationsTag('JGLOBAL_AUTH_NO_USER','This user and/or password is incorrect', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_WANT_RESET_PASSWORD','Souhaitez-vous envoyer un lien de réinitialisation ?');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_WANT_RESET_PASSWORD','Would you like to send a reset link?', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT_FOR_OTHER','%s - Une demande de réinitialisation de mot de passe a été effectuée pour vous');
				EmundusHelperUpdate::insertTranslationsTag('COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT_FOR_OTHER','%s - A password reset request has been made for you', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_USERS_EMAIL_PASSWORD_RESET_BODY_FOR_OTHER','<p>Madame, Monsieur,</p>\n<p>Une demande de réinitialisation du mot de passe de votre compte <b> %s</b> a été effectuée par un administrateur.</p>\n<p>Cliquez sur le lien ci-dessous pour finaliser la réinitialisation :</p>\n<p>%3$s</p>\n<p>Si ce lien ne fonctionne pas, voici le code de vérification à saisir sur la page de réinitialisation de mot de passe :  %2$s</p>');
				EmundusHelperUpdate::insertTranslationsTag('COM_USERS_EMAIL_PASSWORD_RESET_BODY_FOR_OTHER','<p>Madam, Sir,</p>\n<p>A request to reset the password for your <b> %s</b> account has been made by an administrator.</p>\n<p>Click on the link below to complete the reset:</p>\n<p>%3$s</p>\n<p>If this link does not work, here is the verification code to enter on the password reset page: %2$s</p>', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_TITLE','Voulez-vous vraiment quitter le formulaire ?');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_TITLE','Do you really want to leave the form?', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_TEXT','Les données/informations saisies sur l’étape en cours ne seront pas conservées. Seules les saisies validées en fin d’étape sont sauvegardées.');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_TEXT','Entries for the current stage will not be saved. Only entries validated at the end of the stage will be saved.', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_CONFIRM','Quitter sans enregistrer');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_CONFIRM','Quit without saving', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_CANCEL','Retour');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_WANT_EXIT_FORM_CANCEL','Go back', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_USERS_EXCEPTIONS_INTRO','Utilisateurs ayant le droit de compléter des formulaires en dehors des périodes de candidature. Utile pour tester un environnement de candidature avant la publication d\'une phase !');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_USERS_EXCEPTIONS_INTRO','Users with the right to complete forms outside the application periods. Useful for testing an application environment before publishing a phase!', 'override', null, null, null, 'en-GB');

				$query->clear()
					->delete($db->quoteName('#__emundus_setup_emails'))
					->where($db->quoteName('lbl') . ' LIKE ' . $db->quote('regenerate_password'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__emundus_setup_actions'))
					->set($db->quoteName('status') . ' = 0')
					->where($db->quoteName('name') . ' IN (' . $db->quote('mail_evaluator') . ',' . $db->quote('mail_group') . ')');
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__menu'))
					->set($db->quoteName('note') . ' = ' . $db->quote('11|u|1,11|c|1'))
					->where($db->quoteName('note') . ' = ' . $db->quote('11|u|1'))
					->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_emundus&view=files&format=raw&layout=access&users={fnums}'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update('#__emundus_setup_config')
					->set($db->quoteName('value') . ' = ' . $db->quote('{"forms":{"title":"COM_EMUNDUS_ONBOARD_FORMS","tabs":[{"title":"COM_EMUNDUS_FORM_MY_FORMS","key":"form","controller":"form","getter":"getallform","actions":[{"action":"duplicateform","label":"COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE","controller":"form","name":"duplicate"},{"action":"publishform","label":"COM_EMUNDUS_ONBOARD_ACTION_PUBLISH","controller":"form","name":"publish","showon":{"key":"status","operator":"!=","value":"1"}},{"action":"unpublishform","label":"COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH","controller":"form","name":"unpublish","showon":{"key":"status","operator":"=","value":"1"}},{"action":"index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"},{"action":"createform","controller":"form","label":"COM_EMUNDUS_ONBOARD_ADD_FORM","name":"add"}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_FILTER_PUBLISH","getter":"","controller":"form","key":"filter","default":"1","values":[{"label":"COM_EMUNDUS_ONBOARD_FILTER_PUBLISH","value":"1"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH","value":"Unpublish"}]}]},{"title":"COM_EMUNDUS_FORM_MY_EVAL_FORMS","key":"form_evaluations","controller":"form","getter":"getallgrilleEval","actions":[{"action":"createformeval","label":"COM_EMUNDUS_ONBOARD_ADD_EVAL_FORM","controller":"form","name":"add"},{"action":"/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%&mode=eval","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"}],"filters":[]},{"title":"COM_EMUNDUS_FORM_PAGE_MODELS","key":"form_models","controller":"formbuilder","getter":"getallmodels","actions":[{"action":"deleteformmodelfromids","label":"COM_EMUNDUS_ACTIONS_DELETE","controller":"formbuilder","parameters":"&model_ids=%id%","name":"delete"},{"action":"/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%form_id%&mode=models","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"}],"filters":[]}]},"campaigns":{"title":"COM_EMUNDUS_ONBOARD_CAMPAIGNS","tabs":[{"title":"COM_EMUNDUS_ONBOARD_CAMPAIGNS","key":"campaign","controller":"campaign","getter":"getallcampaign","actions":[{"action":"index.php?option=com_emundus&view=campaigns&layout=add","label":"COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN","controller":"campaign","name":"add","type":"redirect"},{"action":"duplicatecampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE","controller":"campaign","name":"duplicate"},{"action":"index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"campaign","type":"redirect","name":"edit"},{"action":"deletecampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_DELETE","controller":"campaign","name":"delete","confirm":"COM_EMUNDUS_ONBOARD_CAMPDELETE","showon":{"key":"nb_files","operator":"<","value":"1"}},{"action":"unpublishcampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH","controller":"campaign","name":"unpublish","showon":{"key":"published","operator":"=","value":"1"}},{"action":"publishcampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_PUBLISH","controller":"campaign","name":"publish","showon":{"key":"published","operator":"=","value":"0"}},{"action":"pincampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_PIN_CAMPAIGN","controller":"campaign","name":"pin","icon":"push_pin","iconOutlined":true,"showon":{"key":"pinned","operator":"!=","value":"1"}},{"action":"unpincampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_UNPIN_CAMPAIGN","controller":"campaign","name":"unpin","icon":"push_pin","iconOutlined":false,"showon":{"key":"pinned","operator":"=","value":"1"}}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_FILTER_ALL","getter":"","controller":"campaigns","key":"filter","values":[{"label":"COM_EMUNDUS_ONBOARD_FILTER_ALL","value":"all"},{"label":"COM_EMUNDUS_CAMPAIGN_YET_TO_COME","value":"yettocome"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_OPEN","value":"ongoing"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_CLOSE","value":"Terminated"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_PUBLISH","value":"Publish"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH","value":"Unpublish"}],"default":"Publish"},{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAMS","getter":"getallprogramforfilter","controller":"programme","key":"program","values":null}]},{"title":"COM_EMUNDUS_ONBOARD_PROGRAMS","key":"programs","controller":"programme","getter":"getallprogram","actions":[{"action":"index.php?option=com_fabrik&view=form&formid=108","controller":"programme","label":"COM_EMUNDUS_ONBOARD_ADD_PROGRAM","name":"add","type":"redirect"},{"action":"index.php?option=com_fabrik&view=form&formid=108&rowid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"programme","type":"redirect","name":"edit"}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAM_CATEGORIES","getter":"getprogramcategories","controller":"programme","key":"recherche","values":null}]}]},"emails":{"title":"COM_EMUNDUS_ONBOARD_EMAILS","tabs":[{"controller":"email","getter":"getallemail","title":"COM_EMUNDUS_ONBOARD_EMAILS","key":"emails","actions":[{"action":"index.php?option=com_emundus&view=emails&layout=add","controller":"email","label":"COM_EMUNDUS_ONBOARD_ADD_EMAIL","name":"add","type":"redirect"},{"action":"index.php?option=com_emundus&view=emails&layout=add&eid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"email","type":"redirect","name":"edit"},{"action":"deleteemail","label":"COM_EMUNDUS_ACTIONS_DELETE","controller":"email","name":"delete","showon":{"key":"type","operator":"!=","value":"1"}},{"action":"preview","label":"COM_EMUNDUS_ONBOARD_VISUALIZE","controller":"email","name":"preview","icon":"preview","iconOutlined":true,"title":"subject","content":"message"}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAM_CATEGORIES","getter":"getemailcategories","controller":"email","key":"recherche","values":null}]}]}}'))
					->where('namekey = ' . $db->quote('onboarding_lists'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->select('form_id')
					->from($db->quoteName('#__emundus_setup_formlist'))
					->where($db->quoteName('type') . ' LIKE ' . $db->quote('profile'));
				$db->setQuery($query);
				$form_id = $db->loadResult();

				$query->clear()
					->select('group_id')
					->from($db->quoteName('#__fabrik_formgroup'))
					->where($db->quoteName('form_id') . ' = ' . $db->quote($form_id));
				$db->setQuery($query);
				$groups = $db->loadColumn();

				if(!empty($groups))
				{
					$query->clear()
						->select('id,params')
						->from($db->quoteName('#__fabrik_elements'))
						->where($db->quoteName('group_id') . ' IN (' . implode(',', $groups) . ')')
						->andWhere($db->quoteName('name') . ' IN (' . $db->quote('nationality') . ',' . $db->quote('default_language') . ')');
					$db->setQuery($query);
					$elements = $db->loadObjectList();

					foreach ($elements as $element)
					{
						$params = json_decode($element->params, true);
						$params['bootstrap_class'] = 'input-large';

						$query->clear()
							->update($db->quoteName('#__fabrik_elements'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $db->quote($element->id));
						$db->setQuery($query);
						$db->execute();
					}
				}

				$query->clear()
					->delete($db->quoteName('#__modules'))
					->where($db->quoteName('title') . ' LIKE ' . $db->quote('Spotlight eMundus'));
				$db->setQuery($query);
				$db->execute();

                EmundusHelperUpdate::insertTranslationsTag('COM_USERS_RESET_REQUEST_FAILED', 'Si un compte est associé à cette adresse, alors vous avez reçu un email afin de réinitialiser votre mot de passe', 'override', null, 'fabrik_groups', 'label', 'fr-FR');
                EmundusHelperUpdate::insertTranslationsTag('COM_USERS_RESET_REQUEST_FAILED', 'If an account is associated with this address, you have received an email to reset your password.', 'override', null, 'fabrik_groups', 'label', 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_FABRIK_GO','Rechercher');
				EmundusHelperUpdate::insertTranslationsTag('COM_FABRIK_GO','Search', 'override', null, null, null, 'en-GB');

				$query->clear()
					->update($db->quoteName('#__menu'))
					->set($db->quoteName('published') . ' = 1')
					->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_emundus&view=application&format=raw&layout=logs'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->select('id')
					->from($db->quoteName('#__fabrik_forms'))
					->where($db->quoteName('label') . ' LIKE ' . $db->quote('SETUP_EMAIL_DETAILS'));
				$db->setQuery($query);
				$emails_history_formid = $db->loadResult();

				if(!empty($emails_history_formid)) {
					$query->clear()
						->select('group_id')
						->from($db->quoteName('#__fabrik_formgroup'))
						->where($db->quoteName('form_id') . ' = ' . $db->quote($emails_history_formid));
					$db->setQuery($query);
					$groups = $db->loadColumn();

					$query->clear()
						->update($db->quoteName('#__fabrik_elements'))
						->set($db->quoteName('hidden') . ' = 1')
						->where($db->quoteName('group_id') . ' IN (' . implode(',', $groups) . ')')
						->where($db->quoteName('name') . ' LIKE ' . $db->quote('state'));
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->update($db->quoteName('#__fabrik_elements'))
						->set($db->quoteName('show_in_list_summary') . ' = 0')
						->set($db->quoteName('filter_type') . ' = ' . $db->quote(''))
						->where($db->quoteName('group_id') . ' IN (' . implode(',', $groups) . ')')
						->where($db->quoteName('name') . ' LIKE ' . $db->quote('message'));
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->update($db->quoteName('#__menu'))
						->set($db->quoteName('template_style_id') . ' = 22')
						->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_fabrik%'));
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->select('id,params')
						->from($db->quoteName('#__fabrik_lists'))
						->where($db->quoteName('form_id') . ' = ' . $db->quote($emails_history_formid));
					$db->setQuery($query);
					$list = $db->loadObject();

					if(!empty($list))
					{
						$params = json_decode($list->params, true);
						$params['csv_export_frontend'] = '10';
						$params['allow_edit_details'] = '10';
						$params['allow_add'] = '10';
						$params['allow_delete'] = '10';
						$params['distinct'] = '0';

						$query->clear()
							->update($db->quoteName('#__fabrik_lists'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $db->quote($list->id));
						$db->setQuery($query);
						$db->execute();
					}
				}

				/* DASHBOARD FOR SYSADMIN PROFILE */
				$query->clear()
					->select('count(id)')
					->from($db->quoteName('#__emundus_widgets_repeat_access'))
					->where($db->quoteName('profile') . ' = 1');
				$db->setQuery($query);
				$existing_dashboard = $db->loadResult();

				if(empty($existing_dashboard))
				{
				$query->clear()
					->select('*')
					->from($db->quoteName('#__emundus_widgets_repeat_access'))
					->where($db->quoteName('profile') . ' = 2');
				$db->setQuery($query);
				$dashboards = $db->loadObjectList();

					foreach ($dashboards as $dashboard)
					{
						$query->clear()
							->insert($db->quoteName('#__emundus_widgets_repeat_access'));
						foreach ($dashboard as $key => $widget)
						{
							if ($key == 'id')
							{
								continue;
							}

							if($key == 'profile')
							{
								$query->set($db->quoteName($key) . ' = 1');
								continue;
							}

							$query->set($db->quoteName($key) . ' = ' . $db->quote($widget));
						}
						$db->setQuery($query);
						$db->execute();
					}
				}

				// Add redirect rules in .htaccess file for Tchooz security
				$file = JPATH_ROOT . '/.htaccess';
				$insertLines = "# Redirect to home page all requests to hidden files or directories" . PHP_EOL .
			"RewriteRule ^\\..+ / [R=301,L]" . PHP_EOL . PHP_EOL .
			"# Redirect to the home page all requests to other files or directories not needed on the web product" . PHP_EOL .
			"RewriteRule ^cli / [R=301,L]" . PHP_EOL .
			"RewriteRule ^Dockerfile / [R=301,L]" . PHP_EOL .
			"RewriteRule ^LICENCE / [R=301,L]" . PHP_EOL .
			"RewriteRule ^configuration.php / [R=301,L]" . PHP_EOL .
			"RewriteRule ^defines.php / [R=301,L]" . PHP_EOL .
			"RewriteRule ^logs / [R=301,L]" . PHP_EOL . PHP_EOL .
			"# Redirect specific file types to home page" . PHP_EOL .
			"RewriteRule ^.*\\.sql / [R=301,L]" . PHP_EOL .
			"RewriteRule ^.*\\.zip / [R=301,L]" . PHP_EOL .
			"RewriteRule ^.*\\.json / [R=301,L]" . PHP_EOL .
			"RewriteRule ^.*\\.config.js / [R=301,L]" . PHP_EOL .
			"RewriteRule ^.*\\.md / [R=301,L]" . PHP_EOL;
				$succeed['add_htaccess_redirect_rules'] = EmundusHelperUpdate::insertIntoFile($file, $insertLines);

				// Add exception rules in .htaccess file for certbot and manifest.json file
				$insertLines = "# Redirect exclusion list" . PHP_EOL .
			"RewriteCond %{REQUEST_URI} !^/.well-known/acme-challenge/" . PHP_EOL .
			"RewriteCond %{REQUEST_URI} !^/.manifest.json" . PHP_EOL;
				$insertBeforeLine = "# Redirect to home page all requests to hidden files or directories";
				$succeed['add_htaccess_exeption'] = EmundusHelperUpdate::insertIntoFile($file, $insertLines, $insertBeforeLine);

				$old_values = [
					'fr-FR' => 'Confirmer le mot de passe <span class=\"required\"></span><ul><li> Longueur minimum : 6 caractères.</li>\n<li>Avec au moins 1 chiffre.</li>\n<li>Avec au moins 1 symbole.</li>\n<li>Avec au moins 1 lettre majuscule.</li></ul>\n',
				];
				$new_values = [
					'fr-FR' => 'Confirmez le mot de passe'
				];
				EmundusHelperUpdate::updateOverrideTag('COM_USERS_FIELD_RESET_PASSWORD2_LABEL', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Mot de passe',
					'en-GB' => 'Password',
				];
				$new_values = [
					'fr-FR' => 'Nouveau mot de passe',
					'en-GB' => 'New password',
				];
				EmundusHelperUpdate::updateOverrideTag('COM_USERS_FIELD_RESET_PASSWORD1_LABEL', $old_values, $new_values);

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_NEW_FILE','Nouveau dossier');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_NEW_FILE','New file', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_NEW_FILE_DESC','Votre dossier est en cours de création, merci de patienter...');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_FABRIK_NEW_FILE_DESC','Your file is being created, so please be patient...', 'override', null, null, null, 'en-GB');

				$old_values = [
					'fr-FR' => 'Longueur minimum : %s caractères.',
					'en-GB' => 'Minimum length: %s characters.'
				];
				$new_values = [
					'fr-FR' => 'Minimum %s caractères',
					'en-GB' => 'Minimum %s characters'
				];
				EmundusHelperUpdate::updateOverrideTag('USER_PASSWORD_MIN_LENGTH', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Avec au moins %s chiffre.',
					'en-GB' => 'With at least %s number(s).'
				];
				$new_values = [
					'fr-FR' => '%s chiffre(s)',
					'en-GB' => '%s number(s)'
				];
				EmundusHelperUpdate::updateOverrideTag('USER_PASSWORD_MIN_INT', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Avec au moins %s symbole.',
					'en-GB' => 'With at least %s symbol(s).'
				];
				$new_values = [
					'fr-FR' => '%s symbole(s)',
					'en-GB' => '%s symbol(s)'
				];
				EmundusHelperUpdate::updateOverrideTag('USER_PASSWORD_MIN_SYM', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Avec au moins %s lettre majuscule.',
					'en-GB' => 'With at least %s uppercase letter(s).'
				];
				$new_values = [
					'fr-FR' => '%s lettre(s) majuscule',
					'en-GB' => '%s uppercase letter(s)'
				];
				EmundusHelperUpdate::updateOverrideTag('USER_PASSWORD_MIN_UPPER', $old_values, $new_values);

				$old_values = [
					'fr-FR' => 'Avec au moins %s lettre minuscule.',
					'en-GB' => 'With at least %s lowercase letter(s).'
				];
				$new_values = [
					'fr-FR' => '%s lettre(s) minuscule',
					'en-GB' => '%s lowercase letter(s)'
				];
				EmundusHelperUpdate::updateOverrideTag('USER_PASSWORD_MIN_LOWER', $old_values, $new_values);

				$query->clear()
					->select('id')
					->from($db->quoteName('#__fabrik_forms'))
					->where($db->quoteName('label') . ' LIKE ' . $db->quote('FORM_REGISTRATION'));
				$db->setQuery($query);
				$registration_form_id = $db->loadResult();

				if(!empty($registration_form_id))
				{
					$query->clear()
						->update($db->quoteName('#__fabrik_forms'))
						->set($db->quoteName('intro') . ' = ' . $db->quote(''))
						->where($db->quoteName('id') . ' = ' . $db->quote($registration_form_id))
						->where($db->quoteName('intro') . ' LIKE ' . $db->quote('<p>EMUNDUS_REGISTRATION_INSTRUCTIONS</p>'));
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->select('id')
						->from($db->quoteName('#__fabrik_groups'))
						->where($db->quoteName('name') . ' LIKE ' . $db->quote('GROUP_REGISTRATION_CIVILITY'));
					$db->setQuery($query);
					$group_civility = $db->loadAssoc();

					if(empty($group_civility))
					{
						$datas          = [
							'name'  => 'GROUP_REGISTRATION_CIVILITY'
						];
						$group_civility = EmundusHelperUpdate::addFabrikGroup($datas, ['repeat_group_show_first' => 1],1,true);

						EmundusHelperUpdate::joinFormGroup($registration_form_id, [$group_civility['id']]);
					}


					$query->clear()
						->select('id')
						->from($db->quoteName('#__fabrik_groups'))
						->where($db->quoteName('name') . ' LIKE ' . $db->quote('GROUP_REGISTRATION_NAMES'));
					$db->setQuery($query);
					$group = $db->loadAssoc();

					if(empty($group))
					{
						$datas = [
							'name'  => 'GROUP_REGISTRATION_NAMES'
						];
						$group = EmundusHelperUpdate::addFabrikGroup($datas, ['group_columns' => 2, 'repeat_group_show_first' => 1],1,true);

						EmundusHelperUpdate::joinFormGroup($registration_form_id,[$group['id']]);
					}

					$elements_to_search = [$db->quote('firstname'), $db->quote('lastname')];
					$query->clear()
						->select('fe.id')
						->from($db->quoteName('#__fabrik_elements','fe'))
						->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
						->where($db->quoteName('ffg.form_id') . ' = ' . $db->quote($registration_form_id))
						->where($db->quoteName('fe.name') . ' IN (' .implode(',',$elements_to_search) . ')')
						->where($db->quoteName('fe.published') . ' = 1');
					$db->setQuery($query);
					$elements = $db->loadColumn();

					if(!empty($elements))
					{
						$query->clear()
							->update($db->quoteName('#__fabrik_elements'))
							->set($db->quoteName('group_id') . ' = ' . $db->quote($group['id']))
							->where($db->quoteName('id') . ' IN (' . implode(',', $elements) . ')');
						$db->setQuery($query);
						$db->execute();
					}

					$query->clear()
						->select('fe.id')
						->from($db->quoteName('#__fabrik_elements','fe'))
						->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
						->where($db->quoteName('ffg.form_id') . ' = ' . $db->quote($registration_form_id))
						->where($db->quoteName('fe.name') . ' LIKE ' . $db->quote('civility'))
						->where($db->quoteName('fe.published') . ' = 1');
					$db->setQuery($query);
					$elements = $db->loadColumn();

					if(!empty($elements))
					{
						$query->clear()
							->update($db->quoteName('#__fabrik_elements'))
							->set($db->quoteName('group_id') . ' = ' . $db->quote($group_civility['id']))
							->where($db->quoteName('id') . ' IN (' . implode(',', $elements) . ')');
						$db->setQuery($query);
						$db->execute();
					}

					$query->clear()
						->select('fe.id,fe.params')
						->from($db->quoteName('#__fabrik_elements','fe'))
						->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
						->where($db->quoteName('ffg.form_id') . ' = ' . $db->quote($registration_form_id))
						->where($db->quoteName('fe.name') . ' LIKE ' . $db->quote('email'))
						->where($db->quoteName('fe.published') . ' = 1');
					$db->setQuery($query);
					$email_field = $db->loadObject();

					if(!empty($email_field))
					{
						$params = json_decode($email_field->params, true);
						$params['password'] = 3;

						$query->clear()
							->update($db->quoteName('#__fabrik_elements'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $db->quote($email_field->id));
						$db->setQuery($query);
						$db->execute();
					}

					$query->clear()
						->select('fe.id,fe.params')
						->from($db->quoteName('#__fabrik_elements','fe'))
						->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
						->where($db->quoteName('ffg.form_id') . ' = ' . $db->quote($registration_form_id))
						->where($db->quoteName('fe.name') . ' LIKE ' . $db->quote('password'))
						->where($db->quoteName('fe.published') . ' = 1');
					$db->setQuery($query);
					$password_field = $db->loadObject();

					if(!empty($password_field))
					{
						$tip_code = '$params = JComponentHelper::getParams(\'com_users\');
$min_length = $params->get(\'minimum_length\');
$min_int = $params->get(\'minimum_integers\');
$min_sym = $params->get(\'minimum_symbols\');
$min_up = $params->get(\'minimum_uppercase\');
$min_low = $params->get(\'minimum_lowercase\');

$tip_text = JText::sprintf(\'USER_PASSWORD_MIN_LENGTH\', $min_length);

if ((int)$min_int > 0) {
	$tip_text .= \',\'.JText::sprintf(\'USER_PASSWORD_MIN_INT\', $min_int);
}
if ((int)$min_sym > 0) {
	$tip_text .= \',\'.JText::sprintf(\'USER_PASSWORD_MIN_SYM\', $min_sym);
}
if ((int)$min_up > 0) {
	$tip_text .= \',\'.JText::sprintf(\'USER_PASSWORD_MIN_UPPER\', $min_up);
}
if ((int)$min_low > 0) {
	$tip_text .= \',\'.JText::sprintf(\'USER_PASSWORD_MIN_LOWER\', $min_low);
}

return $tip_text;';
						$params = json_decode($password_field->params, true);
						$params['rollover'] = $tip_code;
						$params['tipseval'] = 1;
						$params['password'] = 1;
						$params['validations'] = [
							'plugin' => ['checkpassword','notempty'],
							'plugin_published' => ['1','1'],
							'validate_in' => ['both','both'],
							'validation_on' => ['both','both'],
							'validate_hidden' => ['0','0'],
							'must_validate' => ['0','0'],
							'show_icon' => ['1','1'],
						];

						$query->clear()
							->update($db->quoteName('#__fabrik_elements'))
							->set($db->quoteName('plugin') . ' = ' . $db->quote('field'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $db->quote($password_field->id));
						$db->setQuery($query);
						$db->execute();

						$js_showicon = 'var passwordInput = document.querySelector(&#039;#jos_emundus_users___password&#039;);

var spanShowPassword = document.createElement(&#039;span&#039;);
spanShowPassword.classList.add(&#039;material-icons-outlined&#039;);
spanShowPassword.classList.add(&#039;em-pointer&#039;);
spanShowPassword.innerText = &quot;visibility_off&quot;;
spanShowPassword.style.position = &quot;absolute&quot;;
spanShowPassword.style.top = &quot;12px&quot;;
spanShowPassword.style.right = &quot;10px&quot;;
spanShowPassword.style.opacity = &quot;0.3&quot;;

passwordInput.parentNode.style.position = &quot;relative&quot;;

passwordInput.parentNode.insertBefore(spanShowPassword, passwordInput.nextSibling);

spanShowPassword.addEventListener(&#039;click&#039;, function () {
  if (spanShowPassword.innerText == &quot;visibility&quot;) {
    spanShowPassword.innerText = &quot;visibility_off&quot;;
    passwordInput.type = &quot;password&quot;;
  } else {
    spanShowPassword.innerText = &quot;visibility&quot;;
    passwordInput.type = &quot;text&quot;;
  }
});';

						$query->clear()
							->select('id,params')
							->from($db->quoteName('#__fabrik_jsactions'))
							->where($db->quoteName('element_id') . ' = ' . $db->quote($password_field->id))
							->where($db->quoteName('action') . ' LIKE ' . $db->quote('load'));
						$db->setQuery($query);
						$password_js = $db->loadObject();

						if(!empty($password_js))
						{
							$params = json_decode($password_js->params, true);
							$params['js_published'] = 1;

							$query->clear()
								->update($db->quoteName('#__fabrik_jsactions'))
								->set($db->quoteName('code') . ' = ' . $db->quote($js_showicon))
								->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
								->where($db->quoteName('id') . ' = ' . $db->quote($password_js->id));
							$db->setQuery($query);
							$db->execute();
						}

						$query->clear()
							->select('id,params')
							->from($db->quoteName('#__fabrik_jsactions'))
							->where($db->quoteName('element_id') . ' = ' . $db->quote($password_field->id))
							->where($db->quoteName('action') . ' LIKE ' . $db->quote('change'));
						$db->setQuery($query);
						$password_js_change = $db->loadObject();

						if(!empty($password_js_change))
						{

							$query->clear()
								->update($db->quoteName('#__fabrik_jsactions'))
								->set($db->quoteName('code') . ' = ' . $db->quote('checkPasswordSymbols(this.form.formElements.get(&#039;jos_emundus_users___password&#039;));'))
								->where($db->quoteName('id') . ' = ' . $db->quote($password_js_change->id));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}

				EmundusHelperUpdate::updateComponentParameter('com_fabrik', 'use_fabrikdebug', 1);

				EmundusHelperUpdate::installExtension('plg_fabrik_validationrule_checkpassword','checkpassword','{"name":"plg_fabrik_validationrule_checkpassword","type":"plugin","creationDate":"September 2023","author":"eMundus","copyright":"Copyright (C) 2015-2023 eMundus - All rights reserved.","authorEmail":"dev@emundus.io","authorUrl":"www.emundus.fr","version":"3.10","description":"PLG_VALIDATIONRULE_CHECKPASSWORD_DESCRIPTION","group":"","filename":"checkpassword"}','plugin',1,'fabrik_validationrule');

				EmundusHelperUpdate::insertTranslationsTag('PLEASE_CHECK_THIS_FIELD','Veuillez cocher la case');
				EmundusHelperUpdate::insertTranslationsTag('PLEASE_CHECK_THIS_FIELD','Please tick the box', 'override', null, null, null, 'en-GB');

				$query->clear()
					->select('form_id')
					->from($db->quoteName('#__emundus_setup_formlist'))
					->where($db->quoteName('type') . ' LIKE ' . $db->quote('profile'));
				$db->setQuery($query);
				$form_id = $db->loadResult();

				if(!empty($form_id))
				{
					$query->clear()
						->select('id,params')
						->from($db->quoteName('#__fabrik_forms'))
						->where($db->quoteName('id') . ' = ' . $db->quote($form_id));
					$db->setQuery($query);
					$form = $db->loadObject();

					if(!empty($form))
					{
						$params = json_decode($form->params, true);

						if(is_array($params['form_php_file']) && !in_array('emundus-updatesession.php', $params['form_php_file']))
						{
							$params['plugin_state'][]          = 1;
							$params['only_process_curl'][]     = 'onAfterProcess';
							$params['form_php_file'][]         = 'emundus-updatesession.php';
							$params['form_php_require_once'][] = 0;
							$params['curl_code'][]             = '';
							$params['plugins'][]               = 'php';
							$params['plugin_locations'][]      = 'both';
							$params['plugin_events'][]         = 'both';
							$params['plugin_description'][]    = 'Update eMundus session';

							$query->clear()
								->update($db->quoteName('#__fabrik_forms'))
								->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
								->where($db->quoteName('id') . ' = ' . $db->quote($form_id));
							$db->setQuery($query);
							$db->execute();
						}
					}
				}

				EmundusHelperUpdate::insertFalangTranslation(2,12,'emundus_setup_tags','description','Identifiant du candidat');
				EmundusHelperUpdate::insertFalangTranslation(2,13,'emundus_setup_tags','description','Nom complet du candidat');
				EmundusHelperUpdate::insertFalangTranslation(2,14,'emundus_setup_tags','description','Email du candidat');
				EmundusHelperUpdate::insertFalangTranslation(2,15,'emundus_setup_tags','description','Identifiant du candidat');
				EmundusHelperUpdate::insertFalangTranslation(2,20,'emundus_setup_tags','description','Nom de l\'utilisateur actif');
				EmundusHelperUpdate::insertFalangTranslation(2,21,'emundus_setup_tags','description','Email de l\'utilisateur actif');
				EmundusHelperUpdate::insertFalangTranslation(2,34,'emundus_setup_tags','description','Numéro de dossier');

				$query->clear()
					->update($db->quoteName('#__menu'))
					->set($db->quoteName('alias') . ' = ' . $db->quote('gestion-des-droits'))
					->where($db->quoteName('alias') . ' LIKE ' . $db->quote('gestion-de-vos-droits'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__content'))
					->set($db->quoteName('alias') . ' = ' . $db->quote('gestion-des-droits'))
					->where($db->quoteName('alias') . ' LIKE ' . $db->quote('gestion-de-vos-droits'));
				$db->setQuery($query);
				$db->execute();

				// Remove spotlight module
				$query->clear()
					->delete($db->quoteName('#__modules'))
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_finder'))
					->where($db->quoteName('title') . ' LIKE ' . $db->quote('Spotlight%'));
				$db->setQuery($query);
				$db->execute();

				// Sort by invitation date
				$query->clear()
					->update($db->quoteName('#__fabrik_lists'))
					->set($db->quoteName('order_by') . ' = ' . $db->quote('["5849"]'))
					->set($db->quoteName('order_dir') . ' = ' . $db->quote('["DESC"]'))
					->where($db->quoteName('label') . ' LIKE ' . $db->quote('TABLE_SETUP_INVITATION_BY_EMAIL'));
				$db->setQuery($query);
				$db->execute();

				// Update databasejoin
				$query->clear()
					->select('id,params')
					->from($db->quoteName('#__fabrik_elements'))
					->where($db->quoteName('name') . ' LIKE ' . $db->quote('course'))
					->where($db->quoteName('plugin') . ' LIKE ' . $db->quote('databasejoin'))
					->where($db->quoteName('group_id') . ' = 139');
				$db->setQuery($query);
				$course_elt = $db->loadObject();

				if(!empty($course_elt))
				{
					$params = json_decode($course_elt->params, true);
					$params['join_db_name'] = 'jos_emundus_setup_programmes';
					$params['join_key_column'] = 'code';
					$params['join_val_column'] = 'label';
					$params['join_val_column_concat'] = "label, ' [', code, ']'";

					$query->clear()
						->update($db->quoteName('#__fabrik_elements'))
						->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
						->where($db->quoteName('id') . ' = ' . $db->quote($course_elt->id));
					$db->setQuery($query);
					$db->execute();
				}

				EmundusHelperUpdate::updateComponentParameter('com_emundus', 'logs', 1);

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_NEWSLETTER','Newsletter');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_NEWSLETTER','Newsletter', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_UNIVERSITY','Université');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_UNIVERSITY','University', 'override', null, null, null, 'en-GB');

				$query->clear()
					->delete($db->quoteName('#__modules'))
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emunduspanel'))
					->where($db->quoteName('position') . ' LIKE ' . $db->quote('content-top-a'))
					->where($db->quoteName('client_id') . ' = 0');
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->select($db->quoteName('id'))
					->from($db->quoteName('#__emundus_widgets'))
					->where($db->quoteName('name') . ' LIKE ' . $db->quote('faq'))
					->orWhere($db->quoteName('label') . ' LIKE ' . $db->quote('FAQ'));
				$db->setQuery($query);
				$faq_widget_id = $db->loadColumn();

				if(!empty($faq_widget_id))
				{
					$query->clear()
						->delete($db->quoteName('#__emundus_widgets_repeat_access'))
						->where($db->quoteName('parent_id') . ' IN (' . implode(',',$faq_widget_id) . ')');
					$db->setQuery($query);
					$db->execute();

					$query->clear()
						->delete($db->quoteName('#__emundus_setup_dashboard'));
					$db->setQuery($query);
					$db->execute();
				}
			}

			if (version_compare($cache_version, '1.37.2', '<=') || $firstrun) {
				EmundusHelperUpdate::updateComponentParameter('com_users', 'minimum_length', 12);
				EmundusHelperUpdate::updateComponentParameter('com_users', 'minimum_integers', 1);
				EmundusHelperUpdate::updateComponentParameter('com_users', 'minimum_symbols', 1);
				EmundusHelperUpdate::updateComponentParameter('com_users', 'minimum_uppercase', 1);
				EmundusHelperUpdate::updateComponentParameter('com_users', 'minimum_lowercase', 1);
				EmundusHelperUpdate::updateComponentParameter('com_users', 'reset_count', 5);
				EmundusHelperUpdate::updateComponentParameter('com_users', 'reset_time', 1);
			}

            if (version_compare($cache_version, '1.37.3', '<=') || $firstrun) {
                $old_values = [
                    'fr-FR' => "<div>J'accepte <a href=\"fr/politique-de-confidentialite-des-donnees\" target=\"_blank\"> <i> la politique de confidentialité des données </i></a><i data-isicon=\"true\" class=\"icon-star small \"></i></div>",
                    'en-GB' => "<div> I accept <a href=\"en/politique-de-confidentialite-des-donnees\" target=\"_blank\"><i>the terms and conditions </i></a><i data-isicon=\"true\" class=\"icon-star small \"></i></div>",
                ];
                $new_values = [
                    'fr-FR' => "Je consens à l'exploitation de mes données personnelles afin de créer mon compte utilisateur.",
                    'en-GB' => 'I hereby give my consent to the processing of my personal data to create my user account.',
                ];
                EmundusHelperUpdate::updateOverrideTag('ACCEPT_THE_TERMS', $old_values, $new_values);

                $eMConfig            = JComponentHelper::getParams('com_emundus');
                $all_rights_group_id = $eMConfig->get('all_rights_group', 1);

                $query->clear()
                    ->select('id')
                    ->from($db->quoteName('#__emundus_groups'))
                    ->where($db->quoteName('user_id') . ' = 62')
                    ->where($db->quoteName('group_id') . ' = ' . $db->quote($all_rights_group_id));
                $db->setQuery($query);
                $group = $db->loadResult();

                if(empty($group)){
                    $query->clear()
                        ->insert($db->quoteName('#__emundus_groups'))
                        ->columns($db->quoteName('user_id') . ',' . $db->quoteName('group_id'))
                        ->values('62,' . $db->quote($all_rights_group_id));
                    $db->setQuery($query);
                    $db->execute();
                }
            }

            if (version_compare($cache_version, '1.37.7', '<=') || $firstrun) {
                $query->clear()
                    ->select('value')
                    ->from('#__emundus_setup_config')
                    ->where('namekey = ' . $db->quote('onboarding_lists'));

                $db->setQuery($query);
                $onboarding_lists = $db->loadResult();
                $onboarding_lists = json_decode($onboarding_lists, true);

                if (!empty($onboarding_lists)) {
                    $something_to_update = false;

                    foreach ($onboarding_lists as $l_key => $list) {
                        if ($l_key === 'emails') {
                            foreach($list['tabs'] as $t_key => $tab) {
                                if($tab['getter'] === 'getallemail') {
                                    if ($tab['filters'][0]['key'] !== 'category') {
                                        $tab['filters'][0]['key'] = 'category';
                                        $onboarding_lists[$l_key]['tabs'][$t_key] = $tab;
                                        $something_to_update = true;
                                    }
                                }
                            }
                        }
                    }

                    if ($something_to_update) {
                        $query->clear()
                            ->update('#__emundus_setup_config')
                            ->set('value = ' . $db->quote(json_encode($onboarding_lists)))
                            ->where('namekey = ' . $db->quote('onboarding_lists'));

                        $db->setQuery($query);
                        $db->execute();
                    }
                }

				$query->clear()
					->select('id,params')
					->from($db->quoteName('#__fabrik_elements'))
					->where($db->quoteName('plugin') . ' LIKE ' . $db->quote('textarea'));
				$db->setQuery($query);
				$textarea_elts = $db->loadObjectList();

				foreach ($textarea_elts as $textarea_elt) {
					$params = json_decode($textarea_elt->params, true);

					if($params['bootstrap_class'] == 'input-medium') {
						$params['bootstrap_class'] = 'input-xlarge';

						$query->clear()
							->update($db->quoteName('#__fabrik_elements'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $db->quote($textarea_elt->id));
						$db->setQuery($query);
						$db->execute();
					}
				}

				// Add exception rules in .htaccess file for session cookie security
				$file = JPATH_ROOT . '/.htaccess';
				$insertLines = "# Tchooz session cookie security" . PHP_EOL .
			"php_value session.cookie_secure On" . PHP_EOL .
			"php_value session.cookie_samesite Strict" . PHP_EOL;
				$succeed['add_htaccess_exception'] = EmundusHelperUpdate::insertIntoFile($file, $insertLines);
            }

			if (version_compare($cache_version, '1.37.9', '<=') || $firstrun) {
				EmundusHelperUpdate::installExtension('plg_extension_emundus', 'emundus', '{"name":"plg_extension_emundus","type":"plugin","creationDate":"November 2023","author":"J\u00e9r\u00e9my LEGENDRE","copyright":"(C) 2010 Open Source Matters, Inc.","authorEmail":"jeremy.legendre@emundus.fr","authorUrl":"www.emundus.fr","version":"1.0.0","description":"PLG_EXTENSION_EMUNDUS_XML_DESCRIPTION","group":"","filename":"emundus"}', 'plugin', 1, 'extension', '{}');
				EmundusHelperUpdate::enableEmundusPlugins('emundus','extension');

				// Update colors
                $dashboard_files_associated_by_status_params = array(
                    'eval' => 'php|$db = JFactory::getDbo();
$query = $db->getQuery(true);

$user_id = JFactory::getUser()->id;

try {
    $query->select(\'*\')
        ->from($db->quoteName(\'jos_emundus_setup_status\'))
        ->order(\'ordering\');
    $db->setQuery($query);
    $status = $db->loadObjectList();

    $datas = [];

    foreach ($status as $statu) {
        $file = new stdClass;
        $file->label = $statu->value;

        $styles_files = JPATH_SITE . \'/templates/g5_helium/custom/config/default/styles.yaml\';
		$yaml = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($styles_files));

		$file->color = $yaml[\'accent\'][$statu->class];

        $query->clear()
            ->select(\'distinct eua.fnum as files\')
            ->from($db->quoteName(\'#__emundus_users_assoc\',\'eua\'))
            ->leftJoin($db->quoteName(\'#__emundus_campaign_candidature\',\'cc\').\' ON \'.$db->quoteName(\'cc.fnum\').\' = \'.$db->quoteName(\'eua.fnum\'))
                    ->where($db->quoteName(\'cc.status\').\' = \'.$db->quote($statu->step))
                    ->andWhere($db->quoteName(\'cc.published\').\' = \'.$db->quote(1))
                    ->andWhere($db->quoteName(\'eua.user_id\').\' = \'.$db->quote($user_id));

        $db->setQuery($query);
        $files_user_assoc = $db->loadColumn();

        $query->clear()
            ->select(\'distinct ega.fnum as files\')
            ->from($db->quoteName(\'#__emundus_group_assoc\',\'ega\'))
            ->leftJoin($db->quoteName(\'#__emundus_campaign_candidature\',\'cc\').\' ON \'.$db->quoteName(\'cc.fnum\').\' = \'.$db->quoteName(\'ega.fnum\'))
            ->leftJoin($db->quoteName(\'#__emundus_groups\',\'eg\').\' ON \'.$db->quoteName(\'eg.group_id\').\' = \'.$db->quoteName(\'ega.group_id\'))
            ->where($db->quoteName(\'cc.status\').\' = \'.$db->quote($statu->step))
            ->andWhere($db->quoteName(\'cc.published\').\' = \'.$db->quote(1))
            ->andWhere($db->quoteName(\'eg.user_id\').\' = \'.$db->quote($user_id));

        $db->setQuery($query);
        $files_group_assoc = $db->loadColumn();

        $query->clear()
            ->select(\'distinct cc.fnum as files\')
            ->from($db->quoteName(\'#__emundus_groups\',\'eg\'))
            ->leftJoin($db->quoteName(\'#__emundus_setup_groups_repeat_course\',\'esgrc\').\' ON \'.$db->quoteName(\'esgrc.parent_id\').\' = \'.$db->quoteName(\'eg.group_id\'))
            ->leftJoin($db->quoteName(\'#__emundus_setup_campaigns\', \'esc\').\' ON \'.$db->quoteName(\'esc.training\').\' = \'.$db->quoteName(\'esgrc.course\'))
            ->leftJoin($db->quoteName(\'#__emundus_campaign_candidature\',\'cc\').\' ON \'.$db->quoteName(\'cc.campaign_id\').\' = \'.$db->quoteName(\'esc.id\'))
            ->where($db->quoteName(\'cc.status\').\' = \'.$db->quote($statu->step))
            ->andWhere($db->quoteName(\'cc.published\').\' = \'.$db->quote(1))
            ->andWhere($db->quoteName(\'eg.user_id\').\' = \'.$db->quote($user_id));

        $db->setQuery($query);
        $files_group_programs = $db->loadColumn();

        $file->value = sizeof(array_unique(array_merge($files_user_assoc,$files_group_assoc,$files_group_programs)));
        $datas[] = $file;
    }

            $dataSource = new stdClass;
            $dataSource->chart = new stdClass;
            $dataSource->chart = array(
                \'caption\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_ASSOCIATED_BY_STATUS_CAPTION"),
                \'xaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_STATUS"),
                \'yaxisname\'=> JText::_("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_NUMBER"),
                \'animation\' => 1,
                \'numberScaleValue\' => "1",
                \'numDivLines\' => 1,
                \'numbersuffix\'=> "",
                \'theme\'=> "fusion"
            );
            $dataSource->data = $datas;
            return $dataSource;
        } catch (Exception $e) {
        return array(\'dataset\' => \'\');
    }'
                );
                EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_FILES_ASSOCIATED_BY_STATUS', $dashboard_files_associated_by_status_params);
            }

			if (version_compare($cache_version, '1.38.0', '<=') || $firstrun) {
				EmundusHelperUpdate::updateYamlVariable('error-4677', 'Error', JPATH_ROOT . '/templates/g5_helium/custom/config/_error/index.yaml', 'error');
				$full_layout_error = "version: 2
preset:
  image: 'gantry-admin://images/layouts/3-col.png'
  name: _joomla_-_gantry4
  timestamp: 1530009501
layout:
  drawer: {  }
  top: {  }
  navigation: {  }
  showcase: {  }
  feature: {  }
  utility: {  }
  breadcrumb: {  }
  maintop: {  }
  /container-main/:
    -
      -
        'main-mainbody 75':
          -
            - error-4677
      -
        'sidebar 25': {  }
  mainbottom: {  }
  extension: {  }
  bottom: {  }
  footer: {  }
  copyright: {  }
  offcanvas: {  }
structure:
  drawer:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  top:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  navigation:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  showcase:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  feature:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  utility:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  breadcrumb:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  maintop:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  main-mainbody:
    title: Mainbody
    attributes:
      class: ''
  sidebar:
    type: section
    subtype: aside
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
    block:
      fixed: 1
  container-main:
    attributes:
      boxed: ''
  mainbottom:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  extension:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  bottom:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  footer:
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  copyright:
    type: section
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
  offcanvas:
    inherit:
      outline: default
      include:
        - attributes
        - block
        - children
";
				EmundusHelperUpdate::updateYamlVariable('', '', JPATH_ROOT . '/templates/g5_helium/custom/config/_error/layout.yaml', '', $full_layout_error);

				EmundusHelperUpdate::addYamlVariable('email-history', 'url("/media/com_emundus/images/tchoozy/objects-illustrations/email-history.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('wide-background', 'url("/media/com_emundus/images/tchoozy/backgrounds/wide-background.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('demonstration', 'url("/media/com_emundus/images/tchoozy/complex-illustrations/demonstration.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('corner-bottom-left-background', 'block")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('corner-top-right-background', 'block")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('candidate-button', 'url("/media/com_emundus/images/tchoozy/complex-illustrations/candidate-button.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('digital-testing', 'url("/media/com_emundus/images/tchoozy/complex-illustrations/digital-testing.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('corner-bottom-right-background', 'url("/media/com_emundus/images/tchoozy/backgrounds/corner-bottom-right-background.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('setting-tools', 'url("/media/com_emundus/images/tchoozy/complex-illustrations/setting-tools.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('groups', 'url("/media/com_emundus/images/tchoozy/objects-illustrations/groups.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('profiles', 'block', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);
				EmundusHelperUpdate::addYamlVariable('hiding', 'url("/media/com_emundus/images/tchoozy/complex-illustrations/hiding.svg")', JPATH_ROOT . '/templates/g5_helium/custom/config/default/styles.yaml', 'tchoozy', false, true, false);

				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature', 'copy_users_assoc', 'INT');
				$datas = array(
					'name' => 'copy_users_assoc',
					'group_id' => 254,
					'plugin' => 'radiobutton',
					'label' => 'COPY_USERS_ASSOC'
				);
				$params = array(
					'sub_options' => array(
						'sub_values' => array(0,1),
						'sub_labels' => array('JNO','JYES'),
						'sub_initial_selection' => array('0')
					)
				);
				EmundusHelperUpdate::addFabrikElement($datas,$params);
				EmundusHelperUpdate::insertTranslationsTag('COPY_USERS_ASSOC', 'Copier les utilisateurs associés', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COPY_USERS_ASSOC', 'Copy associated users', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature', 'copy_groups_assoc', 'INT');
				$datas = array(
					'name' => 'copy_groups_assoc',
					'group_id' => 254,
					'plugin' => 'radiobutton',
					'label' => 'COPY_GROUPS_ASSOC',
				);
				$params = array(
					'sub_options' => array(
						'sub_values' => array(0,1),
						'sub_labels' => array('JNO','JYES'),
						'sub_initial_selection' => array('0'),
					)
				);
				EmundusHelperUpdate::addFabrikElement($datas,$params);
				EmundusHelperUpdate::insertTranslationsTag('COPY_GROUPS_ASSOC', 'Copier les groupes associés', 'override', null, 'fabrik_elements', 'label');
				EmundusHelperUpdate::insertTranslationsTag('COPY_GROUPS_ASSOC', 'Copy associated groups', 'override', null, 'fabrik_elements', 'label', 'en-GB');

				EmundusHelperUpdate::installExtension('plg_fabrik_element_insee', 'insee', '{"name":"plg_fabrik_element_insee","type":"plugin","creationDate":"August 2023","author":"eMundus","copyright":"Copyright (C) 2005-2023 eMundus - All rights reserved.","authorEmail":"dev@emundus.fr","authorUrl":"www.emundus.fr","version":"3.10","description":"PLG_ELEMENT_INSEE_DESCRIPTION","group":"","filename":"insee"}', 'plugin', 1, 'fabrik_element');

				//Remove some colors from fabrik element
				$query->clear()
					->select('id,params')
					->from($db->quoteName('#__fabrik_elements'))
					->where($db->quoteName('name') . ' LIKE ' . $db->quote('class'))
					->where($db->quoteName('plugin') . ' LIKE ' . $db->quote('dropdown'))
					->where($db->quoteName('group_id') . ' IN (112,139)');
				$db->setQuery($query);
				$class_elts = $db->loadObjectList();

				foreach ($class_elts as $class_elt) {
					if (!empty($class_elt)) {
						$params           = json_decode($class_elt->params, true);
						$colors_to_remove = ['label-lightblue', 'label-lightyellow', 'label-yellow', 'label-darkyellow', 'label-lightgreen', 'label-darkgreen', 'label-lightgreen', 'label-darkgreen', 'label-lightorange', 'label-darkorange', 'label-lightred', 'label-darkred', 'label-lightpurple', 'label-darkpurple'];

						if (!empty($params['sub_options'])) {
							foreach ($colors_to_remove as $color_to_remove) {
								$index = array_search($color_to_remove, $params['sub_options']['sub_values']);
								if ($index !== false) {
									unset($params['sub_options']['sub_values'][$index]);
									unset($params['sub_options']['sub_labels'][$index]);
								}
							}

							$params['sub_options']['sub_values'] = array_values($params['sub_options']['sub_values']);
							$params['sub_options']['sub_labels'] = array_values($params['sub_options']['sub_labels']);

							if (!in_array('label-pink', $params['sub_options']['sub_values'])) {
								$params['sub_options']['sub_values'][] = 'label-pink';
								$params['sub_options']['sub_labels'][] = 'Pink';
							}

							if (!in_array('label-pink', $params['sub_options']['sub_values'])) {
								$params['sub_options']['sub_values'][] = 'label-pink';
								$params['sub_options']['sub_labels'][] = 'Pink';
							}
						}

						$colors_to_remove = array_map((function ($value) use ($db) {
							return $db->quote($value);
						}), $colors_to_remove);
						$query->clear()
							->update($db->quoteName('#__emundus_setup_profiles'))
							->set($db->quoteName('class') . ' = ' . $db->quote('label-default'))
							->where($db->quoteName('class') . 'IN (' . implode(',', $colors_to_remove) . ')');
						$db->setQuery($query);
						$db->execute();

						$query->clear()
							->update($db->quoteName('#__fabrik_elements'))
							->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
							->where($db->quoteName('id') . ' = ' . $db->quote($class_elt->id));
						$db->setQuery($query);
						$db->execute();
					}
				}

				$query->clear()
					->update($db->quoteName('#__menu'))
					->set($db->quoteName('title') . ' = ' . $db->quote('Emails envoyés'))
					->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_emundus&view=application&format=raw&layout=mail'))
					->where($db->quoteName('menutype') . ' LIKE ' . $db->quote('application'));
				$db->setQuery($query);
				$db->execute();

				EmundusHelperUpdate::addColumn('jos_emundus_setup_attachments', 'max_filesize', 'DOUBLE(6,2)');

				EmundusHelperUpdate::installExtension('plg_fabrik_element_emundus_geolocalisation', 'emundus_geolocalisation', '{"name":"plg_fabrik_element_emundus_geolocalisation","type":"plugin","creationDate":"September 2023","author":"eMundus - LEGENDRE J\u00e9r\u00e9my","copyright":"Copyright (C) 2005-2023 Media A-Team, Inc. - All rights reserved.","authorEmail":"dev@emundus.io","authorUrl":"www.emundus.fr","version":"3.10","description":"PLG_ELEMENT_FIELD_DESCRIPTION","group":"","filename":"emundus_geolocalisation"}', 'plugin', 1, 'fabrik_element');
				EmundusHelperUpdate::enableEmundusPlugins('emundus_geolocalisation', 'fabrik_element');

				$query->clear()
					->update($db->quoteName('#__fabrik_forms'))
					->set($db->quoteName('view_only_template') . ' = ' . $db->quote('emundus'));
				$db->setQuery($query);
				$db->execute();

				EmundusHelperUpdate::addCustomEvents([
					['label' => 'onAfterSubmitFile', 'category' => 'File']
				]);

				$query->clear()
					->select('id,params')
					->from($db->quoteName('#__fabrik_forms'))
					->where($db->quoteName('label') . ' LIKE ' . $db->quote('SETUP_GROUPS'));
				$db->setQuery($query);
				$setup_groups_form = $db->loadObject();

				if(!empty($setup_groups_form->id)) {
					$params = json_decode($setup_groups_form->params, true);

					$params['plugin_events'][0] = 'both';

					$query->clear()
						->update($db->quoteName('#__fabrik_forms'))
						->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
						->where($db->quoteName('id') . ' = ' . $db->quote($setup_groups_form->id));
					$db->setQuery($query);
					$db->execute();
				}

				$query->clear()
					->select('id,params')
					->from($db->quoteName('#__fabrik_elements'))
					->where($db->quoteName('name') . ' LIKE ' . $db->quote('copy_tag'))
					->where($db->quoteName('group_id') . ' = 254');
				$db->setQuery($query);
				$copy_tag_elt = $db->loadObject();

				if(!empty($copy_tag_elt->id)) {
					$params = json_decode($copy_tag_elt->params, true);

					$params['sub_options']['sub_initial_selection'] = ["0"];

					$query->clear()
						->update($db->quoteName('#__fabrik_elements'))
						->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
						->where($db->quoteName('id') . ' = ' . $db->quote($copy_tag_elt->id));
					$db->setQuery($query);
					$db->execute();
				}

				$query->clear()
					->select('id,params')
					->from($db->quoteName('#__fabrik_elements'))
					->where($db->quoteName('name') . ' LIKE ' . $db->quote('date_time'))
					->where($db->quoteName('group_id') . ' = 111');
				$db->setQuery($query);
				$date_history_emails = $db->loadObject();

				if(!empty($date_history_emails->id)) {
					$params = json_decode($date_history_emails->params, true);

					$params['date_store_as_local'] = 0;
					$params['date_table_format'] = "d\/m\/Y H:i";

					$query->clear()
						->update($db->quoteName('#__fabrik_elements'))
						->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
						->where($db->quoteName('id') . ' = ' . $db->quote($date_history_emails->id));
					$db->setQuery($query);
					$db->execute();
				}

				$query->clear()
					->update($db->quoteName('#__fabrik_elements'))
					->set($db->quoteName('filter_type') . ' = ' . $db->quote('field'))
					->where($db->quoteName('name') . ' LIKE ' . $db->quote('subject'))
					->where($db->quoteName('group_id') . ' = 111');
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__fabrik_lists'))
					->set($db->quoteName('filter_action') . ' = ' . $db->quote('submitform'))
					->where($db->quoteName('label') . ' LIKE ' . $db->quote('TABLE_SETUP_EMAIL_HISTORY'));
				$db->setQuery($query);
				$db->execute();

				EmundusHelperUpdate::insertTranslationsTag('ACCOUNT_FORM', 'Espace profil');
				EmundusHelperUpdate::insertTranslationsTag('ACCOUNT_FORM', 'Profile area', 'override', null, null, null, 'en-GB');

				$query->clear()
					->select('form_id')
					->from($db->quoteName('#__emundus_setup_formlist'))
					->where($db->quoteName('type') . ' LIKE ' . $db->quote('profile'));
				$db->setQuery($query);
				$profile_form_id = $db->loadResult();

				if(!empty($profile_form_id)) {
					$query->clear()
						->select('params')
						->from($db->quoteName('#__fabrik_forms'))
						->where($db->quoteName('id') . ' = ' . $db->quote($profile_form_id));
					$db->setQuery($query);
					$profile_form_params = $db->loadResult();

					$profile_form_params = json_decode($profile_form_params);
					$profile_form_params->submit_button_label = 'SAVE_CONTINUE';
					$profile_form_params->goback_button_label = 'GO_BACK';

					$query->clear()
						->update($db->quoteName('#__fabrik_forms'))
						->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($profile_form_params)))
						->where($db->quoteName('id') . ' = ' . $db->quote($profile_form_id));
					$db->setQuery($query);
					$db->execute();
				}

				$query->clear()
					->update($db->quoteName('#__emundus_setup_profiles'))
					->set($db->quoteName('class') . ' = ' . $db->quote('label-green-1'))
					->where($db->quoteName('menutype') . ' LIKE ' . $db->quote('coordinatormenu'));
				$db->setQuery($query);
				$db->execute();

				$query->clear()
					->update($db->quoteName('#__fabrik_forms'))
					->set($db->quoteName('params') . ' = JSON_REPLACE(' . $db->quoteName('params') . ', ' . $db->quote('$.tiplocation') . ', ' . $db->quote('above') . ')')
					->where('JSON_EXTRACT(' . $db->quoteName('params') . ', ' . $db->quote('$.tiplocation') . ') = ' . $db->quote('tip'));
				$db->setQuery($query);
				$db->execute();

                // Remove pin and unpin actions from campaign list
                $query->clear()
                    ->select('value')
                    ->from($db->quoteName('#__emundus_setup_config'))
                    ->where($db->quoteName('namekey') . ' LIKE ' . $db->quote('onboarding_lists'));
                $db->setQuery($query);
                $list_config = $db->loadResult();

                if (!empty($list_config)) {
                    $changed = false;
                    $list_config = json_decode($list_config, true);

                    if (!empty($list_config['campaigns'])) {
                        foreach($list_config['campaigns']['tabs'] as $tab_key => $tab) {
                            foreach($tab['actions'] as $action_key => $action) {
                                if ($action['action'] == 'pincampaign' || $action['action'] == 'unpincampaign') {
                                    unset($tab['actions'][$action_key]);
                                    $list_config['campaigns']['tabs'][$tab_key]['actions'] = array_values($tab['actions']);
                                    $changed = true;
                                }
                            }
                        }
                    }

                    if ($changed) {
                        $query->clear()
                            ->update($db->quoteName('#__emundus_setup_config'))
                            ->set($db->quoteName('value') . ' = ' . $db->quote(json_encode($list_config)))
                            ->where($db->quoteName('namekey') . ' LIKE ' . $db->quote('onboarding_lists'));
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
			}
		}

		return $succeed;
	}


	/**
	 * @param $type
	 * @param $parent
	 *
	 *
	 * @since version 1.33.0
	 */
	public function preflight($type, $parent)
	{
		if (version_compare(PHP_VERSION, '7.4.0', '<'))
		{
			echo "\033[31mThis extension works with PHP 7.4.0 or newer. Please contact your web hosting provider to update your PHP version. \033[0m\n";
			exit;
		}

		if ($this->schema_version != '3.10.9-2022-10-05-em')
		{
			echo "\033[31mYou have to run update-db.sh before CLI ! \033[0m\n";
			exit;
		}

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

		if (version_compare(PHP_VERSION, '8.0.0', '>='))
		{
			$query->clear()
				->update('#__extensions')
				->set($db->quoteName('enabled') . ' = 0')
				->where($db->quoteName('name') . ' LIKE ' . $db->quote('%dpcalendar%'));
			$db->setQuery($query);
			$db->execute();
		}

        // Check all rights group parameter
        $query->clear()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__emundus_setup_groups'))
            ->order($db->quoteName('id'));
        $db->setQuery($query);
        $all_rights_group = $db->loadResult();

        if(!empty($all_rights_group))
        {
            EmundusHelperUpdate::updateComponentParameter('com_emundus', 'all_rights_group', $all_rights_group);
        }
	}


	/**
	 * @param $type
	 * @param $parent
	 *
	 *
	 * @since version 1.33.0
	 */
	function postflight($type, $parent)
	{
		// Update jos_extensions informations
		$config = JFactory::getConfig();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('custom_data')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' LIKE ' . $db->quote('com_emundus'));
		$db->setQuery($query);
		$custom_data = $db->loadResult();

		if (!empty($custom_data))
		{
			$custom_data = json_decode($custom_data, true);

			$custom_data['sitename'] = $config->get('sitename');
		}
		else
		{
			$custom_data = [
				'sitename' => $config->get('sitename'),
			];
		}

		$query->clear()
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('custom_data') . ' = ' . $db->quote(json_encode($custom_data)))
			->where($db->quoteName('element') . ' LIKE ' . $db->quote('com_emundus'));
		$db->setQuery($query);


		if (!$db->execute())
		{
			return false;
		}

		// Insert new translations in overrides files
		EmundusHelperUpdate::languageBaseToFile();

		// Recompile Gantry5 css at each update
		EmundusHelperUpdate::recompileGantry5();

		// Clear Joomla Cache
		EmundusHelperUpdate::clearJoomlaCache();

		// Clear dashboard of emundus accounts
		$query->clear()
			->delete($db->quoteName('#__emundus_setup_dashboard'))
			->where($db->quoteName('user') . ' IN (62,95)');
		$db->setQuery($query);
		$db->execute();

		EmundusHelperUpdate::checkHealth();

		EmundusHelperUpdate::checkPageClass();

		if(file_exists(JPATH_SITE.'/.git') && file_exists(JPATH_SITE . '/administrator/components/com_emundus/scripts/pre-commit'))
		{
			copy(JPATH_SITE . '/administrator/components/com_emundus/scripts/pre-commit', JPATH_SITE.'/.git/hooks/pre-commit');
			chmod(JPATH_SITE.'/.git/hooks/pre-commit', 0755);

			echo ' - Git pre-commit hook installed' . PHP_EOL;
		}

		// if payment is activated, remove cookie samesite line in .htaccess file, else add it
		$eMConfig = JComponentHelper::getParams('com_emundus');
		$payment_activated = $eMConfig->get('application_fee');

		EmundusHelperUpdate::removeFromFile(JPATH_ROOT . '/.htaccess', ['php_value session.cookie_samesite Strict']);
		if (!$payment_activated) {
			EmundusHelperUpdate::insertIntoFile(JPATH_ROOT . '/.htaccess', "php_value session.cookie_samesite Lax" . PHP_EOL);
		}

		return true;
	}


	/**
	 * Delete old SQL files named ...-em
	 *
	 * @since version 1.33.0
	 */
	private function deleteOldSqlFiles()
	{
		$source = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql';
		if ($files = scandir($source))
		{
			foreach ($files as $file)
			{
				if (strpos($file, 'em') !== false and is_file($file)) JFile::delete($file);
			}
		}
		else
		{
			echo("Can't scan SQL Files");
		}
	}
}
