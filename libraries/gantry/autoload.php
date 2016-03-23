<?php
/**
 * @version   $Id: autoload.php 4532 2012-10-26 16:42:16Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

require_once dirname(__FILE__) . '/classes/Gantry/Loader.php';
spl_autoload_register(array('Gantry_Loader', 'loadClass'), true);