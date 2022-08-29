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
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');
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

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_formbuilder = new EmundusModelFormbuilder;
        $this->m_translations = new EmundusModelTranslations;
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

    public function testUpdateGroupParams() {
        $h_sample = new EmundusUnittestHelperSamples();
        $data = $h_sample->createSampleGroup();

        $this->assertGreaterThan(0, $data['group_id'], 'Le groupe a bien été créé.');

        $new_intro = 'Mon introduction';
        $this->m_formbuilder->updateGroupParams($data['group_id'], ['intro' => $new_intro], 'fr');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('params')
            ->from('#__fabrik_groups')
            ->where('id = ' . $data['group_id']);

        $db->setQuery($query);

        $params = $db->loadResult();
        $this->assertNotEmpty($params);

        $params = json_decode($params, true);
        $this->assertTrue($params['is_sample'], 'Le groupe utilisé est bien un groupe de test');

        $this->assertNotEmpty($params['intro'], 'Mon introduction n\'est pas vide');
        $this->assertSame($params['intro'], 'FORM_' . $data['form_id'] . '_GROUP_' . $data['group_id'] . '_INTRO', 'Mon introduction a une balise de traduction bien formatée');
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

        $deleted = $h_sample->deleteSampleGroup($data['group_id']);
        $this->assertTrue($deleted, 'Le groupe de test a bien été supprimé');

        $deleted = $h_sample->deleteSampleForm($data['form_id']);
        $this->assertTrue($deleted, 'Le formulaire de test a bien été supprimé');
    }
}
