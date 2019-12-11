<?php

/**
 * Copyright 2012 OneDrive Inc.
 * Copyright 2015 www.florisdeleeuw.nl
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if (!class_exists('OneDrive_Client')) {
  require_once dirname(__FILE__) . '/../autoload.php';
}

/**
 * Manage large file uploads, which may be media but can be any type
 * of sizable data.
 */
class OneDrive_Http_MediaFileUpload {

  const UPLOAD_MEDIA_TYPE = 'media';
  const UPLOAD_MULTIPART_TYPE = 'multipart';
  const UPLOAD_RESUMABLE_TYPE = 'resumable';

  /** @var string $mimeType */
  private $mimeType;

  /** @var string $data */
  private $data;

  /** @var bool $resumable */
  private $resumable;

  /** @var int $chunkSize */
  private $chunkSize;

  /** @var int $size */
  private $size;

  /** @var string $resumeUri */
  private $resumeUri;

  /** @var int $progress */
  private $progress;

  /** @var OneDrive_Client */
  private $client;

  /** @var OneDrive_Http_Request */
  private $request;

  /** @var string */
  private $boundary;

  /**
   * Result code from last HTTP call
   * @var int
   */
  private $httpResultCode;

  /**
   * @param $mimeType string
   * @param $data string The bytes you want to upload.
   * @param $resumable bool
   * @param bool $chunkSize File will be uploaded in chunks of this many bytes.
   * only used if resumable=True
   */
  public function __construct(
  OneDrive_Client $client, OneDrive_Http_Request $request, $mimeType, $data, $resumable = false, $chunkSize = false, $boundary = false
  ) {
    $this->client = $client;
    $this->request = $request;
    $this->mimeType = $mimeType;
    $this->data = $data;
    $this->size = strlen($this->data);
    $this->resumable = $resumable;
    if (!$chunkSize) {
      $chunkSize = 256 * 1024;
    }
    $this->chunkSize = $chunkSize;
    $this->progress = 0;
    $this->boundary = $boundary;

    // Process Media Request
    $this->process();
  }

  /**
   * Set the size of the file that is being uploaded.
   * @param $size - int file size in bytes
   */
  public function setFileSize($size) {
    $this->size = $size;
  }

  /**
   * Return the progress on the upload
   * @return int progress in bytes uploaded.
   */
  public function getProgress() {
    return $this->progress;
  }

  /**
   * Return the HTTP result code from the last call made.
   * @return int code
   */
  public function getHttpResultCode() {
    return $this->httpResultCode;
  }

  /**
   * Send the next part of the file to upload.
   * @param [$chunk] the next set of bytes to send. If false will used $data passed
   * at construct time.
   */
  public function nextChunk($chunk = false) {
    if (false == $this->resumeUri) {
      $this->resumeUri = $this->getResumeUri();
    }

    if (false == $chunk) {
      $chunk = substr($this->data, $this->progress, $this->chunkSize);
    }

    $firstBytePos = (is_array($this->progress)) ? $this->progress[0] : 0;
    $lastBytePos = $firstBytePos + strlen($chunk) - 1;
    $headers = array(
        'content-range' => "bytes $firstBytePos-$lastBytePos/$this->size",
        //'content-type' => $this->request->getRequestHeader('content-type'),
        'content-length' => $this->chunkSize,
            //'expect' => '',
    );

    $httpRequest = new OneDrive_Http_Request(
            $this->resumeUri, 'PUT', $headers, $chunk
    );

    if ($this->client->getClassConfig("OneDrive_Http_Request", "enable_gzip_for_uploads")) {
      $httpRequest->enableGzip();
    } else {
      $httpRequest->disableGzip();
    }

    $httpRequest = $this->client->getAuth()->sign($httpRequest);

    $response = $this->client->getIo()->makeRequest($httpRequest);
    $code = $response->getResponseHttpCode();
    $this->httpResultCode = $code;
    $body = @json_decode($response->getResponseBody(), true);

    if (201 == $code) {
      $class = $this->request->getExpectedClass();
      return new $class($body);
    } else if (202 == $code) {
      // Track the amount uploaded.
      $range = reset($body['nextExpectedRanges']);
      $this->progress = explode('-', $range);

      // No problems, but upload not complete.
      return false;
    } else {
      return OneDrive_Http_REST::decodeHttpResponse($response, $this->client);
    }
  }

  /**
   * @param $meta
   * @param $params
   * @return array|bool
   * @visible for testing
   */
  private function process() {
    
  }

  /**
   * Valid upload types:
   * - resumable (UPLOAD_RESUMABLE_TYPE)
   * - media (UPLOAD_MEDIA_TYPE)
   * - multipart (UPLOAD_MULTIPART_TYPE)
   * @param $meta
   * @return string
   * @visible for testing
   */
  public function getUploadType($meta) {
    if ($this->resumable) {
      return self::UPLOAD_RESUMABLE_TYPE;
    }

    if (false == $meta && $this->data) {
      return self::UPLOAD_MEDIA_TYPE;
    }

    return self::UPLOAD_MULTIPART_TYPE;
  }

  private function getResumeUri() {

    $response = $this->client->getIo()->makeRequest($this->request);
    $code = $response->getResponseHttpCode();

    $message = $code;
    $body = @json_decode($response->getResponseBody());

    if (isset($body->uploadUrl)) {
      return $body->uploadUrl;
    }
    if (!empty($body->error->errors)) {
      $message .= ': ';
      foreach ($body->error->errors as $error) {
        $message .= "{$error->domain}, {$error->message};";
      }
      $message = rtrim($message, ';');
    }

    $error = "Failed to start the resumable upload (HTTP {$message})";
    $this->client->getLogger()->error($error);
    throw new OneDrive_Exception($error);
  }

}
