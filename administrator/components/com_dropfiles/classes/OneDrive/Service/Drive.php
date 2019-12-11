<?php

/*
 * Copyright 2010 Google Inc.
 * Copyright 2015 www.florisdeleeuw.nl
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/**
 * Service definition for Drive (v2).
 *
 * <p>
 * The API to interact with Drive.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://api.onedrive.com/v1.0/drive/" target="_blank">Documentation</a>
 * </p>
 *
 * @author OneDrive, Inc.
 */
class OneDrive_Service_Drive extends OneDrive_Service {

  /** View and manage the files in your OneDrive Drive. */
  const DRIVE = "https://api.onedrive.com/v1.0/drive/";

  public $about;
  public $items;
  public $changes;
  public $revisions;

  /**
   * Constructs the internal representation of the Drive service.
   *
   * @param OneDrive_Client $client
   */
  public function __construct(OneDrive_Client $client) {
    parent::__construct($client);
    $this->servicePath = 'v1.0/drive/';
    $this->version = 'v1.0';
    $this->serviceName = 'drive';

    $this->about = new OneDrive_Service_Drive_About_Resource(
            $this, $this->serviceName, 'about', array(
        'methods' => array(
            'get' => array(
                'path' => '',
                'httpMethod' => 'GET',
                'parameters' => array(
                    'select' => array(
                        'location' => 'query',
                        'type' => 'boolean',
                    ),
                    'expand' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'orderby' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'top' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'skipToken' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                ),
            ),
        )
            )
    );

    $this->changes = new OneDrive_Service_Drive_Changes_Resource(
            $this, $this->serviceName, 'changes', array(
        'methods' => array(
            'get' => array(
                'path' => 'items/{id}/view.delta',
                'httpMethod' => 'GET',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'token' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'select' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'top' => array(
                        'location' => 'query',
                        'type' => 'integer',
                    ),
                ),
            )
        )
            )
    );

    $this->links = new OneDrive_Service_Drive_Links_Resource(
            $this, $this->serviceName, 'links', array(
        'methods' => array(
            'create' => array(
                'path' => 'items/{id}/action.createLink',
                'httpMethod' => 'POST',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'type' => array(
                        'location' => 'query',
                        'type' => 'string',
                    )
                ),
            ),
        )
            )
    );

    $this->items = new OneDrive_Service_Drive_Items_Resource(
            $this, $this->serviceName, 'items', array(
        'methods' => array(
            'copy' => array(
                'path' => 'items/{id}/action.copy',
                'httpMethod' => 'POST',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'Prefer' => array(
                        'location' => 'header',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'parentReference' => array(
                        'location' => 'query',
                        'type' => 'boolean',
                    ),
                    'name' => array(
                        'location' => 'query',
                        'type' => 'string',
                    )
                ),
            ), 'delete' => array(
                'path' => 'items/{id}',
                'httpMethod' => 'DELETE',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'if-match' => array(
                        'location' => 'header',
                        'type' => 'string',
                        'required' => false,
                    )
                ),
            ), 'get' => array(
                'path' => 'items/{id}',
                'httpMethod' => 'GET',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'expand' => array(
                        'location' => 'query',
                        'type' => 'string',
                    )
                ),
            ), 'insert' => array(
                'path' => 'items/{parent_item_id}/children',
                'httpMethod' => 'POST',
                'parameters' => array(
                    'parent_item_id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    )
                ),
            ), 'upload' => array(
                'path' => 'items/{parent_item_id}:/{filename}:/upload.createSession',
                'httpMethod' => 'POST',
                'parameters' => array(
                    'filename' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'parent_item_id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'expand' => array(
                        'location' => 'query',
                        'type' => 'string',
                    )
                ),
            ), 'search' => array(
                'path' => 'items/{id}/view.search',
                'httpMethod' => 'GET',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'q' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'select' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'expand' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'orderby' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'top' => array(
                        'location' => 'query',
                        'type' => 'integer',
                    ),
                    'skipToken' => array(
                        'location' => 'query',
                        'type' => 'integer',
                    ),
                ),
            ), 'patch' => array(
                'path' => 'items/{id}',
                'httpMethod' => 'PATCH',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'expand' => array(
                        'location' => 'query',
                        'type' => 'string',
                    ),
                    'if-match' => array(
                        'location' => 'header',
                        'type' => 'string',
                        'required' => true,
                    ),
                ),
            ), 'update' => array(
                'path' => 'items/{id}',
                'httpMethod' => 'PUT',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'if-match' => array(
                        'location' => 'header',
                        'type' => 'string',
                        'required' => true,
                    ),
                ),
            ), 'download' => array(
                'path' => 'items/{id}/content',
                'httpMethod' => 'GET',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    )
                ),
            ), 'downloadthumbnail' => array(
                'path' => 'items/{id}/thumbnails/{thumb-id}/{size}/content',
                'httpMethod' => 'GET',
                'parameters' => array(
                    'id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'thumb-id' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    ),
                    'size' => array(
                        'location' => 'path',
                        'type' => 'string',
                        'required' => true,
                    )
                ),
            )
        )
            )
    );
    $this->revisions = new Onedrive_Service_Drive_Revisions_Resource(
        $this, $this->serviceName, 'revisions', array(
            'methods' => array(
                'get' => array(
                    'path' => 'items/{id}/versions/{revisionId}',
                    'httpMethod' => 'GET',
                    'parameters' => array(
                        'id' => array(
                            'location' => 'path',
                            'type' => 'string',
                            'required' => true,
                        ),
                        'revisionId' => array(
                            'location' => 'path',
                            'type' => 'string',
                            'required' => true,
                        ),
                    ),
                ),
                'list' => array(
                    'path' => 'items/{id}/versions',
                    'httpMethod' => 'GET',
                    'parameters' => array(
                        'id' => array(
                            'location' => 'path',
                            'type' => 'string',
                            'required' => true,
                        ),
                    ),
                ),
                'restore' => array(
                    'path' => 'items/{id}/versions/{revisionId}/restoreVersion',
                    'httpMethod' => 'POST',
                    'parameters' => array(
                        'id' => array(
                            'location' => 'path',
                            'type' => 'string',
                            'required' => true,
                        ),
                        'revisionId' => array(
                            'location' => 'path',
                            'type' => 'string',
                            'required' => true,
                        ),
                    ),
                )
            )
        )
    );
  }

}

/**
 * The "about" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new OneDrive_Service_Drive(...);
 *   $about = $driveService->about;
 *  </code>
 */
class OneDrive_Service_Drive_About_Resource extends OneDrive_Service_Resource {

  /**
   * Gets the information about the current user along with Drive API settings
   * (about.get)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool includeSubscribed When calculating the number of remaining
   * change IDs, whether to include public files the user has opened and shared
   * files. When set to false, this counts only change IDs for owned files and any
   * shared or public files that the user has explicitly added to a folder they
   * own.
   * @opt_param string maxChangeIdCount Maximum number of remaining change IDs to
   * count
   * @opt_param string startChangeId Change ID to start counting from when
   * calculating number of remaining change IDs
   * @return OneDrive_Service_Drive_About
   */
  public function get($optParams = array()) {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "OneDrive_Service_Drive_About");
  }

}

/**
 * The "revisions" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new DropfilesGoogle_Service_Drive(...);
 *   $revisions = $driveService->revisions;
 *  </code>
 */
class Onedrive_Service_Drive_Revisions_Resource extends Onedrive_Service_Resource
{
    /**
     * Gets a specific revision. (revisions.get)
     *
     * @param string $fileId The ID of the file.
     * @param string $revisionId The ID of the revision.
     * @param array $optParams Optional parameters.
     * @return Onedrive_Service_Drive_Revision
     */
    public function get($fileId, $revisionId, $optParams = array())
    {
        $params = array('id' => $fileId, 'revisionId' => $revisionId);
        $params = array_merge($params, $optParams);
        return $this->call('get', array($params), "Onedrive_Service_Drive_Revision");
    }

    /**
     * Lists a file's revisions. (revisions.listRevisions)
     *
     * @param string $fileId The ID of the file.
     * @param array $optParams Optional parameters.
     * @return Onedrive_Service_Drive_RevisionList
     */
    public function listRevisions($fileId, $optParams = array())
    {
        $params = array('id' => $fileId);
        $params = array_merge($params, $optParams);
        return $this->call('list', array($params), "Onedrive_Service_Drive_RevisionList");
    }

    /**
     * Restore a revision.
     *
     * @param string $fileId The ID for the file.
     * @param string $revisionId The ID for the revision.
     * @param array $optParams Optional parameters.
     * @return Onedrive_Service_Drive_Revision
     */
    public function restore($fileId, $revisionId, $optParams = array())
    {
        $params = array('id' => $fileId, 'revisionId' => $revisionId);
        $params = array_merge($params, $optParams);
        return $this->call('restore', array($params));
    }
}

class OneDrive_Service_Drive_Changes_Resource extends OneDrive_Service_Resource {

  public function get($fileId, $optParams = array()) {
    $params = array('id' => $fileId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "OneDrive_Service_Drive_Changes");
  }

}

class OneDrive_Service_Drive_Links_Resource extends OneDrive_Service_Resource {

  public function create($fileId, $postBody, $optParams = array()) {
    $params = array('id' => $fileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('create', array($params), "OneDrive_Service_Drive_Permission");
  }

}

/**
 * The "files" collection of methods.
 * Typical usage is:
 *  <code>
 *   $driveService = new OneDrive_Service_Drive(...);
 *   $files = $driveService->files;
 *  </code>
 */
class OneDrive_Service_Drive_Items_Resource extends OneDrive_Service_Resource {

  /**
   * Creates a copy of the specified file. (files.copy)
   *
   * @param string $fileId The ID of the file to copy.
   * @param OneDrive_DriveFile $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool convert Whether to convert this file to the corresponding
   * OneDrive Docs format.
   * @opt_param string ocrLanguage If ocr is true, hints at the language to use.
   * Valid values are ISO 639-1 codes.
   * @opt_param string visibility The visibility of the new file. This parameter
   * is only relevant when the source is not a native OneDrive Doc and
   * convert=false.
   * @opt_param bool pinned Whether to pin the head revision of the new copy. A
   * file can have a maximum of 200 pinned revisions.
   * @opt_param bool ocr Whether to attempt OCR on .jpg, .png, .gif, or .pdf
   * uploads.
   * @opt_param string timedTextTrackName The timed text track name.
   * @opt_param string timedTextLanguage The language of the timed text.
   * @return OneDrive_Service_Drive_Item
   */
  public function copy($fileId, OneDrive_Service_Drive_Item $postBody, $optParams = array()) {
    $params = array('id' => $fileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('copy', array($params), "OneDrive_Service_Drive_Item");
  }

  /**
   * Permanently deletes a file by ID. Skips the trash. The currently
   * authenticated user must own the file. (files.delete)
   *
   * @param string $fileId The ID of the file to delete.
   * @param array $optParams Optional parameters.
   */
  public function delete($fileId, $optParams = array()) {
    $params = array('id' => $fileId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  /**
   * Gets a file's metadata by ID. (files.get)
   *
   * @param string $fileId The ID for the file in question.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string projection This parameter is deprecated and has no
   * function.
   * @opt_param string revisionId Specifies the Revision ID that should be
   * downloaded. Ignored unless alt=media is specified.
   * @opt_param bool acknowledgeAbuse Whether the user is acknowledging the risk
   * of downloading known malware or other abusive files. Ignored unless alt=media
   * is specified.
   * @opt_param bool updateViewedDate Whether to update the view date after
   * successfully retrieving the file.
   * @return OneDrive_Service_Drive_Item
   */
  public function get($fileId, $optParams = array()) {
    $params = array('id' => $fileId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "OneDrive_Service_Drive_Item");
  }

  /**
   * Insert a new file. (files.insert)
   *
   * @param OneDrive_DriveFile $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param bool convert Whether to convert this file to the corresponding
   * OneDrive Docs format.
   * @opt_param bool useContentAsIndexableText Whether to use the content as
   * indexable text.
   * @opt_param string ocrLanguage If ocr is true, hints at the language to use.
   * Valid values are ISO 639-1 codes.
   * @opt_param string visibility The visibility of the new file. This parameter
   * is only relevant when convert=false.
   * @opt_param bool pinned Whether to pin the head revision of the uploaded file.
   * A file can have a maximum of 200 pinned revisions.
   * @opt_param bool ocr Whether to attempt OCR on .jpg, .png, .gif, or .pdf
   * uploads.
   * @opt_param string timedTextTrackName The timed text track name.
   * @opt_param string timedTextLanguage The language of the timed text.
   * @return OneDrive_Service_Drive_Item
   */
  public function insert($parent_folder_id, OneDrive_Service_Drive_Item $entry, $optParams = array()) {
    $params = array('parent_item_id' => $parent_folder_id, 'postBody' => $entry);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "OneDrive_Service_Drive_Item");
  }

  public function upload($filename, $parent_item_id, $postBody, $optParams = array()) {
    $params = array('filename' => $filename, 'parent_item_id' => $parent_item_id, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('upload', array($params), "OneDrive_Service_Drive_Item");
  }

  /**
   * Lists the user's files. (files.listFiles)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string q Query string for searching files.
   * @opt_param string pageToken Page token for files.
   * @opt_param string corpus The body of items (files/documents) to which the
   * query applies.
   * @opt_param string projection This parameter is deprecated and has no
   * function.
   * @opt_param int maxResults Maximum number of files to return.
   * @return OneDrive_Service_Drive_FileList
   */
  public function search($optParams = array()) {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('search', array($params), "OneDrive_Service_Drive_FileList");
  }

  /**
   * Updates file metadata and/or content. This method supports patch semantics.
   * (files.patch)
   *
   * @param string $fileId The ID of the file to update.
   * @param OneDrive_DriveFile $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string addParents Comma-separated list of parent IDs to add.
   * @opt_param bool updateViewedDate Whether to update the view date after
   * successfully updating the file.
   * @opt_param string removeParents Comma-separated list of parent IDs to remove.
   * @opt_param bool setModifiedDate Whether to set the modified date with the
   * supplied modified date.
   * @opt_param bool convert Whether to convert this file to the corresponding
   * OneDrive Docs format.
   * @opt_param bool useContentAsIndexableText Whether to use the content as
   * indexable text.
   * @opt_param string ocrLanguage If ocr is true, hints at the language to use.
   * Valid values are ISO 639-1 codes.
   * @opt_param bool pinned Whether to pin the new revision. A file can have a
   * maximum of 200 pinned revisions.
   * @opt_param bool newRevision Whether a blob upload should create a new
   * revision. If false, the blob data in the current head revision is replaced.
   * If true or not set, a new blob is created as head revision, and previous
   * revisions are preserved (causing increased use of the user's data storage
   * quota).
   * @opt_param bool ocr Whether to attempt OCR on .jpg, .png, .gif, or .pdf
   * uploads.
   * @opt_param string timedTextLanguage The language of the timed text.
   * @opt_param string timedTextTrackName The timed text track name.
   * @return OneDrive_Service_Drive_Item
   */
  public function patch($fileId, $postBody, $optParams = array()) {
    $params = array('id' => $fileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "OneDrive_Service_Drive_Item");
  }

  /**
   * Updates file metadata and/or content. (files.update)
   *
   * @param string $fileId The ID of the file to update.
   * @param OneDrive_DriveFile $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string addParents Comma-separated list of parent IDs to add.
   * @opt_param bool updateViewedDate Whether to update the view date after
   * successfully updating the file.
   * @opt_param string removeParents Comma-separated list of parent IDs to remove.
   * @opt_param bool setModifiedDate Whether to set the modified date with the
   * supplied modified date.
   * @opt_param bool convert Whether to convert this file to the corresponding
   * OneDrive Docs format.
   * @opt_param bool useContentAsIndexableText Whether to use the content as
   * indexable text.
   * @opt_param string ocrLanguage If ocr is true, hints at the language to use.
   * Valid values are ISO 639-1 codes.
   * @opt_param bool pinned Whether to pin the new revision. A file can have a
   * maximum of 200 pinned revisions.
   * @opt_param bool newRevision Whether a blob upload should create a new
   * revision. If false, the blob data in the current head revision is replaced.
   * If true or not set, a new blob is created as head revision, and previous
   * revisions are preserved (causing increased use of the user's data storage
   * quota).
   * @opt_param bool ocr Whether to attempt OCR on .jpg, .png, .gif, or .pdf
   * uploads.
   * @opt_param string timedTextLanguage The language of the timed text.
   * @opt_param string timedTextTrackName The timed text track name.
   * @return OneDrive_Service_Drive_Item
   */
  public function update($fileId, OneDrive_Service_Drive_Item $postBody, $optParams = array()) {
    $params = array('id' => $fileId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "OneDrive_Service_Drive_Item");
  }

  public function download($fileId, $optParams = array()) {
    $params = array('id' => $fileId);
    $params = array_merge($params, $optParams);
    return $this->call('download', array($params));
  }

  public function downloadthumbnail($fileId, $thumbId, $thumbsize, $optParams = array()) {
    $params = array('id' => $fileId, 'thumb-id' => $thumbId, 'size' => $thumbsize);
    $params = array_merge($params, $optParams);
    return $this->call('downloadthumbnail', array($params));
  }

}

class OneDrive_Service_Drive_About extends OneDrive_Collection {

  public $id;
  public $driveType;
  protected $ownerType = 'OneDrive_Service_Drive_IdentitySet';
  protected $ownerDataType = 'array';
  protected $quotaType = 'OneDrive_Service_Drive_AboutQuota';
  protected $quotaDataType = 'array';

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }

  public function setOwner($owner) {
    $this->owner = $owner;
  }

  public function getOwner() {
    return $this->owner;
  }

  public function setDriveType($driveType) {
    $this->driveType = $driveType;
  }

  public function getDriveType() {
    return $this->driveType;
  }

  public function setQuota($quota) {
    $this->quota = $quota;
  }

  public function getQuota() {
    return $this->quota;
  }

}

class OneDrive_Service_Drive_AboutQuota extends OneDrive_Model {

  protected $internal_gapi_mappings = array();
  public $deleted;
  public $remaining;
  public $state;
  public $total;
  public $used;

  public function setDeleted($deleted) {
    $this->deleted = $deleted;
  }

  public function getDeleted() {
    return $this->deleted;
  }

  public function setRemaining($remaining) {
    $this->remaining = $remaining;
  }

  public function getRemaining() {
    return $this->remaining;
  }

  public function setState($state) {
    $this->state = $state;
  }

  public function getState() {
    return $this->state;
  }

  public function setTotal($total) {
    $this->total = $total;
  }

  public function getTotal() {
    return $this->total;
  }

  public function setUsed($used) {
    $this->used = $used;
  }

  public function getUsed() {
    return $this->used;
  }

}

class OneDrive_Service_Drive_Item extends OneDrive_Collection {

  public $id;
  public $name;
  public $eTag;
  public $cTag;
  protected $createdByType = 'OneDrive_Service_Drive_IdentitySet';
  protected $createdByDataType = '';
  public $createdDateTime;
  protected $lastModifiedByType = 'OneDrive_Service_Drive_IdentitySet';
  protected $lastModifiedByDataType = '';
  public $lastModifiedDateTime;
  public $size;
  protected $parentReferenceType = 'OneDrive_Service_Drive_ItemReference';
  protected $parentReferenceDataType = '';
  public $description;
  public $webUrl;
  protected $fileType = 'OneDrive_Service_Drive_FileFacet';
  protected $fileDataType = '';
  protected $folderType = 'OneDrive_Service_Drive_FolderFacet';
  protected $folderDataType = '';
  protected $imageType = 'OneDrive_Service_Drive_ImageFacet';
  protected $imageDataType = '';
  protected $photoType = 'OneDrive_Service_Drive_PhotoFacet';
  protected $photoDataType = '';
  protected $audioType = 'OneDrive_Service_Drive_AudioFacet';
  protected $audioDataType = '';
  protected $videoType = 'OneDrive_Service_Drive_VideoFacet';
  protected $videoDataType = '';
  protected $locationType = 'OneDrive_Service_Drive_LocationFacet';
  protected $locationDataType = '';
  protected $deletedType = 'OneDrive_Service_Drive_DeletedFacet';
  protected $deletedDataType = '';
  protected $childrenType = 'OneDrive_Service_Drive_Item';
  protected $childrenDataType = 'array';
  protected $thumbnailsType = 'OneDrive_Service_Drive_ThumbnailSet';
  protected $thumbnailsDataType = '';

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }

  public function setETag($eTag) {
    $this->eTag = $eTag;
  }

  public function getETag() {
    return $this->eTag;
  }

  public function setCTag($cTag) {
    $this->cTag = $cTag;
  }

  public function getCTag() {
    return $this->cTag;
  }

  public function setCreatedBy(OneDrive_Service_Drive_IdentitySet $createdBy) {
    $this->createdBy = $createdBy;
  }

  public function getCreatedBy() {
    return $this->createdBy;
  }

  public function setCreatedDateTime($createdDateTime) {
    $this->createdDateTime = $createdDateTime;
  }

  public function getCreatedDateTime() {
    return $this->createdDateTime;
  }

  public function setLastModifiedBy(OneDrive_Service_Drive_IdentitySet $lastModifiedBy) {
    $this->lastModifiedBy = $lastModifiedBy;
  }

  public function getLastModifiedBy() {
    return $this->lastModifiedBy;
  }

  public function setLastModifiedDateTime($lastModifiedDateTime) {
    $this->lastModifiedDateTime = $lastModifiedDateTime;
  }

  public function getLastModifiedDateTime() {
    return $this->lastModifiedDateTime;
  }

  public function setSize($size) {
    $this->size = $size;
  }

  public function getSize() {
    return $this->size;
  }

  public function setParentReference(OneDrive_Service_Drive_ItemReference $parentReference) {
    $this->parentReference = $parentReference;
  }

  public function getParentReference() {
    return $this->parentReference;
  }

  public function setDescription($description) {
    $this->description = $description;
  }

  public function getDescription() {
    return $this->description;
  }

  public function setWebUrl($webUrl) {
    $this->webUrl = $webUrl;
  }

  public function getWebUrl() {
    return $this->webUrl;
  }

  public function setFile(OneDrive_Service_Drive_FileFacet $file) {
    $this->file = $file;
  }

  public function getFile() {
    return $this->file;
  }

  public function setFolder(OneDrive_Service_Drive_FolderFacet $folder) {
    $this->folder = $folder;
  }

  public function getFolder() {
    return $this->folder;
  }

  public function setImage(OneDrive_Service_Drive_ImageFacet $image) {
    $this->image = $image;
  }

  public function getImage() {
    return $this->image;
  }

  public function setPhoto(OneDrive_Service_Drive_PhotoFacet $photo) {
    $this->photo = $photo;
  }

  public function getPhoto() {
    return $this->photo;
  }

  public function setAudio(OneDrive_Service_Drive_AudioFacet $audio) {
    $this->audio = $audio;
  }

  public function getAudio() {
    return $this->audio;
  }

  public function setVideo(OneDrive_Service_Drive_VideoFacet $video) {
    $this->video = $video;
  }

  public function getVideo() {
    return $this->video;
  }

  public function setLocation(OneDrive_Service_Drive_LocationFacet $location) {
    $this->location = $location;
  }

  public function getLocation() {
    return $this->location;
  }

  public function setDeleted(OneDrive_Service_Drive_DeletedFacet $deleted) {
    $this->deleted = $deleted;
  }

  public function getDeleted() {
    return $this->deleted;
  }

  public function setChildren(OneDrive_Service_Drive_Item $children) {
    $this->children = $children;
  }

  public function getChildren() {
    return $this->children;
  }

  public function setThumbnails(OneDrive_Service_Drive_ThumbnailSet $thumbnails) {
    $this->thumbnails = $thumbnails;
  }

  public function getThumbnails() {
    return $this->thumbnails;
  }

}

class OneDrive_Service_Drive_IdentitySet extends OneDrive_Model {

  protected $userType = 'OneDrive_Service_Drive_Identity';
  protected $userDataType = '';
  protected $applicationType = 'OneDrive_Service_Drive_Identity';
  protected $applicationDataType = '';
  protected $deviceType = 'OneDrive_Service_Drive_Identity';
  protected $deviceDataType = '';

  public function setUser(OneDrive_Service_Drive_Identity $user) {
    $this->user = $user;
  }

  public function getUser() {
    return $this->user;
  }

  public function setApplication(OneDrive_Service_Drive_Identity $application) {
    $this->application = $application;
  }

  public function getApplication() {
    return $this->application;
  }

  public function setDevice(OneDrive_Service_Drive_Identity $device) {
    $this->device = $device;
  }

  public function getDevice() {
    return $this->device;
  }

}

class OneDrive_Service_Drive_Identity extends OneDrive_Model {

  public $id;
  public $displayName;

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }

  public function setDisplayName($displayName) {
    $this->displayName = $displayName;
  }

  public function getDisplayName() {
    return $this->displayName;
  }

}

class OneDrive_Service_Drive_ItemReference extends OneDrive_Model {

  public $driveId;
  public $id;
  public $path;

  public function setDriveId($driveId) {
    $this->driveId = $driveId;
  }

  public function getDriveId() {
    return $this->driveId;
  }

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }

  public function setPath($path) {
    $this->path = $path;
  }

  public function getPath() {
    return $this->path;
  }

}

class OneDrive_Service_Drive_FileFacet extends OneDrive_Model {

  public $mimeType;
  protected $hashesType = 'OneDrive_Service_Drive_HashesType';
  protected $hashesDataType = '';

  public function setMimeType($mimeType) {
    $this->mimeType = $mimeType;
  }

  public function getMimeType() {
    return $this->mimeType;
  }

  public function setHashes(OneDrive_Service_Drive_HashesType $hashes) {
    $this->hashes = $hashes;
  }

  public function getHashes() {
    return $this->hashes;
  }

}

class OneDrive_Service_Drive_HashesType extends OneDrive_Model {

  public $sha1Hash;
  public $crc32Hash;

  public function setSha1Hash($sha1Hash) {
    $this->sha1Hash = $sha1Hash;
  }

  public function getSha1Hash() {
    return $this->sha1Hash;
  }

  public function setCrc32Hash($crc32Hash) {
    $this->crc32Hash = $crc32Hash;
  }

  public function getCrc32Hash() {
    return $this->crc32Hash;
  }

}

class OneDrive_Service_Drive_FolderFacet extends OneDrive_Model {

  public $mimeType;
  protected $hashesType = 'OneDrive_Service_Drive_HashesType';
  protected $hashesDataType = '';

  public function setMimeType($mimeType) {
    $this->mimeType = $mimeType;
  }

  public function getMimeType() {
    return $this->mimeType;
  }

  public function setHashes(OneDrive_Service_Drive_HashesType $hashes) {
    $this->hashes = $hashes;
  }

  public function getHashes() {
    return $this->hashes;
  }

}

class OneDrive_Service_Drive_ImageFacet extends OneDrive_Model {

  public $width;
  public $height;

  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }

  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }

}

class OneDrive_Service_Drive_PhotoFacet extends OneDrive_Model {

  public $takenDateTime;
  public $cameraMake;
  public $cameraModel;
  public $fNumber;
  public $exposureDenominator;
  public $exposureNumerator;
  public $focalLength;
  public $iso;

  public function setTakenDateTime($takenDateTime) {
    $this->takenDateTime = $takenDateTime;
  }

  public function getTakenDateTime() {
    return $this->takenDateTime;
  }

  public function setCameraMake($cameraMake) {
    $this->cameraMake = $cameraMake;
  }

  public function getCameraMake() {
    return $this->cameraMake;
  }

  public function setCameraModel($cameraModel) {
    $this->cameraModel = $cameraModel;
  }

  public function getCameraModel() {
    return $this->cameraModel;
  }

  public function setFNumber($fNumber) {
    $this->fNumber = $fNumber;
  }

  public function getFNumber() {
    return $this->fNumber;
  }

  public function setExposureDenominator($exposureDenominator) {
    $this->exposureDenominator = $exposureDenominator;
  }

  public function getExposureDenominator() {
    return $this->exposureDenominator;
  }

  public function setExposureNumerator($exposureNumerator) {
    $this->exposureNumerator = $exposureNumerator;
  }

  public function getExposureNumerator() {
    return $this->exposureNumerator;
  }

  public function setFocalLength($focalLength) {
    $this->focalLength = $focalLength;
  }

  public function getFocalLength() {
    return $this->focalLength;
  }

  public function setIso($iso) {
    $this->iso = $iso;
  }

  public function getIso() {
    return $this->iso;
  }

}

class OneDrive_Service_Drive_AudioFacet extends OneDrive_Model {

  public $album;
  public $albumArtist;
  public $artist;
  public $bitrate;
  public $composers;
  public $copyright;
  public $disc;
  public $discCount;
  public $duration;
  public $genre;
  public $hasDrm;
  public $isVariableBitrate;
  public $title;
  public $track;
  public $trackCount;
  public $year;

  public function setAlbum($album) {
    $this->album = $album;
  }

  public function getAlbum() {
    return $this->album;
  }

  public function setAlbumArtist($albumArtist) {
    $this->albumArtist = $albumArtist;
  }

  public function getAlbumArtist() {
    return $this->albumArtist;
  }

  public function setArtist($artist) {
    $this->artist = $artist;
  }

  public function getArtist() {
    return $this->artist;
  }

  public function setBitrate($bitrate) {
    $this->bitrate = $bitrate;
  }

  public function getBitrate() {
    return $this->bitrate;
  }

  public function setComposers($composers) {
    $this->composers = $composers;
  }

  public function getComposers() {
    return $this->composers;
  }

  public function setCopyright($copyright) {
    $this->copyright = $copyright;
  }

  public function getCopyright() {
    return $this->copyright;
  }

  public function setDisc($disc) {
    $this->disc = $disc;
  }

  public function getDisc() {
    return $this->disc;
  }

  public function setDiscCount($discCount) {
    $this->discCount = $discCount;
  }

  public function getDiscCount() {
    return $this->discCount;
  }

  public function setDuration($duration) {
    $this->duration = $duration;
  }

  public function getDuration() {
    return $this->duration;
  }

  public function setGenre($genre) {
    $this->genre = $genre;
  }

  public function getGenre() {
    return $this->genre;
  }

  public function setHasDrm($hasDrm) {
    $this->hasDrm = $hasDrm;
  }

  public function getHasDrm() {
    return $this->hasDrm;
  }

  public function setIsVariableBitrate($isVariableBitrate) {
    $this->isVariableBitrate = $isVariableBitrate;
  }

  public function getIsVariableBitrate() {
    return $this->isVariableBitrate;
  }

  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }

  public function setTrack($track) {
    $this->track = $track;
  }

  public function getTrack() {
    return $this->track;
  }

  public function setTrackCount($trackCount) {
    $this->trackCount = $trackCount;
  }

  public function getTrackCount() {
    return $this->trackCount;
  }

  public function setYear($year) {
    $this->year = $year;
  }

  public function getYear() {
    return $this->year;
  }

}

class OneDrive_Service_Drive_VideoFacet extends OneDrive_Model {

  public $bitrate;
  public $duration;
  public $height;
  public $width;

  public function setBitrate($bitrate) {
    $this->bitrate = $bitrate;
  }

  public function getBitrate() {
    return $this->bitrate;
  }

  public function setDuration($duration) {
    $this->duration = $duration;
  }

  public function getDuration() {
    return $this->duration;
  }

  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }

  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }

}

class OneDrive_Service_Drive_LocationFacet extends OneDrive_Model {

  public $altitude;
  public $latitude;
  public $longitude;

  public function setAltitude($altitude) {
    $this->altitude = $altitude;
  }

  public function getAltitude() {
    return $this->altitude;
  }

  public function setLatitude($latitude) {
    $this->latitude = $latitude;
  }

  public function getLatitude() {
    return $this->latitude;
  }

  public function setLongitude($longitude) {
    $this->longitude = $longitude;
  }

  public function getLongitude() {
    return $this->longitude;
  }

}

class OneDrive_Service_Drive_DeletedFacet extends OneDrive_Model {
  
}

class OneDrive_Service_Drive_ThumbnailSet extends OneDrive_Model {

  public $id;
  protected $smallType = 'OneDrive_Service_Drive_Thumbnail';
  protected $smallDataType = '';
  protected $mediumType = 'OneDrive_Service_Drive_Thumbnail';
  protected $mediumDataType = '';
  protected $largeType = 'OneDrive_Service_Drive_Thumbnail';
  protected $largeDataType = '';
  protected $smallSquareType = 'OneDrive_Service_Drive_Thumbnail';
  protected $smallSquareDataType = '';
  protected $mediumSquareType = 'OneDrive_Service_Drive_Thumbnail';
  protected $mediumSquareDataType = '';
  protected $largeSquareType = 'OneDrive_Service_Drive_Thumbnail';
  protected $largeSquareDataType = '';
  protected $c1500x1500Type = 'OneDrive_Service_Drive_Thumbnail';
  protected $c1500x1500DataType = '';

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }

  public function setSmall(OneDrive_Service_Drive_Thumbnail $small) {
    $this->small = $small;
  }

  public function getSmall() {
    return $this->small;
  }

  public function setMedium(OneDrive_Service_Drive_Thumbnail $medium) {
    $this->medium = $medium;
  }

  public function getMedium() {
    return $this->medium;
  }

  public function setLarge(OneDrive_Service_Drive_Thumbnail $large) {
    $this->large = $large;
  }

  public function getLarge() {
    return $this->large;
  }

  public function setSmallSquare(OneDrive_Service_Drive_Thumbnail $smallSquare) {
    $this->smallSquare = $smallSquare;
  }

  public function getSmallSquare() {
    return $this->smallSquare;
  }

  public function setMediumSquare(OneDrive_Service_Drive_Thumbnail $mediumSquare) {
    $this->mediumSquare = $mediumSquare;
  }

  public function getMediumSquare() {
    return $this->mediumSquare;
  }

  public function setLargeSquare(OneDrive_Service_Drive_Thumbnail $largeSquare) {
    $this->largeSquare = $largeSquare;
  }

  public function getLargeSquare() {
    return $this->largeSquare;
  }

  public function setC1500x1500(OneDrive_Service_Drive_Thumbnail $c1500x1500) {
    $this->c1500x1500 = $c1500x1500;
  }

  public function getC1500x1500() {
    return $this->c1500x1500;
  }

}

class OneDrive_Service_Drive_Thumbnail extends OneDrive_Model {

  public $width;
  public $height;
  public $url;

  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }

  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }

}

class OneDrive_Service_Drive_FileList extends OneDrive_Collection {

  protected $valueType = 'OneDrive_Service_Drive_Item';
  protected $valueDataType = 'array';

  public function setValue(OneDrive_Service_Drive_Item $value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }

}

class OneDrive_Service_Drive_Permission extends OneDrive_Collection {

  public $id;
  public $roles;
  protected $linkType = 'OneDrive_Service_Drive_SharingLink';
  protected $linkDataType = '';
  protected $inheritedFromType = 'OneDrive_Service_Drive_ItemReference';
  protected $inheritedFromDataType = '';
  public $shareId;

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }

  public function setRoles($roles) {
    $this->roles = $roles;
  }

  public function getRoles() {
    return $this->roles;
  }

  public function setLink(OneDrive_Service_Drive_SharingLink $link) {
    $this->link = $link;
  }

  public function getLink() {
    return $this->link;
  }

  public function setInheritedFrom(OneDrive_Service_Drive_ItemReference $inheritedFrom) {
    $this->inheritedFrom = $inheritedFrom;
  }

  public function getInheritedFrom() {
    return $this->inheritedFrom;
  }

  public function setShareId($shareId) {
    $this->shareId = $shareId;
  }

  public function getShareId() {
    return $this->shareId;
  }

}

class OneDrive_Service_Drive_SharingLink extends OneDrive_Model {

  public $token;
  public $webUrl;
  public $type;
  protected $applicationType = 'OneDrive_Service_Drive_Identity';
  protected $applicationDataType = '';

  public function setToken($token) {
    $this->token = $token;
  }

  public function getToken() {
    return $this->token;
  }

  public function setWebUrl($webUrl) {
    $this->webUrl = $webUrl;
  }

  public function getWebUrl() {
    return $this->webUrl;
  }

  public function setType($type) {
    $this->type = $type;
  }

  public function getType() {
    return $this->type;
  }

  public function setApplication(OneDrive_Service_Drive_Identity $application) {
    $this->application = $application;
  }

  public function getApplication() {
    return $this->application;
  }

}

class OneDrive_Service_Drive_Changes extends OneDrive_Collection {

  protected $valueType = 'OneDrive_Service_Drive_Item';
  protected $valueDataType = 'array';

  public function setValue(OneDrive_Service_Drive_Item $value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }

}

class Onedrive_Service_Drive_Revision extends Onedrive_Model
{
    public $id;
    public $lastModifiedBy;
    public $lastModifiedDateTime;
    public $size;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    public function getLastModifiedDateTime()
    {
        return $this->lastModifiedDateTime;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getDownloadUrl()
    {
        if (isset($this->modelData['@content.downloadUrl'])) {
            return $this->modelData["@content.downloadUrl"];
        }

        return null;
    }
}

class Onedrive_Service_Drive_RevisionList extends Onedrive_Collection
{
    protected $valueType = 'Onedrive_Service_Drive_Revision';
    protected $valueDataType = 'array';
    public $id;
    public $lastModifiedBy;
    public $lastModifiedDateTime;
    public $published;

    public function setValue($items)
    {
        $this->value = $items;
    }
    public function getValue()
    {
        return $this->value;
    }

    public function getId()
    {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }
    public function getLastModifiedDateTime()
    {
        return $this->lastModifiedDateTime;
    }
    public function getPublished()
    {
        return $this->published;
    }
}