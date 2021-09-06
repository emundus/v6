<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if ($this->rowRegistrant->published == 4)
{
	$heading = Text::_('EB_WAITING_LIST_CANCELLED');
}
else
{
	$heading = Text::_('EB_REGISTRATION_CANCELLED');
}
?>
<h1 class="eb_title"><?php echo $this->escape($heading); ?></h1>
<p class="info"><?php echo $this->message; ?></p>