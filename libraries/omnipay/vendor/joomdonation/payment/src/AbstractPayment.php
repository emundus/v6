<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Ossolution\Payment;

/**
 * Abstract Payment Class
 *
 * @since  1.0
 */

abstract class AbstractPayment implements PaymentInterface
{
	/**
	 * The name of payment method
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $name;

	/**
	 * The title of payment method
	 *
	 * @var string
	 *
	 * @since 1.0
	 */
	public $title;

	/**
	 * Payment method type
	 *
	 * @var int 0: off-site (redirect), 1: on-site (credit card)
	 */
	protected $type = 0;

	/**
	 * Payment plugin parameters
	 *
	 * @var JRegistry
	 */
	protected $params;

	/**
	 * Redirect page heading
	 *
	 * @var string
	 */
	protected $redirectHeading;

	/**
	 * Instantiate the payment object
	 *
	 * @param JRegistry $params
	 * @param array     $config
	 */
	public function __construct($params, $config = array())
	{
		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}
		else
		{
			$this->name = get_class($this);
		}

		if (isset($config['type']))
		{
			$this->type = $config['type'];
		}

		$this->params = $params;
	}

	/**
	 * {@inheritdoc }
	 *
	 * @param $row
	 * @param $data
	 */

	abstract public function processPayment($row, $data);

	/**
	 * Get name of the payment method
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Get title of the payment method
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set title of the payment method
	 *
	 * @param $value String
	 */

	public function setTitle($value)
	{
		$this->title = $value;
	}

	/**
	 * Method to check if this payment method is a CreditCard based payment method
	 *
	 * @return int
	 */
	public function getCreditCard()
	{
		return $this->type;
	}

	/**
	 * Set heading text for the redirect to payment gateway page
	 *
	 * @param $value
	 */
	public function setRedirectHeading($value)
	{
		$this->redirectHeading = $value;
	}

	/**
	 * Get payment plugin parameters
	 *
	 * @return JRegistry
	 */
	public function getParams()
	{
		return $this->params;
	}

	/***
	 * Render form which will redirect users to payment gateway for processing payment
	 *
	 * @param string $url The payment gateway URL which users will be redirected to
	 * @param array  $data
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	protected function renderRedirectForm($url = null, $data = array())
	{
	?>
		<div class="payment-heading"><?php echo $this->redirectHeading; ?></div>
		<form method="post" action="<?php echo $url; ?>" name="payment_form" id="payment_form">
			<?php
			foreach ($data as $key => $val)
			{
				echo '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
				echo "\n";
			}
			?>
			<script type="text/javascript">
				function redirect() {
					document.payment_form.submit();
				}
				setTimeout('redirect()', 3000);
			</script>
		</form>
	<?php
	}

	/**
	 * Helper method to log the callback data sent from payment gateway to the payment plugin for payment verification
	 *
	 * @param array  $data
	 * @param string $response
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	protected function logGatewayData($data, $response = null)
	{
		if (!$this->params->get('ipn_log', 0))
		{
			return;
		}

		$logFile = JPATH_COMPONENT . '/' . $this->getName() . '_ipn_logs.txt';

		$text = '[' . gmdate('m/d/Y g:i A') . '] - ';
		$text .= "Callback data From : " . $this->getTitle() . " \n";

		foreach ($data as $key => $value)
		{
			$text .= "$key=$value, ";
		}

		$text .= $response;

		$fp = fopen($logFile, 'a');
		fwrite($fp, $text . "\n\n");
		fclose($fp);
	}
}
