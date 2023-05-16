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

	public function mapDatas($datas,$uid = 0): bool{
		$result = true;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$mapping = $this->getAttributeMapping();

		if(empty($uid)){
			$uid = JFactory::getUser()->id;
		}

		try
		{
			if(!empty($datas) && !empty($mapping['mapping'])){

				foreach($mapping['mapping'] as $map){
					$table = $map['table'];
					$user_key = $map['user_key'];

					$query->clear()
						->select('id')
						->from($db->quoteName($table))
						->where($db->quoteName($user_key) . ' = ' . $db->quote($uid));
					$db->setQuery($query);
					$exists = $db->loadResult();

					if($exists)
					{
						$query->clear()
							->update($db->quoteName($table))
							->where($db->quoteName($user_key) . ' = ' . $db->quote($uid));
					} else {
						$query->clear()
							->insert($db->quoteName($table))
							->set($db->quoteName($user_key) . ' = ' . $db->quote($uid));
					}

					foreach($map['attributes'] as $api_key => $attribute){
						if(!empty($datas->{$api_key})){
							$value =  $datas->{$api_key};

							if(isset($attribute['mapping_options'])){
								if(!empty($attribute['mapping_options'][$datas->{$api_key}])){
									$value = $attribute['mapping_options'][$datas->{$api_key}];
								}
							}

							if(!empty($value)){
								$query->set($db->quoteName($attribute['key']) . ' = ' . $db->quote($value));
							}
						}
					}

					$db->setQuery($query);
					if(!$db->execute()){
						JLog::add('Error when map Postgrest api datas with query : '.$query->__toString().' with error : '.$db->getErrorMsg(), JLog::ERROR, 'com_emundus');
						return false;
					}
				}
			}
		}
		catch (\Exception $e)
		{
			$result = false;
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $result;
	}
}