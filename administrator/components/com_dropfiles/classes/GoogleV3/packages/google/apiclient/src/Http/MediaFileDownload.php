<?php
/**
 * Copyright 2012 Google Inc.
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


namespace Google\Http;

use Google\Client;
use Google\Exception as GoogleException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

/**
 * Manage large file downloads, which may be media but can be any type
 * of sizable data.
 */
class MediaFileDownload
{
    const DOWNLOAD_MEDIA_TYPE = 'media';
    const DOWNLOAD_MULTIPART_TYPE = 'multipart';
    const DOWNLOAD_RESUMABLE_TYPE = 'resumable';

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

    /** @var Client */
    private $client;

    /** @var Google_Http_Request */
    private $request;

    /** @var string */
    private $boundary;

    /**
     * Result code from last HTTP call
     * @var int
     */
    private $httpResultCode;

    /**
     * @param Client Google Client
     * @param RequestInterface Psr7 request interface
     * @param $mimeType string
     * @param $data string The bytes you want to download.
     * @param $resumable bool
     * @param bool $chunkSize File will be downloaded in chunks of this many bytes.
     * only used if resumable=True
     */
    public function __construct(
        Client $client,
        RequestInterface $request,
        $mimeType,
        $data,
        $resumable = false,
        $chunkSize = false
    ) {
        $this->client = $client;
        $this->request = $request;
        $this->mimeType = $mimeType;
        $this->data = $data;
        $this->resumable = $resumable;
        $this->chunkSize = $chunkSize;
        $this->progress = 0;

        //$this->process();
    }

    /**
     * Set the size of the file that is being downloaded.
     * @param $size - int file size in bytes
     */
    public function setFileSize($size)
    {
        $this->size = $size;
    }

    /**
     * Return the progress on the download
     * @return int progress in bytes downloaded.
     */
    public function getProgress()
    {
        return $this->progress;
    }

    /**
     * Send the next part of the file to download.
     * @param [$chunk] the next set of bytes to send. If false will used $data passed
     * at construct time.
     */
    public function nextChunk($chunk = false)
    {

        $resumeUri = $this->getResumeUri();
        $lastBytePos = $this->progress + $this->chunkSize - 1;

        $lastBytePosSize = $this->size - 1;

        $lastBytePos = min($lastBytePos,$lastBytePosSize);

        $headers = array('Range' => "bytes=$this->progress-$lastBytePos");

        $request = new Request(
            'GET',
            $resumeUri,
            $headers
        );


        return $this->makeGetRequest($request);
    }

    /**
     * Return the HTTP result code from the last call made.
     * @return int code
     */
    public function getHttpResultCode()
    {
        return $this->httpResultCode;
    }

    /**
     * Sends a PUT-Request to google drive and parses the response,
     * setting the appropiate variables from the response()
     *
     * @param RequestInterface $httpRequest the Reuqest which will be send
     *
     * @return false|mixed false when the download is unfinished or the decoded http response
     *
     */
    private function makePutRequest(RequestInterface $request)
    {
        $response = $this->client->execute($request);
        $this->httpResultCode = $response->getStatusCode();

        if (308 == $this->httpResultCode) {
            // Track the amount downloaded.
            $range = explode('-', $response->getHeaderLine('range'));
            $this->progress = $range[1] + 1;

            // Allow for changing download URLs.
            $location = $response->getHeaderLine('location');
            if ($location) {
                $this->resumeUri = $location;
            }

            // No problems, but download not complete.
            return false;
        }

        return REST::decodeHttpResponse($response, $this->request);
    }

    /**
     * @param RequestInterface $request
     *
     * @return Psr\Http\Message\ResponseInterface
     * @throws GoogleException
     * @throws Google_Service_Exception
     */
    private function makeGetRequest(RequestInterface $request)
    {
        $response = $this->client->execute($request);
        $this->httpResultCode = $response->getStatusCode();
        if ($this->httpResultCode >= 200 && $this->httpResultCode < 300) {

            $range = explode('-', $response->getHeaderLine('content-range'));

            $range = explode('/', $range[1]);

            $this->progress = $range[0] + 1;

            // Allow for changing download URLs.
            $location = $response->getHeaderLine('location');
            if ($location) {
                $this->resumeUri = $location;
            }

            // No problems, but download not complete.
            //return false;
            return $response;
        }
        else if($this->httpResultCode >= 400)
        {
            return REST::decodeHttpResponse($response, $this->request);

        }

        return false;

    }


    /**
     * Resume a previously unfinished download
     * @param $resumeUri the resume-URI of the unfinished, resumable download.
     */
    public function resume($resumeUri)
    {
        $this->resumeUri = $resumeUri;
        $headers = array(
            'content-range' => "bytes */$this->size",
            'content-length' => 0,
        );
        $httpRequest = new Request(
            'PUT',
            $this->resumeUri,
            $headers
        );

        return $this->makePutRequest($httpRequest);
    }

    /**
     * @return Google_Http_Request $request
     * @visible for testing
     */
    private function process()
    {
        $this->transformToDownloadUrl();
        $request = $this->request;

        $postBody = '';
        $contentType = false;

        $meta = (string) $request->getBody();
        $meta = is_string($meta) ? json_decode($meta, true) : $meta;

        $downloadType = $this->getDownloadType($meta);
        $request = $request->withUri(
            Uri::withQueryValue($request->getUri(), 'downloadType', $downloadType)
        );

        $mimeType = $this->mimeType ?
            $this->mimeType :
            $request->getHeaderLine('content-type');

        if (self::DOWNLOAD_RESUMABLE_TYPE == $downloadType) {
            $contentType = $mimeType;
            $postBody = is_string($meta) ? $meta : json_encode($meta);
        } else if (self::DOWNLOAD_MEDIA_TYPE == $downloadType) {
            $contentType = $mimeType;
            $postBody = $this->data;
        } else if (self::DOWNLOAD_MULTIPART_TYPE == $downloadType) {
            // This is a multipart/related download.
            $boundary = $this->boundary ? $this->boundary : mt_rand();
            $boundary = str_replace('"', '', $boundary);
            $contentType = 'multipart/related; boundary=' . $boundary;
            $related = "--$boundary\r\n";
            $related .= "Content-Type: application/json; charset=UTF-8\r\n";
            $related .= "\r\n" . json_encode($meta) . "\r\n";
            $related .= "--$boundary\r\n";
            $related .= "Content-Type: $mimeType\r\n";
            $related .= "Content-Transfer-Encoding: base64\r\n";
            $related .= "\r\n" . base64_encode($this->data) . "\r\n";
            $related .= "--$boundary--";
            $postBody = $related;
        }

        $request = $request->withBody(Psr7\stream_for($postBody));

        if (isset($contentType) && $contentType) {
            $request = $request->withHeader('content-type', $contentType);
        }

        return $this->request = $request;
    }

    /**
     * Valid download types:
     * - resumable (DOWNLOAD_RESUMABLE_TYPE)
     * - media (DOWNLOAD_MEDIA_TYPE)
     * - multipart (DOWNLOAD_MULTIPART_TYPE)
     * @param $meta
     * @return string
     * @visible for testing
     */
    public function getDownloadType($meta)
    {
        if ($this->resumable) {
            return self::DOWNLOAD_RESUMABLE_TYPE;
        }

        if (false == $meta && $this->data) {
            return self::DOWNLOAD_MEDIA_TYPE;
        }

        return self::DOWNLOAD_MULTIPART_TYPE;
    }

    public function getResumeUri()
    {
        if (is_null($this->resumeUri)) {
            $this->resumeUri = $this->request->getUri();
        }

        return $this->resumeUri;
    }

    private function fetchResumeUri()
    {
        $result = null;
        $body = $this->request->getBody();
        if ($body) {
            $headers = array(
                'content-type' => 'application/json; charset=UTF-8',
                'content-length' => $body->getSize(),
                'x-download-content-type' => $this->mimeType,
                'x-download-content-length' => $this->size,
                'expect' => '',
            );
            foreach ($headers as $key => $value) {
                $this->request = $this->request->withHeader($key, $value);
            }
        }

        $response = $this->client->execute($this->request, false);
        $location = $response->getHeaderLine('location');
        $code = $response->getResponseHttpCode();

        if (200 == $code && true == $location) {
            return $location;
        }

        $message = $code;
        $body = json_decode((string) $this->request->getBody(), true);
        if (isset($body['error']['errors'])) {
            $message .= ': ';
            foreach ($body['error']['errors'] as $error) {
                $message .= "{$error[domain]}, {$error[message]};";
            }
            $message = rtrim($message, ';');
        }

        $error = "Failed to start the resumable download (HTTP {$message})";
        $this->client->getLogger()->error($error);

        throw new GoogleException($error);
    }

    private function transformToDownloadUrl()
    {
        $parts = parse_url((string) $this->request->getUri());
        if (!isset($parts['path'])) {
            $parts['path'] = '';
        }
        $parts['path'] = '/download' . $parts['path'];
        $uri = Uri::fromParts($parts);
        $this->request = $this->request->withUri($uri);
    }

    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
