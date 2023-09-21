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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');
class PostgREST extends Api
{
	private $db;

	public function __construct()
	{
		parent::__construct();

		if (version_compare(JVERSION, '4.0', '>'))
		{
			$this->db = Factory::getContainer()->get('DatabaseDriver');
		} else {
			$this->db = Factory::getDbo();
		}

		$this->setAuth();
		$this->setHeaders();
		$this->setBaseUrl();
		$this->setClient();
	}

	public function setBaseUrl(): void
	{
		$config = ComponentHelper::getParams('com_emundus');
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
		$config = ComponentHelper::getParams('com_emundus');

		$this->auth['bearer_token'] = $config->get('postgrest_api_bearer_token', '');
	}

	public function getAttributeMapping(): array
	{
		$result = array();

		$query = $this->db->getQuery(true);

		try
		{
			$query->select('config')
				->from($this->db->quoteName('#__emundus_setup_sync'))
				->where($this->db->quoteName('type') . ' LIKE ' . $this->db->quote('postgrest'));
			$this->db->setQuery($query);
			$result = $this->db->loadResult();
			if(!empty($result)){
				$result = json_decode($result, true);
			} else {
				$result = array();
			}
		}
		catch (\Exception $e)
		{
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus');
		}

		return $result;
	}

	public function mapDatas($datas,$uid = 0): bool{
		$result = true;

		$query = $this->db->getQuery(true);
		$mapping = $this->getAttributeMapping();

		if(empty($uid)){
			if (version_compare(JVERSION, '4.0', '>'))
			{
				$uid = Factory::getApplication()->getIdentity()->id;
			} else {
				$uid = Factory::getUser()->id;
			}
		}

		try
		{
			if(!empty($datas) && !empty($mapping['mapping'])){

				foreach($mapping['mapping'] as $map){
					$table = $map['table'];
					$user_key = $map['user_key'];

					$query->clear()
						->select('id')
						->from($this->db->quoteName($table))
						->where($this->db->quoteName($user_key) . ' = ' . $this->db->quote($uid));
					$this->db->setQuery($query);
					$exists = $this->db->loadResult();

					if($exists)
					{
						$query->clear()
							->update($this->db->quoteName($table))
							->where($this->db->quoteName($user_key) . ' = ' . $this->db->quote($uid));
					} else {
						$query->clear()
							->insert($this->db->quoteName($table))
							->set($this->db->quoteName($user_key) . ' = ' . $this->db->quote($uid));
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
								$query->set($this->db->quoteName($attribute['key']) . ' = ' . $this->db->quote($value));
							}
						}
					}

					$this->db->setQuery($query);
					if(!$this->db->execute()){
						JLog::add('Error when map Postgrest api datas with query : '.$query->__toString().' with error : '.$this->db->getErrorMsg(), JLog::ERROR, 'com_emundus');
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