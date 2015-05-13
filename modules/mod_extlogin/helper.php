<?php
/**
* @version		$Id: helper.php 8473 2007-08-20 20:13:58Z jinx $
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modExtLoginHelper
{
	function getReturnURL($params, $type)
	{
		if($itemid =  $params->get($type)) 
		{
			$url = 'index.php?Itemid='.$itemid;
			$url = JRoute::_($url, false);
		}
		else
		{
			// Redirect to login
			$uri =& JFactory::getURI();
			$url = $uri->toString();
		}
		return base64_encode($url);
	}

	function getType()
	{
		$user = JFactory::getUser();
	    return (!$user->get('guest')) ? 'logout' : 'login';
	}
	/**
	 * formats the date passed into format required by 'datetime' attribute of <date> tag
	 * if no intDate supplied, uses current date.
	 * @param intDate integer optional
	 * @return string
	 **/
	function getDateTimeValue( $intDate = null ) 
	{

    $strFormat = 'l jS \of F Y, H:i';
    $strDate = $intDate ? date( $strFormat, $intDate ) : date( $strFormat ) ;
   
    return $strDate;
	}
	/**
	 * return time left for candidature periode
	 * @param time_left integer optional
	 * @param endtime mktime optional
	 * @return array
	**/
	function timeleft($time_left=0, $endtime=null) 
	{ 
		if($endtime != null) 
			$time_left = $endtime - time(); 
		//die($time_left. ' : '.$endtime.' : '.time());
		if($time_left > 0)
		{ 
			$days = floor($time_left / 86400); 
			$time_left = $time_left - $days * 86400; 
			$hours = floor($time_left / 3600); 
			$time_left = $time_left - $hours * 3600; 
			$minutes = floor($time_left / 60); 
			$seconds = $time_left - $minutes * 60; 
		} 
		else 
		{ 
			return array(0, 0, 0, 0); 
		} 
		return array($days, $hours, $minutes, $seconds); 
	}
}