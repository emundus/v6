<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\View\Engine;

use FOF30\Utils\Buffer;
use FOF30\View\Compiler\CompilerInterface;
use FOF30\View\Exception\PossiblySuhosin;

defined('_JEXEC') or die;

/**
 * View engine for compiling PHP template files.
 */
abstract class CompilingEngine extends AbstractEngine implements EngineInterface
{
	/** @var  CompilerInterface  The compiler used by this engine */
	protected $compiler = null;

	/**
	 * Get the evaluated contents of the view template.
	 *
	 * @param   string  $path         The path to the view template
	 * @param   array   $forceParams  Any additional information to pass to the view template engine
	 *
	 * @return  array  Content evaluation information
	 */
	public function get($path, array $forceParams = array())
	{
		// If it's cached return the path to the cached file's path
		if ($this->isCached($path))
		{
			return array(
				'type'    => 'path',
				'content' => $this->getCachePath($path),
			);
		}

		// Not cached or caching not really allowed. Compile it and cache it.
		$content = $this->compile($path, $forceParams);
		$cachePath = $this->putToCache($path, $content);

		// If we could cache it, return the cached file's path
		if ($cachePath !== false)
		{
			return array(
				'type'    => 'path',
				'content' => $cachePath,
			);
		}

		// We could not write to the cache. Hm, can I use a stream wrapper?
		$canUseStreams = Buffer::canRegisterWrapper();

		if ($canUseStreams)
		{
			$id = $this->getIdentifier($path);
			$streamPath = 'fof://' . $this->view->getContainer()->componentName . '/compiled_templates/' . $id . '.php';

			return array(
				'type'    => 'path',
				'content' => $streamPath,
			);
		}

		// I couldn't use a stream wrapper. I have to give up.
		throw new PossiblySuhosin;
	}

	/**
	 * A method to compile the raw view template into valid PHP
	 *
	 * @param   string  $path         The path to the view template
	 * @param   array   $forceParams  Any additional information to pass to the view template compiler
	 *
	 * @return  string  The template compiled to executable PHP
	 */
	protected function compile($path, array $forceParams = array())
	{
		return $this->compiler->compile($path, $forceParams);
	}

	protected function getIdentifier($path)
	{
		if (function_exists('sha1'))
		{
			return sha1($path);
		}

		return md5($path);
	}

	protected function getCachePath($path)
	{
		$id = $this->getIdentifier($path);
		return JPATH_CACHE . '/' . $this->view->getContainer()->componentName . '/compiled_templates/' . $id . '.php';
	}

	protected function isCached($path)
	{
		if (!$this->compiler->isCacheable())
		{
			return false;
		}

		$cachePath = $this->getCachePath($path);

		if (!file_exists($cachePath))
		{
			return false;
		}

		$cacheTime = filemtime($cachePath);
		$fileTime = filemtime($path);

		return $fileTime <= $cacheTime;
	}

	protected function getCached($path)
	{
		$cachePath = $this->getCachePath($path);

		return file_get_contents($cachePath);
	}

	protected function putToCache($path, $content)
	{
		$cachePath = $this->getCachePath($path);

		if (@file_put_contents($cachePath, $content))
		{
			return $cachePath;
		}

		if (!class_exists('JFile'))
		{
			\JLoader::import('joomla.filesystem.file');
		}

		if (\JFile::write($cachePath, $content))
		{
			return $cachePath;
		}

		return false;
	}
}