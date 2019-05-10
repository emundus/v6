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


function loadJchOptimizeClass($sClass)
{
        if(is_array($sClass))
        {
                foreach($sClass as $class)
                {
                        loadJchOptimizeClass($class);
                }
        }
        else
        {
                $class  = $sClass;
        }
        
        $prefix = substr($class, 0, 3);

        // If the class already exists do nothing.
        if (class_exists($class, false))
        {
                return true;
        }

        if ($prefix !== 'Jch')
        {
                return false;
        }
        else
        {
                $class = str_replace($prefix, '', $class);
        }

        if (strpos($class, '\\') !== FALSE)
        {
                $filename = str_replace('Optimize\\', '', $class);
                $file     = dirname(__FILE__) . '/libs/' . $filename . '.php';
        }
        elseif (strpos($class, 'Platform') === 0)
        {
                $class    = str_replace('Platform', '', $class);
                $filename = strtolower($class);
                $file     = dirname(dirname(__FILE__)) . '/platform/' . $filename . '.php';

                loadJchOptimizeClass('JchInterface' . $class);
        }
        elseif (strpos($class, 'Interface') === 0)
        {
                $filename = strtolower(str_replace('Interface', '', $class));
                $file     = dirname(__FILE__) . '/interfaces/' . $filename . '.php';
        }
        else
        {
                $filename = str_replace('Optimize', '', $class);
                $filename = strtolower(($class == 'Optimize') ? 'jchoptimize' : $filename);
                $file     = dirname(__FILE__) . '/' . $filename . '.php';
        }

        if (!file_exists($file))
        {
                return false;
        }
        else
        {
                include $file;

                if (!class_exists($sClass) && !interface_exists($sClass))
                {
                        return false;
                }
        }
}

spl_autoload_register('loadJchOptimizeClass', true, true);




