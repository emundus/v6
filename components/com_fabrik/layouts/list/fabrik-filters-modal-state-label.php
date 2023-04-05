<?php
/**
 * Layout for modal filter state, per element
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die;

$d = $displayData;
?>
<button class="btn btn-sm btn-info m-1">
	<span data-modal-state-label><?php echo $d->label;?></span>:
	<span data-modal-state-value><?php echo $d->displayValue . ' '; ?></span>
	<a data-filter-clear="<?php echo $d->key; ?>" href="#" style="color: white;">
		<?php echo FabrikHelperHTML::icon('icon-cancel', '', 'style="text-align: right; "'); ?>
	</a>
</button>