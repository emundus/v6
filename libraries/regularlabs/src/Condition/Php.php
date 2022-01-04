<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use JDocument;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\MVC\Model\BaseDatabaseModel as JModel;
use Joomla\CMS\Version;
use RegularLabs\Library\Condition;
use RegularLabs\Library\RegEx;

/**
 * Class Php
 * @package RegularLabs\Library\Condition
 */
class Php extends Condition
{
	public static function createFunctionInMemory($string = '')
	{
		$file_name = getmypid() . '_' . md5($string);

		$tmp_path  = JFactory::getConfig()->get('tmp_path', JPATH_ROOT . '/tmp');
		$temp_file = $tmp_path . '/regularlabs' . '/' . $file_name;

		// Write file
		if ( ! file_exists($temp_file) || is_writable($temp_file))
		{
			JFile::write($temp_file, $string);
		}

		// Include file
		include_once $temp_file;

		// Delete file
		if ( ! JFactory::getApplication()->get('debug'))
		{
			@chmod($temp_file, 0777);
			@unlink($temp_file);
		}
	}

	public static function getApplication()
	{
		if (JFactory::getApplication()->input->get('option') != 'com_finder')
		{
			return JFactory::getApplication();
		}

		return CMSApplication::getInstance('site');
	}

	public static function getDocument()
	{
		if (JFactory::getApplication()->input->get('option') != 'com_finder')
		{
			return JFactory::getDocument();
		}

		$lang    = JFactory::getLanguage();
		$version = new Version;

		$attributes = [
			'charset'      => 'utf-8',
			'lineend'      => 'unix',
			'tab'          => "\t",
			'language'     => $lang->getTag(),
			'direction'    => $lang->isRtl() ? 'rtl' : 'ltr',
			'mediaversion' => $version->getMediaVersion(),
		];

		return JDocument::getInstance('html', $attributes);
	}

	public static function getVarInits()
	{
		return [
			'$app = $mainframe = RegularLabs\Library\Condition\Php::getApplication();',
			'$document = $doc = RegularLabs\Library\Condition\Php::getDocument();',
			'$database = $db = JFactory::getDbo();',
			'$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();',
			'$Itemid = $app->input->getInt(\'Itemid\');',
		];
	}

	public function execute($string = '', $article = null, $module = null)
	{
		if ( ! $function_name = $this->getFunctionName($string))
		{
			// Something went wrong!
			return true;
		}

		return $this->runFunction($function_name, $string, $article, $module);
	}

	public function pass()
	{
		if ( ! is_array($this->selection))
		{
			$this->selection = [$this->selection];
		}

		$pass = false;
		foreach ($this->selection as $php)
		{
			// replace \n with newline and other fix stuff
			$php = str_replace('\|', '|', $php);
			$php = RegEx::replace('(?<!\\\)\\\n', "\n", $php);
			$php = trim(str_replace('[:REGEX_ENTER:]', '\n', $php));

			if ($php == '')
			{
				$pass = true;
				break;
			}

			ob_start();
			$pass = (bool) $this->execute($php, $this->article, $this->module);
			ob_end_clean();

			if ($pass)
			{
				break;
			}
		}

		return $this->_($pass);
	}

	private function generateFileContents($function_name = 'rl_function', $string = '')
	{
		$init_variables = self::getVarInits();

		$contents = [
			'<?php',
			'defined(\'_JEXEC\') or die;',
			'function ' . $function_name . '($article, $module){',
			implode("\n", $init_variables),
			$string,
			';return true;',
			';}',
		];

		$contents = implode("\n", $contents);

		// Remove Zero Width spaces / (non-)joiners
		$contents = str_replace(
			[
				"\xE2\x80\x8B",
				"\xE2\x80\x8C",
				"\xE2\x80\x8D",
			],
			'',
			$contents
		);

		return $contents;
	}

	private function getArticleById($id = 0)
	{
		if ( ! $id)
		{
			return null;
		}

		if ( ! class_exists('ContentModelArticle'))
		{
			require_once JPATH_SITE . '/components/com_content/models/article.php';
		}

		$model = JModel::getInstance('article', 'contentModel');

		if ( ! method_exists($model, 'getItem'))
		{
			return null;
		}

		return $model->getItem($id);
	}

	private function getFunctionName($string = '')
	{
		$function_name = 'regularlabs_php_' . md5($string);

		if (function_exists($function_name))
		{
			return $function_name;
		}

		$contents = $this->generateFileContents($function_name, $string);
		self::createFunctionInMemory($contents);

		if ( ! function_exists($function_name))
		{
			// Something went wrong!
			return false;
		}

		return $function_name;
	}

	private function runFunction($function_name = 'rl_function', $string = '', $article = null, $module = null)
	{
		if ( ! $article && strpos($string, '$article') !== false)
		{
			if ($this->request->option == 'com_content' && $this->request->view == 'article')
			{
				$article = $this->getArticleById($this->request->id);
			}
		}

		return $function_name($article, $module);
	}
}
