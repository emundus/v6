<?php


use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');
include_once (JPATH_SITE . '/components/com_emundus/helpers/emails.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusHelperEmail extends TestCase
{
    private $h_emails;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->h_emails = new EmundusHelperEmails();
    }

    public function testFoo()
    {
        $foo = true;
        $this->assertSame(true, $foo);
    }

    public function testCorrectEmail()
    {
        $this->assertSame(false, $this->h_emails->correctEmail(''), 'Validate empty email returns false');

        $this->assertSame(false, $this->h_emails->correctEmail('@email.com'), 'Validate email with wrong format returns false');
        $this->assertSame(false, $this->h_emails->correctEmail('jeremy.legendreemundus.fr'), 'Validate email with wrong format returns false');
        $this->assertSame(false, $this->h_emails->correctEmail('jeremy.legendre@'), 'Validate email with wrong format returns false');

        $this->assertSame(false, $this->h_emails->correctEmail('jeremy.legendre@wrong.dns'), 'Validate email with wrong dns returns false');

        $this->assertSame(true, $this->h_emails->correctEmail('jeremy.legendre@emundus.fr'), 'Validate correct email format returns true');
    }
}