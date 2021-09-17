<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Eway payment class
 */
class os_eway extends RADPaymentOmnipay
{
	protected $omnipayPackage = 'Eway_Direct';

	/**
	 * Constructor
	 *
	 * @param   JRegistry  $params
	 * @param   array      $config
	 */
	public function __construct($params, $config = ['type' => 1])
	{
		$config['params_map'] = [
			'customerId' => 'eway_customer_id',
			'testMode'   => 'eway_mode',
		];

		parent::__construct($params, $config);
	}
}
