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
<textarea name="<?php echo $name; ?>"
          id="<?php echo $name; ?>"<?php echo $attributes; ?>><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?></textarea>
