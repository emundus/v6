<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopSeoHelper {

	public function getAlias($name) {
		$alias = strip_tags($name);

		$jConfig = JFactory::getConfig();
		if (!$jConfig->get('unicodeslugs')) {
			$lang = JFactory::getLanguage();
			$alias = str_replace(',', '-', $lang->transliterate($alias));
		}

		$app = JFactory::getApplication();
		if (method_exists($app, 'stringURLSafe')) {
			$alias = $app->stringURLSafe($alias);
		} elseif (method_exists('JFilterOutput', 'stringURLUnicodeSlug')) {
			$alias = JFilterOutput::stringURLUnicodeSlug($alias);
		} else {
			$alias = JFilterOutput::stringURLSafe($alias);
		}

		return $alias;
	}

	public function autoFillKeywordMeta(&$element, $object) {
		$config =& hikashop_config();

		$max = (int)$config->get('max_size_of_metadescription', 0);

		$description = $object . '_description';
		$meta_description = $object . '_meta_description';
		$keyword = $object . '_keywords';

		if (empty($element->$description))
			return;

		if (empty($element->$meta_description)) {

			if(empty($max) || $max < 1)
				$max =  300;

			$substr = (function_exists('mb_substr')) ? 'mb_substr' : 'substr';

			$element->$meta_description = $substr($this->clean($element->$description), 0, $max);
		}

		if (empty($element->$keyword)) {
			$txt = $this->clean($element->$description);

			$words = array();
			if (preg_match_all('~\p{L}+~u', $txt, $matches) > 0) {
				foreach ($matches[0] as $w) {
					$words[$w] = isset($words[$w]) === false ? 1 : $words[$w] + 1;
				}
			}

			arsort($words);
			$i = 0;

			$max_keywords = (int)$config->get('keywords_number', 0);
			$excluded_words = explode(',', $config->get('keywords_exclusion_list', ''));

			$keywords = array();
			$strlen = (function_exists('mb_strlen')) ? 'mb_strlen' : 'strlen';

			foreach ($words as $word => $nb) {
				if ($strlen($word) < 3)
					continue;

				$skip = false;
				foreach ($excluded_words as $excluded_word) {
					if ($word == trim($excluded_word)) {
						$skip = true;
						break;
					}
				}

				if($skip == true)
					continue;

				$i++;

				if ($i > $max_keywords)
					break;

				$keywords[$i] = $word;
			}

			$element->$keyword = implode(',', $keywords);
		}
	}

	public function clean($str) {
		$str = strip_tags($str);

		if(function_exists('mb_strtolower'))
			$str = mb_strtolower($str, 'utf-8');
		else
			$str = strtolower($str);
		return $str;
	}

	public function substr($str, $start, $length = null) {
		if ($length !== null) {
			if(function_exists('mb_substr'))
				$str = mb_substr($str, $start, $length);
			else
				$str = substr($str, $start, $length);
		} else {
			if(function_exists('mb_substr'))
				$str = mb_substr($str, $start);
			else
				$str = substr($str, $start);
		}

		return $str;
	}
}
