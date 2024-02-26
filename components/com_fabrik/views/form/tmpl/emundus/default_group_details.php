<?php
/**
 * Bootstrap Form Template - group details
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

foreach ($this->elements as $element) :
	 if (!$element->hidden) {
		if ($element->startRow) :?>
			<div class="row-fluid">
		<?php
		endif;
		?>

		<div class="<?php echo $element->span;?>">
			<div class="row-fluid mb-3">
				<div>
					<?php echo $element->label; ?>
				</div>
				<div class="fabrikElement">
					<?php echo $element->element_ro; ?>
				</div>
			</div>
		</div>

	<?php
	if ($element->endRow) :
	?>
		</div>
	<?php
	endif;
	}
endforeach; ?>
