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

include_once(JPATH_BASE . 'includes/defines.php');
include_once(JPATH_BASE . 'includes/framework.php');
include_once(JPATH_ROOT . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_ROOT . '/components/com_emundus/models/emails.php');
include_once(JPATH_ROOT . '/components/com_emundus/models/profile.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

class EmundusModelEmailsTest extends TestCase
{
    private $m_emails;

	private $h_sample;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
	    $app = JFactory::getApplication();
	    $this->h_sample = new EmundusUnittestHelperSamples;
	    $username = 'test-expert-email-' . rand(0, 1000) . '@emundus.fr';
	    $this->h_sample->createSampleUser(9, $username);
	    $logged_in = $app->login([
		    'username' => $username,
		    'password' => 'test1234'
	    ]);

	    $m_profile = new EmundusModelProfile();
	    $m_profile->initEmundusSession();
        $this->m_emails = new EmundusModelEmails;
    }

	public function testFoo()
	{
		$this->assertTrue(true);
	}

	public function testDeleteSystemEmails()
	{
		$data = $this->m_emails->getAllEmails(999, 0, '', '', '');
		$this->assertNotEmpty($data);

		// select one email with type 1
		$system_emails = array_filter($data['datas'], function($email) {
			return $email->type == 1;
		});

		$deleted = $this->m_emails->deleteEmail(current($system_emails)->id);
		$this->assertFalse($deleted, 'La suppression de l\'email n\'a pas fonctionné, car c\'est un email système');

		$email = $this->m_emails->getEmailById(current($system_emails)->id);
		$this->assertNotEmpty($email->id, 'On retrouve bien l\'email par son id');
	}

	public function testCreateEmail()
	{
		$data = [
			'lbl' => 'Test de la création',
			'subject' => 'Test de la création',
			'name' => '',
			'emailfrom' => '',
			'message' => '<p>Test de la création</p>',
			'type' => 2,
			'category' => '',
			'published' => 1
		];

		$created = $this->m_emails->createEmail($data);
		$this->assertNotFalse($created, 'La création de l\'email a fonctionné');
		$created_email = $this->m_emails->getEmailById($created);

		$this->assertNotNull($created_email, 'L\'email a bien été créé, on le retrouve par son sujet');

		$email_by_id = $this->m_emails->getEmailById($created_email->id);
		$this->assertNotNull($email_by_id, 'L\'email a bien été créé, on le retrouve par son id');
		$this->assertSame($created_email->subject, $email_by_id->subject, 'L\'email a bien été créé, on le retrouve par son id et il est le même que par son libelle');
	}

	public function testDeleteEmails()
	{
		$lbl = 'Test de la suppression ' . rand(0, 1000);
		$data = [
			'lbl' => $lbl,
			'subject' => 'Test de la création',
			'name' => 'Test de la création',
			'emailfrom' => '',
			'message' => '<p>Test de la création</p>',
			'type' => 2,
			'category' => '',
			'published' => 1
		];

		sleep(1);
		$created = $this->m_emails->createEmail($data);
		$this->assertNotFalse($created, 'La création de l\'email a fonctionnée');

		$deleted = $this->m_emails->deleteEmail($created);
		$this->assertTrue($deleted, 'La suppression de l\'email a fonctionnée d\'après le retour de la fonction.');

		$email = $this->m_emails->getEmailById($created);
		$this->assertNull($email, 'L\'email a bien été supprimé, on ne le retrouve plus en base');
	}

	public function testsendExpertMail()
	{
		$response = $this->m_emails->sendExpertMail([]);
		$this->assertEmpty($response['sent'], 'L\'envoi de l\'email a échoué, car il manque des paramètres');

		$params = [
			'mail_from' => '',
			'mail_from_name' => '',
			'mail_subject' => '',
			'mail_body' => '',
			'fnums' => []
		];

		$app = JFactory::getApplication();
		$jinput = $app->input;

		$user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram();
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

		$response = $this->m_emails->sendExpertMail([$fnum]);
		$this->assertEmpty($response['sent'], 'L\'envoi de l\'email a échoué, car il manque des paramètres');

		$params['mail_to'] = ['userunittest' . rand(0, 1000) . '@emundus.test.fr'];
		$jinput->post->set('mail_to', $params['mail_to']);

		$response = $this->m_emails->sendExpertMail([$fnum]);
		$this->assertContains($params['mail_to'][0], $response['failed'], 'L\'envoi de l\'email n\'a pas fonctionné, car il y n\'y a pas de message.');

		$params['mail_subject'] = 'Test de l\'envoi d\'email';
		$jinput->post->set('mail_subject', $params['mail_subject']);
		$params['mail_body'] = '<p>Test de l\'envoi d\'email</p>';
		$jinput->post->set('mail_body', $params['mail_body']);

		/*
		 * @todo : test de l'envoi de l'email
		 * it can not be tested because of the mail function, not available in the test environment
		 * $response = $this->m_emails->sendExpertMail([$fnum]);
		 * $this->assertContains($params['mail_to'][0], $response['sent'], 'L\'envoi de l\'email expert a fonctionné.');
		 */
	}


	function testsetTagsFabrik() {
		$user_id = $this->h_sample->createSampleUser(9, 'userunittest' . rand(0, 1000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram();
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

		$string = 'Une chaine sans tag fabrik';
		$new_string = $this->m_emails->setTagsFabrik($string, [$fnum]);

		$this->assertEquals($string, $new_string, 'La chaine ne contient pas de tag fabrik, elle n\'a pas été modifiée');

		$string = 'Une chaine avec un tag fabrik ${99999}';
		$new_string = $this->m_emails->setTagsFabrik($string, [$fnum]);

		$this->assertNotEquals($string, $new_string, 'La chaine contient un tag fabrik, elle a été modifiée');

		$string = 'Une chaine avec un tag fabrik existant correspondant à la campagne ${1906}';
		$new_string = $this->m_emails->setTagsFabrik($string, [$fnum]);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('label')
			->from('#__emundus_setup_campaigns')
			->where('id = ' . $campaign_id);

		$db->setQuery($query);
		$campaign_label = $db->loadResult();

		$new_string_expected = 'Une chaine avec un tag fabrik existant correspondant à la campagne ' . $campaign_label;
		$this->assertEquals($new_string_expected, $new_string, 'La chaine utilisant un tag fabrik existant retourne le résultat attendu');

		$string = 'Une chaine avec un tag fabrik existant correspondant à la campagne ${1906} et un tag fabrik inexistant ${99999}';
		$new_string = $this->m_emails->setTagsFabrik($string, [$fnum]);
		$new_string_expected = 'Une chaine avec un tag fabrik existant correspondant à la campagne ' . $campaign_label . ' et un tag fabrik inexistant ';
		$this->assertEquals($new_string_expected, $new_string, 'La chaine utilisant un tag fabrik existant et un tag fabrik inexistant retourne le résultat attendu');

		$string = 'Une chaine avec plusieurs tags fabriks existants ${1906} et un tag fabrik inexistant ${99999} puis ${1906}';
		$new_string = $this->m_emails->setTagsFabrik($string, [$fnum]);
		$new_string_expected = 'Une chaine avec plusieurs tags fabriks existants ' . $campaign_label . ' et un tag fabrik inexistant  puis ' . $campaign_label;
		$this->assertEquals($new_string_expected, $new_string, 'La chaine utilisant plusieurs tags fabriks existants et un tag fabrik inexistant retourne le résultat attendu');
	}
}
