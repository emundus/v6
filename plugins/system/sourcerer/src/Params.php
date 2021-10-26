<?php
/**
 * @package         Sourcerer
 * @version         9.0.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\Sourcerer;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;

class Params
{
	protected static $areas   = null;
	protected static $params  = null;
	protected static $regexes = null;

	public static function get($key = '', $default = '')
	{
		if ($key != '')
		{
			return self::getByKey($key, $default);
		}

		if ( ! is_null(self::$params))
		{
			return self::$params;
		}

		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		$params = RL_Parameters::getPlugin('sourcerer');

		$params->tag = RL_PluginTag::clean($params->syntax_word);

		$params->html_tags_syntax = [['<', '>'], ['\[\[', '\]\]']];
		$params->splitter         = '<!-- START: SRC_SPLIT -->';

		$params->include_path  = str_replace('//', '/', ('/' . trim($params->include_path, ' /\\') . '/'));
		$params->user_is_admin = $user->authorise('core.admin', 1);


		self::$params = $params;

		return self::$params;
	}

	public static function getArea($type = 'default')
	{
		$areas = self::getAreaSettings();

		return $areas->{$type} ?? $areas->default;
	}

	public static function getAreaSettings()
	{
		if ( ! is_null(self::$areas))
		{
			return self::$areas;
		}

		$areas = (object) [];

		// Initialise the different enables
		$areas->default = self::getAreaDefault();

		self::$areas = $areas;

		return self::$areas;
	}

	public static function getRegex($type = 'tag')
	{
		$regexes = self::getRegexes();

		return $regexes->{$type} ?? $regexes->tag;
	}

	public static function getTagCharacters()
	{
		$params = self::get();

		if ( ! isset($params->tag_character_start))
		{
			self::setTagCharacters();
		}

		return [$params->tag_character_start, $params->tag_character_end];
	}

	public static function getTags($only_start_tags = false)
	{
		$params = self::get();

		[$tag_start, $tag_end] = self::getTagCharacters();

		$tags = [
			[
				$tag_start . $params->tag,
			],
			[
				$tag_start . '/' . $params->tag . $tag_end,
			],
		];

		return $only_start_tags ? $tags[0] : $tags;
	}

	public static function setTagCharacters()
	{
		$params = self::get();

		[self::$params->tag_character_start, self::$params->tag_character_end] = explode('.', $params->tag_characters);
	}

	private static function getAreaByType($type = 'default')
	{
	}

	private static function getAreaDefault()
	{
		$params = self::get();

		return (object) [
			'enable'         => true,
			'enable_css'     => $params->enable_css,
			'enable_js'      => $params->enable_js,
			'enable_php'     => $params->enable_php,
			'forbidden_php'  => $params->forbidden_php,
			'forbidden_tags' => $params->forbidden_tags,
		];
	}

	private static function getByKey($key, $default = '')
	{
		$params = self::get();

		return ($params->{$key} ?? null) ?: $default;
	}

	private static function getRegexes()
	{
		if ( ! is_null(self::$regexes))
		{
			return self::$regexes;
		}

		$params = self::get();

		// Tag character start and end
		[$tag_start, $tag_end] = Params::getTagCharacters();
		$tag_start = RL_RegEx::quote($tag_start);
		$tag_end   = RL_RegEx::quote($tag_end);

		$pre  = RL_PluginTag::getRegexSurroundingTagPre();
		$post = RL_PluginTag::getRegexSurroundingTagPost();

		$spaces = RL_PluginTag::getRegexSpaces('*');

		self::$regexes = (object) [];

		self::$regexes->tag = '('
			. '(?<start_pre>' . $pre . ')'
			. $tag_start . RL_RegEx::quote($params->tag) . $spaces . '(?<data>( .*?)?)' . $tag_end
			. '(?<start_post>' . $post . ')'

			. '(?<content>.*?)'

			. '(?<end_pre>' . $pre . ')'
			. $tag_start . '\/' . RL_RegEx::quote($params->tag) . $tag_end
			. '(?<end_post>' . $post . ')'
			. ')';

		return self::$regexes;
	}
}
