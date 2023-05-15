<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once (JPATH_BASE . 'includes/defines.php' );
include_once (JPATH_BASE . 'includes/framework.php' );
include_once (__DIR__ . '/helpers/samples.php');
include_once (JPATH_SITE . '/components/com_emundus/models/formbuilder.php');
include_once (__DIR__ . '/../models/translations.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelFormbuilderTest extends TestCase
{
    private $m_formbuilder;
    private $m_translations;
    private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_formbuilder = new EmundusModelFormbuilder;
        $this->m_translations = new EmundusModelTranslations;
        $this->h_sample = new EmundusUnittestHelperSamples;
    }

    public function testFoo()
    {
        $foo = true;
        $this->assertSame(true, $foo);
    }

    public function testFormsTradElement()
    {
        $override_original_file_size = filesize(JPATH_SITE . '/language/overrides/fr-FR.override.ini');

        $new_trad = "Mon élément modifié";
        $reference_id = 999999;

        $this->m_translations->insertTranslation('ELEMENT_TEST', 'Mon élément de test', 'fr-FR', '', 'override', 'fabrik_elements', $reference_id);
        $new_key = $this->m_formbuilder->formsTrad('ELEMENT_TEST', ['fr' => $new_trad, 'en' => 'My test element'], $reference_id);

        $this->assertNotEmpty($new_key, 'La fonction de traduction a fonctionné');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('override')
            ->from('#__emundus_setup_languages')
            ->where('reference_id = ' . $reference_id);

        $db->setQuery($query);
        $override = $db->loadResult();

        $this->assertEquals($new_trad, $override, 'La nouvelle traduction de l\'élément est bien enregistrée en BDD.');

        $override_new_file_size = filesize(JPATH_SITE . '/language/overrides/fr-FR.override.ini');
        $this->assertGreaterThanOrEqual($override_original_file_size, $override_new_file_size, 'New override file size is greater or equal than original override file');

        $this->m_translations->deleteTranslation('ELEMENT_TEST', 'fr-FR', '', $reference_id);
    }

    public function testFormsTradUndefined()
    {
        $override_original_file_size = filesize(JPATH_SITE . '/language/overrides/fr-FR.override.ini');
        $new_trad = "Mon élément modifié";
        $reference_id = 999999;

        $this->m_translations->insertTranslation('ELEMENT_TEST', 'Mon élément de test', 'fr-FR', '', 'override', 'fabrik_elements', $reference_id);
        $new_key = $this->m_formbuilder->formsTrad('ELEMENT_TEST', ['fr' => $new_trad, 'en' => 'My test element']);

        $this->assertNotEmpty($new_key, 'La fonction formsTrad a fonctionné sans définir le type d\'élément passé.');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('override')
            ->from('#__emundus_setup_languages')
            ->where('reference_id = ' . $reference_id);

        $db->setQuery($query);
        $override = $db->loadResult();

        $this->assertEquals($new_trad, $override, 'La nouvelle traduction de l\'élément est bien enregistrée en BDD.');

        $override_new_file_size = filesize(JPATH_SITE . '/language/overrides/fr-FR.override.ini');
        $this->assertGreaterThanOrEqual($override_original_file_size, $override_new_file_size, 'New override file size is greater or equal than original override file');

        $this->m_translations->deleteTranslation('ELEMENT_TEST', 'fr-FR', '', $reference_id);
    }

    public function testCreateFabrikForm()
    {
        // Test 1 - Création de formulaire basique
        $prid = 9;
        $form_id = $this->h_sample->createSampleForm($prid);

        $this->assertGreaterThan(0, $form_id, 'le formulaire a bien été créé');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__fabrik_forms')
            ->where('id = ' . $form_id);

        $db->setQuery($query);

        $form = $db->loadObject();

        $this->assertSame($form->label, 'FORM_' . $prid . '_' . $form_id, 'Le label du formulaire est bien formaté.');
        $this->assertSame($form->intro,'<p>' . 'FORM_' . $prid . '_INTRO_' . $form_id . '</p>', "L'introduction du formulaire est bien formaté");
        $this->assertSame($form->published,'1', 'Le formulaire est bien publié à sa création');

        $deleted = $this->h_sample->deleteSampleForm($form_id);
        $this->assertTrue($deleted, 'Le formulaire de test a bien été supprimé');

        // Test 2 - S'assurer que les paramètres ne vont pas causer d'erreur, si vide ou de mauvais type

        $form_id = $this->h_sample->createSampleForm(0);
        $this->assertSame(0, $form_id);

        $form_id = $this->h_sample->createSampleForm($prid, 'label');
        $this->assertSame(0, $form_id);

        // Se tromper pour le champ introduction ne devrait pas causer d'erreur
        $form_id = $this->h_sample->createSampleForm($prid, ['fr' => 'Formulaire Tests unitaires', 'en' => 'form for unit tests'], 'label intro');
        $this->assertGreaterThan(0, $form_id);

        $deleted = $this->h_sample->deleteSampleForm($form_id);
        $this->assertTrue($deleted, 'Le formulaire de test a bien été supprimé');
    }

    public function testCreateGroup()
    {
        // Test 1 - Un groupe a besoin d'un formulaire pour fonctionner
        $group = $this->m_formbuilder->createGroup(['fr' => '', 'en' => ''], 0);
        $this->assertArrayNotHasKey('group_id', $group);

        $prid = 9;
        $form_id = $this->h_sample->createSampleForm($prid, ['fr' => 'Formulaire Tests unitaires', 'en' => 'form for unit tests']);
        $this->assertGreaterThan(0, $form_id);

        $group = $this->m_formbuilder->createGroup(['fr' => 'Groupe Tests unitaires', 'en' => 'Group Unit tests'] , $form_id);
        $this->assertIsArray($group);

        if (!empty($group['group_id'])) {
            $this->assertGreaterThan(0, $group['group_id'], 'Le groupe a bien été créé.');
            $this->m_formbuilder->updateGroupParams($group['group_id'], ['is_sample' => true]);

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('id')
                ->from('#__fabrik_formgroup')
                ->where('group_id = ' . $group['group_id'])
                ->andWhere('form_id = ' . $form_id);

            $db->setQuery($query);

            $row_id = $db->loadResult();

            $this->assertGreaterThan(0, $row_id, 'Le groupe et le formulaire sont bien liés');

            $deleted = $this->h_sample->deleteSampleGroup($group['group_id']);
            $this->assertTrue($deleted, 'Le groupe de test a bien été supprimé');

            $deleted = $this->h_sample->deleteSampleForm($form_id);
            $this->assertTrue($deleted, 'Le formulaire de test a bien été supprimé');
        }
    }

    public function testUpdateGroupParams() {
        $prid = 9;
        $form_id = $this->h_sample->createSampleForm($prid, ['fr' => 'Formulaire Tests unitaires', 'en' => 'form for unit tests'], 'label intro');
        $this->assertGreaterThan(0, $form_id);

        $group = $this->m_formbuilder->createGroup(['fr' => 'Groupe Tests unitaires', 'en' => 'Group Unit tests'] , $form_id);
        $this->assertIsArray($group);

        if (!empty($group['group_id'])) {
            $this->assertGreaterThan(0, $group['group_id'], 'Le groupe a bien été créé.');


            $new_intro = 'Mon introduction';
            $this->m_formbuilder->updateGroupParams($group['group_id'], ['intro' => $new_intro, 'is_sample' => true], 'fr');

            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('params')
                ->from('#__fabrik_groups')
                ->where('id = ' . $group['group_id']);

            $db->setQuery($query);

            $params = $db->loadResult();
            $this->assertNotEmpty($params);

            $params = json_decode($params, true);
            $this->assertTrue($params['is_sample'], 'Le groupe utilisé est bien un groupe de test');

            $this->assertNotEmpty($params['intro'], 'Mon introduction n\'est pas vide');
            $this->assertSame($params['intro'], 'FORM_' . $form_id . '_GROUP_' . $group['group_id'] . '_INTRO', 'Mon introduction a une balise de traduction bien formatée');
            $this->assertNotSame($new_intro, $params['intro'], 'Mon introduction n\'a pas été inséré en direct mais via une traduction.');


            $query->clear()
                ->select('override')
                ->from('#__emundus_setup_languages')
                ->where('tag = ' . $db->quote($params['intro']))
                ->andWhere('type = ' . $db->quote('override'))
                ->andWhere('lang_code = ' . $db->quote('fr-FR'));

            $db->setQuery($query);

            $translation = $db->loadResult();
            $this->assertSame($translation, $new_intro, 'La traduction de l\'introduction du groupe enregistrée est correcte.');

            $deleted = $this->h_sample->deleteSampleGroup($group['group_id']);
            $this->assertTrue($deleted, 'Le groupe de test a bien été supprimé');

            $deleted = $this->h_sample->deleteSampleForm($form_id);
            $this->assertTrue($deleted, 'Le formulaire de test a bien été supprimé');
        }
    }

    public function testDeleteFormModel()
    {
        $deleted = $this->m_formbuilder->deleteFormModel(0);
        $this->assertFalse($deleted);

        $deleted = $this->m_formbuilder->deleteFormModelFromIds(0);
        $this->assertFalse($deleted);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->insert('#__emundus_template_form')
            ->columns(['form_id', 'label'])
            ->values('99999, ' .  $db->quote('Modèle Test unitaire'));
        $db->setQuery($query);
        $inserted = false;

        try {
            $inserted = $db->execute();
        } catch (Exception $e) {
            JLog::add('Failed to insert model for unit tests ' . $e->getMessage(), JLog::ERROR, 'com_emundus.tests');
        }

        if ($inserted) {
            $deleted = $this->m_formbuilder->deleteFormModel(99999);
            $this->assertTrue($deleted);
        }
    }

    public function testCopyForm()
    {
        $new_form_id = $this->m_formbuilder->copyForm(0, 'Test Unitaire - ');
        $this->assertEquals(0, $new_form_id, 'Copy form returns 0 if no form id given');


        $new_form_id = $this->m_formbuilder->copyForm(9999999, 'Test Unitaire - ');
        $this->assertEquals(0, $new_form_id, 'Copy form returns 0 if no form does not exists');

        $new_form_id = $this->m_formbuilder->copyForm(102, 'Test Unitaire - ');
        $this->assertNotEmpty($new_form_id, 'La copie de formulaire fonctionne');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->clear()
            ->select('label')
            ->from('#__fabrik_forms')
            ->where('id = ' . $new_form_id);

        try {
            $db->setQuery($query);
            $label = $db->loadResult();

            $this->assertSame('FORM_MODEL_' .$new_form_id, $label, 'Le label d\'un formulaire copié est correct');
        } catch (Exception $e) {
            JLog::add('Failed to insert model for unit tests ' . $e->getMessage(), JLog::ERROR, 'com_emundus.tests');
        }
    }

	public function testgetDocumentSample() {
		$document = $this->m_formbuilder->getDocumentSample(0, 0);
		$this->assertEmpty($document, 'Le document de test est vide si aucun identifiant de document et/ou profile n\'est donné');

		$document = $this->m_formbuilder->getDocumentSample(9999999, 0);
		$this->assertEmpty($document, 'Le document de test est vide si aucun identifiant de profile n\'est donné');

		$document = $this->m_formbuilder->getDocumentSample(0, 9999999);
		$this->assertEmpty($document, 'Le document de test est vide si aucun identifiant de document n\'est donné');

		// Création d'un document de test
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->insert('#__emundus_setup_attachment_profiles')
			->columns(['profile_id', 'campaign_id', 'attachment_id', 'displayed', 'mandatory', 'ordering', 'published', 'bank_needed', 'duplicate', 'sample_filepath', 'has_sample'])
			->values('1, null, 1, 1, 1, 1, 1, null, 0, null, 0');
		$db->setQuery($query);
		$db->execute();

		$document = $this->m_formbuilder->getDocumentSample(1, 1);
		$this->assertNotEmpty($document, 'Le document de test est bien renvoyé');
	}

	public function testaddFormModel()
	{
		$created = $this->m_formbuilder->addFormModel(0, 'Test Unitaire - ');
		$this->assertFalse($created, 'addFormModel returns false if no form id given');

		$created = $this->m_formbuilder->addFormModel(9999999, 'Test Unitaire - ');
		$this->assertFalse($created, 'addFormModel returns false if no form does not exists');
	}
}
