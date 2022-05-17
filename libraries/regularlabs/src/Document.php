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

use Joomla\CMS\Document\Document as JDocument;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\CacheNew as Cache;

/**
 * Class Document
 * @package RegularLabs\Library
 */
class Document
{
	/**
	 * Enqueues an admin error
	 *
	 * @param string $message
	 */
	public static function adminError($message)
	{
		self::adminMessage($message, 'error');
	}

	/**
	 * Enqueues an admin message
	 *
	 * @param string $message
	 * @param string $type
	 */
	public static function adminMessage($message, $type = 'message')
	{
		if ( ! self::isAdmin())
		{
			return;
		}

		self::message($message, $type);
	}

	/**
	 * Check if page is an admin page
	 *
	 * @param bool $exclude_login
	 *
	 * @return bool
	 */
	public static function isAdmin($exclude_login = false)
	{
		$cache = new Cache([__METHOD__, $exclude_login]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$input = JFactory::getApplication()->input;
		$user  = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		$is_admin = (
			self::isClient('administrator')
			&& ( ! $exclude_login || ! $user->get('guest'))
			&& $input->get('task') != 'preview'
			&& ! (
				$input->get('option') == 'com_finder'
				&& $input->get('format') == 'json'
			)
		);

		return $cache->set($is_admin);
	}

	/**
	 * Enqueues a message
	 *
	 * @param string $message
	 * @param string $type
	 */
	public static function message($message, $type = 'message')
	{
		Language::load('plg_system_regularlabs');

		JFactory::getApplication()->enqueueMessage($message, $type);
	}

	/**
	 * Check if page is an edit page
	 *
	 * @return bool
	 */
	public static function isClient($identifier)
	{
		$identifier = $identifier == 'admin' ? 'administrator' : $identifier;

		$cache = new Cache([__METHOD__, $identifier]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		return $cache->set(JFactory::getApplication()->isClient($identifier));
	}

	/**
	 * Enqueues an error
	 *
	 * @param string $message
	 */
	public static function error($message)
	{
		self::message($message, 'error');
	}

	/**
	 * @return null|string
	 *
	 * @depecated Use getComponentBuffer
	 */
	public static function getBuffer()
	{
		return self::getComponentBuffer();
	}

	/**
	 * @return null|string
	 */
	public static function getComponentBuffer()
	{
		$buffer = self::get()->getBuffer('component');

		if (empty($buffer) || ! is_string($buffer))
		{
			return null;
		}

		$buffer = trim($buffer);

		if (empty($buffer))
		{
			return null;
		}

		return $buffer;
	}

	/**
	 * @return  JDocument  The document object
	 */
	public static function get()
	{
		$document = JFactory::getApplication()->getDocument();

		if ( ! is_null($document))
		{
			return $document;
		}

		JFactory::getApplication()->loadDocument();

		return JFactory::getApplication()->getDocument();
	}

	/**
	 * Checks if context/page is a category list
	 *
	 * @param string $context
	 *
	 * @return bool
	 */
	public static function isCategoryList($context)
	{
		$cache = new Cache([__METHOD__, $context]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$app   = JFactory::getApplication();
		$input = $app->input;

		// Return false if it is not a category page
		if ($context != 'com_content.category' || $input->get('view') != 'category')
		{
			return $cache->set(false);
		}

		// Return false if layout is set and it is not a list layout
		if ($input->get('layout') && $input->get('layout') != 'list')
		{
			return $cache->set(false);
		}

		// Return false if default layout is set to blog
		if ($app->getParams()->get('category_layout') == '_:blog')
		{
			return $cache->set(false);
		}

		// Return true if it IS a list layout
		return $cache->set(true);
	}

	/**
	 * Check if page is an edit page
	 *
	 * @return bool
	 */
	public static function isEditPage()
	{
		$cache = new Cache(__METHOD__);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$input = JFactory::getApplication()->input;

		$option = $input->get('option');

		// always return false for these components
		if (in_array($option, ['com_rsevents', 'com_rseventspro']))
		{
			return $cache->set(false);
		}

		$task = $input->get('task');

		if (strpos($task, '.') !== false)
		{
			$task = explode('.', $task);
			$task = array_pop($task);
		}

		$view = $input->get('view');

		if (strpos($view, '.') !== false)
		{
			$view = explode('.', $view);
			$view = array_pop($view);
		}

		$is_edit_page = (
			in_array($option, ['com_config', 'com_contentsubmit', 'com_cckjseblod'])
			|| ($option == 'com_comprofiler' && in_array($task, ['', 'userdetails']))
			|| in_array($task, ['edit', 'form', 'submission'])
			|| in_array($view, ['edit', 'form'])
			|| in_array($input->get('do'), ['edit', 'form'])
			|| in_array($input->get('layout'), ['edit', 'form', 'write'])
			|| self::isAdmin()
		);

		return $cache->set($is_edit_page);
	}

	/**
	 * Checks if current page is a feed
	 *
	 * @return bool
	 */
	public static function isFeed()
	{
		$cache = new Cache(__METHOD__);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$input = JFactory::getApplication()->input;

		$is_feed = (
			self::get()->getType() == 'feed'
			|| in_array($input->getWord('format'), ['feed', 'xml'])
			|| in_array($input->getWord('type'), ['rss', 'atom'])
		);

		return $cache->set($is_feed);
	}

	/**
	 * Checks if current page is a html page
	 *
	 * @return bool
	 */
	public static function isHtml()
	{
		$cache = new Cache(__METHOD__);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$is_html = (self::get()->getType() == 'html');

		return $cache->set($is_html);
	}

	/**
	 * Checks if current page is a https (ssl) page
	 *
	 * @return bool
	 */
	public static function isHttps()
	{
		$cache = new Cache(__METHOD__);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$is_https = (
			( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')
			|| (isset($_SERVER['SSL_PROTOCOL']))
			|| (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
			|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
		);

		return $cache->set($is_https);
	}

	/**
	 * Checks if current page is a JSON format fle
	 *
	 * @return bool
	 */
	public static function isJSON()
	{
		$cache = new Cache(__METHOD__);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$is_json = JFactory::getApplication()->input->get('format') == 'json';

		return $cache->set($is_json);
	}

	/**
	 * Check if the current setup matches the given main version number
	 *
	 * @param int    $version
	 * @param string $title
	 *
	 * @return bool
	 */
	public static function isJoomlaVersion($version, $title = '')
	{
		if ((int) JVERSION == $version)
		{
			return true;
		}

		if ($title && self::isAdmin())
		{
			Language::load('plg_system_regularlabs');

			JFactory::getApplication()->enqueueMessage(
				JText::sprintf('RL_NOT_COMPATIBLE_WITH_JOOMLA_VERSION', JText::_($title), (int) JVERSION),
				'error'
			);
		}

		return false;
	}

	/**
	 * Checks if current page is a pdf
	 *
	 * @return bool
	 */
	public static function isPDF()
	{
		$cache = new Cache(__METHOD__);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$input = JFactory::getApplication()->input;

		$is_pdf = (
			self::get()->getType() == 'pdf'
			|| $input->getWord('format') == 'pdf'
			|| $input->getWord('cAction') == 'pdf'
		);

		return $cache->set($is_pdf);
	}

	/**
	 * Loads the required scripts and styles used in forms
	 */
	public static function loadEditorButtonDependencies()
	{
		self::loadMainDependencies();

		JHtml::_('bootstrap.popover');
	}

	/**
	 * Loads the required scripts and styles used in forms
	 */
	public static function loadMainDependencies()
	{
		JHtml::_('jquery.framework');

		self::script('regularlabs/script.min.js');
		self::style('regularlabs/style.min.css');
	}

	/**
	 * Adds a script file to the page (with optional versioning)
	 *
	 * @param string $file
	 * @param string $version
	 * @param array  $options
	 * @param array  $attribs
	 * @param bool   $load_jquery
	 */
	public static function script($file, $version = '', $options = [], $attribs = [], $load_jquery = true)
	{
		if ( ! $url = File::getMediaFile('js', $file))
		{
			return;
		}

		if ($load_jquery)
		{
			JHtml::_('jquery.framework');
		}

		if (strpos($file, 'regularlabs/') !== false
			&& strpos($file, 'regular.') === false
		)
		{
			JHtml::_('behavior.core');
			JHtml::_('script', 'jui/cms.js', ['version' => 'auto', 'relative' => true]);
			$version = '22.4.18687';
		}

		if ( ! empty($version))
		{
			$url .= '?v=' . $version;
		}

		self::get()->addScript($url, $options, $attribs);
	}

	/**
	 * Adds a stylesheet file to the page(with optional versioning)
	 *
	 * @param string $file
	 * @param string $version
	 * @param array  $options
	 * @param array  $attribs
	 */
	public static function style($file, $version = '', $options = [], $attribs = [])
	{
		if (strpos($file, 'regularlabs/') === 0)
		{
			$version = '22.4.18687';
		}

		$file = File::getMediaFile('css', $file);
		if ( ! $file)
		{
			return;
		}

		if ( ! empty($version))
		{
			$file .= '?v=' . $version;
		}

		self::get()->addStylesheet($file, $options, $attribs);
	}

	public static function loadPopupDependencies()
	{
		self::loadMainDependencies();
		self::loadFormDependencies();

		self::style('regularlabs/popup.min.css');
	}

	/**
	 * Loads the required scripts and styles used in forms
	 */
	public static function loadFormDependencies()
	{
		if ((int) JVERSION != 3)
		{
			return;
		}

		JHtml::_('jquery.framework');
		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.formvalidator');
		JHtml::_('behavior.combobox');
		JHtml::_('behavior.keepalive');
		JHtml::_('behavior.tabstate');

		JHtml::_('formbehavior.chosen', '#jform_position', null, ['disable_search_threshold' => 0]);
		JHtml::_('formbehavior.chosen', '.multipleCategories', null, ['placeholder_text_multiple' => JText::_('JOPTION_SELECT_CATEGORY')]);
		JHtml::_('formbehavior.chosen', '.multipleTags', null, ['placeholder_text_multiple' => JText::_('JOPTION_SELECT_TAG')]);
		JHtml::_('formbehavior.chosen', 'select');

		self::script('regularlabs/form.min.js');
		self::style('regularlabs/form.min.css');
	}

	/**
	 * Remove joomla script options
	 *
	 * @param string $string
	 * @param string $name
	 * @param string $alias
	 */
	public static function removeScriptsOptions(&$string, $name, $alias = '')
	{
		RegEx::match(
			'(<script type="application/json" class="joomla-script-options new">)(.*?)(</script>)',
			$string,
			$match
		);

		if (empty($match))
		{
			return;
		}

		$alias = $alias ?: Extension::getAliasByName($name);

		$scripts = json_decode($match[2]);

		if ( ! isset($scripts->{'rl_' . $alias}))
		{
			return;
		}

		unset($scripts->{'rl_' . $alias});

		$string = str_replace(
			$match[0],
			$match[1] . json_encode($scripts) . $match[3],
			$string
		);
	}

	/**
	 * Remove style/css blocks from html string
	 *
	 * @param string $string
	 * @param string $name
	 * @param string $alias
	 */
	public static function removeScriptsStyles(&$string, $name, $alias = '')
	{
		[$start, $end] = Protect::getInlineCommentTags($name, null, true);
		$alias = $alias ?: Extension::getAliasByName($name);

		$string = RegEx::replace('((?:;\s*)?)(;?)' . $start . '.*?' . $end . '\s*', '\1', $string);
		$string = RegEx::replace('\s*<link [^>]*href="[^"]*/(' . $alias . '/css|css/' . $alias . ')/[^"]*\.css[^"]*"[^>]*( /)?>', '', $string);
		$string = RegEx::replace('\s*<script [^>]*src="[^"]*/(' . $alias . '/js|js/' . $alias . ')/[^"]*\.js[^"]*"[^>]*></script>', '', $string);
	}

	/**
	 * Adds a javascript declaration to the page
	 *
	 * @param string $content
	 * @param string $name
	 * @param bool   $minify
	 * @param string $type
	 */
	public static function scriptDeclaration($content = '', $name = '', $minify = true, $type = 'text/javascript')
	{
		if ($minify)
		{
			$content = self::minify($content);
		}

		if ( ! empty($name))
		{
			$content = Protect::wrapScriptDeclaration($content, $name, $minify);
		}

		self::get()->addScriptDeclaration($content, $type);
	}

	/**
	 * Minify the given string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function minify($string)
	{
		// place new lines around string to make regex searching easier
		$string = "\n" . $string . "\n";

		// Remove comment lines
		$string = RegEx::replace('\n\s*//.*?\n', '', $string);
		// Remove comment blocks
		$string = RegEx::replace('/\*.*?\*/', '', $string);
		// Remove enters
		$string = RegEx::replace('\n\s*', ' ', $string);

		// Remove surrounding whitespace
		$string = trim($string);

		return $string;
	}

	/**
	 * Adds extension options to the page
	 *
	 * @param array  $options
	 * @param string $name
	 */
	public static function scriptOptions($options = [], $name = '')
	{
		$key = 'rl_' . Extension::getAliasByName($name);
		JHtml::_('behavior.core');

		self::get()->addScriptOptions($key, $options);
	}

	/**
	 * @param string $buffer
	 *
	 * @depecated Use setComponentBuffer
	 */
	public static function setBuffer($buffer = '')
	{
		self::setComponentBuffer($buffer);
	}

	/**
	 * @param string $buffer
	 */
	public static function setComponentBuffer($buffer = '')
	{
		self::get()->setBuffer($buffer, 'component');
	}

	/**
	 * Adds a stylesheet declaration to the page
	 *
	 * @param string $content
	 * @param string $name
	 * @param bool   $minify
	 * @param string $type
	 */
	public static function styleDeclaration($content = '', $name = '', $minify = true, $type = 'text/css')
	{
		if ($minify)
		{
			$content = self::minify($content);
		}

		if ( ! empty($name))
		{
			$content = Protect::wrapStyleDeclaration($content, $name, $minify);
		}

		self::get()->addStyleDeclaration($content, $type);
	}

	/**
	 * Alias of \RegularLabs\Library\Document::style()
	 *
	 * @param string $file
	 * @param string $version
	 */
	public static function stylesheet($file, $version = '')
	{
		self::style($file, $version);
	}
}
