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


if (!defined('_JCH_EXEC'))
{
        define('_JCH_EXEC', '1');
}

defined('_JCH_EXEC') or die('Restricted access');


function jchoptimize_class_autoload($class)
{
	$class = ltrim($class, '\\');
	$file = dirname(__FILE__) . DIRECTORY_SEPARATOR; 

	if (substr($class, 0, 17) == 'JchOptimize\\LIBS\\')
	{
		$file .= 'libs' . DIRECTORY_SEPARATOR . substr($class, 17) . '.php'; 
	}
	elseif(substr($class, 0, 17) == 'JchOptimize\\Core\\')
	{
		$file .=  strtolower(substr($class, 17)) . '.php';
       	}
	elseif(substr($class, 0, 21) == 'JchOptimize\\Platform\\')
	{
		$file = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'platform' . DIRECTORY_SEPARATOR . strtolower(substr($class, 21)) . '.php';
	}
	elseif(substr($class, 0, 23) == 'JchOptimize\\Interfaces\\')
	{
		$file .= 'interfaces' . DIRECTORY_SEPARATOR . strtolower(substr($class, 23, -9)) . '.php';
	}
	elseif(substr($class, 0, 19) == 'JchOptimize\\Minify\\')
	{
		$file .= 'minify' . DIRECTORY_SEPARATOR . strtolower(substr($class, 19)) . '.php';
	}
	elseif(substr($class, 0, 17) == 'JchOptimize\\Root\\')
	{
		$file = dirname(dirname(__FILE__)) . substr($class, 17) . '.php';
	}
	else
	{
		return false;
	}


	if (file_exists($file))
	{
		require_once($file);
	}
}

spl_autoload_register('jchoptimize_class_autoload', true, true);




