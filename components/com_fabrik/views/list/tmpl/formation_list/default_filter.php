<?php
/**
 * Bootstrap List Template - Filter
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$style = $this->toggleFilters ? 'style="display:none"' : '';

foreach ($this->filters as $key => $filter) :
    echo '<p class="em-filter-label">'. $filter->label.'</p>'. $filter->element.'</td>';

endforeach;

