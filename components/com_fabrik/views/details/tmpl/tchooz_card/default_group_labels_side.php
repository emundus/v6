<?php
/**
 * Bootstrap Details Template
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$element = $this->element;

$notEmptyLabel = strpos($element->label, 'fabrikEmptyLabel');
?>
<div class="<?php echo $element->containerClass .' '. $element->span;?>">
    <?php if(!$notEmptyLabel) : ?>
        <div class="<?php echo FabrikHelperHTML::getGridSpan('4'); ?> fabrikLabel">
            <?php if (strpos($element->label, 'fabrikEmptyLabel') === false) : ?>
                <?php echo $element->label;?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
	<div class="<?php echo FabrikHelperHTML::getGridSpan('8'); ?>">
		<?php if ($this->tipLocation == 'above') : ?>
			<p class=""><?php echo $element->tipAbove ?></p>
		<?php endif ?>

		<div class="fabrikElement">
			<?php echo $element->element;?>
		</div>

		<?php if ($this->tipLocation == 'side') : ?>
			<p class=""><?php echo $element->tipSide ?></p>
		<?php endif ?>


	<?php if ($this->tipLocation == 'below') :?>
		<p class=""><?php echo $element->tipBelow ?></p>
	<?php endif ?>
	</div>
</div><!--  end span -->
