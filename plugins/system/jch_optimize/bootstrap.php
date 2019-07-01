<?php

/**
 * JCH Optimize - Joomla! plugin to aggregate and minify external resources for 
 *   optmized downloads
 * @author Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2010 Samuel Marshall. All rights reserved.
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
 * 
 * This plugin includes other copyrighted works. See individual 
 * files for details.
 */
//$start = microtime(true);


define('_JEXEC', 1);

$DIR = dirname(dirname(dirname(dirname(__FILE__))));

if (file_exists($DIR . '/defines.php'))
{
        include_once $DIR . '/defines.php';
}

if (!defined('_JDEFINES'))
{
        define('JPATH_BASE', $DIR);
        require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';

require_once JPATH_PLUGINS . '/system/jch_optimize/jchoptimize/loader.php';
