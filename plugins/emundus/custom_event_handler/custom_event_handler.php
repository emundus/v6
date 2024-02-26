<?php
/**
 * @package       eMundus
 * @version       6.6.5
 * @author        eMundus.fr
 * @copyright (C) 2019 eMundus SOFTWARE. All rights reserved.
 * @license       GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Worker;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Log\Log;
use Joomla\Utilities\ArrayHelper;

class plgEmundusCustom_event_handler extends JPlugin
{

	private $hEvents = null;

	private $_searchData = null;

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		jimport('joomla.log.log');
		JLog::addLogger(array('text_file' => 'com_emundus.custom_event_handler.php'), JLog::ALL, array('com_emundus.custom_event_handler'));

		require_once(JPATH_SITE . '/components/com_emundus/helpers/events.php');
		$this->hEvents = new EmundusHelperEvents();
	}


	function callEventHandler(string $event, array $args = null): array
	{
		$events = [];
		$codes  = [];
		$params = json_decode($this->params);

		if (!empty($params) && !empty($params->event_handlers)) {
			foreach ($params->event_handlers as $event_handler) {
				if ($event_handler->event == $event && $event_handler->published) {
					$events[] = $event_handler->event;
					$codes[]  = $event_handler->code;
				}
			}
		}

		$returned_values = [];

		if (method_exists($this->hEvents, $event)) {
			$returned_values[$event] = $this->hEvents->{$event}($args);
		}

		foreach ($events as $index => $caller_index) {
			try {
				$returned_values[$caller_index] = $this->_runPHP($codes[$index], $args);
			}
			catch (ParseError $p) {
				JLog::add('Error while running event ' . $caller_index . ' : "' . $p->getMessage() . '"', JLog::ERROR, 'com_emundus');
				continue;
			}
		}

		return $returned_values;
	}

	private function _runPHP($code = '', $data = null)
	{
		$php_result = true;

		if (class_exists('FabrikWorker')) {
			$w    = new FabrikWorker;
			$code = $w->parseMessageForPlaceHolder($code, $data);
		} else {
			$code = $this->parseMessageForPlaceHolder($code, $data);
		}

		try {
			$php_result = eval($code);
		}
		catch (ParseError $p) {
			Log::add('Error while running event ' . $code . ' : "' . $p->getMessage() . '"', JLog::ERROR, 'com_emundus');

			return false;
		}

		return $php_result;
	}

	private function parseMessageForPlaceHolder($msg, $searchData = null, $keepPlaceholders = true, $addSlashes = false, $theirUser = null, $unsafe = true)
	{
		$returnType = is_array($msg) ? 'array' : 'string';
		$messages   = (array) $msg;

		foreach ($messages as &$msg) {
			$this->parseAddSlashes = $addSlashes;

			if (!($msg == '' || is_array($msg) || \Joomla\String\StringHelper::strpos($msg, '{') === false)) {
				$msg = str_replace(array('%7B', '%7D'), array('{', '}'), $msg);

				if (is_object($searchData)) {
					$searchData = ArrayHelper::fromObject($searchData);
				}
				// Merge in request and specified search data
				$f                 = \Joomla\CMS\Filter\InputFilter::getInstance();
				$post              = $f->clean($_REQUEST, 'array');
				$this->_searchData = is_null($searchData) ? $post : array_merge($post, $searchData);

				// Enable users to use placeholder to insert session token
				$this->_searchData['JSession::getFormToken'] = \Joomla\CMS\Session\Session::getFormToken();

				// Replace with the user's data
				$msg = self::replaceWithUserData($msg);

				if (!is_null($theirUser)) {
					// Replace with a specified user's data
					$msg = self::replaceWithUserData($msg, $theirUser, 'your');
				}

				$msg = self::replaceWithGlobals($msg);

				if (!$unsafe) {
					$msg = self::replaceWithUnsafe($msg);
					$msg = self::replaceWithSession($msg);
				}

				$msg = preg_replace("/{}/", "", $msg);

				// Replace {element name} with form data
				$msg = preg_replace_callback("/{([^}\s]+(\|\|[\w|\s]+|<\?php.*\?>)*)}/i", array($this, 'replaceWithFormData'), $msg);

				if (!$keepPlaceholders) {
					$msg = preg_replace("/{[^}\s]+}/i", '', $msg);
				}
			}
		}

		return $returnType === 'array' ? $messages : ArrayHelper::getValue($messages, 0, '');
	}

	private static function replaceWithUserData($msg, $user = null, $prefix = 'my')
	{
		$app = Factory::getApplication();

		if (is_null($user))
		{
			$user = Factory::getUser();
		}

		$user->levels = $user->getAuthorisedViewLevels();

		if (is_object($user))
		{
			foreach ($user as $key => $val)
			{
				if (substr($key, 0, 1) != '_')
				{
					if (!is_object($val) && !is_array($val))
					{
						$msg = str_replace('{$' . $prefix . '->' . $key . '}', $val, $msg);
						$msg = str_replace('{$' . $prefix . '-&gt;' . $key . '}', $val, $msg);
					}
					elseif (is_array($val))
					{
						$msg = str_replace('{$' . $prefix . '->' . $key . '}', implode(',', $val), $msg);
						$msg = str_replace('{$' . $prefix . '-&gt;' . $key . '}', implode(',', $val), $msg);
					}
				}
			}
		}
		/*
		 *  $$$rob parse another users data into the string:
		 *  format: is {$their->var->email} where var is the $app->input var to search for
		 *  e.g url - index.php?owner=62 with placeholder {$their->owner->id}
		 *  var should be an integer corresponding to the user id to load
		 */
		$matches = array();
		preg_match('/{\$their-\>(.*?)}/', $msg, $matches);

		foreach ($matches as $match)
		{
			$bits   = explode('->', str_replace(array('{', '}'), '', $match));

			if (count($bits) !== 3)
			{
				continue;
			}

			$userId = $app->input->getInt(ArrayHelper::getValue($bits, 1));

			// things like user elements might be single entry arrays
			if (is_array($userId))
			{
				$userId = array_pop($userId);
			}

			if (!empty($userId))
			{
				$user = Factory::getUser($userId);
				$val  = $user->get(ArrayHelper::getValue($bits, 2));
				$msg  = str_replace($match, $val, $msg);
			}
		}

		return $msg;
	}

	private static function replaceWithGlobals($msg)
	{
		$replacements = self::globalReplacements();

		foreach ($replacements as $key => $value)
		{
			$msg = str_replace($key, $value, $msg);
		}

		return $msg;
	}

	private static function globalReplacements()
	{
		$app       = Factory::getApplication();
		$itemId    = self::itemId();
		$config    = Factory::getConfig();
		$session   = Factory::getSession();
		$token     = $session->get('session.token');

		$replacements = array(
			'{$jConfig_live_site}' => COM_FABRIK_LIVESITE,
			'{$jConfig_offset}' => $config->get('offset'),
			'{$Itemid}' => $itemId,
			'{$jConfig_sitename}' => $config->get('sitename'),
			'{$jConfig_mailfrom}' => $config->get('mailfrom'),
			'{where_i_came_from}' => $app->input->server->get('HTTP_REFERER', '', 'string'),
			'{date}' => date('Ymd'),
			'{year}' => date('Y'),
			'{mysql_date}' => date('Y-m-d H:i:s'),
			'{session.token}' => $token
		);

		foreach ($_SERVER as $key => $val)
		{
			if (!is_object($val) && !is_array($val))
			{
				$replacements['{$_SERVER->' . $key . '}']    = $val;
				$replacements['{$_SERVER-&gt;' . $key . '}'] = $val;
			}
		}

		if ($app->isClient('administrator'))
		{
			$replacements['{formview}'] = 'task=form.view';
			$replacements['{listview}'] = 'task=list.view';
			$replacements['{detailsview}'] = 'task=details.view';
		}
		else
		{
			$replacements['{formview}'] = 'view=form';
			$replacements['{listview}'] = 'view=list';
			$replacements['{detailsview}'] = 'view=details';
		}

		return array_merge($replacements, self::langReplacements());
	}

	public static function langReplacements()
	{
		$langtag   = Factory::getLanguage()->getTag();
		$lang      = str_replace('-', '_', $langtag);
		$shortlang = explode('_', $lang);
		$shortlang = $shortlang[0];
		$multilang = self::getMultiLangURLCode();

		$replacements = array(
			'{lang}' => $lang,
			'{langtag}' => $langtag,
			'{multilang}' => $multilang,
			'{shortlang}' => $shortlang,
		);

		return $replacements;
	}

	public static function replaceWithUnsafe($msg)
	{
		$replacements = self::unsafeReplacements();

		foreach ($replacements as $key => $value)
		{
			$msg = str_replace($key, $value, $msg);
		}

		return $msg;
	}

	public static function unsafeReplacements()
	{
		$config = Factory::getConfig();

		$replacements = array(
			'{$jConfig_absolute_path}' => JPATH_SITE,
			'{$jConfig_secret}' => $config->get('secret')
		);

		return $replacements;
	}

	public static function replaceWithSession($msg)
	{
		if (strstr($msg, '{$session->'))
		{
			$session   = Factory::getSession();
			$sessionData = array(
				'id' => $session->getId(),
				'token' => $session->get('session.token'),
				'formtoken' => \Joomla\CMS\Session\Session::getFormToken()
			);

			foreach ($sessionData as $key => $value)
			{
				$msg = str_replace('{$session->' . $key . '}', $value, $msg);
			}

			$msg = preg_replace_callback(
				'/{\$session-\>(.*?)}/',
				function($matches) use ($session) {
					$bits       = explode(':', $matches[1]);

					if (count($bits) > 1)
					{
						$sessionKey = $bits[1];
						$nameSpace  = $bits[0];
					}
					else
					{
						$sessionKey = $bits[0];
						$nameSpace  = 'default';
					}

					$val        = $session->get($sessionKey, '', $nameSpace);

					if (is_string($val))
					{
						return $val;
					}
					else if (is_numeric($val))
					{
						return (string) $val;
					}

					return '';
				},
				$msg
			);
		}

		return $msg;
	}

	public static function itemId($listId = null)
	{
		static $listIds = array();

		$app = Factory::getApplication();

		if (!$app->isAdmin())
		{
			// Attempt to get Itemid from possible list menu item.
			if (!is_null($listId))
			{
				if (!array_key_exists($listId, $listIds))
				{
					$db         = Factory::getDbo();
					$myLanguage = Factory::getLanguage();
					$myTag      = $myLanguage->getTag();
					$qLanguage  = !empty($myTag) ? ' AND ' . $db->q($myTag) . ' = ' . $db->qn('m.language') : '';
					$query      = $db->getQuery(true);
					$query->select('m.id AS itemId')->from('#__extensions AS e')
						->leftJoin('#__menu AS m ON m.component_id = e.extension_id')
						->where('e.name = "com_fabrik" and e.type = "component" and m.link LIKE "%listid=' . $listId . '"' . $qLanguage);
					$db->setQuery($query);

					if ($itemId = $db->loadResult())
					{
						$listIds[$listId] = $itemId;
					}
					else{
						$listIds[$listId] = false;
					}
				}
				else{
					if ($listIds[$listId] !== false)
					{
						return $listIds[$listId];
					}
				}
			}

			$itemId = (int) $app->input->getInt('itemId');

			if ($itemId !== 0)
			{
				return $itemId;
			}

			$menus = $app->getMenu();
			$menu  = $menus->getActive();

			if (is_object($menu))
			{
				return $menu->id;
			}
		}

		return null;
	}

	public static function getMultiLangURLCode()
	{
		$multiLang = false;

		if (JLanguageMultilang::isEnabled())
		{
			$lang      = Factory::getLanguage()->getTag();
			$languages = LanguageHelper::getLanguages();
			foreach ($languages as $language)
			{
				if ($language->lang_code === $lang)
				{
					$multiLang = $language->sef;
					break;
				}
			}
		}

		return $multiLang;
	}
}
