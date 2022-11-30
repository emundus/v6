<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_CONFIGURATION . '/configuration.php';


class com_emundusInstallerScript
{
    protected $manifest_cache;
    protected $schema_version;

    public function __construct() {
        // Get component manifest cache
        $db = JFactory::getDBO();
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

        require_once (JPATH_ADMINISTRATOR . '/components/com_emundus/helpers/update.php');
        $cache_version = $this->manifest_cache->version;

        # Check first run
        $firstrun = false;
        $regex = '/^6\.[0-9]*/m';
        preg_match_all($regex, $cache_version, $matches, PREG_SET_ORDER, 0);
        if (!empty($matches)) {
            $cache_version = (string) $parent->manifest->version;
            $firstrun = true;
        }

        if ($this->manifest_cache) {
            # First run condition
            if (version_compare($cache_version, '1.33.0', '<') || $firstrun) {
                # Delete emundus sql files in con_admin
                #$this->deleteOldSqlFiles();

                # Update SCP params
                EmundusHelperUpdate::updateSCPParams('pro_plugin', array('email_active','email_on_admin_login'), array('0','0'));

                EmundusHelperUpdate::genericUpdateParams('#__modules', 'module', 'mod_emundusflow', array('show_programme'), array('0'));
                EmundusHelperUpdate::genericUpdateParams('#__fabrik_cron', 'plugin', 'emundusrecall', array('log', 'log_email', 'cron_rungate') , array('0', 'mail@emundus.fr', '1'));

                EmundusHelperUpdate::updateConfigurationFile('lifetime', '45');

                # Insert translations in override file
                EmundusHelperUpdate::insertTranslationsTag('CREATE_A_NEW_FILE','Créer un nouveau dossier pour un utilisateur existant','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('CREATE_A_NEW_FILE','Create a new folder for an existing user','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SEND_CREDENTIALS_BY_EMAIL','Envoyer un email d\'information lors de l\'importation','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SEND_CREDENTIALS_BY_EMAIL','Send an information email when importing','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('COM_USERS_LOGIN_NO_ACCOUNT','Pas encore de compte ?','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('COM_USERS_LOGIN_NO_ACCOUNT','No account yet?','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('COM_USERS_SUBMIT_RESET','Réinitialiser mon mot de passe','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('COM_USERS_SUBMIT_RESET','Reset my password','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('PROGRAMME_LOGO','Logo du programme','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('PROGRAMME_LOGO','Programme logo','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('PROGRAMME','Programme','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('PROGRAMME','Programme','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('IS_LIMITED','Limiter les candidatures','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('IS_LIMITED','Limiting applications','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_DOCUMENTS','Copier les documents','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_DOCUMENTS','Copy attachments','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_DELETE_FROM_FILE',"Supprimer le dossier d'origine après la copie",'override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_DELETE_FROM_FILE','Delete the original file after copying','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_MOVE_HIKASHOP','Déplacer les commandes Hikashop','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_APPLICATION_MOVE_HIKASHOP','Move Hikashop commands','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_TAG','Copier les étiquettes','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('EMUNDUS_COPY_TAG','Copy the tags','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('LIMIT','Limite','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('LIMIT','Limit','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('LIMIT_STATUS','Statut à limiter','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('LIMIT_STATUS','Status to limit','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('TIMEZONE','Timezone : ','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('TIMEZONE','Timezone : ','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('LOGOUT','Logout','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('FORMATION','Formation','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('FORMATION','Training','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('CREATE_NEW_MENU','Créer un nouveau menu','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('CREATE_NEW_MENU','Create new menu','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('VIDEO_MAX_LENGTH','Taille max de la vidéo','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('VIDEO_MAX_LENGTH','Video max length','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ATTACHMENTS_MIN_PDF','Nombre minimum de pages dans le pdf','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ATTACHMENTS_MIN_PDF','Minimum page number in pdf','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ATTACHMENTS_MAX_PDF','Nombre de page(s) maximum pour le pdf','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('COM_EMUNDUS_ATTACHMENTS_MAX_PDF','Maximum number of page(s) for the pdf','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('TABLE_SETUP_LETTERS_INTRO','Générer automatiquement des courriers personnalisés pour vos candidats. Vous pouvez y inclure des champs dynamiques appelés «balises», qui seront remplacés par les données renseignées par vos candidats.','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('TABLE_SETUP_LETTERS_INTRO','Automatically generate personalised mailings for your candidates. You can include dynamic fields called "tags", which will be replaced by the data filled in by your candidates.','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CAMPAIGNS','Pour quelle(s) campagnes(s) souhaitez-vous que ce courrier soit généré ?','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CAMPAIGNS','For which campaign(s) would you like this mail to be generated?','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_PROGRAMS','Pour quel(s) programme(s) souhaitez-vous que ce courrier soit généré ?','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_PROGRAMS','For which programme(s) do you want this mail to be generated?','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_STATUS','Pour quel(s) statut(s) souhaitez-vous que ce courrier soit généré ?','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_STATUS','For which status(es) do you want this mail to be generated?','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_INTRO',"<p style='text-align: right;'><a href='https://emundus.atlassian.net/wiki/external/1668448315/YmRiNGUyMjI3ODQ4NDZjZWIxNDRiMWQ4ZDIwODQwNGI?atlOrigin=eyJpIjoiMjZiMTk4ZWVhMDk3NDExN2JhOWNkYTk4YjFiZmQ2MzgiLCJwIjoiYyJ9' target='_blank' rel='noopener noreferrer'>Besoin d'aide ?</a></p>
                ",'override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_INTRO',"<p style='text-align: right;'><a href='https://emundus.atlassian.net/wiki/external/1668448315/YmRiNGUyMjI3ODQ4NDZjZWIxNDRiMWQ4ZDIwODQwNGI?atlOrigin=eyJpIjoiMjZiMTk4ZWVhMDk3NDExN2JhOWNkYTk4YjFiZmQ2MzgiLCJwIjoiYyJ9' target='_blank' rel='noopener noreferrer'>Need help ?</a></p>
                ",'override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_DOCUMENT_TYPE_HINT',"Votre modèle de courrier doit être rattaché à un type de document pour être visible dans vos dossiers. Si vous ne trouvez pas votre type de document, vous pouvez en <a href='administration-site/types-documents/form/34'>créer un nouveau</a>.",'override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_DOCUMENT_TYPE_HINT',"Your mail template must be attached to a document type to be visible in your folders. If you cannot find your document type, you can <a href='administration-site/types-documents/form/34'>create a new one</a>.",'override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CAMPAIGNS_HINT','Ce courrier ne sera disponible que lorsque le statut du candidat fera partie de ceux que vous avez sélectionné.','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CAMPAIGNS_HINT',"This mail will only be available when the applicant's status is one of those you have selected.",'override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_PROGRAMS_HINT','Ce courrier ne sera disponible que pour les candidats inscrits dans le(s) programme(s) sélectionné(s).','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_PROGRAMS_HINT','This mail will only be available to applicants registered in the selected programme(s).','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_STATUS_HINT','Pour quel(s) statut(s) souhaitez-vous que ce courrier soit généré ?','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_STATUS_HINT','For which status(es) do you want this mail to be generated?','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CREATE_A_TEMPLATE_FROM_HINT','Créez votre modèle directement depuis votre plateforme ou depuis un logiciel externe.','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_CREATE_A_TEMPLATE_FROM_HINT','Create your template directly from your platform or from external software.','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_179','Contenu du courrier','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_179','Content of the letter','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_179_INTRO',"<p>Pour rendre ce courrier dynamique, insérer des <a href='component/emundus/?view=export_select_columns&format=html&layout=all_programs' target='_blank' rel='noopener noreferrer'>balises</a> dans sa construction afin d’ajouter des informations personnalisées pour chaque candidat. Par exemple, la balise ".'<em>$APPLICANT_NAME</em>'." sera remplacée par le nom de votre candidat. Bonjour ".'<em>$APPLICANT_NAME</em>'.' deviendra Bonjour Julien.</p>','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_179_INTRO',"<p>To make this mail dynamic, insert <a href='component/emundus/?view=export_select_columns&format=html&layout=all_programs' target='_blank' rel='noopener noreferrer'>tags</a> in its construction in order to add personalised information for each candidate. For example, the tag ".'<em>$APPLICANT_NAME</em>'." will be replaced by the name of your candidate. Hello ".'<em>$APPLICANT_NAME</em>'." will become Hello Julien.</p>",'override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('LETTER_TYPE','Type de lettre','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('LETTER_TYPE','Type of letter','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('LETTER_EXPORT_TO_PDF','Générer en pdf','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('LETTER_EXPORT_TO_PDF','Generate in pdf','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_185','Contenu du courrier','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_185','Content of the letter','override',null,'fabrik_elements','label','en-GB');

                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_185_INTRO',"<p>Pour rendre ce courrier dynamique, insérer des <a href='component/emundus/?view=export_select_columns&format=html&layout=all_programs' target='_blank' rel='noopener noreferrer'>balises</a> dans sa construction afin d’ajouter des informations personnalisées pour chaque candidat. Par exemple, la balise ".'<em>$APPLICANT_NAME</em>'." sera remplacée par le nom de votre candidat. Bonjour ".'<em>$APPLICANT_NAME</em>'.' deviendra Bonjour Julien.</p>','override',null,'fabrik_elements','label');
                EmundusHelperUpdate::insertTranslationsTag('SETUP_LETTERS_GROUP_185_INTRO',"<p>To make this mail dynamic, insert <a href='component/emundus/?view=export_select_columns&format=html&layout=all_programs' target='_blank' rel='noopener noreferrer'>tags</a> in its construction in order to add personalised information for each candidate. For example, the tag ".'<em>$APPLICANT_NAME</em>'." will be replaced by the name of your candidate. Hello ".'<em>$APPLICANT_NAME</em>'." will become Hello Julien.</p>",'override',null,'fabrik_elements','label','en-GB');

                $succeed['campaign_workflow'] = EmundusHelperUpdate::updateCampaignWorkflowTable();
                $succeed['event_handlers'] = EmundusHelperUpdate::convertEventHandlers();

                EmundusHelperUpdate::addYamlVariable('location','/media/com_emundus/js/fabrik.js',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','javascript',true,true);
                EmundusHelperUpdate::addYamlVariable('inline','',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','javascript');
                EmundusHelperUpdate::addYamlVariable('in_footer','0',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','javascript');
                EmundusHelperUpdate::addYamlVariable('extra','{  }',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','javascript');
                EmundusHelperUpdate::addYamlVariable('priority','0',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','javascript');
                EmundusHelperUpdate::addYamlVariable('name','Fabrik',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','javascript');

                EmundusHelperUpdate::addYamlVariable('location','https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','css',true,true);
                EmundusHelperUpdate::addYamlVariable('inline','',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','css');
                EmundusHelperUpdate::addYamlVariable('extra','{  }',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','css');
                EmundusHelperUpdate::addYamlVariable('priority','0',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','css');
                EmundusHelperUpdate::addYamlVariable('name','Material Icons',JPATH_ROOT . '/templates/g5_helium/custom/config/default/page/assets.yaml','css');

                EmundusHelperUpdate::updateFont('family=Inter:300,400,500,600,700,800,900,400&subset=latin,vietnamese,latin-ext');

                $datas = [
                    'menutype' => 'usermenu',
                    'title' => 'Informations de compte',
                    'alias' => 'informations-de-compte',
                    'path' => 'informations-de-compte',
                    'link' => 'index.php?option=com_users&view=profile&layout=edit',
                    'type' => 'component',
                    'component_id' => 25,
                    'params' => [
                        'menu_show' => 0
                    ]
                ];
                EmundusHelperUpdate::addJoomlaMenu($datas);
            }

            if((version_compare($cache_version, '1.33.28', '<') || $firstrun)) {
                EmundusHelperUpdate::installExtension('PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE','custom_event_handler','{"name":"PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE","type":"plugin","creationDate":"18 August 2021","author":"James Dean","copyright":"(C) 2010-2019 EMUNDUS SOFTWARE. All rights reserved.","authorEmail":"james@emundus.fr","authorUrl":"https:\/\/www.emundus.fr","version":"1.22.1","description":"PLG_EMUNDUS_CUSTOM_EVENT_HANDLER_TITLE_DESC","group":"","filename":"custom_event_handler"}','plugin', 1, 'emundus');
            }

            if((version_compare($cache_version, '1.33.32', '<') || $firstrun)) {
                EmundusHelperUpdate::disableEmundusPlugins('emundus_su');
            }

            if (version_compare($cache_version, '1.34.0', '<') || $firstrun) {
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);

                EmundusHelperUpdate::addColumn('jos_emundus_setup_campaigns','pinned','TINYINT',1);
                EmundusHelperUpdate::addColumn('jos_emundus_setup_programmes','color','VARCHAR',10);

                EmundusHelperUpdate::genericUpdateParams('#__modules', 'module', 'mod_falang', array('advanced_dropdown','full_name'), array('0','0'));

                // Add back button to login, register and reset view
                $datas = [
                    'title' => 'eMundus - Back button',
                    'note' => 'Back button available on login and register views',
                    'content' => '<p><a class="em-back-button em-pointer" href="/"><span class="material-icons em-mr-4">navigate_before</span>Retour à la page d\'accueil</a></p>',
                    'position' => 'header-a',
                    'module' => 'mod_custom',
                    'access' => 9,
                    'params' => [
                        'prepare_content' => 0,
                        'backgroundimage' => '',
                        'layout' => '_:default',
                        'moduleclass_sfx' => '',
                        'cache' => 1,
                        'cache_time' => 900,
                        'cachemode' => 'static',
                    ]
                ];
                $moduleid = EmundusHelperUpdate::addJoomlaModule($datas);
                if(!empty($moduleid)) {
                    $query->clear()
                        ->select('id')
                        ->from($db->quoteName('#__menu'))
                        ->where($db->quoteName('link') . ' IN (' . $db->quote('index.php?option=com_users&view=login').',' . $db->quote('index.php?option=com_fabrik&view=form&formid=307') . ',' . $db->quote('index.php?option=com_users&view=reset') . ')');
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

                        if(!$is_existing){
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

                EmundusHelperUpdate::insertTranslationsTag('HIKA_BILLING_DESCRIPTION','Afin de poursuivre, vous devrez régler les frais de dossier liés à l’inscription');
                EmundusHelperUpdate::insertTranslationsTag('HIKA_BILLING_DESCRIPTION','In order to continue, you will need to pay the registration fee.','override',null,null,null,'en-GB');
                EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_PAYMENT_METHOD_SENTENCE','Vous souhaitez payer par');
                EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_PAYMENT_METHOD_SENTENCE','You wish to pay by','override',null,null,null,'en-GB');
                EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_NEW_BILLING_ADDRESS','Adresse de facturation');
                EmundusHelperUpdate::insertTranslationsTag('MAKE_THIS_ADDRESS_THE_DEFAULT_BILLING_ADDRESS','Enregistrer cette adresse');
                EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_CONFIRM_MY_ADDRESS','Valider mon adresse');
                EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_CONFIRM_MY_ADDRESS','Validate my address','override',null,null,null,'en-GB');
                EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_COUPON_TITLE','Code de réduction');
                EmundusHelperUpdate::insertTranslationsTag('HIKASHOP_COUPON_TITLE','Discount code','override',null,null,null,'en-GB');
                EmundusHelperUpdate::insertTranslationsTag('CHECKOUT_BUTTON_FINISH','Procéder au paiement');
                EmundusHelperUpdate::insertTranslationsTag('CHECKOUT_BUTTON_FINISH','Process to payment','override',null,null,null,'en-GB');
                EmundusHelperUpdate::insertTranslationsTag('HIKA_NEW','Ajouter une adresse');
                EmundusHelperUpdate::insertTranslationsTag('HIKA_NEW','Add an address','override',null,null,null,'en-GB');
                //

                $succeed['campaign_workflow'] = EmundusHelperUpdate::addProgramToCampaignWorkflow();

                // Install announcement module
                //TODO : Install a module or a plugin via folder (parse xml file and insert necessary datas)
                EmundusHelperUpdate::installExtension('MOD_EMUNDUS_ANNOUNCEMENTS_SYS_XML','mod_emundus_announcements','{"name":"MOD_EMUNDUS_ANNOUNCEMENTS_SYS_XML","type":"module","creationDate":"September 2022","author":"eMundus","copyright":"Copyright (C) 2022 eMundus. All rights reserved.","authorEmail":"dev@emundus.fr","authorUrl":"www.emundus.fr","version":"1.0.0","description":"MOD_EMUNDUS_ANNOUNCEMENTS_XML_DESCRIPTION","group":"","filename":"mod_emundus_announcements"}','module');
                EmundusHelperUpdate::createModule('Announcement','top-b','mod_emundus_announcements','{"announcement_content":"Cette plateforme de préproduction est une copie de la production datant du [DATE]. Les mails sont désactivés. Elle est isolée du web.","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,1);

                $datas = [
                    'title' => 'Announcement',
                    'note' => 'Back button available on login and register views',
                    'position' => 'top-b',
                    'module' => 'mod_emundus_announcements',
                    'params' => [
                        'announcement_content' => 'Cette plateforme de préproduction est une copie de la production datant du [DATE]. Les mails sont désactivés. Elle est isolée du web.'
                    ]
                ];
                EmundusHelperUpdate::addJoomlaModule($datas,0,true);
                //

                // Install smart search menu
                $query->clear()
                    ->select('extension_id')
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('element') . ' LIKE ' . $db->quote('com_finder'));
                $db->setQuery($query);
                $ext_id = $db->loadResult();

                $datas = [
                    'menutype' => 'main',
                    'title' => 'COM_FINDER',
                    'alias' => 'com-finder',
                    'path' => 'com-finder',
                    'link' => 'index.php?option=com_finder',
                    'type' => 'component',
                    'component_id' => $ext_id,
                    'params' => [],
                    'client_id' => 1,
                    'img' => 'class:finder'
                ];
                EmundusHelperUpdate::addJoomlaMenu($datas);
                //

                EmundusHelperUpdate::installExtension('Smart Search - eMundus','emundus','{"name":"Smart Search - eMundus","type":"plugin","creationDate":"November 2022","author":"HUBINET Brice","copyright":"Copyright (C) 2016 eMundus. All rights reserved.","authorEmail":"dev@emundus.fr","authorUrl":"www.emundus.fr","version":"","description":"This plugin indexes applications in eMundus extension.","group":"","filename":"emundus"}','plugin',1,'finder');
                $datas = [
                    'title' => 'Spotlight eMundus',
                    'note' => 'Advanced search based on Joomla indexing',
                    'position' => 'drawer',
                    'module' => 'mod_finder',
                    'access' => 7,
                    'params' => [
                        'searchfilter' => '',
                        'show_autosuggest' => 0,
                        'show_advanced' => 0,
                        'field_size' => 25,
                        'show_label' => 1,
                        'label_pos' => 'left',
                        'alt_label' => '',
                        'show_button' => 0,
                        'button_pos' => 'left',
                        'opensearch' => 1,
                        'opensearch_title' => '',
                        'set_itemid' => 0,
                        'layout' => '_:tchooz',
                    ]
                ];
                EmundusHelperUpdate::addJoomlaModule($datas,1,true);

                $succeed['hikashop_events_added'] = EmundusHelperUpdate::addCustomEvents([['label' => 'onHikashopBeforeOrderCreate', 'category' => 'Hikashop'],
                    ['label' => 'onHikashopAfterOrderCreate', 'category' => 'Hikashop'],
                    ['label' => 'onHikashopBeforeOrderUpdate', 'category' => 'Hikashop'],
                    ['label' => 'onHikashopAfterOrderUpdate', 'category' => 'Hikashop'],
                    ['label' => 'onHikashopAfterOrderConfirm', 'category' => 'Hikashop'],
                    ['label' => 'onHikashopAfterOrderDelete', 'category' => 'Hikashop'],
                    ['label' => 'onHikashopCheckoutWorkflowLoad', 'category' => 'Hikashop'],
                    ['label' => 'onHikashopBeforeProductListingLoad', 'category' => 'Hikashop']
                ]);
            }

            // Insert new translations in overrides files
            $succeed['language_base_to_file'] = EmundusHelperUpdate::languageBaseToFile();


            // Recompile Gantry5 css at each update
            $dir = JPATH_BASE . '/templates/g5_helium/custom/css-compiled';
            if(!empty($dir)) {
                foreach (glob($dir . '/*') as $file) {
                    unlink($file);
                }

                rmdir($dir);
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
        if(version_compare(PHP_VERSION, '7.2.0', '<')) {
            echo "\033[31mThis extension works with PHP 7.2.0 or newer.Please contact your web hosting provider to update your PHP version. \033[0m\n";
            exit;
        }

        if($this->schema_version != '3.10.9-2022-10-05-em') {
            echo "\033[31mYou have to run update-db.sh before CLI ! \033[0m\n";
            exit;
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
        echo "\rComposant eMundus mis à jour avec succès !\n";
    }


    /**
     * Delete old SQL files named ...-em
     *
     * @since version 1.33.0
     */
    private function deleteOldSqlFiles() {
        $source = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/mysql';
        if ($files = scandir($source)) {
            foreach ($files as $file) {
                if (strpos($file, 'em') !== false AND is_file($file)) JFile::delete($file);
            }
        } else {
            echo("Can't scan SQL Files");
        }
    }
}
