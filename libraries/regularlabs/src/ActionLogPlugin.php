<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Plugin\CMSPlugin as JCMSPlugin;
use RegularLabs\Library\ParametersNew as Parameters;

/**
 * Class ActionLogPlugin
 * @package RegularLabs\Library
 */
class ActionLogPlugin extends JCMSPlugin
{
	static $ids    = [];
	public $alias  = '';
	public $events = [];
	public $items  = [];
	public $name   = '';
	public $option = '';
	public $table  = null;

	public function __construct(&$subject, array $config = [])
	{
		parent::__construct($subject, $config);

		Language::load('plg_actionlog_' . $this->alias);

		$config = Parameters::getComponent($this->alias);

		$enable_actionlog = $config->enable_actionlog ?? true;
		$this->events     = $enable_actionlog ? ['*'] : [];

		if ($enable_actionlog && ! empty($config->actionlog_events))
		{
			$this->events = ArrayHelper::toArray($config->actionlog_events);
		}

		$this->name   = JText::_($this->name);
		$this->option = $this->option ?: 'com_' . $this->alias;
	}

	public function onContentChangeState($context, $ids, $value)
	{
		if (strpos($context, $this->option) === false)
		{
			return;
		}

		if ( ! ArrayHelper::find(['*', 'change_state'], $this->events))
		{
			return;
		}

		$item = $this->getItem($context);

		if ( ! $this->table)
		{
			if ( ! is_file($item->file))
			{
				return;
			}

			require_once $item->file;

			$this->table = (new $item->model)->getTable();
		}

		foreach ($ids as $id)
		{
			$this->table->load($id);

			$title    = $this->table->title ?? $this->table->name ?? $this->table->id;
			$itemlink = str_replace('{id}', $this->table->id, $item->url);

			$message = [
				'type'     => $item->title,
				'id'       => $id,
				'title'    => $title,
				'itemlink' => $itemlink,
			];

			Log::changeState($message, $context, $value);
		}
	}

	private function getItem($context)
	{
		$item = $this->getItemData($context);

		$item->title = isset($item->title)
			? JText::_($item->title)
			: $item->type . ' ' . JText::_('RL_ITEM');

		if ( ! isset($item->file))
		{
			$item->file = JPATH_ADMINISTRATOR . '/components/' . $this->option . '/models/' . $item->type . '.php';
		}

		if ( ! isset($item->model))
		{
			$item->model = $this->alias . 'Model' . ucfirst($item->type);
		}

		if ( ! isset($item->url))
		{
			$item->url = 'index.php?option=' . $this->option . '&view=' . $item->type . '&layout=edit&id={id}';
		}

		return $item;
	}

	private function getItemData($context)
	{
		$default = (object) [
			'type' => 'item',
		];

		$type = key($this->items) ?: 'item';

		if (strpos($context, '.') !== false)
		{
			$parts = explode('.', $context);
			$type  = $parts[1];
		}

		if ( ! isset($this->items[$type]))
		{
			return $default;
		}

		$item = $this->items[$type];

		if ( ! isset($item->type))
		{
			$item->type = $type;
		}

		return $item;
	}

	public function onExtensionAfterDelete($context, $table)
	{
		self::onContentAfterDelete($context, $table);
	}

	public function onContentAfterDelete($context, $table)
	{
		if (strpos($context, $this->option) === false)
		{
			return;
		}

		if ( ! ArrayHelper::find(['*', 'delete'], $this->events))
		{
			return;
		}

		$item = $this->getItem($context);

		$title = $table->title ?? $table->name ?? $table->id;

		$message = [
			'type'  => $item->title,
			'id'    => $table->id,
			'title' => $title,
		];

		Log::delete($message, $context);
	}

	public function onExtensionAfterInstall($installer, $eid)
	{
		// Prevent duplicate logs
		if (in_array('install_' . $eid, self::$ids))
		{
			return;
		}

		$context = JFactory::getApplication()->input->get('option');

		if (strpos($context, $this->option) === false)
		{
			return;
		}

		if ( ! ArrayHelper::find(['*', 'install'], $this->events))
		{
			return;
		}

		$extension = Extension::getById($eid);

		if (empty($extension->manifest_cache))
		{
			return;
		}

		$manifest = json_decode($extension->manifest_cache);

		if (empty($manifest->name))
		{
			return;
		}

		self::$ids[] = 'install_' . $eid;

		$message = [
			'id'             => $eid,
			'extension_name' => JText::_($manifest->name),
		];

		Log::install($message, 'com_regularlabsmanager', $manifest->type);
	}

	public function onExtensionAfterSave($context, $table, $isNew)
	{
		self::onContentAfterSave($context, $table, $isNew);
	}

	public function onContentAfterSave($context, $table, $isNew)
	{
		if (strpos($context, $this->option) === false)
		{
			return;
		}

		$event = $isNew ? 'create' : 'update';

		if ( ! ArrayHelper::find(['*', $event], $this->events))
		{
			return;
		}

		$item = $this->getItem($context);

		$title    = $table->title ?? $table->name ?? $table->id;
		$item_url = str_replace('{id}', $table->id, $item->url);

		$message = [
			'type'     => $item->title,
			'id'       => $table->id,
			'title'    => $title,
			'itemlink' => $item_url,
		];

		Log::save($message, $context, $isNew);
	}

	public function onExtensionAfterUninstall($installer, $eid, $result)
	{
		// Prevent duplicate logs
		if (in_array('uninstall_' . $eid, self::$ids))
		{
			return;
		}

		$context = JFactory::getApplication()->input->get('option');

		if (strpos($context, $this->option) === false)
		{
			return;
		}

		if ( ! ArrayHelper::find(['*', 'uninstall'], $this->events))
		{
			return;
		}

		if ($result === false)
		{
			return;
		}

		$manifest = $installer->get('manifest');

		if ($manifest === null)
		{
			return;
		}

		self::$ids[] = 'uninstall_' . $eid;

		$message = [
			'id'             => $eid,
			'extension_name' => JText::_($manifest->name),
		];

		Log::uninstall($message, 'com_regularlabsmanager', $manifest->attributes()->type);
	}
}
