<?php
/**
 * @version   $Id: set.php 6564 2013-01-16 17:13:36Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformgroup');


class GantryFormGroupSet extends GantryFormGroup
{
    protected $type = 'set';
    protected $baseetype = 'group';
    protected $hidden = true;

	public function getInput()
	{
        return '';
    }
}