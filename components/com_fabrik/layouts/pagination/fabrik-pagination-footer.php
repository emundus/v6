<?php
/**
 * Layout: List Pagination Footer
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.3.3
 */

$d = $displayData;

if ($d->showNav) :
?>
<div class="list-footer container">
	<div class="limit row input-group pb-2">
			<div class="col col-sm-2" style="text-align:right;">
				<label for="<?php echo $d->listName;?>">
					<p style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
						<?php echo $d->label; ?>
					</p>
				</label>
			</div>
			<?php echo $d->list; ?>
			<div class="col col-sm-3 ms-auto"  style="text-align:right;">
				<p style="margin-top: 0.5rem; margin-bottom: 0.5rem;">
					<?php echo $d->pagesCounter; ?>
				</p>
			</div>
	</div>
	<?php echo $d->links; ?>
	<input type="hidden" name="limitstart<?php echo $d->id; ?>" id="limitstart<?php echo $d->id; ?>" value="<?php echo $d->value; ?>" />
</div>
	<?php
else :
	if ($d->showTotal) : ?>
		<div class="list-footer">
			<div class="input-group">
				<p>
					<?php echo $d->pagesCounter; ?>
				</p>
			</div>
		</div>
		<?php
	endif;
endif;
