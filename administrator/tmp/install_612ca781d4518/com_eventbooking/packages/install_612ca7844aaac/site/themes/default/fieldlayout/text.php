<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
?>
<input type="<?php echo $type; ?>"
       name="<?php echo $name; ?>" id="<?php echo $name; ?>"
       value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"<?php echo $attributes; ?> />
