<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Helper\Storage;
use FOF40\Model\Model;

class SEOAndLinkTools extends Model
{
	public $defaultConfig = [
		'linkmigration' => 0,
		'migratelist'   => '',
	];

	public function getConfig()
	{
		$params = Storage::getInstance();

		$config = [];

		foreach ($this->defaultConfig as $k => $v)
		{
			$config[$k] = $params->getValue($k, $v);
		}

		return $config;
	}

	public function saveConfig($newParams)
	{
		$params = Storage::getInstance();

		foreach ($newParams as $key => $value)
		{
			$params->setValue($key, $value);
		}

		$params->save();
	}
}
