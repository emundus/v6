<?php
/**
 * Layout: List filters
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.4
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
/*Use only one file for 1 and >1 filter columns. 
* view.html.php is doing $layoutFile = $this->filterCols > 1 ? 'fabrik-filters-bootstrap' : 'fabrik-filters';
* Leaving this and keep this fabrik-filters.php in case of existing layout overrides (Fabrik3 upgrades).
*/
include 'fabrik-filters-bootstrap.php';