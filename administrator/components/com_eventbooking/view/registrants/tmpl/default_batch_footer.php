<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<button class="btn" type="button" data-dismiss="modal">
	<?php echo Text::_('JCANCEL'); ?>
</button>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('registrant.batch_mail'); return false;">
	<?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>