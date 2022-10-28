<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$config            = EventbookingHelper::getConfig();
$controlGroupClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-group') : 'control-group';
$controlLabelClass = $bootstrapHelper ? $bootstrapHelper->getClassMapping('control-label') : 'control-label';
$controlsClass     = $bootstrapHelper ? $bootstrapHelper->getClassMapping('controls') : 'controls';
?>
<div class="<?php echo $controlGroupClass . $class ?>" <?php echo $controlGroupAttributes ?>>
	<div class="<?php echo $controlLabelClass ?>">
		<?php
			echo $label;

			if ($config->get('display_field_description', 'use_tooltip') == 'under_field_label' && strlen($description) > 0)
			{
			?>
				<p class="eb-field-description"><?php echo $description; ?></p>
			<?php
			}
		?>
	</div>
	<div class="<?php echo $controlsClass; ?>">
		<?php
			echo $input;
			if ($config->get('display_field_description', 'use_tooltip') == 'under_field_input' && strlen($description) > 0)
			{
			?>
				<p class="eb-field-description"><?php echo $description; ?></p>
			<?php
			}
		?>
	</div>
</div>

