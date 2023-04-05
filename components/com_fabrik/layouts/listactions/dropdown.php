<?php
/**
 * Layout: list row buttons - rendered as a Bootstrap dropdown
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$ulclass = 'dropdown-menu ';

if ($displayData['align'] == 'right')
{
	$ulclass .= ' dropdown-menu-end';
}
else
{
	$ulclass .= ' dropdown-menu-start';
}

?>
<div class="dropdown  fabrik_action ">
	<button class="btn btn-default btn-mini dropdown-toggle " type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
		<span class="caret"></span>
	</button>
	<ul class="<?php echo $ulclass; ?>">
		<li class="nav-link"><?php echo implode('</li>' . "\n" . '<li class="nav-link">', $displayData['items']); ?></li>
	</ul>
</div>