<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

/**
 * A helper class to quickly get the component parameters
 */
class AdmintoolsHelperParams
{
	/** @var  string  The component we belong to */
	protected $component = null;

	/**
	 * Cached component parameters
	 *
	 * @var \Joomla\Registry\Registry
	 */
	private $params = null;

	/**
	 * Public constructor for the params object
	 *
	 * @param  string $component  The component we belong to
	 */
	public function __construct($component = 'com_admintools')
	{
		$this->component = $component;

		$this->reload();
	}

	/**
	 * Reload the params
	 */
	public function reload()
	{
		$db = JFactory::getDbo();

		$sql = $db->getQuery(true)
				  ->select($db->qn('params'))
				  ->from($db->qn('#__extensions'))
				  ->where($db->qn('element') . " = " . $db->q($this->component));
		$json = $db->setQuery($sql)->loadResult();

		$this->params = new JRegistry($json);
	}

	/**
	 * Returns the value of a component configuration parameter
	 *
	 * @param   string $key     The parameter to get
	 * @param   mixed  $default Default value
	 *
	 * @return  mixed
	 */
	public function get($key, $default = null)
	{
		return $this->params->get($key, $default);
	}

	/**
	 * Returns a copy of the loaded component parameters as an array
	 *
	 * @return  array
	 */
	public function getParams()
	{
		return $this->params->toArray();
	}

	/**
	 * Sets the value of a component configuration parameter
	 *
	 * @param   string $key    The parameter to set
	 * @param   mixed  $value  The value to set
	 *
	 * @return  void
	 */
	public function set($key, $value)
	{
		$this->setParams(array($key => $value));
	}

	/**
	 * Sets the value of multiple component configuration parameters at once
	 *
	 * @param   array  $params  The parameters to set
	 *
	 * @return  void
	 */
	public function setParams(array $params)
	{
		foreach ($params as $key => $value)
		{
			$this->params->set($key, $value);
		}
	}

	/**
	 * Actually Save the params into the db
	 */
	public function save()
	{
		$db   = JFactory::getDbo();
		$data = $this->params->toString();

		$sql  = $db->getQuery(true)
				   ->update($db->qn('#__extensions'))
				   ->set($db->qn('params') . ' = ' . $db->q($data))
				   ->where($db->qn('element') . ' = ' . $db->q($this->component))
				   ->where($db->qn('type') . ' = ' . $db->q('component'));

		$db->setQuery($sql);

		try
		{
			$db->execute();
		}
		catch (\Exception $e)
		{
			// Don't sweat if it fails
		}
	}
}