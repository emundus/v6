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

        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');
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
		if (!empty($matches)) {
			$cache_version = (string) $parent->manifest->version;
			$firstrun      = true;
		}

		if ($this->manifest_cache) {
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			# First run condition
			if (version_compare($cache_version, '1.33.0', '<') || $firstrun) {
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

			if ((version_compare($cache_version, '1.33.28', '<') || $firstrun)) {
				EmundusHelperUpdate::installExtension('PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE', 'custom_event_handler', '{"name":"PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE","type":"plugin","creationDate":"18 August 2021","author":"James Dean","copyright":"(C) 2010-2019 EMUNDUS SOFTWARE. All rights reserved.","authorEmail":"james@emundus.fr","authorUrl":"https:\/\/www.emundus.fr","version":"1.22.1","description":"PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE_DESC","group":"","filename":"custom_event_handler"}', 'plugin', 1, 'emundus');
			}

			if ((version_compare($cache_version, '1.33.32', '<') || $firstrun)) {
				EmundusHelperUpdate::disableEmundusPlugins('emundus_su');
			}

			if (version_compare($cache_version, '1.34.0', '<') || $firstrun) {
				EmundusHelperUpdate::addColumn('jos_emundus_setup_campaigns', 'pinned', 'TINYINT', 1);
				EmundusHelperUpdate::addColumn('jos_emundus_setup_campaigns', 'eval_start_date', 'DATETIME');
				EmundusHelperUpdate::addColumn('jos_emundus_setup_programmes', 'color', 'VARCHAR', 10);

				EmundusHelperUpdate::genericUpdateParams('#__modules', 'module', 'mod_falang', array('advanced_dropdown', 'full_name'), array('0', '0'));

				// Add back button to login, register and reset view
				$back_module = EmundusHelperUpdate::getModule(0, 'eMundus - Back button');
				if (!empty($back_module) && !empty($back_module['id'])) {
					$moduleid = $back_module['id'];
				}
				else {
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

				if (!empty($moduleid)) {
					$query->clear()
						->select('id')
						->from($db->quoteName('#__menu'))
						->where($db->quoteName('link') . ' IN (' . $db->quote('index.php?option=com_users&view=login') . ',' . $db->quote('index.php?option=com_fabrik&view=form&formid=307') . ',' . $db->quote('index.php?option=com_users&view=reset') . ')');
					$db->setQuery($query);
					$menus = $db->loadColumn();

					foreach ($menus as $menu) {
						$query->clear()
							->select('moduleid')
							->from($db->quoteName('#__modules_menu'))
							->where($db->quoteName('menuid') . ' = ' . $db->quote($menu))
							->andWhere($db->quoteName('moduleid') . ' = ' . $db->quote($moduleid['id']));
						$db->setQuery($query);
						$is_existing = $db->loadResult();

						if (!$is_existing) {
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

				if (!empty($campaign_elements)) {
					foreach ($campaign_elements as $campaign_element) {
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

			if (version_compare($cache_version, '1.34.4', '<') || $firstrun) {
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_MISSING_MANDATORY_FILE_UPLOAD', 'Veuillez remplir le champ obligatoire %s du formulaire %s');
				EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_MISSING_MANDATORY_FILE_UPLOAD', 'Please fill the mandatory field %s of form %s', 'override', null, null, null, 'en-GB');
			}

			if (version_compare($cache_version, '1.34.10', '<') || $firstrun) {
				EmundusHelperUpdate::insertTranslationsTag('APPLICATION_CREATION_DATE', 'Dossier crée le');
				EmundusHelperUpdate::insertTranslationsTag('APPLICATION_CREATION_DATE', 'File created on', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('CAMPAIGN_ID', 'Campagne');
				EmundusHelperUpdate::insertTranslationsTag('CAMPAIGN_ID', 'Campaign', 'override', null, null, null, 'en-GB');

				EmundusHelperUpdate::insertTranslationsTag('SEND_ON', 'Envoyé le');
				EmundusHelperUpdate::insertTranslationsTag('SEND_ON', 'Send on', 'override', null, null, null, 'en-GB');
			}

			if (version_compare($cache_version, '1.34.33', '<') || $firstrun) {
				EmundusHelperUpdate::addColumn('jos_emundus_uploads', 'local_filename', 'VARCHAR', 255);
			}

			if (version_compare($cache_version, '1.34.36', '<') || $firstrun) {
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('id,params')
					->from($db->quoteName('#__fabrik_forms'))
					->where("JSON_EXTRACT(params,'$.curl_code') LIKE '%media\/com_emundus\/lib\/chosen\/chosen.min.css%'");
				$db->setQuery($query);
				$forms_to_update = $db->loadObjectList();

				foreach ($forms_to_update as $form) {
					$params = json_decode($form->params);
					if (isset($params->curl_code)) {
						foreach ($params->curl_code as $key => $code) {
							if (strpos($code, 'media/com_emundus/lib/chosen/chosen.min.css') !== false) {
								if (is_object($params->curl_code)) {
									$params->curl_code->{$key} = str_replace('media/com_emundus/lib/chosen/chosen.min.css', 'media/jui/css/chosen.css', $params->curl_code->{$key});
								}
								elseif (is_array($params->curl_code)) {
									$params->curl_code[$key] = str_replace('media/com_emundus/lib/chosen/chosen.min.css', 'media/jui/css/chosen.css', $params->curl_code[$key]);
								}
							}
							if (strpos($code, 'media/com_emundus/lib/chosen/chosen.jquery.min.js') !== false) {
								if (is_object($params->curl_code)) {
									$params->curl_code->{$key} = str_replace('media/com_emundus/lib/chosen/chosen.jquery.min.js', 'media/jui/js/chosen.jquery.min.js', $params->curl_code->{$key});
								}
								elseif (is_array($params->curl_code)) {
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

			if (version_compare($cache_version, '1.34.49', '<') || $firstrun) {
				EmundusHelperUpdate::addCustomEvents([
					['label' => 'onHikashopAfterCheckoutStep', 'category' => 'Hikashop', 'published' => 1],
					['label' => 'onHikashopAfterCartProductsLoad', 'category' => 'Hikashop', 'published' => 1],
					['label' => 'onBeforeRenderApplications', 'category' => 'Applicant', 'published' => 1]
				]);
			}

			if (version_compare($cache_version, '1.34.56', '<') || $firstrun) {
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('id')
					->from($db->quoteName('#__modules'))
					->where($db->quoteName('module') . ' LIKE ' . $db->quote('mod_emundus_version'))
					->where($db->quoteName('client_id') . ' = 0');
				$db->setQuery($query);
				$moduleid = $db->loadResult();

				if (!empty($moduleid)) {
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

			if (version_compare($cache_version, '1.34.64', '<') || $firstrun) {
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

				if (!empty($password_inputs)) {
					foreach ($password_inputs as $password) {
						$password_jsaction['element_id'] = $password;
						$query->clear()
							->select($db->quoteName('id'))
							->from($db->quoteName('#__fabrik_jsactions'))
							->where($db->quoteName('element_id') . ' = ' . $db->quote($password_jsaction['element_id']))
							->andWhere($db->quoteName('action') . ' LIKE ' . $db->quote($password_jsaction['action']))
							->andWhere($db->quoteName('code') . ' LIKE ' . $db->quote('%Invalid password%'));
						$db->setQuery($query);
						$password_onchange = $db->loadResult();

						if (!empty($password_onchange)) {
							$password_jsaction['action_id'] = $password_onchange;
							EmundusHelperUpdate::updateJsAction($password_jsaction);
						}
						else {
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

			if (version_compare($cache_version, '1.35.0', '<=') || $firstrun) {
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

				EmundusHelperUpdate::updateEmundusParam('gotenberg_url', 'https://gotenberg.microservices.tchooz.app', 'https://docs.emundus.app');

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

				foreach ($modules as $module) {
					$params = json_decode($module->params);

					if (isset($params->layout) && $params->layout == '_:default') {
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

						foreach ($module_translations as $module_translation) {
							$translation_params = json_decode($module_translation->value);

							if (isset($translation_params->layout) && $translation_params->layout == '_:default') {
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

				if (!empty($fabrik_extension)) {
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

				EmundusHelperUpdate::updateEmundusParam('export_application_pdf_title_color', '#000000', '#ee1c25');

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

				if (!empty($fabrik_lists)) {
					foreach ($fabrik_lists as $list) {
						$params                    = json_decode($list->params, true);
						$params['advanced-filter'] = "0";

						if ($params['show-table-filters'] != "0") {
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

			if (version_compare($cache_version, '1.35.5', '<=') || $firstrun) {
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);

				$query->select('params')
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('element') . ' LIKE ' . $db->quote('emunduswaitingroom'));
				$db->setQuery($query);
				$waiting_room_params = $db->loadResult();

				if (!empty($waiting_room_params)) {
					$strings_allowed_to_add = [
						'paybox_',
						'stripeconnect_',
						'notif_payment=monetico&ctrl=checkout&task=notify&option=com_hikashop&tmpl=component',
						'option=com_hikashop&ctrl=checkout&task=notify&notif_payment=payzen&tmpl=component',
					];
					$strings                = [];

					$params = json_decode($waiting_room_params, true);
					if (empty($params['strings_allowed'])) {
						$params['strings_allowed'] = [];
					}

					// We get values from the database.
					foreach ($params['strings_allowed'] as $string) {
						$strings[] = $string['string_allowed_text'];
					}

					foreach ($strings_allowed_to_add as $string_allowed_to_add) {
						if (!in_array($string_allowed_to_add, $strings)) {
							$strings[] = $string_allowed_to_add;
						}
					}

					$params['strings_allowed'] = [];
					foreach ($strings as $key => $string) {
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

            if (version_compare($cache_version, '1.35.9', '<=') || $firstrun) {
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

                EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS',$dashboard_files_by_status_params);
                EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_USERS_BY_MONTH',$dashboard_users_by_month_params);
                EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_FILES_ASSOCIATED_BY_STATUS',$dashboard_files_associated_by_status_params);
                EmundusHelperUpdate::updateWidget('COM_EMUNDUS_DASHBOARD_FILES_BY_TAG',$dashboard_files_by_tag_params);

                EmundusHelperUpdate::addColumnIndex('jos_messages', 'page');

                EmundusHelperUpdate::addCustomEvents([['label' => 'onAfterMoveApplication', 'category' => 'Campaign']]);
            }

			if (version_compare($cache_version, '1.36.0', '<') || $firstrun) {
				EmundusHelperUpdate::addCustomEvents([
					['label' => 'onBeforeEmundusRedirectToHikashopCart', 'category' => 'Hikashop'],
					['label' => 'onBeforeApplicantEnterApplication', 'category' => 'Files'],
					['label' => 'onAccessDenied', 'category' => 'Access']
				]);

				// Campaign candidature tabs
				$columns = [
					[
						'name' => 'name',
						'type' => 'VARCHAR',
						'length' => 255,
						'null' => 0,
					],
					[
						'name' => 'applicant_id',
						'type' => 'INT',
						'null' => 0,
					],
					[
						'name' => 'ordering',
						'type' => 'INT',
						'default' => 1,
						'null' => 1,
					]
				];
				$foreign_keys = [
					[
						'name' => 'jos_emundus_users_fk_applicant_id',
						'from_column' => 'applicant_id',
						'ref_table' => 'jos_emundus_users',
						'ref_column' => 'user_id',
						'update_cascade' => true,
						'delete_cascade' => true,
					]
				];
				EmundusHelperUpdate::createTable('jos_emundus_campaign_candidature_tabs',$columns,$foreign_keys,'Storage tab for filing');

				$columns = [
					[
						'name' => 'date_time',
						'type' => 'datetime',
						'null' => 1,
					],
					[
						'name' => 'fnum_from',
						'type' => 'VARCHAR',
						'length' => 255,
						'null' => 0,
					],
					[
						'name' => 'fnum_to',
						'type' => 'VARCHAR',
						'length' => 255,
						'null' => 0,
					],
					[
						'name' => 'published',
						'type' => 'TINYINT',
						'default' => 1,
						'null' => 0,
					]
				];
				$foreign_keys = [];
				EmundusHelperUpdate::createTable('jos_emundus_campaign_candidature_links',$columns,$foreign_keys,'Links between two fnums');

				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature','tab','INT',10);
				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature','name','VARCHAR',255);
				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature','updated','DATETIME');
				EmundusHelperUpdate::addColumn('jos_emundus_campaign_candidature','updated_by','INT',10);

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
                $group_id = $sql_result['group_id'];
                $form_id = $sql_result['form_id'];
                $list_id = $sql_result['list_id'];

				EmundusHelperUpdate::addColumn('jos_emundus_campaign_workflow','display_preliminary_documents','TINYINT(1)');
                $query->clear()
                    ->select('id')
                    ->from($db->quoteName('#__fabrik_elements'))
                    ->where('name = ' . $db->quote('display_preliminary_documents'))
                    ->andWhere('plugin = ' . $db->quote('yesno'));

                $db->setQuery($query);
                $element_id = $db->loadResult();

                if (empty($element_id)) {
                    if (!empty($group_id)) {
                        $values = ['display_preliminary_documents',$group_id,'yesno','Afficher les documents à télécharger ?', '0','0000-00-00 00:00:00','2023-03-29 07:47:05','62','sysadmin','0000-00-00 00:00:00','0','0','0','0','0','0','10','0',"",'1','1','0','0','0','1','0','0','{"yesno_default":"0","yesno_icon_yes":"","yesno_icon_no":"","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}'];
                        $query->clear()
                            ->insert($db->quoteName('#__fabrik_elements'))
                            ->columns(['name','group_id','plugin','label','checked_out','checked_out_time','created','created_by','created_by_alias','modified','modified_by','width','height','`default`','hidden','eval','ordering','show_in_list_summary','filter_type','filter_exact_match','published','link_to_detail','primary_key','auto_increment','access','use_in_page_title','parent_id','params'])
                            ->values(implode(',', $db->quote($values)));

                        $db->setQuery($query);
                        $inserted = $db->execute();

                        if ($inserted) {
                            $display_preliminary_documents_id = $db->insertid();
                            EmundusHelperUpdate::addJsAction([
                                'element_id' => $display_preliminary_documents_id,
                                'action' => 'load',
                                'code' => 'const value=this.get(&#039;value&#039;);const fab=this.form.elements;let {jos_emundus_campaign_workflow___specific_documents,jos_emundus_campaign_workflow_repeat_documents___id_0}=fab;if(value!=&#039;0&#039;){showFabrikElt(jos_emundus_campaign_workflow___specific_documents);}else{document.querySelector(&#039;#jos_emundus_campaign_workflow___specific_documents_input_0&#039;).click();hideFabrikElt(jos_emundus_campaign_workflow___specific_documents);  hideFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0, true);}'
                            ]);
                            EmundusHelperUpdate::addJsAction([
                                'element_id' => $display_preliminary_documents_id,
                                'action' => 'change',
                                'code' => 'const value=this.get(&#039;value&#039;);const fab=this.form.elements;let{jos_emundus_campaign_workflow___specific_documents,jos_emundus_campaign_workflow_repeat_documents___id_0}=fab;if(value!=&#039;0&#039;){showFabrikElt(jos_emundus_campaign_workflow___specific_documents)}else{document.querySelector(&#039;#jos_emundus_campaign_workflow___specific_documents_input_0&#039;).click();hideFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0);hideFabrikElt(jos_emundus_campaign_workflow___specific_documents,true)}'
                            ]);
                        }
                    }
                }

				EmundusHelperUpdate::addColumn('jos_emundus_campaign_workflow', 'specific_documents', 'TINYINT(1)');
                $query->clear()
                    ->select('id')
                    ->from($db->quoteName('#__fabrik_elements'))
                    ->where('name = ' . $db->quote('specific_documents'))
                    ->andWhere('plugin = ' . $db->quote('yesno'));

                $db->setQuery($query);
                $element_id = $db->loadResult();

                if (empty($element_id)) {
                    if (!empty($group_id)) {
                        $values = ['specific_documents',$group_id,'yesno','Afficher des documents  spécifique ?', '0','0000-00-00 00:00:00','2023-03-29 07:47:05','62','sysadmin','0000-00-00 00:00:00','0','0','0','0','0','0','10','0',"",'1','1','0','0','0','1','0','0','{"yesno_default":"0","yesno_icon_yes":"","yesno_icon_no":"","options_per_row":"4","toggle_others":"0","toggle_where":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","validations":[]}'];
                        $query->clear()
                            ->insert($db->quoteName('#__fabrik_elements'))
                            ->columns(['name','group_id','plugin','label','checked_out','checked_out_time','created','created_by','created_by_alias','modified','modified_by','width','height','`default`','hidden','eval','ordering','show_in_list_summary','filter_type','filter_exact_match','published','link_to_detail','primary_key','auto_increment','access','use_in_page_title','parent_id','params'])
                            ->values(implode(',', $db->quote($values)));

                        $db->setQuery($query);
                        $inserted = $db->execute();

                        if ($inserted) {
                            $specific_documents_id = $db->insertid();
                            EmundusHelperUpdate::addJsAction([
                                'element_id' => $specific_documents_id,
                                'action' => 'load',
                                'code' => 'const value=this.get(&#039;value&#039;);const fab=this.form.elements;let{jos_emundus_campaign_workflow_repeat_documents___id_0}=fab;if(value!= &#039;0&#039;){showFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0)}else{hideFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0, true)}'
                            ]);
                            EmundusHelperUpdate::addJsAction([
                                'element_id' => $specific_documents_id,
                                'action' => 'change',
                                'code' => 'const value=this.get(&#039;value&#039;);const fab=this.form.elements;let{jos_emundus_campaign_workflow_repeat_documents___id_0}=fab;if(value!= &#039;0&#039;){showFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0)}else{hideFabrikGroupByElt(jos_emundus_campaign_workflow_repeat_documents___id_0, true)}'
                            ]);
                        }
                    }
                }

				$result = EmundusHelperUpdate::createTable('jos_emundus_campaign_workflow_repeat_documents', [
					['name' => 'parent_id', 'type' => 'int'],
					['name' => 'href', 'type' => 'text'],
					['name' => 'title', 'type' => 'VARCHAR', 'length' => 255]
				]);
				if ($result['status']) {
					$sql = "create index fb_parent_fk_parent_id_INDEX on jos_emundus_campaign_workflow_repeat_documents (parent_id)";
					$db->setQuery($sql);
					$db->execute();

					$values = ['Documents à télécharger', '', 'Documents à télécharger', 1, '2023-04-19 08:36:17', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', 1, 0, '{"split_page":"0","list_view_and_query":"1","access":"1","intro":"","outro":"","repeat_group_button":1,"repeat_template":"repeatgroup","repeat_max":"","repeat_min":"","repeat_num_element":"","repeat_sortable":"0","repeat_order_element":"","repeat_error_message":"","repeat_no_data_message":"","repeat_intro":"","repeat_add_access":"1","repeat_delete_access":"1","repeat_delete_access_user":"","repeat_copy_element_values":"0","group_columns":"1","group_column_widths":"","repeat_group_show_first":"1","random":"0","labels_above":"-1","labels_above_details":"-1"}'];
					$query->clear()
						->insert($db->quoteName('#__fabrik_groups'))
						->columns(['name','css','label','published','created','created_by','created_by_alias','modified','modified_by','checked_out','checked_out_time','is_join','private','params'])
						->values(implode(',', $db->quote($values)));

					$db->setQuery($query);
					$inserted = $db->execute();

					if ($inserted) {
						$new_group_id = $db->insertid();

						$columns = array('name', 'group_id', 'plugin', 'label', 'checked_out', 'checked_out_time', 'created', 'created_by', 'created_by_alias', 'modified', 'modified_by', 'width', 'height', 'default', 'hidden', 'eval', 'ordering', 'show_in_list_summary', 'filter_type', 'filter_exact_match', 'published', 'link_to_detail', 'primary_key', 'auto_increment', 'access', 'use_in_page_title', 'parent_id', 'params');
						$element_values = [
							['id', $new_group_id, 'internalid', 'id', 0, '0000-00-00 00:00:00', '2023-04-19 08:41:52', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 3, 0, '', 1, 0, 1, 0, '', '', 1, 1, 1, 1, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}'],
							['parent_id', $new_group_id, 'field', 'parent_id', 0, '0000-00-00 00:00:00', '2023-04-19 08:41:52', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, 0, '', 1, 0, 2, 0, '', '', 1, 1, 0, 0, 1, 0, 0, '{"rollover":"","comment":"","sub_default_value":"","sub_default_label":"","element_before_label":1,"allow_frontend_addtocheckbox":0,"database_join_display_type":"dropdown","joinType":"simple","join_conn_id":-1,"date_table_format":"Y-m-d","date_form_format":"Y-m-d H:i:s","date_showtime":0,"date_time_format":"H:i","date_defaulttotoday":1,"date_firstday":0,"multiple":0,"allow_frontend_addtodropdown":0,"password":0,"maxlength":255,"text_format":"text","integer_length":6,"decimal_length":2,"guess_linktype":0,"disable":0,"readonly":0,"ul_max_file_size":16000,"ul_email_file":0,"ul_file_increment":0,"upload_allow_folderselect":1,"fu_fancy_upload":0,"upload_delete_image":1,"make_link":0,"fu_show_image_in_table":0,"image_library":"gd2","make_thumbnail":0,"imagepath":"\\/","selectImage_root_folder":"\\/","image_front_end_select":0,"show_image_in_table":0,"image_float":"none","link_target":"_self","radio_element_before_label":0,"options_per_row":4,"ck_options_per_row":4,"allow_frontend_addtoradio":0,"use_wysiwyg":0,"my_table_data":"id","update_on_edit":0,"view_access":1,"show_in_rss_feed":0,"show_label_in_rss_feed":0,"icon_folder":-1,"use_as_row_class":0,"filter_access":1,"full_words_only":0,"inc_in_adv_search":1,"sum_on":0,"sum_access":0,"avg_on":0,"avg_access":0,"median_on":0,"median_access":0,"count_on":0,"count_access":0}'],
							['href', $new_group_id, 'fileupload', 'Document', 0, '0000-00-00 00:00:00', '2023-04-19 08:39:57', 62, 'sysadmin', '2023-04-19 09:11:13', 62, 0, 0, '', 0, 0, 3, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"ul_max_file_size":"10240","ul_device_capture":"0","ul_file_types":"pdf,jpg,png,jpeg,docx","ul_directory":"\/images\/emundus\/phases\/{jos_emundus_campaign_workflow___id}","ul_email_file":"0","random_filename":"0","length_random_filename":"","ul_file_increment":"1","upload_allow_folderselect":"0","upload_delete_image":"1","upload_use_wip":"0","allow_unsafe":"0","fu_clean_filename":"1","fu_rename_file_code":"","default_image":"","make_link":"1","fu_show_image_in_table":"1","fu_show_image":"0","fu_show_image_in_email":"1","image_library":"gd2","fu_main_max_width":"","fu_main_max_height":"","image_quality":"90","fu_title_element":"","fu_map_element":"","restrict_lightbox":"1","make_thumbnail":"0","fu_make_pdf_thumb":"0","thumb_dir":"images\/stories\/thumbs","thumb_prefix":"","thumb_suffix":"","thumb_max_width":"200","thumb_max_height":"100","fileupload_crop":"0","fileupload_crop_dir":"images\/stories\/crop","fileupload_crop_width":"200","fileupload_crop_height":"100","win_width":"400","win_height":"400","fileupload_storage_type":"filesystemstorage","fileupload_aws_accesskey":"","fileupload_aws_secretkey":"","fileupload_aws_location":"","fileupload_ssl":"0","fileupload_aws_encrypt":"0","fileupload_aws_bucketname":"","fileupload_s3_serverpath":"1","fileupload_amazon_acl":"2","fileupload_skip_check":"0","fileupload_amazon_auth_url":"60","ajax_upload":"0","ajax_show_widget":"1","ajax_runtime":"html5,html4","ajax_max":"4","ajax_dropbox_width":"400","ajax_dropbox_height":"200","ajax_chunk_size":"0","fu_use_download_script":"0","fu_open_in_browser":"0","fu_force_download_script":"0","fu_download_acl":"","fu_download_noaccess_image":"","fu_download_noaccess_url":"","fu_download_access_image":"","fu_download_hit_counter":"","fu_download_log":"0","fu_download_append":"0","ul_export_encode_csv":"relative","ul_export_encode_json":"relative","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["1"],"must_validate":["0"],"show_icon":["1"]}}'],
							['title', $new_group_id, 'field', 'Nom du document', 0, '0000-00-00 00:00:00', '2023-04-19 09:11:42', 62, 'sysadmin', '0000-00-00 00:00:00', 0, 0, 0, '', 0, 0, 12, 0, '', 1, 1, 0, 0, 0, 1, 0, 0, '{"placeholder":"","password":"0","maxlength":"255","disable":"0","readonly":"0","autocomplete":"1","speech":"0","advanced_behavior":"0","bootstrap_class":"input-medium","text_format":"text","integer_length":"11","decimal_length":"2","field_use_number_format":"0","field_thousand_sep":",","field_decimal_sep":".","text_format_string":"","field_format_string_blank":"1","text_input_mask":"","text_input_mask_autoclear":"0","text_input_mask_definitions":"","render_as_qrcode":"0","scan_qrcode":"0","guess_linktype":"0","link_target_options":"default","rel":"","link_title":"","link_attributes":"","show_in_rss_feed":"0","show_label_in_rss_feed":"0","use_as_rss_enclosure":"0","rollover":"","tipseval":"0","tiplocation":"top-left","labelindetails":"0","labelinlist":"0","comment":"","edit_access":"1","edit_access_user":"","view_access":"1","view_access_user":"","list_view_access":"1","encrypt":"0","store_in_db":"1","default_on_copy":"0","can_order":"0","alt_list_heading":"","custom_link":"","custom_link_target":"","custom_link_indetails":"1","use_as_row_class":"0","include_in_list_query":"1","always_render":"0","icon_folder":"0","icon_hovertext":"1","icon_file":"","icon_subdir":"","filter_length":"20","filter_access":"1","full_words_only":"0","filter_required":"0","filter_build_method":"0","filter_groupby":"text","inc_in_adv_search":"1","filter_class":"input-medium","filter_responsive_class":"","tablecss_header_class":"","tablecss_header":"","tablecss_cell_class":"","tablecss_cell":"","sum_on":"0","sum_label":"Sum","sum_access":"1","sum_split":"","avg_on":"0","avg_label":"Average","avg_access":"1","avg_round":"0","avg_split":"","median_on":"0","median_label":"Median","median_access":"1","median_split":"","count_on":"0","count_label":"Count","count_condition":"","count_access":"1","count_split":"","custom_calc_on":"0","custom_calc_label":"Custom","custom_calc_query":"","custom_calc_access":"1","custom_calc_split":"","custom_calc_php":"","notempty-message":[""],"notempty-validation_condition":[""],"tip_text":[""],"icon":[""],"validations":{"plugin":["notempty"],"plugin_published":["1"],"validate_in":["both"],"validation_on":["both"],"validate_hidden":["1"],"must_validate":["0"],"show_icon":["1"]}}']
						];

						foreach($element_values as $values) {
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
						if (!empty($list_id)) {
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
				EmundusHelperUpdate::addColumn('jos_emundus_setup_attachment_profiles', 'sample_filepath', 255);

				// check if table jos_emundus_setup_config exists
				$str_query = 'SHOW TABLES LIKE ' . $db->quote('jos_emundus_setup_config');
				$db->setQuery($str_query);
				$table_exists = $db->loadResult();

				if (!$table_exists) {
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

					// insert default values
					$query->clear()
						->insert($db->quoteName('#__emundus_setup_config'))
						->columns($db->quoteName(['namekey', 'value', 'default']))
						->values($db->quote('onboarding_lists') . ', ' . $db->quote('{"forms":{"title":"COM_EMUNDUS_ONBOARD_FORMS","tabs":[{"title":"COM_EMUNDUS_FORM_MY_FORMS","key":"form","controller":"form","getter":"getallform","actions":[{"action":"duplicateform","label":"COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE","controller":"form","name":"duplicate"},{"action":"index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"},{"action":"createform","controller":"form","label":"COM_EMUNDUS_ONBOARD_ADD_FORM","name":"add"}],"filters":[]},{"title":"COM_EMUNDUS_FORM_MY_EVAL_FORMS","key":"form_evaluations","controller":"form","getter":"getallgrilleEval","actions":[{"action":"createformeval","label":"COM_EMUNDUS_ONBOARD_ADD_EVAL_FORM","controller":"form","name":"add"},{"action":"/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%id%&mode=eval","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"}],"filters":[]},{"title":"COM_EMUNDUS_FORM_PAGE_MODELS","key":"form_models","controller":"formbuilder","getter":"getallmodels","actions":[{"action":"deleteformmodelfromids","label":"COM_EMUNDUS_ACTIONS_DELETE","controller":"formbuilder","parameters":"&model_ids=%id%","name":"delete"},{"action":"/index.php?option=com_emundus&view=form&layout=formbuilder&prid=%form_id%&mode=models","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"form","type":"redirect","name":"edit"}],"filters":[]}]},"campaigns":{"title":"COM_EMUNDUS_ONBOARD_CAMPAIGNS","tabs":[{"title":"COM_EMUNDUS_ONBOARD_CAMPAIGNS","key":"campaign","controller":"campaign","getter":"getallcampaign","actions":[{"action":"index.php?option=com_emundus&view=campaigns&layout=add","label":"COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN","controller":"campaign","name":"add","type":"redirect"},{"action":"duplicatecampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_DUPLICATE","controller":"campaign","name":"duplicate"},{"action":"index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"campaign","type":"redirect","name":"edit"},{"action":"deletecampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_DELETE","controller":"campaign","name":"delete","showon":{"key":"nb_files","operator":"<","value":"1"}},{"action":"unpublishcampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH","controller":"campaign","name":"unpublish","showon":{"key":"published","operator":"=","value":"1"}},{"action":"publishcampaign","label":"COM_EMUNDUS_ONBOARD_ACTION_PUBLISH","controller":"campaign","name":"publish","showon":{"key":"published","operator":"=","value":"0"}}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_FILTER_ALL","getter":"","controller":"campaigns","key":"filter","values":[{"label":"COM_EMUNDUS_ONBOARD_FILTER_ALL","value":"all"},{"label":"COM_EMUNDUS_CAMPAIGN_YET_TO_COME","value":"yettocome"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_OPEN","value":"ongoing"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_CLOSE","value":"Terminated"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_PUBLISH","value":"Publish"},{"label":"COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH","value":"Unpublish"}],"default":"Publish"},{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAMS","getter":"getallprogramforfilter","controller":"programme","key":"program","values":null}]},{"title":"COM_EMUNDUS_ONBOARD_PROGRAMS","key":"programs","controller":"programme","getter":"getallprogram","actions":[{"action":"index.php?option=com_fabrik&view=form&formid=108","controller":"programme","label":"COM_EMUNDUS_ONBOARD_ADD_PROGRAM","name":"add","type":"redirect"},{"action":"index.php?option=com_fabrik&view=form&formid=108&rowid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"programme","type":"redirect","name":"edit"}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAM_CATEGORIES","getter":"getprogramcategories","controller":"programme","key":"recherche","values":null}]}]},"emails":{"title":"COM_EMUNDUS_ONBOARD_EMAILS","tabs":[{"controller":"email","getter":"getallemail","title":"COM_EMUNDUS_ONBOARD_EMAILS","key":"emails","actions":[{"action":"index.php?option=com_emundus&view=emails&layout=add","controller":"email","label":"COM_EMUNDUS_ONBOARD_ADD_EMAIL","name":"add","type":"redirect"},{"action":"index.php?option=com_emundus&view=emails&layout=add&eid=%id%","label":"COM_EMUNDUS_ONBOARD_MODIFY","controller":"email","type":"redirect","name":"edit"},{"action":"deleteemail","label":"COM_EMUNDUS_ACTIONS_DELETE","controller":"email","name":"delete","showon":{"key":"type","operator":"!=","value":"1"}},{"action":"preview","label":"COM_EMUNDUS_ONBOARD_VISUALIZE","controller":"email","name":"preview","icon":"preview","title":"subject","content":"message"}],"filters":[{"label":"COM_EMUNDUS_ONBOARD_ALL_PROGRAM_CATEGORIES","getter":"getemailcategories","controller":"email","key":"recherche","values":null}]}]}}') . ', ' . $db->quote(''));

					$db->setQuery($query);
					$db->execute();
				}
			}

			// Insert new translations in overrides files
			$succeed['language_base_to_file'] = EmundusHelperUpdate::languageBaseToFile();


			// Recompile Gantry5 css at each update
			$succeed['recompile_gantry_5'] = EmundusHelperUpdate::recompileGantry5();

			// Clear Joomla Cache
			$succeed['clear_joomla_cache'] = EmundusHelperUpdate::clearJoomlaCache();
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
		if (version_compare(PHP_VERSION, '7.2.0', '<')) {
			echo "\033[31mThis extension works with PHP 7.2.0 or newer.Please contact your web hosting provider to update your PHP version. \033[0m\n";
			exit;
		}

		if ($this->schema_version != '3.10.9-2022-10-05-em') {
			echo "\033[31mYou have to run update-db.sh before CLI ! \033[0m\n";
			exit;
		}

        if(version_compare(PHP_VERSION, '8.0.0', '>=')) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->clear()
                ->update('#__extensions')
                ->set($db->quoteName('enabled') . ' = 0')
                ->where($db->quoteName('name') . ' LIKE ' . $db->quote('%dpcalendar%'));
            $db->setQuery($query);
            $db->execute();
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
		$config = JFactory::getConfig();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('custom_data')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' LIKE ' . $db->quote('com_emundus'));
		$db->setQuery($query);
		$custom_data = $db->loadResult();

		if (!empty($custom_data)) {
			$custom_data = json_decode($custom_data, true);

			$custom_data['sitename'] = $config->get('sitename');
		}
		else {
			$custom_data = [
				'sitename' => $config->get('sitename'),
			];
		}

		$query->clear()
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('custom_data') . ' = ' . $db->quote(json_encode($custom_data)))
			->where($db->quoteName('element') . ' LIKE ' . $db->quote('com_emundus'));
		$db->setQuery($query);

		if ($db->execute()) {
			echo "Application name updated";

			return true;
		}
		else {
			echo "Application name not updated";

			return false;
		}
	}


	/**
	 * Delete old SQL files named ...-em
	 *
	 * @since version 1.33.0
	 */
	private function deleteOldSqlFiles()
	{
		$source = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql';
		if ($files = scandir($source)) {
			foreach ($files as $file) {
				if (strpos($file, 'em') !== false and is_file($file)) JFile::delete($file);
			}
		}
		else {
			echo("Can't scan SQL Files");
		}
	}
}
