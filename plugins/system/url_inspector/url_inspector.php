<?php
/**
 * @package    Joomla.Plugin
 * @subpackage System.redirect
 *
 * @copyright Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Plugin class for redirect handling.
 *
 * @since 1.6
 */
class PlgSystemurl_Inspector extends JPlugin
{
	private static $parameters = null;

	private static $inspector_forbidden_words = null;

	private static $objeto = null;

	private static $lang_firewall = null;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var   boolean
	 * @since 3.4
	 */
	protected $autoloadLanguage = false;

	/**
	 * The global exception handler registered before the plugin was instantiated
	 *
	 * @var   callable
	 * @since 3.6
	 */
	private static $previousExceptionHandler;

	/**
	 * Constructor.
	 *
	 * @param   object &$subject The object to observe
	 * @param   array  $config   An optional associative array of configuration settings.
	 *
	 * @since 1.6
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if (file_exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'securitycheckpro.php'))
		{
			self::$parameters = $this->load('pro_plugin');

			// Creamos un nuevo objeto para utilizar las funciones
			include_once JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'securitycheckpro' . DIRECTORY_SEPARATOR . 'securitycheckpro.php';
			self::$objeto = new plgSystemSecuritycheckpro($subject, $config);

			// Cargamos el lenguaje del sitio
			self::$lang_firewall = JFactory::getLanguage();
			self::$lang_firewall->load('com_securitycheckpro', JPATH_ADMINISTRATOR);
		}

	}
	
	
	/**
	 * Overwrite the onAfterInitialise method
	 *
	 * @since 3.0
	 */
	function onAfterInitialise()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Initialize variables
		$forbidden_words = null;

		// Get remote IP
		$remote_ip = self::get_ip();
		
		// Check if the IP already belongs to blacklist
		include_once JPATH_ADMINISTRATOR.'/components/com_securitycheckpro/library/model.php';
        $model = new SecuritycheckproModel;
		
		$aparece_lista_negra = $model->chequear_ip_en_lista($remote_ip, "blacklist");
		
		if (!$aparece_lista_negra) {

			// Get uri and url
			$uri = JUri::getInstance();
			$url = rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'query', 'fragment')));
			$url = filter_var($url, FILTER_SANITIZE_STRING);

			if ((!is_null(self::$parameters)) && (array_key_exists('write_log_inspector', self::$parameters)))
			{
				$write_log_inspector = self::$parameters['write_log_inspector'];
			}
			else
			{
				$write_log_inspector = 1;
			}

			if ((!is_null(self::$parameters)) && (array_key_exists('inspector_forbidden_words', self::$parameters)))
			{
				$inspector_forbidden_words = self::$parameters['inspector_forbidden_words'];
			}
			else
			{
				$inspector_forbidden_words = 'wp-login.php,.git,owl.prev,tmp.php,home.php,Guestbook.php,aska.cgi,default.asp,jax_guestbook.php,bbs.cg,gastenboek.php,light.cgi,yybbs.cgi,wsdl.php,wp-content,cache_aqbmkwwx.php,.suspected,seo-joy.cgi,google-assist.php,wp-main.php,sql_dump.php,xmlsrpc.php';
			}

			if ((!is_null(self::$parameters)) && (array_key_exists('action_inspector', self::$parameters)))
			{
				$action_inspector = self::$parameters['action_inspector'];
			}
			else
			{
				$action_inspector = 2;
			}

			$inspector_forbidden_words_array = explode(",", $inspector_forbidden_words);
			$found = false;
			
			foreach ($inspector_forbidden_words_array as $word)
			{
				$word = filter_var($word, FILTER_SANITIZE_STRING);

				if (!empty($word))
				{
					$found = strstr($url, $word);

					if ($found)
					{
						$forbidden_words .= $word;
						break;
					}
				}
			}
			
			// Forbidden words found; take actions
			if ($found)
			{
				// Adds IP, uri and date to url_inspector database
				$data = (object) array(
				'ip' => $remote_ip,
				'uri' => $url,
				'forbidden_words'    => $forbidden_words,
				'date_added' => JFactory::getDate()->toSql()
				);

				try
				{
					$db->insertObject('#__securitycheckpro_url_inspector_logs', $data, 'id');
				}
				catch (Exception $e)
				{
				// JErrorPage::render(new Exception(JText::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));
				}

				// Write a log (if set to do it) in Securitycheck Pro logs
				$access_attempt = self::$lang_firewall->_('COM_SECURITYCHECKPRO_CPANEL_URL_INSPECTOR_TEXT');
				$not_applicable = self::$lang_firewall->_('COM_SECURITYCHECKPRO_NOT_APPLICABLE');			
				self::$objeto->grabar_log($write_log_inspector, $remote_ip, 'URL_FORBIDDEN_WORDS', $forbidden_words, 'URL_INSPECTOR', $url, $not_applicable, '---', '---');

				// Actions
				if ($action_inspector == 1)
				{
					// Add to dynamic blacklist
					self::$objeto->actualizar_lista_dinamica($remote_ip);
				}
				elseif ($action_inspector == 2)
				{
					// Add to blacklist
					JLoader::register('SecuritycheckproModel', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'model.php');
					include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'firewallconfig.php';
					$firewallconfig_object = new SecuritycheckprosModelFirewallConfig;
					$firewallconfig_object->manage_list('blacklist', 'add', $remote_ip, false);

					// Redireccionamos para evitar que las peticiones continuen
					$error_403 = self::$lang_firewall->_('COM_SECURITYCHECKPRO_403_ERROR');
					self::$objeto->redirection(403, $error_403, true);
				}
			}
		}

	}

	// Hace una consulta a la tabla especificada como parámetro

	private function load($key_name)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select($db->quoteName('storage_value'))
			->from($db->quoteName('#__securitycheckpro_storage'))
			->where($db->quoteName('storage_key') . ' = ' . $db->quote($key_name));
		$db->setQuery($query);
		$res = $db->loadResult();

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$this->config = new JRegistry;
		}
		else
		{
			$this->config = new JRegistry('securitycheckpro');
		}

		if (!empty($res))
		{
			$res = json_decode($res, true);

			return $res;
		}
	}

	/**
	 * Method to handle an error condition from JError.
	 *
	 * @param   JException $error The JException object to be handled.
	 *
	 * @return void
	 *
	 * @since 1.6
	 */
	public static function handleError(JException $error)
	{
		self::doErrorHandling($error);
	}

	/**
	 * Method to handle an uncaught exception.
	 *
	 * @param   Exception|Throwable $exception The Exception or Throwable object to be handled.
	 *
	 * @return void
	 *
	 * @since  3.5
	 * @throws InvalidArgumentException
	 */
	public static function handleException($exception)
	{
		// If this isn't a Throwable then bail out
		if (!($exception instanceof Throwable) && !($exception instanceof Exception))
		{
			throw new InvalidArgumentException(
				sprintf('The error handler requires an Exception or Throwable object, a "%s" object was given instead.', get_class($exception))
			);
		}

		self::doErrorHandling($exception);
	}

	// Obtiene la IP remota que realiza las peticiones

	private static function get_ip()
	{
		// Inicializamos las variables
		$clientIpAddress = 'Not set';
		$ip_valid = false;
		
		// Contribution of George Acu - thanks!		
		if (isset($_SERVER['HTTP_TRUE_CLIENT_IP']))
		{
			# CloudFlare specific header for enterprise paid plan, compatible with other vendors
			$clientIpAddress = $_SERVER['HTTP_TRUE_CLIENT_IP']; 
		} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
		{
			# another CloudFlare specific header available in all plans, including the free one
			$clientIpAddress = $_SERVER['HTTP_CF_CONNECTING_IP']; 
		} elseif (isset($_SERVER['HTTP_INCAP_CLIENT_IP'])) 
		{
			// Users of Incapsula CDN
			$clientIpAddress = $_SERVER['HTTP_INCAP_CLIENT_IP']; 
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
		{
			# specific header for proxies
			$clientIpAddress = $_SERVER['HTTP_X_FORWARDED_FOR']; 
			$result_ip_address = explode(', ', $clientIpAddress);
            $clientIpAddress = $result_ip_address[0];
		} elseif (isset($_SERVER['REMOTE_ADDR']))
		{
			# this one would be used, if no header of the above is present
			$clientIpAddress = $_SERVER['REMOTE_ADDR']; 
		}

		$ip_valid = filter_var($clientIpAddress, FILTER_VALIDATE_IP);

				// Si la ip no es válida entonces devolvemos 'Not set'
		if (!$ip_valid)
		{
			$clientIpAddress = 'Not set';
		}

				// Devolvemos el resultado
		return $clientIpAddress;
	}

	/**
	 * Internal processor for all error handlers
	 *
	 * @param   Exception|Throwable $error The Exception or Throwable object to be handled.
	 *
	 * @return void
	 *
	 * @since 3.5
	 */
	private static function doErrorHandling($error)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		//Initialize variables
		$forbidden_words = null;

		// Get remote IP
		$remote_ip = self::get_ip();

		// Get uri and url
		$uri = JUri::getInstance();
		$url = rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'query', 'fragment')));
		$url = filter_var($url, FILTER_SANITIZE_STRING);

		if ((!is_null(self::$parameters)) && (array_key_exists('write_log_inspector', self::$parameters)))
		{
			$write_log_inspector = self::$parameters['write_log_inspector'];
		}
		else
		{
			$write_log_inspector = 1;
		}

		if ((!is_null(self::$parameters)) && (array_key_exists('inspector_forbidden_words', self::$parameters)))
		{
			$inspector_forbidden_words = self::$parameters['inspector_forbidden_words'];
		}
		else
		{
			$inspector_forbidden_words = 'wp-login.php,.git,owl.prev,tmp.php,home.php,Guestbook.php,aska.cgi,default.asp,jax_guestbook.php,bbs.cg,gastenboek.php,light.cgi,yybbs.cgi,wsdl.php,wp-content,cache_aqbmkwwx.php,.suspected,seo-joy.cgi,google-assist.php,wp-main.php,sql_dump.php,xmlsrpc.php';
		}

		if ((!is_null(self::$parameters)) && (array_key_exists('action_inspector', self::$parameters)))
		{
			$action_inspector = self::$parameters['action_inspector'];
		}
		else
		{
			$action_inspector = 2;
		}

		$inspector_forbidden_words_array = explode(",", $inspector_forbidden_words);
		$found = false;

		foreach ($inspector_forbidden_words_array as $word)
		{
			$word = filter_var($word, FILTER_SANITIZE_STRING);
			$found = strstr($url, $word);

			if ($found)
			{
				$forbidden_words .= $word;
				break;
			}
		}

		// Forbidden words found; take actions
		if ($found)
		{
			// Adds IP, uri and date to url_inspector database
			$data = (object) array(
			'ip' => $remote_ip,
			'uri' => $url,
			'forbidden_words'    => $forbidden_words,
			'date_added' => JFactory::getDate()->toSql()
			);

			try
			{
				$db->insertObject('#__securitycheckpro_url_inspector_logs', $data, 'id');
			}
			catch (Exception $e)
			{
			// JErrorPage::render(new Exception(JText::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));
			}

			// Write a log (if set to do it) in Securitycheck Pro logs
			$access_attempt = self::$lang_firewall->_('COM_SECURITYCHECKPRO_CPANEL_url_INSPECTOR_TEXT');
			$not_applicable = self::$lang_firewall->_('COM_SECURITYCHECKPRO_NOT_APPLICABLE');
			self::$objeto->grabar_log($write_log_inspector, $remote_ip, 'URL_FORBIDDEN_WORDS', $forbidden_words, 'URL_INSPECTOR', $url, $not_applicable, '---', '---');

			// Actions
			if ($action_inspector == 1)
			{
				// Add to dynamic blacklist
				self::$objeto->actualizar_lista_dinamica($remote_ip);
			}
			elseif ($action_inspector == 2)
			{
				// Add to blacklist
				include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_securitycheckpro' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'firewallconfig.php';
				$firewalllists_object = new SecuritycheckprosModelFirewallConfig;
				$firewalllists_object->manage_list('blacklist', 'add', $remote_ip);

				// Redireccionamos para evitar que las peticiones continuen
				$error_403 = self::$lang_firewall->_('COM_SECURITYCHECKPRO_403_ERROR');
				self::$objeto->redirection(403, $error_403, true);
			}
		}

		$urlRel = rawurldecode($uri->toString(array('path', 'query', 'fragment')));

		$urlWithoutQuery = rawurldecode($uri->toString(array('scheme', 'host', 'port', 'path', 'fragment')));
		$urlRelWithoutQuery = rawurldecode($uri->toString(array('path', 'fragment')));

		// Why is this (still) here?
		if ((strpos($url, 'mosConfig_') !== false) || (strpos($url, '=http://') !== false))
		{
			JErrorPage::render($error);
		}

		$query->select('*')
			->from($db->quoteName('#__redirect_links'))
			->where(
				'('
				. $db->quoteName('old_url') . ' = ' . $db->quote($url)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRel)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlWithoutQuery)
				. ' OR '
				. $db->quoteName('old_url') . ' = ' . $db->quote($urlRelWithoutQuery)
				. ')'
			);

		$db->setQuery($query);

		$redirect = null;

		try
		{
			$redirects = $db->loadAssocList();
		}
		catch (Exception $e)
		{
			JErrorPage::render(new Exception(JText::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));
		}

		$possibleMatches = array_unique(
			array($url, $urlRel, $urlWithoutQuery, $urlRelWithoutQuery)
		);

		foreach ($possibleMatches as $match)
		{
			if (($index = array_search($match, array_column($redirects, 'old_url'))) !== false)
			{
				$redirect = (object) $redirects[$index];

				if ((int) $redirect->published === 1)
				{
					break;
				}
			}
		}

		// A redirect object was found and, if published, will be used
		if (!is_null($redirect) && ((int) $redirect->published === 1))
		{
			if (!$redirect->header || (bool) JComponentHelper::getParams('com_redirect')->get('mode', false) === false)
			{
				$redirect->header = 301;
			}

			if ($redirect->header < 400 && $redirect->header >= 300)
			{
				$urlQuery = $uri->getQuery();

				$oldUrlParts = parse_url($redirect->old_url);

				if (empty($oldUrlParts['query']) && $urlQuery !== '')
				{
					$redirect->new_url .= '?' . $urlQuery;
				}

				$destination = JUri::isInternal($redirect->new_url) ? JRoute::_($redirect->new_url) : $redirect->new_url;

				$app->redirect($destination, (int) $redirect->header);
			}

			JErrorPage::render(new RuntimeException($error->getMessage(), $redirect->header, $error));
		}
		// No redirect object was found so we create an entry in the redirect table
		elseif (is_null($redirect))
		{
			$data = (object) array(
			'id' => 0,
			'old_url' => $url,
			'referer' => $app->input->server->getString('HTTP_REFERER', ''),
			'hits' => 1,
			'published' => 0,
			'created_date' => JFactory::getDate()->toSql()
			);

			try
			{
				$db->insertObject('#__redirect_links', $data, 'id');
			}
			catch (Exception $e)
			{
				JErrorPage::render(new Exception(JText::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));
			}
		}

		// We have an unpublished redirect object, increment the hit counter
		else
		{
			$redirect->hits += 1;

			try
			{
				$db->updateObject('#__redirect_links', $redirect, 'id');
			}
			catch (Exception $e)
			{
				JErrorPage::render(new Exception(JText::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));
			}
		}

		JErrorPage::render($error);
	}
}
