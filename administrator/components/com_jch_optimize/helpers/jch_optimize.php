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
defined('_JEXEC') or die;

class Jch_optimizeHelper{

	public static function prepareUpdate(&$update, &$headers)
	{
                $uri = JUri::getInstance($update->downloadurl->_data);

                // I don't care about download URLs not coming from our site
                $host = $uri->getHost();
                if ($host != 'www.jch-optimize.net')
                {
                        return true;
                }

		include_once JPATH_PLUGINS . '/system/jch_optimize/jchoptimize/loader.php';

		$params = JchPlatformPlugin::getPluginParams();
                // Get the download ID
                $dlid = trim($params->get('pro_downloadid', ''));

                // If the download ID is invalid, return without any further action
                if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
                {
                        return true;
                }

                // Append the Download ID to the download URL
                if (!empty($dlid))
                {
                        $uri->setVar('dlid', $dlid);
                        $update->downloadurl->_data = $uri->toString();
                }

                return true;
	}	
}
