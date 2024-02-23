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

include_once(JPATH_BASE . 'includes/defines.php' );
include_once(JPATH_BASE . 'includes/framework.php' );
include_once(JPATH_SITE . '/components/com_emundus/unittest/helpers/samples.php');
include_once(JPATH_SITE . '/components/com_emundus/models/payment.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelPaymentTest extends TestCase
{
    private $m_payment;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_payment = new EmundusModelPayment;
		$this->h_sample = new EmundusUnittestHelperSamples;
    }

	public function testFoo()
	{
		$this->assertTrue(true);
	}

	public function testcreatePaymentOrder()
	{
		$user_id = $this->h_sample->createSampleUser(9, 'unit-test-candidat-' . rand(0, 10000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram();
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

		$this->assertNotEmpty($this->m_payment->createPaymentOrder($fnum, ''), 'Order created');
	}

	public function testdidUserPay() {
		$user_id = $this->h_sample->createSampleUser(9, 'unit-test-candidat-' . rand(0, 10000) . '@emundus.test.fr');
		$program = $this->h_sample->createSampleProgram();
		$campaign_id = $this->h_sample->createSampleCampaign($program);
		$fnum = $this->h_sample->createSampleFile($campaign_id, $user_id);

		$this->assertFalse($this->m_payment->didUserPay($user_id, $fnum, 0), 'Can not attest that user did pay if product is not set');
		$product_id = $this->h_sample->getSamplePaymentProduct();
		$this->assertFalse($this->m_payment->didUserPay($user_id, $fnum, $product_id), 'User and file has no payment record, it has been just created');

		$order_id = $this->m_payment->createPaymentOrder($fnum, '');
		$this->h_sample->createSampleOrderItem($order_id, $product_id);
		$this->assertFalse($this->m_payment->didUserPay($user_id, $fnum, $product_id), 'User and file has a payment record, but not a confirmed one');

		// update order status to confirmed
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__hikashop_order'))
			->set($db->quoteName('order_status') . ' = ' . $db->quote('confirmed'))
			->where($db->quoteName('order_id') . ' = ' . $db->quote($order_id));

		$db->setQuery($query);
		$db->execute();

		$this->assertTrue($this->m_payment->didUserPay($user_id, $fnum, $product_id), 'User and file has a payment record, and it is confirmed');

		// if I create a new fnum for same user and same program, it should not be considered as paid
		$new_campaign_id = $this->h_sample->createSampleCampaign($program, true);
		$fnum_without_payment = $this->h_sample->createSampleFile($new_campaign_id, $user_id);

		$this->assertTrue($this->m_payment->didUserPay($user_id, $fnum, $product_id), 'User and file has a payment record, and it is confirmed, but for a different fnum');
		$this->assertFalse($this->m_payment->didUserPay($user_id, $fnum_without_payment, $product_id), 'User and file has a payment record, and it is confirmed, but for a different fnum');
	}
}
