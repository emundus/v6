<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JLoader::import('joomla.application.component.model');

if (!class_exists('JoomlaCompatModel'))
{
	if (interface_exists('JModel'))
	{
		abstract class JoomlaCompatModel extends JModelLegacy
		{
		}
	}
	else
	{
		class JoomlaCompatModel extends JModel
		{
		}
	}
}

class AdmintoolsModelStorage extends JoomlaCompatModel
{
	/** @var JRegistry */
	private $config = null;

	public function __construct($config = array())
	{
		parent::__construct($config);

		// Check for F0F
		if (!defined('F0F_INCLUDED'))
		{
			require_once JPATH_LIBRARIES . '/f0f/include.php';
		}
	}

	public function getValue($key, $default = null)
	{
		if (is_null($this->config))
		{
			$this->load();
		}

		return $this->config->get($key, $default);
	}

	public function setValue($key, $value, $save = false)
	{
		if (is_null($this->config))
		{
			$this->load();
		}

		$x = $this->config->set($key, $value);

		if ($save)
		{
			$this->save();
		}

		return $x;
	}

	public function resetContents($save = false)
	{
		if (is_null($this->config))
		{
			$this->load();
		}

		$this->config->loadArray(array());

		if ($save)
		{
			$this->save();
		}
	}

	public function load()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select($db->quoteName('value'))
			->from($db->quoteName('#__admintools_storage'))
			->where($db->quoteName('key') . ' = ' . $db->quote('cparams'));
		$db->setQuery($query);

		$error = 0;
		try
		{
			$res = $db->loadResult();
		}
		catch (Exception $e)
		{
			$error = $e->getCode();
		}

		if (method_exists($db, 'getErrorNum') && $db->getErrorNum())
		{
			$error = $db->getErrorNum();
		}

		if ($error)
		{
			$res = null;
		}


		$this->config = new JRegistry();

		if (!empty($res))
		{
			$res = json_decode($res, true);
			$this->config->loadArray($res);
		}
	}

	public function save()
	{
		if (is_null($this->config))
		{
			$this->load();
		}

		$db = JFactory::getDBO();
		$data = $this->config->toArray();
		$data = json_encode($data);

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__admintools_storage'))
			->where($db->quoteName('key') . ' = ' . $db->quote('cparams'));
		$db->setQuery($query);
		$db->execute();

		$object = (object)array(
			'key'   => 'cparams',
			'value' => $data
		);
		$db->insertObject('#__admintools_storage', $object);
	}
}