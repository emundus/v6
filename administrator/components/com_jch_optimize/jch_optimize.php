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

include_once JPATH_PLUGINS . '/system/jch_optimize/jchoptimize/loader.php';

$app = JFactory::getApplication();

$action = $app->input->get('action', '', 'string');

if (!$action)
{
	exit();
}

jimport('joomla.event.dispatcher');

$plugin = JchPlatformPlugin::getPlugin();

if (!$plugin)
{
	exit();
}

$dispatcher = JDispatcher::getInstance();
$className  = 'Plg' . $plugin->type . $plugin->name;

if(!class_exists($className))
{
	$path = JPATH_PLUGINS . '/' . $plugin->type . '/' . $plugin->name . '/' . $plugin->name . '.php';
	require_once $path;
}

$jchoptimize = new $className($dispatcher, (array) ($plugin));
$jchoptimize->loadLanguage();

try
{
	$results = $dispatcher->trigger('onAjax' . ucfirst($action));
}
catch (Exception $e)
{
	$results = $e;
}


if (is_scalar($results))
{
	$out = (string) $results;
}
else
{
	$out = implode((array) $results);
}

echo $out;


exit();
