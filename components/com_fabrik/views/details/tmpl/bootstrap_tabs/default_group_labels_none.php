<?php
/**
 * Bootstrap Form Template: Labels None
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$element = $this->element;
?>
<div class="mt-3">
<span class="d-none"><?php echo $element->label;?></span>

<div class="fabrikElement <?php echo $element->bsClass;?>">
	<?php echo $element->element;?>
</div>
</div>