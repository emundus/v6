<?php
/**
 * @version   $Id: catalog.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

require_once(dirname(__FILE__) . '/default/theme.php');
RokNavMenu::registerTheme(dirname(__FILE__).'/default','default', 'Default', 'RokNavMenuDefaultTheme');

require_once(dirname(__FILE__) . '/fusion/theme.php');
RokNavMenu::registerTheme(dirname(__FILE__).'/fusion','fusion', 'Default Fusion', 'RokNavMenuFusionTheme');