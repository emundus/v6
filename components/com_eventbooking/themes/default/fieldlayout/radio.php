<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$size            = (int) $row->size ?: 1;
$span            = intval(12 / $size);
$i               = 0;
$numberOptions   = count($options);
$rowFluid        = $bootstrapHelper ? $bootstrapHelper->getClassMapping('row-fluid') : 'row-fluid';
$spanClass       = $bootstrapHelper ? $bootstrapHelper->getClassMapping('span' . $span) : 'span' . $span;
$clearFixClass   = $bootstrapHelper ? $bootstrapHelper->getClassMapping('clearfix') : 'clearfix';
$ukFieldsetClass = $bootstrapHelper ? $bootstrapHelper->getFrameworkClass('uk-fieldset', 2) : '';
?>
<fieldset id="<?php echo $name; ?>" class="eb-radio-container <?php echo $ukFieldsetClass . $clearFixClass; ?>">
    <div class="<?php echo $rowFluid . ' ' . $clearFixClass; ?>">
		<?php
		foreach ($options as $optionValue => $optionText)
		{
            $i++;
            $checked = ($optionValue == $value) ? 'checked' : '';
		?>
            <div class="<?php echo $spanClass ?>">
                <label class="radio" for="<?php echo $name.$i; ?>">
                    <input type="radio" id="<?php echo $name . $i; ?>"
                           name="<?php echo $name; ?>"
                           value="<?php echo htmlspecialchars($optionValue, ENT_COMPAT, 'UTF-8') ?>"
	                    <?php echo $checked . $attributes; ?>
                    /><?php echo $optionText; ?>
                </label>
            </div>
		<?php
            if ($i % $size == 0 && $i < $numberOptions)
            {
            ?>
                </div>
                <div class="<?php echo $rowFluid . ' ' . $clearFixClass; ?>">
            <?php
            }
		}
		?>
	</div>
</fieldset>
