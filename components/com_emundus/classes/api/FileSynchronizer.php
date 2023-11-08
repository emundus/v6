<?php
/**
 * @package       com_emundus
 * @subpackage    api
 * @author        eMundus.fr
 * @copyright (C) 2022 eMundus SOFTWARE. All rights reserved.
 * @license       GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace classes\api;

use EmundusModelEmails;
use GuzzleHttp\Client as GuzzleClient;
use JComponentHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use JLog;

defined('_JEXEC') or die('Restricted access');

require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'emails.php');

class FileSynchronizer
{
	/**
	 * @var string $type of api used
	 */
	public $type = 'ged';

	/**
	 * @var array $auth
	 */
	private $auth = array();

	/**
	 * @var array $headers
	 */
	private $headers = array();

	/**
	 * @var string $baseUrl
	 */
	private $baseUrl = '';

	/**
	 * @var string $authenticationUrl
	 */
	private $authenticationUrl = '';


	/**
	 * @var string $coreUrl
	 */
	private $coreUrl = '';

	/**
	 * @var string $modelUrl
	 */
	private $modelUrl = '';

	/**
	 * @var string $searchUrl
	 */
	private $searchUrl = '';

	/**
	 * @var string $emundusRootDirectory
	 */
	private $emundusRootDirectory = '';

	/**
	 * @param $client GuzzleClient
	 */
	private $client = null;

	private $db;

	public function __construct($type = 'ged')
	{
		JLog::addLogger(['text_file' => 'com_emundus.sync.php'], JLog::ERROR, 'com_emundus.sync');

		$this->db = Factory::getDbo();

		$this->setType($type);
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = $type;
		$this->setConfigFromType();
	}

	private function setConfigFromType()
	{
		$this->setAuth();
		$this->setHeaders();
		$this->setBaseUrl();
		$this->setUrls();
		$client       = new GuzzleClient([
			'base_uri' => $this->getBaseUrl(),
			'headers'  => $this->getHeaders()
		]);
		$this->client = $client;
		$this->setEmundusRootDirectory();
	}

	private function setAuth()
	{
		$config = ComponentHelper::getParams('com_emundus');

		switch ($this->type) {
			case 'ged':
				$this->auth['consumer_key']    = $config->get('external_storage_ged_alfresco_user');
				$this->auth['consumer_secret'] = $config->get('external_storage_ged_alfresco_password');
				break;
			default:
				break;
		}
	}

	private function setHeaders()
	{
		$this->headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);
	}

	private function getHeaders(): array
	{
		return $this->headers;
	}

	private function setBaseUrl()
	{
		$config = ComponentHelper::getParams('com_emundus');

		switch ($this->type) {
			case 'ged':
				$this->baseUrl = $config->get('external_storage_ged_alfresco_base_url');
				break;
			default:
				break;
		}
	}

	private function getBaseUrl(): string
	{
		return $this->baseUrl;
	}

	private function setUrls()
	{
		switch ($this->type) {
			case 'ged':
				$this->authenticationUrl = 'alfresco/api/-default-/public/authentication/versions/1';
				$this->coreUrl           = 'alfresco/api/-default-/public/alfresco/versions/1';
				$this->modelUrl          = 'alfresco/api/-default-/public/alfresco/versions/1';
				$this->searchUrl         = 'alfresco/api/-default-/public/search/versions/1';
				break;
			default:
				break;
		}
	}

	public function getEmundusRootDirectory()
	{
		if (empty($this->emundusRootDirectory)) {
			// search emundus root directory in bdd
			$query = $this->db->getQuery(true);

			$query->select('params')
				->from('#__emundus_setup_sync')
				->where('type = ' . $this->db->quote($this->type));
			$this->db->setQuery($query);

			try {
				$params                     = $this->db->loadResult();
				$params                     = json_decode($params, true);
				$this->emundusRootDirectory = !empty($params['emundus_root_directory']) ? $params['emundus_root_directory'] : '';
			}
			catch (\Exception $e) {
				JLog::add('Error getting sync type params : ' . preg_replace("/[\r\n]/", " ", $query->__toString() . ' -> ' . $e->getMessage()), JLog::ERROR, 'com_emundus.sync');
				$this->emundusRootDirectory = '';
			}

			if (!empty($this->emundusRootDirectory) && !$this->checkEmundusRootDirectoryExists($this->emundusRootDirectory)) {
				$this->emundusRootDirectory = '';
			}
		}

		return $this->emundusRootDirectory;
	}

	private function setEmundusRootDirectory()
	{
		$this->getEmundusRootDirectory();

		if (empty($this->emundusRootDirectory)) {
			switch ($this->type) {
				case 'ged':
					$documentLibrary = $this->getGEDDocumentLibrary();
					if (!empty($documentLibrary)) {
						$found = $this->getGEDEmundusRootDirectory($documentLibrary);

						if (!$found) {
							$response = $this->createFolder($documentLibrary, array(
								'name'     => 'EMUNDUS',
								'nodeType' => 'cm:folder',
							));

							if (!empty($response)) {
								$this->emundusRootDirectory = $response->entry->id;
								$this->saveEmundusRootDirectory();
							}
						}
					}
					break;
				default:
					break;
			}
		}
	}

	private function checkEmundusRootDirectoryExists($id)
	{
		$exists = false;
		switch ($this->type) {
			case 'ged':
				$response = $this->get($this->coreUrl . "/nodes/" . $id);
				$exists   = !empty($response->entry->id);
				break;
			default:
				break;
		}

		return $exists;
	}

	private function getGEDDocumentLibrary()
	{
		$documentLibrary = '';
		$eMConfig        = ComponentHelper::getParams('com_emundus');

		$site     = $eMConfig->get('external_storage_ged_alfresco_site');
		$response = $this->get($this->coreUrl . "/sites/$site/containers");

		if (!empty($response->list) && !empty($response->list->entries)) {
			foreach ($response->list->entries as $entry) {
				if ($entry->entry->folderId == 'documentLibrary') {
					$documentLibrary = $entry->entry->id;
				}
			}
		}

		return $documentLibrary;
	}

	private function getGEDEmundusRootDirectory($parentId): bool
	{
		$found = false;

		// get children
		$response = $this->get($this->coreUrl . "/nodes/$parentId/children");
		if (!empty($response->list) && !empty($response->list->entries)) {

			foreach ($response->list->entries as $entry) {
				// check if properties custom:author is EMUNDUS

				if ($entry->entry->name == 'EMUNDUS') {
					$this->emundusRootDirectory = $entry->entry->id;
					$this->saveEmundusRootDirectory();
					$found = true;
					break;
				}
			}
		}

		return $found;
	}

	private function saveEmundusRootDirectory()
	{
		$saved = false;

		if (!empty($this->emundusRootDirectory)) {
			$query = $this->db->getQuery(true);

			try {
				$query->select('params')
					->from('#__emundus_setup_sync')
					->where("type = " . $this->db->quote($this->type));
				$this->db->setQuery($query);
				$params = $this->db->loadResult();
				$params = json_decode($params);

				$params->emundus_root_directory = $this->emundusRootDirectory;
				$params                         = json_encode($params);

				$query->clear()
					->update('#__emundus_setup_sync')
					->set('params = ' . $this->db->quote($params))
					->where("type = " . $this->db->quote($this->type));
				$this->db->setQuery($query);

				$saved = $this->db->execute();
			}
			catch (\Exception $e) {
				JLog::add('Failed to save eMundus root directory config ' . $e->getMessage(), JLog::ERROR, 'com_emundus.sync');
			}
		}
		else {
			JLog::add('Tried to save emundus root directory, but  emundusRootDirectory is empty', JLog::WARNING, 'com_emundus.sync');
		}

		return $saved;
	}

	private function post($url, $params = array())
	{
		try {
			$response = $this->client->post($url, [
				'auth' => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
				'json' => $params
			]);

			return json_decode($response->getBody());
		}
		catch (\Exception $e) {
			JLog::add('[POST] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.sync');

			return $e->getMessage();
		}
	}

	private function postFormData($url, $params = array())
	{
		try {
			$response = $this->client->post($url, [
				'auth'      => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
				'multipart' => $params
			]);

			return json_decode($response->getBody());
		}
		catch (\Exception $e) {
			JLog::add('[POST-multipart] : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.sync');

			return $e->getMessage();
		}
	}

	private function get($url, $params = array())
	{
		try {
			$response = $this->client->get($url, [
				'auth'  => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
				'query' => $params
			]);

			return json_decode($response->getBody());
		}
		catch (\Exception $e) {
			JLog::add('[GET] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.sync');

			return $e->getMessage();
		}
	}

	private function delete($url, $params = array())
	{
		try {
			$response = $this->client->delete($url, [
				'auth'  => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
				'query' => $params
			]);

			return $response->getStatusCode();
		}
		catch (\Exception $e) {
			$type = $e->getCode() == 404 ? JLog::WARNING : JLog::ERROR; // 404 means the file does not exist, thus we can ignore it
			JLog::add('[DELETE] ' . $e->getMessage(), $type, 'com_emundus.sync');

			return $e->getCode();
		}
	}

	private function put($url, $params = array())
	{
		try {
			$response = $this->client->put($url, [
				'auth'    => [$this->auth['consumer_key'], $this->auth['consumer_secret']],
				'body'    => json_encode($params),
				'headers' => ['Content-Type' => 'application/json']
			]);

			return $response->getBody();
		}
		catch (\Exception $e) {
			JLog::add('[PUT] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.sync');

			return $e->getMessage();
		}
	}


	public function addFile($upload_id)
	{
		$saved = false;

		$query = $this->db->getQuery(true);

		$query->select('sync')
			->from('#__emundus_setup_attachments')
			->leftJoin('#__emundus_uploads ON #__emundus_uploads.attachment_id = #__emundus_setup_attachments.id')
			->where('#__emundus_uploads.id = ' . $this->db->quote($upload_id));
		$this->db->setQuery($query);
		$sync = $this->db->loadResult();

		if (!empty($sync)) {
			$relativePaths = $this->getRelativePaths();

			foreach ($relativePaths as $relativePath) {
				$path = $this->replaceTypes($relativePath, $upload_id);

				if (empty($path)) {
					JLog::add('Could not rewrite path for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus.sync');
					continue;
				}

				if (substr($path, -1) == '/') {
					$path = substr($path, 0, -1);
				}
				$filepath = $this->getFilePath($upload_id);
				$filename = $this->getFileName($upload_id, $path);

				if (empty($filepath) || empty($filename)) {
					JLog::add('Could not get filepath or filename for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus.sync');
					continue;
				}

				switch ($this->type) {
					case 'ged':
						$saved = $this->addGEDFile($upload_id, $filename, $filepath, $path);
						break;
					default:
						break;
				}
			}
		}

		return $saved;
	}

	public function addGEDFile($upload_id, $filename, $filepath, $relativePath)
	{
		$saved = false;

		if (empty($this->emundusRootDirectory)) {
			$this->setEmundusRootDirectory();
		}

		if (!empty($this->emundusRootDirectory)) {
			$ext          = pathinfo($filepath, PATHINFO_EXTENSION);
			$file_pointer = fopen($filepath, 'r');

			if ($file_pointer) {
				$params     = array(
					array(
						'name'     => 'name',
						'contents' => $filename . '.' . $ext
					),
					array(
						'name'     => 'nodeType',
						'contents' => 'cm:content',
					),
					array(
						'name'     => 'relativePath',
						'contents' => $relativePath,
					),
					array(
						'name'     => 'filedata',
						'contents' => $file_pointer,
					),
				);
				$properties = $this->getGEDProperties($upload_id);
				foreach ($properties as $key => $property) {
					$params[] = array(
						'name'     => $key,
						'contents' => $property
					);
				}

				$response = $this->postFormData($this->coreUrl . "/nodes/$this->emundusRootDirectory/children", $params);

				fclose($filepath);

				if (!empty($response->entry)) {
					$saved = $this->saveNodeId($upload_id, $response->entry->id, $relativePath . '/' . $filename);

					if (!$saved) {
						JLog::add('Could not save node id for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus.sync');
					}
				}
				else {
					JLog::add('Could not add file for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus.sync');
				}
			}
			else {
				JLog::add('Could not open file for upload_id ' . $upload_id . ' with file name :' . $filename . ' and file path : ' . $filepath, JLog::ERROR, 'com_emundus.sync');
			}
		}
		else {
			JLog::add('Could not post request, empty root directory, ' . $upload_id . ' with file name :' . $filename . ' and file path : ' . $filepath, JLog::ERROR, 'com_emundus.sync');
		}

		return $saved;
	}

	public function updateFile($upload_id)
	{
		$updated = false;
		$deleted = false;
		$nodeId  = $this->getNodeId($upload_id);

		if (!empty($nodeId)) {
			$deleted = $this->deleteFile($upload_id);
		}

		if (empty($nodeId) || $deleted) {
			$updated = $this->addFile($upload_id);
		}

		return $updated;
	}

	public function deleteFile($upload_id): bool
	{
		$nodeId = $this->getNodeId($upload_id);

		if (!empty($nodeId)) {
			$response_code = $this->delete($this->coreUrl . "/nodes/$nodeId");

			// 404 means the node doesn't exist
			if ($response_code == 204 || $response_code == 404) {
				$query = $this->db->getQuery(true);

				$query->delete('#__emundus_uploads_sync')
					->where('node_id = ' . $this->db->quote($nodeId));
				$this->db->setQuery($query);
				$this->db->execute();

				return true;
			}
			else {
				JLog::add('Could not delete file for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus.sync');
			}
		}
		else {
			JLog::add('Could not get node id for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus.sync');
		}

		return false;
	}

	public function checkFileExists($upload_id): bool
	{
		$exists = false;

		switch ($this->type) {
			case 'ged':
				$nodeId = $this->getNodeId($upload_id);
				if (!empty($nodeId)) {
					$response = $this->get($this->coreUrl . "/nodes/$nodeId");

					if (!empty($response->entry->id)) {
						$exists = true;
					}
				}
				break;
			default:
				break;
		}

		// update upload sync state
		$query = $this->db->getQuery(true);

		$state = $exists ? 1 : 0;
		$query->update('#__emundus_uploads_sync')
			->set('state = ' . $this->db->quote($state))
			->where('upload_id = ' . $this->db->quote($upload_id));

		$this->db->setQuery($query);

		try {
			$this->db->execute();
		}
		catch (\Exception $e) {
			JLog::add('Could not update upload sync state for upload_id ' . $upload_id, JLog::ERROR, 'com_emundus.sync');
		}

		return $exists;
	}

	private function getRelativePaths(): array
	{
		$paths = array();

		$query = $this->db->getQuery(true);

		$query->select('config')
			->from('#__emundus_setup_sync')
			->where("type = " . $this->db->quote($this->type));
		$this->db->setQuery($query);

		try {
			$config = $this->db->loadResult();
			$config = json_decode($config);

			$tree = $config->tree;

			foreach ($tree as $node) {
				$paths[] = $this->createPathsFromTree($node);
			}
		}
		catch (\Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus.sync');
		}

		return $paths;
	}

	private function createPathsFromTree($node)
	{
		if (empty($node->type) && empty($node->children)) {
			return '';
		}

		$path = $node->type;

		if (!empty($node->childrens)) {
			foreach ($node->childrens as $child) {
				$path .= '/' . $this->createPathsFromTree($child);
			}
		}

		return $path;
	}

	private function replaceTypes($path, $upload_id)
	{
		$unchangedPath = $path;
		$userId        = $this->getApplicantId($upload_id);
		$fnum          = $this->getFnum($upload_id);

		if (!empty($userId) && !empty($fnum)) {
			if (class_exists('EmundusModelEmails')) {
				$post = [
					'FNUM'           => $fnum,
					'DOCUMENT_TYPE'  => $this->getDocumentType($upload_id),
					'CAMPAIGN_LABEL' => $this->getCampaignLabel($upload_id),
					'CAMPAIGN_YEAR'  => $this->getCampaignYear($upload_id),
				];

				$m_emails = new EmundusModelEmails();
				$tags     = $m_emails->setTags($userId, $post, $fnum, '', $path);

				foreach ($tags['patterns'] as $key => $pattern) {
					$tags['patterns'][$key] = str_replace(array('/', '\\'), '', $pattern);
				}

				$path = str_replace($tags['patterns'], $tags['replacements'], $path);
			}
			else {
				JLog::add('EmundusModelEmails class not found', JLog::ERROR, 'com_emundus.sync');
			}
		}

		if ($path == $unchangedPath) {
			JLog::add('Could not replace types for path ' . $path, JLog::ERROR, 'com_emundus.sync');

			return false;
		}

		return $path;
	}

	public function createFolder($parentNodeId, $params)
	{
		return $this->post($this->coreUrl . "/nodes/$parentNodeId/children", $params);
	}

	private function getNodeId($upload_id)
	{
		$query = $this->db->getQuery(true);

		$query->select('node_id')
			->from('#__emundus_uploads_sync')
			->where("upload_id = " . $this->db->quote($upload_id));
		$this->db->setQuery($query);

		try {
			return $this->db->loadResult();
		}
		catch (\Exception $e) {
			JLog::add($e->getMessage(), JLog::ERROR, 'com_emundus.sync');

			return '';
		}
	}

	private function saveNodeId($upload_id, $node_id, $path)
	{
		$saved   = false;
		$sync_id = $this->getSyncId();

		if ($sync_id == -1) {
			return $saved;
		}

		$query = $this->db->getQuery(true);

		$query->insert('#__emundus_uploads_sync')
			->columns('upload_id, sync_id, state, relative_path, node_id')
			->values($upload_id . ', ' . $sync_id . ', ' . $this->db->quote('1') . ', ' . $this->db->quote($path) . ', ' . $this->db->quote($node_id));

		$this->db->setQuery($query);

		try {
			$saved = $this->db->execute();
		}
		catch (\Exception $e) {
			$app = Factory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
			JLog::add('Error inserting into #__emundus_uploads_sync: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return $saved;
	}

	private function updateNodeId($upload_id, $node_id, $params)
	{
		$updated = false;

		$query = $this->db->getQuery(true);

		$query->update('#__emundus_uploads_sync')
			->set('node_id = ' . $this->db->quote($node_id))
			->where("upload_id = " . $this->db->quote($upload_id));

		if (!empty($params)) {
			foreach ($params as $key => $value) {
				$query->set($key . ' = ' . $this->db->quote($value));
			}
		}

		$this->db->setQuery($query);

		try {
			$updated = $this->db->execute();
		}
		catch (\Exception $e) {
			$app = Factory::getApplication();
			$app->enqueueMessage($e->getMessage(), 'error');
			JLog::add('Error updating #__emundus_uploads_sync: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return $updated;
	}

	private function getSyncId()
	{
		$sync_id = -1;

		$query = $this->db->getQuery(true);

		$query->select('id')
			->from('#__emundus_setup_sync')
			->where("type = " . $this->db->quote($this->type));
		$this->db->setQuery($query);

		try {
			$sync_id = $this->db->loadResult();
		}
		catch (\Exception $e) {
			JLog::add('Error getting sync_id: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return $sync_id;
	}

	private function getFileName($upload_id, $path)
	{
		$query = $this->db->getQuery(true);

		$query->select('config')
			->from('#__emundus_setup_sync')
			->where("type = " . $this->db->quote($this->type));
		$this->db->setQuery($query);
		$config = $this->db->loadResult();
		$config = json_decode($config);

		$filename = $this->replaceTypes($config->name, $upload_id);

		if (empty($filename)) {
			return false;
		}

		$filename = str_replace(' ', '', $filename);
		$filename = str_replace(['/', '\\'], '', $filename);

		// check if filename already exists
		$query->clear()
			->select('relative_path')
			->from('#__emundus_uploads_sync')
			->where("relative_path LIKE " . $this->db->quote($path . '/' . $filename . '%'));

		$this->db->setQuery($query);

		try {
			$existing_files = $this->db->loadColumn();

			if (in_array($path . '/' . $filename, $existing_files)) {
				$filename = $this->getUniqueFileName($filename, $path, $existing_files);
			}
		}
		catch (\Exception $e) {
			JLog::add('Error getting existing files: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return $filename;
	}

	private function getUniqueFileName($filename, $path, $existing_files)
	{
		$i    = 1;
		$name = $filename;
		while (in_array($path . '/' . $filename, $existing_files)) {
			$filename = $name . '_' . $i;
			$i++;
		}

		return $filename;
	}

	private function getFnum($upload_id): string
	{
		$fnum  = '';
		$query = $this->db->getQuery(true);

		$query->select('fnum')
			->from('#__emundus_uploads')
			->where("id = " . $this->db->quote($upload_id));
		$this->db->setQuery($query);

		try {
			$fnum = $this->db->loadResult();
		}
		catch (\Exception $e) {
			JLog::add('Error getting fnum: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return $fnum;
	}

	private function getFilePath($upload_id): string
	{
		$filePath = "";
		$user     = $this->getApplicantId($upload_id);

		$query = $this->db->getQuery(true);

		$query->select('filename')
			->from('#__emundus_uploads')
			->where('id = ' . $this->db->quote($upload_id));
		$this->db->setQuery($query);

		try {
			$filename = $this->db->loadResult();
			$filePath = JPATH_BASE . DS . EMUNDUS_PATH_REL . DS . $user . DS . $filename;
		}
		catch (\Exception $e) {
			JLog::add('Error getting filename: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return $filePath;
	}

	private function getApplicantId($upload_id): string
	{
		$applicant_id = '';

		$query = $this->db->getQuery(true);

		$query->select('applicant_id')
			->from('#__emundus_campaign_candidature')
			->leftJoin('#__emundus_uploads ON #__emundus_campaign_candidature.fnum = #__emundus_uploads.fnum')
			->where('#__emundus_uploads.id = ' . $this->db->quote($upload_id));

		$this->db->setQuery($query);

		try {
			$applicant_id = $this->db->loadResult();
		}
		catch (\Exception $e) {
			JLog::add('Error getting applicant_id: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return trim($applicant_id);
	}

	private function getCampaignLabel($upload_id): string
	{
		$campaign_label = '';

		$query = $this->db->getQuery(true);

		$query->select('#__emundus_setup_campaigns.label')
			->from('#__emundus_setup_campaigns')
			->leftJoin('#__emundus_campaign_candidature ON #__emundus_setup_campaigns.id = #__emundus_campaign_candidature.campaign_id')
			->leftJoin('#__emundus_uploads ON #__emundus_campaign_candidature.fnum = #__emundus_uploads.fnum')
			->where('#__emundus_uploads.id = ' . $this->db->quote($upload_id));

		$this->db->setQuery($query);

		try {
			$campaign_label = $this->db->loadResult();
		}
		catch (\Exception $e) {
			JLog::add('Error getting campaign label: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return trim($campaign_label);
	}

	private function getCampaignYear($upload_id): string
	{
		$year = '';

		$query = $this->db->getQuery(true);

		$query->select('#__emundus_setup_campaigns.year')
			->from('#__emundus_setup_campaigns')
			->leftJoin('#__emundus_campaign_candidature ON #__emundus_setup_campaigns.id = #__emundus_campaign_candidature.campaign_id')
			->leftJoin('#__emundus_uploads ON #__emundus_campaign_candidature.fnum = #__emundus_uploads.fnum')
			->where('#__emundus_uploads.id = ' . $this->db->quote($upload_id));

		$this->db->setQuery($query);

		try {
			$year = $this->db->loadResult();
		}
		catch (\Exception $e) {
			JLog::add('Error getting year: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return trim($year);
	}

	private function getDocumentType($upload_id): string
	{
		$type = "";

		$query = $this->db->getQuery(true);

		$query->select('value')
			->from('#__emundus_setup_attachments')
			->leftJoin('#__emundus_uploads ON #__emundus_uploads.attachment_id = #__emundus_setup_attachments.id')
			->where('#__emundus_uploads.id = ' . $this->db->quote($upload_id));

		$this->db->setQuery($query);
		try {
			$type = $this->db->loadResult();
		}
		catch (\Exception $e) {
			JLog::add('Error getting document type: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return trim($type);
	}

	public function getAspect($aspect_id)
	{
		$response = $this->get($this->modelUrl . '/aspects/' . $aspect_id);

		if (!empty($response)) {
			return $response->entry;
		}
		else {
			JLog::add('Error getting aspect: ' . preg_replace("/[\r\n]/", " ", $this->modelUrl . '/aspects/' . $aspect_id), JLog::ERROR, 'com_emundus.sync');

			return false;
		}
	}

	public function getAspects()
	{
		$response = $this->get($this->modelUrl . '/aspects');

		if (!empty($response)) {
			return $response->entries;
		}
		else {
			JLog::add('Error getting aspects', JLog::ERROR, 'com_emundus.sync');

			return false;
		}
	}

	private function getGEDProperties($upload_id): array
	{
		$properties = [];

		if (!empty($upload_id)) {
			$query = $this->db->getQuery(true);

			$query->select('params')
				->from('#__emundus_setup_attachments')
				->leftJoin('#__emundus_uploads ON #__emundus_uploads.attachment_id = #__emundus_setup_attachments.id')
				->where('#__emundus_uploads.id = ' . $this->db->quote($upload_id));

			$this->db->setQuery($query);

			try {
				$params = $this->db->loadResult();
			}
			catch (\Exception $e) {
				JLog::add('Error getting params: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
			}

			if (!empty($params)) {
				$params = json_decode($params);

				if (!empty($params->aspects)) {
					$params->aspects    = json_decode($params->aspects);
					$attachment_aspects = $params->aspects->default ? $this->getConfigAspects() : $params->aspects->aspects;
				}
				else {
					$attachment_aspects = $this->getConfigAspects();
				}
			}
			else {
				$attachment_aspects = $this->getConfigAspects();
			}

			foreach ($attachment_aspects as $aspect) {
				if (!empty($aspect->mapping)) {
					$query->clear()
						->select('tag')
						->from('#__emundus_setup_tags')
						->where('id = ' . $this->db->quote($aspect->mapping));

					$this->db->setQuery($query);

					try {
						$tag = $this->db->loadResult();

						if (!empty($tag)) {
							$properties[$aspect->name] = $tag;
						}
					}
					catch (\Exception $e) {
						JLog::add('Error getting tag: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
					}
				}
			}
		}

		return $this->replaceValues($properties, $upload_id);
	}

	private function getAspectNames()
	{
		$aspectNames = [];

		$query = $this->db->getQuery(true);

		$query->select('params')
			->from('#__emundus_setup_sync')
			->where('type = ' . $this->db->quote($this->type));

		$this->db->setQuery($query);

		try {
			$params = $this->db->loadResult();
			$params = json_decode($params, true);

			if (!empty($params['aspectNames'])) {
				$aspectNames = $params['aspectNames'];
			}
		}
		catch (\Exception $e) {
			JLog::add('Error getting config: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		return $aspectNames;
	}


	private function getConfigAspects(): array
	{
		$aspects = [];

		$query = $this->db->getQuery(true);

		$query->select('config')
			->from('#__emundus_setup_sync')
			->where('type = ' . $this->db->quote($this->type));

		$this->db->setQuery($query);

		try {
			$config = $this->db->loadResult();
		}
		catch (\Exception $e) {
			JLog::add('Error getting config: ' . preg_replace("/[\r\n]/", " ", $query->__toString()), JLog::ERROR, 'com_emundus.sync');
		}

		if (!empty($config)) {
			$config = json_decode($config);
			if (!empty($config->aspects)) {
				$aspects = $config->aspects;
			}
		}

		return $aspects;
	}

	private function replaceValues($values, $upload_id)
	{
		if (!empty($values) && !empty($upload_id)) {
			$userId = $this->getApplicantId($upload_id);
			$fnum   = $this->getFnum($upload_id);

			if (!empty($userId) && !empty($fnum)) {
				if (class_exists('EmundusModelEmails')) {
					$m_emails = new EmundusModelEmails();
					try {
						$tags = $m_emails->setTags($userId, [], $fnum);

						foreach ($values as $key => $value) {
							$old_value = $value;

							$new_value = str_replace($tags['patterns'], $tags['replacements'], '/\[' . $value . '\]/',);

							if ('[' . $old_value . ']' !== $new_value) {
								$values[$key] = $new_value;
							}
							else {
								unset($values[$key]);
							}
						}
					}
					catch (\Exception $e) {
						JLog::add('Error getting tags: ' . preg_replace("/[\r\n]/", " ", $e->getMessage()), JLog::ERROR, 'com_emundus.sync');
					}
				}
			}
		}

		return $values;
	}
}