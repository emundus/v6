<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2021 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;

?>
<div id="eb-registration-complete-page" class="eb-container">
	<h1 class="eb-page-heading"><?php echo Text::_('EB_REGISTRATION_CANCELLED'); ?></h1>
	<div class="eb-message"><?php echo $this->message; ?></div>
</div>