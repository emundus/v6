<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') or die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use FOF30\Container\Container;
use FOF30\Model\DataModel;

/**
 * @property   string  $source
 * @property   string  $dest
 * @property   int     $published
 * @property   int     $keepurlparams
 *
 * @method  $this  source()  source(string $v)
 * @method  $this  dest()  dest(string $v)
 * @method  $this  keepurlparams()  keepurlparams(string $v)
 * @method  $this  published()  published(string $v)
 */
class Redirections extends DataModel
{
	public function __construct(Container $container, array $config)
	{
		$config['tableName']   = '#__admintools_redirects';
		$config['idFieldName'] = 'id';
		$config['aliasFields'] = array('enabled' => 'published');

		parent::__construct($container, $config);
	}

	public function check()
	{
		if (!$this->source)
		{
			throw new \Exception(\JText::_('COM_ADMINTOOLS_ERR_REDIRECTION_NEEDS_SOURCE'));
		}

		if (!$this->dest)
		{
			throw new \Exception(\JText::_('COM_ADMINTOOLS_ERR_REDIRECTION_NEEDS_DEST'));
		}

		if (empty($this->published) && ($this->published !== 0))
		{
			$this->published = 0;
		}

		return parent::check();
	}

	public function setRedirectionState($newState)
	{
		$params = Storage::getInstance();

		$params->setValue('urlredirection', $newState ? 1 : 0);
		$params->save();
	}

	public function getRedirectionState()
	{
		$params = Storage::getInstance();

		return $params->getValue('urlredirection', 1);
	}

	public function buildQuery($overrideLimits = false)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
		            ->select(array('*'))
		            ->from($db->quoteName('#__admintools_redirects'));

		$fltSource = $this->getState('source', null, 'string');

		if ($fltSource)
		{
			$fltSource = '%' . $fltSource . '%';
			$query->where($db->quoteName('source') . ' LIKE ' . $db->quote($fltSource));
		}

		$fltDest = $this->getState('dest', null, 'string');

		if ($fltDest)
		{
			$fltDest = '%' . $fltDest . '%';
			$query->where($db->quoteName('dest') . ' LIKE ' . $db->quote($fltDest));
		}

		$fltKeepURLParams = $this->getState('keepurlparams', null, 'cmd');

		if (is_numeric($fltKeepURLParams) && !is_null($fltKeepURLParams) && $fltKeepURLParams >= 0)
		{
			$query->where($db->quoteName('keepurlparams') . ' = ' . $db->quote($fltKeepURLParams));
		}

		$fltPublished = $this->getState('published', null, 'cmd');

		if (!is_null($fltPublished) && ($fltPublished !== ''))
		{
			$query->where($db->quoteName('published') . ' = ' . $db->quote($fltPublished));
		}

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', null, 'cmd');

			if (!in_array($order, array_keys($this->knownFields)))
			{
				$order = 'id';
			}

			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($order . ' ' . $dir);
		}

		return $query;
	}
}