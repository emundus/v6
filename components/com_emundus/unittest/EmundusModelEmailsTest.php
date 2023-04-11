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
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/models/emails.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelEmailsTest extends TestCase
{
    private $m_emails;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_emails = new EmundusModelEmails;
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
		$lbl = 'Test de la création ' . rand(0, 1000);
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
		$created = $this->m_emails->createEmail($data);
		$created_email = $this->m_emails->getEmailById($created);

		$deleted = $this->m_emails->deleteEmail($created_email->id);
		$this->assertTrue($deleted, 'La suppression de l\'email a fonctionné');

		$email = $this->m_emails->getEmailById($created_email->id);
		$this->assertNull($email, 'L\'email a bien été supprimé, on ne le retrouve plus');
	}
}
