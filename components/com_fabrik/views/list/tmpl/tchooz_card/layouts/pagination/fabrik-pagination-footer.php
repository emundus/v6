<?php
/**
 * Layout: List Pagination Footer
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.3.3
 */

$d = $displayData;

if ($d->showNav) :
	?>
<div class="list-footer">
	<div class="limit">
		<div class="em-flex-row em-flex-space-between">
            <span class="em-text-neutral-600">
                <?php echo $d->pagesCounter; ?>
			</span>
            <div class="em-flex-row em-flex-space-between em-flex-gap-8">
                <span class="add-on">
                    <label class="em-mb-0" for="<?php echo $d->listName;?>">
                        <small>
                        <?php echo $d->label; ?>
                        </small>
                    </label>
			    </span>
                <?php echo $d->list; ?>
            </div>
		</div>
	</div>
	<?php echo $d->links; ?>
	<input type="hidden" name="limitstart<?php echo $d->id; ?>" id="limitstart<?php echo $d->id; ?>" value="<?php echo $d->value; ?>" />
</div>
<?php
else :
	if ($d->showTotal) : ?>
<div class="list-footer">
	<span class="add-on">
			<small>
				<?php echo $d->pagesCounter; ?>
			</small>
	</span>
</div>
<?php
	endif;
endif;