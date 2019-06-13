<?php
/**
 * JCH Optimize - Aggregate and minify external resources for optmized downloads
 * 
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall
 * @license GNU/GPLv3, See LICENSE file
 * 
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

// No direct access
defined('_JCH_EXEC') or die('Restricted access');

/**
 * 
 */
class JchOptimizeJson
{
	/**
	 * Determines whether the request was successful
	 *
	 * @var    boolean
	 */
	public $success = true;

	/**
	 * The response message
	 *
	 * @var    string
	 */
	public $message = '';

	/**
         * The error code
         * 
	 */
	public $code = 0;

	/**
	 * The response data
	 *
	 * @var    mixed
	 */
	public $data = '';

	/**
	 * Constructor
	 *
	 * @param   mixed    $response        The Response data
	 * @param   string   $message         The response message
         * 
	 */
	public function __construct($response = null, $message = '')
	{
		$this->message = $message;

		// Check if we are dealing with an error
		if ($response instanceof Exception)
		{
			// Prepare the error response
			$this->success = false;
			$this->message = $response->getMessage();
                        $this->code = $response->getCode();
		}
		else
		{
			$this->data    = $response;
		}
	}

	/**
	 * Magic toString method for sending the response in JSON format
	 *
	 * @return  string  The response in JSON format
	 */
	public function __toString()
	{
                @header( 'Content-Type: application/json; charset=utf-8' );
                
		return json_encode($this);
	}
}
