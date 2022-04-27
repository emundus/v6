<?php
defined('_JEXEC') or die;
/**
 * @package    miniOrange
 * @subpackage Plugins
 * @license    GNU/GPLv3
 * @copyright  Copyright 2015 miniOrange. All Rights Reserved.
 *
 *
 * This file is part of miniOrange SAML plugin.
 *
 * miniOrange SAML plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * miniOrange SAML plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with miniOrange SAML plugin.  If not, see <http://www.gnu.org/licenses/>.
 */
 
 if (defined('_JEXEC')) {
	 
	 
	/**
	 * This block of code is not used.
	 */
	class plgauthenticationminiorangesaml extends JPlugin
	{
		/**
		 * This method should handle any authentication and report back to the subject
		 * 
		 *
		 * @access    public
		 * @param     array     $credentials    Array holding the user credentials ('username' and 'password')
		 * @param     array     $options        Array of extra options
		 * @param     object    $response       Authentication response object
		 * @return    boolean
		 */
		function onUserAuthenticate( $credentials, $options, &$response )
		{
			$cookie = JFactory::getApplication()->input->cookie->get('mosamlauthadmin');
			if (isset($cookie['mosamlauthadmin']) && $cookie['mosamlauthadmin'] != '-1'){
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select(array('api_key','customer_token'));
				$query->from($db->quoteName('#__miniorange_saml_customer_details'));
				$query->where($db->quoteName('id')." = 1");
				$db->setQuery($query);
				$customerResult = $db->loadAssoc();
			}
		}
	}
 }
