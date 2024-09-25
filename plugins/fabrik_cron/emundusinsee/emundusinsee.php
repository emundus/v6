<?php
/**
 * A cron task to email a recall to incomplet applications
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.email
 * @copyright   Copyright (C) 2015 emundus.fr - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

use GuzzleHttp\Client as GuzzleClient;

/**
 * A cron task to fill database with data from INSEE API
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.emundusinsee
 * @since       3.0
 */
class PlgFabrik_Cronemundusinsee extends PlgFabrik_Cron
{

	/**
	 * @var array $auth
	 */
	protected $auth = array();


	/**
	 * @var array $headers
	 */
	protected $headers = array();

	/**
	 * @var string $baseUrl
	 */
	protected $baseUrl = 'https://geo.api.gouv.fr';

	/**
	 * @param   GuzzleClient  $client
	 */
	protected $client = null;

	/**
	 * @var bool
	 */
	protected $retry = false;

	/**
	 * Do the plugin action
	 *
	 * @param   array  &$data  data
	 *
	 * @return  int  number of records updated
	 * @throws Exception
	 */
	public function process(&$data, &$listModel) {
		$data_affected = 0;

		$db = FabrikWorker::getDbo(true);

		$params        = $this->getParams();
		$url           = $params->get('data_source', 'communes');
		$db_table_name = $params->get('db_table_name', '');

		if (!empty($url) && !empty($db_table_name)) {
			$table_created = $this->createTable($db_table_name, $url);

			if ($table_created['status']) {
				$this->setClient();
				$response = $this->getMethod($url);
				
				if ($response['status'] == 200) {

					// Need to check primary_key if it's not `id` (ex. data_departements)
					$query = "SELECT distinct column_name 
								FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS tc
								INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS ku
								    ON tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
								    AND tc.CONSTRAINT_NAME = ku.CONSTRAINT_NAME
								    AND ku.table_name='$db_table_name';";
					$db->setQuery($query);
					$primary_key = $db->loadResult();

					$query = $db->getQuery(true);

					switch ($url) {
						case 'communes':
							$query->select('*')
								->from($db->quoteName($db_table_name));
							$db->setQuery($query);
							$existing_data = $db->loadObjectList('code');

							$columns = [
								$db->quoteName('nom'),
								$db->quoteName('code'),
								$db->quoteName('codeDepartement'),
								$db->quoteName('siren'),
								$db->quoteName('codeEpci'),
								$db->quoteName('codeRegion'),
								$db->quoteName('population'),
							];

							$codes_postaux_columns = [
								$db->quoteName('code'),
								$db->quoteName('commune'),
							];

							$communes_to_insert    = array_udiff($response['data'], $existing_data, array($this, 'compareByCode'));
							$communes_to_unpublish = array_udiff($existing_data, $response['data'], array($this, 'compareByCode'));
							$communes_to_update    = array_udiff($response['data'], $communes_to_insert, array($this, 'compareByCode'));

							foreach ($communes_to_insert as $commune) {
								$values = [
									$db->quote($commune->nom),
									$db->quote($commune->code),
									$db->quote($commune->codeDepartement),
									$db->quote($commune->siren),
									$db->quote($commune->codeEpci),
									$db->quote($commune->codeRegion),
									$db->quote($commune->population),
								];

								$query->clear()
									->insert($db->quoteName($db_table_name))
									->columns($columns)
									->values(implode(',', $values));
								$db->setQuery($query);
								if ($db->execute()) {
									$data_affected++;
									$commune_id = $db->insertid();

									foreach ($commune->codesPostaux as $code_postal) {
										$values = [
											$db->quote($code_postal),
											$db->quote($commune_id),
										];

										$query->clear()
											->insert($db->quoteName('data_communes_codes_postaux'))
											->columns($codes_postaux_columns)
											->values(implode(',', $values));
										$db->setQuery($query);
										$db->execute();
									}
								}
							}

							foreach ($communes_to_unpublish as $commune) {
								$query->clear()
									->update($db->quoteName($db_table_name))
									->set($db->quoteName('published') . ' = 0')
									->where($db->quoteName($primary_key) . ' = ' . $db->quote($commune->{$primary_key}));
								$db->setQuery($query);
								if($db->execute()) {
									$data_affected++;
								}
							}

							foreach ($communes_to_update as $commune) {

								$commune_id = $existing_data[$commune->code]->{$primary_key};
								$need_update = false;

								$query->clear()
									->update($db->quoteName($db_table_name));
								if($commune->nom != $existing_data[$commune->code]->nom) {
									$need_update = true;
									$query->set($db->quoteName('nom') . ' = ' . $db->quote($commune->nom));
								}
								if($commune->codeDepartement != $existing_data[$commune->code]->codeDepartement) {
									$need_update = true;
									$query->set($db->quoteName('codeDepartement') . ' = ' . $db->quote($commune->codeDepartement));
								}
								if($commune->siren != $existing_data[$commune->code]->siren) {
									$need_update = true;
									$query->set($db->quoteName('siren') . ' = ' . $db->quote($commune->siren));
								}
								if($commune->codeEpci != $existing_data[$commune->code]->codeEpci) {
									$need_update = true;
									$query->set($db->quoteName('codeEpci') . ' = ' . $db->quote($commune->codeEpci));
								}
								if($commune->codeRegion != $existing_data[$commune->code]->codeRegion) {
									$need_update = true;
									$query->set($db->quoteName('codeRegion') . ' = ' . $db->quote($commune->codeRegion));
								}
								if($commune->population != $existing_data[$commune->code]->population) {
									$need_update = true;
									$query->set($db->quoteName('population') . ' = ' . $db->quote($commune->population));
								}
								$query->where($db->quoteName($primary_key) . ' = ' . $db->quote($commune_id));

								if($need_update) {
									$db->setQuery($query);
									if ($db->execute()) {
										$data_affected++;
									}
								}

								$query->clear()
									->select('code')
									->from($db->quoteName('data_communes_codes_postaux'))
									->where($db->quoteName('commune') . ' = ' . $db->quote($commune_id));
								$db->setQuery($query);
								$codes_postaux = $db->loadColumn();

								if(!empty($commune->codesPostaux)) {
									$codes_postaux_to_insert    = array_diff($commune->codesPostaux, $codes_postaux);
									$codes_postaux_to_unpublish = array_diff($codes_postaux, $commune->codesPostaux);

									foreach ($codes_postaux_to_insert as $code_postal) {
										$values = [
											$db->quote($code_postal),
											$db->quote($commune_id),
										];

										$query->clear()
											->insert($db->quoteName('data_communes_codes_postaux'))
											->columns($codes_postaux_columns)
											->values(implode(',', $values));
										$db->setQuery($query);
										$db->execute();
									}

									foreach ($codes_postaux_to_unpublish as $code_postal) {
										$query->clear()
											->update($db->quoteName('data_communes_codes_postaux'))
											->set($db->quoteName('published') . ' = 0')
											->where($db->quoteName('code') . ' = ' . $db->quote($code_postal['code']))
											->where($db->quoteName('commune') . ' = ' . $db->quote($commune_id));
										$db->setQuery($query);
										$db->execute();
									}
								}
							}

							break;
						case 'departements':
							$query->select('*')
								->from($db->quoteName($db_table_name));
							$db->setQuery($query);
							$existing_data = $db->loadObjectList();

							$columns = [
								$db->quoteName('nom'),
								$db->quoteName('code'),
								$db->quoteName('codeRegion')
							];

							$departements_to_insert    = array_udiff($response['data'], $existing_data, array($this, 'compareByCode'));
							$departements_to_unpublish = array_udiff($existing_data, $response['data'], array($this, 'compareByCode'));
							$departements_to_update    = array_udiff($response['data'], $departements_to_insert, array($this, 'compareByCode'));

							foreach ($departements_to_insert as $departement) {
								$values = [
									$db->quote($departement->nom),
									$db->quote($departement->code),
									$db->quote($departement->codeRegion),
								];

								$query->clear()
									->insert($db->quoteName($db_table_name))
									->columns($columns)
									->values(implode(',', $values));
								$db->setQuery($query);
								if ($db->execute()) {
									$data_affected++;
								}
							}

							foreach ($departements_to_unpublish as $departement) {
								$query->clear()
									->update($db->quoteName($db_table_name))
									->set($db->quoteName('published') . ' = 0')
									->where($db->quoteName($primary_key) . ' = ' . $db->quote($departement->{$primary_key}));
								$db->setQuery($query);
								if($db->execute()) {
									$data_affected++;
								}
							}

							foreach ($departements_to_update as $departement) {

								$departement_id = $existing_data[$departement->code]->{$primary_key};
								$need_update = false;

								$query->clear()
									->update($db->quoteName($db_table_name));
								if($departement->nom != $existing_data[$departement->code]->nom) {
									$need_update = true;
									$query->set($db->quoteName('nom') . ' = ' . $db->quote($departement->nom));
								}
								if($departement->codeRegion != $existing_data[$departement->code]->codeRegion) {
									$need_update = true;
									$query->set($db->quoteName('codeRegion') . ' = ' . $db->quote($departement->codeRegion));
								}
								$query->where($db->quoteName($primary_key) . ' = ' . $db->quote($departement_id));

								if($need_update) {
									$db->setQuery($query);
									if ($db->execute()) {
										$data_affected++;
									}
								}
							}

							break;
						case 'regions':
							$query->select('*')
								->from($db->quoteName($db_table_name));
							$db->setQuery($query);
							$existing_data = $db->loadObjectList();

							$columns = [
								$db->quoteName('nom'),
								$db->quoteName('code'),
							];

							$regions_to_insert    = array_udiff($response['data'], $existing_data, array($this, 'compareByCode'));
							$regions_to_unpublish = array_udiff($existing_data, $response['data'], array($this, 'compareByCode'));
							$regions_to_update    = array_udiff($response['data'], $regions_to_insert, array($this, 'compareByCode'));

							foreach ($regions_to_insert as $region) {
								$values = [
									$db->quote($region->nom),
									$db->quote($region->code),
								];

								$query->clear()
									->insert($db->quoteName($db_table_name))
									->columns($columns)
									->values(implode(',', $values));
								$db->setQuery($query);
								if ($db->execute()) {
									$data_affected++;
								}
							}

							foreach ($regions_to_unpublish as $region) {
								$query->clear()
									->update($db->quoteName($db_table_name))
									->set($db->quoteName('published') . ' = 0')
									->where($db->quoteName($primary_key) . ' = ' . $db->quote($region->{$primary_key}));
								$db->setQuery($query);
								if($db->execute()) {
									$data_affected++;
								}
							}

							foreach ($regions_to_update as $region) {

								$region_id = $existing_data[$region->code]->{$primary_key};
								$need_update = false;

								$query->clear()
									->update($db->quoteName($db_table_name));
								if($region->nom != $existing_data[$region->code]->nom) {
									$need_update = true;
									$query->set($db->quoteName('nom') . ' = ' . $db->quote($region->nom));
								}
								$query->where($db->quoteName($primary_key) . ' = ' . $db->quote($region_id));

								if($need_update) {
									$db->setQuery($query);
									if ($db->execute()) {
										$data_affected++;
									}
								}
							}

							break;
						default:
							break;
					}

				}
			}
		}

		return $data_affected;
	}

	/**
	 * @return bool
	 */
	private function getRetry(): int {
		return $this->retry;
	}

	/**
	 * @param   bool  $retry
	 */
	private function setRetry($retry): void {
		$this->retry = $retry;
	}

	/**
	 * @return string
	 */
	private
	function getBaseUrl(): string {
		return $this->baseUrl;
	}

	/**
	 * @return null
	 */
	private function getClient() {
		return $this->client;
	}

	/**
	 * @param   null  $client
	 */
	private function setClient($client = null): void {
		if (empty($this->client)) {
			$this->client = new GuzzleClient([
				'base_uri' => $this->baseUrl,
				'verify'   => false
			]);
		}
		else {
			$this->client = $client;
		}
	}

	/**
	 * @return array
	 */
	private function getHeaders(): array {
		return $this->headers;
	}

	/**
	 * @return array
	 */
	private function getAuth(): array {
		return $this->auth;
	}

	private function setAuth(): void {
		$config = JComponentHelper::getParams('com_emundus');

		$this->auth['bearer_token'] = $config->get('api_bearer_token', '');
	}


	private function getMethod($url, $params = array()) {
		$response = ['status' => 200, 'message' => '', 'data' => ''];

		try {
			$url_params         = http_build_query($params);
			$url                = !empty($url_params) ? $url . '?' . $url_params : $url;
			$request            = $this->client->get($this->baseUrl . '/' . $url, ['headers' => $this->getHeaders()]);
			$response['status'] = $request->getStatusCode();
			$response['data']   = json_decode($request->getBody());
		}
		catch (\Exception $e) {
			if ($this->getRetry()) {
				$this->setRetry(false);
				$this->get($url, $params);
			}

			JLog::add('[GET] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.api');
			$response['status']  = $e->getCode();
			$response['message'] = $e->getMessage();
		}

		return $response;
	}

	private function createTable($table, $url) {
		$result = ['status' => false, 'message' => ''];

		if (empty($table)) {
			$result['message'] = 'CREATE TABLE : Please refer a database name.';

			return $result;
		}

		$columns      = [];
		$foreigns_key = [];
		switch ($url) {
			case 'communes':
				$columns = [
					[
						'name'   => 'nom',
						'type'   => 'VARCHAR',
						'length' => 255,
						'null'   => 0,
					],
					[
						'name'   => 'code',
						'type'   => 'VARCHAR',
						'length' => 5,
						'null'   => 0,
					],
					[
						'name'   => 'codeDepartement',
						'type'   => 'VARCHAR',
						'length' => 3,
						'null'   => 0,
					],
					[
						'name'   => 'siren',
						'type'   => 'VARCHAR',
						'length' => 50,
						'null'   => 1,
					],
					[
						'name'   => 'codeEpci',
						'type'   => 'VARCHAR',
						'length' => 50,
						'null'   => 1,
					],
					[
						'name'   => 'codeRegion',
						'type'   => 'VARCHAR',
						'length' => 3,
						'null'   => 1,
					],
					[
						'name' => 'population',
						'type' => 'INT',
						'null' => 1,
					],
					[
						'name'    => 'published',
						'type'    => 'TINYINT',
						'null'    => 0,
						'default' => 1
					]
				];
				break;
			case 'codes-postaux':
				$columns      = [
					[
						'name'   => 'code',
						'type'   => 'VARCHAR',
						'length' => 5,
						'null'   => 0,
					],
					[
						'name' => 'commune',
						'type' => 'INT',
						'null' => 0,
					],
					[
						'name'    => 'published',
						'type'    => 'TINYINT',
						'null'    => 0,
						'default' => 1
					],
				];
				$foreigns_key = [
					[
						'name'           => 'data_communes_fk_codes_postaux',
						'from_column'    => 'commune',
						'ref_table'      => 'data_communes',
						'ref_column'     => 'id',
						'update_cascade' => true,
						'delete_cascade' => true,
					]
				];
				break;
			case 'departements':
				$columns = [
					[
						'name'   => 'nom',
						'type'   => 'VARCHAR',
						'length' => 255,
						'null'   => 0,
					],
					[
						'name'   => 'code',
						'type'   => 'VARCHAR',
						'length' => 5,
						'null'   => 0,
					],
					[
						'name'   => 'codeRegion',
						'type'   => 'VARCHAR',
						'length' => 3,
						'null'   => 1,
					],
					[
						'name'    => 'published',
						'type'    => 'TINYINT',
						'null'    => 0,
						'default' => 1
					],
				];
				break;
			case 'regions':
				$columns = [
					[
						'name'   => 'nom',
						'type'   => 'VARCHAR',
						'length' => 255,
						'null'   => 0,
					],
					[
						'name'   => 'code',
						'type'   => 'VARCHAR',
						'length' => 5,
						'null'   => 0,
					],
					[
						'name'    => 'published',
						'type'    => 'TINYINT',
						'null'    => 0,
						'default' => 1
					],
				];
				break;
			default:
				break;
		}

		try {
			$db             = FabrikWorker::getDbo(true);
			$table_existing = $db->setQuery('SHOW TABLE STATUS WHERE Name LIKE ' . $db->quote($table))->loadResult();

			if (empty($table_existing)) {
				$query = 'CREATE TABLE ' . $table . '(';
				$query .= 'id INT AUTO_INCREMENT PRIMARY KEY';

				if (!empty($columns)) {
					foreach ($columns as $column) {
						$query_column = ',' . $column['name'];
						if (!empty($column['type'])) {
							$query_column .= ' ' . $column['type'];
						}
						else {
							$query_column .= ' VARCHAR';
						}
						if (!empty($column['length'])) {
							$query_column .= '(' . $column['length'] . ')';
						}
						if (!empty($column['default'])) {
							$query_column .= ' DEFAULT ' . $column['default'];
						}
						if ($column['null'] == 1) {
							$query_column .= ' NULL';
						}
						else {
							$query_column .= ' NOT NULL';
						}

						$query .= $query_column;
					}
				}
				if (!empty($foreigns_key)) {
					foreach ($foreigns_key as $fk) {
						if (!empty($fk['name']) && !empty($fk['from_column']) && !empty($fk['ref_table']) && !empty($fk['ref_column'])) {
							$query .= ',CONSTRAINT ' . $fk['name'] . ' FOREIGN KEY (' . $fk['from_column'] . ') REFERENCES ' . $fk['ref_table'] . '(' . $fk['ref_column'] . ')';
						}
						if (!empty($fk['update_cascade'])) {
							$query .= ' ON UPDATE CASCADE';
						}
						if (!empty($fk['delete_cascade'])) {
							$query .= ' ON DELETE CASCADE';
						}
					}
				}
				$query .= ')';
				if (!empty($comment)) {
					$query .= ' COMMENT ' . $db->quote($comment);
				}
				$db->setQuery($query);
				$result['status'] = $db->execute();

				if ($result['status'] && $url == 'communes') {
					$result = $this->createTable('data_communes_codes_postaux', 'codes-postaux');
				}
			}
			else {
				$query = "SHOW COLUMNS FROM " . $table;
				$db->setQuery($query);
				$existing_columns = $db->loadAssocList('Field');

				$columns_to_create = array_filter($columns, function ($column) use ($existing_columns) {
					return !in_array($column['name'], array_column($existing_columns, 'Field'));
				});

				foreach ($columns_to_create as $column) {
					$query = 'ALTER TABLE ' . $table . ' ADD COLUMN ' . $column['name'];

					if (!empty($column['type'])) {
						$query .= ' ' . $column['type'];
					}
					else {
						$query .= ' VARCHAR';
					}
					if (!empty($column['length'])) {
						$query .= '(' . $column['length'] . ')';
					}
					if (!empty($column['default'])) {
						$query .= ' DEFAULT ' . $column['default'];
					}
					if ($column['null'] == 1) {
						$query .= ' NULL';
					}
					else {
						$query .= ' NOT NULL';
					}

					$db->setQuery($query);
					$db->execute();
				}

				$result['status'] = true;
			}
		}
		catch (Exception $e) {
			$result['message'] = 'ADDING TABLE : Error : ' . $e->getMessage();
		}

		return $result;
	}

	private function compareByCode($a, $b): int {
		return strcmp($a->code, $b->code);
	}
}
