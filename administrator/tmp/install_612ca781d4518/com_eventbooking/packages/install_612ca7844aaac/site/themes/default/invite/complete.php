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
<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_INVITATION_COMPLETE')); ?></h1>
<p class="eb-message"><?php echo $this->message; ?></p>