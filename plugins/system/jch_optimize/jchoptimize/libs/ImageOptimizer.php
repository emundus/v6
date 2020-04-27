<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for
 * optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\LIBS;

defined('_JEXEC') or die('Restricted access');

use CURLFile;
use curl_init;
use curl_exec;
use RuntimeException;
use JchOptimize\Core\FileRetriever;
use JchOptimize\Core\Json;

class ImageOptimizer
{

        protected $auth = array();

        public function __construct($dlid, $secret)
        {
                $this->auth = array(
                        'auth' => array( 
                                'dlid' => $dlid,
                                'secret' => $secret
                        )
                );
        }

        public function upload($opts = array())
        {
                if (empty($opts['files'][0]))
                {
                        throw new \Exception('File parameter was not provided', 500);
                }

                //if (!files_exists($opts['files']))
                //{
                //        throw new Exception('File \'' . $opts['files'] . '\' does not exist', 404);
                //}

		$files = array();

		foreach($opts['files'] as $i => $file)
		{
			if (class_exists('CURLFile'))
			{
				$files['files[' . $i . ']'] = new CURLFile($file);
			}
			else
			{
				$files['files[' . $i . ']'] = '@' . $file;
			}
		}

                unset($opts['files']);

		$data = array_merge($files, array( "data" => json_encode(array_merge($this->auth, $opts))));

	        return self::request($data, "https://api.jch-optimize.net/");
        }

        private function request($data, $url)
        {
		ini_set('upload_max_filesize', '50M');
		ini_set('post_max_size', '50M');
		ini_set('max_input_time', 300);
		ini_set('max_execution_time', 300);

		$aHeaders = array('Content-Type' => 'multipart/form-data');
		$oFileRetriever = FileRetriever::getInstance(array('curl'));

		$response = $oFileRetriever->getFileContents($url, $data, $aHeaders, '', 30);

		if($oFileRetriever->response_code === 0 && $oFileRetriever->response_error !== '')
		{
			return new Json(new \Exception($oFileRetriever->response_error), 500);
		}

		return json_decode($response);
	}

	public static function curlRequest($url, $data)
	{
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curl, CURLOPT_FAILONERROR, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
                curl_setopt($curl, CURLOPT_TIMEOUT, 300);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 300);

		$response = curl_exec($curl);
                $error    = curl_errno($curl);
                $message  = curl_error($curl);

                curl_close($curl);

                if ($error > 0)
                {
                        $curl_error = new \RuntimeException(sprintf('cURL returned with the following error: "%s"', $message), $error);
			$response = new Json($curl_error);
                }

		return array(
			'body' => $response,
			'code' => 200
		);
        }
}
