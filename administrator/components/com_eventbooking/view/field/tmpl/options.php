<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<div class="<?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
	<?php
	$span4Class = $bootstrapHelper->getClassMapping('span4');

	for ($i = 0 , $n = count($this->options) ; $i < $n ; $i++)
	{
		$value = $this->options[$i] ;
	?>
        <div class="<?php echo $span4Class; ?>">
            <input value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" type="checkbox" class="form-check-input" name="depend_on_options[]"><?php echo $value;?>
        </div>
	<?php
	}
	?>
</div>
