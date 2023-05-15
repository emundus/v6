<?php
/**
 * @package     com_emundus
 * @subpackage  api
 * @author    eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license    GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace classes\api;

use JComponentHelper;
use JFactory;
use JLog;

use classes\api\Api;

defined('_JEXEC') or die('Restricted access');
class PostgREST extends Api
{
	public function __construct()
	{
		parent::__construct();

		$this->setAuth();
		$this->setHeaders();
		$this->setBaseUrl();
		$this->setClient();
	}


	public function setBaseUrl(): void
	{
		$config = JComponentHelper::getParams('com_emundus');
		$this->baseUrl = $config->get('postgrest_api_base_url', '');
	}

	/**
	 * @param array $headers
	 */
	public function setHeaders(): void
	{
		$auth = $this->getAuth();

		$this->headers = array(
			'Authorization' => 'Bearer ' . $auth['bearer_token'],
		);

	}

	public function setAuth(): void
	{
		$config = JComponentHelper::getParams('com_emundus');

		$this->auth['bearer_token'] = $config->get('postgrest_api_bearer_token', '');
	}

	public function getAttributeMapping(): array
	{
		$result = array();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		try
		{
			$query->select('config')
				->from($db->quoteName('#__emundus_setup_sync'))
				->where($db->quoteName('type') . ' LIKE ' . $db->quote('postgrest'));
			$db->setQuery($query);
			$result = $db->loadResult();
			if(!empty($result)){
				$result = json_decode($result, true);
			} else {
				$result = array();
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $result;
	}
}