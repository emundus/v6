<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;

class EventbookingHelperBootstrap
{
	/**
	 * Bootstrap Helper instance
	 *
	 * @var EventbookingHelperBootstrap
	 */
	protected static $instance;

	/**
	 * Twitter bootstrap version, default 2
	 * @var string
	 */
	protected $bootstrapVersion;

	/**
	 * UI component
	 *
	 * @var RADUiInterface
	 */
	protected $ui;

	/**
	 * The class mapping to map between twitter bootstrap 2 and twitter bootstrap 3
	 * @var string
	 */
	protected static $classMaps;

	/**
	 * Get bootstrap helper object
	 *
	 * @return EventbookingHelperBootstrap
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			if (Factory::getApplication()->isClient('administrator'))
			{
				if (EventbookingHelper::isJoomla4())
				{
					self::$instance = new self('5');
				}
				else
				{
					self::$instance = new self('2');
				}
			}
			else
			{
				$config         = EventbookingHelper::getConfig();
				self::$instance = new self($config->twitter_bootstrap_version);
			}
		}

		return static::$instance;
	}

	/**
	 * Constructor, initialize the classmaps array
	 *
	 * @param   string  $ui
	 * @param   array   $classMaps
	 *
	 * @throws Exception
	 */
	public function __construct($ui, $classMaps = [])
	{
		if (empty($ui))
		{
			$ui = 2;
		}

		switch ($ui)
		{
			case 2:
			case 3:
			case 4:
			case 5:
				$uiClass = 'RADUiBootstrap' . $ui;
				break;
			default:
				$uiClass = 'RADUi' . ucfirst($ui);
				break;
		}

		$this->bootstrapVersion = $ui;

		if (!class_exists($uiClass))
		{
			throw new Exception(sprintf('UI class %s not found', $uiClass));
		}

		$this->ui = new $uiClass($classMaps);
	}

	/**
	 * Get the mapping of a given class
	 *
	 * @param   string  $class  The input class
	 *
	 * @return string The mapped class
	 */
	public function getClassMapping($class)
	{
		return $this->ui->getClassMapping($class);
	}

	/**
	 * Get twitter bootstrap version
	 *
	 * @return int|string
	 */
	public function getBootstrapVersion()
	{
		return $this->bootstrapVersion;
	}

	/**
	 * Method to get input with prepend add-on
	 *
	 * @param   string  $input
	 * @param   string  $addOn
	 *
	 * @return string
	 */
	public function getPrependAddon($input, $addOn)
	{
		return $this->ui->getPrependAddon($input, $addOn);
	}

	/**
	 * Method to get input with append add-on
	 *
	 * @param   string  $input
	 * @param   string  $addOn
	 *
	 * @return string
	 */
	public function getAppendAddon($input, $addOn)
	{
		return $this->ui->getAppendAddon($input, $addOn);
	}

	/**
	 * Get framework own css class
	 *
	 * @param   string  $class
	 * @param   int     $behavior
	 *
	 * @return string
	 */
	public function getFrameworkClass($class, $behavior = 0)
	{
		return $this->ui->getFrameworkClass($class, $behavior);
	}

	/**
	 * Get UI Component
	 *
	 * @return RADUiInterface
	 */
	public function getUi()
	{
		return $this->ui;
	}
}
